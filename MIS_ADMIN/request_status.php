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
        .approve-btn, .reject-btn {
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }

        .approve-btn {
            background-color: #28a745;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
                    <input type="text" id="searchInput" placeholder="Search by name or department..." onkeyup="filterTables()" />
                </label>
            </div>
            <div class="user"><img src="../user.jpg" /></div>
        </div>

        <!-- Pending Requests Table -->
        <div class="request-status-list">
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
                                <form action="approve_request.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                    <button type="submit" class="approve-btn">
                                        <ion-icon name="checkmark-outline"></ion-icon> Approve
                                    </button>
                                </form>
                                <button class="reject-btn" onclick="openRejectModal(<?= htmlspecialchars($row['request_id']) ?>)">
                                    <ion-icon name="close-outline"></ion-icon> Reject
                                </button>
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

        <!-- All Requests Table -->
        <div class="request-status-list" style="margin-top: 30px;">
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
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRejectModal()">&times;</span>
        <h3>Reject Request</h3>
        <form id="rejectForm" action="reject_request.php" method="POST">
            <input type="hidden" name="request_id" id="rejectRequestId">
            <label for="rejectReason">Reason for Rejection:</label>
            <textarea name="reject_reason" id="rejectReason" rows="4" required></textarea>
            <br><br>
            <button type="submit" class="reject-btn">Submit</button>
        </form>
    </div>
</div>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script>
    document.querySelector(".toggle").onclick = () => {
        document.querySelector(".navigation").classList.toggle("active");
        document.querySelector(".main").classList.toggle("active");
    };

    document.querySelectorAll(".navigation li").forEach(item =>
        item.addEventListener("mouseover", function () {
            document.querySelectorAll(".navigation li").forEach(i => i.classList.remove("hovered"));
            this.classList.add("hovered");
        })
    );

    function filterTables() {
        const filter = document.getElementById("searchInput").value.toLowerCase();
        document.querySelectorAll(".request-table tbody tr").forEach(row => {
            const name = row.cells[1]?.textContent.toLowerCase();
            const dept = row.cells[2]?.textContent.toLowerCase();
            row.style.display = (name.includes(filter) || dept.includes(filter)) ? "" : "none";
        });
    }

    function openRejectModal(requestId) {
        document.getElementById("rejectRequestId").value = requestId;
        document.getElementById("rejectModal").style.display = "block";
    }

    function closeRejectModal() {
        document.getElementById("rejectModal").style.display = "none";
    }
</script>

</body>
</html>