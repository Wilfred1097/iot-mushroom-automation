<?php
include_once 'conn.php';

// Check if the request is an AJAX request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from AJAX request
    $vehicleType = $_POST['vehicleType'];
    $vehicleTypeAmount = $_POST['vehicleTypeAmount'];
    $vehicleTypeOvertime = $_POST['vehicleTypeOvertime'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO vehicle_category (vehicle_type, amount, overtime) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $vehicleType, $vehicleTypeAmount, $vehicleTypeOvertime);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle Type added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user details.']);
    }

    // Close the statemen
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
