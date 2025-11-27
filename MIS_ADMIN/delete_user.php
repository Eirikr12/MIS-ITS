<?php
require_once "src/config.php"; // Database connection

// Ensure an ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No user ID provided.");
}

$id = intval($_GET['id']); // Always sanitize ID inputs

// Step 1: Fetch the user's position from user_login
$position_query = "SELECT position FROM user_login WHERE id = ?";
$position_stmt = $link->prepare($position_query);
$position_stmt->bind_param("i", $id);
$position_stmt->execute();
$position_stmt->bind_result($position);
$position_stmt->fetch();
$position_stmt->close();

// Step 2: Delete related data based on the user's position
switch ($position) {
    case "admin":
        $stmt = $link->prepare("DELETE FROM admin_info WHERE id = ?");
        break;
    case "tech":
        $stmt = $link->prepare("DELETE FROM tech_info WHERE id = ?");
        break;
    case "employee":
        $stmt = $link->prepare("DELETE FROM employee_info WHERE id = ?");
        break;
    case "hr":
        $stmt = $link->prepare("DELETE FROM hr_info WHERE id = ?");
        break;
    default:
        $stmt = null;
        break;
}

if ($stmt) {
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        echo "Error deleting related position-specific info: " . $stmt->error;
        exit();
    }
    $stmt->close();
}

// Step 3: Delete related data in request_info
$stmt_request = $link->prepare("DELETE FROM request_info WHERE id = ?");
$stmt_request->bind_param("i", $id);

if (!$stmt_request->execute()) {
    echo "Error deleting related request info: " . $stmt_request->error;
    exit();
}
$stmt_request->close();

// Step 4: Delete related data in temporary_login
$stmt2 = $link->prepare("DELETE FROM temporary_login WHERE temporary_id = ?");
$stmt2->bind_param("i", $id);

if (!$stmt2->execute()) {
    echo "Error deleting related temporary login info: " . $stmt2->error;
    exit();
}
$stmt2->close();

// Step 5: Delete from user_login
$stmt3 = $link->prepare("DELETE FROM user_login WHERE id = ?");
$stmt3->bind_param("i", $id);

if ($stmt3->execute()) {
    $stmt3->close();
    header("Location: user_registration.php?msg=deleted");
    exit();
} else {
    echo "Error deleting user: " . $stmt3->error;
}
?>
