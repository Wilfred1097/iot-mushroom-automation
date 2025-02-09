<?php
require './mysql/conn.php'; // Adjust path to conn.php if necessary

// Retrieve the email from the query parameters
$email = $_GET['email'] ?? '';

// Check if the email is provided
if (empty($email)) {
    header("Location: pages-error-404.php");
    exit;
}

// Initialize error message
$errorMessage = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = htmlspecialchars($_POST['otp'] ?? '');

    // Validate OTP input
    if (empty($enteredOtp)) {
        echo '<p class="text-danger">Please enter the OTP.</p>';
    } else {
        // Prepare the SQL query to check the OTP
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND otp = ?");
        if ($stmt === false) {
            die("Error preparing query: " . $conn->error);
        }

        // Bind parameters and execute the statement
        $stmt->bind_param("ss", $email, $enteredOtp);
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set from the statement

        $customer = $result->fetch_assoc();

        if ($customer) {
            // Construct the URL with query parameters
            $redirectUrl = 'pages-new-password.php?email=' . urlencode($email);

            // Redirect to the constructed URL
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            $errorMessage = 'Invalid OTP. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SmartSpot - Confirm OTP</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
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

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">CONFIRM YOUR OTP</h5>
                    <p class="text-center small">Enter the OTP that we sent to your Email</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate method="POST">

                    <div class="col-12">
                      <label for="otp" class="form-label">OTP</label>
                      <div class="input-group has-validation">
                        <input type="number" name="otp" class="form-control" id="otp" required placeholder="Enter OTP here">
                        <div class="invalid-feedback">Please enter your OTP.</div>
                      </div>
                      <?php if ($errorMessage): ?>
                          <p class="text-danger text-center mt-3 mb-1"><?php echo $errorMessage; ?></p>
                      <?php endif; ?>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-success w-100" type="submit">Confirm</button>
                    </div>
                   <!--  <div class="col-12 text-center">
                      <p class="small mb-0">Mispelled Email address? <a href="../pages-register.php">Reset OTP here</a></p>
                    </div> -->
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

  <script>
        // Function to handle phone number input
    function handleOTPInput(event) {
        let input = event.target;
        let value = input.value;

        // Remove non-numeric characters
        value = value.replace(/\D/g, '');

        // Limit the length to 11 digits
        if (value.length > 6) {
            value = value.substring(0, 6);
        }

        // Update the input field with the processed value
        input.value = value;
    }

    // Add event listener for phone number input
    document.getElementById('otp').addEventListener('input', handleOTPInput);
  </script>
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

</body>

</html>
