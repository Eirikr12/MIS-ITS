<?php
require_once "src/config.php";

header("Content-Type: application/json");

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userID'], $data['EmpID'], $data['field'], $data['newValue'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data']);
    exit();
}

// Extract data
$userID = $data['userID'];
$EmpID = $data['EmpID'];
$field = $data['field'];
$newValue = $data['newValue'];

// Optional: sanitize / validate values further here

// Save to a table called 'edit_requests'
$stmt = $link->prepare("
    INSERT INTO edit_requests (employee_id, requested_by, field_to_edit, new_value, request_date, status)
    VALUES (?, ?, ?, ?, NOW(), 'pending')
");

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Query error']);
    exit();
}

$stmt->bind_param("siss", $EmpID, $userID, $field, $newValue);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
?>