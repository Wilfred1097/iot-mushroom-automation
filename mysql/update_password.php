<?php
// update_password.php

include_once 'conn.php'; // Include the database connection file

// Get POST data
$email = $_POST['email'];
$currentPassword = $_POST['currentPassword'];
$newPassword = $_POST['newPassword'];

// Validate the email and password (you should implement your own validation logic here)

// Fetch the user by email
$stmt = $conn->prepare("SELECT password FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($currentPassword, $user['password'])) {
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the password in the database
    $updateStmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);
    $updateStmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
}

// Close the database connection
$conn->close();
?>
