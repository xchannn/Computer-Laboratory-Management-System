<?php
session_start(); // Start the session

// Check if user is not logged in or not an assistant
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'assistant') {
    header("Location: ../index.php");
    exit();
}

// Include database connection
include '../includes/db.php';

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all equipment data
$query = $conn->query("SELECT serial_number, name, picture, brand, description, quantity, cost, status, date_purchased FROM hf202_equipment");

// Check for errors in the query
if (!$query) {
    die("Error fetching equipment: " . $conn->error);
} else {
    $equipments = [];
    while ($row = $query->fetch_assoc()) {
        $equipments[] = $row;
    }
}

// Log the number of rows fetched
error_log("Number of equipment rows fetched: " . count($equipments));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HF202 Laboratory - CLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .content {
            padding: 20px;
        }

        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: #f8f9fa;
            text-align: center;
            font-weight: bold;
            border: none;
            padding: 10px;
            font-size: 14px;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
            font-size: 12px;
        }

        .equipment-img {
            max-width: 80px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .table-actions {
            display: flex;
            justify-content: center;
        }

        .table-actions .btn {
            margin-right: 5px;
            padding: 5px 8px;
            font-size: 12px;
        }

        .table-actions .btn i {
            margin-right: 3px;
        }

        /* Enhance Search Bar */
        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_filter label {
            margin-bottom: 0;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            padding: 6px 10px;
            border-radius: 4px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .dataTables_wrapper .dataTables_filter .form-control {
            display: inline-block;
            width: auto;
        }

        .dataTables_wrapper .dataTables_filter .form-control[type="search"] {
            margin-left: 0.5em;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEquipmentModalLabel"><i class="fas fa-plus"></i> Add Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEquipmentForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addEquipmentSerial" class="form-label"><i class="fas fa-barcode"></i> Serial Number</label>
                                <input type="text" class="form-control" id="addEquipmentSerial" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addEquipmentName" class="form-label"><i class="fas fa-tag"></i> Equipment Name</label>
                                <input type="text" class="form-control" id="addEquipmentName" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addEquipmentBrand" class="form-label"><i class="fas fa-industry"></i> Brand</label>
                                <input type="text" class="form-control" id="addEquipmentBrand" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addEquipmentQuantity" class="form-label"><i class="fas fa-boxes"></i> Quantity</label>
                                <input type="number" class="form-control" id="addEquipmentQuantity" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addEquipmentCost" class="form-label"><i class="fas fa-dollar-sign"></i> Cost</label>
                                <input type="number" class="form-control" id="addEquipmentCost" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addEquipmentStatus" class="form-label"><i class="fas fa-info-circle"></i> Status</label>
                                <select class="form-select" id="addEquipmentStatus" required>
                                    <option value="Functional">Functional</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                    <option value="Out of Order">Out of Order</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="addEquipmentDatePurchased" class="form-label"><i class="fas fa-calendar-alt"></i> Date Purchased</label>
                                <input type="date" class="form-control" id="addEquipmentDatePurchased" name="date_purchased" required>
                            </div>
                            <div class="col-md-6">
                                <label for="addEquipmentPicture" class="form-label"><i class="fas fa-image"></i> Picture</label>
                                <input type="file" class="form-control" id="addEquipmentPicture">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="addEquipmentDescription" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                            <textarea class="form-control" id="addEquipmentDescription" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Equipment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Equipment Modal -->
    <div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEquipmentModalLabel"><i class="fas fa-edit"></i> Edit Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editEquipmentForm">
                        <input type="hidden" id="editEquipmentSerial">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editEquipmentName" class="form-label"><i class="fas fa-tag"></i> Equipment Name</label>
                                <input type="text" class="form-control" id="editEquipmentName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEquipmentBrand" class="form-label"><i class="fas fa-industry"></i> Brand</label>
                                <input type="text" class="form-control" id="editEquipmentBrand" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editEquipmentQuantity" class="form-label"><i class="fas fa-boxes"></i> Quantity</label>
                                <input type="number" class="form-control" id="editEquipmentQuantity" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEquipmentCost" class="form-label"><i class="fas fa-dollar-sign"></i> Cost</label>
                                <input type="number" class="form-control" id="editEquipmentCost" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editEquipmentStatus" class="form-label"><i class="fas fa-info-circle"></i> Status</label>
                                <select class="form-select" id="editEquipmentStatus" required>
                                    <option value="Functional">Functional</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                    <option value="Out of Order">Out of Order</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editEquipmentDatePurchased" class="form-label"><i class="fas fa-calendar-alt"></i> Date Purchased</label>
                                <input type="date" class="form-control" id="editEquipmentDatePurchased" name="date_purchased" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editEquipmentPicture" class="form-label"><i class="fas fa-image"></i> Picture</label>
                                <input type="file" class="form-control" id="editEquipmentPicture">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editEquipmentDescription" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                            <textarea class="form-control" id="editEquipmentDescription" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-desktop"></i> HF202 Laboratory</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal"><i class="fas fa-plus"></i> Add Equipment</button>
        </div>

        <div class="table-container">
            <table id="equipmentTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>Equipment Name</th>
                        <th>Picture</th>
                        <th>Brand</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Date Purchased</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipments as $equipment) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($equipment['serial_number']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                            <td>
                                <img src="../uploads/<?php echo htmlspecialchars($equipment['picture']); ?>" alt="Equipment Image" class="equipment-img">
                            </td>
                            <td><?php echo htmlspecialchars($equipment['brand']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['description']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['cost']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['status']); ?></td>
                            <td><?php echo htmlspecialchars($equipment['date_purchased']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-primary btn-sm editEquipmentBtn" data-bs-toggle="modal" data-bs-target="#editEquipmentModal" data-serial="<?php echo htmlspecialchars($equipment['serial_number']); ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm deleteEquipmentBtn" data-serial="<?php echo htmlspecialchars($equipment['serial_number']); ?>">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#equipmentTable').DataTable({
                "lengthMenu": [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                "pageLength": 5,
                "columnDefs": [{
                        "orderable": false,
                        "targets": [2, 9]
                    } // Disable sorting on picture and actions columns
                ]
            });

            // Edit equipment button click
            $('#equipmentTable').on('click', '.editEquipmentBtn', function() {
                var serialNumber = $(this).data('serial');
                // Fetch equipment data based on serial number and fill the form
                // Implement the logic here to fill the form with the correct data
            });

            // Delete equipment button click
            $('#equipmentTable').on('click', '.deleteEquipmentBtn', function() {
                var serialNumber = $(this).data('serial');
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
                        // Implement delete logic here
                        Swal.fire(
                            'Deleted!',
                            'The equipment has been deleted.',
                            'success'
                        );
                    }
                });
            });
        });
    </script>
</body>

</html>