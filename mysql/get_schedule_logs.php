<?php
// Include the database connection
include 'conn.php';

// Query to fetch logs with user details in descending order
$query = "SELECT 
            s.id, 
            user.fname, 
            user.mname, 
            user.lname, 
            s.days,
            s.start_time,
            s.end_time,
            s.device,
            s.date_added
          FROM `schedule` AS s
          JOIN `user` ON s.user = user.email 
          ORDER BY s.days";

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
