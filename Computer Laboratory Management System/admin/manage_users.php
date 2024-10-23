<?php
session_start();

// Initialize variables
$success_message = '';
$error_message = '';

// Check if user is not logged in or not an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch session messages if any
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all users
$query = $conn->query("SELECT * FROM users");

// Check for errors in the query
if (!$query) {
    $error_message = "Error fetching users: " . $conn->error;
} else {
    // Fetch users if query was successful
    $users = [];
    while ($row = $query->fetch_assoc()) {
        $users[] = $row;
    }
}

// Define roles for selection
$roles = ['admin', 'assistant'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CLMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f0f2f5;
        }

        .content {
            padding: 40px 20px;
        }

        .action-buttons .btn {
            width: 75px;
            /* Set a specific width */
            height: 35px;
            /* Set a specific height */
            font-size: 14px;
            /* Adjust the font size if necessary */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-buttons .btn i {
            margin-right: 5px;
            /* Add some space between the icon and the text */
        }


        .user-picture {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: none;
            font-weight: bold;
            color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        @media (max-width: 576px) {
            .content {
                padding: 20px 10px;
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .card-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .scrollable-users {
            max-height: 400px;
            overflow-y: auto;
        }

        .search-bar {
            margin-bottom: 20px;
            position: relative;
        }

        .search-bar input {
            padding-left: 35px;
        }

        .search-bar .fa-search {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #aaa;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-info {
            text-align: center;
            color: black;
        }

        .username {
            color: black;
        }

        .name {
            color: black;
        }

        .role {
            color: black;
        }

        .card-info h5 {
            margin-bottom: 5px;
        }

        .add-user-btn {
            position: absolute;
            right: 20px;
            top: 20px;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container-fluid dashboard-container">
        <div class="card-container">
            <div class="card">
                <div class="card-header text-center position-relative">
                    <h2><i class="fas fa-users-cog"></i> Manage Users</h2>
                    <button type="button" class="btn btn-primary add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus"></i> Add New User
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_message)) : ?>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: '<?php echo addslashes($success_message); ?>'
                            });
                        </script>
                    <?php endif; ?>
                    <?php if (!empty($error_message)) : ?>
                        <script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: '<?php echo addslashes($error_message); ?>'
                            });
                        </script>
                    <?php endif; ?>

                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search" class="form-control" placeholder="Search users...">
                    </div>

                    <div class="row row-cols-1 row-cols-md-3 g-4 scrollable-users" id="user-cards">
                        <?php foreach ($users as $user) : ?>
                            <div class="col user-card">
                                <div class="card h-100">
                                    <div class="card-body card-content">
                                        <?php if (!empty($user['picture'])) : ?>
                                            <img src="../uploads/<?php echo $user['picture']; ?>" alt="User Picture" class="user-picture">
                                        <?php else : ?>
                                            <img src="../images/default-user.png" alt="Default User Picture" class="user-picture">
                                        <?php endif; ?>
                                        <div class="card-info">
                                            <h5 class="username"><i class="fas fa-user"></i> Username: <?php echo htmlspecialchars($user['username']); ?></h5>
                                            <h5 class="name"><i class="fas fa-signature"></i> Name: <?php echo htmlspecialchars($user['name']); ?></h5>
                                            <h5 class="role"><i class="fas fa-user-tag"></i> Role: <?php echo htmlspecialchars($user['role']); ?></h5>
                                        </div>
                                        <div class="action-buttons mt-3">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-name="<?php echo htmlspecialchars($user['name']); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>"><i class="fas fa-edit"></i> Edit</button>
                                            <form action="delete_user.php" method="POST" class="d-inline" onsubmit="return confirmDelete(event, this)">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus"></i> Add New User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="add_user.php" method="POST" enctype="multipart/form-data" id="add-user-form">
                                <div class="form-group row">
                                    <label for="username" class="col-sm-4 col-form-label"><i class="fas fa-user"></i> Username:</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="username" name="username" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label"><i class="fas fa-signature"></i> Name:</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="name" name="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="password" class="col-sm-4 col-form-label"><i class="fas fa-lock"></i> Password:</label>
                                    <div class="col-sm-8">
                                        <input type="password" id="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="role" class="col-sm-4 col-form-label"><i class="fas fa-user-tag"></i> Role:</label>
                                    <div class="col-sm-8">
                                        <select id="role" name="role" class="form-control" required>
                                            <?php foreach ($roles as $role) : ?>
                                                <option value="<?php echo $role; ?>"><?php echo ucfirst($role); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="photo" class="col-sm-4 col-form-label"><i class="fas fa-camera"></i> Photo:</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="photo" name="photo" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-center">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-user-edit"></i> Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="edit_user.php" method="POST" enctype="multipart/form-data" id="edit-user-form">
                                <input type="hidden" id="edit-user-id" name="user_id">
                                <div class="form-group row">
                                    <label for="edit-username" class="col-sm-4 col-form-label"><i class="fas fa-user"></i> Username:</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="edit-username" name="username" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edit-name" class="col-sm-4 col-form-label"><i class="fas fa-signature"></i> Name:</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="edit-name" name="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edit-password" class="col-sm-4 col-form-label"><i class="fas fa-lock"></i> Password:</label>
                                    <div class="col-sm-8">
                                        <input type="password" id="edit-password" name="password" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edit-role" class="col-sm-4 col-form-label"><i class="fas fa-user-tag"></i> Role:</label>
                                    <div class="col-sm-8">
                                        <select id="edit-role" name="role" class="form-control" required>
                                            <?php foreach ($roles as $role) : ?>
                                                <option value="<?php echo $role; ?>"><?php echo ucfirst($role); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="edit-photo" class="col-sm-4 col-form-label"><i class="fas fa-camera"></i> Photo:</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="edit-photo" name="photo" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-center">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function confirmDelete(event, form) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        document.getElementById('search').addEventListener('keyup', function() {
            let searchValue = this.value.toLowerCase();
            let userCards = document.getElementsByClassName('user-card');
            Array.from(userCards).forEach(function(card) {
                let username = card.querySelector('.card-info').textContent.toLowerCase();
                if (username.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        document.getElementById('add-user-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var form = this;
            var formData = new FormData(form);
            fetch('add_user.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'New user has been added successfully!'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'User Alerady Exist!'
                });
            });
        });

        // Fill the edit modal with user data when the edit button is clicked
        document.getElementById('editUserModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var username = button.getAttribute('data-username');
            var name = button.getAttribute('data-name');
            var role = button.getAttribute('data-role');

            var modal = this;
            modal.querySelector('#edit-user-id').value = userId;
            modal.querySelector('#edit-username').value = username;
            modal.querySelector('#edit-name').value = name;
            modal.querySelector('#edit-role').value = role;
        });

        // Handle the edit user form submission
        document.getElementById('edit-user-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var form = this;
            var formData = new FormData(form);
            fetch('edit_user.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json()).then(data => {
                console.log(data); // Log the response to debug
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'User details have been updated successfully!'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            }).catch(error => {
                console.error('Error:', error); // Log any fetch error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred!'
                });
            });
        });
    </script>
</body>

</html>