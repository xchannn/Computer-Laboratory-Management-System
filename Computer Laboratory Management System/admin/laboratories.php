<?php
session_start();

// Include the database connection file
include('../includes/db.php');

// Fetch data from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT lab_id, assistant_id, room_name, capacity, updated_on FROM labs WHERE room_name LIKE '%$search%' OR assistant_id LIKE '%$search%'";
$result = $conn->query($sql);

// Function to get assistant name
function getAssistantName($conn, $assistant_id)
{
    $sql = "SELECT name FROM users WHERE id = '$assistant_id' AND role = 'assistant'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    return 'Unknown';
}

// Fetch all assistants
$assistants = [];
$sqlAssistants = "SELECT id, name FROM users WHERE role = 'assistant'";
$resultAssistants = $conn->query($sqlAssistants);
if ($resultAssistants->num_rows > 0) {
    while ($row = $resultAssistants->fetch_assoc()) {
        $assistants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.7/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            margin-top: 20px;
            padding-left: 250px;
            /* Adjusted for the sidebar width */
        }

        .content {
            padding: 20px;
        }

        .container {
            margin-top: 20px;
            width: 200px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            margin-bottom: 10px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #333;
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 1rem;
            /* Larger font size */
            text-align: center;
        }

        .btn {
            font-size: 0.875rem;
        }

        .btn i {
            margin-right: 5px;
        }

        .table thead th {
            border-bottom: 2px solid #6c757d;
            color: #6c757d;
        }

        .table tbody tr {
            border-top: 1px solid #ddd;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .search-bar input {
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            width: 100%;
        }

        .search-bar .btn {
            background-color: #125C92;
            color: #fff;
            border-radius: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2px 20px;
        }

        h1 {
            font-weight: bold;
            color: #343a40;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }

        .action-btn {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s, box-shadow 0.2s;
            border-radius: 50px;
            padding: 5px 10px;
        }

        .action-btn:hover {
            background-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .no-matched {
            font-size: 1.2rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .search-bar .input-group {
                flex-direction: column;
            }

            .search-bar input {
                margin-bottom: 10px;
            }
        }

        .modal-content {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            border-bottom: 1px solid #ddd;
        }

        .modal-body {
            text-align: left;
            padding: 20px 30px;
        }

        .modal-footer {
            border-top: 1px solid #ddd;
            padding: 10px 20px;
        }

        .modal-body p {
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .modal-body strong {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content container-fluid dashboard-container">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0"><i class="fas fa-laptop"> </i> Laboratories</h1>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addLabModal"><i class="fas fa-plus"></i> Add Laboratory</button>
            </div>
            <div class="row search-bar">
                <div class="col-md-12">
                    <div class="input-group">
                        <input type="text" id="search-input" name="search" class="form-control" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="search-btn"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="lab-content">
                <?php if ($result->num_rows > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Actions</th>
                                    <th>Room ID</th>
                                    <th>Assistant Incharge</th>
                                    <th>Room Name</th>
                                    <th>Capacity</th>
                                    <th>Date Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <button class='btn btn-info btn-sm action-btn' onclick='editLab(<?php echo $row["lab_id"]; ?>)'><i class='fas fa-edit'></i> Edit</button>
                                            <button class='btn btn-danger btn-sm action-btn' onclick='deleteLab(<?php echo $row["lab_id"]; ?>)'><i class='fas fa-trash'></i> Delete</button>
                                        </td>
                                        <td><?php echo $row["lab_id"]; ?></td>
                                        <td><?php echo getAssistantName($conn, $row["assistant_id"]); ?></td>
                                        <td><?php echo $row["room_name"]; ?></td>
                                        <td><?php echo $row["capacity"]; ?></td>
                                        <td><?php echo date("F j, Y", strtotime($row["updated_on"])); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="text-center no-matched">No matched items found.</p>
                <?php } ?>
                <?php $conn->close(); ?>
            </div>
        </div>
    </div>

    <!-- Add Laboratory Modal -->
    <div class="modal fade" id="addLabModal" tabindex="-1" role="dialog" aria-labelledby="addLabModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLabModalLabel"><i class="fas fa-plus-circle"></i> Add Laboratory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addLabForm" action="add_lab.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="assistant_id"><i class="fas fa-user"></i> Assistant Incharge</label>
                            <select class="form-control" id="assistant_id" name="assistant_id" required>
                                <option value="" selected disabled>Select Assistant</option>
                                <?php foreach ($assistants as $assistant) { ?>
                                    <option value="<?php echo $assistant['id']; ?>"><?php echo $assistant['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="room_name"><i class="fas fa-door-open"></i> Room Name</label>
                            <input type="text" class="form-control" id="room_name" name="room_name" required>
                        </div>
                        <div class="form-group">
                            <label for="capacity"><i class="fas fa-users"></i> Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Laboratory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Laboratory Modal -->
    <div class="modal fade" id="editLabModal" tabindex="-1" role="dialog" aria-labelledby="editLabModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLabModalLabel"><i class="fas fa-edit"></i> Edit Laboratory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editLabForm" action="edit_lab.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_lab_id" name="lab_id">
                        <div class="form-group">
                            <label for="edit_assistant_id"><i class="fas fa-user"></i> Assistant Incharge</label>
                            <select class="form-control" id="edit_assistant_id" name="assistant_id" required>
                                <option value="" selected disabled>Select Assistant</option>
                                <?php foreach ($assistants as $assistant) { ?>
                                    <option value="<?php echo $assistant['id']; ?>"><?php echo $assistant['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_room_name"><i class="fas fa-door-open"></i> Room Name</label>
                            <input type="text" class="form-control" id="edit_room_name" name="room_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_capacity"><i class="fas fa-users"></i> Capacity</label>
                            <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.7/dist/sweetalert2.min.js"></script>

    <script>
        // Function to handle the Edit action
        function editLab(lab_id) {
            $.ajax({
                url: 'get_lab.php',
                type: 'GET',
                data: {
                    lab_id: lab_id
                },
                success: function(response) {
                    let lab = JSON.parse(response);
                    $('#edit_lab_id').val(lab.lab_id);
                    $('#edit_assistant_id').val(lab.assistant_id);
                    $('#edit_room_name').val(lab.room_name);
                    $('#edit_capacity').val(lab.capacity);
                    $('#editLabModal').modal('show');
                }
            });
        }
        // Handle form submission for editing a lab
        $('#editLabForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'edit_lab.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    let result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Laboratory Updated',
                            text: result.message,
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                        });
                    }
                }
            });
        });

        // Handle form submission for adding a new lab
        $('#addLabForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: 'add_lab.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    let result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Laboratory Added',
                            text: result.message,
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                        });
                    }
                }
            });
        });

        // Function to handle the Delete action
        function deleteLab(lab_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this laboratory!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_lab.php',
                        type: 'POST',
                        data: {
                            lab_id: lab_id
                        },
                        success: function(response) {
                            let result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: result.message,
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message,
                                });
                            }
                        }
                    });
                }
            });
        }
        // handling for search queries
        $(document).ready(function() {
            $('#search-input').on('input', function() {
                let searchQuery = $(this).val();
                $.ajax({
                    url: 'search_labs.php',
                    type: 'GET',
                    data: {
                        search: searchQuery
                    },
                    success: function(response) {
                        $('#lab-content').html(response);
                    }
                });
            });
        });
    </script>
</body>

</html>