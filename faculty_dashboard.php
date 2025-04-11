<?php
session_start();


include('db_connection.php');


// ✅ Define SQL before using it
$sql = "
    SELECT 
        e.*, 
        u.role AS added_by_role
    FROM events e
    JOIN users u ON e.added_by = u.id
    ORDER BY e.event_date DESC
";

// ✅ Use the correct variable in query
$result = $conn->query($sql);



// Assuming the department of the logged-in faculty is stored in the session
$facultyDepartment = $_SESSION['department'];  // Fetching department from the session

// Fetch upcoming events related to the faculty's department
$upcomingEventsQuery = "
    SELECT e.*, ec.category_name, e.hod_status, e.dean_status, e.principal_status
    FROM events e

    LEFT JOIN event_categories ec ON e.category = ec.id
    WHERE e.event_date >= CURDATE() AND e.department = '$facultyDepartment'
    ORDER BY e.event_date ASC
";


$result = $conn->query($sql);

$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Fetch past events related to the faculty's department
$pastEventsQuery = "
    SELECT e.*, ec.category_name, e.hod_status, e.dean_status, e.principal_status, e.event_photos
    FROM events e
    LEFT JOIN event_categories ec ON e.category = ec.id
    WHERE e.event_date < CURDATE() AND e.department = '$facultyDepartment'
    ORDER BY e.event_date DESC
";
$pastEventsResult = $conn->query($pastEventsQuery);

// Function to get status badge
function getStatusBadge($status) {
    if ($status === 'Approved') {
        return '<span class="badge bg-success">✅ Approved</span>';
    } elseif ($status === 'Rejected') {
        return '<span class="badge bg-danger">❌ Rejected</span>';
    } else {
        return '<span class="badge bg-warning">⏳ Pending</span>';
    }
}

// Function to determine final event status
function getFinalStatus($hod, $dean, $principal) {
    if ($hod === 'Approved' && $dean === 'Approved' && $principal === 'Approved') {
        return '<span class="badge bg-primary">🚀 Proceed</span>';
    } elseif ($hod === 'Rejected' || $dean === 'Rejected' || $principal === 'Rejected') {
        return '<span class="badge bg-danger">❌ Rejected</span>';
    } else {
        return '<span class="badge bg-warning">⏳ Pending</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
      

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 80px;

      background-color: #f5f5f5; /* light gray background */

      display: flex;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      z-index: 999;
    }

    .logo {
      height: 90px;
      margin-right: 20px;
    }

    .heading {
      font-size: 28px;
      font-weight: bold;
      color: #333;
    }

    
   
    /* Heading style */
.container h1 {
    color: #2dd62d;
    font-size: 2.5rem;
    margin-bottom: 30px;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
}


.centered-heading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: clamp(20px, 2.5vw, 32px);

  font-weight: 900;
  background: linear-gradient(90deg, #ff6ec4, #7873f5, #4ade80, #facc15, #f87171);
  background-size: 300%;
  animation: gradientShift 6s infinite linear;
  background-clip: text;
-webkit-background-clip: text;

  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5), 2px 2px 8px rgba(0, 0, 0, 0.3);
  letter-spacing: 2px;
  white-space: nowrap;
  font-family: 'Segoe UI', 'Poppins', sans-serif;
  text-align: center;
  z-index: 10;
}

@keyframes gradientShift {
  0% { background-position: 0% }
  100% { background-position: 100% }
}
.space {
    padding-top: 80px; /* same or more than header height */
}


    </style>
</head>
<body>
    
<header>
        <img src="logo.png" alt="College Logo" class="logo" />
        <div class="centered-heading">SITAMS ACTIVITY MANAGEMENT SYSTEM</div>
      </header>

<div class="space">
</div>
<div class="text-end text-muted mb-3">
    <span class="badge bg-light text-dark">
        Welcome, <?= htmlspecialchars($_SESSION['role']) ?> 
        (ID: <?= $_SESSION['user_id'] ?>, Dept: <?= htmlspecialchars($_SESSION['department']) ?>)
    </span>
</div>

  <div class="container mt-5">
        <h1 class="text-center text-primary"></h1>
        <a href="login.php" class="btn btn-danger float-end">Logout</a>

       
        <!-- Add Event Button -->
        <button class="btn btn-success mt-4" id="toggle-event-form">Add Event</button>

        <!-- Add Event Form Container (Initially Hidden) -->
        <div id="event-form-container" style="display: none;">
       
        <h2 class="mt-4" >Add New Event</h2>
        <form id="add-event-form" method="POST" enctype="multipart/form-data">
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
                <!-- Default department set to HOD's department -->
                <select class="form-control" id="department" name="department" required>
                    <option value="<?= $facultyDepartment; ?>" selected><?= $facultyDepartment; ?></option>
                </select>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Event Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="1">Seminar</option>
                    <option value="2">Webinar</option>
                    <option value="3">FDP</option>
                    <option value="4">Festival Event</option>
                    <option value="5">Alumni Event</option>
                    <option value="6">Workshop</option>
                    <option value="7">Guest Lecture</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>
