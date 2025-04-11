<?php
session_start();
include('db_connection.php');

// Check if event ID is provided in the POST request
if (isset($_POST['id'])) {
    $eventId = $_POST['id'];

    // Prepare the SQL query to delete the event by its ID
    $query = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    // Bind the event ID and execute the deletion query
    $stmt->bind_param('i', $eventId);
    
    if ($stmt->execute()) {
        // Return success response if the event was deleted
        echo json_encode(['status' => 'success']);
    } else {
        // Return error response if there was an issue with deletion
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete event']);
    }
    
    // Close the prepared statement
    $stmt->close();
} else {
    // Return error response if no event ID was provided
    echo json_encode(['status' => 'error', 'message' => 'Event ID not provided']);
}
?>
