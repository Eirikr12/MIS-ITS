<?php
session_start();
require_once "src/config.php";

// 🔐 Redirect to login if not authenticated
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("location: ../login.php");
    exit();
}

$user_id = $_SESSION["id"];
$session_token = $_SESSION["user_session_id"];

// ✅ Validate user session ID from DB
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

// ✅ Fetch user list with registration date
$query = "SELECT id, username, email, position, date_reg,
          CASE WHEN status = 0 THEN 'Pending' ELSE 'Approved' END AS status 
          FROM user_login";
$result = $link->query($query);

// 🔄 Separate into pending and approved users
$pendingUsers = [];
$approvedUsers = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'Pending') {
        $pendingUsers[] = $row;
    } else {
        $approvedUsers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIS-ADMIN - User Registration</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css" />
    <link rel="stylesheet" href="User Registration/user_registration_table.css" />
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .cardHeader {
            margin-bottom: 20px;
        }
        .action-buttons a {
            margin: 2px;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            font-size: 0.9em;
        }
        .edit-btn { background-color: #007bff; }
        .edit-btn:hover { background-color: #0056b3; }
        .delete-btn { background-color: #dc3545; }
        .delete-btn:hover { background-color: #c82333; }
        .approve-btn { background-color: #28a745; }
        .approve-btn:hover { background-color: #218838; }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 100px;
            left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
        }
        .modal-close {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }
        .modal input, .modal select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
        }
        .search-container {
            margin: 20px 0;
        }
        .search-input {
            padding: 8px;
            width: 300px;
            font-size: 1em;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div class="navigation">
        <ul>
            <li><a href="#"><span class="icon"><ion-icon name="laptop-outline"></ion-icon></span><span class="title">MIS_ADMIN</span></a></li>
            <li><a href="dashboard.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">DASHBOARD</span></a></li>
            <li class="hovered"><a href="user_registration.php"><span class="icon"><ion-icon name="person-add-outline"></ion-icon></span><span class="title">USER REGISTRATION</span></a></li>
            <li><a href="request_status.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">REQUEST STATUS</span></a></li>
            <li><a href="work_loads.php"><span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span><span class="title">WORK LOADS</span></a></li>
            <li><a href="view_profile.php"><span class="icon"><ion-icon name="person-outline"></ion-icon></span><span class="title">VIEW PROFILE</span></a></li>
            <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">LOG OUT</span></a></li>
        </ul>
    </div>

    <!-- Main -->
    <div class="main">
        <div class="topbar">
            <div class="toggle" onclick="toggleMenu()">
                <ion-icon name="menu-outline"></ion-icon>
            </div>
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

        <!-- Approved Users Table -->
        <div class="user-approval-list" style="margin-top: 40px;">
            <div class="cardHeader">
                <h2>Approved Users</h2>
            </div>
            <table id="approvedTable">
                <thead>
                <tr>
                    <th onclick="sortTable(0, 'approvedTable')">Username</th>
                    <th onclick="sortTable(1, 'approvedTable')">Email</th>
                    <th onclick="sortTable(2, 'approvedTable')">Role</th>
                    <th onclick="sortTable(3, 'approvedTable')">Status</th>
                    <th onclick="sortTable(4, 'approvedTable')">Registered</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $query = "SELECT id, username, email, position, date_reg, 
                          CASE WHEN status = 0 THEN 'Offline' ELSE 'Online' END AS status 
                          FROM user_login";
                $result = $link->query($query);

                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['date_reg']) ?></td>
                        <td class="action-buttons">
                            <a href="#" class="edit-btn" onclick="openEditModal(
                                <?= $row['id'] ?>, 
                                '<?= addslashes($row['username']) ?>', 
                                '<?= addslashes($row['email']) ?>', 
                                '<?= $row['position'] ?>', 
                                '<?= $row['status'] ?>')">Edit</a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pending Temporary Users Table -->
        <div class="user-approval-list">
            <div class="cardHeader">
                <h2>Pending Temporary Users</h2>
            </div>
            <table id="pendingTemporaryTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to fetch pending users from the registration_request table
                    $query = "
                        SELECT 
                            r.user_id AS temporary_id,
                            t.temp_username AS temporary_username,
                            r.email AS temporary_email,
                            p.position_name AS temporary_position,
                            d.department_name AS department_name,
                            CASE 
                                WHEN r.status_id = 0 THEN 'Pending' 
                                WHEN r.status_id = 1 THEN 'Approved' 
                                ELSE 'Declined' 
                            END AS status
                        FROM registration_request r
                        LEFT JOIN temporary_log_in t ON r.user_id = t.temp_user_id
                        LEFT JOIN position_info p ON r.position_id = p.position_id
                        LEFT JOIN department_info d ON r.department_id = d.department_id
                        WHERE r.status_id = 0
                    ";
                    $result = $link->query($query);

                    // Check if there are rows to display
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['temporary_username']) ?></td>
                                <td><?= htmlspecialchars($row['temporary_email']) ?></td>
                                <td><?= htmlspecialchars($row['temporary_position']) ?></td>
                                <td><?= htmlspecialchars($row['department_name']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td class="action-buttons">
                                    <a href="approve_user.php?id=<?= $row['temporary_id'] ?>" class="approve-btn" onclick="return confirm('Approve this temporary user?');">Approve</a>
                                    <a href="delete_user.php?id=<?= $row['temporary_id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to cancel this user?');">Decline</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center;'>No pending temporary users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Approved Temporary Users Table -->
        <div class="user-approval-list" style="margin-top: 40px;">
            <div class="cardHeader">
                <h2>Approved Temporary Users</h2>
            </div>
            <table id="approvedTemporaryTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to fetch approved temporary users
                    $query = "
                        SELECT 
                            t.temp_user_id AS temporary_id,
                            t.temp_username AS temporary_username,
                            r.email AS temporary_email,
                            p.position_name AS temporary_position,
                            CASE 
                                WHEN r.status_id = 1 THEN 'Approved' 
                                ELSE 'Pending' 
                            END AS status
                        FROM registration_request r
                        LEFT JOIN temporary_log_in t ON r.user_id = t.temp_user_id
                        LEFT JOIN position_info p ON r.position_id = p.position_id
                        WHERE r.status_id = 1
                    ";
                    $result = $link->query($query);

                    // Check if there are rows to display
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['temporary_username']) ?></td>
                                <td><?= htmlspecialchars($row['temporary_email']) ?></td>
                                <td><?= htmlspecialchars($row['temporary_position']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center;'>No approved temporary users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h3>Edit User</h3>
        <form action="update_user.php" method="POST">
            <input type="hidden" name="user_id" id="editUserId">
            <label>Username:</label>
            <input type="text" name="username" id="editUsername" required>
            <label>Email:</label>
            <input type="email" name="email" id="editEmail" required>
            <label>Role:</label>
            <select name="position" id="editPosition" required>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
                <option value="hr">HR</option>
                <option value="tech">Tech</option>
            </select>
            <label>Status:</label>
            <select name="status" id="editStatus" required>
                <option value="1">Approved</option>
                <option value="0">Pending</option>
            </select>
            <button type="submit" class="edit-btn" style="margin-top: 10px;">Save Changes</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    function toggleMenu() {
        document.querySelector(".navigation").classList.toggle("active");
        document.querySelector(".main").classList.toggle("active");
    }

    function openEditModal(id, username, email, position, status) {
        document.getElementById("editModal").style.display = "block";
        document.getElementById("editUserId").value = id;
        document.getElementById("editUsername").value = username;
        document.getElementById("editEmail").value = email;
        document.getElementById("editPosition").value = position;
        document.getElementById("editStatus").value = (status === 'Approved' ? 1 : 0);
    }

    function closeModal() {
        document.getElementById("editModal").style.display = "none";
    }

    function sortTable(n, tableId) {
        const table = document.getElementById(tableId);
        let switching = true, dir = "asc", switchcount = 0;
        while (switching) {
            switching = false;
            const rows = table.rows;
            for (let i = 1; i < (rows.length - 1); i++) {
                let shouldSwitch = false;
                const x = rows[i].getElementsByTagName("TD")[n];
                const y = rows[i + 1].getElementsByTagName("TD")[n];
                if ((dir === "asc" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) ||
                    (dir === "desc" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())) {
                    shouldSwitch = true;
                    break;
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount++;
            } else {
                if (switchcount === 0 && dir === "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }

    function filterTables() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const tables = ['pendingTemporaryTable', 'approvedTable', 'approvedTemporaryTable'];
        tables.forEach(tableId => {
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                row.style.display = (name.includes(input) || email.includes(input)) ? "" : "none";
            });
        });
    }
</script>
</body>
</html>
