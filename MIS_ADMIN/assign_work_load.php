<?php
session_start();
require_once "src/config.php"; // Database connection

// Check kung naka-login pa ang user
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check kung kompleto ang data
if (!isset($data['request_id']) || !isset($data['tech_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$request_id = $data['request_id'];
$tech_id = $data['tech_id'];

// Insert assignment into database
$assign_query = "INSERT INTO tech_request (original_request_id, assigned_tech_id, tech_status) 
                 VALUES (?, ?, 'Assigned')";

$stmt = $link->prepare($assign_query);
if ($stmt) {
    $stmt->bind_param("ii", $request_id, $tech_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to execute query"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
}
?>
