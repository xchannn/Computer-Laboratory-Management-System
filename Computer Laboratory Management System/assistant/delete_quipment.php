<?php
include '../includes/db.php';  // Include your database connection file

if (isset($_GET['sn'])) {
    $id = intval($_GET['sn']);  // Use intval to sanitize the input

    // Ensure the connection is successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to delete the record
    $sql = "DELETE FROM equipment WHERE serial_number = ?";

    // Prepare and bind
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $id);  // 'i' denotes integer
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle the error if the statement preparation fails
        die("Statement preparation failed: " . $conn->error);
    }

    // Close the connection
    $conn->close();

    // Redirect back to the equipment list page or any other page
    header("Location: equipments.php"); // Ensure this path is correct
    exit;
} else {
    // Handle the case where no id is provided
    die("No id provided for deletion.");
}
?>
