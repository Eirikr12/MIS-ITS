<?php
session_start();
require_once "src/config.php";

if (!isset($_GET['id'])) {
    die("User ID is required.");
}

$id = intval($_GET['id']); // Sanitize the ID

// Fetch user data from the registration_request table
$query = "
    SELECT 
        r.user_id, 
        r.status_id, 
        r.email 
    FROM registration_request r
    WHERE r.user_id = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Approve the user
if ($user['status_id'] == 0) {
    $stmt = $link->prepare("UPDATE registration_request SET status_id = 1, date_approved = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: user_registration.php?success=1");
        exit();
    } else {
        echo "Error approving user.";
    }
} else {
    echo "User is already approved or declined.";
}
?>
