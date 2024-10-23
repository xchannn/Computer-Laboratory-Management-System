<?php
session_start();

// Include database connection
include '../includes/db.php';
$sql = "SELECT * FROM equipment"; // Adjust your query as needed
$result = $conn->query($sql);

// Check if user is not logged in or not an assistant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'assistant') {
    header("Location: ../index.php");
    exit();
}

// Ensure $assistantId is defined
if (!isset($_SESSION['id'])) {
    echo "No user ID found in session.";
    exit();
}

$assistantId = $_SESSION['id']; // Assuming user_id is stored in the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Tables in the database
$equipmentTable = 'equipment';
$labsTable = 'labs';
$usersTable = 'users';

// Fetch the labs assigned to the logged-in assistant
$sqlLabs = "SELECT lab_id, room_name FROM labs WHERE assistant_id = ?";
$stmtLabs = $conn->prepare($sqlLabs);
$stmtLabs->bind_param('i', $assistantId);
$stmtLabs->execute();
$resultLabs = $stmtLabs->get_result();

$labs = array();
while ($row = $resultLabs->fetch_assoc()) {
    $labs[$row['lab_id']] = $row['room_name'];
}

$stmtLabs->close();

if (empty($labs)) {
    echo "No labs assigned.";
    exit();
}

// Create a comma-separated list of lab IDs safely for SQL query
$labIds = implode(',', array_map('intval', array_keys($labs)));

// Ensure $labIds is not empty
if (empty($labIds)) {
    echo "No labs available.";
    exit();
}

// Prepare the equipment SQL query with search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sqlEquipment = "SELECT equipment.serial_number, equipment.lab_id, equipment.name, equipment.status, equipment.brand, equipment.picture, equipment.quantity, equipment.date_purchased, labs.room_name 
                 FROM equipment 
                 LEFT JOIN labs ON equipment.lab_id = labs.lab_id
                 WHERE equipment.lab_id IN ($labIds)";

if ($searchTerm) {
    $sqlEquipment .= " AND (equipment.name LIKE ? 
                          OR equipment.serial_number LIKE ? 
                          OR equipment.brand LIKE ? 
                          OR labs.room_name LIKE ?)";
}

$stmtEquipment = $conn->prepare($sqlEquipment);

if ($searchTerm) {
    $searchTermWildcard = '%' . $searchTerm . '%';
    $stmtEquipment->bind_param('ssss', $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard);
}

$stmtEquipment->execute();
$resultEquipment = $stmtEquipment->get_result();

// Initialize an array to hold the data grouped by lab_id
$equipmentData = array();

// Process the result set and group data by lab_id
if ($resultEquipment->num_rows > 0) {
    while ($row = $resultEquipment->fetch_assoc()) {
        $labId = $row['lab_id'];
        if (!isset($equipmentData[$labId])) {
            $equipmentData[$labId] = array(
                'room_name' => $row['room_name'],
                'equipments' => array()
            );
        }
        $equipmentData[$labId]['equipments'][] = $row;
    }
}

