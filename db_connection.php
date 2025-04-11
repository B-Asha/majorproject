<?php
// Database connection using mysqli
$servername = "localhost"; // Your server (localhost for local development)
$username = "root"; // Your database username
$password = ""; // Your database password (empty for XAMPP by default)
$dbname = "activity_management"; // Your database name

// Create the connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
try {
    $pdo = new PDO("mysql:host=localhost;dbname=activity_management", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
