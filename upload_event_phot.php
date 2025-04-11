<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['event_photos'])) {
    $eventId = $_POST['id'];
    $uploadDir = 'uploads/';
    $uploadedFiles = [];

    foreach ($_FILES['event_photos']['name'] as $key => $photoName) {
        $photoPath = $uploadDir . uniqid() . '_' . $photoName;
        move_uploaded_file($_FILES['event_photos']['tmp_name'][$key], $photoPath);
        $uploadedFiles[] = $photoPath;
    }

    // Fetch existing event photos
    $result = $conn->query("SELECT event_photo FROM events WHERE id = $eventId");
    $row = $result->fetch_assoc();
    $existingPhotos = !empty($row['event_photo']) ? explode(',', $row['event_photo']) : [];

    // Merge existing photos and new uploads
    $allPhotos = array_merge($existingPhotos, $uploadedFiles);

    // Update event photos in the database
    $newPhotoPath = implode(',', $allPhotos);
    $conn->query("UPDATE events SET event_photo = '$newPhotoPath' WHERE id = $eventId");

    echo json_encode(['status' => 'success']);
}
?>
