<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

require_once "src/config.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $password = password_hash("default123", PASSWORD_DEFAULT); // Default password for new users
    $status = 1; // Active by default

    $sql = "INSERT INTO user_login (user_id, username, email, position, password, status) VALUES (UUID(), ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $position, $password, $status);
        if (mysqli_stmt_execute($stmt)) {
            echo "Employee added successfully!";
        } else {
            echo "Error: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employee</title>
</head>
<body>
    <h2>Add Employee</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Position:</label>
        <input type="text" name="position" required><br>
        <button type="submit">Add Employee</button>
    </form>
</body>
</html>
