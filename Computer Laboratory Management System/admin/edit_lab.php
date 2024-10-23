<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lab_id = intval($_POST['lab_id']);
    $assistant_id = $conn->real_escape_string($_POST['assistant_id']);
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $capacity = intval($_POST['capacity']);

    // Check if room name already exists (excluding the current lab)
    $sqlCheck = "SELECT COUNT(*) as count FROM labs WHERE room_name = '$room_name' AND lab_id != $lab_id";
    $resultCheck = $conn->query($sqlCheck);
    $row = $resultCheck->fetch_assoc();

    if ($row['count'] > 0) {
        $response = ['success' => false, 'message' => 'Room name already exists'];
    } else {
        $sql = "UPDATE labs SET assistant_id = '$assistant_id', room_name = '$room_name', capacity = $capacity, updated_on = NOW() WHERE lab_id = $lab_id";
        if ($conn->query($sql) === TRUE) {
            $response = ['success' => true, 'message' => 'Laboratory updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . $conn->error];
        }
    }

    echo json_encode($response);
}
$conn->close();
?>
