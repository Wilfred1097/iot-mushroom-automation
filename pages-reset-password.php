<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SmartSpot - Confirm OTP</title>
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

  <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
                    <h5 class="card-title text-center pb-0 fs-4">RESET YOUR PASSWORD</h5>
                    <p class="text-center small">Enter the email that you used during registration</p>
                  </div>

                  <form class="row g-3" id="emailForm" novalidate method="POST">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
                            <label for="email">Email</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn w-100 btn-success" type="submit">Send OTP</button>
                    </div>
                    <div class="col-12 text-center">
                        <!-- <p class="small mb-0">Don't have an account? <a href="pages-register.php">Create an account</a></p><hr> -->
                        <p class="small mb-0">Go back to <a class="text-success" href="pages-login.php">Login page</a></p>
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

  <!-- jQuery (necessary for AJAX and SweetAlert) -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- SweetAlert JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
  <!-- Your custom script -->

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    $(document).ready(function() {
        $('#emailForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Get the email value
            var email = $('#email').val();

            $.ajax({
                url: 'mysql/check-email.php',
                type: 'GET', // Use GET since we're reading data
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        const { fname, mname, lname, email } = response.user;
                        const otp = Math.floor(100000 + Math.random() * 900000);

                        // Show a loading alert with a message
                        Swal.fire({
                            title: 'Sending OTP...',
                            text: `Sending OTP to your email (${email})`,
                            icon: 'info',
                            didOpen: () => {
                                Swal.showLoading();
                            },
                            allowOutsideClick: false
                        });

                        $.ajax({
                            url: 'mysql/resend-otp.php',
                            type: 'POST',
                            data: {
                                fname: fname,
                                mname: mname,
                                lname: lname,
                                email: email,
                                otp: otp
                            },
                            success: function(resendResponse) {
                                // Hide the loading alert
                                Swal.close();

                                // Parse JSON response
                                const response = JSON.parse(resendResponse);
                                
                                // Handle success or error based on response
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'OTP sent successfully!',
                                        text: 'A new OTP verification code was sent to your email address.',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            const redirectUrl = `pages-confirm-new-otp.php?email=${encodeURIComponent(email)}`;
                                            window.location.href = redirectUrl;
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                // Hide the loading alert
                                Swal.close();
                                console.error('AJAX Error:', status, error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'An error occurred while resending OTP',
                                    text: 'There was an error processing your request. Please try again later.',
                                });
                            }
                        });

                     }else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Email is not yet registered!',
                            text: 'You can proceed with registration.',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Do you want to proceed to registration?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes',
                                    cancelButtonText: 'No'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Redirect to the registration page
                                        window.location.href = 'pages-register.php';
                                    }
                                });
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error (check-email):', status, error);
                    Swal.fire({
                        icon: 'error',
                        title: 'An error occurred',
                        text: 'There was an error processing your request. Please try again later.',
                    });
                }
            });
        });
    });
</script>

</body>

</html>
