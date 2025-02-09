<?php
include 'conn.php';

// Check if 'id' is sent via POST
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Prepare and execute delete query
    $query = "DELETE FROM vehicle_category WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Vehicle category deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete vehicle category']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
?>
