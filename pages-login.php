<?php
require_once './mysql/check-cookies.php'; 

session_start();
require './mysql/conn.php'; // Adjust path to conn.php if necessary

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');

    // Validate required fields
    if (empty($email) || empty($password)) {
        $errorMessage = 'Email and Password are required.';
    } else {
        // Prepare SQL statement to select user by email
        $stmt = $conn->prepare("SELECT email, password, user_type, status FROM user WHERE email = ?");
        if ($stmt === false) {
            die("Error preparing query: " . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param("s", $email); // "s" indicates the type is string

        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set

        // Fetch the user data
        $user = $result->fetch_assoc();

        if ($user) {
            $user_type = $user['user_type'];

            // Check user type
            if ($user_type === 'admin') {
                // Admin: proceed directly to password verification
                if (password_verify($password, $user['password'])) {
                    // Set cookie for 1 hour
                    $user_data = json_encode(['email' => $email, 'user_type' => $user_type]);
                    setcookie('iot-mushroom', $user_data, time() + 1 * 3600, '/');

                    // Start session and store user information
                    $_SESSION['user_email'] = $email;

                    // Redirect to admin dashboard
                    header("Location: index.php"); // Assuming you have a separate dashboard for admin
                    exit;
                } else {
                    $errorMessage = 'Invalid email or password.';
                }
            } elseif ($user_type === 'researcher') {
                // Staff: check the status before password verification
                if ($user['status'] === 'active') {
                    // Status is active, proceed to password verification
                    if (password_verify($password, $user['password'])) {
                        // Set cookie for 1 hour
                        $user_data = json_encode(['email' => $email, 'user_type' => $user_type]);
                        setcookie('iot-mushroom', $user_data, time() + 1 * 3600, '/');

                        // Start session and store user information
                        $_SESSION['user_email'] = $email;

                        // Redirect to staff dashboard
                        header("Location: index.php"); // Assuming you have a separate dashboard for staff
                        exit;
                    } else {
                        $errorMessage = 'Invalid email or password.';
                    }
                } else {
                    $errorMessage = 'Account is inactive. Please contact support.';
                }
            } else {
                $errorMessage = 'Unknown user type.';
            }
        } else {
            $errorMessage = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Mushroom Automation - Login</title>
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="card mb-3">
<!-- jhcsc_logo -->
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <div class="row justify-content-center align-items-center">
                     <!--  <div  style="
                            display: inline-flex;
                            justify-content: center;
                            align-items: center;
                            width: 90px;
                            height: 90px;
                            border-radius: 50%;
                            background-color: rgba(173, 216, 230, 0.3); /* Light blue background */
                            border: 5px solid rgba(173, 216, 230, 0.6); /* Light blue border */
                        " class="mb-3">
                          <i class="bi bi-cpu" style="color: #007bff; font-size: 36px;"></i>
                        </div> -->
                        <img src="assets/img/jhcsc_logo.png" style="height: 120px; width:150px;" alt="">

                      <h6 class="text-center pb-0 fs-4 mt-2">JHCSC MUSHROOM AUTOMATION SYSTEM</h6>
                    </div>
                    <p class="text-center small">Enter credentials to access control panel</p>
                  </div>

                  <!-- <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">SmartSpot</h5>
                    <p class="text-center small">Login to Your Account</p>
                  </div> -->

                  <?php if (isset($errorMessage)): ?>
                    <p class="text-danger text-center"><?= htmlspecialchars($errorMessage); ?></p>
                  <?php endif; ?>

                  <form class="row g-3 needs-validation" novalidate method="POST">

                    <div class="col-md-12">
                      <div class="form-floating">
                        <input type="email" name="email" class="form-control" id="firstName" required placeholder="First Name">
                        <label for="firstName">Email</label>
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="form-floating">
                        <input type="password" name="password" class="form-control" id="yourPassword" required placeholder="First Name">
                        <label for="yourPassword">Password</label>
                        <i id="togglePassword" class="bi bi-eye" style="position: absolute; right: 10px; top: 38%; cursor: pointer;"></i>
                      </div>
                    </div>

                    <div class="col-12">
                      <button class="btn w-100 btn-success" type="submit">Login</button>
                    </div>
                    <div class="col-12 text-center">
                      <!-- <p class="small mb-0">Don't have an account? <a href="pages-register.php">Create an account</a></p><hr> -->
                      <p class="small mb-0">Forgot your password? <a href="pages-reset-password.php" class="text-success">Reset Here</a></p>
                    </div>
                  </form>

                </div>
              </div>

            </div>
          </div>
        </div>

      </section>

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

  <script>
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('yourPassword');

      togglePassword.addEventListener('click', function () {
          // Toggle the type attribute
          const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
          passwordInput.setAttribute('type', type);

          // Toggle the eye icon
          this.classList.toggle('bi-eye');
          this.classList.toggle('bi-eye-slash');
      });
  </script>

  <script>
  window.embeddedChatbotConfig = {
  chatbotId: "0pxMZ3nZU9eRHhGHWiOUi",
  domain: "www.chatbase.co"
  }
  </script>
  <script
  src="https://www.chatbase.co/embed.min.js"
  chatbotId="0pxMZ3nZU9eRHhGHWiOUi"
  domain="www.chatbase.co"
  defer>
  </script>

</body>

</html>
