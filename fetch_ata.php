<?php
include('db_connection.php'); // Include the database connection

// Fetch updated events for HOD or Principal Dashboard
// Check if we are fetching for a particular user (HOD/Principal)
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : ''; // Pass 'hod' or 'principal' via GET

if ($userType === 'hod') {
    // HOD dashboard: Fetch events for the department that the HOD is managing
    $eventsQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
    $eventsResult = $conn->query($eventsQuery);
    $events = '';
    while ($row = $eventsResult->fetch_assoc()) {
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['resource_person']) . '</td>
                        <td><span class="badge bg-warning">' . $row['status'] . '</span></td>
                    </tr>';
    }
} elseif ($userType === 'principal') {
    // Principal dashboard: Fetch events for review by the Principal
    $eventsQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
    $eventsResult = $conn->query($eventsQuery);
    $events = '';
    while ($row = $eventsResult->fetch_assoc()) {
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['department']) . '</td>
                        <td><span class="badge bg-success">' . $row['status'] . '</span></td>
                    </tr>';
    }
} elseif ($userType === 'dean') {
    $eventsQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
    $eventsResult = $conn->query($eventsQuery);
    while ($row = $eventsResult->fetch_assoc()) {
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['department']) . '</td>
                        <td>
                            <button class="btn btn-success">Approve</button>
                            <button class="btn btn-danger">Reject</button>
                        </td>
                    </tr>';
    }
}

// Fetch the notifications (for both HOD and Principal dashboards)
$notificationsQuery = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5"; // Get latest 5 notifications
$notificationsResult = $conn->query($notificationsQuery);
$notifications = '';
while ($row = $notificationsResult->fetch_assoc()) {
    $notifications .= '<li class="list-group-item">' . htmlspecialchars($row['message']) . '</li>';
}

// Return data as JSON
echo json_encode(array('events' => $events, 'notifications' => $notifications));
?>
