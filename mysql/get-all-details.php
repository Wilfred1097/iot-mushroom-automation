<?php
// Include database connection file
include 'conn.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get the email from query parameters
$email = $_GET['email'] ?? '';

// Sanitize input to avoid SQL injection
$email = filter_var($email, FILTER_SANITIZE_EMAIL);

// Prepare the SQL query
$query = 'SELECT * FROM customer WHERE email = ?';

// Initialize response array
$response = [];

// Check if email is not empty
if (!empty($email)) {
    try {
        // Prepare and execute the statement
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);

        // Fetch the customer details
        $customerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if any record was found
        if ($customerDetails) {
            $response = $customerDetails;
        } else {
            $response = ['error' => 'No customer found with this email.'];
        }
    } catch (PDOException $e) {
        // Handle SQL errors
        $response = ['error' => 'Database error: ' . $e->getMessage()];
    }
} else {
    $response = ['error' => 'No email provided.'];
}

// Return the JSON response
echo json_encode($response);

// Close database connection
$pdo = null;
?>
