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

$user_session_id = $_SESSION['id'];

// Fetch user details including the position name and department name
$query = "
  SELECT 
    ul.username, 
    ul.email, 
    p.position_name AS position, 
    d.department_name AS department, 
    ei.employee_fname, 
    ei.employee_mname, 
    ei.employee_lname, 
    ei.display_picture 
  FROM 
    user_login ul
  LEFT JOIN 
    employee_info ei 
  ON 
    ul.id = ei.id 
  LEFT JOIN 
    position_info p 
  ON 
    ei.position_id = p.position_id 
  LEFT JOIN 
    department_info d 
  ON 
    ei.department_id = d.department_id 
  WHERE 
    ul.id = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_session_id);
$stmt->execute();
$stmt->bind_result($username, $email, $position, $department, $first_name, $middle_name, $last_name, $display_picture);
$stmt->fetch();
$stmt->close();

// If no display picture is set, use a default image
$profile_photo = !empty($display_picture) ? "uploads/" . $display_picture : "../default_user.jpg";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-ADMIN - View Profile</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css" />
    <link rel="stylesheet" href="View Profile/view_profile.css" />

    <script type="text/javascript">
        function preventBack() {
            window.history.forward();
        }

        setTimeout("preventBack()", 0);

        window.onunload = function () {
            null
        };
    </script>
</head>

<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="laptop-outline"></ion-icon>
                        </span>
                        <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_ADMIN</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="user_registration.php">
                        <span class="icon">
                            <ion-icon name="person-add-outline"></ion-icon>
                        </span>
                        <span class="title">USER REGISTRATION</span>
                    </a>
                </li>
                <li>
                    <a href="request_status.php">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">REQUEST STATUS</span>
                    </a>
                </li>
                <li>
                    <a href="work_loads.php">
                        <span class="icon">
                            <ion-icon name="clipboard-outline"></ion-icon>
                        </span>
                        <span class="title">WORK LOADS</span>
                    </a>
                </li>
                <li class="hovered">
                    <a href="view_profile.php">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">VIEW PROFILE</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
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
                <div class="user">
                    <img src="../user.jpg" />
                </div>
            </div>

            <div class="profile-container">
                <div class="cardHeader">
                    <h2>Profile</h2>
                </div>

                <div class="profile-details">
                    <div class="profile-info-container">
                        <!-- Left side: User information -->
                        <form method="POST" enctype="multipart/form-data">
                            <div class="profile-info">
                                <label for="username">Username:</label>
                                <input type="text" name="username" id="username"
                                    value="<?php echo htmlspecialchars($username); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email"
                                    value="<?php echo htmlspecialchars($email); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="position">Position:</label>
                                <input type="text" name="position" id="position"
                                    value="<?php echo htmlspecialchars($position); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="department">Department:</label>
                                <input type="text" name="department" id="department"
                                    value="<?php echo htmlspecialchars($department); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="first_name">First Name:</label>
                                <input type="text" name="first_name" id="first_name"
                                    value="<?php echo htmlspecialchars($first_name); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="middle_name">Middle Name:</label>
                                <input type="text" name="middle_name" id="middle_name"
                                    value="<?php echo htmlspecialchars($middle_name); ?>" readonly />
                            </div>

                            <div class="profile-info">
                                <label for="profile_photo">Profile Photo:</label>
                                <input type="file" name="profile_photo" id="profile_photo" accept="image/*" />
                            </div>

                            <button type="submit" name="save_changes">Save Changes</button>
                            <button type="submit" name="remove_photo">Remove Photo</button>
                        </form>
                    </div>

                  
                    <!-- Right side: User photo -->
                    <div class="profile-photo-container">
                        <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="User Photo" id="user-photo" />
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>