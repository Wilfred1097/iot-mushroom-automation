<?php
// Start session (if needed)
session_start();
require './mysql/conn.php'; // Adjust path to conn.php if necessary

// Check if the 'SmartSpot' cookie exists
if (!isset($_COOKIE['iot-mushroom'])) {
    // Redirect to login page if the cookie does not exist
    header("Location: pages-login.php");
    exit;
}
// Retrieve the email from the cookie
$user_data = json_decode($_COOKIE['iot-mushroom'], true);
$user_email = $user_data['email'];
$user_type = $user_data['user_type'];

// Initialize customer details
$customer_fname = $customer_mname = $customer_lname = '';

// Query to select customer details
$stmt = $conn->prepare("SELECT fname, mname, lname, user_type, profile FROM user WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        $customer_fname = $customer['fname'];
        $customer_mname = $customer['mname'];
        $customer_lname = $customer['lname'];
        $user_type = $customer['user_type'];

        // Get the first letter of the first name
        $first_letter_fname = substr(htmlspecialchars($customer_fname), 0, 1);
    } else {
        // Handle the case where no customer is found
        echo "No customer found for this email.";
    }
    
    $stmt->close();
} else {
    // Handle SQL prepare error
    echo "SQL prepare error: " . $conn->error;
}

// Query to select customer details
$vacant_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 0")->fetch_row()[0];
$occupied_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 1")->fetch_row()[0];

$total_space = $vacant_space + $occupied_space;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign_out'])) {
    // Delete the user_email cookie
    setcookie('iot-mushroom', '', time() - 3600, '/'); // Set expiration time to the past to delete

    // Optionally, destroy the session if you are using one
    session_unset();
    session_destroy();

    // Redirect to login page
    header("Location: pages-login.php");
    exit;
}

// Assuming you have a database connection established
$query = "SELECT COUNT(*) AS badge_count FROM parking_only WHERE TIMESTAMP(date_in, time_in) <= NOW() - INTERVAL 12 HOUR AND date_out = ''";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$badgeCount = $row['badge_count'];

