<?php
// edit_equipment.php
include '../includes/db.php';  // Include your database connection file

// Function to validate image file extensions
function validateImage($file)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($fileExtension, $allowedExtensions);
}

// Function to sanitize filenames
function sanitizeFilename($filename)
{
    // Remove any character that is not a letter, digit, dash, underscore, or dot
    return preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $serialNumber = htmlspecialchars($_POST['serial_number']);
    $name = htmlspecialchars($_POST['name']);
    $brand = htmlspecialchars($_POST['brand']);
    $quantity = htmlspecialchars($_POST['quantity']);
    $datePurchased = htmlspecialchars($_POST['date_purchased']);
    $status = htmlspecialchars($_POST['equipment_status']);
    $labId = htmlspecialchars($_POST['lab_id']);
    $picture = $_FILES['picture'];

    $pictureName = null;

    // Handle file upload if there is a file
    if ($picture['error'] == UPLOAD_ERR_OK) {
        if (validateImage($picture)) {
            $sanitizedFilename = sanitizeFilename(basename($picture['name']));
            $pictureName = time() . '_' . $sanitizedFilename;
            $targetFilePath = '../uploads/' . $pictureName;
            if (!move_uploaded_file($picture['tmp_name'], $targetFilePath)) {
                http_response_code(500);
                echo json_encode(['message' => 'File upload failed.']);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.']);
            exit;
        }
    }

    // Update equipment details in the database
    $query = "UPDATE equipment SET name = ?, brand = ?, quantity = ?, date_purchased = ?, status = ?, lab_id = ?";
    if ($pictureName) {
        $query .= ", picture = ?";
    }
    $query .= " WHERE serial_number = ?";

    if ($stmt = $conn->prepare($query)) {
        if ($pictureName) {
            $stmt->bind_param("ssisssss", $name, $brand, $quantity, $datePurchased, $status, $labId, $pictureName, $serialNumber);
        } else {
            $stmt->bind_param("ssissss", $name, $brand, $quantity, $datePurchased, $status, $labId, $serialNumber);
        }

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['message' => 'Equipment updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to update equipment.']);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Database error.']);
    }

    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
}
