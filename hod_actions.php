<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($eventId && in_array($status, ['Approved', 'Rejected'])) {
        $stmt = $conn->prepare("UPDATE events SET dean_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $eventId);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Status updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid data"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
