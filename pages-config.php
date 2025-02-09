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
$stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        $customer_fname = $customer['fname'];
        $customer_mname = $customer['mname'];
        $customer_lname = $customer['lname'];

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

  <title>Mushroom Automation - Configuration</title>
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
              <span><?php echo htmlspecialchars($customer['user_type']); ?></span>
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

      <?php if ($user_type === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link collapsed" href="pages-staff-management.php">
            <i class="bi bi-person"></i>
            <span>Researcher Management</span>
          </a>
        </li>

      <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="pages-customer-management.php">
          <i class="bi bi-person"></i>
          <span>Customer Management</span>
        </a>
      </li> -->
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link " href="pages-config.php">
          <i class="bi bi-gear"></i>
          <span>Configuration</span>
        </a>
      </li>

    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Profile</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item">Users</li>
          <li class="breadcrumb-item active">Profile</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <div class="col-xl-4">

          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

              <img src="assets/img/profile/<?php echo $customer['profile']?>" alt="Profile" class="rounded-circle">
              <h2><?php echo htmlspecialchars($customer_fname) . ' ' . htmlspecialchars($customer_mname) . ' ' . htmlspecialchars($customer_lname); ?></h2>
              <h3><?php echo $customer['user_type']?></h3>
              </div>
            </div>
          </div>

        </div>

        <div class="col-xl-8">

          <div class="card">
            <div class="card-body pt-3">
              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">

                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                </li>

                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
                </li>

              </ul>
              <div class="tab-content pt-2">

                <div class="tab-pane fade show active profile-overview" id="profile-overview">

                  <h5 class="card-title">Profile Details</h5>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                    <div class="col-lg-9 col-md-8"><?php echo htmlspecialchars($customer_fname) . ' ' . htmlspecialchars($customer_mname) . ' ' . htmlspecialchars($customer_lname); ?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Position</div>
                    <div class="col-lg-9 col-md-8"><?php echo $customer['user_type']?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Address</div>
                    <div class="col-lg-9 col-md-8"><?php echo $customer['address']?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Gender</div>
                    <div class="col-lg-9 col-md-8"><?php echo $customer['gender']?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Phone</div>
                    <div class="col-lg-9 col-md-8"><?php echo $customer['cont']?></div>
                  </div>

                  <div class="row">
                    <div class="col-lg-3 col-md-4 label">Email</div>
                    <div class="col-lg-9 col-md-8"><?php echo $customer['email']?></div>
                  </div>

                </div>

                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                  <!-- Profile Edit Form -->
                  <form id="edit-profile">
                    <input type="hidden" id="Id" name="Id" value="<?php echo $customer['id']?>">

                    <div class="row mb-3">
                      <label for="Fname" class="col-md-4 col-lg-3 col-form-label">First Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="fname" type="text" class="form-control" id="Fname" value="<?php echo $customer['fname']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Mname" class="col-md-4 col-lg-3 col-form-label">Middle Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="mname" type="text" class="form-control" id="Mname" value="<?php echo $customer['mname']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Address" class="col-md-4 col-lg-3 col-form-label">Last Name</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="lname" type="text" class="form-control" id="Lname" value="<?php echo $customer['lname']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="address" type="text" class="form-control" id="Address" value="<?php echo $customer['address']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                        <label for="Gender" class="col-md-4 col-lg-3 col-form-label">Gender</label>
                        <div class="col-md-8 col-lg-9">
                            <select name="gender" class="form-control" id="Gender">
                                <option value="Male" <?php echo ($customer['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($customer['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>
                    </div>


                    <div class="row mb-3">
                      <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="phone" type="text" class="form-control" id="Phone" value="<?php echo $customer['cont']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="email" class="form-control" id="Email" value="<?php echo $customer['email']?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                        <label for="ProfilePicture" class="col-md-4 col-lg-3 col-form-label">Profile Picture</label>
                        <div class="col-md-8 col-lg-9">
                            <input type="file" class="form-control" id="ProfilePicture" name="profilePicture" accept=".png, .jpg, .jpeg">
                            <small class="form-text text-muted">Accepted formats: .png, .jpg, .jpeg</small>
                        </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form><!-- End Profile Edit Form -->

                </div>

                <div class="tab-pane fade" id="profile-settings">
                  <!-- Settings Form -->
                  <form id="parking-fee">
                    <div class="row mb-3">
                      <label for="2-wheeler-parking" class="col-md-4 col-lg-3 col-form-label">2 Wheeler Parking Only</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="2-wheeler-parking" type="number" class="form-control" id="2-wheeler-parking" value="<?php echo $config['2_wheeler_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="3-wheeler-parking" class="col-md-4 col-lg-3 col-form-label">4 Wheeler Parking Only</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="3-wheeler-parking" type="number" class="form-control" id="3-wheeler-parking" value="<?php echo $config['3_wheeler_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="4-wheeler-parking" class="col-md-4 col-lg-3 col-form-label">3 Wheeler Parking Only</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="4-wheeler-parking" type="number" class="form-control" id="4-wheeler-parking" value="<?php echo $config['4_wheeler_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="2-wheeler-overnight" class="col-md-4 col-lg-3 col-form-label">2 Wheeler Overnight Parking</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="2-wheeler-overnight" type="number" class="form-control" id="2-wheeler-overnight" value="<?php echo $config['2_wheeler_overnight_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="3-wheeler-overnight" class="col-md-4 col-lg-3 col-form-label">3 Wheeler Overnight Parking</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="3-wheeler-overnight" type="number" class="form-control" id="3-wheeler-overnight" value="<?php echo $config['3_wheeler_overnight_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="4-wheeler-overnight" class="col-md-4 col-lg-3 col-form-label">4 Wheeler Overnight Parking</label>
                      <div class="col-md-8 col-lg-9">
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input name="4-wheeler-overnight" type="number" class="form-control" id="4-wheeler-overnight" value="<?php echo $config['4_wheeler_overnight_rate']?>">
                        </div>
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Update Prices</button>
                    </div>
                  </form><!-- End settings Form -->

                </div>

                <div class="tab-pane fade pt-3" id="profile-change-password">
                  <!-- Change Password Form -->
                  <form id="change-password">
                    <input type="hidden" name="email" id="email" value="<?php echo $user_email?>">

                    <div class="row mb-3">
                      <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="password" id="currentPassword" type="password" class="form-control" id="currentPassword">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="newpassword" id="newPassword" type="password" class="form-control" id="newPassword">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                      <div class="col-md-8 col-lg-9">
                        <input name="renewpassword" id="renewPassword" type="password" class="form-control" id="renewPassword">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                  </form><!-- End Change Password Form -->

                </div>

              </div><!-- End Bordered Tabs -->

            </div>
          </div>

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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>


  <script>
      document.getElementById('vehicle-type').addEventListener('submit', function(event) {
      event.preventDefault(); // Prevent the default form submission

      // Gather form data
      const vehicleType = document.getElementById('vehicleType').value;
      const vehicleTypeAmount = document.getElementById('vehicleTypeAmount').value;
      const vehicleTypeOvertime = document.getElementById('vehicleTypeOvertime').value;

      // Send AJAX request to PHP script
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'mysql/add_vehicle_type.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
          const response = JSON.parse(xhr.responseText);
          if (response.status === 'success') {
              Swal.fire({
                  title: 'Success!',
                  text: response.message,
                  icon: 'success',
                  confirmButtonText: 'OK'
              }).then((result) => {
                  if (result.isConfirmed) { // Clear the input fields
                      // Clear the input fields
                      document.getElementById('vehicleType').value = '';
                      document.getElementById('vehicleTypeAmount').value = '';
                      document.getElementById('vehicleTypeOvertime').value = '';
                      fetchParkingData(); // Refresh table data
                  }
              });
          } else {
              Swal.fire({
                  title: 'Error!',
                  text: response.message,
                  icon: 'error',
                  confirmButtonText: 'OK'
              });
          }
      };

      // Send the data
      xhr.send(`vehicleType=${encodeURIComponent(vehicleType)}&vehicleTypeAmount=${encodeURIComponent(vehicleTypeAmount)}&vehicleTypeOvertime=${encodeURIComponent(vehicleTypeOvertime)}`);
  });
</script>

  <script>
        document.getElementById('edit-profile').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Create a FormData object to gather all form data
            const formData = new FormData();

            // Append each form field to the FormData object
            formData.append('Id', document.getElementById('Id').value);
            formData.append('fname', document.getElementById('Fname').value);
            formData.append('mname', document.getElementById('Mname').value);
            formData.append('lname', document.getElementById('Lname').value);
            formData.append('address', document.getElementById('Address').value);
            formData.append('gender', document.getElementById('Gender').value);
            formData.append('phone', document.getElementById('Phone').value);
            formData.append('email', document.getElementById('Email').value);

            // Include the Profile Picture file if one is selected
            const profilePictureInput = document.getElementById('ProfilePicture');
            if (profilePictureInput.files.length > 0) {
                formData.append('profilePicture', profilePictureInput.files[0]);
            }

            // Send AJAX request to PHP script
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'mysql/update_user_info.php', true);
            xhr.onload = function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            };

            // Send the FormData object
            xhr.send(formData);
        });
  </script>

  <script>
    // Add event listener to the form
    document.getElementById('parking-fee').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Gather form data
        const two_parking_only = document.getElementById('2-wheeler-parking').value;
        const three_parking_only = document.getElementById('3-wheeler-parking').value;
        const four_parking_only = document.getElementById('4-wheeler-parking').value;
        const two_overnight_parking = document.getElementById('2-wheeler-overnight').value;
        const three_overnight_parking = document.getElementById('3-wheeler-overnight').value;
        const four_overnight_parking = document.getElementById('4-wheeler-overnight').value;

        // Send data to the PHP script using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/update_parking_rates.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                const response = JSON.parse(xhr.responseText);
                Swal.fire({
                    title: response.status === 'success' ? 'Success!' : 'Error',
                    text: response.message,
                    icon: response.status,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Clear the input fields if the update was successful
                        if (response.status === 'success') {
                            // document.getElementById('2-wheeler-parking').value = '';
                            // document.getElementById('3-wheeler-parking').value = '';
                            // document.getElementById('4-wheeler-parking').value = '';
                            // document.getElementById('2-wheeler-overnight').value = '';
                            // document.getElementById('3-wheeler-overnight').value = '';
                            // document.getElementById('4-wheeler-overnight').value = '';
                            location.reload();
                        }
                    }
                });
            }
        };

        // Send the request with the form data
        xhr.send(`two_parking_only=${encodeURIComponent(two_parking_only)}&three_parking_only=${encodeURIComponent(three_parking_only)}&four_parking_only=${encodeURIComponent(four_parking_only)}&two_overnight_parking=${encodeURIComponent(two_overnight_parking)}&three_overnight_parking=${encodeURIComponent(three_overnight_parking)}&four_overnight_parking=${encodeURIComponent(four_overnight_parking)}`);
    });
