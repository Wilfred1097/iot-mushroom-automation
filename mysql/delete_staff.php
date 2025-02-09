<?php
include_once 'conn.php'; // Include the database connection file

// Set the response type to JSON to prevent issues with content type
header('Content-Type: application/json');

// Check if the ID is provided
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare the DELETE SQL query
    $sql = "DELETE FROM `user` WHERE `id` = ?";

    // Prepare and bind the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id); // 'i' denotes the parameter is an integer

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error']);
    }

} else {
    echo json_encode(['status' => 'error']);
}

// Close the database connection
$conn->close();
?>
