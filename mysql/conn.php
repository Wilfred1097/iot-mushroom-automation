<?php
// Database credentials
$servername = '153.92.15.53';
$username = 'u605048123_root2025';
$password = '#7nL=dw3N7';
$database = 'u605048123_jhcsc';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// else {
//     echo("Database Connected");
// }
?>

