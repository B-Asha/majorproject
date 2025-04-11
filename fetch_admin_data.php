<?php
include('db_connection.php');

// Fetching upcoming events
$upcomingEventsQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$upcomingEventsResult = $conn->query($upcomingEventsQuery);

// Fetching past events
$pastEventsQuery = "SELECT * FROM events WHERE event_date < CURDATE() ORDER BY event_date DESC";
$pastEventsResult = $conn->query($pastEventsQuery);

// Preparing upcoming events data
$upcomingEvents = '';
while ($row = $upcomingEventsResult->fetch_assoc()) {
    $upcomingEvents .= '<li class="list-group-item">
                            <strong>' . htmlspecialchars($row['event_name']) . '</strong> (' . htmlspecialchars($row['event_date']) . ')<br>
                            Resource Person: ' . htmlspecialchars($row['resource_person']) . '<br>
                            Department: ' . htmlspecialchars($row['department']) . '<br>
                            Details: ' . htmlspecialchars($row['event_details']) . '<br>';

    // Resource person photo
    if (!empty($row['resource_person_photo'])) {
        $upcomingEvents .= '<strong>Resource Person Photo:</strong><br>
                            <img src="uploads/' . htmlspecialchars($row['resource_person_photo']) . '" width="100"><br>';
    }

    // Event photos
    $eventPhotosQuery = "SELECT photo_path FROM event_photos WHERE event_id = '{$row['id']}'";
    $eventPhotosResult = $conn->query($eventPhotosQuery);
    $upcomingEvents .= '<strong>Event Photos:</strong><br>';
    while ($photo = $eventPhotosResult->fetch_assoc()) {
        $upcomingEvents .= '<img src="uploads/' . htmlspecialchars($photo['photo_path']) . '" width="100"><br>';
    }

    $upcomingEvents .= '<a href="admin_dashboard.php?delete_event=' . $row['id'] . '" class="btn btn-danger btn-sm mt-2">Delete</a>
                        </li>';
}

// Preparing past events data
$pastEvents = '';
while ($row = $pastEventsResult->fetch_assoc()) {
    $pastEvents .= '<li class="list-group-item">
                        <strong>' . htmlspecialchars($row['event_name']) . '</strong> (' . htmlspecialchars($row['event_date']) . ')<br>
                        Resource Person: ' . htmlspecialchars($row['resource_person']) . '<br>
                        Department: ' . htmlspecialchars($row['department']) . '<br>
                        Details: ' . htmlspecialchars($row['event_details']) . '<br>';

    // Resource person photo
    if (!empty($row['resource_person_photo'])) {
        $pastEvents .= '<strong>Resource Person Photo:</strong><br>
                        <img src="uploads/' . htmlspecialchars($row['resource_person_photo']) . '" width="100"><br>';
    }

    // Event photos
    $eventPhotosQuery = "SELECT photo_path FROM event_photos WHERE event_id = '{$row['id']}'";
    $eventPhotosResult = $conn->query($eventPhotosQuery);
    $pastEvents .= '<strong>Event Photos:</strong><br>';
    while ($photo = $eventPhotosResult->fetch_assoc()) {
        $pastEvents .= '<img src="uploads/' . htmlspecialchars($photo['photo_path']) . '" width="100"><br>';
    }

    $pastEvents .= '<a href="admin_dashboard.php?delete_event=' . $row['id'] . '" class="btn btn-danger btn-sm mt-2">Delete</a>
                    </li>';
}

// Returning data as JSON
echo json_encode([
    'upcomingEvents' => $upcomingEvents,
    'pastEvents' => $pastEvents
]);

$conn->close();
?>
