<?php
include('db_connection.php'); // Include your database connection

// Ensure the session is started
session_start();

// Fetch the user type from the query string (HOD, Principal, or Dean)
$userType = isset($_GET['user_type']) ? $_GET['user_type'] : ''; // Pass 'hod', 'principal', or 'dean' via GET

$events = '';
$notifications = '';

// Check if user type is set in the session
if (!isset($_SESSION['department'])) {
    echo json_encode(array('error' => 'Department not set in session.'));
    exit;
}

// Fetch events based on user type
if ($userType === 'hod') {
    // Fetch upcoming events for HOD based on department
    $eventsQuery = "SELECT e.*, ec.category_name FROM events e
                    LEFT JOIN event_categories ec ON e.category = ec.id
                    WHERE e.event_date >= CURDATE() AND e.department = ?
                    ORDER BY e.event_date ASC";
    $stmt = $conn->prepare($eventsQuery);
    $stmt->bind_param("s", $_SESSION['department']); // Assuming the department is stored in the session
    $stmt->execute();
    $eventsResult = $stmt->get_result();
    
    // Prepare events data for HOD dashboard
    while ($row = $eventsResult->fetch_assoc()) {
        // Display resource person photos
        $photos = explode(",", $row['resource_person_photo']);
        $photo_html = "";
        foreach ($photos as $photo) {
            $photo_html .= '<img src="' . htmlspecialchars($photo) . '" alt="Resource Person Photo" width="100" height="100" class="m-1">';
        }
        
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['resource_person']) . '</td>
                        <td>' . $photo_html . '</td>
                        <td>' . htmlspecialchars($row['category_name']) . '</td>
                        <td>' . htmlspecialchars($row['department']) . '</td>
                        <td><span class="badge bg-warning">' . htmlspecialchars($row['status']) . '</span></td>
                    </tr>';
    }
    $stmt->close();

} elseif ($userType === 'principal') {
    // Fetch upcoming events for Principal
    $eventsQuery = "SELECT e.*, ec.category_name FROM events e
                    LEFT JOIN event_categories ec ON e.category = ec.id
                    WHERE e.event_date >= CURDATE()
                    ORDER BY e.event_date ASC";
    $eventsResult = $conn->query($eventsQuery);
    
    // Prepare events data for Principal dashboard
    while ($row = $eventsResult->fetch_assoc()) {
        // Display resource person photos
        $photos = explode(",", $row['resource_person_photo']);
        $photo_html = "";
        foreach ($photos as $photo) {
            $photo_html .= '<img src="' . htmlspecialchars($photo) . '" alt="Resource Person Photo" width="100" height="100" class="m-1">';
        }
        
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['resource_person']) . '</td>
                        <td>' . $photo_html . '</td>
                        <td>' . htmlspecialchars($row['category_name']) . '</td>
                        <td>' . htmlspecialchars($row['department']) . '</td>
                        <td><span class="badge bg-success">' . htmlspecialchars($row['status']) . '</span></td>
                    </tr>';
    }

} elseif ($userType === 'dean') {
    // Fetch events for Dean (with approve/reject functionality)
    $eventsQuery = "SELECT e.*, ec.category_name FROM events e
                    LEFT JOIN event_categories ec ON e.category = ec.id
                    WHERE e.event_date >= CURDATE()
                    ORDER BY e.event_date ASC";
    $eventsResult = $conn->query($eventsQuery);
    
    // Prepare events data for Dean dashboard
    while ($row = $eventsResult->fetch_assoc()) {
        // Display resource person photos
        $photos = explode(",", $row['resource_person_photo']);
        $photo_html = "";
        foreach ($photos as $photo) {
            $photo_html .= '<img src="' . htmlspecialchars($photo) . '" alt="Resource Person Photo" width="100" height="100" class="m-1">';
        }
        
        $events .= '<tr>
                        <td>' . htmlspecialchars($row['event_name']) . '</td>
                        <td>' . htmlspecialchars($row['event_date']) . '</td>
                        <td>' . htmlspecialchars($row['resource_person']) . '</td>
                        <td>' . $photo_html . '</td>
                        <td>' . htmlspecialchars($row['category_name']) . '</td>
                        <td>' . htmlspecialchars($row['department']) . '</td>
                        <td>
                            <button class="btn btn-success approve-btn" data-event-id="' . $row['id'] . '">Approve</button>
                            <button class="btn btn-danger reject-btn" data-event-id="' . $row['id'] . '">Reject</button>
                        </td>
                    </tr>';
    }
}

// Fetch the latest 5 notifications
$notificationsQuery = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$notificationsResult = $conn->query($notificationsQuery);

// Prepare notifications data
while ($row = $notificationsResult->fetch_assoc()) {
    $notifications .= '<li class="list-group-item">' . htmlspecialchars($row['message']) . '</li>';
}

// Return the data as JSON for AJAX
echo json_encode(array('events' => $events, 'notifications' => $notifications));
?>
