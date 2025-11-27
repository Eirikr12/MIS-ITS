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

// Validate input
if (!isset($data['tech_request_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit();
}

$tech_request_id = $data['tech_request_id'];
$current_tech_id = $_SESSION['id']; // assuming technician is logged in

// Update tech request status
$complete_query = "UPDATE tech_request 
                   SET tech_status = 'Completed', 
                       completion_date = NOW()
                   WHERE id = ? 
                   AND assigned_tech_id = ?";

$stmt = $link->prepare($complete_query);
if ($stmt) {
    $stmt->bind_param("ii", $tech_request_id, $current_tech_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to execute update"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
}
?>
