<?php
session_start(); // Start the session

include '../includes/db.php';

// Check if user is logged in and has a valid role
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'assistant')) {
    header("Location: ../index.php");
    exit();
}

// Fetch current user details
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found!";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updateSuccess = false; // Initialize success flag

    // Update name if changed
    $new_name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE username = ?");
    $stmt->bind_param("ss", $new_name, $username);
    if ($stmt->execute() === TRUE) {
        $updateSuccess = true;
    } else {
        echo "Error updating name: " . $conn->error;
    }

    // Upload new profile picture if provided
    if (isset($_FILES['picture']) && $_FILES['picture']['name']) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['picture']['name']);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES['picture']['tmp_name']);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'File is not an image.',
                        icon: 'error',
                        button: 'OK',
                    });
                  </script>";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES['picture']['size'] > 5000000) { // 5MB
            echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'Sorry, your file is too large.',
                        icon: 'error',
                        button: 'OK',
                    });
                  </script>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.',
                        icon: 'error',
                        button: 'OK',
                    });
                  </script>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'Sorry, your file was not uploaded.',
                        icon: 'error',
                        button: 'OK',
                    });
                  </script>";
        } else {
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                $filename = basename($_FILES['picture']['name']);
                $stmt = $conn->prepare("UPDATE users SET picture = ? WHERE username = ?");
                $stmt->bind_param("ss", $filename, $username);
                if ($stmt->execute() === TRUE) {
                    $updateSuccess = true;
                } else {
                    echo "<script>
                            swal({
                                title: 'Error!',
                                text: 'Error updating picture: " . $conn->error . "',
                                icon: 'error',
                                button: 'OK',
                            });
                          </script>";
                }
            } else {
                echo "<script>
                        swal({
                            title: 'Error!',
                            text: 'Sorry, there was an error uploading your file.',
                            icon: 'error',
                            button: 'OK',
                        });
                      </script>";
            }
        }
    }

    // Update password if provided
    if ($_POST['password']) {
        $new_password = $_POST['password']; // No hashing
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $new_password, $username);
        if ($stmt->execute() === TRUE) {
            $updateSuccess = true;
        } else {
            echo "<script>
                    swal({
                        title: 'Error!',
                        text: 'Error updating password: " . $conn->error . "',
                        icon: 'error',
                        button: 'OK',
                    });
                  </script>";
        }
    }

    // Set session variable to indicate success for name and picture update
    if ($updateSuccess) {
        $_SESSION['update_success'] = true;
    }

    // Redirect to avoid form resubmission
    header("Location: manage_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .dashboard-container {
            margin-top: 20px;
            padding-left: 270px; /* Adjusted for the sidebar width */
        }
        .content-container {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 500px; /* Adjusted width */
            margin: auto;
        }
        .content-container h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: #343a40;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
        }
        .form-control-file {
            border-radius: 0.5rem;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-label {
            color: #495057;
            font-weight: bold;
        }
        .icon {
            margin-right: 8px;
        }
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: 80%;
            max-width: 600px;
        }
        @media (max-width: 992px) {
            .dashboard-container {
                padding-left: 0; /* Remove padding for smaller screens */
            }
        }
        .profile-pic {
            display: block;
            margin: 0 auto 15px;
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include '../includes/assistant_sidebar.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <div class="content container-fluid dashboard-container">
        <div class="content-container">
            <h2><i class="fas fa-user-edit icon"></i>Manage Profile</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="text-center">
                    <?php if ($user['picture']) : ?>
                        <img src="../uploads/<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture" class="profile-pic">
                    <?php else : ?>
                        <img src="../images/default-profile.png" alt="Default Profile Picture" class="profile-pic">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label"><i class="fas fa-user icon"></i>Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label"><i class="fas fa-signature icon"></i>Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <?php if ($_SESSION['role'] == 'assistant') : ?>
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fas fa-key icon"></i>New Password</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="picture" class="form-label"><i class="fas fa-image icon"></i>Profile Picture</label>
                    <input type="file" name="picture" id="picture" class="form-control-file">
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save icon"></i>Update Profile</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['update_success'])) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            swal({
                title: "Success!",
                text: "Profile updated successfully!",
                icon: "success",
                button: "OK",
            });
            <?php unset($_SESSION['update_success']); ?>
        });
    </script>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script

</body>
</html>
