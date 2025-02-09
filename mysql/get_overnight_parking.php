<?php
// Include the database connection
include 'conn.php';

// SQL query to get all records from the overnight_parking table
$query = "SELECT * FROM parking_only WHERE TIMESTAMP(date_in, time_in) <= NOW() - INTERVAL 12 HOUR AND date_out = '' ORDER BY id desc";
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
