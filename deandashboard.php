<?php
session_start();
include('db_connection.php'); // Ensure the database connection is included

// Default department and category filters
$departmentFilter = isset($_GET['department']) ? $_GET['department'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Fetch all events for display on page load (without filtering)
$eventsQuery = "SELECT * FROM events ORDER BY event_date ASC";
$eventsResult = $conn->query($eventsQuery);

// Query for upcoming events with department and category filters
$upcomingEventsQuery = "SELECT e.id, e.event_name, e.resource_person, e.event_details, e.event_date, e.department, e.category, e.added_by, e.hod_status, e.dean_status, e.principal_status, ec.category_name, u.role AS creator_role 
                        FROM events e
                        LEFT JOIN event_categories ec ON e.category = ec.id
                        LEFT JOIN users u ON e.added_by = u.id
                        WHERE e.event_date >= CURDATE() 
                        AND (e.department = '$departmentFilter' OR '$departmentFilter' = '') 
                        AND (e.category = '$categoryFilter' OR '$categoryFilter' = '') 
                        AND (e.event_date BETWEEN '$startDate' AND '$endDate' OR '$startDate' = '' OR '$endDate' = '') 
                         AND (
                            u.role != 'faculty' OR 
                            (u.role = 'faculty' AND e.hod_status = 'Approved')
                        )
                        ORDER BY e.event_date ASC";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Query for past events with department and category filters
$pastEventsQuery = "SELECT e.id, e.event_name, e.resource_person, e.event_details, e.event_date, e.department, e.category, e.added_by, e.hod_status, e.dean_status, e.principal_status, ec.category_name, u.role AS creator_role 
                    FROM events e
                    LEFT JOIN event_categories ec ON e.category = ec.id
                    LEFT JOIN users u ON e.added_by = u.id
                    WHERE e.event_date < CURDATE() 
                    AND (e.department = '$departmentFilter' OR '$departmentFilter' = '') 
                    AND (e.category = '$categoryFilter' OR '$categoryFilter' = '') 
                    AND (e.event_date BETWEEN '$startDate' AND '$endDate' OR '$startDate' = '' OR '$endDate' = '') 
                    ORDER BY e.event_date DESC";
$pastEventsResult = $conn->query($pastEventsQuery);




function getStatusBadge($status) {
    $status = strtolower($status);
    $class = 'secondary';
    switch ($status) {
        case 'approved':
            $class = 'success';
            break;
        case 'rejected':
            $class = 'danger';
            break;
        case 'pending':
        default:
            $class = 'warning';
            break;
    }
    return '<span class="badge bg-' . $class . '">' . ucfirst($status) . '</span>';
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
    <title>Dean Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="text-end text-muted mb-3">
    <span class="badge bg-light text-dark">
        Welcome, <?= htmlspecialchars($_SESSION['role']) ?> 
        (ID: <?= $_SESSION['user_id'] ?>)
    </span>
</div>


    <div class="container mt-5">
        <h1 class="text-center text-primary">Dean Dashboard</h1>
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
        <select id="department" class="form-control" name="department" required>
                    <option value="">All Departments</option>
                    <option value="CSE">CSE</option>
                    <option value="CSD">CSD</option>
                    <option value="ECE">ECE</option>
                    <option value="EEE">EEE</option>
                    <option value="AI">AI</option>
                    <option value="AIML">AIML</option>
                    <option value="MBA">MBA</option>
                    <option value="MCA">MCA</option>
                    <option value="MECH">MECH</option>
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






        <!-- Filters for Date Range, Department, and Category -->
        <h2 class="mt-4">Filter Events by Date Range, Department, and Category</h2>
        <div class="row mb-3">
            <div class="col">
                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date" class="form-control">
            </div>
            <div class="col">
                <label for="end-date">End Date:</label>
                <input type="date" id="end-date" class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="department">Department:</label>
                <select id="department" class="form-control">
                    <option value="">All Departments</option>
                    <option value="CSE">CSE</option>
                    <option value="CSD">CSD</option>
                    <option value="ECE">ECE</option>
                    <option value="EEE">EEE</option>
                    <option value="AI">AI</option>
                    <option value="AIML">AIML</option>
                    <option value="MBA">MBA</option>
                    <option value="MCA">MCA</option>
                    <option value="MECH">MECH</option>
                </select>
            </div>
            <div class="col">
                <label for="category">Category:</label>
                <select id="category" class="form-control">
                    <option value="">All Categories</option>
                    <option value="1">Seminar</option>
                    <option value="2">Webinar</option>
                    <option value="3">FDP</option>
                    <option value="4">Festival Events</option>
                    <option value="5">Alumni Event</option>
                    <option value="6">Workshop</option>
                    <option value="7">Guest Lecture</option>
                </select>
            </div>
        </div>
        <button id="filter-btn" class="btn btn-primary mb-3">Filter Events</button>
        <button id="download-btn" class="btn btn-success mb-3">Download Filtered Events</button>

        <!-- Tabs to switch between Upcoming, Past, and Filtered Events -->
        <ul class="nav nav-tabs" id="event-tabs">
            <li class="nav-item">
                <a class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" href="#upcoming">Upcoming Events</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="past-tab" data-bs-toggle="tab" href="#past">Past Events</a>
            </li>
            <li class="nav-item">
      
          <a class="nav-link" id="filtered-tab" data-bs-toggle="tab" href="#filtered" style="display: none;">Filtered Events</a>
            </li>
</ul>


        <div class="tab-content mt-3">
            <!-- Upcoming Events Tab -->
            <div class="tab-pane fade show active" id="upcoming">
                <h2 class="mt-4">Upcoming Events</h2>
                <table class="table table-bordered" id="upcoming-events-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Resource Person</th>
                            <th>faculty status</th>
                            <th>hod status</th>
                            <th>Principal status</th>
                            <th>Status</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $upcomingEventsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['department']); ?></td>
                                <td><?= htmlspecialchars($row['event_name']); ?></td>
                                <td><?= htmlspecialchars($row['event_date']); ?></td>
                                <td><?= htmlspecialchars($row['category_name']); ?></td>
                                <td><?= htmlspecialchars($row['resource_person']); ?></td>
                                
                                <td>
<?php
$creatorRole = strtolower($row['creator_role'] ?? '');

if ($creatorRole === 'hod') {
    // If HOD added it, just show "Pending"
    echo getStatusBadge('pending');
} elseif (
    $creatorRole === 'faculty' &&
    strtolower($row['hod_status']) === 'approved' &&
    strtolower($row['dean_status']) === 'pending'
) {
    // Faculty added, HOD approved, Dean pending — show action buttons
    ?>
    <button class="btn btn-success dean-approve-btn" data-event-id="<?= $row['id']; ?>">Approve</button>
    <button class="btn btn-danger dean-reject-btn" data-event-id="<?= $row['id']; ?>">Reject</button>
<?php
} else {
    // For other states, just show dean status badge
    echo getStatusBadge($row['dean_status']);
}
?>
</td>

<td>
<?php
$creatorRole = strtolower($row['creator_role']);
$deanStatus = strtolower($row['dean_status']); // ✅ Define this before using it

if ($creatorRole === 'hod' && $deanStatus === 'pending') {
    // Dean is approving HOD-added event – show buttons
    ?>
    <button class="btn btn-success hod-approve-btn" data-event-id="<?= $row['id']; ?>">Approve</button>
    <button class="btn btn-danger hod-reject-btn" data-event-id="<?= $row['id']; ?>">Reject</button>
    <?php
} else {
    echo getStatusBadge($row['dean_status']);
}
?>
</td>

<td><?= getStatusBadge($row['principal_status']); ?></td>
<td><?= getFinalStatus($row['hod_status'], $row['dean_status'], $row['principal_status']); ?></td>

<td>
    <a href="event_deta.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
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
            </div>

            <!-- Past Events Tab -->
            <div class="tab-pane fade" id="past">
                <h2 class="mt-4">Past Events</h2>
                <table class="table table-bordered" id="past-events-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Resource Person</th>
                            <th>Event photos</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $pastEventsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['department']); ?></td>
                                <td><?= htmlspecialchars($row['event_name']); ?></td>
                                <td><?= htmlspecialchars($row['event_date']); ?></td>
                                <td><?= htmlspecialchars($row['category_name']); ?></td>
                                <td><?= htmlspecialchars($row['resource_person']); ?></td>
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
                            <?php if ($_SESSION['user_id'] == $row['added_by']): ?>
                           
                             <form id="upload-form-<?= $row['id']; ?>" class="mt-2" style="display:none;" method="POST" enctype="multipart/form-data">
                             <input type="hidden" name="event_id" value="<?= $row['id']; ?>">
    <input type="file" name="event_photos[]" accept="image/*" multiple required>
    <button type="submit" class="btn btn-success btn-sm mt-2">Upload Photos</button>
