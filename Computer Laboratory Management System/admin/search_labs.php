<?php
include('../includes/db.php');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT lab_id, assistant_id, room_name, capacity, updated_on FROM labs WHERE room_name LIKE '%$search%' OR assistant_id LIKE '%$search%'";
$result = $conn->query($sql);

function getAssistantName($conn, $assistant_id) {
    $sql = "SELECT name FROM users WHERE id = '$assistant_id' AND role = 'assistant'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    return 'Unknown';
}

if ($result->num_rows > 0) {
    echo '<div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>Room ID</th>
                        <th>Assistant Incharge</th>
                        <th>Room Name</th>
                        <th>Capacity</th>
                        <th>Date Updated</th>
                    </tr>
                </thead>
                <tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>
                    <button class="btn btn-info btn-sm action-btn" onclick="editLab(' . $row["lab_id"] . ')"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-danger btn-sm action-btn" onclick="deleteLab(' . $row["lab_id"] . ')"><i class="fas fa-trash"></i> Delete</button>
                </td>
                <td>' . $row["lab_id"] . '</td>
                <td>' . getAssistantName($conn, $row["assistant_id"]) . '</td>
                <td>' . $row["room_name"] . '</td>
                <td>' . $row["capacity"] . '</td>
                <td>' . date("F j, Y", strtotime($row["updated_on"])) . '</td>
              </tr>';
    }
    echo '  </tbody>
            </table>
          </div>';
} else {
    echo '<p class="text-center no-matched">No matched items found.</p>';
}
$conn->close();
?>
