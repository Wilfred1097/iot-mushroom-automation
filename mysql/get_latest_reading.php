<?php
// Include the database connection file
include('conn.php'); 

header('Content-Type: application/json'); // Ensure the response is JSON format

// Query to fetch the latest temperature and humidity
$query = "SELECT `temperature`, `humidity` FROM `sensors_data` ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result) {
    // Fetch the latest data from the query
    $data = mysqli_fetch_assoc($result);
    
    // Check if data exists
    if ($data) {
        // Prepare the response
        $response = [
            'status' => 'success',
            'data' => [
                'temperature' => $data['temperature'],
                'humidity' => $data['humidity']
            ]
        ];
    } else {
        // No data found
        $response = [
            'status' => 'error',
            'message' => 'No sensor data found.'
        ];
    }
} else {
    // Query failed
    $response = [
        'status' => 'error',
        'message' => 'Error fetching sensor data: ' . mysqli_error($conn)
    ];
}

// Close the database connection
mysqli_close($conn);

// Return the response as JSON
echo json_encode($response);
?>
