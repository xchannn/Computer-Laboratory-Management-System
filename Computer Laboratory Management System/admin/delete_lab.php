<?php
session_start();
include('../includes/db.php');

$response = array('success' => false, 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lab_id = $_POST['lab_id'];

    if (empty($lab_id)) {
        $response['message'] = 'Invalid request.';
    } else {
        $sql = "DELETE FROM labs WHERE lab_id = '$lab_id'";

        if ($conn->query($sql) === TRUE) {
            $response['success'] = true;
            $response['message'] = 'Laboratory deleted successfully.';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
    }
}

echo json_encode($response);

$conn->close();
?>
