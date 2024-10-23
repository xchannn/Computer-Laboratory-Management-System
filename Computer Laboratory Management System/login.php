<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to check user credentials and fetch assistant_id
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $stmt->bind_result($assistantId);
    $stmt->fetch();

    if ($assistantId) {
        $_SESSION['assistant_id'] = $assistantId;
        header('Location: equipment_list.php');
    } else {
        echo "Invalid credentials";
    }

    $stmt->close();
    $conn->close();
}
?>
