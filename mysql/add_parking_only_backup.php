<?php
// Include the database connection
include 'conn.php';

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $vehicleType = $data['vehicleType'];
    $makeModel = $data['makeModel'];
    $parkingLicense = $data['parkingLicense'];
    $color = $data['color'];
    $dateIn = $data['dateIn'];
    $timeIn = $data['timeIn'];
    $paymentMethod = $data['paymentMethod'];
    $amount = $data['amount'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO parking_only (vehicle_type, make_model, license_num, color, date_in, time_in, payment_type, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $vehicleType, $makeModel, $parkingLicense, $color, $dateIn, $timeIn, $paymentMethod, $amount);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert data.']);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No data received.']);
}
?>
