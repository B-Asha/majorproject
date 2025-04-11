<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $role = $_POST['role'] ?? null;

    if (!$eventId || !$status || !$role) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }

    $column = '';
    if ($role === 'hod') {
        $column = 'hod_status';
    } elseif ($role === 'dean') {
        $column = 'dean_status';
    } elseif ($role === 'principal') {
        $column = 'principal_status';
    }

    if ($column === '') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE events SET $column = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $eventId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB update failed']);
    }
}
?>
