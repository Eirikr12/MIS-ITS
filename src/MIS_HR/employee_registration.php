<?php
session_start();
require_once "src/config.php";

// Redirect if not logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("Location: ../login.php");
    exit();
}

// Validate session
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
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Handle new user registration
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $role = trim($_POST['position']);
    $department = trim($_POST['department']);


    $username = strtolower($first_name . "." . $last_name);
    $temp_password = bin2hex(random_bytes(4));
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($role)) {
        // Check if user exists
        $check_query = "SELECT id FROM user_login WHERE email = ? OR username = ?";
        $stmt_check = $link->prepare($check_query);
        $stmt_check->bind_param("ss", $email, $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "A user with this email or username already exists.";
        } else {
            // Insert into user_login
            $insert_query = "INSERT INTO temporary_log_in (temp_username, temp_password, status_id) VALUES (?, ?, 0)";
            $stmt = $link->prepare($insert_query);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $temp_user_id = $stmt->insert_id;
                // Insert into registration_request
                $insert_reg_query = "INSERT INTO registration_request (f_name, m_name, l_name, contact, email, position_id, department_id, date_submitted, status_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0, ?)";
                $stmt_reg = $link->prepare($insert_reg_query);
                $stmt_reg->bind_param("sssssisi", $first_name, $middle_name, $last_name, $contact, $email, $role, $department, $temp_user_id);
                $stmt_reg->execute();
                $stmt_reg->close();

                $message = "User added successfully.<br>Username: <b>$username</b><br>Temporary Password: <b>$temp_password</b>";
            } else {
                $message = "Error adding user.";
            }

            $stmt->close();
        }

        $stmt_check->close();
    } else {
        $message = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-HR - Employee Registration</title>
    <link rel="stylesheet" href="HR Dashboard/style.css" />
    <link rel="stylesheet" href="User Registration/user_registration_table.css" />
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="icon"><ion-icon name="laptop-outline"></ion-icon></span><span class="title">MIS_HR</span></a></li>
                <li><a href="dashboard.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">DASHBOARD</span></a></li>
                <li class="hovered"><a href="employee_registration.php"><span class="icon"><ion-icon name="person-add-outline"></ion-icon></span><span class="title">EMPLOYEE REGISTRATION</span></a></li>
                <li><a href="account_confirmation.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">ACCOUNT CONFIRMATION</span></a></li>
                <li><a href="view_profile.php"><span class="icon"><ion-icon name="person-outline"></ion-icon></span><span class="title">VIEW PROFILE</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">LOG OUT</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="search"><label><input type="text" placeholder="Search here" /><ion-icon name="search-outline"></ion-icon></label></div>
                <div class="user"><img src="../user.jpg" /></div>
            </div>

            <div class="container-content">
                <div class = "content-flex">
                <div class="employee-registration">
                    <div class="cardHeader"><h2>Employee Registration</h2></div>

                    <form method="POST" class="add-user-form">
                        <div class="form-group"><label for="first_name">First Name:</label><input type="text" name="first_name" id="first_name" required /></div>
                        <div class="form-group"><label for="middle_name">Middle Name:</label><input type="text" name="middle_name" id="middle_name" required /></div>
                        <div class="form-group"><label for="last_name">Last Name:</label><input type="text" name="last_name" id="last_name" required /></div>
                        <div class="form-group"><label for="email">Email:</label><input type="email" name="email" id="email" required /></div>
                        <div class="form-group"><label for="contact">Contact Number:</label><input type="number" name="contact" id="contact" required /></div>
                        <div class="form-group">
                            <label for="position">Position:</label>
                            <select name="position" id="position" required>
                                <option value="" disabled selected hidden> Select Position</option>
                                <option value="4">Employee</option>
                                <option value="1">Admin</option>
                                <option value="2">Tech</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department">Department:</label>
                            <select name="department" id="department" required>
                                <option value="" disabled selected hidden> Select Department</option>
                                <option value="1">BSCS</option>
                                <option value="2">BSBA</option>
                                <option value="3">BSIT</option>
                                <option value="4">BSAS</option>
                            </select>
                        </div>
                        
                        <input type="submit" value="Create User">
                    </form>

                    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
                </div>

                <div class="regitration-history">
                    <h2>Registration History</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT r.*, s.status_name, p.position_name, d.department_name 
                                      FROM registration_request r 
                                      JOIN status s ON r.status_id = s.status_id
                                        JOIN position_info p ON r.position_id = p.position_id
                                        JOIN department_info d ON r.department_id = d.department_id
                                      ORDER BY r.date_submitted DESC 
                                      LIMIT 10";
                            $result = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_class = strtolower($row['status_name']);
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) . "</td>
                                        <td>" . htmlspecialchars($row['position_name']) . "</td>
                                        <td>" . htmlspecialchars($row['department_name']) . "</td>
                                        <td>" . htmlspecialchars($row['date_submitted']) . "</td>
                                        <td><span class='badge badge-$status_class'>" . htmlspecialchars($row['status_name']) . "</span></td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
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
