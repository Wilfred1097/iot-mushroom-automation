<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>SmartSpot - Customer Registration</title>
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

  <!-- Include SweetAlert CSS and JS -->
  <link rel="stylesheet" href="assets/plugins/sweetalert2/sweetalert2.min.css">
  <script src="assets/plugins/sweetalert2/sweetalert2.js"></script>

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<!-- <body style="background-image: url('./assets/img/parking1.jpg'); background-repeat: no-repeat; background-size: cover;"> -->
<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-6 d-flex flex-column align-items-center justify-content-center">
              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                    <p class="text-center small">Enter your personal details to create account</p>
                  </div>

                  <form class="row g-3" id="registrationForm" novalidate method="POST" action="mysql/register.php">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="first_name" class="form-control" id="first_name" placeholder="First Name">
                            <label for="first_name">First Name</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="middle_name" class="form-control" id="middle_name" placeholder="Middle Name">
                            <label for="middle_name">Middle Name</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="last_name" class="form-control" id="last_name" placeholder="Last Name">
                            <label for="last_name">Last Name</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="address" class="form-control" id="address" placeholder="Address">
                            <label for="address">Address</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <select name="gender" class="form-select" id="gender" aria-label="Gender">
                                <option value="" disabled selected>Select your gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <label for="gender">Select your gender</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="tel" name="contact_number" class="form-control" id="contact_number" placeholder="Contact Number">
                            <label for="contact_number">Contact Number</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Email">
                            <label for="email">Email</label>
                            <div id="emailTooltip" class="tooltip"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                            <label for="password">Password</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="password" name="confirmpassword" class="form-control" id="confirmpassword" placeholder="Confirm Password">
                            <label for="confirmpassword">Confirm password</label>
                            <div id="passwordTooltip" class="tooltip"></div>
                        </div>
                    </div>

                    <input type="hidden" name="otp" id="otp">

                    <div class="col-12">
                        <button class="btn w-100" style="background-color: #6600FF; color: white;" type="submit">Create Account</button>
                    </div>

                    <div class="col-12 text-center">
                        <p class="small mb-0">Already have an account? <a href="pages-login.php">Log in</a></p>
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

