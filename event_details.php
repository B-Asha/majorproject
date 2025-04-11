<?php
session_start();
include('db_connection.php');

if (!isset($_GET['id'])) {
    die("Invalid event ID.");
}

$eventId = intval($_GET['id']);

$query = "
    SELECT e.*, ec.category_name 
    FROM events e
    LEFT JOIN event_categories ec ON e.category = ec.id
    WHERE e.id = $eventId
";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="event_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>




    <div class="container mt-5">
        <h1 class="text-primary"><?= htmlspecialchars($event['event_name']); ?></h1>
        <p><strong>Details:</strong> <?= nl2br(htmlspecialchars($event['event_details'])); ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']); ?></p>
        <p><strong>Resource Person:</strong> <?= htmlspecialchars($event['resource_person']); ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($event['department']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($event['category_name']); ?></p>

        <?php if (!empty($event['resource_person_photo'])): ?>
            <p><strong>Resource Person Photo:</strong></p>
            <img src="<?= htmlspecialchars($event['resource_person_photo']); ?>" width="200" class="mb-3">
        <?php endif; ?>

        <?php if (!empty($event['event_photos'])): ?>
            <p><strong>Event Photos:</strong></p>
            <div id="photos-container">
                <?php
                $photoPaths = explode(',', $event['event_photos']);
                foreach ($photoPaths as $photoPath):
                ?>
                    <img src="<?= htmlspecialchars($photoPath); ?>" class="event-photo m-1" width="150"
                         data-photo-path="<?= $photoPath; ?>">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No event photos uploaded.</p>
        <?php endif; ?>

        <a href="faculty_dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>

<script>
$(document).ready(function() {
    $(".event-photo").click(function() {
        var photoPath = $(this).data("photo-path");

        if (confirm("Do you want to delete this photo?")) {
            $.ajax({
                url: "delete_photo.php",
                type: "POST",
                data: { photo: photoPath },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === "success") {
                        alert("Photo deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                },
                error: function() {
                    alert("Something went wrong.");
                }
            });
        }
    });
});
</script>

</body>
</html>
