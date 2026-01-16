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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-ITS - User Registration</title>
    <link rel="stylesheet" href="Employee Dashboard/style.css" />
    <link rel="stylesheet" href="User Registration/user_registration_table.css" />

    <script type="text/javascript">
        function preventBack() {
            window.history.forward(); 
        }
        
        setTimeout("preventBack()", 0);
        
        window.onunload = function () { null };
    </script>
    
    <script>
      // Prevent back button navigation
      history.pushState(null, null, location.href);
      window.onpopstate = function () {
        history.pushState(null, null, location.href);
      };
    </script>
</head>

<body>
<div class="container">
      <div class="navigation">
        <ul>
          <li>
            <a href="#">
              <span class="icon"><ion-icon name="laptop-outline"></ion-icon></span>
              <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_EMPLOYEE</span
              >
            </a>
          </li>
          <li class="hovered">
            <a href="dashboard.php">
              <span class="icon"
                ><ion-icon name="home-outline"></ion-icon
              ></span>
              <span class="title">DASHBOARD</span>
            </a>
          </li>
          <li>
            <a href="all_request_list.php">
              <span class="icon"
                ><ion-icon name="person-add-outline"></ion-icon
              ></span>
              <span class="title">ALL REQUEST LIST</span>
            </a>
          </li>
          <li>
            <a href="create_request.php">
              <span class="icon"
                ><ion-icon name="document-text-outline"></ion-icon
              ></span>
              <span class="title">CREATE REQUEST</span>
            </a>
          </li>
          <li>
            <a href="view_profile.php">
              <span class="icon"
                ><ion-icon name="person-outline"></ion-icon
              ></span>
              <span class="title">VIEW PROFILE</span>
            </a>
          </li>
          <li>
            <a href="logout.php">
              <span class="icon"
                ><ion-icon name="log-out-outline"></ion-icon
              ></span>
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

            <div class="user-approval-list">
                <div class="cardHeader">
                    <h2>User Registration List</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="userList">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="User Registration/user_approval.js"></script>
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