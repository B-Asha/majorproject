<?php
session_start();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                $_SESSION['department'] = $user['department'] ?? ''; // Optional department storage for HODs

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
                        // Check for department-specific display (if applicable for HODs)
                        header("Location: hod_dashboard.php?department=" . $_SESSION['department']);
                        break;
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'faculty':
                        header("Location: faculty_dashboard.php");
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
        header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 120px;

      background-color: #f5f5f5; /* light gray background */

      display: flex;
      align-items: center;
      padding: 0 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      z-index: 999;
    }

    .logo {
      height: 90px;
      margin-right: 20px;
    }

    .heading {
      font-size: 28px;
      font-weight: bold;
      color: #333;
    }

    
   
    /* Heading style */
.container h1 {
    color: #2dd62d;
    font-size: 2.5rem;
    margin-bottom: 30px;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
}


.centered-heading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: clamp(20px, 2.5vw, 32px);

  font-weight: 900;
  background: linear-gradient(90deg, #ff6ec4, #7873f5, #4ade80, #facc15, #f87171);
  background-size: 300%;
  animation: gradientShift 6s infinite linear;
  background-clip: text;
-webkit-background-clip: text;

  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5), 2px 2px 8px rgba(0, 0, 0, 0.3);
  letter-spacing: 2px;
  white-space: nowrap;
  font-family: 'Segoe UI', 'Poppins', sans-serif;
  text-align: center;
  z-index: 10;
}

@keyframes gradientShift {
  0% { background-position: 0% }
  100% { background-position: 100% }
}
    </style>
</head>
<body>
<header>
        <img src="logo.png" alt="College Logo" class="logo" />
        <div class="centered-heading">SITAMS ACTIVITY MANAGEMENT SYSTEM</div>
      </header>
      
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
