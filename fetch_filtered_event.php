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
    die("Prepare failed: " . $conn->error);
}

$types = str_repeat("s", count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
while ($row = $result->fetch_assoc()) {
    $output .= "<tr>";
    $output .= "<td>" . htmlspecialchars($row['event_name']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['department']) . "</td>";
    $output .= "<td>" . htmlspecialchars(getCategoryName($row['category'])) . "</td>";
    $output .= "<td>" . htmlspecialchars(date('Y-m-d', strtotime($row['event_date']))) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['resource_person']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['hod_status']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['dean_status']) . "</td>";
    $output .= "<td>" . htmlspecialchars($row['principal_status']) . "</td>";
    $output .= "</tr>";
}

echo $output;
?>
