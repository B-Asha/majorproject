<?php
include('db_connection.php');

// Get the filter dates if provided
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Build the query
$query = "SELECT * FROM events";

if ($startDate && $endDate) {
    $query .= " WHERE event_date BETWEEN '$startDate' AND '$endDate'";  // Filter events between the selected date range
}

$query .= " ORDER BY event_date ASC";  // Order by event_date

$result = $conn->query($query);

// Generate the rows of the table based on all events
$output = '';
while ($row = $result->fetch_assoc()) {
    $output .= '<tr>
                    <td>' . htmlspecialchars($row['event_name']) . '</td>
                    <td>' . htmlspecialchars($row['event_date']) . '</td>
                    <td>' . htmlspecialchars($row['department']) . '</td>
                </tr>';
}

// Return the rows of events as the response
echo $output;
?>