$query1 = "SELECT COUNT(*) AS badge_count1 FROM parking_only WHERE TIMESTAMP(date_in, time_in) >= NOW() - INTERVAL 12 HOUR AND date_out = ''";
$result1 = mysqli_query($conn, $query1);
$row = mysqli_fetch_assoc($result1);
$badgeCount1 = $row['badge_count1'];

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Mushroom Automation - Device Control</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/iot-mushroom.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Include SweetAlert CSS and JS -->
  <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">
  <script src="assets/plugins/sweetalert2/sweetalert2.js"></script>

  <style>
    th {
            cursor: pointer;
        }
  </style>

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/jhcsc_logo.png" style="height: 30px; width:40px; margin-right: 15px;" alt="">
        <span class="d-none d-lg-block">Mushroom Automation</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <!-- <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div> -->
    <!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile/<?php echo $customer['profile']?>" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $first_letter_fname . '. ' . htmlspecialchars($customer_lname); ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo htmlspecialchars($customer_fname) . ' ' . htmlspecialchars($customer_mname) . ' ' . htmlspecialchars($customer_lname); ?></h6>
              <span><?php echo htmlspecialchars($user_type); ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <form method="POST">
                    <button type="submit" name="sign_out" class="dropdown-item d-flex align-items-center" style="background: none; border: none; cursor: pointer;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sign Out</span>
                    </button>
                </form>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link collapsed" href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link " href="pages-device-control.php">
          <i class="bi bi-toggles"></i>
          <span>Device Control</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-schedule.php">
          <i class="bi bi-clock"></i>
          <span>Device Scheduling</span>
        </a>
      </li>

      <?php if ($user_type === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link collapsed" href="pages-staff-management.php">
            <i class="bi bi-person"></i>
            <span>Researcher Management</span>
          </a>
        </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-config.php">
          <i class="bi bi-gear"></i>
          <span>Configuration</span>
        </a>
      </li>

    </ul>

  </aside><!-- End Sidebar-->

<main id="main" class="main">

    <section class="section dashboard">
      <div class="card shadow-lg rounded-4 p-3">
        <div class="container mt-3">
          <div class="row">
            <div class="col-md-6">
              <div class="card text-center">
                <div class="card-body">
                  <h5 class="card-title">
                    <i class="bi bi-gear"></i> System Control
                  </h5>
                  <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="automation_modeSwitch" style="transform: scale(1.5);" onclick="logAutomationState()">
                    <label class="form-check-label ml-2 mb-2">Automation Relay</label>
                  </div>
                  <p class="card-text"><i class="bi bi-info-circle"></i> Switch between Manual or Automatic Mode</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card text-center">
                <div class="card-body">
                  <h5 class="card-title">
                    <i class="bi bi-calendar"></i> Schedule
                  </h5>
                  <button class="btn btn-success btn-sm mb-2" onclick="window.location.href='pages-schedule.php'">View Schedule</button>
                  <p class="card-text"><i class="bi bi-info-circle"></i> Manage automation schedule</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

          <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
            <div class="card info-card sales-card">
              <div class="card-body text-center">
                <h5 class="card-title">
                  <i class="bi bi-droplet-half"></i> Misting System
                </h5>
                <!-- Toggle Switch for Misting Relay -->
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="misting_relaySwitch" onclick="logMistingState()">
                  <label class="form-check-label" for="misting_relaySwitch">Misting Relay</label>
                </div>
                <p class="card-text"><i class="bi bi-info-circle"></i> Control Misting Relay Remotely</p>
              </div>
            </div>
          </div><!-- End Sales Card -->

          <!-- Customers Card -->
          <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
            <div class="card info-card customers-card">
              <div class="card-body text-center">
                <h5 class="card-title">
                  <i class="bi bi-cloud-fill"></i> Humidifier
                </h5>
                <!-- Toggle Switch for Humidifier Relay -->
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="humidifier_relaySwitch" onclick="logHumidifierState()">
                  <label class="form-check-label" for="humidifier_relaySwitch">Humidifier Relay</label>
                </div>
                <p class="card-text"><i class="bi bi-info-circle"></i> Control Humidifier Relay Remotely</p>
              </div>
            </div>
          </div><!-- End Customers Card -->

          <!-- Customers Card -->
          <div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
            <div class="card info-card customers-card">
              <div class="card-body text-center">
                <h5 class="card-title">
                  <i class="bi bi-fan"></i> Fan
                </h5>
                <!-- Toggle Switch for Fan Relay -->
                <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" id="fan_relaySwitch" onclick="logFanState()">
                  <label class="form-check-label" for="fan_relaySwitch">Fan Relay</label>
                </div>
                <p class="card-text"><i class="bi bi-info-circle"></i> Control Fan Relay Remotely</p>
              </div>
            </div>
          </div>

          </div>
        </div><!-- End Left side columns -->

      </div>
    </section>

    <section>
      <div class="card mt-3">
        <div class="card-header text-black">
          <h5 class="mb-0">Logs</h5>
        </div>
        <div class="card-body">
          <ul id="logsList" class="list-group" style="max-height: 400px; overflow-y: auto; font-size: 12px;">
            <!-- Logs will be dynamically inserted here -->
          </ul>
        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      fetchLogs();
    });

    function fetchLogs() {
      fetch("mysql/get_logs.php")
        .then(response => response.json())
        .then(data => {
          const logsList = document.getElementById("logsList");
          logsList.innerHTML = ""; // Clear existing logs

          data.forEach(log => {
            // Combine first name, middle name, and last name
            const fullName = `${log.fname} ${log.mname} ${log.lname}`;

            // Format the date to "Feb. 05, 2025 03:00 AM"
            const formattedDate = formatDate(log.date_created);

            // Create the log item with formatted date, event, and full name
            const logItem = document.createElement("li");
            logItem.className = "list-group-item";
            logItem.textContent = `${formattedDate} - ${log.event} - ${fullName}`;

            logsList.appendChild(logItem);
          });
        })
        .catch(error => console.error("Error fetching logs:", error));
    }

    function formatDate(dateString) {
      const date = new Date(dateString);
      const options = {
        month: "short",
        day: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
        timeZone: "Asia/Singapore"
      };
      return date.toLocaleString("en-US", options);
    }

    function logAutomationState() {
      const automationSwitch = document.getElementById('automation_modeSwitch');
      const status = automationSwitch.checked ? 1 : 0;

      // Send the status to update_automation.php via AJAX
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "mysql/update_automation.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      // Prepare data to send
      const data = "status=" + status;

      // Handle response from the server
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          fetchRelayStatus();
          // console.log(xhr.responseText); // Output the server's response
          if (status === 1) {
            // Fetch sensor data only if automation is turned ON
            fetchTemperatureAndHumidity();
          }
        }
      };

      // Send the request with status data
      xhr.send(data);
    }

    function fetchTemperatureAndHumidity() {
      fetch('mysql/get_latest_reading.php')
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data.status === "success") {
            const temperature = parseFloat(data.data.temperature);
            const humidity = parseFloat(data.data.humidity);

            // Apply automation logic
            applyAutomationLogic(temperature, humidity);
          } else {
            console.error("Error fetching sensor data:", data.message);
          }
        })
        .catch(error => console.error("Fetch error:", error));
    }

    function applyAutomationLogic(temperature, humidity) {
      let mistingRelay = humidity < 80 ? 1 : 0;
      let fanRelay = temperature > 30 ? 1 : 0;
      let humidifierRelay = humidity < 80 ? 1 : 0;

      // Log relay statuses in the console
      console.log(`Misting Relay: ${mistingRelay}`);
      console.log(`Fan Relay: ${fanRelay}`);
      console.log(`Humidifier Relay: ${humidifierRelay}`);

      // Send the new relay statuses to the server
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "mysql/update_relay.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      const data = `misting_relay=${mistingRelay}&humidifier_relay=${humidifierRelay}&fan_relay=${fanRelay}`;

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          // console.log(xhr.responseText); // Output the server's response
          fetchLogs();
          fetchRelayStatus();
        }
      };

      xhr.send(data);
    }
 
    function logMistingState() {
      const mistingSwitch = document.getElementById('misting_relaySwitch');
      const status = mistingSwitch.checked ? 1 : 0;

      // Log the status in the console
      // console.log('Misting Relay: ' + status);

      // Send the status to update_misting.php via AJAX
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "mysql/update_misting.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      
      // Prepare data to send
      const data = "status=" + status;

      // Handle response from the server
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          fetchLogs();
          // console.log(xhr.responseText); // Output the server's response
        }
      };

      // Send the request with status data
      xhr.send(data);
    }

    function logHumidifierState() {
      const humidifierSwitch = document.getElementById('humidifier_relaySwitch');
      const status = humidifierSwitch.checked ? 1 : 0;

      // Log the status in the console
      // console.log('Humidifier Relay: ' + status);

      // Send the status to update_misting.php via AJAX
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "mysql/update_humidifier.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      
      // Prepare data to send
      const data = "status=" + status;

      // Handle response from the server
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          fetchLogs();
          // console.log(xhr.responseText); // Output the server's response
        }
      };

      // Send the request with status data
      xhr.send(data);
    }
 
    function logFanState() {
        const fanSwitch = document.getElementById('fan_relaySwitch');
        const status = fanSwitch.checked ? 1 : 0;

        // Log the status in the console
        // console.log('Fan Relay: ' + status);

        // Send the status to update_misting.php via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "mysql/update_fan.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        // Prepare data to send
        const data = "status=" + status;

        // Handle response from the server
        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4 && xhr.status === 200) {
            fetchLogs();
            // console.log(xhr.responseText); // Output the server's response
          }
        };

        // Send the request with status data
        xhr.send(data);
      }

      function fetchRelayStatus() {
        fetch("mysql/get_relay_status.php")
          .then(response => response.json())
          .then(data => {
            data.forEach(relay => {
              const switchElement = document.getElementById(relay.relay_name + "Switch");
              if (switchElement) {
                switchElement.checked = relay.relay_status == "1"; // Set switch state
              }
            });
          })
          .catch(error => console.error("Error fetching relay status:", error));
      }
 
      document.addEventListener("DOMContentLoaded", function () {
        fetchRelayStatus();
      });
    </script>

</body>

</html>