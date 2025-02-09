<?php
// Include the database connection
include('conn.php');

// Get the status values from the POST request
$mistingRelay = isset($_POST['misting_relay']) ? $_POST['misting_relay'] : null;
$humidifierRelay = isset($_POST['humidifier_relay']) ? $_POST['humidifier_relay'] : null;
$fanRelay = isset($_POST['fan_relay']) ? $_POST['fan_relay'] : null;

// Get current date and time in 'Asia/Singapore' timezone
$dateUpdated = new DateTime('now', new DateTimeZone('Asia/Singapore'));
$currentTime = $dateUpdated->format('Y-m-d H:i:s');

// Prepare an array of updates
$updates = [
    1 => $mistingRelay,
    2 => $humidifierRelay,
    3 => $fanRelay
];

$response = [];

foreach ($updates as $id => $status) {
    if ($status !== null && ($status == 0 || $status == 1)) {
        // Prepare the UPDATE SQL query
        $sql = "UPDATE `relays` SET `relay_status` = ?, `date_updated` = ? WHERE id = ?";
        
        // Prepare and execute statement
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isi", $status, $currentTime, $id);
            if ($stmt->execute()) {
                $response[] = "Relay ID $id updated to $status";
            } else {
                $response[] = "Error updating relay ID $id: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response[] = "Error preparing query for relay ID $id: " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(["status" => "success", "message" => $response]);
?>
