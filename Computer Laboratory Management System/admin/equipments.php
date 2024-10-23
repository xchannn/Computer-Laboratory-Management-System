<?php
session_start();

// Include the database connection file
include('../includes/db.php');

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT equipment.id, equipment.serial_number, equipment.lab_id, equipment.name, equipment.status, equipment.brand, equipment.picture, equipment.quantity, equipment.date_purchased, labs.room_name 
        FROM equipment 
        LEFT JOIN labs ON equipment.lab_id = labs.lab_id";
if ($searchTerm) {
    $sql .= " WHERE equipment.name LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
              OR equipment.serial_number LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
              OR equipment.brand LIKE '%" . $conn->real_escape_string($searchTerm) . "%' 
              OR labs.room_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
}
$result = $conn->query($sql);

// Initialize an array to hold the data grouped by lab_id
$equipmentData = array();

// Process the result set and group data by lab_id
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
        body {
            background-color: #f4f4f9;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content {
            padding: 20px;
        }

        .container {
            margin-top: 20px;
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
            font-size: .75rem;
            /* Smaller font size */
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
        }

        .search-bar .btn {
            background-color: #125C92;
            color: #fff;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1px 20px;
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
            margin-top: 20px;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s, box-shadow 0.2s;
            border-radius: 50px;
            padding: .55px 10px;
        }

        .action-btn:hover {
            background-color: #218838;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content container-fluid dashboard-container">
        <div class="container">
            <h1 class="mb-4"><i class="fas fa-tools"></i> Equipments</h1>
            <div class="row search-bar">
                <div class="col-md-12">
                    <form id="search-form" method="GET" action="">
                        <div class="input-group">
                            <input type="text" id="search-input" name="search" class="form-control" placeholder="Search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div id="equipment-content">
                <?php if (empty($equipmentData)) { ?>
                    <p class="text-center no-matched">No matched items found.</p>
                <?php } else { ?>
                    <?php foreach ($equipmentData as $labId => $lab) { ?>
                        <div class="lab-header">
                            <h2><i class="fas fa-laptop"></i> <?php echo htmlspecialchars($lab['room_name']); ?></h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-fixed">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Serial Number</th>
                                        <th>Name</th>
                                        <th>Picture</th>
                                        <th>Brand</th>
                                        <th>Date Purchased</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lab['equipments'] as $index => $equipment) { ?>
                                        <tr>
                                            <td>
                                                <button class='btn btn-info btn-sm action-btn' data-toggle='modal' data-target='#viewModal' data-id='<?php echo $equipment['id']; ?>' data-serial_number='<?php echo htmlspecialchars($equipment['serial_number']); ?>' data-name='<?php echo htmlspecialchars($equipment['name']); ?>' data-picture='<?php echo htmlspecialchars($equipment['picture']); ?>' data-brand='<?php echo htmlspecialchars($equipment['brand']); ?>' data-date_purchased='<?php echo date("F j, Y", strtotime($equipment['date_purchased'])); ?>' data-quantity='<?php echo htmlspecialchars($equipment['quantity']); ?>' data-status='<?php echo htmlspecialchars($equipment['status']); ?>'><i class='fas fa-eye'></i> View</button>
                                            </td>
                                            <td><?php echo htmlspecialchars($equipment['serial_number']); ?></td>
                                            <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                                            <td><img src='../uploads/<?php echo htmlspecialchars($equipment['picture']); ?>' alt='photo' class='img-thumbnail'></td>
                                            <td><?php echo htmlspecialchars($equipment['brand']); ?></td>
                                            <td><?php echo date("F j, Y", strtotime($equipment['date_purchased'])); ?></td>
                                            <td><?php echo htmlspecialchars($equipment['quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($equipment['status']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel"><i class="fas fa-eye"></i> Equipment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img id="modalPicture" src="" alt="Equipment Picture" class="img-fluid mb-3" style="max-width: 100%; height: auto;">
                    </div>
                    <p><strong><i class="fas fa-barcode"></i> Serial Number:</strong> <span id="modalSerialNumber"></span></p>
                    <p><strong><i class="fas fa-tag"></i> Name:</strong> <span id="modalName"></span></p>
                    <p><strong><i class="fas fa-industry"></i> Brand:</strong> <span id="modalBrand"></span></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Date Purchased:</strong> <span id="modalDatePurchased"></span></p>
                    <p><strong><i class="fas fa-cubes"></i> Quantity:</strong> <span id="modalQuantity"></span></p>
                    <p><strong><i class="fas fa-info-circle"></i> Status:</strong> <span id="modalStatus"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#search-input').on('keyup', function() {
                let searchTerm = $(this).val();
                $.ajax({
                    url: '',
                    method: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        const htmlContent = $(response).find('#equipment-content').html();
                        $('#equipment-content').html(htmlContent);
                    }
                });
            });

            $('#search-form').on('submit', function(e) {
                e.preventDefault();
                let searchTerm = $('#search-input').val();
                $.ajax({
                    url: '',
                    method: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        const htmlContent = $(response).find('#equipment-content').html();
                        $('#equipment-content').html(htmlContent);
                    }
                });
            });

            $('#viewModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var serial_number = button.data('serial_number');
                var name = button.data('name');
                var picture = button.data('picture');
                var brand = button.data('brand');
                var date_purchased = button.data('date_purchased');
                var quantity = button.data('quantity');
                var status = button.data('status');

                var modal = $(this);
                modal.find('#modalSerialNumber').text(serial_number);
                modal.find('#modalName').text(name);
                modal.find('#modalPicture').attr('src', '../uploads/' + picture);
                modal.find('#modalBrand').text(brand);
                modal.find('#modalDatePurchased').text(date_purchased);
                modal.find('#modalQuantity').text(quantity);
                modal.find('#modalStatus').text(status);
            });
        });
    </script>
</body>

</html>