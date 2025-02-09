<?php
include 'conn.php';

$todayCount = $conn->query("SELECT COUNT(id) FROM `parking_only` WHERE DATE(date_in) = CURDATE()")->fetch_row()[0];

echo "Today: $todayCount";
$conn->close()
?>
