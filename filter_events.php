<?php
include('db_connection.php');

// Get the date range from the request (make sure the date format is correct)
$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

// Query to fetch events within the date range
$query = "
    SELECT * FROM events
    WHERE event_date BETWEEN ? AND ?
    ORDER BY event_date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Generate the rows of the table based on the filtered events
$output = '';
while ($row = $result->fetch_assoc()) {
    $output .= '<tr>
                    <td>' . htmlspecialchars($row['event_name']) . '</td>
                    <td>' . htmlspecialchars($row['event_date']) . '</td>
                    <td>' . htmlspecialchars($row['department']) . '</td>
                </tr>';
}

// Return the filtered events as the response
echo $output;
?>
