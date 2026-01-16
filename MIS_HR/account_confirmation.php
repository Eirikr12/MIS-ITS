<?php
session_start();
require_once "src/config.php"; // Ensure this connects to your DB

// Redirect if user not logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['user_session_id'])) {
    header("Location: ../login.php");
    exit();
}

// Validate session token
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
    header("Location: ../login.php");
    exit();
}

// Handle user edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_user_id'])) {
    $edit_user_id = $_POST['edit_user_id'];
    $employee_fname = $_POST['employee_fname'];
    $employee_mname = $_POST['employee_mname'];
    $employee_lname = $_POST['employee_lname'];
    $employee_contact = $_POST['employee_contact'];
    $department_name = $_POST['department_name'];
    $position_name = $_POST['position_name'];

    // Fetch department_id and position_id based on names
    $dept_query = "SELECT department_id FROM department_info WHERE department_name = ?";
    $stmt = $link->prepare($dept_query);
    $stmt->bind_param("s", $department_name);
    $stmt->execute();
    $stmt->bind_result($department_id);
    $stmt->fetch();
    $stmt->close();

    $pos_query = "SELECT position_id FROM position_info WHERE position_name = ?";
    $stmt = $link->prepare($pos_query);
    $stmt->bind_param("s", $position_name);
    $stmt->execute();
    $stmt->bind_result($position_id);
    $stmt->fetch();
    $stmt->close();

    // Fetch the correct employee_id from employee_info
    $emp_query = "SELECT employee_id FROM employee_info WHERE id = ?";
    $stmt = $link->prepare($emp_query);
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $stmt->bind_result($employee_id);
    $stmt->fetch();
    $stmt->close();

    if (!$employee_id) {
        echo "Error: Employee ID not found.";
        exit();
    }

    // Insert the edit request into the `edit_requests` table
    $insert_query = "
        INSERT INTO edit_requests (
            employee_id, 
            e_fname, 
            e_mname, 
            e_lname, 
            e_contact, 
            e_department_id, 
            e_position_id, 
            request_date, 
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
    ";
    $stmt = $link->prepare($insert_query);
    $stmt->bind_param(
        "isssiii",
        $employee_id,
        $employee_fname,
        $employee_mname,
        $employee_lname,
        $employee_contact,
        $department_id,
        $position_id
    );

    if ($stmt->execute()) {
        // Set a flash message in the session
        $_SESSION['success_message'] = "Request to edit submitted.";
        header("Location: account_confirmation.php");
        exit();
    } else {
        echo "Error inserting edit request.";
    }
}

// Fetch employee list
$query = "
    SELECT 
        e.id,
        e.employee_id,
        e.employee_fname,
        e.employee_mname,
        e.employee_lname,
        e.employee_contact,
        d.department_name AS department,
        p.position_name AS position
    FROM employee_info e
    LEFT JOIN department_info d ON e.department_id = d.department_id
    LEFT JOIN position_info p ON e.position_id = p.position_id
";
$stmt = $link->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$accounts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch department and position lists for dropdowns
$departments = [];
$positions = [];

$dept_query = "SELECT department_name FROM department_info";
$result = $link->query($dept_query);
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department_name'];
}

$pos_query = "SELECT position_name FROM position_info";
$result = $link->query($pos_query);
while ($row = $result->fetch_assoc()) {
    $positions[] = $row['position_name'];
}

// Fetch specific user for editing
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_query = "
        SELECT 
            e.id,
            e.employee_fname,
            e.employee_mname,
            e.employee_lname,
            e.employee_contact,
            e.department_id,
            e.position_id
        FROM employee_info e
        WHERE e.id = ?
    ";
    $stmt = $link->prepare($edit_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Filter if search is present
$filteredAccounts = $accounts;
if (isset($_GET['search'])) {
    $searchTerm = strtolower($_GET['search']);
    $filteredAccounts = array_filter($filteredAccounts, function ($account) use ($searchTerm) {
        return
            strpos(strtolower($account['employee_id']), $searchTerm) !== false ||
            strpos(strtolower($account['employee_fname']), $searchTerm) !== false ||
            strpos(strtolower($account['employee_mname']), $searchTerm) !== false ||
            strpos(strtolower($account['employee_lname']), $searchTerm) !== false ||
            strpos(strtolower($account['employee_contact']), $searchTerm) !== false ||
            strpos(strtolower($account['department']), $searchTerm) !== false ||
            strpos(strtolower($account['position']), $searchTerm) !== false;
    });
}

// Prevent back button access
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-HR - Account Confirmation</title>
    <link rel="stylesheet" href="HR Dashboard/style.css" />
    <link rel="stylesheet" href="Account Confirmation/account_confirmation.css" />
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
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

        .modal input,
        .modal select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
        }
    </style>
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
                <li>
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
                <li  class="hovered">
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

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="search">
                    <form method="GET" action="">
                        <label>
                            <input type="text" placeholder="Search here" name="search"
                                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
                            <ion-icon name="search-outline"></ion-icon>
                        </label>
                    </form>
                </div>
                <div class="user"><img src="../user.jpg" /></div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); // Clear the message after displaying ?>
            <?php endif; ?>

            <div class="account-confirmation-list">
                <div class="cardHeader">
                    <h2>Account Confirmation</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Contacts</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredAccounts)) : ?>
                            <tr>
                                <td colspan="9" class="no-results">No accounts found</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($filteredAccounts as $account) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($account['employee_fname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_mname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_lname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_contact']) ?></td>
                                    <td><?= htmlspecialchars($account['department']) ?></td>
                                    <td><?= htmlspecialchars($account['position']) ?></td>
                                    <td>
                                        <button class = "request-edit" onclick="openEditModal(
                                            <?= $account['id'] ?>,
                                            '<?= addslashes($account['employee_fname']) ?>',
                                            '<?= addslashes($account['employee_mname']) ?>',
                                            '<?= addslashes($account['employee_lname']) ?>',
                                            '<?= addslashes($account['employee_contact']) ?>',
                                            '<?= addslashes($account['department']) ?>',
                                            '<?= addslashes($account['position']) ?>'
                                        )">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3>Edit Employee</h3>
            <form method="POST" action="account_confirmation.php">
                <input type="hidden" name="edit_user_id" id="editUserId">
                <label>First Name:</label>
                <input type="text" name="employee_fname" id="editEmployeeFname" required>
                <label>Middle Name:</label>
                <input type="text" name="employee_mname" id="editEmployeeMname">
                <label>Last Name:</label>
                <input type="text" name="employee_lname" id="editEmployeeLname" required>
                <label>Contact:</label>
                <input type="text" name="employee_contact" id="editEmployeeContact" required>
                <label>Department:</label>
                <select name="department_name" id="editDepartmentName" required>
                    <?php foreach ($departments as $department) : ?>
                        <option value="<?= htmlspecialchars($department) ?>"><?= htmlspecialchars($department) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Position:</label>
                <select name="position_name" id="editPositionName" required>
                    <?php foreach ($positions as $position) : ?>
                        <option value="<?= htmlspecialchars($position) ?>"><?= htmlspecialchars($position) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        function openEditModal(id, fname, mname, lname, contact, department, position) {
            document.getElementById("editModal").style.display = "block";
            document.getElementById("editUserId").value = id;
            document.getElementById("editEmployeeFname").value = fname;
            document.getElementById("editEmployeeMname").value = mname;
            document.getElementById("editEmployeeLname").value = lname;
            document.getElementById("editEmployeeContact").value = contact;
            document.getElementById("editDepartmentName").value = department;
            document.getElementById("editPositionName").value = position;
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
    </script>
</body>

</html>