</form>
<button class="btn btn-primary btn-sm mt-2" onclick="toggleUploadForm(<?= $row['id']; ?>)">Upload Photos</button>
<?php endif; ?>

                        </td>
                        <td>
    <a href="event_deta.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
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

            <!-- Filtered Events Tab (Initially Hidden) -->
            <div class="tab-pane fade" id="filtered">
                <h2 class="mt-4">Filtered Events</h2>
                <table class="table table-bordered" id="filtered-events-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Resource Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filtered events will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sort events by department or category
        function sortTable(order, column) {
            var table = column === 'department' ? '#upcoming-events-table' : '#past-events-table';
            var rows = $(table + ' tbody tr').get();
            var index = (column === 'department') ? 0 : 3; // For department column = 0, category column = 3

            rows.sort(function(a, b) {
                var keyA = $(a).find('td').eq(index).text();
                var keyB = $(b).find('td').eq(index).text();

                if (order === 'asc') {
                    return keyA.localeCompare(keyB);
                } else {
                    return keyB.localeCompare(keyA);
                }
            });

            $.each(rows, function(index, row) {
                $(table + ' tbody').append(row);
            });
        }

        // Sorting department-wise
        $('#sort-department, #sort-department-past').click(function(e) {
            e.preventDefault();
            var currentSort = $(this).hasClass('asc') ? 'desc' : 'asc';
            $(this).toggleClass('asc', currentSort === 'asc');
            sortTable(currentSort, 'department');
        });

        // Sorting category-wise
        $('#sort-category, #sort-category-past').click(function(e) {
            e.preventDefault();
            var currentSort = $(this).hasClass('asc') ? 'desc' : 'asc';
            $(this).toggleClass('asc', currentSort === 'asc');
            sortTable(currentSort, 'category');
        });

        // Filter events based on date range, department, and category
        $('#filter-btn').click(function() {
            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();
            var department = $('#department').val();
            var category = $('#category').val();

            // Ensure both start date and end date are provided
            if (!startDate || !endDate) {
                alert('Please select both start and end dates!');
                return;
            }

            // Make an AJAX request to fetch the filtered events
            $.ajax({
                url: 'fetch_filtered_events.php',
                method: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    department: department,
                    category: category
                },
                success: function(data) {
                    // Show the filtered events tab and hide others
                    $('#event-tabs a#filtered-tab').show(); // Make the filtered tab visible
                    $('#filtered').tab('show'); // Switch to the Filtered tab

                    // Populate the Filtered Events table
                    if (data.trim() === "") {
                        alert("No events found for the selected filters.");
                    } else {
                        $('#filtered-events-table tbody').html(data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        });

        // Handle the download button click
        $('#download-btn').click(function() {
            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();
            var department = $('#department').val();
            var category = $('#category').val();

            // Redirect to the download page to download the filtered events as Excel
            window.location.href = 'download_filtered_events.php?start_date=' + startDate + '&end_date=' + endDate + '&department=' + department + '&category=' + category;
        });



        
        $(document).on("click", ".hod-approve-btn, .hod-reject-btn", function () {
    var button = $(this);
    var eventId = button.data("event-id");
    var status = button.hasClass("hod-approve-btn") ? "Approved" : "Rejected";

    updateHODStatusByDean(eventId, status);
});

function updateHODStatusByDean(eventId, status) {
    $.ajax({
        url: 'dean_actions.php', // ✅ Create this file to handle status updates for HOD-added events
        type: 'POST',
        data: {
            event_id: eventId,
            status: status
        },
        success: function (response) {
            try {
                var data = JSON.parse(response);
                if (data.success) {
                    alert("HOD Event " + status + " successfully by Dean!");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                alert("Unexpected response: " + response);
            }
        },
        error: function () {
            alert("AJAX error. Please try again.");
        }
    });
}
        
$(document).on("click", ".dean-approve-btn, .dean-reject-btn", function () {
    var button = $(this);
    var eventId = button.data("event-id");
    var status = button.hasClass("dean-approve-btn") ? "Approved" : "Rejected";

    updateHODStatusByDean(eventId, status);
});

function updateHODStatusByDean(eventId, status) {
    $.ajax({
        url: 'dean_hod_event_actions.php', // ✅ Create this file to handle status updates for HOD-added events
        type: 'POST',
        data: {
            event_id: eventId,
            status: status
        },
        success: function (response) {
            try {
                var data = JSON.parse(response);
                if (data.success) {
                    alert("HOD Event " + status + " successfully by Dean!");
                    location.reload();
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                alert("Unexpected response: " + response);
            }
        },
        error: function () {
            alert("AJAX error. Please try again.");
        }
    });
}






$(document).ready(function() {
            $("#toggle-event-form").click(function() {
                $("#event-form-container").slideToggle(); // Smooth show/hide effect
            });
        });


        
$(document).ready(function () {
    $('#add-event-form').submit(function (e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'add_event.php', // ensure this path is correct
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                try {
                    const res = JSON.parse(response);

                    if (res.status === 'success') {
                        alert('Event added successfully!');

                        // Optional: You can reload the event list or dynamically add the row
                        location.reload(); // Quick way to show the newly added event
                    } else {
                        alert('Error: ' + res.message);
                        console.log(res);
                    }
                } catch (e) {
                    alert('Unexpected error. Check console.');
                    console.error(response);
                }
            },
            error: function (xhr, status, error) {
                alert('AJAX error: ' + error);
                console.log(xhr.responseText);
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
        




        
function toggleUploadForm(eventId) {
    const form = document.getElementById('upload-form-' + eventId);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

$(document).on('submit', 'form[id^="upload-form-"]', function (e) {
    e.preventDefault();

    var form = $(this);
    var formData = new FormData(this);
    var eventId = this.id.replace('upload-form-', '');

    $.ajax({
        url: 'upload_event_photo.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    alert('Photos uploaded successfully!');
                    location.reload();
                } else {
                    alert('Upload failed: ' + res.message);
                    console.log(res);
                }
            } catch (e) {
                alert('Unexpected error. Check console.');
                console.error(response);
            }
        },
        error: function (xhr, status, error) {
            alert('AJAX error: ' + error);
            console.log(xhr.responseText);
        }
    });
});


    
       









    </script>
</body>
</html>