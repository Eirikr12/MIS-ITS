<?php
require_once "src/config.php";

if (!isset($_GET['id'])) {
    die("User ID is required.");
}

$id = $_GET['id'];

// Fetch user data
$stmt = $link->prepare("SELECT username, email, position, status FROM user_login WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    $stmt = $link->prepare("UPDATE user_login SET username = ?, email = ?, position = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssii", $username, $email, $position, $status, $id);
    
    if ($stmt->execute()) {
        header("Location: user_registration.php");
        exit();
    } else {
        echo "Error updating record.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        form { max-width: 400px; margin: auto; padding: 20px; }
        label, input, select { display: block; width: 100%; margin-bottom: 10px; }
        button { padding: 8px 15px; }
    </style>
</head>
<body>
    <h2>Edit User</h2>
    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required />

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />

        <label>Position:</label>
        <input type="text" name="position" value="<?= htmlspecialchars($user['position']) ?>" required />

        <label>Status:</label>
        <select name="status" required>
            <option value="0" <?= $user['status'] == 0 ? 'selected' : '' ?>>Pending</option>
            <option value="1" <?= $user['status'] == 1 ? 'selected' : '' ?>>Approved</option>
        </select>

        <button type="submit">Update</button>
        <a href="user_registration.php">Cancel</a>
    </form>
</body>
</html>
