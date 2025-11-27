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

// Fetch only approved work load data with assigned technician
$query = "
    SELECT 
        request_info.request_id,
        request_info.request_description,
        CONCAT(employee_info.employee_fname, ' ', employee_info.employee_lname) AS employee_name,
        tech_info.id AS tech_id,
        CONCAT(tech_info.tech_fname, ' ', tech_info.tech_lname) AS assigned_to
    FROM request_info
    LEFT JOIN employee_info ON request_info.id = employee_info.id
    LEFT JOIN tech_info ON request_info.id = tech_info.id
    WHERE request_info.request_status = 'Approved'
";
$result = $link->query($query);

// Fetch technician list
$technicians_query = "SELECT id, CONCAT(tech_fname, ' ', tech_lname) AS tech_name FROM tech_info";
$technicians_result = $link->query($technicians_query);
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
                                    <?= htmlspecialchars($row['assigned_to']) ?: '' ?>
                                    <br />
                                    <select onchange="assignWorkLoad(<?= $row['request_id'] ?>, this.value)">
                                        <option value="">Select Technician</option>
                                        <?php foreach ($technicians as $tech) { ?>
                                            <option value="<?= $tech['id'] ?>" <?= $row['tech_id'] == $tech['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tech['tech_name']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
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
    </script>
</body>
</html>