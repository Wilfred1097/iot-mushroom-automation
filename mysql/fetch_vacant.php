<?php
// Assuming you have a database connection in 'conn.php'
include 'conn.php';


// Query to fetch vacant and occupied spaces
$query = "SELECT * FROM config WHERE id=1"; // Assuming there's a row with id=1
$result = mysqli_query($conn, $query);

if ($result) {
    // Fetch the result
    $rate = mysqli_fetch_assoc($result);

    // Get the vacant and occupied space values
    $vacant_space = $rate['vacant_space'];
    $occupied_space = $rate['occupied_space'];

    // Calculate the total space
    $total_space = $vacant_space + $occupied_space;

    // Return the total space value
    echo $total_space . '/' . $vacant_space;
} else {
    echo 'Error fetching data.';
}

// Close the database connection
mysqli_close($conn);
?>
