<?php
session_start();
include '../includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $name = $_POST['name'];
    $role = $_POST['role'];

    // Password without hashing
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($name) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Handle picture upload
    if (!empty($_FILES['photo']['name'])) {
        $picture = $_FILES['photo']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($picture);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size
        if ($_FILES['photo']['size'] > 5000000) {
            echo json_encode(['success' => false, 'message' => 'File size too large.']);
            exit();
        }

        // Allow certain file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG & GIF files are allowed.']);
            exit();
        }

        // Check if the directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            echo json_encode(['success' => false, 'message' => 'Error uploading file.']);
            exit();
        }

        // Update user with picture
        $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, role = ?, picture = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $name, $role, $picture, $user_id);
    } else {
        // Update user without picture
        $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $name, $role, $user_id);
    }

    if ($stmt->execute()) {
        // Update password if provided
        if (!empty($password)) {
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $password, $user_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating password: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $stmt->error]);
    }

    $stmt->close();
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>
