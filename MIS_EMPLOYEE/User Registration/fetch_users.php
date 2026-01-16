<?php
// filepath: c:\xampp\htdocs\MIS_ITS\MIS_HR\User Registration\fetch_users.php
require_once "../../src/config.php"; // Database connection

header("Content-Type: application/json");

// Fetch user data by joining `user_login` and `approval_status` tables
$query = "
    SELECT 
        user_login.id,
        user_login.username AS first_name,
        user_login.email,
        user_login.position AS role,
        CASE 
            WHEN approval_status.status = 0 THEN 'Pending'
            WHEN approval_status.status = 1 THEN 'Approved'
            ELSE 'Unknown'
        END AS status
    FROM user_login
    LEFT JOIN approval_status ON user_login.id = approval_status.user_id
";

$result = $link->query($query);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);
?>