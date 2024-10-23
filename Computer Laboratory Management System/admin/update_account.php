<?php
session_start(); // Start the session

// Check if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Get the submitted form data
$userId = $_POST['user_id'];
$name = $_POST['name'];
$password = $_POST['password'];
$role = $_POST['role'];

// Update user data in the database
if (!empty($password)) {
    // If the password field is not empty, update it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = $conn->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE id = ?");
    $query->bind_param("sssi", $name, $hashed_password, $role, $userId);
} else {
    // If the password field is empty, do not update the password
    $query = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
    $query->bind_param("ssi", $name, $role, $userId);
}

if ($query->execute()) {
    $_SESSION['success'] = "Account updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update account. Please try again.";
}

header("Location: manage_account.php");
exit();
?>
