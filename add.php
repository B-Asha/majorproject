<?php
session_start();
include('db_connection.php');

// Check if the form data is sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = $_POST['event_name'];
    $eventDetails = $_POST['event_details'];
    $eventDate = $_POST['event_date'];
    $resourcePerson = $_POST['resource_person'];
    $department = $_POST['department'];
    $category = $_POST['category'];

    // Handle file upload for resource person photo
    if (isset($_FILES['resource_person_photo']) && $_FILES['resource_person_photo']['error'] == 0) {
        $photoTmp = $_FILES['resource_person_photo']['tmp_name'];
        $photoName = time() . '_' . $_FILES['resource_person_photo']['name'];
        $photoPath = 'uploads/' . $photoName;
        move_uploaded_file($photoTmp, $photoPath);
    } else {
        $photoPath = '';
    }

    // Insert the event data into the database
    $query = "INSERT INTO events (event_name, event_details, event_date, resource_person, department, category, resource_person_photo) 
              VALUES ('$eventName', '$eventDetails', '$eventDate', '$resourcePerson', '$department', '$category', '$photoPath')";

    if ($conn->query($query) === TRUE) {
        // Fetch the category name from the category table
        $categoryNameQuery = "SELECT category_name FROM event_categories WHERE id = '$category'";
        $categoryResult = $conn->query($categoryNameQuery);
        $categoryName = $categoryResult->fetch_assoc()['category_name'];
        
        // Prepare the response
        $response = [
            'status' => 'success',
            'id' => $conn->insert_id,
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'resource_person' => $resourcePerson,
            'category' => $categoryName,  // Return the category name, not the ID
            'department' => $department
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding event']);
    }
}
?>
