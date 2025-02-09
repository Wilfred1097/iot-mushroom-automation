<?php
// Database connection
require 'conn.php'; 

// Fetch data from sensors_data table
$sql = "SELECT temperature, humidity, water_level, date_added FROM sensors_data ORDER BY id DESC LIMIT 200";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Close connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
?>
