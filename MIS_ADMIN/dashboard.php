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
    <title>MIS-ADMIN</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css" />

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

<script>
// Enhanced Real-Time Dashboard Updater
function updateDashboard() {
    fetch('get_counts.php')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            // 1. Update All Counts
            const counters = {
                'daily-count': data.daily_completed,
                'monthly-count': data.monthly_completed,
                'workloads-count': data.workloads_count,
                'requests-count': data.requests_count
            };

            Object.keys(counters).forEach(id => {
                const element = document.getElementById(id);
                if (element) element.textContent = counters[id];
            });

            // 2. Update Recent Requests Table
            const tableBody = document.querySelector('.recentOrders tbody');
            if (tableBody && data.recent_requests) {
                tableBody.innerHTML = data.recent_requests.map(request => `
                    <tr>
                        <td>${request.employee || 'N/A'}</td>
                        <td>${request.date || 'N/A'}</td>
                        <td>${request.description || 'No description'}</td>
                        <td><span class="status ${request.status ? request.status.toLowerCase() : 'pending'}">
                            ${request.status ? request.status.charAt(0).toUpperCase() + request.status.slice(1) : 'Pending'}
                        </span></td>
                    </tr>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Dashboard update failed:', error);
            // Fallback: Manual reload option
            alert('Auto-update failed. Click OK to reload...');
            window.location.reload();
        });
}

// Initialize with faster first load
document.addEventListener('DOMContentLoaded', () => {
    updateDashboard(); // Immediate load
    setInterval(updateDashboard, 15000); // Refresh every 15 seconds (adjusted from 30s)
});

// Optional: Add manual refresh button (add this HTML: <button id="refresh-btn">Refresh Now</button>)
document.getElementById('refresh-btn')?.addEventListener('click', updateDashboard);
</script>

  </head>

  <body>
    <div class="container">
      <div class="navigation">
        <ul>
          <li>
            <a href="#">
              <span class="icon"><ion-icon name="laptop-outline"></ion-icon></span>
              <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_ADMIN</span>
            </a>
          </li>
          <li class="hovered">
            <a href="dashboard.php">
              <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
              <span class="title">DASHBOARD</span>
            </a>
          </li>
          <li>
            <a href="user_registration.php">
              <span class="icon"><ion-icon name="person-add-outline"></ion-icon></span>
              <span class="title">USER REGISTRATION</span>
            </a>
          </li>
          <li>
            <a href="request_status.php">
              <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
              <span class="title">REQUEST STATUS</span>
            </a>
          </li>
          <li>
            <a href="work_loads.php">
              <span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span>
              <span class="title">WORK LOADS</span>
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
              <div class="numbers" id="daily-count">0</div>
              <div class="cardName">Daily Completed</div>
            </div>
            <div class="iconBx">
              <ion-icon name="checkmark-done-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers" id="workloads-count">0</div>
              <div class="cardName">Work Loads</div>
            </div>
            <div class="iconBx">
              <ion-icon name="clipboard-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers" id="request-count">0</div>
              <div class="cardName">Requests</div>
            </div>
            <div class="iconBx">
              <ion-icon name="document-text-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers" id="monthly-count">0</div>
              <div class="cardName">Total Monthly Completed</div>
            </div>
            <div class="iconBx">
              <ion-icon name="trending-up-outline"></ion-icon>
            </div>
          </div>
        </div>
        <div class="graphBox">
          <div class="box">
            <canvas id="myChart"></canvas>
          </div>
          <div class="box">
            <canvas id="earning"></canvas>
          </div>
        </div>

        <div class="details">
          <div class="recentOrders">
            <div class="cardHeader">
              <h2>Request Status</h2>
              <a href="request_status.php" class="btn">View All</a>
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

        <!-- Details Section -->
      <div class="details">
        <div class="recentOrders">
          <div class="cardHeader">
            <h2>User List</h2>
          </div>
          <table>
            <thead>
              <tr>

                <td>Employee Name</td>
                <td>Position</td>
                <td>Status</td>
              </tr>
            </thead>
            <tbody>
              
              <?php
              // Query to fetch data from the database
              $query = "
                    SELECT 
                        user_login.id,
                        user_login.status,
                        user_login.position,
                        user_login.department,
                        CASE 
                            WHEN user_login.position = 'admin' THEN CONCAT(admin_info.admin_Fname, ' ', admin_info.admin_Lname)
                            WHEN user_login.position = 'employee' THEN CONCAT(employee_info.employee_fname, ' ', employee_info.employee_lname)
                            WHEN user_login.position = 'hr' THEN CONCAT(hr_info.hr_fname, ' ', hr_info.hr_lname)
                            WHEN user_login.position = 'tech' THEN CONCAT(tech_info.tech_fname, ' ', tech_info.tech_lname)
                            ELSE 'Unknown'
                        END AS employee_name
                    FROM user_login
                    LEFT JOIN admin_info ON user_login.id = admin_info.id
                    LEFT JOIN employee_info ON user_login.id = employee_info.id
                    LEFT JOIN hr_info ON user_login.id = hr_info.id
                    LEFT JOIN tech_info ON user_login.id = tech_info.id
                    GROUP BY user_login.id
                ";
              $result = $link->query($query);

              // Check if there are rows to display
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";

                  echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['position']) . "</td>";
                  echo "<td>" . ($row['status'] == 1 ? "Active" : "Inactive") . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='7'>No data found.</td></tr>";
              }
              ?>
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
