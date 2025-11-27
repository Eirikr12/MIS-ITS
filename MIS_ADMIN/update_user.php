<?php
session_start();
require_once "src/config.php";

// ✅ Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $position = $_POST['position'];
    $status = $_POST['status'];

    // ✅ Basic validation
    if (!empty($user_id) && !empty($username) && !empty($email) && !empty($position)) {
        // 🛠️ Update query
        $query = "UPDATE user_login SET username = ?, email = ?, position = ?, status = ? WHERE id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("sssii", $username, $email, $position, $status, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: user_registration.php?msg=updated");
            exit();
        } else {
            echo "❌ Error updating user: " . $stmt->error;
        }
    } else {
        echo "❗ All fields are required.";
    }
} else {
    echo "⚠️ Invalid request method.";
}
?>
