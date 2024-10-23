<?php
session_start();
include('../includes/db.php');

if (isset($_GET['lab_id'])) {
    $lab_id = intval($_GET['lab_id']);
    $sql = "SELECT lab_id, assistant_id, room_name, capacity FROM labs WHERE lab_id = $lab_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lab not found']);
    }
}
$conn->close();
?>
