<?php
require('fpdf/fpdf.php');
include('conn.php');

$pdf = new FPDF();

// First page
$pdf->AddPage('L');
$pdf->SetFont('Arial','',10);
$date = new DateTime('now', new DateTimeZone('UTC'));
$date->setTimezone(new DateTimeZone('Asia/Singapore'));

$pdf->Cell(65, 10, 'Date Generated: ' . $date->format('M. d, Y h:i A'), 0, 0, "R");

$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

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

$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Overnight Parking History Report',1,1,"C");
$pdf->SetFont('Arial','B',10);
$pdf->Cell(12,5,'No.',1, 0, 'C');
$pdf->Cell(56,5,'Owner Name',1, 0, 'C');
$pdf->Cell(100,5,'Address',1, 0, 'C');
$pdf->Cell(26,5,'DOB',1, 0, 'C');
$pdf->Cell(25,5,'License',1, 0, 'C');
$pdf->Cell(32,5,'Contact #',1, 0, 'C');
$pdf->Cell(26,5,'Vehicle Type',1, 0, 'C');


if ($startDate && $endDate) {
    $query = "SELECT * FROM overnight_parking WHERE date_in BETWEEN '$startDate' AND '$endDate'";
} else {
    $query = "SELECT * FROM overnight_parking WHERE date_out != ''"; 
}

$result = mysqli_query($conn, $query);
$no = 0;
$entries = []; // Store entries for second page
$totalProfit = 0; // Initialize total profit

while($row = mysqli_fetch_array($result)){
    $no++;
    $dateIn = DateTime::createFromFormat('Y-m-d', $row['date_in']);
    $dateOut = DateTime::createFromFormat('Y-m-d', $row['date_out']);
    $formattedDateIn = $dateIn->format('M. d, Y');
    $formattedDateOut = $dateOut->format('M. d, Y');
    $timeIn = DateTime::createFromFormat('H:i', $row['time_in']);
    $timeOut = DateTime::createFromFormat('H:i', $row['time_out']);
    $formattedTimeIn = $timeIn->format('h:i A');
    $formattedTimeOut = $timeOut->format('h:i A');

    $pdf->Ln(5);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(12,5,$no,1, 0, 'C');
    $pdf->Cell(56,5,$row['owner_name'],1, 0, 'C');
    $pdf->Cell(100,5,$row['address'],1, 0, 'C');
    $pdf->Cell(26,5,$row['dob'],1, 0, 'C');
    $pdf->Cell(25,5,$row['license'],1, 0, 'C');
    $pdf->Cell(32,5,$row['contact_num'],1, 0, 'C');
    $pdf->Cell(26,5,$row['vehicle_type'] . '-Wheeled',1, 0, 'C');

    // Store entry for second page
    $entries[] = [
        'make_model' => $row['make_model'],
        'color' => $row['color'],
        'cert_reg' => $row['cert_reg'],
        'vin_num' => $row['vin_num'],
        'payment_type' => $row['payment_type'],
        'amount' => $row['amount']
    ];

    // Add to total profit
    $totalProfit += $row['amount'];
}

// Page numbering for the first page
$pageCount = 1;
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Page ' . $pageCount . ' of 2', 0, 0, 'C');

// Add new page for Payment and Amount details
$pdf->AddPage('L');
$pageCount++;
$pdf->SetFont('Arial','',10);
$pdf->Cell(50, 10, 'Date Generated: ' . $date->format('M. d, Y h:i A'), 0, "R");
$pdf->Ln(5);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Payment Details',1,1,"C");
$pdf->SetFont('Arial','B',10);
$pdf->Cell(12, 5, 'No.', 1, 0, 'C');
$pdf->Cell(41,5,'Make/Model',1, 0, 'C');
$pdf->Cell(22,5,'Color',1, 0, 'C');
$pdf->Cell(25,5,'COR',1, 0, 'C');
$pdf->Cell(25,5,'VIN Num',1, 0, 'C');
$pdf->Cell(29,5,'Date In',1, 0, 'C');
$pdf->Cell(24,5,'Time In',1, 0, 'C');
$pdf->Cell(29,5,'Date Out',1, 0, 'C');
$pdf->Cell(24,5,'Time Out',1, 0, 'C');
$pdf->Cell(23, 5, 'Payment', 1, 0, 'C');
$pdf->Cell(23, 5, 'Amount', 1, 0, 'C');

$pdf->SetFont('Arial','',9);
foreach ($entries as $index => $entry) {
    $pdf->Ln(5);
    $pdf->Cell(12, 5, $index + 1, 1, 0, 'C');
    $pdf->Cell(41,5,$entry['make_model'],1, 0, 'C');
    $pdf->Cell(22,5,$entry['color'],1, 0, 'C');
    $pdf->Cell(25,5,$entry['cert_reg'],1, 0, 'C');
    $pdf->Cell(25,5,$entry['vin_num'],1, 0, 'C');
    $pdf->Cell(29,5, $formattedDateIn, 1, 0, 'C');
    $pdf->Cell(24,5, $formattedTimeIn, 1, 0, 'C');
    $pdf->Cell(29,5, $formattedDateOut, 1, 0, 'C');
    $pdf->Cell(24,5, $formattedTimeOut, 1, 0, 'C');
    $pdf->Cell(23, 5, $entry['payment_type'], 1, 0, 'C');
    $pdf->Cell(23, 5, $entry['amount'], 1, 0, 'C');
}

// Add total vehicles and total profit
// $pdf->Ln(10);
// $pdf->SetFont('Arial','B',10);
// $pdf->Cell(0, 10, 'Total Vehicles: ' . $no, 0, 1, 'R');
// $pdf->Cell(0, 10, 'Total Profit: ' . number_format($totalProfit, 2), 0, 1, 'R');


$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(260, 5, 'Total Vehicles:', 1, 0, 'R');
$pdf->Cell(17, 5, $no, 1, 0, 'R');

$pdf->Ln(5);
$pdf->Cell(260, 5, 'Total Profit:', 1, 0, 'R');
$pdf->Cell(17, 5, 'P ' . number_format($totalProfit, 2), 1, 0, 'C');

// Page numbering for the second page
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Page ' . $pageCount . ' of 2', 0, 0, 'C');

$pdf->Output();
?>
