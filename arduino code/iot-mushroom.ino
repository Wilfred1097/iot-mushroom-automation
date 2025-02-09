#include <WiFiManager.h>  // WiFi Manager for ESP32
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <DHT.h>  // DHT Sensor Library
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ctime>

// Define button pins for ESP32
#define TRIGGER_PIN 16  // WiFi Reset Button
#define misting_btn 13  // Misting Button
#define humidifier_btn 12  // Humidifier Button
#define fan_btn 14  // Fan Button
#define mode_btn 17  // Mode Button

// Define relay pins
#define misting_relay 27  // Misting Relay
#define humidifier_relay 26  // Humidifier Relay
#define fan_relay 25  // Fan Relay

// Define DHT11 Sensor
#define DHTPIN 4  // DHT11 Sensor Data Pin
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

// Define Ultrasonic Sensor Pins
#define TRIG_PIN 5  // Trig Pin for Ultrasonic Sensor
#define ECHO_PIN 18  // Echo Pin for Ultrasonic Sensor

// Set Manila Time Zone (GMT+8)
const long gmtOffset_sec = 8 * 3600;  // 8 hours * 3600 seconds
const int daylightOffset_sec = 0;     // No daylight saving in PH

LiquidCrystal_I2C lcd(0x27, 16, 2);

unsigned long wifiConnectTimeout = 60000;  // 1 minute timeout
unsigned long startAttemptTime;

unsigned long lastSentTime = 0;
const unsigned long sendInterval = 30000; // 45-seconds interval

enum Mode { MANUAL, AUTOMATIC };
Mode mode_state = MANUAL;

bool misting_state = false;
bool humidifier_state = false;
bool fan_state = false;
unsigned long displayResetTime = 0;  // Timer for clearing relay messages

// Global variables for scheduled relay states
bool misting_scheduled = false;
bool humidifier_scheduled = false;
bool fan_scheduled = false;
bool schedule_active = false;
String schedule_end_time = "";

void setup() {
  Serial.begin(115200);
  lcd.init();
  lcd.backlight();
  pinMode(TRIGGER_PIN, INPUT_PULLUP);
  pinMode(misting_btn, INPUT_PULLUP);
  pinMode(humidifier_btn, INPUT_PULLUP);
  pinMode(fan_btn, INPUT_PULLUP);
  pinMode(mode_btn, INPUT_PULLUP);

  pinMode(misting_relay, OUTPUT);
  pinMode(humidifier_relay, OUTPUT);
  pinMode(fan_relay, OUTPUT);
  digitalWrite(misting_relay, LOW);
  digitalWrite(humidifier_relay, LOW);
  digitalWrite(fan_relay, LOW);

  dht.begin();
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  
  WiFiManager wm;
  WiFi.begin();

  startAttemptTime = millis();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi...");

  while (WiFi.status() != WL_CONNECTED && (millis() - startAttemptTime) < wifiConnectTimeout) {
    delay(500);
    Serial.print("...");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");
    delay(2000);
  } else {
    Serial.println("\nNo WiFi found. Entering Offline Mode.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Offline Mode");
    delay(2000);
  }

  // Set time zone to Manila (Asia/Manila)
  configTime(gmtOffset_sec, daylightOffset_sec, "asia.pool.ntp.org", "time.nist.gov");
  setenv("TZ", "PHT-8", 1);  // Manila Time (GMT+8)
  tzset();  // Apply the timezone settings

  Serial.println("Time Sync Completed.");
}

float getWaterLevelPercentage() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  long duration = pulseIn(ECHO_PIN, HIGH);
  float distance = duration * 0.034 / 2;
  return constrain(((50 - distance) / 50) * 100, 0, 100);
}

