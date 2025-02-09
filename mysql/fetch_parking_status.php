<?php
// Database connection parameters
include 'conn.php';
// Query the vacant_space and occupied_space from the config table
$vacant_space_result = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 0");
$occupied_space_result = $conn->query("SELECT COUNT(id) FROM `parking_status` WHERE status = 1");

// Check if results exist
if ($vacant_space_result && $occupied_space_result) {
    $vacant_space = $vacant_space_result->fetch_row()[0];
    $occupied_space = $occupied_space_result->fetch_row()[0];

    // Calculate the total space
    $total_space = $vacant_space + $occupied_space;

    // Prepare the response data
    $response = array(
        "total_space" => $total_space,
        "vacant_space" => $vacant_space,
        "occupied_space" => $occupied_space
    );

    // Return the data as a JSON response
    echo json_encode($response);
} else {
    // In case of an error fetching data
    echo json_encode(array("error" => "Error fetching space data from the database."));
}

// Close the database connection
$conn->close();
?>
