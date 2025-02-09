<?php
// Database connection
include_once 'conn.php'; // Make sure this file contains your database connection

header('Content-Type: application/json');

$query = "
SELECT 
    date_series.date AS parking_date,
    COALESCE(p.total_ids, 0) AS parking_only_count,
    COALESCE(o.total_ids, 0) AS overnight_parking_count
FROM 
    (
        SELECT CURDATE() - INTERVAL n DAY AS date
        FROM 
            (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL 
             SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6) AS days
    ) AS date_series
LEFT JOIN 
    (SELECT DATE(date_in) AS date, COUNT(id) AS total_ids 
     FROM parking_only 
     WHERE date_in >= CURDATE() - INTERVAL 7 DAY 
     GROUP BY DATE(date_in)) AS p ON date_series.date = p.date
LEFT JOIN 
    (SELECT DATE(date_in) AS date, COUNT(id) AS total_ids 
     FROM overnight_parking 
     WHERE date_in >= CURDATE() - INTERVAL 7 DAY 
     GROUP BY DATE(date_in)) AS o ON date_series.date = o.date
WHERE 
    date_series.date >= CURDATE() - INTERVAL 7 DAY
ORDER BY 
    parking_date DESC;
";

$result = $conn->query($query);
$totals = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $totals[] = $row;
    }
}

// Return the JSON response
echo json_encode($totals);

// Close the database connection
$conn->close();
?>
