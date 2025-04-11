<?php 
session_start();
include('db_connection.php');

// Fetching events from the database
$upcomingEventsQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

$pastEventsQuery = "SELECT * FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC";
$pastEventsResult = $conn->query($pastEventsQuery);

// Handle event deletion
if (isset($_GET['delete_event']) && is_numeric($_GET['delete_event'])) {
    $eventId = $_GET['delete_event'];
    $eventId = mysqli_real_escape_string($conn, $eventId);
    
    // Delete the event from the events table
    $deleteEventQuery = "DELETE FROM events WHERE id = '$eventId'";
    if ($conn->query($deleteEventQuery) === TRUE) {
        // Delete event photos
        $deletePhotosQuery = "DELETE FROM event_photos WHERE event_id = '$eventId'";
        $conn->query($deletePhotosQuery);
        echo "<script>window.location.href = 'admin_dashboard.php';</script>";
    }
}

// Handle event insertion (new event form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['event_photos'])) {
    $eventName = $_POST['event_name'];
    $resourcePerson = $_POST['resource_person'];
    $eventDetails = $_POST['event_details'];
    $eventDate = $_POST['event_date'];
    $department = $_POST['department']; // New field for department

    $insertEventQuery = "INSERT INTO events (event_name, resource_person, event_details, event_date, department) 
                         VALUES ('$eventName', '$resourcePerson', '$eventDetails', '$eventDate', '$department')";
    
    if ($conn->query($insertEventQuery) === TRUE) {
        $eventId = $conn->insert_id;

        // Upload event photos
        $uploadedFiles = $_FILES['event_photos'];
        $fileCount = count($uploadedFiles['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $uploadedFiles['name'][$i];
            $fileTmpName = $uploadedFiles['tmp_name'][$i];
            $uploadDir = 'uploads/'; 
            $newFileName = uniqid('event_', true) . '_' . basename($fileName);

            if (move_uploaded_file($fileTmpName, $uploadDir . $newFileName)) {
                $insertPhotoQuery = "INSERT INTO event_photos (event_id, photo_path) 
                                     VALUES ('$eventId', '$newFileName')";
                $conn->query($insertPhotoQuery);
            }
        }

        // Upload resource person photo
        if (isset($_FILES['resource_person_photo']) && $_FILES['resource_person_photo']['error'] == 0) {
            $resourcePersonPhoto = $_FILES['resource_person_photo'];
            $resourcePersonFileName = $resourcePersonPhoto['name'];
            $resourcePersonFileTmp = $resourcePersonPhoto['tmp_name'];
            $resourcePersonFileNewName = uniqid('resource_', true) . '_' . basename($resourcePersonFileName);
            
            if (move_uploaded_file($resourcePersonFileTmp, $uploadDir . $resourcePersonFileNewName)) {
                // Update resource person's photo path
                $updateResourcePhotoQuery = "UPDATE events SET resource_person_photo = '$resourcePersonFileNewName' 
                                             WHERE id = '$eventId'";
                $conn->query($updateResourcePhotoQuery);
            }
        }

        echo "<script>window.location.href = 'admin_dashboard.php';</script>";  // Refresh page
    } else {
        echo "Error: " . $conn->error;
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
                        Resource Person: <?= htmlspecialchars($row['resource_person']); ?><br>
                        Department: <?= htmlspecialchars($row['department']); ?><br>
                        Details: <?= htmlspecialchars($row['event_details']); ?><br>

                        <!-- Display resource person photo -->
                        <?php if (!empty($row['resource_person_photo'])): ?>
                            <strong>Resource Person Photo:</strong><br>
                            <img src="uploads/<?= htmlspecialchars($row['resource_person_photo']); ?>" width="100"><br>
                        <?php endif; ?>

                        <!-- Display event photos -->
                        <strong>Event Photos:</strong><br>
                        <?php
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
                        Resource Person: <?= htmlspecialchars($row['resource_person']); ?><br>
                        Department: <?= htmlspecialchars($row['department']); ?><br>
                        Details: <?= htmlspecialchars($row['event_details']); ?><br>

                        <!-- Display resource person photo -->
                        <?php if (!empty($row['resource_person_photo'])): ?>
                            <strong>Resource Person Photo:</strong><br>
                            <img src="uploads/<?= htmlspecialchars($row['resource_person_photo']); ?>" width="100"><br>
                        <?php endif; ?>

                        <!-- Display event photos -->
                        <strong>Event Photos:</strong><br>
                        <?php
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
            <div class="mb-3">
                <label for="resource_person_photo" class="form-label">Resource Person Photo</label>
                <input type="file" class="form-control" name="resource_person_photo" required>
            </div>
            <button type="submit" class="btn btn-success">Add Event</button>
        </form>
    </div>

    <script>
        // Function to fetch the latest data
        function fetchAdminData() {
            $.ajax({
                url: 'fetch_admin_data.php', // PHP file for fetching events
                method: 'GET',
                success: function(response) {
                    const data = JSON.parse(response);

                    // Update the upcoming events section
                    $('#upcoming-events').html(data.upcomingEvents);

                    // Update the past events section
                    $('#past-events').html(data.pastEvents);
                },
                error: function() {
                    alert('Error fetching data.');
                }
            });
        }

        // Auto-refresh every 5 minutes (300000 ms)
        setInterval(fetchAdminData, 300000);

        // Initial data load
        fetchAdminData();
    </script>
</body>
</html>
</body>
</html>
