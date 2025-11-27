<?php
// Initialize the session
session_start();

// Include config file
require_once "src/config.php";

// Check if the user is logged in
if (isset($_SESSION["id"])) {
    // Get the user ID from the session
    $user_id = $_SESSION["id"];

    // Clear the user_session_id and set status to 0 in the database
    $query = "UPDATE user_login SET user_session_id = NULL, status = 0 WHERE id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Destroy the session
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script type="text/javascript">
        // Prevent back button navigation
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
    </script>
</head>
<body onload="noBack();">
    <p>You have been logged out. Redirecting to login...</p>
    <script>
        // Redirect to the correct login.php file outside MIS_EMPLOYEE after 2 seconds
        setTimeout(function () {
            window.location.href = "../login.php"; // Corrected path
        }, 2000);
    </script>
</body>
</html>