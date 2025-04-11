<?php
require 'db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($eventId) || empty($status) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }

    $column = match (strtolower($role)) {
        'hod' => 'hod_status',
        'dean' => 'dean_status',
        'principal' => 'principal_status',
        default => ''
    };

    if ($column === '') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE events SET $column = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $eventId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
    }
}
?>
