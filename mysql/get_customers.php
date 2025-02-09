<?php
// Include the database connection file
include 'conn.php';

// Define the SQL query to fetch customer data
$sql = "SELECT fname, mname, lname, address, gender, cont, email, status, registration_date FROM user WHERE user_type='customer' ORDER BY id DESC";

// Execute the query
$result = $conn->query($sql);

// Initialize an array to hold the results
$customers = array();

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch each row
    while ($row = $result->fetch_assoc()) {
        // Combine first, middle, and last names
        $full_name = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
        
        // Create a new associative array with the combined name and other details
        $customer = array(
            'full_name' => $full_name,
            'address' => $row['address'],
            // 'fname' => $row['fname'],
            // 'mname' => $row['mname'],
            // 'lname' => $row['lname'],
            'gender' => $row['gender'],
            'cont' => $row['cont'],
            'email' => $row['email'],
            'status' => $row['status'],
            'registration_date' => $row['registration_date']
        );

        // Add the modified row to the array
        $customers[] = $customer;
    }
}

// Set the content type to application/json
header('Content-Type: application/json');

// Output the results as JSON
echo json_encode($customers);

// Close the connection
$conn->close();
?>
