<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if the user is an HOD, then get the department
    if ($role === 'hod') {
        $department = $_POST['department'];  // Get the department
    } else {
        $department = null;  // If not an HOD, set department to null
    }

    // Prepare the SQL query to insert the user into the database
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
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <!-- Department Selection (only for HODs) -->
            <select name="role" required>
                <option value="principal">Principal</option>
                <option value="dean">Dean</option>
                <option value="hod">HOD</option>
                <option value="admin">Admin</option>
                <option value="faculty">Faculty</option>
            </select>

            <!-- Department Dropdown (only for HODs) -->
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
        // Show department selection dropdown only if the role is HOD
        document.querySelector('select[name="role"]').addEventListener('change', function() {
            var role = this.value;
            var departmentContainer = document.getElementById('department-container');
            if (role === 'hod') {
                departmentContainer.style.display = 'block';
            } else {
                departmentContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
