<?php
// Include the database connection (mysqli version)
include('db_connection.php');

// Get the current date to classify events as past or upcoming
$currentDate = date('Y-m-d');

// Fetch Upcoming Events (Events scheduled for today or in the future)
$upcomingEventsQuery = "SELECT id, event_name, resource_person, event_details, event_date, department, event_photo FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Fetch Past Events (Events that have already occurred)
$pastEventsQuery = "SELECT id, event_name, resource_person, event_details, event_date, department, event_photo FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC";
$pastEventsResult = $conn->query($pastEventsQuery);

// Handle event deletion
if (isset($_GET['delete_event']) && is_numeric($_GET['delete_event'])) {
    $eventId = $_GET['delete_event'];

    // Sanitize the event ID to prevent SQL injection
    $eventId = mysqli_real_escape_string($conn, $eventId);

    // Check if the event_id exists in the database before deleting
    $checkQuery = "SELECT * FROM events WHERE id = '$eventId'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        // Delete the event from the database
        $deleteQuery = "DELETE FROM events WHERE id = '$eventId'";

        if ($conn->query($deleteQuery) === TRUE) {
            // Delete the photos associated with the event
            $deletePhotosQuery = "DELETE FROM event_photos WHERE event_id = '$eventId'";
            $conn->query($deletePhotosQuery);
            echo "<script>window.location.href = 'admin_dashboard.php';</script>";  // Refresh the page
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Event not found.";
    }
}

// Handle event insertion (if the form for adding events is handled here)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['event_photos'])) {
    // Insert event into the database
    $eventName = $_POST['event_name'];
    $resourcePerson = $_POST['resource_person'];
    $eventDetails = $_POST['event_details'];
    $eventDate = $_POST['event_date'];
    $department = $_POST['department']; // Get the department input

    // Insert the main event data into the `events` table
    $insertQuery = "INSERT INTO events (event_name, resource_person, event_details, event_date, department) 
                    VALUES ('$eventName', '$resourcePerson', '$eventDetails', '$eventDate', '$department')";
    if ($conn->query($insertQuery) === TRUE) {
        // Get the event ID of the newly inserted event
        $eventId = $conn->insert_id;

        // Process the uploaded photos (multiple files)
        $uploadedFiles = $_FILES['event_photos'];
        $fileCount = count($uploadedFiles['name']);

        // Loop through each uploaded file
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $uploadedFiles['name'][$i];
            $fileTmpName = $uploadedFiles['tmp_name'][$i];

            // Define the upload directory (ensure the "uploads" directory exists)
            $uploadDir = 'uploads/';

            // Generate a unique file name for each photo
            $newFileName = uniqid('event_', true) . '_' . basename($fileName);

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($fileTmpName, $uploadDir . $newFileName)) {
                // Insert the file path into the event_photos table
                $insertPhotoQuery = "INSERT INTO event_photos (event_id, photo_path) 
                                     VALUES ('$eventId', '$newFileName')";
                $conn->query($insertPhotoQuery);
            } else {
                echo "Error uploading file: " . $fileName;
            }
        }

        echo "<script>window.location.href = 'admin_dashboard.php';</script>";  // Refresh the page
    } else {
        echo "Error: " . $insertQuery . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center text-secondary">Admin Dashboard</h1>
        <a href="login.php" class="btn btn-danger float-end">Logout</a>

        <h2 class="mt-4">Upcoming Events</h2>
        <?php if ($upcomingEventsResult->num_rows > 0): ?>
            <ul class="list-group">
                <?php while($row = $upcomingEventsResult->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($row['event_name']); ?></strong> (<?= htmlspecialchars($row['event_date']); ?>)<br>
                        Department: <?= htmlspecialchars($row['department']); ?><br>
                        Resource Person: <?= htmlspecialchars($row['resource_person']); ?><br>
                        Details: <?= htmlspecialchars($row['event_details']); ?><br>
                        <?php
                            // Display photos for each event
                            $eventPhotosQuery = "SELECT photo_path FROM event_photos WHERE event_id = '{$row['id']}'";
                            $eventPhotosResult = $conn->query($eventPhotosQuery);
                            while ($photo = $eventPhotosResult->fetch_assoc()) {
                                echo '<img src="uploads/' . htmlspecialchars($photo['photo_path']) . '" width="100"><br>';
                            }
                        ?>
                        <a href="admin_dashboard.php?delete_event=<?= $row['id']; ?>" class="btn btn-danger btn-sm mt-2">Delete</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No upcoming events found.</p>
        <?php endif; ?>

        <h2 class="mt-4">Past Events</h2>
        <?php if ($pastEventsResult->num_rows > 0): ?>
            <ul class="list-group">
                <?php while($row = $pastEventsResult->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($row['event_name']); ?></strong> (<?= htmlspecialchars($row['event_date']); ?>)<br>
                        Department: <?= htmlspecialchars($row['department']); ?><br>
                        Resource Person: <?= htmlspecialchars($row['resource_person']); ?><br>
                        Details: <?= htmlspecialchars($row['event_details']); ?><br>
                        <?php
                            // Display photos for each event
                            $eventPhotosQuery = "SELECT photo_path FROM event_photos WHERE event_id = '{$row['id']}'";
                            $eventPhotosResult = $conn->query($eventPhotosQuery);
                            while ($photo = $eventPhotosResult->fetch_assoc()) {
                                echo '<img src="uploads/' . htmlspecialchars($photo['photo_path']) . '" width="100"><br>';
                            }
                        ?>
                        <a href="admin_dashboard.php?delete_event=<?= $row['id']; ?>" class="btn btn-danger btn-sm mt-2">Delete</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No past events found.</p>
        <?php endif; ?>

        <h2 class="mt-4">Add New Event</h2>
        <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" name="event_name" required>
            </div>
            <div class="mb-3">
                <label for="resource_person" class="form-label">Resource Person</label>
                <input type="text" class="form-control" name="resource_person" required>
            </div>
            <div class="mb-3">
                <label for="event_details" class="form-label">Event Details</label>
                <textarea class="form-control" name="event_details" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" name="event_date" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" name="department" required>
            </div>
            <div class="mb-3">
                <label for="event_photos" class="form-label">Event Photos</label>
                <input type="file" class="form-control" name="event_photos[]" multiple required>
            </div>
            <button type="submit" class="btn btn-success">Add Event</button>
        </form>
    </div>
</body>
</html>