<script>
  // Function to check if email exists in the database
  async function checkEmailExistence(email) {
      try {
          const response = await fetch(`mysql/check-email.php?email=${encodeURIComponent(email)}`);
          const result = await response.json();

          if (result.error) {
              console.error('Error:', result.error);
              return false;
          }

          return result.exists;
      } catch (error) {
          console.error('Error:', error);
          return false;
      }
  }

  // Function to handle email input
  document.getElementById('email').addEventListener('input', async function () {
      const emailInput = this;
      const email = emailInput.value;
      const tooltip = document.getElementById('emailTooltip');

      if (email) {
          const exists = await checkEmailExistence(email);

          if (exists) {
              emailInput.style.borderColor = 'red'; // Set border color to red if email exists
              emailInput.setCustomValidity('This email is already registered.');
              tooltip.textContent = 'This email is already registered'; // Tooltip message
              tooltip.classList.add('show');
          } else {
              emailInput.style.borderColor = 'green'; // Set border color to green if email does not exist
              emailInput.setCustomValidity('Looks good!');
              tooltip.textContent = 'Looks good!';
              tooltip.classList.add('show'); // Show tooltip
          }
      } else {
          emailInput.style.borderColor = ''; // Reset border color if input is empty
          emailInput.setCustomValidity('');
          tooltip.classList.remove('show'); // Hide tooltip
      }
  });

  // Function to handle password and confirmPassword input
  function handlePasswordAndConfirmPasswordInput() {
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmpassword');
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Update border color based on password length
      if (password.length >= 8) {
          passwordInput.style.borderColor = 'green';
      } else {
          passwordInput.style.borderColor = 'red';
      }

      // Update confirmPassword border color based on match with password
      if (password === confirmPassword) {
          confirmPasswordInput.style.borderColor = 'green';
      } else {
          confirmPasswordInput.style.borderColor = 'red';
      }
  }

  // Add event listeners for password and confirmPassword input
  document.getElementById('password').addEventListener('input', handlePasswordAndConfirmPasswordInput);
  document.getElementById('confirmpassword').addEventListener('input', handlePasswordAndConfirmPasswordInput);

  // Function to generate a random OTP
  function generateOTP() {
      const otp = Math.floor(100000 + Math.random() * 900000); // Generates a 6-digit OTP
      document.getElementById('otp').value = otp; // Set OTP in the hidden input field
      return otp;
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

  // Function to validate the entire form including async email check
  async function validateForm() {
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmpassword');
      const email = emailInput.value;
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      const emailTooltip = document.getElementById('emailTooltip');
      const passwordTooltip = document.getElementById('passwordTooltip');

      let isValid = true;

      // Validate email
      if (email) {
          const exists = await checkEmailExistence(email);

          if (exists) {
              emailInput.style.borderColor = 'red'; // Set border color to red if email exists
              emailInput.setCustomValidity('This email is already registered.');
              emailTooltip.textContent = 'This email is already registered'; // Tooltip message
              emailTooltip.classList.add('show');
              isValid = false;
          } else {
              emailInput.style.borderColor = 'green'; // Set border color to green if email does not exist
              emailInput.setCustomValidity('Looks good!');
              emailTooltip.textContent = 'Looks good!';
              emailTooltip.classList.remove('show'); // Hide tooltip
          }
      } else {
          emailInput.style.borderColor = ''; // Reset border color if input is empty
          emailInput.setCustomValidity('');
          emailTooltip.classList.remove('show'); // Hide tooltip
      }

      // Validate password length
      if (password.length >= 8) {
          passwordInput.style.borderColor = 'green';
      } else {
          passwordInput.style.borderColor = 'red';
          isValid = false;
      }

      // Validate password match
      if (password === confirmPassword) {
          confirmPasswordInput.style.borderColor = 'green';
      } else {
          confirmPasswordInput.style.borderColor = 'red';
          isValid = false;
      }

      return isValid;
  }

  // Function to send form data and OTP to the server
  async function sendOtpAndSubmitForm(formData) {
      // Show a Swal loading animation
      Swal.fire({
          title: 'Sending OTP',
          text: 'Please wait...',
          allowOutsideClick: false,
          didOpen: () => {
              Swal.showLoading(); // Show loading animation
          }
      });

      try {
          const response = await fetch('mysql/send-otp.php', {
              method: 'POST',
              body: formData
          });

          const result = await response.text(); // Expecting plain text response

          if (result.includes('OTP has been sent')) {
              // OTP has been sent, show success message and submit the form
              Swal.fire({
                  title: "Success",
                  text: "Account registration submitted successfully!",
                  icon: "success",
                  confirmButtonText: 'OK'
              }).then(() => {
                  document.getElementById('registrationForm').submit();
              });
          } else {
              Swal.fire({
                  title: "Error",
                  text: "Failed to send OTP. Please try again.",
                  icon: "error"
              });
          }
      } catch (error) {
          console.error('Error:', error);
          Swal.fire({
              title: "Error",
              text: "An unexpected error occurred. Please try again.",
              icon: "error"
          });
      }
  }


  // Add event listener for form submission
  document.getElementById('registrationForm').addEventListener('submit', async function(event) {
      // Prevent default form submission
      event.preventDefault();

      // Validate form
      const isValid = await validateForm();

      if (isValid) {
          // Gather form data
          const formData = new FormData(event.target);
          // Generate OTP
          const otp = generateOTP();
          formData.append('otp', otp);

          // Send OTP and then submit the form
          sendOtpAndSubmitForm(formData);
      } else {
          Swal.fire({
              title: "Oops...",
              text: "Please correct the errors in the form before submitting.",
              icon: "error"
          });
      }
  });
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
  <!-- SweetAlert library -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>