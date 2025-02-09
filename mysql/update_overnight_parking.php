<?php
include_once 'conn.php'; // Include your database connection file

// Get the data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Extract values from the request
$id = $data['id'];
$owner = $data['owner'];
$dateOut = $data['date_out']; // Fix: Correct the key name
$timeOut = $data['time_out']; // Fix: Correct the key name
$paymentType = $data['payment_method'];
$amount = $data['amount'];
$duration = $data['duration'];

// Prepare the SQL statement
$sql = "UPDATE `parking_only` SET `vehicle_owner` = ?, `date_out` = ?, `Time_out` = ?, `payment_type` = ?, `amount` = ?, `parking_duration` = ? WHERE `id` = ?";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $owner, $dateOut, $timeOut, $paymentType, $amount, $duration, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Overtime Vehicle Released Successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to release vehicle']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
