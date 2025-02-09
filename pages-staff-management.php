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

// Check if user is 'staff' and trying to access a restricted page
$restricted_pages = ['pages-staff-management.php']; // Add any restricted pages to this array
$current_page = basename($_SERVER['PHP_SELF']); // Get the name of the current page

if ($user_type === 'staff' && in_array($current_page, $restricted_pages)) {
    // Redirect to the index page or simply reload the current page
    header("Location: pages-error-404.php"); // Adjust as needed to redirect or reload the page
    exit;
}

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
        // Handle the case where no staff is found
        echo "No staff found for this email.";
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

  <title>Mushroom Automation - Researchers Management</title>
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

  <!-- Include SweetAlert CSS and JS -->
  <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">
  <script src="assets/plugins/sweetalert2/sweetalert2.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://unpkg.com/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

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
      <a href="dashboard.php" class="logo d-flex align-items-center">
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
        <a class="nav-link collapsed" href="pages-schedule.php">
          <i class="bi bi-clock"></i>
          <span>Device Scheduling</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link " href="pages-customer-management.php">
          <i class="bi bi-person"></i>
          <span>Researcher Management</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-config.php">
          <i class="bi bi-gear"></i>
          <span>Configuration</span>
        </a>
      </li>
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
             <div class="row align-items-center">
                <!-- First column for the title -->
                <div class="col-md-6">
                  <h5 class="card-title">Researcher Management</h5>
                </div>

                <!-- Second column for the search box and button -->
                <div class="col-md-6 d-flex justify-content-end">
                  <input type="text" id="search-box" class="form-control me-2" placeholder="Search..." style="max-width: 400px;">
                  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#basicModal">Add Researcher</button>
                </div>
                 <div class="modal fade" id="basicModal" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Add Researcher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                        <div class="modal-body">
                          <form class="row g-3" id="registrationForm" method="POST" action="mysql/add_staff.php" enctype="multipart/form-data">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="first_name" class="form-control" id="first_name" placeholder="First Name" required>
                                    <label for="first_name">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="middle_name" class="form-control" id="middle_name" placeholder="Middle Name" required>
                                    <label for="middle_name">Middle Name</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="last_name" class="form-control" id="last_name" placeholder="Last Name" required>
                                    <label for="last_name">Last Name</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="address" class="form-control" id="address" placeholder="Address" required>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <select name="gender" class="form-select" id="gender" aria-label="Gender" required>
                                        <option value="" disabled selected>Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <label for="gender">Select gender</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="tel" name="contact_number" class="form-control" id="contact_number" placeholder="Contact Number" required>
                                    <label for="contact_number">Contact Number</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
                                    <label for="email">Email</label>
                                    <div id="emailTooltip" class="tooltip"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="mb-2" for="ProfilePicture">Profile Picture</label>
                                <div class="form">
                                    <input type="file" class="form-control" id="ProfilePicture" name="profilePicture" accept=".png, .jpg, .jpeg" required>
                                    <div id="fileTooltip" class="tooltip"></div>
                                    <small class="form-text text-muted">Accepted formats: .png, .jpg, .jpeg</small>
                                </div>
                            </div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                      </div>
                      
                    </div>
                  </div>
                </div><!-- End Basic Modal-->
              </div>

              <!-- Table with stripped rows -->
              <table id="data-table" class="table table-hover table-borderless table-striped">
                <thead>
                  <tr>
                    <th onclick="sortTable(0)">Name</th>
                    <th onclick="sortTable(1)">Address</th>
                    <th onclick="sortTable(2)">Gender</th>
                    <th onclick="sortTable(3)">Contact #</th>
                    <th onclick="sortTable(4)">Email</th>
                    <th onclick="sortTable(5)">Status</th>
                    <th onclick="sortTable(6)">Registration Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tfoot>
                  <tr>
                    <th onclick="sortTable(0)">Name</th>
                    <th onclick="sortTable(1)">Address</th>
                    <th onclick="sortTable(2)">Gender</th>
                    <th onclick="sortTable(3)">Contact #</th>
                    <th onclick="sortTable(4)">Email</th>
                    <th onclick="sortTable(5)">Status</th>
                    <th onclick="sortTable(6)">Registration Date</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
                <tbody>

                </tbody>
              </table>
              <!-- End Table with stripped rows -->

            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="updateStatusModalLabel">Update Status</h5>
          </div>
          <div class="modal-body">
            <form id="statusForm">
              <input type="hidden" id="itemEmail" name="itemEmail">
              <div class="form-group">
                <label for="statusSelect">Select Status</label>
                <select class="form-control" id="statusSelect" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> -->
            <button type="button" class="btn btn-primary" onclick="updateStatus()">Update Status</button>
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

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
      document.getElementById('registrationForm').addEventListener('submit', function(event) {
          event.preventDefault(); // Prevent the default form submission

          // Show loading alert
          Swal.fire({
              title: "Sending account credentials...",
              text: "Please wait while we are sending the account information.",
              icon: "info",
              showConfirmButton: false,
              allowOutsideClick: false,
              timer: 5000 // Auto-close after 5 seconds
          });

          const formData = new FormData(this); // Create FormData object from the form

          // Send form data to add_staff.php using Fetch API
          fetch('mysql/add_staff.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.text())
          .then(data => {
              // Close loading alert
              swal.close();

              // Check if the response indicates success
              if (data.includes('Success!')) {
                  // Show success alert
                  Swal.fire({
                      title: "Success!",
                      text: "New researcher added successfully!",
                      icon: "success"
                  }).then(() => {
                      // Refresh the page
                      location.reload();
                  });
              } else {
                  // Show error alert
                  Swal.fire({
                      icon: "error",
                      title: "Oops...",
                      text: data // Display error message from the server
                  });
              }
          })
          .catch(error => {
              console.error('Error:', error);
              swal.close();
              Swal.fire({
                  icon: "error",
                  title: "Error!",
                  text: "An unexpected error occurred. Please try again."
              });
          });
      });
  </script>

  <script>
      // Function to update user status
      function updateStatus() {
          const email = document.getElementById('itemEmail').value;
          const status = document.getElementById('statusSelect').value;

          // AJAX request to update the user status
          $.ajax({
              url: 'mysql/update_user_status.php', // Path to your PHP script
              type: 'POST',
              data: {
                  email: email,
                  status: status
              },
              success: function(response) {
                  const result = JSON.parse(response); // Parse the JSON response
                  if (result.success) {
                      // Show success alert if update was successful
                      Swal.fire({
                          icon: 'success',
                          title: 'Success',
                          text: 'Reasearcher account Status updated successfully',
                          confirmButtonText: 'OK'
                      }).then(() => {
                          fetchData(); // Call fetchData after alert is confirmed
                      });
                  } else {
                      // Show error alert if there was an issue
                      Swal.fire({
                          icon: 'error',
                          title: 'Error',
                          text: result.error || 'Failed to update status',
                          confirmButtonText: 'OK'
                      });
                  }
              },
              error: function(xhr, status, error) {
                  // Show alert in case of an AJAX error
                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'An error occurred while updating status',
                      confirmButtonText: 'OK'
                  });
              }
          });

          // Hide modal after sending request
          $('#updateStatusModal').modal('hide');
      }

      // Function to fetch and display data
      function fetchData() {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', 'mysql/get_staff.php', true);
          xhr.onload = function() {
              if (xhr.status >= 200 && xhr.status < 300) {
                  var data = JSON.parse(xhr.responseText);
                  displayData(data);
              } else {
                  console.error('Request failed. Returned status of ' + xhr.status);
              }
          };
          xhr.send();
      }

      // Function to display data in the table
      function displayData(data) {
          const tableBody = document.querySelector('#data-table tbody');
          tableBody.innerHTML = ''; // Clear existing rows

          data.forEach(item => {
              // Create a new row
              const row = document.createElement('tr');

              // Create and append cells for each property
              const nameCell = document.createElement('td');
              nameCell.textContent = item.full_name; // Adjust based on your data structure
              row.appendChild(nameCell);

              const addressCell = document.createElement('td');
              addressCell.textContent = item.add;
              row.appendChild(addressCell);

              const genderCell = document.createElement('td');
              genderCell.textContent = item.gender;
              row.appendChild(genderCell);

              const contactCell = document.createElement('td');
              contactCell.textContent = item.cont;
              row.appendChild(contactCell);

              const emailCell = document.createElement('td');
              emailCell.textContent = item.email;
              row.appendChild(emailCell);

              const statusCell = document.createElement('td');
              if (item.status === 'active') {
                  statusCell.textContent = 'Active';
              } else if (item.status === 'inactive') {
                  statusCell.textContent = 'Inactive';
              } else {
                  statusCell.textContent = 'Unknown'; // Optional: handle other statuses if needed
              }

              row.appendChild(statusCell);


              const registrationDateCell = document.createElement('td');
              registrationDateCell.textContent = item.registration_date;
              row.appendChild(registrationDateCell);

              // Create the action cell with the edit icon
              const actionCell = document.createElement('td');
              const editIcon = document.createElement('i');
              editIcon.className = 'bi bi-pencil-square';
              editIcon.style.cursor = 'pointer'; // Change cursor to pointer
              editIcon.onclick = function() {
                  // Show modal and populate email
                  document.getElementById('itemEmail').value = item.email; // Set email in hidden input
                  $('#updateStatusModal').modal('show'); // Show the modal using Bootstrap's modal method
              };

              // Create the trash icon
              const trashIcon = document.createElement('i');
              trashIcon.className = 'bi bi-trash'; // Use Bootstrap's trash icon class
              trashIcon.style.cursor = 'pointer'; // Change cursor to pointer
              trashIcon.style.marginLeft = '10px'; // Add some space between icons
              trashIcon.onclick = function() {
                  // Use SweetAlert2 for confirmation
                  Swal.fire({
                      title: 'Are you sure?',
                      text: 'You will not be able to revert this!',
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonText: 'Yes, delete it!',
                      cancelButtonText: 'No, keep it',
                      reverseButtons: true
                  }).then((result) => {
                      if (result.isConfirmed) {
                          // Perform the delete action if confirmed
                          deleteItem(item.id); // Call the deleteItem function (AJAX or other logic)
                          Swal.fire(
                              'Deleted!',
                              'The Researcher credentials has been deleted.',
                              'success'
                          );
                      } else if (result.isDismissed) {
                          Swal.fire(
                              'Cancelled',
                              'Researcher account is safe.',
                              'info'
                          );
                      }
                  });
              };

              // Append both icons to the action cell
              actionCell.appendChild(editIcon);
              actionCell.appendChild(trashIcon);

              // Append the action cell to the row
              row.appendChild(actionCell);

              // Append the new row to the table body
              tableBody.appendChild(row);

              function deleteItem(itemId) {
                  // Send an AJAX request to delete the item
                  $.ajax({
                      url: 'mysql/delete_staff.php', // Replace with your actual PHP script for deletion
                      type: 'POST',
                      data: { id: itemId },
                      success: function(response) {
                          if (response.status === 'success') {
                              Swal.fire(
                                  'Deleted!',
                                  'The Staff account has been deleted.',
                                  'success'
                              ).then((result) => {
                                  // If the "Okay" button is clicked, refresh the page
                                  if (result.isConfirmed) {
                                      location.reload(); // Refresh the page
                                  }
                              });
                          } else {
                              Swal.fire('Error!', 'Failed to delete item.', 'error');
                          }
                      },
                      error: function(xhr, status, error) {
                          console.error('Error:', error);
                          Swal.fire('Error!', 'An error occurred while deleting the item.', 'error');
                      }
                  });
              }
          });
      }

      // Function to filter table rows based on search input
      function filterTable() {
          var input = document.getElementById('search-box');
          var filter = input.value.toLowerCase();
          var table = document.getElementById('data-table');
          var rows = table.querySelectorAll('tbody tr');

          rows.forEach(function(row) {
              var cells = row.querySelectorAll('td');
              var found = Array.from(cells).some(function(cell) {
                  return cell.textContent.toLowerCase().includes(filter);
              });
              row.style.display = found ? '' : 'none';
          });
      }

      // Fetch and display data on page load
      document.addEventListener('DOMContentLoaded', function() {
          fetchData(); // Fetch data on page load

          // Add event listener for search box
          var searchBox = document.getElementById('search-box');
          searchBox.addEventListener('input', filterTable);
      });
  </script>


  <script>
      function sortTable(columnIndex) {
          const table = document.getElementById("data-table");
          const tbody = table.querySelector("tbody");
          const rowsArray = Array.from(tbody.rows);
          const isAscending = table.dataset.sortOrder === "asc";

          rowsArray.sort((rowA, rowB) => {
              const cellA = rowA.cells[columnIndex].innerText.trim();
              const cellB = rowB.cells[columnIndex].innerText.trim();
              const a = isNaN(cellA) ? cellA : Number(cellA);
              const b = isNaN(cellB) ? cellB : Number(cellB);
              const comparison = (a > b) ? 1 : (a < b) ? -1 : 0;
              return isAscending ? comparison : -comparison;
          });

          tbody.innerHTML = "";
          tbody.append(...rowsArray);

          table.dataset.sortOrder = isAscending ? "desc" : "asc";
      }

        // Function to handle phone number input
      function handlePhoneNumberInput(event) {
          let input = event.target;
          let value = input.value;

          // Remove non-numeric characters
          value = value.replace(/\D/g, '');

          // Limit the length to 11 digits
          if (value.length > 11) {
              value = value.substring(0, 11);
          }

          // Update the input field with the processed value
          input.value = value;
      }

      // Add event listener for phone number input
      document.getElementById('contact_number').addEventListener('input', handlePhoneNumberInput);
  </script>

</body>

</html>