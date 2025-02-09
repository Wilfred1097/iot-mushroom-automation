<?php
// Include the database connection
include 'conn.php';

// SQL query to get all records from the parking_only table
$query = "SELECT * FROM vehicle_category";
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
