<?php
session_start();
require_once "src/config.php"; // Make sure this connects to your DB

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

// Get employee list with joined department and position names
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
                        <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_HR</span>
                    </a>
                </li>
                <li><a href="dashboard.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">DASHBOARD</span></a></li>
                <li><a href="employee_registration.php"><span class="icon"><ion-icon name="person-add-outline"></ion-icon></span><span class="title">EMPLOYEE REGISTRATION</span></a></li>
                <li class="hovered"><a href="account_confirmation.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">ACCOUNT CONFIRMATION</span></a></li>
                <li><a href="view_profile.php"><span class="icon"><ion-icon name="person-outline"></ion-icon></span><span class="title">VIEW PROFILE</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">LOG OUT</span></a></li>
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

            <div class="account-confirmation-list">
                <div class="cardHeader">
                    <h2>Account Confirmation</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Employee ID</th>
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
                                    <td><?= htmlspecialchars($account['employee_id']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_fname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_mname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_lname']) ?></td>
                                    <td><?= htmlspecialchars($account['employee_contact']) ?></td>
                                    <td><?= htmlspecialchars($account['department']) ?></td>
                                    <td><?= htmlspecialchars($account['position']) ?></td>
                                    <td>
                                        <button class="request-edit"
                                            onclick='requestEdit(<?= json_encode($account["id"]) ?>, <?= json_encode($account["employee_id"]) ?>)'>
                                            Request Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="Account Confirmation/account_confirmation.js"></script>
    <script>
        let toggle = document.querySelector(".toggle");
        let navigation = document.querySelector(".navigation");
        let main = document.querySelector(".main");

        toggle.onclick = function () {
            navigation.classList.toggle("active");
            main.classList.toggle("active");
        };

        let list = document.querySelectorAll(".navigation li");
        list.forEach((item) =>
            item.addEventListener("mouseover", function () {
                list.forEach((el) => el.classList.remove("hovered"));
                this.classList.add("hovered");
            })
        );
    </script>
</body>

</html>
