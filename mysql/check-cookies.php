<?php 
// Check if SmartSpot cookie is set
if (isset($_COOKIE['SmartSpot'])) {
    // Redirect to another page, e.g., homepage
    header('Location: ../dashboard.php');
    exit();
}

// if (isset($_COOKIE['SmartSpot'])) {
//     $user_data = json_decode($_COOKIE['SmartSpot'], true);
//     $email = $user_data['email'];
//     $user_type = $user_data['user_type'];
// }
?>