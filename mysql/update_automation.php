<?php
// Include the database connection
include('conn.php');

// Get the status from the request (assuming it's sent via POST method)
$status = isset($_POST['status']) ? $_POST['status'] : null;

// Get current date and time in 'Asia/Singapore' timezone
$dateUpdated = new DateTime('now', new DateTimeZone('Asia/Singapore'));
$currentTime = $dateUpdated->format('Y-m-d H:i:s');

// Get the email from the cookie
$email = isset($_COOKIE['iot-mushroom']) ? json_decode($_COOKIE['iot-mushroom'], true)['email'] : null;

// Check if status is valid and email is set
if ($status !== null && ($status == 1 || $status == 0) && $email !== null) {

    // Prepare the UPDATE SQL query to update automation mode (id = 4)
    $sql = "UPDATE `relays` SET `relay_status` = ?, `date_updated` = ? WHERE id = 4";

    // Prepare the statement for the relay status update
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the SQL query
        $stmt->bind_param("is", $status, $currentTime);

        // Execute the statement for relay status update
        if ($stmt->execute()) {
            // echo "Automation mode updated successfully.";

            // Prepare the event description based on the status
            $event = ($status == 1) ? "Turn on Automation" : "Turn off Automation";

            // Insert event into the logs table
            $logSql = "INSERT INTO `logs` (`email`, `event`) VALUES (?, ?)";
            if ($logStmt = $conn->prepare($logSql)) {
                // Bind parameters for the log insertion
                $logStmt->bind_param("ss", $email, $event);

                // Execute the log insert statement
                if ($logStmt->execute()) {
                    echo " Event logged successfully.";
                } else {
                    echo " Error logging event: " . $logStmt->error;
                }

                // Close the log statement
                $logStmt->close();
            } else {
                echo " Error preparing log query: " . $conn->error;
            }

            // If status is 0 (automation OFF), turn off all other relays
            if ($status == 0) {
                $updateRelaysSQL = "UPDATE `relays` SET `relay_status` = 0, `date_updated` = ? WHERE id IN (1, 2, 3)";
                
                if ($updateStmt = $conn->prepare($updateRelaysSQL)) {
                    $updateStmt->bind_param("s", $currentTime);
                    if ($updateStmt->execute()) {
                        echo " All relays turned off.";
                    } else {
                        echo " Error turning off relays: " . $updateStmt->error;
                    }
                    $updateStmt->close();
                } else {
                    echo " Error preparing relay update query: " . $conn->error;
                }
            }

        } else {
            echo " Error updating automation mode: " . $stmt->error;
        }

        // Close the automation mode update statement
        $stmt->close();
    } else {
        echo " Error preparing automation mode query: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid input or missing parameters.";
}
?>
