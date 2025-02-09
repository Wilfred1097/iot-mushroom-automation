<?php
session_start();
require 'conn.php'; // Adjust path to conn.php if necessary


// Retrieve and sanitize form inputs
$firstName = htmlspecialchars($_POST['first_name'] ?? '');
$middleName = htmlspecialchars($_POST['middle_name'] ?? '');
$lastName = htmlspecialchars($_POST['last_name'] ?? '');
$address = htmlspecialchars($_POST['address'] ?? '');
$gender = htmlspecialchars($_POST['gender'] ?? '');
$contactNumber = htmlspecialchars($_POST['contact_number'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$password = htmlspecialchars($_POST['password'] ?? '');
$confirmPassword = htmlspecialchars($_POST['confirmpassword'] ?? '');
$otp = htmlspecialchars($_POST['otp'] ?? '');

// Validate required fields
$errors = [];
if (empty($firstName)) $errors[] = "First Name is required.";
if (empty($lastName)) $errors[] = "Last Name is required.";
if (empty($address)) $errors[] = "Address is required.";
if (empty($gender)) $errors[] = "Gender is required.";
if (empty($contactNumber) || strlen($contactNumber) !== 11) $errors[] = "Contact Number must be exactly 11 digits.";
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid Email is required.";
if (empty($password)) $errors[] = "Password is required.";
if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";

// Handle errors
if (!empty($errors)) {
    echo "Errors:<br>";
    foreach ($errors as $error) {
        echo "- " . $error . "<br>";
    }
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL query
$sql = "INSERT INTO user (fname, mname, lname, address, gender, cont, email, password, otp, status, user_type, profile)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare SQL statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

// Execute SQL statement with form data
$status = 'pending'; // Default status
$user_type = 'staff';
$profile = 'admin.jpg';
$stmt->bind_param("ssssssssssss", $firstName, $middleName, $lastName, $address, $gender, $contactNumber, $email, $hashedPassword, $otp, $status, $user_type, $profile);
// Store email in session
$_SESSION['user_email'] = $email;
$_SESSION['first_name'] = $firstName;
$_SESSION['middle_name'] = $middleName;
$_SESSION['last_name'] = $lastName;
$_SESSION['user_otp'] = $otp;

// Execute the statement
if ($stmt->execute()) {
    // Redirect to confirmation page
    echo '<script>
            window.location.href = "../pages-confirm-otp.php";
          </script>';
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
