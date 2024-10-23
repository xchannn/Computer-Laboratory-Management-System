<?php
session_start(); // Start the session

// Check if user is not logged in or not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Fetch data from the database
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$activeAssistants = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role='assistant'")->fetch_assoc()['count'];

// Query to get equipment counts
$equipmentCount = $conn->query("SELECT COUNT(*) AS count FROM equipment")->fetch_assoc()['count'];

// Correct query to get the total number of laboratories
$totalLaboratories = $conn->query("SELECT COUNT(*) AS count FROM labs")->fetch_assoc()['count'];

// Fetch and sum the equipment status results
$equipmentStatusResult = $conn->query("SELECT 
    SUM(CASE WHEN status='Functional' THEN 1 ELSE 0 END) AS functional,
    SUM(CASE WHEN status='Under Maintenance' THEN 1 ELSE 0 END) AS under_maintenance,
    SUM(CASE WHEN status='Out of Order' THEN 1 ELSE 0 END) AS out_of_order
FROM equipment");

$equipmentStatus = $equipmentStatusResult->fetch_assoc();

// Fetch user data including profile picture
$userName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown'; // Assuming the username is stored in the session
$userQuery = $conn->prepare("SELECT picture FROM users WHERE username = ?");
$userQuery->bind_param("s", $userName);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userData = $userResult->fetch_assoc();
$userPicture = isset($userData['picture']) ? "../uploads/" . $userData['picture'] : "https://via.placeholder.com/80"; // Default placeholder if no picture found

$showWelcomeMessage = !isset($_SESSION['welcome_shown']) || !$_SESSION['welcome_shown'];
if ($showWelcomeMessage) {
    $_SESSION['welcome_shown'] = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CLMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        .dashboard-container {
            margin-top: 20px;
            padding-left: 270px;
            /* Adjusted for the sidebar width */
        }

        .card {
            border-radius: 10px;
        }

        .card-header {
            font-weight: bold;
        }

        .equipment-status-card {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-container {
            width: 100%;
            height: 350px;
        }

        .card-text {
            color: #f8f9fa;
        }

        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .user-profile img {
            border-radius: 50%;
            width: 55px;
            height: 55px;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #0A0B0B; /* Add border for better visual appeal */
        }

        .welcome-message {
            margin-left: 15px;
            color: #007bff;
            font-weight: bold;
        }

        @media (max-width: 992px) {
            .dashboard-container {
                padding-left: 0;
                /* Remove padding for smaller screens */
            }
        }

        @media (max-width: 768px) {
            .card-text {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content container-fluid dashboard-container">
        <div class="user-profile">
            <img src="<?php echo htmlspecialchars($userPicture); ?>" alt="User Icon">
            <span><?php echo htmlspecialchars($userName); ?></span>
            <?php if ($showWelcomeMessage) : ?>
                <span class="welcome-message">Welcome!</span>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Total Users</span>
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalUsers; ?></h5>
                        <p class="card-text">Number of registered users.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Active Assistants</span>
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $activeAssistants; ?></h5>
                        <p class="card-text">Currently active assistants.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Equipment Count</span>
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $equipmentCount; ?></h5>
                        <p class="card-text">Total pieces of equipment.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Total Laboratories</span>
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalLaboratories; ?></h5>
                        <p class="card-text">Total number of laboratories.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-header">
                        Overall Equipment Status
                    </div>
                    <div class="card-body equipment-status-card">
                        <div class="chart-container">
                            <canvas id="equipmentStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('equipmentStatusChart').getContext('2d');
            const gradient1 = ctx.createLinearGradient(0, 0, 0, 400);
            gradient1.addColorStop(0, '#28a745');
            gradient1.addColorStop(1, '#218838');

            const gradient2 = ctx.createLinearGradient(0, 0, 0, 400);
            gradient2.addColorStop(0, '#ffc107');
            gradient2.addColorStop(1, '#e0a800');

            const gradient3 = ctx.createLinearGradient(0, 0, 0, 400);
            gradient3.addColorStop(0, '#dc3545');
            gradient3.addColorStop(1, '#c82333');

            const equipmentStatusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Functional', 'Under Maintenance', 'Out of Order'],
                    datasets: [{
                        label: 'Equipment Status',
                        data: [<?php echo $equipmentStatus['functional']; ?>, <?php echo $equipmentStatus['under_maintenance']; ?>, <?php echo $equipmentStatus['out_of_order']; ?>],
                        backgroundColor: [gradient1, gradient2, gradient3],
                        borderColor: ['#fff', '#fff', '#fff'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#333',
                                fontSize: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    const data = tooltipItem.chart.data.datasets[0].data;
                                    const total = data.reduce((acc, value) => acc + value, 0);
                                    const currentValue = data[tooltipItem.dataIndex];
                                    const percentage = ((currentValue / total) * 100).toFixed(2);
                                    const label = tooltipItem.chart.data.labels[tooltipItem.dataIndex];
                                    return `${label}: ${currentValue} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
