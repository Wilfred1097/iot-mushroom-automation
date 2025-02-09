<?php
require('fpdf/fpdf.php');
include('conn.php');

$pdf = new FPDF();
$pdf->AddPage('L');
$pdf->SetFont('Arial','',10);
$date = new DateTime('now', new DateTimeZone('UTC')); // Start with UTC
$date->setTimezone(new DateTimeZone('Asia/Singapore')); // Change to Singapore timezone
$pdf->Cell(65, 10, 'Date Generated: ' . $date->format('M. d, Y h:i A'), 0, 0, "R");

// Add Date Range if both variables are set
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

if ($startDate && $endDate) {
    // Format the start and end dates
    $startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
    $endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);
    
    $formattedStartDate = $startDateObj->format('M. d, Y');
    $formattedEndDate = $endDateObj->format('M. d, Y');

    $pdf->Cell(0, 10, 'Date Range: ' . $formattedStartDate . ' - ' . $formattedEndDate, 0, 1, "R");
} else {
    $pdf->Ln(10); // Just a line break if no date range is provided
}

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Parking History Report',1,1,"C");
$pdf->SetFont('Arial','B',9);
$pdf->Cell(10,5,'No.',1, 0, 'C');
$pdf->Cell(40,5,'Owner',1, 0, 'C');
$pdf->Cell(23,5,'Vehicle Type',1, 0, 'C');
$pdf->Cell(28,5,'Make/Model',1, 0, 'C');
$pdf->Cell(18,5,'License',1, 0, 'C');
$pdf->Cell(16,5,'Color',1, 0, 'C');
$pdf->Cell(40,5,'Datetime In',1, 0, 'C');
$pdf->Cell(40,5,'Datetime Out',1, 0, 'C');
$pdf->Cell(20,5,'Duration',1, 0, 'C');
$pdf->Cell(18,5,'Payment',1, 0, 'C');
$pdf->Cell(24,5,'Amount',1, 0, 'C');

if ($startDate && $endDate) {
    $query = "SELECT *,TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', time_in), CONCAT(date_out, ' ', time_out)) DIV 60 AS parking_duration_hours, TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', time_in), CONCAT(date_out, ' ', time_out)) % 60 AS parking_duration_minutes FROM parking_only WHERE date_in BETWEEN '$startDate' AND '$endDate'";
} else {
    $query = "SELECT *,TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', time_in), CONCAT(date_out, ' ', time_out)) DIV 60 AS parking_duration_hours, TIMESTAMPDIFF(MINUTE, CONCAT(date_in, ' ', time_in), CONCAT(date_out, ' ', time_out)) % 60 AS parking_duration_minutes FROM parking_only"; // Get all data if no date range is provided
}

$result = mysqli_query($conn, $query);
$no = 0;
$totalVehicles = 0;
$totalProfit = 0;

while($row = mysqli_fetch_array($result)){
    $no = $no + 1;
    $totalVehicles++; // Increment total vehicles
    $totalProfit += $row['amount']; // Sum the total amount

    // Format the combined datetime in and datetime out
    $dateIn = DateTime::createFromFormat('Y-m-d H:i', $row['date_in'] . ' ' . $row['Time_in']);
    $formattedDatetimeIn = $dateIn->format('M. d, Y h:i A');

    $dateOut = DateTime::createFromFormat('Y-m-d H:i', $row['date_out'] . ' ' . $row['Time_out']);
    $formattedDatetimeOut = $dateOut->format('M. d, Y h:i A');

    $pdf->Ln(5);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(10,5,$no,1, 0, 'C');
    $pdf->Cell(40, 5, !empty($row['vehicle_owner']) ? $row['vehicle_owner'] : '-', 1, 0, 'C');
    $pdf->Cell(23,5,$row['vehicle_type'] . '-Wheeled',1, 0, 'C');
    $pdf->Cell(28,5,$row['make_model'],1, 0, 'C');
    $pdf->Cell(18,5,$row['license_num'],1, 0, 'C');
    $pdf->Cell(16,5,$row['color'],1, 0, 'C');
    $pdf->Cell(40,5,$formattedDatetimeIn, 1, 0, 'C'); // Combined Datetime In
    $pdf->Cell(40,5,$formattedDatetimeOut, 1, 0, 'C'); // Combined Datetime Out
    $pdf->Cell(20, 5, $row['parking_duration_hours'] . 'h, ' . $row['parking_duration_minutes'] . 'm', 1, 0, 'C');
    $pdf->Cell(18,5,$row['payment_type'],1, 0, 'C');
    $pdf->Cell(24,5,'P ' . $row['amount'] . '.00',1, 0, 'C');
}

// Add totals below the table
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(253, 5, 'Total Vehicles:', 1, 0, 'R');
$pdf->Cell(24, 5, $totalVehicles, 1, 0, 'C');

$pdf->Ln(5);
$pdf->Cell(253, 5, 'Total Profit:', 1, 0, 'R');
$pdf->Cell(24, 5, 'P ' . $totalProfit . '.00', 1, 0, 'C');

$pdf->Output();
?>
