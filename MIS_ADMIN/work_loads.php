<?php
session_start();
require_once "src/config.php"; // Database connection

// Redirect to login if session not set
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("location: ../login.php");
    exit();
}

// Session validation
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

// Disable back navigation after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch only approved work load data
$query = "
    SELECT 
        r.request_id,
        r.request_description,
        CONCAT(ei.employee_fname, ' ', ei.employee_lname) AS employee_name,
        ul.username AS technician_name,
        r.request_status
    FROM request_info r
    LEFT JOIN employee_info ei ON r.id = ei.id
    LEFT JOIN user_login ul ON ul.position = 'tech' AND ul.status = 1
    WHERE r.request_status = 'Approved'
";
$result = $link->query($query);

// Fetch technician list
$query_technicians = "
    SELECT id, username AS tech_name
    FROM user_login
    WHERE position = 'tech' AND status = 1
";
$technicians_result = $link->query($query_technicians);
$technicians = [];
while ($tech = $technicians_result->fetch_assoc()) {
    $technicians[] = $tech;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MIS-ADMIN - Work Loads</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css" />
    <link rel="stylesheet" href="Work Loads/work_loads.css" />

    <!-- Highlighted Change: Added CSS for column lines -->
    <style>
        /* Add column lines to tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Highlighted Change: Added border for column lines */
        table th, table td {
            border: 1px solid #ddd; /* Add column lines */
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        .cardHeader {
            margin-bottom: 20px;
        }
    </style>

    <!-- Prevent back button behavior -->
    <script>
        history.pushState(null, null, location.href);
        window.onpopstate = () => history.pushState(null, null, location.href);
    </script>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="icon"><ion-icon name="laptop-outline"></ion-icon></span><span class="title">MIS_ADMIN</span></a></li>
                <li><a href="dashboard.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">DASHBOARD</span></a></li>
                <li><a href="user_registration.php"><span class="icon"><ion-icon name="person-add-outline"></ion-icon></span><span class="title">USER REGISTRATION</span></a></li>
                <li><a href="request_status.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">REQUEST STATUS</span></a></li>
                <li class="hovered"><a href="work_loads.php"><span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span><span class="title">WORK LOADS</span></a></li>
                <li><a href="view_profile.php"><span class="icon"><ion-icon name="person-outline"></ion-icon></span><span class="title">VIEW PROFILE</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">LOG OUT</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <!-- Search Input -->
                <div class="search">
                    <label>
                        <input type="text" id="searchInput" placeholder="Search here..." onkeyup="filterWorkLoads()" />
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>
                <div class="user">
                    <img src="../user.jpg" />
                </div>
            </div>

            <!-- Work Load Table -->
            <div class="work-loads-list">
                <div class="cardHeader"><h2>Work Loads</h2></div>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Employee Name</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="workLoadsList">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['request_id']) ?></td>
                                <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                <td><?= htmlspecialchars($row['request_description']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['technician_name']) ?: 'Unassigned' ?>
                                    <br />
                                    <select onchange="assignWorkLoad(<?= $row['request_id'] ?>, this.value)">
                                        <option value="">Select Technician</option>
                                        <?php foreach ($technicians as $tech) { ?>
                                            <option value="<?= $tech['id'] ?>">
                                                <?= htmlspecialchars($tech['tech_name']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td><?= htmlspecialchars($row['request_status']) ?></td>
                                <td>
                                    <form method="post" action="approve_request.php">
                                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($row['request_id']) ?>">
                                        <button type="submit" class="approve-btn">Approve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ionicons and JS Scripts -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        // Hamburger menu toggle functionality
        document.querySelector('.toggle').addEventListener('click', () => {
            document.querySelector('.navigation').classList.toggle('active');
            document.querySelector('.main').classList.toggle('active');
        });

        function assignWorkLoad(requestId, techId) {
            if (!techId) return;
            // Send AJAX request to assign technician
            fetch('assign_work_load.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: requestId, tech_id: techId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Technician assigned successfully!');
                    location.reload();
                } else {
                    alert('Failed to assign technician.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Search filtering function
        function filterWorkLoads() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('workLoadsList');
            const rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                // Check each cell in the row (except the last one with action buttons)
                for (let j = 0; j < cells.length - 1; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const txtValue = cell.textContent || cell.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>