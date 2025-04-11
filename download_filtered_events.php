<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

// Category mapping function
function getCategoryName($categoryId) {
    $categories = [
        1 => 'Seminar',
        2 => 'Webinar',
        3 => 'FDP',
        4 => 'Festival Events',
        5 => 'Alumni Event',
        6 => 'Workshop',
        7 => 'Guest Lecture'
    ];
    return $categories[$categoryId] ?? 'Unknown';
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$department = $_GET['department'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$params = [];
$sql = "SELECT * FROM events WHERE event_date BETWEEN ? AND ?";
$params[] = $startDate;
$params[] = $endDate;

if (!empty($department)) {
    $sql .= " AND department = ?";
    $params[] = $department;
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($status)) {
    $sql .= " AND (hod_status = ? OR dean_status = ? OR principal_status = ?)";
    $params[] = $status;
    $params[] = $status;
    $params[] = $status;
}

$sql .= " ORDER BY event_date ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Prepare Failed: " . $conn->error);
}

$types = str_repeat("s", count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Output CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="filtered_events.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, ['Event Name', 'Department', 'Category', 'Event Date', 'Resource Person', 'HOD Status', 'Dean Status', 'Principal Status']);

// Output each event
foreach ($events as $event) {
    fputcsv($output, [
        $event['event_name'],
        $event['department'],
        getCategoryName($event['category']),
        " " .date('Y-m-d', strtotime($event['event_date'])), // Add single quote to force Excel text format

        $event['resource_person'],
        $event['hod_status'],
        $event['dean_status'],
        $event['principal_status']
    ]);
}

fclose($output);
exit;
?>
