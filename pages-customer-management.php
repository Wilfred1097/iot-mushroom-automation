<?php
session_start();
require './mysql/conn.php'; // Adjust path to conn.php if necessary

// Check if the 'SmartSpot' cookie exists
if (!isset($_COOKIE['SmartSpot'])) {
    // Redirect to login page if the cookie does not exist
    header("Location: pages-login.php");
    exit;
}

// Retrieve the email from the cookie
$user_data = json_decode($_COOKIE['SmartSpot'], true);
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
    setcookie('SmartSpot', '', time() - 3600, '/'); // Set expiration time to the past to delete

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

  <title>SmartSpot- Customer Management</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/ss-favicon.png" rel="icon">
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

  <style>
    th {
            cursor: pointer;
        }
  </style>


  <!-- =======================================================
  * Template Name: NiceAdmin
  * Updated: May 30 2023 with Bootstrap v5.3.0
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/ss.png" alt="">
        <span class="d-none d-lg-block">SmartSpot</span>
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
        <a class="nav-link collapsed" href="pages-parking-management.php">
          <i class="bi bi-toggles"></i>
          <span>Device Control</span>
        </a>
      </li>

      <?php if ($user_type === 'admin'): ?>
      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-staff-management.php">
          <i class="bi bi-person"></i>
          <span>Staff Management</span>
        </a>
      </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link " href="pages-customer-management.php">
          <i class="bi bi-person"></i>
          <span>Customer Management</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages_livefeed.php">
          <i class="bi bi-camera-video"></i>
          <span>Live Feed</span>
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

          <div class="col-12">
            <div class="card recent-sales overflow-auto">

              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Customer Management</h5>
                <!-- Search box -->
                <input type="text" id="search-box" class="form-control" placeholder="Search..." style="max-width: 400px;">
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
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <!-- <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>NiceAdmin</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
    </div>
  </footer> --><!-- End Footer -->

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

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to fetch and display data
        function fetchData() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'mysql/get_customers.php', true);
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
            var table = document.getElementById('data-table');
            var tbody = table.querySelector('tbody');
            var headers = Object.keys(data[0] || {});
            
            // Clear existing table content
            tbody.innerHTML = '';

            // Create table rows
            data.forEach(function(row) {
                var tr = document.createElement('tr');
                headers.forEach(function(header) {
                    var td = document.createElement('td');
                    td.textContent = row[header];
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
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
        fetchData();

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
</script>

</body>

</html>