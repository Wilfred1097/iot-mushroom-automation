<?php
require 'conn.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Missing schedule ID"]);
    exit();
}

$schedule_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT start_time, end_time, device FROM `schedule` WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "success", "schedule" => $result->fetch_assoc()]);
} else {
    echo json_encode(["status" => "error", "message" => "Schedule not found"]);
}

$stmt->close();
$conn->close();
?>
