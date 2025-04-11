<?php
require('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'];
    $role = $_POST['role']; // 'principal', 'dean', 'hod'
    $action = $_POST['action']; // 'approve' or 'reject'

    // Determine which column to update
    $statusColumn = '';
    if ($role === 'hod') {
        $statusColumn = 'hod_status';
    } elseif ($role === 'dean') {
        $statusColumn = 'dean_status';
    } elseif ($role === 'principal') {
        $statusColumn = 'principal_status';
    }

    if ($statusColumn !== '') {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $stmt = $conn->prepare("UPDATE events SET $statusColumn = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $eventId);
        if ($stmt->execute()) {
            echo 'Success';
        } else {
            echo 'Error updating status';
        }
    } else {
        echo 'Invalid role';
    }
}
?>