// Function to check and update relay states based on schedule
void checkSchedule() {
  // Get current time
  time_t now;
  struct tm timeInfo;
  time(&now);
  localtime_r(&now, &timeInfo);

  char currentTime[6];  // "HH:MM"
  strftime(currentTime, sizeof(currentTime), "%H:%M", &timeInfo);
  String strCurrentTime = String(currentTime);

  // If schedule was active but the current time has passed the end time, turn off relays
  if (schedule_active && strCurrentTime > schedule_end_time) {

    digitalWrite(misting_relay, LOW);
    digitalWrite(humidifier_relay, LOW);
    digitalWrite(fan_relay, LOW);

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Schedule Ended");
    delay(2000); // Show message briefly

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(mode_state == AUTOMATIC ? "M: Automatic" : "M: Manual");

    Serial.println("⏹️ Schedule Ended - Relays Turned OFF");
  }
}

// Function to process server response and update relays
void processServerResponse(String payload) {
  StaticJsonDocument<1024> doc;
  DeserializationError error = deserializeJson(doc, payload);

  if (error) {
    Serial.print("❌ JSON Parsing Failed: ");
    Serial.println(error.f_str());
    return;
  }

  if (doc.containsKey("relays")) {
    int misting_value = doc["relays"]["misting_relay"].as<int>();
    int humidifier_value = doc["relays"]["humidifier_relay"].as<int>();
    int fan_value = doc["relays"]["fan_relay"].as<int>();
    int automation_mode = doc["relays"]["automation_mode"].as<int>();

    // Get current time
    time_t now;
    struct tm timeInfo;
    time(&now);
    localtime_r(&now, &timeInfo);

    char currentTime[6];  // "HH:MM"
    strftime(currentTime, sizeof(currentTime), "%H:%M", &timeInfo);
    String strCurrentTime = String(currentTime);

    schedule_active = false;
    misting_scheduled = false;
    humidifier_scheduled = false;
    fan_scheduled = false;

    // Check schedule
    if (doc.containsKey("schedule")) {
      JsonArray scheduleArray = doc["schedule"].as<JsonArray>();

      for (JsonObject schedule : scheduleArray) {
        String start_time = schedule["start_time"].as<String>();
        String end_time = schedule["end_time"].as<String>();
        String devices = schedule["device"].as<String>();

        // If current time is within the scheduled period
        if (strCurrentTime >= start_time && strCurrentTime <= end_time) {
          schedule_active = true;
          schedule_end_time = end_time; // Store the end time for later comparison

          if (devices.indexOf("Misting") != -1) misting_scheduled = true;
          if (devices.indexOf("Humidifier") != -1) humidifier_scheduled = true;
          if (devices.indexOf("Fan") != -1) fan_scheduled = true;
        }
      }
    }

    if (schedule_active) {
      // Override manual/automatic mode when schedule is active
      digitalWrite(misting_relay, misting_scheduled ? HIGH : LOW);
      digitalWrite(humidifier_relay, humidifier_scheduled ? HIGH : LOW);
      digitalWrite(fan_relay, fan_scheduled ? HIGH : LOW);

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Scheduled Mode");

      Serial.println("🔄 Scheduled Mode Active: Relays Controlled by Schedule");
    } else {
      // No active schedule, follow relay status from server
      digitalWrite(misting_relay, misting_value ? HIGH : LOW);
      digitalWrite(humidifier_relay, humidifier_value ? HIGH : LOW);
      digitalWrite(fan_relay, fan_value ? HIGH : LOW);

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("M : Automatic");

      // Display Automatic Mode if automation_mode is 1, otherwise Manual Mode
      // if (automation_mode == 1) {
      //   lcd.print("M : Automatic");
      // } else {
      //   lcd.print("M : Manual");
      // }

      Serial.println("⚠️ No Active Schedule - Following Auto/Manual Mode");
      Serial.print("🌡️ Misting Relay: "); Serial.println(misting_value ? "ON" : "OFF");
      Serial.print("💦 Humidifier Relay: "); Serial.println(humidifier_value ? "ON" : "OFF");
      Serial.print("🌀 Fan Relay: "); Serial.println(fan_value ? "ON" : "OFF");
    }
  } else {
    Serial.println("⚠️ Warning: No 'relays' object in response.");
  }
}

