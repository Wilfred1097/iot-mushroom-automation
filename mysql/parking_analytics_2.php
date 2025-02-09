<?php
// Include database connection
include_once 'conn.php';

// Initialize an array to store the results
$result_data = [];

// SQL query to get the total counts for each month
$query = "
SELECT 
    DATE_FORMAT(date_series.date, '%Y-%m') AS parking_month,
    COALESCE(p.total_ids, 0) + COALESCE(o.total_ids, 0) AS total_count
FROM 
    (
        SELECT LAST_DAY(CURDATE() - INTERVAL n MONTH) AS date
        FROM 
            (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL 
             SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL 
             SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11) AS months
    ) AS date_series
LEFT JOIN 
    (SELECT DATE_FORMAT(date_in, '%Y-%m') AS month, COUNT(id) AS total_ids 
     FROM parking_only 
     WHERE date_in >= CURDATE() - INTERVAL 12 MONTH 
     GROUP BY month) AS p ON DATE_FORMAT(date_series.date, '%Y-%m') = p.month
LEFT JOIN 
    (SELECT DATE_FORMAT(date_in, '%Y-%m') AS month, COUNT(id) AS total_ids 
     FROM overnight_parking 
     WHERE date_in >= CURDATE() - INTERVAL 12 MONTH 
     GROUP BY month) AS o ON DATE_FORMAT(date_series.date, '%Y-%m') = o.month
WHERE 
    date_series.date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
ORDER BY 
    parking_month ASC;
";

// Execute the query
if ($result = $conn->query($query)) {
    // Fetch all results
    while ($row = $result->fetch_assoc()) {
        $result_data[] = $row;
    }
    // Free result set
    $result->free();
} else {
    // Handle query error
    echo json_encode(['error' => $conn->error]);
}

// Close the database connection
$conn->close();

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($result_data);
?>
