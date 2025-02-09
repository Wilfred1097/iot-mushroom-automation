<?php
// update_parking_rates.php

include_once 'conn.php'; // Include the database connection file

// Get POST data
$two_wheeler_rate = $_POST['two_parking_only'];
$three_wheeler_rate = $_POST['three_parking_only'];
$four_wheeler_rate = $_POST['four_parking_only'];
$two_wheeler_overnight_rate = $_POST['two_overnight_parking'];
$three_wheeler_overnight_rate = $_POST['three_overnight_parking'];
$four_wheeler_overnight_rate = $_POST['four_overnight_parking'];

// Update parking rates in the database
$stmt = $conn->prepare("UPDATE config SET 
    2_wheeler_rate = ?, 
    3_wheeler_rate = ?, 
    4_wheeler_rate = ?, 
    2_wheeler_overnight_rate = ?, 
    3_wheeler_overnight_rate = ?, 
    4_wheeler_overnight_rate = ? 
    WHERE id = 1"); // Assuming there's an ID for the config entry

$stmt->bind_param("ssssss", $two_wheeler_rate, $three_wheeler_rate, $four_wheeler_rate, $two_wheeler_overnight_rate, $three_wheeler_overnight_rate, $four_wheeler_overnight_rate);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Parking rates updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update parking rates.']);
}

// Close the database connection
$conn->close();
?>
