<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_details = $_POST['event_details'];
    $event_date = $_POST['event_date'];
    $resource_person = $_POST['resource_person'];
    $department = $_POST['department'];
    $category = $_POST['category'];
    $added_by = $_SESSION['user_id']; // Ensure session has user_id

    // Resource Person Photo Upload
    $resource_person_photo = '';
    if (isset($_FILES['resource_person_photo']) && $_FILES['resource_person_photo']['error'] == 0) {
        $upload_dir = 'uploads/';
        $filename = time() . '_' . basename($_FILES['resource_person_photo']['name']);
        $target_file = $upload_dir . $filename;

        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            echo json_encode(["status" => "error", "message" => "Invalid file format. Allowed: JPG, JPEG, PNG, GIF."]);
            exit;
        }

        if (move_uploaded_file($_FILES['resource_person_photo']['tmp_name'], $target_file)) {
            $resource_person_photo = $target_file;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to upload photo."]);
            exit;
        }
    }


   
    


    // Corrected SQL Query
    $stmt = $conn->prepare("INSERT INTO events 
        (event_name, event_details, event_date, resource_person, resource_person_photo, department, category, added_by, hod_status, dean_status, principal_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', 'Pending')");
    
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "SQL error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssssssi", $event_name, $event_details, $event_date, $resource_person, $resource_person_photo, $department, $category, $added_by);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "event_name" => $event_name, "event_date" => $event_date, "resource_person" => $resource_person, "category" => $category, "department" => $department, "id" => $conn->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
}
?>  