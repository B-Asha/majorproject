<?php
session_start();
require_once 'db_connection.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if dean is logged in
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dean') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    // Sanitize and fetch inputs
    $event_id = intval($_POST['event_id']);
    $status = $_POST['status'];

    // Validate status
    if (!in_array($status, ['Approved', 'Rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }

    // Update dean_status in the events table
    $stmt = $conn->prepare("UPDATE events SET dean_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $event_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
