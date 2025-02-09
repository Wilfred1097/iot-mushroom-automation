<?php
require_once 'conn.php';

// Get data from AJAX request
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$exportFormat = $_POST['exportFormat'];

// Prepare SQL query
if ($startDate && $endDate) {
    $sql = "SELECT * FROM parking_only WHERE date_in BETWEEN '$startDate' AND '$endDate'";
} else {
    // If no date range is provided, get all data
    $sql = "SELECT * FROM parking_only";
}

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Check if CSV is selected
if ($exportFormat === 'csv') {
    // Check if no data was found for the specific date range
    if (empty($data) && $startDate && $endDate) {
        // Return JSON response indicating no data found
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No data found for the selected date range.']);
        exit();
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="parking_history.csv"');

    // Output CSV headers
    $output = fopen('php://output', 'w');
    fputcsv($output, array('id', 'vehicle_owner', 'vehicle_type', 'make_model', 'license_num', 'color', 'date_in', 'Time_in', 'date_out', 'Time_out', 'payment_type', 'amount', 'parking_duraton', 'date_added'));

    // Output CSV data
    foreach ($data as $row) {
        $row['Time_in'] = date("g:i A", strtotime($row['Time_in']));
        $row['Time_out'] = date("g:i A", strtotime($row['Time_out']));
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
} else {
    // Check if no data was found for the specific date range
    if (empty($data) && $startDate && $endDate) {
        // Return JSON response indicating no data found
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No data found for the selected date range.']);
        exit();
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="parking_history.csv"');

    // Output CSV headers
    $output = fopen('php://output', 'w');
    fputcsv($output, array('id', 'vehicle_owner', 'vehicle_type', 'make_model', 'license_num', 'color', 'date_in', 'Time_in', 'date_out', 'Time_out', 'payment_type', 'amount', 'parking_duraton', 'date_added'));

    // Output CSV data
    foreach ($data as $row) {
        $row['Time_in'] = date("g:i A", strtotime($row['Time_in']));
        $row['Time_out'] = date("g:i A", strtotime($row['Time_out']));
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

// Return data as JSON if not CSV
header('Content-Type: application/json');
echo json_encode(['data' => $data]);

$conn->close();
?>
