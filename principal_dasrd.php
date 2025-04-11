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
$upcomingEventsQuery = "SELECT e.id, e.event_name, e.resource_person, e.event_details, e.event_date, e.department, e.category, ec.category_name, e.hod_status, e.dean_status, e.principal_status, u.role AS added_by_role
 
                        FROM events e
                        LEFT JOIN event_categories ec ON e.category = ec.id
                        JOIN users u ON e.added_by = u.id

                        WHERE e.event_date >= CURDATE() 
                        -- In your WHERE clause:
AND (
    (e.added_by_role = 'hod' AND dean_status = 'Approved') OR 
    e.added_by_role != 'hod'
)
 

                        AND (e.department = '$departmentFilter' OR '$departmentFilter' = '') 
                        AND (e.category = '$categoryFilter' OR '$categoryFilter' = '') 
                        AND (e.event_date BETWEEN '$startDate' AND '$endDate' OR '$startDate' = '' OR '$endDate' = '') 
                        ORDER BY e.event_date ASC";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Query for past events with department and category filters
$pastEventsQuery = "SELECT e.id, e.event_name, e.resource_person, e.event_details, e.event_date, e.department, e.category, ec.category_name, e.hod_status, e.dean_status, e.principal_status, u.role AS added_by_role

                    FROM events e
                    LEFT JOIN event_categories ec ON e.category = ec.id
                    JOIN users u ON e.added_by = u.id

                    WHERE e.event_date < CURDATE() 
                    -- In your WHERE clause:
AND (
    (e.added_by_role = 'hod' AND dean_status = 'Approved') OR 
    e.added_by_role != 'hod'
)

                    AND (e.department = '$departmentFilter' OR '$departmentFilter' = '') 
                    AND (e.category = '$categoryFilter' OR '$categoryFilter' = '') 
                    AND (e.event_date BETWEEN '$startDate' AND '$endDate' OR '$startDate' = '' OR '$endDate' = '') 
                    ORDER BY e.event_date DESC";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Dashboard</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center text-primary">Principal Dashboard</h1>
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
        <select id="department" name="department" class="form-control" required>

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
                    <option value="3">Workshop</option>
                    <option value="4">FDP</option>
                    <option value="5">Alumni Event</option>
                    <option value="6">Festival Event</option>
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
                        
                            <th>Faculty status</th>
                            <th>Details</th>
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
                                <?php if (
    $row['added_by_role'] === 'faculty' &&
    $row['hod_status'] === 'Approved' &&
    $row['dean_status'] === 'Approved' &&
    $row['principal_status'] === 'Pending'
): ?>
    <button class="btn btn-success btn-sm principal-approve-btn" data-event-id="<?= $row['id']; ?>">Approve</button>
    <button class="btn btn-danger btn-sm principal-reject-btn" data-event-id="<?= $row['id']; ?>">Reject</button>
<?php else: ?>
    <?= getStatusBadge($row['principal_status']); ?>
<?php endif; ?>
</td>



<td>

    <a href="event_detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
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
                            <th>Details</th>
                            <th>Action</th>
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
    <a href="event_detail.php?id=<?= $row['id']; ?>" class="btn btn-info btn-sm">View Details</a>
</td>
                    <td>
                    
    <?php 
    if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] == 'Principal' && 
        !empty($_SESSION['user_id']) && $_SESSION['user_id'] == $row['added_by']): ?>
        <button class="btn btn-danger btn-sm" onclick="deleteEvent(<?= $row['id']; ?>)">Delete</button>
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

        

    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$(document).on('click', '.principal-approve-btn, .principal-reject-btn', function () {
    alert('Button clicked'); // 🔥 First, confirm this fires.

    let eventId = $(this).data('event-id');
    let status = $(this).hasClass('principal-approve-btn') ? 'Approved' : 'Rejected';

    $.ajax({
        url: 'update_event_status.php',
        type: 'POST',
        data: {
            event_id: eventId,
            role: 'principal',
            status: status
        },
        success: function (response) {
            try {
                let res = JSON.parse(response);
                if (res.status === 'success') {
                    alert('Status updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (e) {
                alert('Unexpected error. See console.');
                console.error(response);
            }
        },
        error: function (xhr) {
            alert('AJAX Error: ' + xhr.status);
            console.error(xhr.responseText);
        }
    });
});


</script>





    </script>
</body>
</html>