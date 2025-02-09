<?php
// check-email.php
header('Content-Type: application/json');

// Include the database connection
require 'conn.php'; // This file should have the $conn variable

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['error' => 'Email is required.']);
    exit;
}

// Prepare and execute the query to fetch user details
$stmt = $conn->prepare('SELECT fname, mname, lname, email FROM user WHERE email = ?');

if ($stmt === false) {
    echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    exit;
}

// Bind parameters and execute the statement
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result(); // Get the result set from the statement

// Check if user details are found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Fetch user details
    // Return JSON response with user details
    echo json_encode(['exists' => true, 'user' => $user]);
} else {
    // Return JSON response indicating email does not exist
    echo json_encode(['exists' => false]);
}
?>
