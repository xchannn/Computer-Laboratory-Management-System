<?php
session_start();
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assistant_id = $conn->real_escape_string($_POST['assistant_id']);
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $capacity = intval($_POST['capacity']);

    // Check if room name already exists
    $sqlCheck = "SELECT COUNT(*) as count FROM labs WHERE room_name = '$room_name'";
    $resultCheck = $conn->query($sqlCheck);
    $row = $resultCheck->fetch_assoc();

    if ($row['count'] > 0) {
        $response = ['success' => false, 'message' => 'Room name already exists'];
    } else {
        $sql = "INSERT INTO labs (assistant_id, room_name, capacity, updated_on) VALUES ('$assistant_id', '$room_name', $capacity, NOW())";
        if ($conn->query($sql) === TRUE) {
            $response = ['success' => true, 'message' => 'Laboratory added successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . $conn->error];
        }
    }

    echo json_encode($response);
}
$conn->close();
?>
