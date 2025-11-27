<?php
session_start();
require_once "src/config.php";

// Optional: Check if the user is admin
// if ($_SESSION['position'] !== 'admin') {
//     header("Location: unauthorized.php");
//     exit();
// }

if (isset($_GET['id'])) {
    $temp_id = intval($_GET['id']); // Sanitize the temporary user ID

    // Step 1: Fetch the temporary user's details
    $temp_query = "SELECT temporary_username, temporary_email, temporary_position 
                   FROM temporary_login WHERE temporary_id = ?";
    $temp_stmt = $link->prepare($temp_query);
    $temp_stmt->bind_param("i", $temp_id);
    $temp_stmt->execute();
    $temp_stmt->bind_result($username, $email, $position);
    $temp_stmt->fetch();
    $temp_stmt->close();

    if (!empty($username)) {
        // Step 2: Update the temporary user's status to "Approved" (status = 1)
        $update_temp_query = "UPDATE temporary_login SET temporary_status = 1 WHERE temporary_id = ?";
        $update_temp_stmt = $link->prepare($update_temp_query);
        $update_temp_stmt->bind_param("i", $temp_id);

        if ($update_temp_stmt->execute()) {
            $update_temp_stmt->close();

            // Redirect back to user_registration.php with a success message
            header("Location: user_registration.php?msg=approved");
            exit();
        } else {
            echo "Error updating temporary user status: " . $update_temp_stmt->error;
            exit();
        }
    } else {
        echo "Temporary user not found.";
        exit();
    }
}
?>
