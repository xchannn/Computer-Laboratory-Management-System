<?php
session_start();

// Include the database connection file
include('../includes/db.php');

// Check if the assistant is logged in and has an assistant_id assigned
if (!isset($_SESSION['assistant_id'])) {
    die(json_encode(array("error" => "You are not authorized to view this page.")));
}

$assistantId = intval($_SESSION['assistant_id']);

// Check if an equipment ID is provided
if (!isset($_GET['id'])) {
    die(json_encode(array("error" => "No equipment ID provided.")));
}

$equipmentId = intval($_GET['id']);

// Query to verify the assistant's access to the equipment
$sql = "SELECT equipment.id, equipment.serial_number, equipment.name, equipment.status, equipment.brand, equipment.picture, equipment.quantity, equipment.date_purchased, labs.room_name 
        FROM equipment 
        INNER JOIN labs ON equipment.lab_id = labs.lab_id 
        INNER JOIN users ON labs.assistant_id = users.id
        WHERE equipment.id = ? AND users.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $equipmentId, $assistantId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $equipment = $result->fetch_assoc();
    echo json_encode($equipment);
} else {
    echo json_encode(array("error" => "No equipment found or you are not authorized to view this equipment."));
}

$stmt->close();
$conn->close();
?>
