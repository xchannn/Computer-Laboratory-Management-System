<?php
// Set default timezone to Asia/Manila for Philippine Standard Time (PST)
date_default_timezone_set('Asia/Manila');

// Initialize variable to handle active menu highlighting
$active = basename($_SERVER['PHP_SELF'], ".php");

$labId = ''; // Initialize variable to handle active lab highlighting
if (isset($_GET['lab'])) {
    $labId = $_GET['lab'];
}

// Get current date and time in PST
$currentDate = date('D, M d, Y');
$currentTime = date('h:i:s A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0; /* Reset default margin */
        }
        .sidebar {
            height: 100vh;
            width: 210px;
            position: sticky;
            top: 0;
            left: 0;
            background-color: #f8f9fa; /* Light gray background */
            color: #495057; /* Dark gray text color */
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Optional: Add a subtle box shadow */
            overflow-y: auto; /* Allow sidebar to scroll if content exceeds height */
        }
        .sidebar.sidebar-open {
            transform: translateX(0);
        }
        .sidebar.sidebar-closed {
            transform: translateX(-250px);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef; /* Light border between menu items */
        }
        .sidebar ul li:last-child {
            border-bottom: none; /* Remove border on the last menu item */
        }
        .sidebar ul li a {
            color: #495057;
            display: block;
            text-decoration: none;
            font-size: 1rem;
        }
        .sidebar ul li a.active,
        .sidebar ul li a:hover {
            background-color: #FF7B00; /* Blue background for active/hover */
            color: white; /* White text color for active/hover */
            border-radius: 4px;
        }
        .sidebar ul li a i {
            margin-right: 10px;
        }
        .submenu ul {
            display: none;
            list-style-type: none;
            padding-left: 15px;
        }
        .submenu ul li {
            padding: 10px 0;
        }
        .sidebar-logo {
            text-align: center;
            margin-bottom: 1px;
        }
        .sidebar-logo img {
            max-width: 100%;
            height: auto;
            cursor: pointer;
        }
        .sidebar-footer {
            position: fixed;
            bottom: 0;
            width: 100%; /* Adjusted to full width */
            text-align: center;
            background-color: #F3F9FC; /* Light gray background */
            padding: 10px 0; /* Adjusted padding */
            border-top: 1px solid #dee2e6; /* Light border on top */
        }
        .sidebar-footer p {
            margin: 0;
            font-size: 1.2rem; /* Medium font size */
            color: #6c757d; /* Dark gray text color */
            display: inline-block;
            margin-right: 1px;
        }
        .sidebar-footer .icon {
            font-size: 1rem;
            margin-right: 5px;
        }
        .sidebar-footer .separator {
            margin: 0 10px;
            border-left: 1px solid #DAE1E8; /* Light gray separator */
            height: 20px;
            display: inline-block;
        }
        @media (max-width: 992px) {
            .sidebar {
                width: 200px; /* Adjust sidebar width for smaller screens */
            }
            .sidebar-logo img {
                max-width: 80%;
            }
            .sidebar-footer {
                width: 200px; /* Adjusted to match sidebar width */
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 150px; /* Further adjust sidebar width for mobile */
            }
            .sidebar h2 {
                font-size: 1.2rem;
            }
            .sidebar ul li a {
                font-size: 0.9rem;
            }
            .sidebar-footer {
                width: 150px; /* Adjusted to match sidebar width */
            }
        }
    </style>
</head>
<body>
    <div class="sidebar sidebar-open">
        <div class="sidebar-logo">
            <a href="dashboard.php">
                <img src="../images/logo.png" alt="CLMS Logo">
            </a>
        </div>
        <ul>
            <li>
                <a href="dashboard.php" class="<?= ($active == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="manage_users.php" class="<?= ($active == 'manage_users') ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Manage Users
                </a>
            </li>
            <li>
                <a href="laboratories.php" class="<?= ($active == 'laboratories') ? 'active' : ''; ?>">
                    <i class="fas fa-laptop"></i> Laboratories
                </a>
            </li>
            <li>
                <a href="equipments.php" class="<?= ($active == 'equipments') ? 'active' : ''; ?>">
                    <i class="fas fa-toolbox"></i> Equipments
                </a>
            </li>
            <li>
                <a href="../admin/logout.php" id="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <p><i class="far fa-calendar-alt icon"></i><?= $currentDate; ?></p>
            <div class="separator"></div>
            <p id="current-time"><i class="far fa-clock icon"></i><?= $currentTime; ?></p>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            // Function to update time every second
            function updateTime() {
                var now = new Date();
                var hours = now.getHours();
                var minutes = now.getMinutes();
                var seconds = now.getSeconds();
                var ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                var timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
                $('#current-time').html('<i class="far fa-clock icon"></i>' + timeString);
            }

            // Update time initially
            updateTime();

            // Update time every second
            setInterval(updateTime, 1000);

            // Toggle sidebar submenu
            $('.submenu > a').click(function(e) {
                e.preventDefault();
                var submenu = $(this).next('ul');
                submenu.slideToggle(200);
            });

            // Logout confirmation
            $('#logout').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = '../admin/logout.php';
                }
            });
        });
    </script>
</body>
</html>
