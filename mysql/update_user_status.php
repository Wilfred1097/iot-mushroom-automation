<?php
require_once 'conn.php'; // Include your database connection file

// Get the data sent from AJAX
$email = $_POST['email'];
$status = $_POST['status'];

// Prepare the SQL statement
$sql = "UPDATE `user` SET `status` = ? WHERE `email` = ?";

// Initialize a prepared statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $status, $email); // "ss" indicates two string parameters

// Execute the statement and check if the update was successful
if ($stmt->execute()) {
    // If successful, return a success response
    echo json_encode(['success' => true]);
} else {
    // If there was an error, return an error response
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
