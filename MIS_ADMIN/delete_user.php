<?php
require_once "src/config.php";

if (!isset($_GET['id'])) {
    die("User ID is required.");
}

$id = intval($_GET['id']); // Sanitize the ID

// Fetch user data from the registration_request table
$query = "
    SELECT 
        r.user_id, 
        r.status_id 
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

// Decline the user
if ($user['status_id'] == 0) {
    $stmt = $link->prepare("UPDATE registration_request SET status_id = 2, date_declined = NOW() WHERE user_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: user_registration.php?msg=declined");
        exit();
    } else {
        echo "Error declining user.";
    }
} else {
    echo "User is already approved or declined.";
}
?>
