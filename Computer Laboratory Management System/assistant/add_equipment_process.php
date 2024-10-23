<?php
session_start();

include '../includes/db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $equipment_name = isset($_POST['equipment_name']) ? trim($_POST['equipment_name']) : '';
        $equipment_brand = isset($_POST['equipment_brand']) ? trim($_POST['equipment_brand']) : '';
        $equipment_serial_number = isset($_POST['equipment_serial_number']) ? trim($_POST['equipment_serial_number']) : '';
        $equipment_quantity = isset($_POST['equipment_quantity']) ? intval($_POST['equipment_quantity']) : 0;
        $status = isset($_POST['status']) ? trim($_POST['status']) : '';
        $lab_id = isset($_POST['lab_id']) ? intval($_POST['lab_id']) : 0;
        $equipment_date_purchased = isset($_POST['equipment_date_purchased']) ? $_POST['equipment_date_purchased'] : '';

        // Logging inputs
        error_log("Received data:");
        error_log("Equipment Name: $equipment_name");
        error_log("Equipment Brand: $equipment_brand");
        error_log("Equipment Serial Number: $equipment_serial_number");
        error_log("Equipment Quantity: $equipment_quantity");
        error_log("Status: $status");
        error_log("Lab ID: $lab_id");
        error_log("Equipment Date Purchased: $equipment_date_purchased");

        if (empty($equipment_name) || empty($equipment_brand) || empty($equipment_serial_number) || $equipment_quantity <= 0 || empty($status) || $lab_id <= 0 || empty($equipment_date_purchased)) {
            throw new Exception('All fields are required and must be valid.');
        }

        $upload_file = null;
        if (isset($_FILES['equipment_picture']) && $_FILES['equipment_picture']['error'] == UPLOAD_ERR_OK) {
            $uploads_dir = '../uploads';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }
            $tmp_name = $_FILES['equipment_picture']['tmp_name'];
            $pic_name = basename($_FILES['equipment_picture']['name']);
            $upload_file = $uploads_dir . '/' . time() . '_' . $pic_name;

            if (!move_uploaded_file($tmp_name, $upload_file)) {
                throw new Exception('File upload failed.');
            }
        }

        // Prepare the SQL statement for inserting data
        $sql = "INSERT INTO equipment (name, brand, serial_number, quantity, status, date_purchased, lab_id, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }

        // Use prepared statement to prevent SQL injection
        $stmt->bind_param('sssissss', $equipment_name, $equipment_brand, $equipment_serial_number, $equipment_quantity, $status, $equipment_date_purchased, $lab_id, $upload_file);

        // Logging the statement execution
        error_log("Executing statement with: name=$equipment_name, brand=$equipment_brand, serial_number=$equipment_serial_number, quantity=$equipment_quantity, status=$status, date_purchased=$equipment_date_purchased, lab_id=$lab_id, picture=$upload_file");

        if (!$stmt->execute()) {
            throw new Exception('Execute statement failed: ' . $stmt->error);
        }

        echo json_encode(['status' => 'success', 'message' => 'Equipment added successfully!']);
    } else {
        throw new Exception('Invalid request method.');
    }
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
