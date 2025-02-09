<?php
// Database connection
require "conn.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to generate a random password
function generatePassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

// Get form data
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$last_name = $_POST['last_name'];
$address = $_POST['address'];
$gender = $_POST['gender'];
$contact_number = $_POST['contact_number'];
$email = $_POST['email'];
$user_type = "researcher";

// Generate random password
$password = generatePassword();
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$status = 'active';

// Handle file upload
$profilePicture = $_FILES['profilePicture'];
$targetDir = "../assets/img/profile/";
$fileName = basename($profilePicture["name"]);
$targetFilePath = $targetDir . $fileName;

// Check if the file is an image and move it to the target directory
if (in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
    if (move_uploaded_file($profilePicture["tmp_name"], $targetFilePath)) {
        // Send credentials to the user's email
        if (sendCredentials($email, $first_name, $middle_name, $last_name, $password)) {
            // Prepare the SQL query to save user details
            $sql = "INSERT INTO user (fname, mname, lname, address, gender, cont, email, password, status, user_type, profile) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Prepare statement
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Error preparing query: " . $conn->error);
            }

            // Bind parameters
            $stmt->bind_param("sssssssssss", $first_name, $middle_name, $last_name, $address, $gender, $contact_number, $email, $hashed_password, $status, $user_type, $fileName);

            // Execute the statement
            if ($stmt->execute()) {
                // echo '<script>
                //         window.location.href = "../pages-staff-management.php";
                //       </script>';
                echo 'Success!';
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error sending email. User not added to the database.";
        }
    } else {
        echo "Error moving the uploaded file.";
    }
} else {
    echo "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
}

// Close the connection
$conn->close();

// Function to send credentials email
function sendCredentials($email, $first_name, $middle_name, $last_name, $password) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'catalanwilfredo97@gmail.com'; // SMTP username
        $mail->Password = 'sykmmtpojmudqbik'; // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('mr.daotz97@gmail.com', 'IoT Mushroom Automation');
        $mail->addAddress($email, "$first_name $middle_name $last_name");

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Account Credentials';
        $mail->Body = "
        Good day Mr/Mrs. $first_name $middle_name $last_name,<br><br>
        Your account has been created successfully. Here are your credentials:<br>
        <strong>Email:</strong> $email<br>
        <strong>Password:</strong> $password<br><br>
        <p style='font-size: 14px; color: #666;'>Please keep your credentials confidential.</p>";

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Email not sent
    }
}
?>
