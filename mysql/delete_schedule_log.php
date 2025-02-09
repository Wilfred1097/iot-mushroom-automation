<?php
// Include database connection
include 'conn.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if 'id' parameter is provided
if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Missing log ID"]);
    exit();
}

$log_id = intval($_GET['id']); // Ensure it's an integer

// Prepare delete query
$stmt = $conn->prepare("DELETE FROM `schedule` WHERE id = ?");
$stmt->bind_param("i", $log_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Log deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete log"]);
}

$stmt->close();
$conn->close();
?>
