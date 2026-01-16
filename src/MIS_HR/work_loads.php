<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MIS-ITS - Work Loads</title>
    <link rel="stylesheet" href="Admin Dashboard/style.css" />
    <link rel="stylesheet" href="Work Loads/work_loads.css" />
</head>

<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><ion-icon name="laptop-outline"></ion-icon></span>
                        <span class="title" style="font-size: 1.5em; font-weight: 500">MIS_ITS</span>
                    </a>
                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="user_registration.php">
                        <span class="icon"><ion-icon name="person-add-outline"></ion-icon></span>
                        <span class="title">User Registration</span>
                    </a>
                </li>
                <li>
                    <a href="request_status.php">
                        <span class="icon"><ion-icon name="document-text-outline"></ion-icon></span>
                        <span class="title">Request Status</span>
                    </a>
                </li>
                <li class="hovered">
                    <a href="work_loads.php">
                        <span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span>
                        <span class="title">Work Loads</span>
                    </a>
                </li>
                <li>
                    <a href="view_profile.php">
                        <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="title">View Profile</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="title">Log Out</span>
                    </a>
                </li>
            </ul>
        </div>

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
                    <img src="../user.jpg" />
                </div>
            </div>

            <div class="work-loads-list">
                <div class="cardHeader">
                    <h2>Work Loads</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="workLoadsList">
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="Work Loads/work_loads.js"></script>
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