<?php
session_start(); // Start the session

// Check if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Fetch current user data
$username = $_SESSION['username'];
$query = $conn->prepare("SELECT * FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();
$currentUser = $result->fetch_assoc();

// Define roles for selection
$roles = ['admin', 'assistant'];

// Check if the admin is managing another user's account
$isAdmin = $currentUser['role'] == 'admin';
$selectedUser = $currentUser;

if ($isAdmin && isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $selectedUser = $result->fetch_assoc();
}

// Fetch all users if the current user is admin
$users = [];
if ($isAdmin) {
    $query = $conn->query("SELECT * FROM users");
    while ($row = $query->fetch_assoc()) {
        $users[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - CLMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container-fluid dashboard-container">
        <h1>Manage Account</h1>
        <?php if ($isAdmin): ?>
            <div class="form-group">
                <label for="user_select">Select User to Manage:</label>
                <select id="user_select" name="user_select" class="form-control" onchange="location = this.value;">
                    <option value="manage_account.php">Select a user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="manage_account.php?user_id=<?php echo $user['id']; ?>" <?php if ($selectedUser['id'] == $user['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <form action="update_account.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $selectedUser['id']; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($selectedUser['username']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($selectedUser['name']); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" value="">
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" class="form-control">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role; ?>" <?php if ($selectedUser['role'] == $role) echo 'selected'; ?>>
                            <?php echo ucfirst($role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Account</button>
        </form>
    </div>
</body>
</html>
