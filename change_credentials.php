<?php
session_start();
require_once "src/config.php";

// Check if the user is a temporary account
if (!isset($_SESSION["temporary_user_id"]) || !isset($_SESSION["temporary_username"]) || !isset($_SESSION["temp_position"])) {
    header("Location: login.php");
    exit();
}

$temp_id = $_SESSION["temporary_user_id"];
$temp_username = $_SESSION["temporary_username"];
$temp_position = $_SESSION["temp_position"];

// Define variables and initialize with empty values
$new_username = $new_password = $confirm_password = "";
$new_username_err = $new_password_err = $confirm_password_err = $update_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate new username
    if (empty(trim($_POST["new_username"]))) {
        $new_username_err = "Please enter a new username.";
    } else {
        $new_username = trim($_POST["new_username"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($new_password !== $confirm_password) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Check for errors before updating
    if (empty($new_username_err) && empty($new_password_err) && empty($confirm_password_err)) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Retrieve temporary account details
        $query = "SELECT registration_request.f_name, registration_request.m_name, registration_request.l_name, 
                         registration_request.email, registration_request.contact, registration_request.department_id 
                  FROM registration_request 
                  WHERE user_id = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("i", $temp_id);
        $stmt->execute();
        $stmt->bind_result($fname, $mname, $lname, $email, $contact, $department);
        if ($stmt->fetch()) {
            $stmt->close();

            // Get current date and time
            $current_date = date("Y-m-d"); // Format: YYYY-MM-DD
            $current_time = date("H:i:s"); // Format: HH:MM:SS

            // Insert into user_login table
            $insert_user_query = "INSERT INTO user_login (username, password, email, position, department, status, date_reg, user_session_id) VALUES (?, ?, ?, ?, ?, 1, ?, '')";
            $stmt = $link->prepare($insert_user_query);
            $stmt->bind_param("ssssss", $new_username, $hashed_password, $email, $temp_position, $department, $current_date);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; // Get the newly inserted user ID
                $stmt->close();

                // Insert into employee_info table
                $default_display_picture = ""; // Default value for display_picture
                $insert_employee_query = "INSERT INTO employee_info (id, employee_fname, employee_mname, employee_lname, employee_contact, position_id, department_id, reg_status, display_picture) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', ?)";
                $stmt = $link->prepare($insert_employee_query);
                $stmt->bind_param("issssiss", $user_id, $fname, $mname, $lname, $contact, $temp_position, $department, $default_display_picture);
                $stmt->execute();
                $stmt->close();

                // Delete the temporary account
                $delete_temp_query = "DELETE FROM temporary_log_in WHERE temp_user_id = ?";
                $stmt = $link->prepare($delete_temp_query);
                $stmt->bind_param("i", $temp_id);
                $stmt->execute();
                $stmt->close();

                // Redirect to login page with success message
                session_destroy();
                header("Location: login.php?success=1");
                exit();
            } else {
                $update_err = "Error updating account. Please try again.";
            }
        } else {
            $update_err = "Temporary account not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Credentials</title>
    <link rel="stylesheet" href="sass/trial.css">
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
        <h4>Change Username & Password</h4>
        <div class="flex-row">
            <label class="lf--label" for="new_username">
                <svg x="0px" y="0px" width="12px" height="13px">
                    <path fill="#B1B7C4"
                        d="M8.9,7.2C9,6.9,9,6.7,9,6.5v-4C9,1.1,7.9,0,6.5,0h-1C4.1,0,3,1.1,3,2.5v4c0,0.2,0,0.4,0.1,0.7 C1.3,7.8,0,9.5,0,11.5V13h12v-1.5C12,9.5,10.7,7.8,8.9,7.2z M4,2.5C4,1.7,4.7,1,5.5,1h1C7.3,1,8,1.7,8,2.5v4c0,0.2,0,0.4-0.1,0.6 l0.1,0L7.9,7.3C7.6,7.8,7.1,8.2,6.5,8.2h-1c-0.6,0-1.1-0.4-1.4-0.9L4.1,7.1l0.1,0C4,6.9,4,6.7,4,6.5V2.5z M11,12H1v-0.5 c0-1.6,1-2.9,2.4-3.4c0.5,0.7,1.2,1.1,2.1,1.1h1c0.8,0,1.6-0.4,2.1-1.1C10,8.5,11,9.9,11,11.5V12z" />
                </svg>
            </label>
            <input id="new_username" name="new_username"
                class="lf--input <?php echo (!empty($new_username_err)) ? 'is-invalid' : ''; ?>" placeholder="New Username"
                type="text" value="<?php echo $new_username; ?>" required>
            <span class="invalid-feedback"><?php echo $new_username_err; ?></span>
        </div>
        <div class="flex-row">
            <label class="lf--label" for="new_password">
                <svg x="0px" y="0px" width="15px" height="5px">
                    <g>
                        <path fill="#B1B7C4"
                            d="M6,2L6,2c0-1.1-1-2-2.1-2H2.1C1,0,0,0.9,0,2.1v0.8C0,4.1,1,5,2.1,5h1.7C5,5,6,4.1,6,2.9V3h5v1h1V3h1v2h1V3h1 V2H6z M5.1,2.9c0,0.7-0.6,1.2-1.3,1.2H2.1c-0.7,0-1.3-0.6-1.3-1.2V2.1c0-0.7,0.6-1.2,1.3-1.2h1.7c0.7,0,1.3,0.6,1.3,1.2V2.9z" />
                    </g>
                </svg>
            </label>
            <input id="new_password" name="new_password"
                class="lf--input <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" placeholder="New Password"
                type="password" required>
            <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
        </div>
        <div class="flex-row">
            <label class="lf--label" for="confirm_password">
                <svg x="0px" y="0px" width="15px" height="5px">
                    <g>
                        <path fill="#B1B7C4"
                            d="M6,2L6,2c0-1.1-1-2-2.1-2H2.1C1,0,0,0.9,0,2.1v0.8C0,4.1,1,5,2.1,5h1.7C5,5,6,4.1,6,2.9V3h5v1h1V3h1v2h1V3h1 V2H6z M5.1,2.9c0,0.7-0.6,1.2-1.3,1.2H2.1c-0.7,0-1.3-0.6-1.3-1.2V2.1c0-0.7,0.6-1.2,1.3-1.2h1.7c0.7,0,1.3,0.6,1.3,1.2V2.9z" />
                    </g>
                </svg>
            </label>
            <input id="confirm_password" name="confirm_password"
                class="lf--input <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm Password"
                type="password" required>
            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
        </div>
        <input class="lf--submit" type="submit" value="Update Credentials">
        <?php if (!empty($update_err)) echo "<p>$update_err</p>"; ?>
    </form>
</body>
</html>