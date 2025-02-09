<?php
// Database connection details
require_once 'conn.php';

// Check if vehicle_type is provided via POST
if (isset($_POST['vehicle_type'])) {
    $vehicle_type = $_POST['vehicle_type'];
    
    // Ensure that the vehicle_type is safely included in the query to avoid SQL injection
    $vehicle_type = mysqli_real_escape_string($conn, $vehicle_type);
    
    $query = "SELECT amount, overtime FROM vehicle_category WHERE vehicle_type = '$vehicle_type'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'amount' => $row['amount'],
            'overtime' => $row['overtime']
        ]);
    } else {
        echo json_encode(['amount' => 0, 'overtime' => 0]);
    }
} else {
    echo json_encode(['error' => 'vehicle_type parameter missing']);
}

// Close the database connection
$conn->close();
?>
