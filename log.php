<?php
session_start();
require __DIR__ . '/db_connection.php';  // Include the database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get username and password from the form, trim to remove extra spaces
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if the username and password are not empty
    if (empty($username) || empty($password)) {
        echo "<script>alert('Both fields are required!');</script>";
    } else {
        try {
            // Prepare the SQL statement to select the user from the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);  // Execute with the username parameter
            $user = $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch the user details

            // If a user is found and password matches
            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);
                
                // Set session variables for the logged-in user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Debugging - Check if session is set
                if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
                    die("Session not set properly. Check session_start() and db_connection.");
                }

                // Redirect to dashboard based on user role
                switch ($_SESSION['role']) {
                    case 'principal':
                        header("Location: principal_dashboard.php");
                        break;
                    case 'dean':
                        header("Location: dean_dashboard.php");
                        break;
                    case 'hod':
                        header("Location: hod_dashboard.php");
                        break;
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    default:
                        echo "<script>alert('Invalid Role. Contact Admin.');</script>";
                        exit();
                }
                exit();
            } else {
                // Show error if username/password is incorrect
                echo "<script>alert('Invalid Username or Password!');</script>";
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #4cae4c;
        }
    </style>
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
