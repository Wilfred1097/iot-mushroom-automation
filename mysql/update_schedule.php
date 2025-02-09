<?php
require 'conn.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'], $data['start_time'], $data['end_time'], $data['devices'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit();
}

$schedule_id = intval($data['id']);
$start_time = $data['start_time'];
$end_time = $data['end_time'];
$devices = implode(", ", $data['devices']);

// Fetch the current schedule's day
$stmt = $conn->prepare("SELECT days FROM `schedule` WHERE id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Schedule not found"]);
    exit();
}

$row = $result->fetch_assoc();
$selected_day = $row['days'];

// Check if the new time conflicts with existing schedules (excluding itself)
$conflictStmt = $conn->prepare("
    SELECT * FROM `schedule` 
    WHERE `days` = ? 
    AND `id` != ? 
    AND (
        (? BETWEEN `start_time` AND `end_time`) OR
        (? BETWEEN `start_time` AND `end_time`) OR
        (`start_time` BETWEEN ? AND ?) OR
        (`end_time` BETWEEN ? AND ?)
    )
");
$conflictStmt->bind_param("sissssss", $selected_day, $schedule_id, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
$conflictStmt->execute();
$conflictResult = $conflictStmt->get_result();

if ($conflictResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Schedule conflict detected! Choose a different time."]);
} else {
    // Proceed with the update if no conflict
    $updateStmt = $conn->prepare("UPDATE `schedule` SET start_time = ?, end_time = ?, device = ? WHERE id = ?");
    $updateStmt->bind_param("sssi", $start_time, $end_time, $devices, $schedule_id);

    if ($updateStmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Schedule updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update schedule"]);
    }

    $updateStmt->close();
}

$stmt->close();
$conflictStmt->close();
$conn->close();
?>
