<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $role = $_POST['role'];
    $action = $_POST['action'];

    $status = $action === 'approve' ? 'Approved' : 'Rejected';

    // Get the event details first to check who added it
    $query = $conn->prepare("SELECT added_by_role FROM events WHERE id = ?");
    $query->bind_param("i", $event_id);
    $query->execute();
    $result = $query->get_result();
    $event = $result->fetch_assoc();

    if (!$event) {
        echo "Event not found";
        exit;
    }

    $added_by_role = strtolower($event['added_by_role']);

    if ($role === 'principal') {
        if ($added_by_role === 'dean') {
            // Dean-added event: update ONLY principal_status and force hod and dean status to Pending
            $stmt = $conn->prepare("UPDATE events SET principal_status = ?, hod_status = 'Pending', dean_status = 'Pending' WHERE id = ?");
            $stmt->bind_param("si", $status, $event_id);
        } else {
            // All other events: just update principal_status
            $stmt = $conn->prepare("UPDATE events SET principal_status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $event_id);
        }
    } elseif ($role === 'dean') {
        $stmt = $conn->prepare("UPDATE events SET dean_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $event_id);
    } elseif ($role === 'hod') {
        $stmt = $conn->prepare("UPDATE events SET hod_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $event_id);
    } else {
        echo "Invalid role";
        exit;
    }

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
    $query->close();
    $conn->close();
}
?>
