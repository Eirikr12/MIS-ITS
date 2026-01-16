<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect based on their position
if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
    switch ($_SESSION["position"]) {
        case "admin":
            header("Location: MIS_ADMIN/dashboard.php");
            break;
        case "employee":
            header("Location: MIS_EMPLOYEE/dashboard.php");
            break;
        case "hr":
            header("Location: MIS_HR/dashboard.php");
            break;
        case "tech":
            header("Location: MIS_TECH/dashboard.php");
            break;
        default:
            header("Location: login.php");
            break;
    }
    exit;
}

// Include config file
require_once "src/config.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Check if the user is a temporary account
        $temp_query = "
            SELECT 
                t.temp_user_id, 
                t.temp_username, 
                t.temp_password, 
                t.status_id AS temp_status_id, 
                r.email, 
                r.position_id, 
                r.f_name, 
                r.m_name, 
                r.l_name, 
                r.status_id AS reg_status_id
            FROM temporary_log_in t
            LEFT JOIN registration_request r ON t.temp_user_id = r.user_id
            WHERE BINARY t.temp_username = ?
        ";
        $temp_stmt = $link->prepare($temp_query);
        $temp_stmt->bind_param("s", $username);
        $temp_stmt->execute();
        $temp_stmt->bind_result($temp_id, $temp_username, $temp_password, $temp_status_id, $temp_email, $temp_position, $temp_fname, $temp_mname, $temp_lname, $reg_status_id);

        if ($temp_stmt->fetch()) {
            $temp_stmt->close(); // Close the temporary statement to avoid "Commands out of sync" error

            // Check if the temporary account is approved
            if ($reg_status_id == 0) {
                $login_err = "Your account is pending approval from the admin.";
            } elseif ($reg_status_id == 1 && password_verify($password, $temp_password)) {
                // Check if this is the first login
                if ($temp_status_id == 0) {
                    // Set session variables for temporary accounts
                    $_SESSION["temporary_user_id"] = $temp_id;
                    $_SESSION["temporary_username"] = $temp_username;
                    $_SESSION["temp_position"] = $temp_position;
                    $_SESSION["temp_email"] = $temp_email;
                    $_SESSION["temp_fname"] = $temp_fname;
                    $_SESSION["temp_mname"] = $temp_mname;
                    $_SESSION["temp_lname"] = $temp_lname;

                    // Redirect to change_credentials.php
                    header("Location: change_credentials.php");
                    exit();
                } else {
                    $login_err = "Your account has already been activated. Please contact the admin if you need assistance.";
                }
            } else {
                $login_err = "Invalid username or password.";
            }
        } else {
            $temp_stmt->close(); // Close the temporary statement if no results are found

            // Check if the user is a regular account
            $query = "SELECT id, username, password, position, status FROM user_login WHERE BINARY username = ?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $db_username, $hashed_password, $position, $status);

            if ($stmt->fetch()) {
                $stmt->close(); // Close the statement before executing another query

                if (password_verify($password, $hashed_password)) {
                    // Generate a unique session token
                    $session_token = bin2hex(random_bytes(32));

                    // Update the session token and set status to 1 (active) in the database
                    $update_query = "UPDATE user_login SET user_session_id = ?, status = 1 WHERE id = ?";
                    $update_stmt = $link->prepare($update_query);
                    $update_stmt->bind_param("ss", $session_token, $id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Set session variables
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $db_username;
                    $_SESSION["user_session_id"] = $session_token;
                    $_SESSION["position"] = $position;

                    // Redirect based on the user's position
                    switch ($position) {
                        case "admin": case 1:
                            header("Location: MIS_ADMIN/dashboard.php");
                            break;
                        case "employee": case 4:
                            header("Location: MIS_EMPLOYEE/dashboard.php");
                            break;
                        case "hr": case 3:
                            header("Location: MIS_HR/dashboard.php");
                            break;
                        case "tech": case 2:
                            header("Location: MIS_TECH/dashboard.php");
                            break;
                        default:
                            $login_err = "Invalid user role.";
                            break;
                    }
                    exit();
                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Invalid username or password.";
            }
        }
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="sass/trial.css">
    <script type="text/javascript">
        function preventBack() {
            window.history.forward();
        }

        setTimeout("preventBack()", 0);

        window.onunload = function () {
            null;
        };
    </script>
</head>

<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
        <?php if (!empty($login_err)): ?>
            <div class="error-message">
                <?= htmlspecialchars($login_err) ?>
            </div>
        <?php endif; ?>
        <div class="flex-row">
            <label class="lf--label" for="username">
                <svg x="0px" y="0px" width="12px" height="13px">
                    <path fill="#B1B7C4"
                        d="M8.9,7.2C9,6.9,9,6.7,9,6.5v-4C9,1.1,7.9,0,6.5,0h-1C4.1,0,3,1.1,3,2.5v4c0,0.2,0,0.4,0.1,0.7 C1.3,7.8,0,9.5,0,11.5V13h12v-1.5C12,9.5,10.7,7.8,8.9,7.2z M4,2.5C4,1.7,4.7,1,5.5,1h1C7.3,1,8,1.7,8,2.5v4c0,0.2,0,0.4-0.1,0.6 l0.1,0L7.9,7.3C7.6,7.8,7.1,8.2,6.5,8.2h-1c-0.6,0-1.1-0.4-1.4-0.9L4.1,7.1l0.1,0C4,6.9,4,6.7,4,6.5V2.5z M11,12H1v-0.5 c0-1.6,1-2.9,2.4-3.4c0.5,0.7,1.2,1.1,2.1,1.1h1c0.8,0,1.6-0.4,2.1-1.1C10,8.5,11,9.9,11,11.5V12z" />
                </svg>
            </label>
            <input id="username" name="username"
                class="lf--input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" placeholder="Username"
                type="text" value="<?php echo htmlspecialchars($username); ?>">
        </div>
        <?php if (!empty($username_err)): ?>
            <div class="error-message">
                <?= htmlspecialchars($username_err) ?>
            </div>
        <?php endif; ?>

        <div class="flex-row">
            <label class="lf--label" for="password">
                <svg x="0px" y="0px" width="15px" height="5px">
                    <g>
                        <path fill="#B1B7C4"
                            d="M6,2L6,2c0-1.1-1-2-2.1-2H2.1C1,0,0,0.9,0,2.1v0.8C0,4.1,1,5,2.1,5h1.7C5,5,6,4.1,6,2.9V3h5v1h1V3h1v2h1V3h1 V2H6z M5.1,2.9c0,0.7-0.6,1.2-1.3,1.2H2.1c-0.7,0-1.3-0.6-1.3-1.2V2.1c0-0.7,0.6-1.2,1.3-1.2h1.7c0.7,0,1.3,0.6,1.3,1.2V2.9z" />
                    </g>
                </svg>
            </label>
            <input id="password" name="password"
                class="lf--input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Password"
                type="password">
        </div>
        <?php if (!empty($password_err)): ?>
            <div class="error-message">
                <?= htmlspecialchars($password_err) ?>
            </div>
        <?php endif; ?>
        <input class="lf--submit" type="submit" value="LOGIN">
    </form>
</body>

</html>