</div>


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
                    <th>HOD Status</th>
                    <th>Dean Status</th>
                    <th>Principal Status</th>
                    <th>Status</th>
                    <th>Details</th>
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
                        <td>
    <?php if ($row['added_by_role'] === 'hod'): ?>
        <span class="badge bg-secondary">👨‍🏫 HOD Added Event</span>
    <?php else: ?>
        <?= getStatusBadge($row['hod_status']); ?>
    <?php endif; ?>
</td>
                        <td><?= getStatusBadge($row['dean_status']); ?></td>
                    
                        <td><?= getStatusBadge($row['principal_status']); ?></td>
                      
                       
                        <td>
                        <?php
    $hod = ($row['hod_status']);
    $dean = ($row['dean_status']);
    $principal = ($row['principal_status']);

    if ($hod === 'Rejected' || $dean === 'Rejected' || $principal === 'Rejected') {
        echo '<span class="badge bg-danger">❌ Not Proceed</span>';
    } elseif (
        ($hod === 'Approved' && $dean === 'Approved' && $principal === 'Approved') ||
        ($hod === 'Pending' && $dean === 'Approved' && $principal === 'Approved')
    )
    {
       
        echo '<span class="badge bg-success">✅ Proceed</span>';
    } else {
        echo '<span class="badge bg-warning">⏳ Pending</span>';
    }
    ?>
</td>





                        <td>
    <a href="event_details.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
</td>
                        <td>
                        <?php if ($_SESSION['user_id'] == $row['added_by']): ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?= $row['id']; ?>)">Delete</button>
                        <?php endif; ?>
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
                    <th>Details</th>
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
                                        if (!empty(trim($photoPath))): // Ensure non-empty values are processed
                                    ?>
                                    <div class="photo-container" style="display: inline-block; margin: 5px; text-align: center;">
                <img src="<?= htmlspecialchars($photoPath); ?>" width="100" class="m-1" style="display: block;">
                <button class="btn btn-danger btn-sm delete-photo-btn" 
                        data-event-id="<?= $row['id']; ?>" 
                        data-photo-path="<?= $photoPath; ?>">
                    Delete Photo
                </button>
            </div>
            <?php 
            endif;
        endforeach; 
        ?>
    <?php else: ?>
        <p></p>
    <?php endif; ?>
</div>
<?php if ($_SESSION['user_id'] == $row['added_by']): ?>

<form id="upload-form-<?= $row['id']; ?>" class="mt-2" style="display:none;" method="POST" enctype="multipart/form-data">
    <input type="file" name="event_photos[]" accept="image/*" multiple required>
    <button type="submit" class="btn btn-success btn-sm mt-2">Upload Photos</button>
</form>
<button class="btn btn-primary btn-sm mt-2" onclick="toggleUploadForm(<?= $row['id']; ?>)">Upload Photos</button>
<?php endif; ?>
                        </td>
                        <td>
    <a href="event_details.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
</td>
                        <td>
                        <?php if ($_SESSION['user_id'] == $row['added_by']): ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?= $row['id']; ?>)">Delete Event</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>



$(document).ready(function() {
            $("#toggle-event-form").click(function() {
                $("#event-form-container").slideToggle(); // Smooth show/hide effect
            });
        });


        // Toggle photo upload form
        function toggleUploadForm(eventId) {
            var form = document.getElementById('upload-form-' + eventId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Event form submission
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

        // Delete event functionality (AJAX)
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
                    }
                });
            }
        }
        




    
        $(document).on('submit', 'form[id^="upload-form-"]', function(event) {
    event.preventDefault();
    
    var form = $(this);
    var formData = new FormData(this);
    var eventId = form.attr('id').split('-')[2]; 

    formData.append('id', eventId);  // Append event ID

    $.ajax({
        url: 'upload_event_photo.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                alert('Photos uploaded successfully!');
                $('#photos-' + eventId).append(data.photosHtml); // Append new photos
            } else {
                alert('Error: ' + data.message);
            }
        },
        error: function() {
            alert('Error with the AJAX request.');
        }
    });
});





$(".deletable-photo").click(function () {
    let photoPath = $(this).attr("src"); // Get image path
    let fileName = photoPath.split("/").pop(); // Extract filename
    let eventId = $(this).data("event-id"); // Get event ID

    console.log("Deleting:", fileName, "Event ID:", eventId); // Debugging

    if (confirm("Are you sure you want to delete this photo?")) {
        $.ajax({
            url: "delete_event_photo.php",
            type: "POST",
            data: { event_id: eventId, photo_path: fileName }, // Send only filename
            success: function (response) {
                console.log("Response:", response);
                let res = JSON.parse(response);
                if (res.status === "success") {
                    location.reload();
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    }
});


    </script>
</body>
</html>