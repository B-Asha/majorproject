<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'], $_POST['status'])) {
    $eventId = intval($_POST['event_id']);
    $status = $_POST['status'];

    // Validate status
    if (!in_array($status, ['Approved', 'Rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit;
    }

    // Update dean_status in the database
    $stmt = $conn->prepare("UPDATE events SET dean_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $eventId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
