<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Assign department if the role is HOD or Faculty
    if ($role === 'hod' || $role === 'faculty') {
        $department = $_POST['department'];  
    } else {
        $department = null;
    }

    // Insert user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, department) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $password, $role, $department])) {
        echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Registration failed!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="register_style.css">
    <style>
         body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
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
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <select name="role" required>
                <option value="principal">Principal</option>
                <option value="dean">Dean</option>
                <option value="hod">HOD</option>
                
                <option value="faculty">Faculty</option>
            </select>

            <div id="department-container" style="display: none;">
                <label for="department">Select Department</label>
                <select name="department" id="department">
                    <option value="CSE">CSE</option>
                    <option value="EEE">EEE</option>
                    <option value="ECE">ECE</option>
                    <option value="AI">AI</option>
                    <option value="MECH">MECH</option>
                    <option value="MCA">MCA</option>
                    <option value="MBA">MBA</option>
                    <option value="CSD">CSD</option>
                    <option value="AIML">AIML</option>
                </select>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        // Show department selection for HOD and Faculty
        document.querySelector('select[name="role"]').addEventListener('change', function() {
            var role = this.value;
            var departmentContainer = document.getElementById('department-container');

            if (role === 'hod' || role === 'faculty') {
                departmentContainer.style.display = 'block';
            } else {
                departmentContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
