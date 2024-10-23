<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("SELECT * FROM hf202_equipment WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment = $result->fetch_assoc();

    echo json_encode($equipment);

    $stmt->close();
    $conn->close();
}
?>
