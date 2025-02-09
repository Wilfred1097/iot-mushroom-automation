<?php
// Include the database connection
include 'conn.php';

// SQL query to get all records from the parking_only table
$query = "SELECT id, vehicle_owner, vehicle_type, make_model, license_num, color, date_in, Time_in, date_out, Time_out, payment_type, amount, TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', Time_in), CONCAT(date_out, ' ', Time_out)) DIV 60 AS parking_duration_hours, TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', Time_in), CONCAT(date_out, ' ', Time_out)) % 60 AS parking_duration_minutes, date_added FROM parking_only WHERE date_out != '' AND Time_out != '' ORDER BY id DESC";
$result = $conn->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return the data as a JSON response
header('Content-Type: application/json');
echo json_encode($data);

// Close the database connection
$conn->close();
?>
