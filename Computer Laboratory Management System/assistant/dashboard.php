<?php
session_start(); // Start the session

// Check if user is not logged in or not an assistant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'assistant') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Tables in the database
$equipmentTable = 'equipment';
$labsTable = 'labs';
$usersTable = 'users';

// Fetch the assistant's user ID
$userIdQuery = $conn->prepare("SELECT id FROM $usersTable WHERE username = ?");
$userIdQuery->bind_param("s", $username);
$userIdQuery->execute();
$userIdResult = $userIdQuery->get_result();
$userId = $userIdResult->fetch_assoc()['id'];

// Query to get equipment status counts for each lab assigned to the assistant
$equipmentStatusQuery = $conn->prepare("
    SELECT 
        l.room_name AS lab_name,
        e.lab_id,
        SUM(CASE WHEN e.status = 'Functional' THEN 1 ELSE 0 END) AS functional,
        SUM(CASE WHEN e.status = 'Under Maintenance' THEN 1 ELSE 0 END) AS under_maintenance,
        SUM(CASE WHEN e.status = 'Out of Order' THEN 1 ELSE 0 END) AS out_of_order
    FROM $equipmentTable e
    INNER JOIN $labsTable l ON e.lab_id = l.lab_id
    WHERE l.assistant_id = ?
    GROUP BY e.lab_id
");
$equipmentStatusQuery->bind_param("i", $userId);
$equipmentStatusQuery->execute();
$equipmentStatusResult = $equipmentStatusQuery->get_result();

$equipmentStatusData = [];
while ($row = $equipmentStatusResult->fetch_assoc()) {
    $equipmentStatusData[] = $row;
}

// Fetch the total number of labs assigned to the assistant
$labsAssignedQuery = $conn->prepare("SELECT COUNT(DISTINCT lab_id) AS count FROM $labsTable WHERE assistant_id = ?");
$labsAssignedQuery->bind_param("i", $userId);
$labsAssignedQuery->execute();
$labsAssigned = $labsAssignedQuery->get_result()->fetch_assoc()['count'];

// Fetch the names of the labs assigned
$labNamesQuery = $conn->prepare("SELECT room_name FROM $labsTable WHERE assistant_id = ?");
$labNamesQuery->bind_param("i", $userId);
$labNamesQuery->execute();
$labNamesResult = $labNamesQuery->get_result();
$labNames = [];
while ($row = $labNamesResult->fetch_assoc()) {
    $labNames[] = $row['room_name'];
}

// Fetch user profile picture
$userQuery = $conn->prepare("SELECT picture FROM $usersTable WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userData = $userQuery->get_result()->fetch_assoc();
$userPicture = isset($userData['picture']) ? "../uploads/" . $userData['picture'] : "https://via.placeholder.com/40"; // Default placeholder if no picture found

// Show welcome message once per session
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
    <title>Assistant Dashboard - CLMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        .dashboard-container {
            margin-top: 20px;
            padding-left: 270px;
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
            padding: 10px; /* Adjusted padding */
        }

        .chart-container {
            width: 100%;
            height: 130px; /* Adjusted height */
        }

        .card-text {
            color: #f8f9fa;
        }

        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .user-profile img {
            border-radius: 70%;
            width: 60px;
            height: 60px;
            margin-right: 10px;
        }

        .welcome-message {
            margin-left: 15px;
            color: #007bff;
            font-weight: bold;
        }

        @media (max-width: 992px) {
            .dashboard-container {
                padding-left: 0;
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
    <?php include '../includes/assistant_sidebar.php'; ?>

    <div class="content container-fluid dashboard-container">
        <div class="d-flex align-items-center mb-3">
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($userPicture); ?>" alt="User Icon">
                <span><?php echo htmlspecialchars($username); ?></span>
            </div>
            <?php if ($showWelcomeMessage) : ?>
                <span class="welcome-message">Welcome!</span>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-6 col-sm-6">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Total Equipment</span>
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo array_sum(array_column($equipmentStatusData, 'functional')) + array_sum(array_column($equipmentStatusData, 'under_maintenance')) + array_sum(array_column($equipmentStatusData, 'out_of_order')); ?></h5>
                        <p class="card-text">Number of equipments.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Computer Laboratory Assigned</span>
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $labsAssigned; ?></h5>
                        <p class="card-text">Room Name: <b><?php echo implode(", ", $labNames); ?></b></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($equipmentStatusData as $index => $labData) : ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Equipment Status - <?php echo htmlspecialchars($labData['lab_name']); ?>
                        </div>
                        <div class="card-body equipment-status-card">
                            <div class="chart-container">
                                <canvas id="equipmentStatusChart-<?php echo $index; ?>"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data for each lab
        const equipmentStatusData = <?php echo json_encode($equipmentStatusData); ?>;
        equipmentStatusData.forEach((labData, index) => {
            const ctx = document.getElementById(`equipmentStatusChart-${index}`).getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Functional', 'Under Maintenance', 'Out of Order'],
                    datasets: [{
                        data: [labData.functional, labData.under_maintenance, labData.out_of_order],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                        borderColor: ['#218838', '#e0a800', '#c82333'],
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
                                    const currentValue = tooltipItem.raw;
                                    const label = tooltipItem.label;
                                    return `${label}: ${currentValue}`;
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
