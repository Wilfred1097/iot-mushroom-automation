<?php
// Database connection details
require_once 'conn.php';

// Retrieve data from the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Ensure id is an integer
$temperature = isset($_GET['temperature']) ? floatval($_GET['temperature']) : 0.0; // Convert to float
$ph_level = isset($_GET['ph_level']) ? floatval($_GET['ph_level']) : 0.0; // Convert to float

// Ensure ID is valid
if ($id <= 0) {
    die("Invalid ID.");
}

// Prepare the SQL query
$sql = "UPDATE `overnight_parking` SET `temperature`=?, `ph_level`=? WHERE `id`=?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind parameters
    $stmt->bind_param("ddi", $temperature, $ph_level, $id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Record updated successfully.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

// Close the connection
$conn->close();
?>