// Function to send sensor data and get the schedule from the server
void sendDataToServer(float temperature, float humidity, float water_level) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = "http://smartmushroomfarm.site/mysql/add_sensors_data.php?temperature=";
    url += String(temperature, 2) + "&humidity=" + String(humidity, 2) + "&water_level=" + String(water_level, 2);

    http.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);
    http.begin(url);
    int httpResponseCode = http.GET();

    if (httpResponseCode == 200) {
      String payload = http.getString();
      Serial.print("✅ Data Forwarded Successfully");
      Serial.println("📡 Server Response: " + payload);

      processServerResponse(payload); // Handle schedule and relay states
    } else {
      Serial.print("❌ Error Sending Data. HTTP Response Code: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  }
}

void loop() {
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  float water_level = getWaterLevelPercentage();

  if (millis() - lastSentTime > sendInterval) {
    sendDataToServer(temperature, humidity, water_level);
    lastSentTime = millis();
  }

  checkSchedule();

    // Check for WiFi Reset Button (AP Mode)
  if (digitalRead(TRIGGER_PIN) == LOW) {
    WiFiManager wm;
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Opening Wifi");
    lcd.setCursor(0, 1);
    lcd.print("Captive Portal");
    delay(1000);

    wm.setConfigPortalTimeout(120);
    if (!wm.startConfigPortal("IoT Mushroom Automation")) {
      Serial.println("Failed to connect, restarting...");
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("AP Timeout...");
      delay(3000);
      ESP.restart();
    }

    Serial.println("Connected to WiFi!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");
    delay(2000);
  }

  if (digitalRead(mode_btn) == LOW) {
    delay(200);
    mode_state = (mode_state == MANUAL) ? AUTOMATIC : MANUAL;
    digitalWrite(misting_relay, LOW);
    digitalWrite(humidifier_relay, LOW);
    digitalWrite(fan_relay, LOW);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(mode_state == AUTOMATIC ? "M: Automatic" : "M: Manual");
    delay(500);
  }

  if (mode_state == AUTOMATIC) {
    lcd.setCursor(0, 0);
    lcd.print("M : Automatic");
    lcd.setCursor(0, 1);
    lcd.print("T:"); lcd.print(temperature, 1);
    lcd.print(" H:"); lcd.print(humidity, 0);
    lcd.print(" W:"); lcd.print(water_level, 0);

    // Relay control logic
    digitalWrite(fan_relay, temperature > 30 ? HIGH : LOW);
    digitalWrite(misting_relay, humidity < 80 ? HIGH : LOW);
    digitalWrite(humidifier_relay, humidity < 80 ? HIGH : LOW);
  } else {
    lcd.setCursor(0, 0);
    if (digitalRead(misting_btn) == LOW) {
      misting_state = !misting_state;
      digitalWrite(misting_relay, misting_state ? HIGH : LOW);
      lcd.clear();
      lcd.setCursor(0, 1);
      lcd.print(misting_state ? "Misting On" : "Misting Off");
      displayResetTime = millis() + 3000;
    }
    if (digitalRead(humidifier_btn) == LOW) {
      humidifier_state = !humidifier_state;
      digitalWrite(humidifier_relay, humidifier_state ? HIGH : LOW);
      lcd.clear();
      lcd.setCursor(0, 1);
      lcd.print(humidifier_state ? "Humidifier On" : "Humidifier Off");
      displayResetTime = millis() + 3000;
    }
    if (digitalRead(fan_btn) == LOW) {
      fan_state = !fan_state;
      digitalWrite(fan_relay, fan_state ? HIGH : LOW);
      lcd.clear();
      lcd.setCursor(0, 1);
      lcd.print(fan_state ? "Fan On" : "Fan Off");
      displayResetTime = millis() + 3000;
    }

    if (millis() > displayResetTime) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("M: Manual");
      lcd.setCursor(0, 1);
      lcd.print("T:"); lcd.print(temperature, 1);
      lcd.print(" H:"); lcd.print(humidity, 0);
      lcd.print(" W:"); lcd.print(water_level, 0);
    }
  }

  delay(1000);
}
