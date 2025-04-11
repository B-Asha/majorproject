<?php
session_start();
include 'db_connection.php'; // PDO or MySQLi connection

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$department = $_SESSION['department'];
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
<!-- Filters for Date Range, Department, Category, and Status -->
<h2 class="mt-4">Filter Events by Date Range, Department, Category, and Status</h2>
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
    <div class="col">
        <label for="status">Status:</label>
        <select id="status" class="form-control">
            <option value="">All Statuses</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
            <option value="Pending">Pending</option>
        </select>
    </div>
</div>

<!-- Filter and Download Buttons -->
<button id="filter-btn" class="btn btn-primary mb-3">Filter Events</button>
<button id="download-btn" class="btn btn-success mb-3">Download Filtered Events</button>

<!-- Tabs to switch between Upcoming, Past, and Filtered Events -->
<ul class="nav nav-tabs" id="event-tabs">
    
    <li class="nav-item">
        <a class="nav-link" id="filtered-tab" data-bs-toggle="tab" href="#filtered" style="display: none;">Filtered Events</a>
    </li>
</ul>

<!-- Filtered Events Table Container -->
<div class="tab-content mt-3">
    <div class="tab-pane fade" id="filtered">
        <table class="table table-bordered" id="filtered-events-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Department</th>
                    <th>Category</th>
                    <th>Event Date</th>
                    <th>Resource Person</th>
                    <th>HOD Status</th>
                    <th>Dean Status</th>
                    <th>Principal Status</th>
                    <!-- Add other columns if needed -->
                </tr>
            </thead>
            <tbody>
                <!-- Filtered events will be populated here by JS -->
            </tbody>
        </table>
    </div>
</div>
<a href="dean_dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</body>
<script>
   
    
   $('#filter-btn').click(function() {
    var startDate = $('#start-date').val();
    var endDate = $('#end-date').val();
    var department = $('#department').val();
    var category = $('#category').val();
    var status = $('#status').val(); // <-- New line

    if (!startDate || !endDate) {
        alert('Please select both start and end dates!');
        return;
    }

    $.ajax({
        url: 'fetch_filtered_events.php',
        method: 'GET',
        data: {
            start_date: startDate,
            end_date: endDate,
            department: department,
            category: category,
            status: status // <-- New line
        },
        success: function(data) {
            $('#event-tabs a#filtered-tab').show();
            $('#filtered').tab('show');

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
$('#download-btn').click(function() {
    var startDate = $('#start-date').val();
    var endDate = $('#end-date').val();
    var department = $('#department').val();
    var category = $('#category').val();
    var status = $('#status').val(); // <-- New line

    window.location.href = 'download_filtered_events.php?start_date=' + startDate +
        '&end_date=' + endDate +
        '&department=' + department +
        '&category=' + category +
        '&status=' + status; // <-- New line
});


</script> 