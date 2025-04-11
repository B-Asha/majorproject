<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'];
    $status = $_POST['status'];

    // Sanitize inputs
    $eventId = intval($eventId);
    $status = in_array($status, ['Approved', 'Rejected']) ? $status : 'Pending';

    $stmt = $conn->prepare("UPDATE events SET dean_status = ? WHERE id = ? AND dean_status = 'Pending'");
    $stmt->bind_param("si", $status, $eventId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
