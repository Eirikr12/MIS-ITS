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

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-TECH</title>
    <link rel="stylesheet" href="Tech Dashboard/style.css" />

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
              <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_TECH</span>
            </a>
          </li>
          <li class="hovered">
            <a href="dashboard.php">
              <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
              <span class="title">DASHBOARD</span>
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
              <div class="numbers">54</div>
              <div class="cardName">Daily Completed</div>
            </div>
            <div class="iconBx">
              <ion-icon name="checkmark-done-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers">80</div>
              <div class="cardName">Work Loads</div>
            </div>
            <div class="iconBx">
              <ion-icon name="clipboard-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers">284</div>
              <div class="cardName">Requests</div>
            </div>
            <div class="iconBx">
              <ion-icon name="document-text-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers">7,842</div>
              <div class="cardName">Total Monthly Completed</div>
            </div>
            <div class="iconBx">
              <ion-icon name="trending-up-outline"></ion-icon>
            </div>
          </div>
        </div>
        

        <div class="details">
          <div class="recentOrders">
            <div class="cardHeader">
              <h2>Request Status</h2>
              
            </div>
            <table>
              <thead>
                <tr>
                  <td>Name</td>
                  <td>Date</td>
                  <td>Time</td>
                  <td>Status</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Monkey D. Luffy</td>
                  <td>02-04-2025</td>
                  <td>7:22am</td>
                  <td><span class="status delivered">Completed</span></td>
                </tr>
                <tr>
                  <td>Roronoa Zoro</td>
                  <td>02-08-2025</td>
                  <td>2:30pm</td>
                  <td><span class="status pending">Pending</span></td>
                </tr>
                <tr>
                  <td>Naruto Uzumaki</td>
                  <td>03-02-2025</td>
                  <td>3:30pm</td>
                  <td><span class="status pending">Pending</span></td>
                </tr>
                <tr>
                  <td>Ichigo Kurosaki</td>
                  <td>02-32-2025</td>
                  <td>8:00am</td>
                  <td><span class="status inprogress">In Progress</span></td>
                </tr>
                <tr>
                  <td>Sung Jinwoo</td>
                  <td>03-31-2025</td>
                  <td>10:00am</td>
                  <td><span class="status delivered">Completed</span></td>
                </tr>
                <tr>
                  <td>Taro Sakamoto</td>
                  <td>02-31-2025</td>
                  <td>1:00pm</td>
                  <td><span class="status inprogress">In Progress</span></td>
                </tr>
                <tr>
                  <td>Angelo Ecleo</td>
                  <td>02-23-2025</td>
                  <td>12:00pm</td>
                  <td><span class="status delivered">Completed</span></td>
                </tr>
                <tr>
                  <td>Vincent</td>
                  <td>13-23-2025</td>
                  <td>09:00am</td>
                  <td><span class="status inprogress">In Progress</span></td>
                </tr>
                <tr>
                  <td>Kenneth</td>
                  <td>09-23-2025</td>
                  <td>3:02pm</td>
                  <td><span class="status pending">Pending</span></td>
                </tr>
                <tr>
                  <td>Jasper</td>
                  <td>09-23-2025</td>
                  <td>20:30pm</td>
                  <td><span class="status pending">Pending</span></td>
                </tr>
                <tr>
                  <td>Febe</td>
                  <td>02-23-2025</td>
                  <td>02:21pm</td>
                  <td><span class="status inprogress">In Progress</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="recentCustomers">
            <div class="cardHeader">
              <h2>Recent Requests</h2>
            </div>
            <table>
              <tr>
                <td width="60px">
                  <div class="imgBx"><img src="img1.jpg" /></div>
                </td>
                <td>
                  <h4>Angelo Ecleo<br /><span>CCS Dept</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img2.jpg" /></div>
                </td>
                <td>
                  <h4>JM De Leon<br /><span>Nursing Dept</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img3.jpg" /></div>
                </td>
                <td>
                  <h4>Michael Nolasco<br /><span>CBAA Dept</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img4.jpg" /></div>
                </td>
                <td>
                  <h4>Jobert Aplacador<br /><span>Marine</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img5.jpg" /></div>
                </td>
                <td>
                  <h4>wfwfwfw<br /><span>wfwfwfw</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img6.jpg" /></div>
                </td>
                <td>
                  <h4>wfwfwf<br /><span>wfwfw</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img7.jpg" /></div>
                </td>
                <td>
                  <h4>wfwfw<br /><span>wfwfwfw</span></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="imgBx"><img src="img8.jpg" /></div>
                </td>
                <td>
                  <h4>wfwfw<br /><span>feffef</span></h4>
                </td>
              </tr>
            </table>
          </div>
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

        // Add a script to fetch and update data dynamically
        function fetchDashboardData() {
            fetch('api/dashboard_data.php') // API endpoint to fetch data
                .then(response => response.json())
                .then(data => {
                    // Update card data
                    document.querySelector('.card:nth-child(1) .numbers').textContent = data.dailyCompleted;
                    document.querySelector('.card:nth-child(2) .numbers').textContent = data.workLoads;
                    document.querySelector('.card:nth-child(3) .numbers').textContent = data.requests;
                    document.querySelector('.card:nth-child(4) .numbers').textContent = data.totalMonthlyCompleted;
                })
                .catch(error => console.error('Error fetching dashboard data:', error));
        }

        // Fetch data every 10 seconds
        setInterval(fetchDashboardData, 10000);
        fetchDashboardData(); // Initial fetch
    </script>
  </body>
</html>
