<?php
require('fpdf/fpdf.php');
include('conn.php');

// Create a new FPDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Get the current date and time (for display)
$date = new DateTime('now', new DateTimeZone('UTC')); // Start with UTC
$date->setTimezone(new DateTimeZone('Asia/Singapore')); // Change to Singapore timezone
$pdf->Cell(70, 10, 'Date Generated: ' . $date->format('M. d, Y h:i:s A'), 0, 0, "R");

// Get the start and end date from the URL parameters
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate   = isset($_GET['endDate'])   ? $_GET['endDate']   : '';

if ($startDate && $endDate) {
    // If the input is in datetime-local format, it is typically "YYYY-MM-DDTHH:MM" (length 16).
    // Append ":00" if seconds are not provided.
    if (strlen($startDate) === 16) {
        $startDate .= ":00";
    }
    if (strlen($endDate) === 16) {
        $endDate .= ":00";
    }

    // Convert the ISO 8601 datetime (with a "T") to a DateTime object
    $startDateObj = DateTime::createFromFormat('Y-m-d\TH:i:s', $startDate);
    $endDateObj   = DateTime::createFromFormat('Y-m-d\TH:i:s', $endDate);

    if ($startDateObj && $endDateObj) {
        // Format for display (with seconds)
        $formattedStartDate = $startDateObj->format('M. d, Y h:i:s A');
        $formattedEndDate   = $endDateObj->format('M. d, Y h:i:s A');
        $pdf->Cell(0, 10, 'Date Range: ' . $formattedStartDate . ' - ' . $formattedEndDate, 0, 1, "R");

        // Convert to MySQL datetime format ("Y-m-d H:i:s")
        $mysqlStartDate = $startDateObj->format('Y-m-d H:i:s');
        $mysqlEndDate   = $endDateObj->format('Y-m-d H:i:s');
    } else {
        // If conversion failed, show an error in the PDF
        $pdf->Cell(0, 10, 'Invalid date format. Please check the input.', 0, 1, "C");
        $mysqlStartDate = $mysqlEndDate = null;
    }
} else {
    $pdf->Ln(10); // Just a line break if no date range is provided
    $mysqlStartDate = $mysqlEndDate = null;
}

// Set the table headers for the data
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Sensor Data Report',1,1,"C");
$pdf->SetFont('Arial','B',9);
$pdf->Cell(62,5,'Datetime',1, 0, 'C');
$pdf->Cell(32,5,'Temperature',1, 0, 'C');
$pdf->Cell(32,5,'Humidity',1, 0, 'C');
$pdf->Cell(32,5,'Water Level',1, 0, 'C');
$pdf->Cell(32,5,'Status',1, 0, 'C');  // New column for Status

// Function to determine the status based on temperature and humidity
function getStatus($temperature, $humidity) {
    if ($temperature >= 25 && $temperature <= 30 && $humidity >= 80 && $humidity <= 90) {
        return 'Normal';
    } else if ($temperature > 30 && $humidity < 80) {
        return 'Too Hot';
    } else if ($temperature < 25 && $humidity > 90) {
        return 'Too Cold';
    } else {
        return 'Unusual';
    }
}

// Build the query using the MySQL formatted datetime strings
if ($mysqlStartDate && $mysqlEndDate) {
    $query = "SELECT date_added, temperature, humidity, water_level FROM sensors_data WHERE date_added BETWEEN '$mysqlStartDate' AND '$mysqlEndDate'";
} else {
    $query = "SELECT date_added, temperature, humidity, water_level FROM sensors_data";
}

// Execute the query
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_array($result)) {
    // Convert the fetched date_added (assumed to be in "Y-m-d H:i:s") to a DateTime object
    $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $row['date_added']);
    $formattedDate = $dateObj->format('M. d, Y h:i:s A');

    // Get the status based on temperature and humidity
    $status = getStatus($row['temperature'], $row['humidity']);

    // Print the data in the table
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(62,5,$formattedDate,1, 0, 'C');
    $pdf->Cell(32,5,$row['temperature'] . chr(176) . 'C',1, 0, 'C');
    $pdf->Cell(32,5,$row['humidity'] . ' %',1, 0, 'C');
    $pdf->Cell(32,5,$row['water_level'] . ' %',1, 0, 'C');
    $pdf->Cell(32,5,$status,1, 0, 'C');  // Display the status in the new column
}

// Close the database connection
mysqli_close($conn);

// Output the PDF
$pdf->Output();
?>
