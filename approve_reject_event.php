<?php
// Include database connection
include('db_connection.php');

// Check if the necessary data is sent
if (isset($_POST['event_id']) && isset($_POST['action'])) {
    $eventId = $_POST['event_id']; // Event ID from the button click
    $action = $_POST['action']; // Action - either 'approve' or 'reject'

    // Validate the action and ensure it's either 'approve' or 'reject'
    if ($action == 'approve' || $action == 'reject') {
        
        // Prepare the query to update the event status based on the action
        $status = ($action == 'approve') ? 'Approved' : 'Rejected';

        // Update event status in the database
        $updateQuery = "UPDATE events SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('si', $status, $eventId); // 'si' means string and integer parameters
        if ($stmt->execute()) {
            echo "Event has been " . $status . " successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Event ID or action not set.";
}

// Close the database connection
$conn->close();
?>
