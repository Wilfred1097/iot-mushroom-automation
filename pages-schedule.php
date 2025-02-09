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

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Mushroom Automation - Device Scheduling</title>
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
        <a class="nav-link collapsed" href="pages-device-control.php">
          <i class="bi bi-toggles"></i>
          <span>Device Control</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="pages-schedule.php">
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
        <h5 class="card-title ml-3 mb-0">
          <i class="bi bi-clock"></i> Device Scheduling
        </h5>
        <div class="container mt-3">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="day-select" class="form-label">Select Day</label>
              <select id="day-select" class="form-select">
                <option value="Sun">Sunday</option>
                <option value="Mon">Monday</option>
                <option value="Tue">Tuesday</option>
                <option value="Wed">Wednesday</option>
                <option value="Thuy">Thursday</option>
                <option value="Fri">Friday</option>
                <option value="Sat">Saturday</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="start-time" class="form-label">Start Time</label>
              <input type="time" id="start-time" class="form-control">
            </div>
            <div class="col-md-4">
              <label for="end-time" class="form-label">End Time</label>
              <input type="time" id="end-time" class="form-control">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12 text-center">
              <span id="selected-schedule">Selected Schedule: Not set</span>
            </div>
            <div class="col-md-12 text-center">
              <span id="selected-devices">Selected Devices: Not set</span>
            </div>
          </div>
          <div class="row mb-3 align-items-center">
            <label class="form-label col-md-3">Select Device</label>
            <div class="col-md-9 d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="device" id="misting" value="Misting">
                <label class="form-check-label" for="misting">Misting</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="device" id="humidifier" value="Humidifier">
                <label class="form-check-label" for="humidifier">Humidifier</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="device" id="fan" value="Fan">
                <label class="form-check-label" for="fan">Fan</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12 text-center">
              <button id="submit-btn" class="btn btn-success w-100">Submit</button>
            </div>
          </div>
        </div>
      </div>
    </section>

  <section>
    <div class="card mt-3">
      <div class="card-header text-black">
        <h5 class="mb-0">Schedule list</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-sm table-bordered text-center table-striped table-hover">
            <thead class="table-success">
              <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Days</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Devices</th>
                <th>Date Added</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="logsTableBody">
              <!-- Logs will be dynamically inserted here -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- Edit Schedule Modal -->
  <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editScheduleForm">
            <input type="hidden" id="editScheduleId">

            <div class="mb-3">
              <label for="editStartTime" class="form-label">Start Time</label>
              <input type="time" class="form-control" id="editStartTime" required>
            </div>

            <div class="mb-3">
              <label for="editEndTime" class="form-label">End Time</label>
              <input type="time" class="form-control" id="editEndTime" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Devices</label>
              <div id="editDevicesContainer">
                <input type="checkbox" name="device" value="Misting"> Misting
                <input type="checkbox" name="device" value="Humidifier"> Humidifier
                <input type="checkbox" name="device" value="Fan"> Fan
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="updateSchedule()">Save Changes</button>
        </div>
      </div>
    </div>
  </div>


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
      fetch("mysql/get_schedule_logs.php")
        .then(response => response.json())
        .then(data => {
          const logsTableBody = document.getElementById("logsTableBody");
          logsTableBody.innerHTML = ""; // Clear existing logs

          data.forEach((log, index) => {
            // Combine first name, middle name (if any), and last name
            const fullName = `${log.fname} ${log.mname ? log.mname + ' ' : ''}${log.lname}`.trim();

            // Format the date
            const formattedDate = formatDate(log.date_added);

            // Create table row with edit and delete icons
            const daysFullName = {
              "Mon": "Monday",
              "Tue": "Tuesday",
              "Wed": "Wednesday",
              "Thu": "Thursday",
              "Fri": "Friday",
              "Sat": "Saturday",
              "Sun": "Sunday"
            };

            function formatTimeTo12Hour(time) {
              const [hour, minute] = time.split(":");
              let period = "AM";
              let formattedHour = parseInt(hour);

              if (formattedHour >= 12) {
                period = "PM";
                if (formattedHour > 12) {
                  formattedHour -= 12;
                }
              } else if (formattedHour === 0) {
                formattedHour = 12;
              }

              return `${formattedHour}:${minute} ${period}`;
            }

           const row = `
            <tr>
              <td>${index + 1}</td>
              <td>${fullName}</td>
              <td>${daysFullName[log.days] || log.days}</td> <!-- Convert short day name to full name -->
              <td>${formatTimeTo12Hour(log.start_time)}</td>
              <td>${formatTimeTo12Hour(log.end_time)}</td>
              <td>${log.device}</td>
              <td>${formattedDate}</td>
              <td>
                <button class="btn btn-sm btn-primary" onclick="editLog(${log.id})">
                  <i class="bi bi-pen"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteLog(${log.id})">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          `;

            logsTableBody.innerHTML += row;
          });
        })
        .catch(error => console.error("Error fetching logs:", error));
    }

    function updateSchedule() {
        let id = document.getElementById("editScheduleId").value;
        let startTime = document.getElementById("editStartTime").value;
        let endTime = document.getElementById("editEndTime").value;

        let selectedDevices = Array.from(document.querySelectorAll("#editDevicesContainer input[type=checkbox]:checked"))
          .map(cb => cb.value);

        if (!startTime || !endTime || selectedDevices.length === 0) {
          Swal.fire("Warning", "Please fill all fields before saving.", "warning");
          return;
        }

        fetch("mysql/update_schedule.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            id: id,
            start_time: startTime,
            end_time: endTime,
            devices: selectedDevices
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === "success") {
            Swal.fire("Success", "Schedule updated successfully!", "success").then(() => {
              location.reload();
              fetchLogs(); // Refresh logs table
            });
          } else {
            Swal.fire("Error", "Failed to update schedule.", "error");
          }
        })
        .catch(error => console.error("Error updating schedule:", error));
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

    function editLog(id) {
      // Find the row with the corresponding schedule
      fetch(`mysql/get_single_schedule.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === "success") {
            document.getElementById("editScheduleId").value = id;
            document.getElementById("editStartTime").value = data.schedule.start_time;
            document.getElementById("editEndTime").value = data.schedule.end_time;

            // Uncheck all checkboxes first
            document.querySelectorAll("#editDevicesContainer input[type=checkbox]").forEach(cb => cb.checked = false);

            // Check devices that exist in the fetched schedule
            data.schedule.device.split(", ").forEach(device => {
              let checkbox = document.querySelector(`#editDevicesContainer input[value="${device}"]`);
              if (checkbox) checkbox.checked = true;
            });

            // Show the modal
            var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            editModal.show();
          } else {
            Swal.fire("Error", "Failed to fetch schedule details.", "error");
          }
        })
        .catch(error => console.error("Error fetching schedule:", error));
    }

    function deleteLog(id) {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`mysql/delete_schedule_log.php?id=${id}`, { method: "DELETE" })
            .then(response => response.json())
            .then(data => {
              if (data.status === "success") {
                Swal.fire({
                  title: "Deleted!",
                  text: "Log has been deleted.",
                  icon: "success"
                }).then(() => {
                  fetchLogs(); // Refresh logs after deletion
                });
              } else {
                Swal.fire({
                  title: "Error!",
                  text: "Failed to delete log: " + data.message,
                  icon: "error"
                });
              }
            })
            .catch(error => {
              console.error("Error deleting log:", error);
              Swal.fire({
                title: "Error!",
                text: "Something went wrong while deleting the log.",
                icon: "error"
              });
            });
        }
      });
    }

    document.addEventListener("DOMContentLoaded", function () {
      const daySelect = document.getElementById("day-select");
      const startTime = document.getElementById("start-time");
      const endTime = document.getElementById("end-time");
      const selectedSchedule = document.getElementById("selected-schedule");
      const selectedDevices = document.getElementById("selected-devices");
      const deviceCheckboxes = document.querySelectorAll("input[name='device']");
      const submitButton = document.getElementById("submit-btn");
      var userEmail = <?php echo json_encode($user_email); ?>;

      function formatTime(time) {
          const [hour, minute] = time.split(":");
          const period = hour >= 12 ? "PM" : "AM";
          const formattedHour = hour % 12 || 12;
          return `${formattedHour}:${minute} ${period}`;
      }

      function updateDevices() {
          const selected = Array.from(deviceCheckboxes)
              .filter(checkbox => checkbox.checked)
              .map(checkbox => checkbox.value);
          selectedDevices.textContent = selected.length > 0 
              ? `Selected Devices: ${selected.join(", ")}` 
              : "Selected Devices: Not set";
      }

      function clearForm() {
          // Clear all input fields and reset the UI
          daySelect.value = "";
          startTime.value = "";
          endTime.value = "";
          deviceCheckboxes.forEach(checkbox => checkbox.checked = false);
          selectedSchedule.textContent = "Selected Schedule: Not set";
          selectedDevices.textContent = "Selected Devices: Not set";
      }

      submitButton.addEventListener("click", function () {
          const selectedDay = daySelect.value;
          const selectedStartTime = startTime.value;
          const selectedEndTime = endTime.value;
          const selectedDeviceList = Array.from(deviceCheckboxes)
              .filter(checkbox => checkbox.checked)
              .map(checkbox => checkbox.value);

          if (!selectedDay || !selectedStartTime || !selectedEndTime || selectedDeviceList.length === 0) {
              Swal.fire({
                  icon: "warning",
                  title: "Missing Information",
                  text: "Please select a day, start time, end time, and at least one device.",
              });
              return;
          }

          Swal.fire({
              title: "Confirm Schedule",
              html: `
                  <p>Are you sure you want to set the schedule?</p>
                  <strong>Every ${selectedDay} from ${selectedStartTime} to ${selectedEndTime}</strong>
                  <br>
                  <strong>Devices: ${selectedDeviceList.join(", ")}</strong>
              `,
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Yes, Confirm",
              cancelButtonText: "No, Cancel",
          }).then((result) => {
              if (result.isConfirmed) {
                  fetch("mysql/add_schedule.php", {
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json",
                      },
                      body: JSON.stringify({
                          day: selectedDay,
                          start_time: selectedStartTime,
                          end_time: selectedEndTime,
                          devices: selectedDeviceList,
                          user_email: userEmail
                      }),
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.status === 200) {
                          Swal.fire({
                              icon: "success",
                              title: "Schedule Successfully Added",
                              text: data.message,
                          }).then(() => {
                              clearForm(); // Clear inputs after success
                              fetchLogs();
                          });
                      } else if (data.status === 409) {
                          Swal.fire({
                              icon: "warning",
                              title: "Schedule Conflict",
                              text: "Schedule already added. Please select a different time.",
                          });
                      } else {
                          Swal.fire({
                              icon: "error",
                              title: "Error",
                              text: data.message,
                          });
                      }
                  })
                  .catch(error => {
                      console.error("Error:", error);
                      Swal.fire({
                          icon: "error",
                          title: "Error",
                          text: "Failed to communicate with the server.",
                      });
                  });
              }
          });
      });

      daySelect.addEventListener("change", updateSchedule);
      startTime.addEventListener("input", updateSchedule);
      endTime.addEventListener("input", updateSchedule);
      deviceCheckboxes.forEach(checkbox => checkbox.addEventListener("change", updateDevices));
  });
</script>
</body>

</html>