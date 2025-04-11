<?php
session_start();
require 'db_connection.php'; // adjust this to your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $status = $_POST['status'] ?? '';

    if (!$eventId || !in_array($status, ['Approved', 'Rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE events SET principal_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $eventId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
}
?>
