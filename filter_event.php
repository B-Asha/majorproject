<?php
include 'db_connection.php'; // your DB connection file

$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$department = $_POST['department'] ?? '';
$category = $_POST['category'] ?? '';
$status = $_POST['status'] ?? '';

// Build SQL query dynamically
$sql = "SELECT * FROM events WHERE 1=1";

// Add filters
if (!empty($startDate)) {
    $sql .= " AND event_date >= '$startDate'";
}
if (!empty($endDate)) {
    $sql .= " AND event_date <= '$endDate'";
}
if (!empty($department)) {
    $sql .= " AND department = '$department'";
}
if (!empty($category)) {
    $sql .= " AND category = '$category'";
}
if (!empty($status)) {
    if ($status === 'Approved') {
        $sql .= " AND hod_status = 'Approved' AND dean_status = 'Approved' AND principal_status = 'Approved'";
    } elseif ($status === 'Rejected') {
        $sql .= " AND (hod_status = 'Rejected' OR dean_status = 'Rejected' OR principal_status = 'Rejected')";
    } elseif ($status === 'Pending') {
        $sql .= " AND (hod_status = 'Pending' OR dean_status = 'Pending' OR principal_status = 'Pending')";
    }
}

// Run the query
$result = mysqli_query($conn, $sql);

// Send back filtered events
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['event_name']}</td>
            <td>{$row['event_date']}</td>
            <td>{$row['department']}</td>
            <td>{$row['category']}</td>
            <td>{$row['resource_person']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No events found</td></tr>";
}
?>
