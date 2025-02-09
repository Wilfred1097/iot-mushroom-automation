<?php
include_once 'conn.php';

// Check if the request is an AJAX request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from AJAX request
    $id = $_POST['Id'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Handle file upload for Profile Picture
    $profilePictureFilename = null;
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/profile/'; // Ensure this directory exists and is writable
        $fileName = uniqid('profile_') . '_' . basename($_FILES['profilePicture']['name']);
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadFilePath)) {
            $profilePictureFilename = $fileName; // Save only the filename
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload profile picture.']);
            exit;
        }
    }

    // Update user details, including profile picture if uploaded
    $sql = "UPDATE user SET fname = ?, mname = ?, lname = ?, address = ?, gender = ?, cont = ?, email = ?";
    $params = [$fname, $mname, $lname, $address, $gender, $phone, $email];
    $types = "sssssss";

    if ($profilePictureFilename) {
        $sql .= ", profile = ?"; // Save the filename in the `profile` column
        $params[] = $profilePictureFilename;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "s";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User details updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user details.']);
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
