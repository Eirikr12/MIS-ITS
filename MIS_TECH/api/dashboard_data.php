<?php
require_once '../src/config.php'; // Include database connection

header('Content-Type: application/json');

// Initialize response array
$response = [
    'dailyCompleted' => 0,
    'workLoads' => 0,
    'requests' => 0,
    'totalMonthlyCompleted' => 0,
];

// Fetch "Daily Completed" count
$query = "SELECT COUNT(*) AS dailyCompleted FROM request_info WHERE request_status = 'Approved' AND request_date = CURDATE()";
$result = $link->query($query);
if ($row = $result->fetch_assoc()) {
    $response['dailyCompleted'] = $row['dailyCompleted'];
}

// Fetch "Work Loads" count
$query = "SELECT COUNT(*) AS workLoads FROM request_info WHERE request_status IN ('Pending', 'In Progress')";
$result = $link->query($query);
if ($row = $result->fetch_assoc()) {
    $response['workLoads'] = $row['workLoads'];
}

// Fetch "Requests" count
$query = "SELECT COUNT(*) AS requests FROM request_info";
$result = $link->query($query);
if ($row = $result->fetch_assoc()) {
    $response['requests'] = $row['requests'];
}

// Fetch "Total Monthly Completed" count
$query = "SELECT COUNT(*) AS totalMonthlyCompleted FROM request_info WHERE request_status = 'Approved' AND MONTH(request_date) = MONTH(CURDATE()) AND YEAR(request_date) = YEAR(CURDATE())";
$result = $link->query($query);
if ($row = $result->fetch_assoc()) {
    $response['totalMonthlyCompleted'] = $row['totalMonthlyCompleted'];
}

// Return JSON response
echo json_encode($response);
?>