<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Check if user ID is provided
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Delete user from the database
    $query = $conn->prepare("DELETE FROM users WHERE id = ?");
    $query->bind_param("i", $user_id);
    
    if ($query->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting user!";
        header("Location: manage_users.php");
        exit();
    }
} else {
    $_SESSION['error'] = "User ID not provided!";
    header("Location: manage_users.php");
    exit();
}
?>
