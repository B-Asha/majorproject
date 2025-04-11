<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['event_photos'])) {
    $eventId = $_POST['id']; // Get event ID
    $uploadDir = 'uploads/events/'; // Folder to store event images

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedPaths = []; // Store image paths

    foreach ($_FILES['event_photos']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['event_photos']['name'][$key]);
        $filePath = $uploadDir . time() . '_' . $fileName; // Unique filename

        if (move_uploaded_file($tmpName, $filePath)) {
            $uploadedPaths[] = $filePath; // Save uploaded file path
        }
    }

    if (!empty($uploadedPaths)) {
        // Convert array to a comma-separated string
        $photosString = implode(',', $uploadedPaths);

        // Fetch existing photos from the database
        $selectQuery = "SELECT event_photos FROM events WHERE id = ?";
        $stmt = $conn->prepare($selectQuery);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $stmt->bind_result($existingPhotos);
        $stmt->fetch();
        $stmt->close();

        // Append new photos to existing ones
        $finalPhotos = !empty($existingPhotos) ? $existingPhotos . ',' . $photosString : $photosString;

        // Update the database with the new photo paths
        $updateQuery = "UPDATE events SET event_photos = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $finalPhotos, $eventId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "photos" => $uploadedPaths]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update database."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$conn->close();
?>