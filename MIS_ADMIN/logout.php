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
    <link rel="stylesheet" href="logout.css">
    <script type="text/javascript">
        // Prevent back button navigation
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
    </script>
</head>
<body onload="noBack();">
    <div class="logout-container">
        <div class="logout-message">
            <div class="spinner"></div>
            <h1>Logging Out...</h1>
            <p>You have been logged out. Redirecting to login...</p>
        </div>
    </div>
    <script>
        // Redirect to the correct login.php file outside MIS_EMPLOYEE after 2 seconds
        setTimeout(function () {
            window.location.href = "../login.php"; // Corrected path
        }, 2000);
    </script>
</body>
</html>

<style>
/* General styling for the logout page */
body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(135deg, #007bff, #0056b3);
    font-family: "Roboto", sans-serif;
    color: #fff;
}

/* Container for the logout message */
.logout-container {
    text-align: center;
    animation: fadeIn 1s ease-in-out;
}

/* Spinner animation */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-top: 5px solid #fff;
    border-radius: 50%;
    margin: 20px auto;
    animation: spin 1s linear infinite;
}

/* Heading and paragraph styling */
.logout-message h1 {
    font-size: 2em;
    margin: 10px 0;
}

.logout-message p {
    font-size: 1.2em;
    margin: 5px 0;
    opacity: 0.8;
}

/* Keyframes for fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Keyframes for spinner rotation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>