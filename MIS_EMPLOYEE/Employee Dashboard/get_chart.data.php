<?php
require_once "src/config.php"; // Ensure this file connects to the database

// Query to fetch data from the user_login table
$query = "SELECT id, username, position, department, status FROM user_login";
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