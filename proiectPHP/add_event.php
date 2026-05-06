<?php
    include_once "includes/header.php";
    include_once "config/database.php";

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        die("Database connection failed.");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];
        $event_type = $_POST['event_type'];
        $max_participants = $_POST['max_participants'];
        $created_by = $_SESSION['user_id'];

        $image = NULL;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($image_ext), $allowed_extensions)) {
                $image_new_name = uniqid() . '.' . $image_ext;
                $image_upload_path = 'uploads/events/' . $image_new_name;

                if (move_uploaded_file($image_tmp, $image_upload_path)) {
                    $image = $image_upload_path;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image type. Only JPG, PNG, GIF, JPEG files are allowed.";
            }
        }

        $query = "INSERT INTO events (title, description, event_date, location, event_type, max_participants, created_by, image) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if ($stmt->execute([$title, $description, $event_date, $location, $event_type, $max_participants, $created_by, $image])) {
            header("Location: events.php?msg=Event created successfully.");
            exit();
        } else {
            $error = "Failed to create event. Please try again.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
</head>
<body>
<div class="container mt-5">
    <h2>Create an Event</h2>
    <p class="mb-4">Fill out the form below to create a new event.</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="form-group">
            <label for="title">Event Title</label>
            <input type="text" name="title" class="form-control" id="title" required>
        </div>
        <div class="form-group">
            <label for="description">Event Description</label>
            <textarea name="description" class="form-control" id="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="event_date">Event Date and Time</label>
            <input type="datetime-local" name="event_date" class="form-control" id="event_date" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control" id="location">
        </div>
        <div class="form-group">
            <label for="event_type">Event Type</label>
            <select name="event_type" class="form-control" id="event_type" required>
                <option value="workshop">Workshop</option>
                <option value="mentoring">Mentoring</option>
                <option value="networking">Networking</option>
                <option value="conference">Conference</option>
            </select>
        </div>
        <div class="form-group">
            <label for="max_participants">Max Participants</label>
            <input type="number" name="max_participants" class="form-control" id="max_participants" required>
        </div>
        <div class="form-group">
            <label for="image">Event Image</label>
            <input type="file" name="image" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>

    <a href="events.php" class="btn btn-secondary mt-3">Back to Events</a>
</div>
</body>
</html>

<?php
    include_once "includes/footer.php";
?>
