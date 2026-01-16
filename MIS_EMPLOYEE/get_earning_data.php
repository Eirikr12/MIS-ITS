<?php
require_once "../src/config.php"; // Ensure this file connects to the database

// Query to fetch data from the user_login table
$query = "SELECT position, COUNT(*) AS count FROM user_login GROUP BY position";
$result = $link->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>