<?php
// Database connection
require_once 'conn.php';

$sql = "SELECT slots_number, status FROM parking_status ORDER BY slots_number ASC";
$result = $conn->query($sql);

$parking_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $parking_data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($parking_data);

$conn->close();
?>
