<?php
session_start();
require 'db_connection.php';  // Include the database connection

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from the form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if the username and password are not empty
    if (empty($username) || empty($password)) {
        echo "<script>alert('Both fields are required!');</script>";
    } else {
        // Prepare the SQL statement to select the user from the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);  // Execute with the username parameter
        $user = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the user details

        // If a user is found and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Debugging - Check if session is set
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
                die("Session not set properly. Check session_start() and db_connection.");
            }

            // Redirect to dashboard based on user role
            if ($_SESSION['role'] == 'principal') {
                header("Location: principal_dashboard.php");
            } elseif ($_SESSION['role'] == 'dean') {
                header("Location: dean_dashboard.php");
            } elseif ($_SESSION['role'] == 'hod') {
                header("Location: hod_dashboard.php");
            } elseif ($_SESSION['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                echo "<script>alert('Invalid Role. Contact Admin.');</script>";
            }
            exit();
        } else {
            // Show error if username/password is incorrect
            echo "<script>alert('Invalid Username or Password!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
