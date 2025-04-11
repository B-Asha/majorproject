<?php
session_start();
include('db_connection.php'); // Ensure the database connection is included

// Fetch all events for display on page load (without filtering)
$eventsQuery = "SELECT * FROM events ORDER BY event_date ASC";
$eventsResult = $conn->query($eventsQuery);
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
    <div class="container mt-5">
        <h1 class="text-center text-primary">Dean Dashboard</h1>
        <a href="login.php" class="btn btn-danger float-end">Logout</a>

        <!-- Date Range Filtering -->
        <h2 class="mt-4">Filter Events by Date Range</h2>
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

        <!-- Department and Category Filters -->
        <div class="row mb-3">
            <div class="col">
                <label for="department-filter">Department:</label>
                <select id="department-filter" class="form-control">
                    <option value="">All Departments</option>
                    <option value="CSE">CSE</option>
                    <option value="CSD">CSD</option>
                    <option value="AI">AI</option>
                    <option value="AIML">AIML</option>
                    <option value="EEE">EEE</option>
                    <option value="ECE">ECE</option>
                    <option value="MCA">MCA</option>
                    <option value="MBA">MBA</option>
                    <option value="MECH">MECH</option>

                    <!-- Add more departments if necessary -->
                </select>
            </div>
            <div class="col">
                <label for="category-filter">Category:</label>
                <select id="category-filter" class="form-control">
                    <option value="">All Categories</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Alumni Events">Alumni Events</option>
                    <option value="webinar">webinar</option>
                    <option value="fdp">fdp</option>
                    <option value="workshop">workshop</option>
                    <option value="guest lecture">guest lecture</option>
                    <option value="festival_event">festival event</option>
                    <!-- Add more categories if necessary -->
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
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Resource Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $eventsResult->fetch_assoc()): ?>
                            <?php if (strtotime($row['event_date']) >= strtotime('today')): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['event_name']); ?></td>
                                    <td><?= htmlspecialchars($row['event_date']); ?></td>
                                    <td><?= htmlspecialchars($row['department']); ?></td>
                                    <td><?= htmlspecialchars($row['category']); ?></td>
                                    <td><?= htmlspecialchars($row['resource_person']); ?></td>
                                </tr>
                            <?php endif; ?>
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
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Resource Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Reset the pointer to the beginning of the result set
                        $eventsResult->data_seek(0); // Reset the result set to the first row

                        // Loop again to fetch past events only
                        while($row = $eventsResult->fetch_assoc()):
                            // Check if the event date is less than today
                            if (strtotime($row['event_date']) < strtotime('today')):
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['event_name']); ?></td>
                                    <td><?= htmlspecialchars($row['event_date']); ?></td>
                                    <td><?= htmlspecialchars($row['department']); ?></td>
                                    <td><?= htmlspecialchars($row['category']); ?></td>
                                    <td><?= htmlspecialchars($row['resource_person']); ?></td>
                                </tr>
                            <?php endif; ?>
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
        // Filter events based on date range, department, and category
        $('#filter-btn').click(function() {
            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();
            var department = $('#department-filter').val();
            var category = $('#category-filter').val();

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
                        alert("No events found for the selected date range.");
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
            var department = $('#department-filter').val();
            var category = $('#category-filter').val();

            // Redirect to the download page to download the filtered events as Excel
            window.location.href = 'download_filtered_events.php?start_date=' + startDate + '&end_date=' + endDate + '&department=' + department + '&category=' + category;
        });
    </script>
</body>
</html>
