<?php
require 'conn.php'; // Ensure this file contains your database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['day'], $data['start_time'], $data['end_time'], $data['devices'], $data['user_email'])) {
        $day = $data['day'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $devices = implode(", ", $data['devices']); // Convert array to comma-separated string
        $user_email = $data['user_email'];

        // Check if there is an existing schedule with overlapping time for the same day
        $stmt = $conn->prepare("
            SELECT * FROM schedule 
            WHERE days = ? 
            AND (
                (? BETWEEN start_time AND end_time) OR
                (? BETWEEN start_time AND end_time) OR
                (start_time BETWEEN ? AND ?) OR
                (end_time BETWEEN ? AND ?)
            )
        ");
        $stmt->bind_param("sssssss", $day, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => 409, "message" => "Schedule already added"]); // 409 Conflict
        } else {
            // No conflict, proceed with insertion
            $stmt = $conn->prepare("INSERT INTO schedule(days, start_time, end_time, device, user) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $day, $start_time, $end_time, $devices, $user_email);

            if ($stmt->execute()) {
                echo json_encode(["status" => 200, "message" => "Schedule successfully added"]);
            } else {
                echo json_encode(["status" => 500, "message" => "Failed to add schedule"]);
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["status" => 400, "message" => "Invalid input"]);
    }
} else {
    echo json_encode(["status" => 405, "message" => "Method Not Allowed"]);
}
?>