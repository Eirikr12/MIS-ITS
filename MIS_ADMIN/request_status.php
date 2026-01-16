<?php
require_once "src/config.php";
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("location: ../login.php");
    exit();
}

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
    header("location: ../login.php");
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Pending Requests
$pending_result = $link->query("
    SELECT 
        r.request_id,
        CONCAT(e.employee_fname, ' ', e.employee_lname) AS employee_name,
        u.department,
        r.request_description,
        r.request_date,
        r.request_status
    FROM request_info r
    JOIN user_login u ON r.id = u.id
    JOIN employee_info e ON u.id = e.id
    WHERE r.request_status = 'Pending'
");

// Approved Requests
$approved_result = $link->query("
    SELECT 
        r.request_id,
        CONCAT(e.employee_fname, ' ', e.employee_lname) AS employee_name,
        u.department,
        r.request_description,
        r.request_date,
        r.request_status
    FROM request_info r
    JOIN user_login u ON r.id = u.id
    JOIN employee_info e ON u.id = e.id
    WHERE r.request_status = 'Approved'
");

// All Requests
$all_result = $link->query("
    SELECT 
        r.request_id,
        CONCAT(e.employee_fname, ' ', e.employee_lname) AS employee_name,
        u.department,
        r.request_description,
        r.request_date,
        r.request_status
    FROM request_info r
    JOIN user_login u ON r.id = u.id
    JOIN employee_info e ON u.id = e.id
");

// Approve Request Logic
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['approve_request_id'])) {
    $request_id = $_POST['approve_request_id'];

    $stmt = $link->prepare("UPDATE request_info SET request_status = 'Approved' WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();

    header("Location: request_status.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MIS-ADMIN - Request Status</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css"/>
    <link rel="stylesheet" href="Request Status/request_status.css"/>
    <style>
        .approve-btn {
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            background-color: #28a745;
            transition: background-color 0.3s ease;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .request-table {
            width: 100%;
            border-collapse: collapse;
        }

        .request-table th, .request-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .request-table th {
            background-color: #f2f2f2;
        }

        .cardHeader {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="navigation">
        <ul>
            <li><a href="#"><span class="icon"><ion-icon name="laptop-outline"></ion-icon></span><span class="title">MIS_ADMIN</span></a></li>
            <li><a href="dashboard.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">DASHBOARD</span></a></li>
            <li><a href="user_registration.php"><span class="icon"><ion-icon name="person-add-outline"></ion-icon></span><span class="title">USER REGISTRATION</span></a></li>
            <li class="hovered"><a href="request_status.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">REQUEST STATUS</span></a></li>
            <li><a href="work_loads.php"><span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span><span class="title">WORK LOADS</span></a></li>
            <li><a href="view_profile.php"><span class="icon"><ion-icon name="person-outline"></ion-icon></span><span class="title">VIEW PROFILE</span></a></li>
            <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">LOG OUT</span></a></li>
        </ul>
    </div>

    <div class="main">

        <div class="topbar">
            <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
            <div class="search">
                <label>
                    <ion-icon name="search-outline"></ion-icon>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by name or email..." onkeyup="filterTables()" />
                </label>
            </div>
            <div class="user">
                <img src="../user.jpg" />
            </div>
        </div>

        <!-- All Requests Table -->
        <div class="request-status-list">
            <div class="cardHeader"><h2>All Requests</h2></div>
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Request Description</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $all_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['request_id']) ?></td>
                            <td><?= htmlspecialchars($row['employee_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['request_description']) ?></td>
                            <td><?= htmlspecialchars($row['request_date']) ?></td>
                            <td><?= htmlspecialchars($row['request_status']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pending Requests Table -->
        <div class="request-status-list" style="margin-top: 30px;">
            <div class="cardHeader"><h2>Pending Requests</h2></div>
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Request Description</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pending_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['request_id']) ?></td>
                            <td><?= htmlspecialchars($row['employee_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['request_description']) ?></td>
                            <td><?= htmlspecialchars($row['request_date']) ?></td>
                            <td><?= htmlspecialchars($row['request_status']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="approve_request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                    <button type="submit" class="approve-btn">Approve</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Approved Requests Table -->
        <div class="request-status-list" style="margin-top: 30px;">
            <div class="cardHeader"><h2>Approved Requests</h2></div>
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Request Description</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $approved_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['request_id']) ?></td>
                            <td><?= htmlspecialchars($row['employee_name']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td><?= htmlspecialchars($row['request_description']) ?></td>
                            <td><?= htmlspecialchars($row['request_date']) ?></td>
                            <td><?= htmlspecialchars($row['request_status']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Ionicons for icons -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<!-- Filtering and Toggle Menu Script -->
<script>
function filterTables() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const tables = document.querySelectorAll(".request-table tbody");

    tables.forEach(tbody => {
        const rows = tbody.querySelectorAll("tr");
        rows.forEach(row => {
            const rowText = Array.from(row.querySelectorAll("td"))
                .map(cell => cell.textContent.toLowerCase())
                .join(" ");
            row.style.display = rowText.includes(input) ? "" : "none";
        });
    });
}

// Toggle Menu
let toggle = document.querySelector('.toggle');
let navigation = document.querySelector('.navigation');
let main = document.querySelector('.main');

toggle.onclick = function () {
    navigation.classList.toggle('active');
    main.classList.toggle('active');
};
</script>

</body>
</html>
