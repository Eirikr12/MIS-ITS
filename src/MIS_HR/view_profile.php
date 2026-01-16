<?php
session_start();
require_once "src/config.php"; // Ensure this file connects to the database

// If user is not logged in, redirect to login page
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
  header("Location: ../login.php");
  exit();
}

// Validate the session token
$user_id = $_SESSION["id"];
$session_token = $_SESSION["user_session_id"];

$query = "SELECT user_session_id FROM user_login WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($db_user_session_id);
$stmt->fetch();
$stmt->close();

if ($db_user_session_id !== $session_token) {
    // If the session token doesn't match, log the user out
    session_destroy();
    header("Location: login.php");
    exit();
}

// Prevent back button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

require_once "src/config.php"; // Ensure this file exists and connects to mis_db

$user_session_id = $_SESSION['id'];

// Fetch user details from user_login table
$query = "SELECT username, email, position FROM user_login WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_session_id);
$stmt->execute();
$stmt->bind_result($username, $email, $position);
$stmt->fetch();
$stmt->close();

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_position = trim($_POST['position']);

    if (!empty($new_username) && !empty($new_email) && !empty($new_position)) {
        $update_query = "UPDATE user_login SET username = ?, email = ?, position = ? WHERE id = ?";
        $update_stmt = $link->prepare($update_query);
        $update_stmt->bind_param("ssss", $new_username, $new_email, $new_position, $user_session_id);

        if ($update_stmt->execute()) {
            $message = "Profile updated successfully.";
            $username = $new_username;
            $email = $new_email;
            $position = $new_position;
        } else {
            $message = "Error updating profile.";
        }
        $update_stmt->close();
    } else {
        $message = "Fields cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-HR - View Profile</title>
    <link rel="stylesheet" href="HR Dashboard/style.css" />
    <link rel="stylesheet" href="View Profile/view_profile.css" />
</head>

<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><ion-icon name="laptop-outline"></ion-icon></span>
                        <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_HR</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="employee_registration.php">
                        <span class="icon"><ion-icon name="person-add-outline"></ion-icon></span>
                        <span class="title">EMPLOYEE REGISTRATION</span>
                    </a>
                </li>
                <li>
                    <a href="account_confirmation.php">
                        <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
                        <span class="title">ACCOUNT CONFIRMATION</span>
                    </a>
                </li>
                
                <li class="hovered">
                    <a href="view_profile.php">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="title">VIEW PROFILE</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="title">LOG OUT</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="search">
                    <label>
                        <input type="text" placeholder="Search here" />
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>
                <div class="user">
                    <img src="../user.jpg" />
                </div>
            </div>

            <div class="profile-container">
                <div class="cardHeader">
                    <h2>Profile</h2>
                </div>
                <form method="POST">

          <div class="profile-info">
              <label for="adminName">Username:</label>
              <input type="text" name="username" id="adminName" value="<?php echo htmlspecialchars($username); ?>" disabled />
          </div>

          <div class="profile-info">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled />
          </div>

          <div class="profile-info">
            <label for="position">Position:</label>
            <input type="text" name="position" id="position" value="<?php echo htmlspecialchars($position); ?>" disabled />
          </div>

            </form>

            <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
            
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="View Profile/view_profile.js"></script>
    <script>
        let toggle = document.querySelector(".toggle");
        let navigation = document.querySelector(".navigation");
        let main = document.querySelector(".main");

        toggle.onclick = function () {
            navigation.classList.toggle("active");
            main.classList.toggle("active");
        };
        let list = document.querySelectorAll(".navigation li");
        function activeLink() {
            list.forEach((item) => item.classList.remove("hovered"));
            this.classList.add("hovered");
        }
        list.forEach((item) => item.addEventListener("mouseover", activeLink));
    </script>
</body>
</html>