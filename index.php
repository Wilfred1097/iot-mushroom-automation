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

$parking_only_profit_today = $conn->query("SELECT SUM(amount) FROM `parking_only` WHERE date_out = CURDATE()")->fetch_row()[0];
$overnight_parking_profit_today = $conn->query("SELECT SUM(amount) FROM `overnight_parking` WHERE DATE(date_added) = CURDATE()")->fetch_row()[0];
$parking_only = $conn->query("SELECT COUNT(*) FROM parking_only WHERE TIMESTAMP(date_in, time_in) >= NOW() - INTERVAL 12 HOUR AND date_out = ''")->fetch_row()[0];
$overnight_parking = $conn->query("SELECT COUNT(*) FROM parking_only WHERE TIMESTAMP(date_in, time_in) <= NOW() - INTERVAL 12 HOUR AND date_out = ''")->fetch_row()[0];
$vacant_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 0")->fetch_row()[0];
$occupied_space = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 1")->fetch_row()[0];

$total_profit = $parking_only_profit_today + $overnight_parking_profit_today;
$total_space = $vacant_space + $occupied_space;

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Mushroom Automation - Dashboard</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/iot-mushroom.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

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
              <!-- <h6>Kevin Anderson</h6> -->
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
        <a class="nav-link " href="dashboard.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-device-control.php">
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
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Temperature</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <b class="bi bi-thermometer-half"></b>
                    </div>
                    <div class="ps-3">
                      <h6 id="temperature-value">--&deg;C</h6> <!-- Temperature value here -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Humidity</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-moisture"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="humidity-value">--%</h6> <!-- Humidity value here -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3">
              <div class="card info-card customers-card">
                <div class="card-body">
                  <h5 class="card-title">Water Level</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-archive"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="water-level-value">--%</h6> <!-- Water level value here -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Customers Card -->

            <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Device Status</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-plugin"></i>
                    </div>
                    <div class="ps-3">
                      <h6 id="device-status" class="text-danger">Offline</h6> <!-- Default: Offline -->
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Parking Analytics Chart -->
            <div class="col-lg-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Sensor Analytics</h5>

                  <!-- Line Chart -->
                  <div id="reportsChart"></div>

                  <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      // Initialize the chart variable
                      let chart;

                      function fetchDataAndUpdateChart() {
                        // Fetch data from get_sensors_history.php
                        fetch("mysql/get_sensors_history.php")
                          .then(response => response.json())
                          .then(data => {
                            // Process the data to extract series values
                            let temperatureData = [];
                            let humidityData = [];
                            let waterLevelData = [];
                            let timestamps = [];

                            data.forEach(entry => {
                              // Convert date_added (UTC+8) to UTC
                              let dateUTC8 = new Date(entry.date_added.replace(" ", "T")); // Convert to JS Date
                              let dateUTC = new Date(dateUTC8.getTime() - 4 * 60 * 60 * 1000); // Convert to UTC

                              temperatureData.push(parseFloat(entry.temperature));
                              humidityData.push(parseFloat(entry.humidity));
                              waterLevelData.push(parseFloat(entry.water_level));
                              timestamps.push(dateUTC.toISOString()); // Format in ISO (ApexCharts expects this)
                            });

                            // If chart is already initialized, update the series and x-axis
                            if (chart) {
                              chart.updateSeries([
                                {
                                  name: "Temperature",
                                  data: temperatureData,
                                },
                                {
                                  name: "Humidity",
                                  data: humidityData,
                                },
                              ]);

                              chart.updateOptions({
                                xaxis: {
                                  categories: timestamps, // Update the x-axis with the new timestamps
                                },
                              });
                            } else {
                              // Create the chart with dynamic data if it's the first time loading
                              chart = new ApexCharts(document.querySelector("#reportsChart"), {
                                series: [
                                  {
                                    name: "Temperature",
                                    data: temperatureData,
                                  },
                                  {
                                    name: "Humidity",
                                    data: humidityData,
                                  },
                                ],
                                chart: {
                                  height: 350,
                                  type: "area",
                                  toolbar: {
                                    show: false,
                                  },
                                },
                                markers: {
                                  size: 4,
                                },
                                colors: ["#4154f1", "#2eca6a"],
                                fill: {
                                  type: "gradient",
                                  gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.3,
                                    opacityTo: 0.4,
                                    stops: [0, 90, 100],
                                  },
                                },
                                dataLabels: {
                                  enabled: false,
                                },
                                stroke: {
                                  curve: "smooth",
                                  width: 2,
                                },
                                xaxis: {
                                  type: "datetime",
                                  categories: timestamps, // Use the date_added values for x-axis
                                },
                                tooltip: {
                                  x: {
                                    format: "dd/MM/yy HH:mm",
                                  },
                                },
                              }).render();
                            }
                          })
                          // .catch(error => console.error("Error fetching sensor history:", error));
                      }

                      // Initial fetch and chart render
                      fetchDataAndUpdateChart();

                      // Set interval to refresh the chart every 30 seconds
                      setInterval(fetchDataAndUpdateChart, 30000);
                    });
                  </script>


                  <!-- End Line Chart -->

                </div>
              </div>
            </div>

            <!-- Parking Only -->
            <div class="col-12">
              <div class="card recent-sales overflow-auto">

                <div class="card-body">
                  <h5 class="card-title">Sensor Reading History</h5>

                  <div>
                    <!-- Pagination Controls -->
                    <div class="row">
                      <div class="col-md-6">
                        <label for="recordsPerPage">Show:</label>
                        <select id="recordsPerPage" onchange="changePageSize()">
                          <option value="10">10</option>
                          <option value="20">20</option>
                          <option value="30">30</option>
                        </select>

                        <button class="btn btn-success btn-sm" id="prevPageBtn" onclick="changePage(-1)">Previous</button>
                        <button class="btn btn-success btn-sm" id="nextPageBtn" onclick="changePage(1)">Next</button>
                      </div>
                      
                      <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                          <input type="datetime-local" id="startDate" class="form-control form-control-sm m-1" />
                          <span class="mx-2">to</span>
                          <input type="datetime-local" id="endDate" class="form-control form-control-sm m-1" />
                          <button class="btn btn-success btn-sm m-1" id="filterBtn" style="white-space: nowrap;">Download PDF Report</button>
                        </div>
                      </div>

                    </div>

                    <!-- Table -->
                    <table class="table table-hover table-borderless table-striped">
                      <thead>
                        <tr>
                          <th scope="col">Time Stamp</th>
                          <th scope="col">Temperature</th>
                          <th scope="col">Humidity</th>
                          <th scope="col">Water Level</th>
                          <th scope="col">Status</th>
                        </tr>
                      </thead>
                      <tbody id="tableBody">
                        <!-- Data rows will be added here -->
                      </tbody>
                    </table>
                  </div>

                </div>

              </div>
            </div><!-- End Parking Only -->

          </div>
        </div><!-- End Left side columns -->

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

  <script src="assets/plugins/jquery/jquery-3.7.1.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
      document.getElementById("filterBtn").addEventListener("click", function() {
          // Get the start and end date-time values
          let startDate = document.getElementById("startDate").value;
          let endDate = document.getElementById("endDate").value;

          // Log the values to the console
          console.log("Start Date: ", startDate);
          console.log("End Date: ", endDate);

          // Check if the time part contains seconds and append ":00" if missing
          if (startDate && !startDate.includes(":00")) {
              // If seconds are not included, append ":00" (e.g., "2025-02-03T21:00:00")
              startDate += ":00";
          }

          if (endDate && !endDate.includes(":00")) {
              // If seconds are not included, append ":00" (e.g., "2025-02-03T22:00:00")
              endDate += ":00";
          }

          // Log the updated values to the console
          console.log("Updated Start Date: ", startDate);
          console.log("Updated End Date: ", endDate);

          // Check if either startDate or endDate is empty
          if (!startDate || !endDate) {
              // Display SweetAlert error message
              Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Please fill both start date and end date!',
                  confirmButtonText: 'Okay'
              });
          } else {
              // Proceed to open print_report.php with the startDate and endDate as URL parameters
              const url = `mysql/print_report.php?startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}`;

              // Open the print_report.php page with the parameters
              window.open(url, '_blank');
          }
      });
  </script>

  <script>
      let currentPage = 1;
      let pageSize = 10;
      let totalData = [];
      
      function fetchSensorHistory() {
        fetch("mysql/get_sensors_history.php")
          .then(response => response.json())
          .then(data => {
            totalData = data;  // Store fetched data
            displayTableData(); // Display the data with pagination
          })
          .catch(error => console.error("Error fetching sensor history:", error));
      }

      function getStatus(temperature, humidity) {
          if (temperature >= 25 && temperature <= 30 && humidity >= 80 && humidity <= 90) {
              return '<span class="badge bg-success">Normal</span>';
          } else if (temperature > 30 && humidity < 80) {
              return '<span class="badge bg-danger">Too Hot</span>';
          } else if (temperature < 25 && humidity > 90) {
              return '<span class="badge bg-info">Too Cold</span>';
          } else {
              return '<span class="badge bg-warning">Unusual</span>';
          }
      }

      function displayTableData() {
        // Calculate the starting and ending index for the current page
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalData.length);

        const tableBody = document.querySelector("#tableBody");
        tableBody.innerHTML = ""; // Clear previous data

        // Loop through the data for the current page
        for (let i = startIndex; i < endIndex; i++) {
          let entry = totalData[i];

          // Convert date_added to JavaScript Date object
          let dateObj = new Date(entry.date_added.replace(" ", "T"));

          // Format the date as "Feb. 03, 2025 09:09 PM"
          let formattedDate = dateObj.toLocaleString("en-US", {
            year: "numeric",
            month: "short",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            hour12: true
          });

          // Get status based on temperature and humidity
          let temperature = parseFloat(entry.temperature);
          let humidity = parseFloat(entry.humidity);
          let status = getStatus(temperature, humidity);

          let row = `
            <tr>
              <td>${formattedDate}</td>
              <td>${temperature} °C</td>
              <td>${humidity} %</td>
              <td>${entry.water_level} %</td>
              <td>${status}</td>
            </tr>
          `;
          tableBody.innerHTML += row;
        }

        // Toggle the visibility of pagination buttons
        document.getElementById("prevPageBtn").disabled = currentPage === 1;
        document.getElementById("nextPageBtn").disabled = currentPage * pageSize >= totalData.length;
      }

      function changePage(direction) {
        currentPage += direction;
        displayTableData();
      }

      function changePageSize() {
        pageSize = parseInt(document.getElementById("recordsPerPage").value);
        currentPage = 1;  // Reset to the first page
        displayTableData();
      }

      // Initial fetch and display
      fetchSensorHistory();

          // Refresh data every 45 seconds
      setInterval(fetchSensorHistory, 30000);
  </script>

  <script>
    function fetchSensorData() {
      $.ajax({
        url: "mysql/get_sensors_data.php",
        type: "GET",
        dataType: "json",
        success: function (data) {
          if (data.length > 0) {
            let latestData = data[0]; // Get the latest entry
            let currentTime = new Date();
            let sensorTime = new Date(latestData.date_added.replace(" ", "T")); // Convert to JS Date
            
            let timeDifference = (currentTime - sensorTime) / 1000 / 60; // Difference in minutes

            // Determine Online/Offline Status
            let status = timeDifference <= 2 ? "Online" : "Offline";
            let statusColor = status === "Online" ? "text-success" : "text-danger";

            // Update the Device Status Card
            $("#device-status").text(status).removeClass("text-success text-danger").addClass(statusColor);

            // Update the Temperature Card
            $("#temperature-value").html(latestData.temperature + "&deg;C");

            // Update the Humidity Card
            $("#humidity-value").text(latestData.humidity + "%");

            // Update the Water Level Card
            $("#water-level-value").text(latestData.water_level + "%");
          }
        },
        error: function (xhr, status, error) {
          console.error("Error fetching sensor data:", error);
        },
      });
    }

    // Fetch data initially
    fetchSensorData();

    // Refresh data every 45 seconds
    setInterval(fetchSensorData, 30000);
  </script>
</body>

</html>