<?php
session_start();
require_once "src/config.php"; // Ensure this file connects to the database

// If user is not logged in, redirect to login page
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
  header("location: ../login.php");
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
  header("location: ../login.php");
  exit();
}

// Prevent back button access after logout
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
?>

<?php
require_once "src/config.php"; // Ensure this file connects to the database

// Fetch request data for the logged-in user
$query = "
    SELECT 
        request_info.request_id,
        request_info.request_description,
        request_info.request_date,
        request_info.request_status
    FROM request_info
    WHERE request_info.id = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id); // Bind the logged-in user's ID
$stmt->execute();
$result = $stmt->get_result();
?>

<?php
// Fetch today's request count
$today_date = date('Y-m-d'); // Get today's date in 'YYYY-MM-DD' format
$query = "
    SELECT COUNT(*) AS today_requests 
    FROM request_info 
    WHERE DATE(request_date) = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $today_date);
$stmt->execute();
$stmt->bind_result($today_requests);
$stmt->fetch();
$stmt->close();
?>

<?php
// Fetch total distinct requests made by the logged-in user
$query = "
    SELECT COUNT(DISTINCT request_id) AS total_requests 
    FROM request_info 
    WHERE id = ?
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($total_requests);
$stmt->fetch();
$stmt->close();
?>

<?php
// Fetch total approved requests
$query = "
    SELECT COUNT(*) AS approved_requests 
    FROM request_info 
    WHERE id = ? AND request_status = 'Approved'
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($approved_requests);
$stmt->fetch();
$stmt->close();

// Fetch total pending requests
$query = "
    SELECT COUNT(*) AS pending_requests 
    FROM request_info 
    WHERE id = ? AND request_status = 'Pending'
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($pending_requests);
$stmt->fetch();
$stmt->close();

// Fetch total declined requests
$query = "
    SELECT COUNT(*) AS declined_requests 
    FROM request_info 
    WHERE id = ? AND request_status = 'Declined'
";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($declined_requests);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MIS-EMPLOYEE</title>
  <link rel="stylesheet" href="Employee Dashboard/style.css" />

  <script type="text/javascript">
    function preventBack() {
      window.history.forward();
    }

    setTimeout("preventBack()", 0);

    window.onunload = function() {
      null
    };
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
              <div class="cardName">Today's Request</div>
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

        <div class="cardBox">
          <div class="card">
            <div>
              <div class="numbers"><?php echo $total_requests; ?></div>
              <div class="cardName">Total Request Made</div>
            </div>
            <div class="iconBx">
              <ion-icon name="checkmark-done-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $approved_requests; ?></div>
              <div class="cardName">Total Approved Request</div>
            </div>
            <div class="iconBx">
              <ion-icon name="clipboard-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $pending_requests; ?></div>
              <div class="cardName">Total Pending Request</div>
            </div>
            <div class="iconBx">
              <ion-icon name="document-text-outline"></ion-icon>
            </div>
          </div>
          <div class="card">
            <div>
              <div class="numbers"><?php echo $declined_requests; ?></div>
              <div class="cardName">Total Declined Request</div>
            </div>
            <div class="iconBx">
              <ion-icon name="trending-up-outline"></ion-icon>
            </div>
          </div>
        </div>


      <!-- Graph Section -->
      <div class="details">
        <div class="recentOrders">
          <div class="cardHeader">
            <h2>Request List</h2>
            <a href="all_request_list.php" class="btn">View All</a>
          </div>
          <table>
            <thead>
              <tr>

                <td>Request ID</td>
                
                <td>Description</td>
                <td>Date Requested </td>
                <td>Status</td>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>

                  <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                 
                  <td><?php echo htmlspecialchars($row['request_description']); ?></td>
                  <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['request_status']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

      
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
  <script src="Employee Dashboard/my_chart.js"></script>
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