</script>

  <script>
    // Add event listener to the form
    document.getElementById('change-password').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Gather form data
        const email = document.getElementById('email').value;
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const renewPassword = document.getElementById('renewPassword').value;

        // Validate that the current password is provided
        if (currentPassword === '') {
            Swal.fire({
                title: 'Error',
                text: 'Please enter your current password.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return; // Stop further execution if validation fails
        }

        // Validate the new password length
        if (newPassword.length < 8) {
            Swal.fire({
                title: 'Error',
                text: 'New password must be at least 8 characters long.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return; // Stop further execution if validation fails
        }

        // Validate that the new password matches the confirmed password
        if (newPassword !== renewPassword) {
            Swal.fire({
                title: 'Error',
                text: 'Passwords do not match. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return; // Stop further execution if validation fails
        }

        // Send data to the PHP script using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'mysql/update_password.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear the input fields
                            // document.getElementById('currentPassword').value = '';
                            // document.getElementById('newPassword').value = '';
                            // document.getElementById('renewPassword').value = '';
                          location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        };

        // Send the request with the form data
        xhr.send(`email=${encodeURIComponent(email)}&currentPassword=${encodeURIComponent(currentPassword)}&newPassword=${encodeURIComponent(newPassword)}`);
    });
</script>

<!-- <button class="btn btn-edit" title="Edit" onclick="openEditModal(${row.id}, '${row.bin_num}', '${row.token}')">
    <i class="bi bi-pencil-square" style="color: #0000FF"></i> 
</button>  -->

<script>
    function fetchParkingData() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'mysql/get_vehicle_category.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                const data = JSON.parse(this.responseText);
                let tbody = document.querySelector('#vehicle-type-table tbody');
                tbody.innerHTML = '';

                data.forEach(row => {
                    let tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.vehicle_type} Wheeled</td>
                        <td>${row.amount} pesos</td>
                        <td>${row.overtime} pesos</td>
                        <td>
                             
                            <button class="btn btn-trash" title="Delete" onclick="openDeleteModal(${row.id})">
                                <i class="bi bi-trash" style="color: #FF0000"></i> 
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        };
        xhr.send();
    }

    function openDeleteModal(id) {
        Swal.fire({
            title: 'Do you want to delete this vehicle category?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // AJAX call to delete the vehicle category
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'mysql/delete_vehicle_category.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        const response = JSON.parse(this.responseText);
                        if (response.status === 'success') {
                            Swal.fire('Deleted!', response.message, 'success');
                            fetchParkingData(); // Refresh table after deletion
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                };
                xhr.send('id=' + id); // Send the ID to delete
            }
        });
    }

    window.onload = function() {
        fetchParkingData();
    };
</script>


</body>

</html>