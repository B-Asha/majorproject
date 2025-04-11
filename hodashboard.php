<?php
session_start();
include('db_connection.php');

// Fetch upcoming events
$upcomingEventsQuery = "
    SELECT e.*, ec.category_name
    FROM events e
    LEFT JOIN event_categories ec ON e.category = ec.id
    WHERE e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Fetch past events
$pastEventsQuery = "
    SELECT e.*, ec.category_name
    FROM events e
    LEFT JOIN event_categories ec ON e.category = ec.id
    WHERE e.event_date < CURDATE()
    ORDER BY e.event_date DESC
";
$pastEventsResult = $conn->query($pastEventsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center text-primary">HOD Dashboard</h1>
        <a href="login.php" class="btn btn-danger float-end">Logout</a>

        <!-- Add Event Form -->
        <h2 class="mt-4">Add New Event</h2>
        <form id="add-event-form" method="POST" enctype="multipart/form-data">
            <!-- Form fields (as you had them) -->
            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="event_name" name="event_name" required>
            </div>
            <div class="mb-3">
                <label for="event_details" class="form-label">Event Details</label>
                <textarea class="form-control" id="event_details" name="event_details" required></textarea>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required>
            </div>
            <div class="mb-3">
                <label for="resource_person" class="form-label">Resource Person Name</label>
                <input type="text" class="form-control" id="resource_person" name="resource_person" required>
            </div>
            <div class="mb-3">
                <label for="resource_person_photo" class="form-label">Resource Person Photo</label>
                <input type="file" class="form-control" id="resource_person_photo" name="resource_person_photo" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-control" id="department" name="department" required>
                    <option value="CSE">CSE</option>
                    <option value="EEE">EEE</option>
                    <option value="ECE">ECE</option>
                    <option value="CSD">CSD</option>
                    <option value="AI">AI</option>
                    <option value="AIML">AIML</option>
                    <option value="MECH">MECH</option>
                    <option value="MCA">MCA</option>
                    <option value="MBA">MBA</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Event Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="1">Seminar</option>
                    <option value="2">Webinar</option>
                    <option value="3">FDP</option>
                    <option value="4">Festival Event</option>
                    <option value="5">Alumini Event</option>
                    <option value="6">work Shop</option>
                    <option value="7">Guest Lecture</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>

        <!-- Upcoming Events Table -->
        <h2 class="mt-4">Upcoming Events</h2>
        <table class="table table-bordered" id="events-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Resource Person</th>
                    <th>Category</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $upcomingEventsResult->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id']; ?>">
                        <td><?= htmlspecialchars($row['event_name']); ?></td>
                        <td><?= htmlspecialchars($row['event_date']); ?></td>
                        <td><?= htmlspecialchars($row['resource_person']); ?></td>
                        <td><?= htmlspecialchars($row['category_name']); ?></td>
                        <td><?= htmlspecialchars($row['department']); ?></td>
                        <td><span class="badge bg-warning">Upcoming</span></td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?= $row['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Past Events Table -->
        <h2 class="mt-4">Past Events</h2>
        <table class="table table-bordered" id="past-events-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Resource Person</th>
                    <th>Category</th>
                    <th>Department</th>
                    <th>Event Photos</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $pastEventsResult->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id']; ?>">
                        <td><?= htmlspecialchars($row['event_name']); ?></td>
                        <td><?= htmlspecialchars($row['event_date']); ?></td>
                        <td><?= htmlspecialchars($row['resource_person']); ?></td>
                        <td><?= htmlspecialchars($row['category_name']); ?></td>
                        <td><?= htmlspecialchars($row['department']); ?></td>
                        <td>
                            <div id="photos-<?= $row['id']; ?>">
                                <?php if (!empty($row['event_photo'])): ?>
                                    <?php
                                    $photoPaths = explode(',', $row['event_photo']);
                                    foreach ($photoPaths as $photoPath):
                                    ?>
                                        <div class="photo-container">
                                            <img src="<?= htmlspecialchars($photoPath); ?>" width="100" class="m-1">
                                            <button class="btn btn-danger btn-sm delete-photo-btn" data-event-id="<?= $row['id']; ?>" data-photo-path="<?= $photoPath; ?>">Delete Photo</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No photos uploaded.</p>
                                <?php endif; ?>
                            </div>
                            <form id="upload-form-<?= $row['id']; ?>" class="mt-2" style="display:none;" method="POST" enctype="multipart/form-data">
                                <input type="file" name="event_photos[]" accept="image/*" multiple required>
                                <button type="submit" class="btn btn-success btn-sm">Upload Photos</button>
                            </form>
                            <button class="btn btn-primary btn-sm mt-2" onclick="toggleUploadForm(<?= $row['id']; ?>)">Upload Photos</button>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?= $row['id']; ?>)">Delete Event</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Toggle visibility of the upload form
        function toggleUploadForm(eventId) {
            var form = document.getElementById('upload-form-' + eventId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Handle uploading of photos
        $(document).on('submit', 'form[id^="upload-form-"]', function(event) {
            event.preventDefault();

            var form = $(this);
            var formData = new FormData(this);
            var eventId = form.attr('id').split('-')[2]; // Extract event ID from form ID

            $.ajax({
                url: 'upload_event_photo.php',  // Make sure you create this file to handle the upload
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert('Photos uploaded successfully!');
                        // Refresh the photos display
                        $('#photos-' + eventId).html(data.photosHtml);
                    } else {
                        alert('Error uploading photos.');
                    }
                },
                error: function() {
                    alert('Error with the AJAX request.');
                }
            });
        });

        // Delete event via AJAX
        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                $.ajax({
                    url: 'delete_event.php',
                    type: 'POST',
                    data: { id: eventId },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            $('tr[data-id="' + eventId + '"]').remove();
                            alert('Event deleted successfully!');
                        } else {
                            alert('Error deleting event.');
                        }
                    },
                    error: function() {
                        alert('Error with the AJAX request.');
                    }
                });
            }
        }

        // Handle adding new event
        $(document).ready(function() {
            $('#add-event-form').submit(function(event) {
                event.preventDefault();
                var form = $(this);
                var formData = new FormData(this);

                $.ajax({
                    url: 'add_event.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            alert('Event added successfully!');
                            // Append new event to the upcoming events table
                            var newEventHtml = '<tr><td>' + data.event_name + '</td><td>' + data.event_date + '</td><td>' + data.resource_person + '</td><td>' + data.category + '</td><td>' + data.department + '</td><td><button class="btn btn-danger btn-sm" onclick="deleteEvent(' + data.id + ')">Delete</button></td></tr>';
                            $('#events-table tbody').append(newEventHtml);
                        } else {
                            alert('Error adding event.');
                        }
                    }
                });
            });
        });

        // Delete photo functionality
        $(document).on('click', '.delete-photo-btn', function() {
            var eventId = $(this).data('event-id');
            var photoPath = $(this).data('photo-path');
            if (confirm('Are you sure you want to delete this photo?')) {
                $.ajax({
                    url: 'delete_photo.php',
                    type: 'POST',
                    data: { event_id: eventId, photo_path: photoPath },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            alert('Photo deleted successfully!');
                            $('button[data-photo-path="' + photoPath + '"]').closest('.photo-container').remove();
                        } else {
                            alert('Error deleting photo.');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
