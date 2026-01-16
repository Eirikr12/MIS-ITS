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

// Handle form submission for creating a new request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $request_description = trim($_POST['request_description']);
  
  $date_requested = date("Y-m-d"); // Current date
  $request_status = "Pending"; // Default status for new requests
  $id = $_SESSION['id']; // Get the logged-in user's ID from the session

  if (!empty($request_description) ) {
    // Define the SQL query for inserting a new request
    $insert_query = "INSERT INTO request_info (request_id, id, request_description,request_date, request_status) VALUES (?, ?, ?, ?, ?)";
    $request_id = uniqid("REQ_"); // Generate a unique request ID
    $stmt = $link->prepare($insert_query);
    $stmt->bind_param("sssss", $request_id, $id, $request_description,$date_requested, $request_status);

    if ($stmt->execute()) {
      $message = "Request created successfully.";
    } else {
      $message = "Error creating request.";
    }
    $stmt->close();
  } else {
    $message = "All fields are required.";
  }
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
  <title>MIS-EMPLOYEE - Create Request</title>
  <link rel="stylesheet" href="Employee Dashboard/style.css" />
  <link rel="stylesheet" href="Request Status/request_status.css" />

  <script>
    // Prevent back button navigation
    window.history.forward();

    function noBack() {
      window.history.forward();
    }
  </script>

  <script>
    // Prevent back button navigation
    history.pushState(null, null, location.href);
    window.onpopstate = function() {
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
            <span class="icon">
              <ion-icon name="laptop-outline"></ion-icon>
            </span>
            <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_EMPLOYEE</span>
          </a>
        </li>
        <li class="hovered">
          <a href="dashboard.php">
            <span class="icon">
              <ion-icon name="home-outline"></ion-icon>
            </span>
            <span class="title">DASHBOARD</span>
          </a>
        </li>
        <li>
          <a href="all_request_list.php">
            <span class="icon">
              <ion-icon name="person-add-outline"></ion-icon>
            </span>
            <span class="title">ALL REQUEST LIST</span>
          </a>
        </li>
        <li>
          <a href="create_request.php">
            <span class="icon">
              <ion-icon name="document-text-outline"></ion-icon>
            </span>
            <span class="title">CREATE REQUEST</span>
          </a>
        </li>
        <li>
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

      <div class="form-container">
        <h2>Create Request</h2>
        <form method="POST">

          <div class="form-group">
            <label for="request_description">Request Description:</label>
            <textarea name="request_description" id="request_description" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn">Submit Request</button>
        </form>
        <?php if (isset($message)) {
          echo "<p>$message</p>";
        } ?>
      </div>
    </div>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <script src="Request Status/request_status.js"></script>
  <script>
    let toggle = document.querySelector(".toggle");
    let navigation = document.querySelector(".navigation");
    let main = document.querySelector(".main");

    toggle.onclick = function() {
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