$stmtEquipment->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <style>
        /* CSS styles */
        body {
            background-color: #f4f4f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content {
            padding: 20px;
        }

        .container {
            max-width: 1500px;
            /* Set maximum width for better readability */
            margin-top: 1px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 0.85rem;
            /* Slightly increase font size for readability */
            padding: 10px;
            /* Adjust padding */
        }

        .btn {
            font-size: 0.85rem;
            /* Slightly increase button font size */
            padding: 1px 1px;
            /* Adjust padding for buttons */
            margin: 0 2px;
            /* Adjust margin for buttons */
        }

        .btn i {
            margin-right: 2px;
            /* Adjust margin */
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
        }

        .search-bar .btn {
            background-color: #125C92;
            color: #fff;
            border-radius: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 5px 10px;
        }

        .table img {
            width: 50px;
            height: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table td,
        .table th {
            text-align: center;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .lab-header {
            margin-top: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #1A58CB;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-weight: bold;
            color: #343a40;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }

        .action-btn {
            color: #fff;
            border-radius: 15px;
            /* Adjusted for smaller buttons */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2px 5px;
            /* Adjusted padding for smaller buttons */
        }

        .table-fixed tbody {
            display: block;
            max-height: 200px;
            overflow-y: auto;
        }

        .table-fixed thead,
        .table-fixed tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .table-fixed thead {
            width: calc(100% - 1em);
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

        .modal-body #modalPicture {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            display: flex;
            align-items: center;
        }

        .form-group label i {
            margin-right: 5px;
        }

        .modal-body img {
            max-width: 300px;
            /* Adjust the maximum width of the picture */
            max-height: 300px;
            /* Adjust the maximum height of the picture */
            border-radius: 15px;
            /* Add border radius */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            /* Add box shadow */
            margin: 0 auto;
            /* Center the picture horizontally */
            display: block;
            /* Make the picture a block element */
        }
    </style>
</head>

<body>
    <?php include '../includes/assistant_sidebar.php'; ?>

    <div class="content container-fluid dashboard-container">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-toolbox"></i> Equipments</h1>
            <div class="row search-bar">
                <div class="col-md-10">
                    <form class="input-group" method="get">
                        <input type="text" class="form-control" name="search" placeholder="Search equipment..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-2 text-right">
                    <!-- Button to trigger the modal -->
                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#addEquipmentModal"><i class="fas fa-plus"></i> Add Equipment</a>

                    <!-- Modal for Adding Equipment -->
                    <div class="modal fade" id="addEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="addEquipmentForm" action="add_equipment_process.php" method="post" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addEquipmentModalLabel"><i class="fas fa-toolbox"></i> Add Equipment</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Form Fields -->
                                        <div class="form-group">
                                            <label for="equipmentName"><i class="fas fa-tag"></i> Equipment Name</label>
                                            <input type="text" class="form-control" id="equipmentName" name="equipment_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentBrand"><i class="fas fa-industry"></i> Brand</label>
                                            <input type="text" class="form-control" id="equipmentBrand" name="equipment_brand" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentSerialNumber"><i class="fas fa-barcode"></i> Serial Number</label>
                                            <input type="text" class="form-control" id="equipmentSerialNumber" name="equipment_serial_number" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentQuantity"><i class="fas fa-sort-numeric-up"></i> Quantity</label>
                                            <input type="number" class="form-control" id="equipmentQuantity" name="equipment_quantity" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentStatus"><i class="fas fa-info-circle"></i> Status</label>
                                            <select class="form-control" id="equipmentStatus" name="status" required>
                                                <option value="Functional">Functional</option>
                                                <option value="Under Maintenance">Under Maintenance</option>
                                                <option value="Out of Order">Out of Order</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentPicture"><i class="fas fa-camera"></i> Picture</label>
                                            <input type="file" class="form-control-file" id="equipmentPicture" name="equipment_picture">
                                        </div>
                                        <div class="form-group">
                                            <label for="equipmentDatePurchased"><i class="fas fa-calendar-alt"></i> Date Purchased</label>
                                            <input type="date" class="form-control" id="equipmentDatePurchased" name="equipment_date_purchased" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="labSelect"><i class="fas fa-flask"></i> Select Lab</label>
                                            <select class="form-control" id="labSelect" name="lab_id" required>
                                                <?php foreach ($labs as $labId => $roomName) : ?>
                                                    <option value="<?php echo htmlspecialchars($labId); ?>"><?php echo htmlspecialchars($roomName); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Add Equipment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>




            </div>

            <?php if (!empty($equipmentData)) : ?>
                <?php foreach ($equipmentData as $labId => $labData) : ?>
                    <div class="table-responsive">
                        <div class="lab-header"><?php echo htmlspecialchars($labData['room_name']); ?></div>
                        <table class="table table-fixed">
                            <thead>
                                <tr>
                                    <th>Serial Number</th>
                                    <th>Name</th>
                                    <th>Brand</th>
                                    <th>Status</th>
                                    <th>Picture</th>
                                    <th>Quantity</th>
                                    <th>Date Purchased</th>
                                    <th>View</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($labData['equipments'] as $equipment) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($equipment['serial_number']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['brand']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['status']); ?></td>
                                        <td>
                                            <?php if (!empty($equipment['picture'])) : ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($equipment['picture']); ?>" alt="Equipment Picture">
                                            <?php else : ?>
                                                No image
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($equipment['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['date_purchased']); ?></td>
                                        <td>
                                            <button class="btn btn-info action-btn" data-toggle="modal" data-target="#viewModal" data-serial-number="<?php echo htmlspecialchars($equipment['serial_number']); ?>" data-name="<?php echo htmlspecialchars($equipment['name']); ?>" data-brand="<?php echo htmlspecialchars($equipment['brand']); ?>" data-status="<?php echo htmlspecialchars($equipment['status']); ?>" data-quantity="<?php echo htmlspecialchars($equipment['quantity']); ?>" data-date-purchased="<?php echo htmlspecialchars($equipment['date_purchased']); ?>" data-picture="<?php echo htmlspecialchars($equipment['picture']); ?>"><i class="fas fa-eye"></i></button>
                                        </td>
                                        <td>
                                            <!-- Button to trigger the Edit Equipment modal -->
                                            <button class="btn btn-primary action-btn" data-toggle="modal" data-target="#editEquipmentModal-<?php echo htmlspecialchars($equipment['serial_number']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Edit Equipment Modal -->
                                            <div class="modal fade" id="editEquipmentModal-<?php echo htmlspecialchars($equipment['serial_number']); ?>" tabindex="-1" role="dialog" aria-labelledby="editEquipmentModalLabel-<?php echo htmlspecialchars($equipment['serial_number']); ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <!-- Form to edit equipment details -->
                                                        <form id="edit-equipment-form-<?php echo htmlspecialchars($equipment['serial_number']); ?>" method="POST" enctype="multipart/form-data">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editEquipmentModalLabel-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-edit"></i> Edit Equipment: <?php echo htmlspecialchars($equipment['serial_number']); ?></h5>
                                                                <!-- Button to close the modal -->
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Serial Number -->
                                                                <div class="form-group">
                                                                    <label for="serial_number-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-barcode"></i> Serial Number:</label>
                                                                    <input type="text" class="form-control" id="serial_number-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="serial_number" value="<?php echo htmlspecialchars($equipment['serial_number']); ?>" readonly>
                                                                </div>

                                                                <!-- Equipment Name -->
                                                                <div class="form-group">
                                                                    <label for="name-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-tag"></i> Name:</label>
                                                                    <input type="text" class="form-control" id="name-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="name" value="<?php echo htmlspecialchars($equipment['name']); ?>" required>
                                                                </div>

                                                                <!-- Equipment Brand -->
                                                                <div class="form-group">
                                                                    <label for="brand-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-industry"></i> Brand:</label>
                                                                    <input type="text" class="form-control" id="brand-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="brand" value="<?php echo htmlspecialchars($equipment['brand']); ?>" required>
                                                                </div>

                                                                <!-- Equipment Quantity -->
                                                                <div class="form-group">
                                                                    <label for="quantity-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-cubes"></i> Quantity:</label>
                                                                    <input type="number" class="form-control" id="quantity-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="quantity" value="<?php echo htmlspecialchars($equipment['quantity']); ?>" required>
                                                                </div>

                                                                <!-- Equipment Status -->
                                                                <div class="form-group">
                                                                    <label for="equipmentStatus-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-info-circle"></i> Status</label>
                                                                    <select class="form-control" id="equipmentStatus-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="equipment_status" required>
                                                                        <option value="Functional" <?php echo ($equipment['status'] === 'Functional') ? 'selected' : ''; ?>>Functional</option>
                                                                        <option value="Under Maintenance" <?php echo ($equipment['status'] === 'Under Maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                                                        <option value="Out of Order" <?php echo ($equipment['status'] === 'Out of Order') ? 'selected' : ''; ?>>Out of Order</option>
                                                                    </select>
                                                                </div>

                                                                <!-- Date Purchased -->
                                                                <div class="form-group">
                                                                    <label for="date_purchased-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-calendar-alt"></i> Date Purchased:</label>
                                                                    <input type="date" class="form-control" id="date_purchased-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="date_purchased" value="<?php echo htmlspecialchars($equipment['date_purchased']); ?>" required>
                                                                </div>

                                                                <!-- Equipment Picture -->
                                                                <div class="form-group">
                                                                    <label for="picture-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-image"></i> Picture:</label>
                                                                    <input type="file" class="form-control-file" id="picture-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="picture" accept="image/*">
                                                                    <small class="form-text text-muted">Leave blank if you don't want to change the picture.</small>
                                                                </div>

                                                                <!-- Lab Room -->
                                                                <div class="form-group">
                                                                    <label for="lab_id-<?php echo htmlspecialchars($equipment['serial_number']); ?>"><i class="fas fa-door-open"></i> Lab Room:</label>
                                                                    <select class="form-control" id="lab_id-<?php echo htmlspecialchars($equipment['serial_number']); ?>" name="lab_id" required>
                                                                        <?php foreach ($labs as $labId => $roomName) : ?>
                                                                            <option value="<?php echo htmlspecialchars($labId); ?>" <?php echo ($equipment['lab_id'] == $labId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($roomName); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <!-- Button to close the modal without saving changes -->
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <!-- Button to submit the form and save changes -->
                                                                <button type="submit" class="btn btn-info">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Button to delete the equipment -->
                                            <button class="btn btn-danger action-btn" onclick="confirmDelete('<?php echo urlencode($equipment['serial_number']); ?>')"><i class="fas fa-trash-alt"></i></button>


                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="no-matched">No equipment matched your search.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Viewing Equipment Details -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye"></i> View Equipment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img id="modalPicture" src="" alt="Equipment Picture" class="img-fluid mb-3">
                    </div>
                    <p><strong><i class="fas fa-barcode"></i> Serial Number:</strong> <span id="modalSerialNumber"></span></p>
                    <p><strong><i class="fas fa-tag"></i> Name:</strong> <span id="modalName"></span></p>
                    <p><strong><i class="fas fa-industry"></i> Brand:</strong> <span id="modalBrand"></span></p>
                    <p><strong><i class="fas fa-info-circle"></i> Status:</strong> <span id="modalStatus"></span></p>
                    <p><strong><i class="fas fa-sort-numeric-up"></i> Quantity:</strong> <span id="modalQuantity"></span></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Date Purchased:</strong> <span id="modalDatePurchased"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Include SweetAlert CSS and JS files -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        // JavaScript for handling the modal
        $('#viewModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var serialNumber = button.data('serial-number');
            var name = button.data('name');
            var brand = button.data('brand');
            var status = button.data('status');
            var quantity = button.data('quantity');
            var datePurchased = button.data('date-purchased');
            var picture = button.data('picture');

            var modal = $(this);
            modal.find('#modalSerialNumber').text(serialNumber);
            modal.find('#modalName').text(name);
            modal.find('#modalBrand').text(brand);
            modal.find('#modalStatus').text(status);
            modal.find('#modalQuantity').text(quantity);
            modal.find('#modalDatePurchased').text(datePurchased);

            if (picture) {
                modal.find('#modalPicture').attr('src', '../uploads/' + picture).show();
            } else {
                modal.find('#modalPicture').hide();
            }
        });




        // JavaScript for handling delete confirmation
        function confirmDelete(id) {
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
                    // Redirect to the delete URL
                    window.location.href = 'delete_quipment.php?sn=' + id;
                }
            });
        }


        // Javascript for handing Adding Equipment
        $(document).ready(function() {
            $('#addEquipmentForm').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission

                var formData = new FormData(this);

                $.ajax({
                    url: 'add_equipment_process.php', // Server-side script to handle the form submission
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        try {
                            var res = typeof response === 'string' ? JSON.parse(response) : response;

                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Equipment added successfully!',
                                }).then(() => {
                                    location.reload(); // Reload the page to reflect changes
                                });

                                $('#addEquipmentModal').modal('hide');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.message,
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Parse Error!',
                                text: 'Error parsing JSON response: ' + e.message,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'AJAX Error!',
                            text: error,
                        });
                    }
                });
            });
        });

        // Javascript for handing Editing Equipment
        $(document).ready(function() {
            $('form[id^="edit-equipment-form"]').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                $.ajax({
                    url: 'edit_equipment.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Equipment details updated successfully.',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>