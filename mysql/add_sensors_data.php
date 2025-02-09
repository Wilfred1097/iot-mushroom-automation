<?php

header("Content-Type: application/json");

// Database connection
require 'conn.php'; // Adjust path to conn.php if necessary

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// Get sensor values from request
$temperature = isset($_REQUEST['temperature']) ? floatval($_REQUEST['temperature']) : null;
$humidity = isset($_REQUEST['humidity']) ? floatval($_REQUEST['humidity']) : null;
$water_level = isset($_REQUEST['water_level']) ? floatval($_REQUEST['water_level']) : null;

// Validate input
if ($temperature === null || $humidity === null || $water_level === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Missing parameters."]);
    exit();
}

// Insert sensor data
$stmt = $conn->prepare("INSERT INTO sensors_data (temperature, humidity, water_level, date_added) VALUES (?, ?, ?, CONVERT_TZ(NOW(), '+00:00', '+08:00'))");
$stmt->bind_param("ddd", $temperature, $humidity, $water_level);
$insertSuccess = $stmt->execute();
$stmt->close();

// Fetch relay statuses
$relayStatuses = [];
$query = "SELECT relay_name, relay_status FROM relays WHERE relay_name IN ('misting_relay', 'humidifier_relay', 'fan_relay', 'automation_mode')";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $relayStatuses[$row['relay_name']] = (int) $row['relay_status']; // Ensure numeric values
    }
    $result->close();
}

// Get the current day abbreviation (e.g., "Mon", "Tue", "Wed")
$currentDay = date("D");

// Fetch today's schedule only
$schedule = [];
$scheduleQuery = $conn->prepare("SELECT start_time, end_time, device FROM schedule WHERE days = ?");
$scheduleQuery->bind_param("s", $currentDay);
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();

if ($scheduleResult) {
    while ($row = $scheduleResult->fetch_assoc()) {
        $schedule[] = [
            "start_time" => $row["start_time"],
            "end_time" => $row["end_time"],
            "device" => $row["device"]
        ];
    }
    $scheduleResult->close();
}
$scheduleQuery->close();

// Close connection
$conn->close();

// Construct response
$response = [
    "status" => $insertSuccess ? "success" : "error",
    "message" => $insertSuccess ? "Data inserted successfully." : "Failed to insert data.",
    "relays" => $relayStatuses,
    "schedule" => $schedule
];

// Send JSON response
http_response_code($insertSuccess ? 200 : 500);
echo json_encode($response);

?>
