<?php
// filepath: c:\xampp\htdocs\MIS_ITS\MIS_EMPLOYEE\Request Status\fetch_requests.php
// filepath: c:\xampp\htdocs\MIS_ITS\MIS_ADMIN\Request Status\fetch_requests.php

require_once "src/config.php"; // Database connection

header("Content-Type: application/json");

// Query to fetch request data
$query = "
    SELECT 
        request_info.id AS requestId,
        CONCAT(user_login.username) AS employeeName,
        user_login.department,
        request_info.request_description AS requestDescription,
        request_info.date_requested AS dateRequested,
        request_info.request_status AS status
    FROM request_info
    LEFT JOIN user_login ON request_info.user_id = user_login.id
";

$result = $link->query($query);

$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

echo json_encode($requests);
?>