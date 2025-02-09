<?php
// Include the database connection
include 'conn.php';

// Query to fetch logs with user details in descending order
$query = "SELECT 
            logs.id, 
            user.fname, 
            user.mname, 
            user.lname, 
            logs.email, 
            logs.event, 
            logs.date_created 
          FROM `logs` 
          JOIN `user` ON logs.email = user.email 
          ORDER BY logs.id DESC";

$result = mysqli_query($conn, $query);

$logs = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row; // Add each row to the logs array
    }

    // Set response header to JSON
    header('Content-Type: application/json');
    echo json_encode($logs); // Convert the array to JSON and output it
} else {
    echo json_encode(['error' => 'Error fetching data.']);
}

// Close the database connection
mysqli_close($conn);
?>
