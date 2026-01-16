<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

require_once "src/config.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    $sql = "UPDATE user_login SET username=?, email=?, position=?, status=? WHERE user_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $position, $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            echo "Employee updated successfully!";
        } else {
            echo "Error: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($link);
}

// Fetch employee data
$employee = ["user_id" => "", "username" => "", "email" => "", "position" => "", "status" => ""];
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM user_login WHERE user_id=?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result->num_rows > 0) {
            $employee = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Employee</title>
</head>
<body>
    <h2>Update Employee</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $employee['user_id']; ?>">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $employee['username']; ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $employee['email']; ?>" required><br>
        <label>Position:</label>
        <input type="text" name="position" value="<?php echo $employee['position']; ?>" required><br>
        <label>Status:</label>
        <select name="status">
            <option value="1" <?php if ($employee['status'] == 1) echo "selected"; ?>>Active</option>
            <option value="0" <?php if ($employee['status'] == 0) echo "selected"; ?>>Inactive</option>
        </select><br>
        <button type="submit">Update Employee</button>
    </form>
</body>
</html>
