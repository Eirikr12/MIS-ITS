<?php
session_start();
require_once "src/config.php"; // Ensure this file connects to the database

// If user is not logged in, redirect to login page
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("location: ../../login.php");
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
    header("location: ../../login.php");
    exit();
}



// Prevent back button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
?>

<?php
date_default_timezone_set('Asia/Manila'); // Change to your timezone
$today_date = date('Y-m-d');

$query = "
    SELECT 
        SUM(CASE WHEN status_id = '1' THEN 1 ELSE 0 END) AS approved_requests,
        SUM(CASE WHEN status_id = '0' THEN 1 ELSE 0 END) AS pending_requests,
        SUM(CASE WHEN status_id = '2' THEN 1 ELSE 0 END) AS declined_requests,
        COUNT(*) AS total_requests,
        SUM(CASE WHEN DATE(date_submitted) = ? THEN 1 ELSE 0 END) AS today_requests
    FROM registration_request
";

$approved_requests = $pending_requests = $declined_requests = $total_registration = $today_requests = 0;

$stmt = $link->prepare($query);
$stmt->bind_param("s", $today_date);
if ($stmt->execute()) {
    $stmt->bind_result(
        $approved_requests, 
        $pending_requests, 
        $declined_requests, 
        $total_registration,
        $today_requests
    );
    $stmt->fetch();
    $stmt->close();
} else {
    die("Query failed: " . $stmt->error);
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-HR</title>
    <link rel="stylesheet" href="HR Dashboard/style.css" />

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
              <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_HR</span>
            </a>
          </li>
          <li class="hovered">
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
          <li>
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

      <!-- main -->
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
            <img src="user.jpg" />
          </div>
        </div>

        <div class="cardBox">
          <div class="card">
            <div>
              <div class="numbers"><?php echo $today_requests; ?></div>
              <div class="cardName">Today's Registration</div>
              <div class="cardName">Total Registration: <?php echo $total_registration?></div>

            </div>
            <div class="iconBx">
              <ion-icon name="checkmark-done-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $approved_requests; ?></div>
              <div class="cardName">Approved Request</div>
            </div>
            <div class="iconBx">
              <ion-icon name="clipboard-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $pending_requests; ?></div>
              <div class="cardName">Pending Request</div>
            </div>
            <div class="iconBx">
              <ion-icon name="document-text-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $declined_requests; ?></div>
              <div class="cardName">Request Declined</div>
            </div>
            <div class="iconBx">
              <ion-icon name="trending-up-outline"></ion-icon>
            </div>
          </div>
        </div>
        
        <div class="dash_tables">
          <h2>Employee Registration Requests</h2>
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
        <div class="dash_tables">
          <h2>Pending Approvals</h2>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Date Requested</th>
              </tr>
            </thead>
            <tbody>
            <?php
                $query = "SELECT r.*, s.status_name, p.position_name, d.department_name 
                FROM registration_request r 
                JOIN status s ON r.status_id = s.status_id
                  JOIN position_info p ON r.position_id = p.position_id
                  JOIN department_info d ON r.department_id = d.department_id
                WHERE r.status_id = '0'
                ORDER BY r.date_submitted DESC 
                LIMIT 10";
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>
                          <td>" . htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) . "</td>
                          
                          <td>{$row['position_name']}</td>

                          <td>{$row['department_name']}</td>
                          
                          <td>{$row['date_submitted']}</td>
                          
                          </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
        <div class="dash_tables">
          <h2>Declined Request</h2>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Date Declined</th>
              </tr>
            </thead>
            <tbody>
            <?php
                $query = "SELECT r.*, s.status_name, p.position_name, d.department_name 
                FROM registration_request r 
                JOIN status s ON r.status_id = s.status_id
                  JOIN position_info p ON r.position_id = p.position_id
                  JOIN department_info d ON r.department_id = d.department_id
                WHERE r.status_id = '2'
                ORDER BY r.date_submitted DESC 
                LIMIT 10";
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>
                          <td>" . htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) . "</td>

                          <td>{$row['position_name']}</td>

                          <td>{$row['department_name']}</td>
                          
                          <td>{$row['date_declined']}</td>
                          
                          </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
        <div class="dash_tables">
          <h2>Employee Registration History</h2>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>Date Approved</th>
              </tr>
            </thead>
            <tbody>
            <?php
                $query = "SELECT r.*, s.status_name, p.position_name, d.department_name 
                FROM registration_request r 
                JOIN status s ON r.status_id = s.status_id
                  JOIN position_info p ON r.position_id = p.position_id
                  JOIN department_info d ON r.department_id = d.department_id
                WHERE r.status_id = '1'
                ORDER BY r.date_submitted DESC 
                LIMIT 10";
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>
                          <td>" . htmlspecialchars($row['f_name'] . " " . $row['m_name'] . " " . $row['l_name']) . "</td>
                          
                          <td>{$row['position_name']}</td>

                          <td>{$row['department_name']}</td>
                          
                          <td>{$row['date_approved']}</td>
                          
                          </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
        
      </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="Admin Dashboard/my_chart.js"></script>
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
