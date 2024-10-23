<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['success' => false, 'message' => ''];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $password = $_POST['password']; // No hashing applied here
    $role = $_POST['role'];
    $picture = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $picture = basename($_FILES["photo"]["name"]);
        } else {
            $response['message'] = 'Error uploading the picture.';
            echo json_encode($response);
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (username, name, password, role, picture) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $name, $password, $role, $picture);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "User added successfully.";
    } else {
        $response['message'] = "Error adding user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
    exit();
}
?>
