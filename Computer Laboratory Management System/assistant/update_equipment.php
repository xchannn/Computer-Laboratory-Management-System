<?php
session_start();
include '../includes/db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];
    $status = $_POST['status'];
    $date_purchased = $_POST['date_purchased'];
    $description = $_POST['description'];

    // Handle file upload if any
    $picture = '';
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $picture = basename($_FILES['picture']['name']);
        $uploadFile = $uploadDir . $picture;
        move_uploaded_file($_FILES['picture']['tmp_name'], $uploadFile);

        $stmt = $conn->prepare("UPDATE hf202_equipment SET name=?, quantity=?, cost=?, status=?, date_purchased=?, description=?, picture=? WHERE id=?");
        $stmt->bind_param("sidssssi", $name, $quantity, $cost, $status, $date_purchased, $description, $picture, $id);
    } else {
        $stmt = $conn->prepare("UPDATE hf202_equipment SET name=?, quantity=?, cost=?, status=?, date_purchased=?, description=? WHERE id=?");
        $stmt->bind_param("sidsssi", $name, $quantity, $cost, $status, $date_purchased, $description, $id);
    }

    if ($stmt->execute()) {
        echo 'success';
    } else {
        error_log("Error updating equipment: " . $stmt->error);
        echo 'error';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request method';
}
?>
