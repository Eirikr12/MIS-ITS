<?php
session_start();
require_once "src/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Default response (kung walang data)
$response = [
    'daily_completed' => 0,
    'workloads_count' => 0,
    'requests_count' => 0,
    'monthly_completed' => 0,
    'recent_requests' => [
        ['employee' => 'No data yet', 'description' => 'Sample request', 'date' => date('m-d-Y')]
    ]
];

// Kunin ang mga counts mula sa database
try {
    // 1. Daily Completed
    $query = "SELECT COUNT(*) FROM tech_request WHERE tech_status = 'Completed' AND DATE(completion_date) = CURDATE()";
    $result = mysqli_query($link, $query);
    if ($result) $response['daily_completed'] = (int)mysqli_fetch_row($result)[0];

    // 2. Workloads Count (Pending/In Progress)
    $query = "SELECT COUNT(*) FROM workloads WHERE status IN ('Pending', 'In Progress')";
    $result = mysqli_query($link, $query);
    if ($result) $response['workloads_count'] = (int)mysqli_fetch_row($result)[0];

    // 3. Employee Requests Count (Pending)
    $query = "SELECT COUNT(*) FROM employee_requests WHERE status = 'Pending'";
    $result = mysqli_query($link, $query);
    if ($result) $response['requests_count'] = (int)mysqli_fetch_row($result)[0];

    // 4. Monthly Completed
    $query = "SELECT COUNT(*) FROM tech_request WHERE tech_status = 'Completed' AND MONTH(completion_date) = MONTH(CURDATE())";
    $result = mysqli_query($link, $query);
    if ($result) $response['monthly_completed'] = (int)mysqli_fetch_row($result)[0];

    // 5. Recent Requests (Last 5)
    $query = "SELECT e.employee_name, er.description, er.date_submitted 
              FROM employee_requests er
              JOIN employees e ON er.employee_id = e.id
              ORDER BY er.date_submitted DESC LIMIT 5";
    $result = mysqli_query($link, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $response['recent_requests'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $response['recent_requests'][] = [
                'employee' => $row['employee_name'],
                'description' => $row['description'],
                'date' => date('m-d-Y', strtotime($row['date_submitted']))
            ];
        }
    }
} catch (Exception $e) {
    // Kung may error, ipakita sa response
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
