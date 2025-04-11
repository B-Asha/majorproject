<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['photo'])) {
    $photoPath = $_POST['photo'];

    // Find the event that contains this photo
    $query = "SELECT id, event_photos FROM events WHERE FIND_IN_SET(?, event_photos)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $photoPath);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        $eventId = $event['id'];
        $photos = explode(',', $event['event_photos']);
        
        // Remove the deleted photo from the list
        $updatedPhotos = array_diff($photos, [$photoPath]);
        $newPhotoList = implode(',', $updatedPhotos);

        // Update database
        $updateQuery = "UPDATE events SET event_photos = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $newPhotoList, $eventId);
        
        if ($updateStmt->execute()) {
            // Delete photo from server
            if (file_exists($photoPath)) {
                unlink($photoPath); // Delete file
            }
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Photo not found in database."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
