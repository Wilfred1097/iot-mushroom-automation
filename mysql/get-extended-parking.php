<?php
// Database connection
require_once 'conn.php';

// SQL query to fetch overnight parking records
$sql = "SELECT * FROM `overnight_parking` WHERE `date_out` = '' AND `time_out` = ''";
$result = $conn->query($sql);

$parkingData = array();
if ($result->num_rows > 0) {
    // Fetch all records
    while($row = $result->fetch_assoc()) {
        $parkingData[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($parkingData);

// Close connection
$conn->close();
?>

