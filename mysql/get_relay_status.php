<?php
include('conn.php'); // Include your database connection file

// Define the query to fetch relay status
$query = "SELECT relay_name, relay_status FROM relays";

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query returned any results
if (mysqli_num_rows($result) > 0) {
    // Create an array to hold the relay status data
    $relayStatus = array();

    // Loop through the results and fetch each row
    while ($row = mysqli_fetch_assoc($result)) {
        $relayStatus[] = $row; // Add each row to the array
    }

    // Return the relay status as JSON response
    echo json_encode($relayStatus);
} else {
    // If no data is found, return an empty array
    echo json_encode([]);
}

// Close the database connection
mysqli_close($conn);
?>
