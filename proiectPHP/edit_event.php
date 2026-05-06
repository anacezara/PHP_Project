<?php
    include_once "config/database.php";

    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $event_id = $_GET['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $event_date = $_POST['event_date'];
        $location = $_POST['location'];
        $event_type = $_POST['event_type'];
        $max_participants = $_POST['max_participants'];

        $image = $_POST['current_image'];
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

        $query = "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, event_type = ?, max_participants = ?, image = ? 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$title, $description, $event_date, $location, $event_type, $max_participants, $image, $event_id]);

        header("Location: events.php?");
        exit();
    }

    $event_id = $_GET['id'];
    $query = "SELECT * FROM events WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header("Location: events.php?msg=Event not found.");
        exit();
    }
?>

<?php include_once "includes/header.php"; ?>

<div class="container mt-5">
    <h2>Edit Event</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label for="title">Event Title</label>
            <input type="text" name="title" class="form-control" id="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Event Description</label>
            <textarea name="description" class="form-control" id="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="event_date">Event Date and Time</label>
            <input type="datetime-local" name="event_date" class="form-control" id="event_date" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control" id="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
        </div>

        <div class="form-group">
            <label for="event_type">Event Type</label>
            <select name="event_type" class="form-control" id="event_type" required>
                <option value="workshop" <?php echo ($event['event_type'] == 'workshop' ? 'selected' : ''); ?>>Workshop</option>
                <option value="mentoring" <?php echo ($event['event_type'] == 'mentoring' ? 'selected' : ''); ?>>Mentoring</option>
                <option value="networking" <?php echo ($event['event_type'] == 'networking' ? 'selected' : ''); ?>>Networking</option>
                <option value="conference" <?php echo ($event['event_type'] == 'conference' ? 'selected' : ''); ?>>Conference</option>
            </select>
        </div>

        <div class="form-group">
            <label for="max_participants">Max Participants</label>
            <input type="number" name="max_participants" class="form-control" id="max_participants" value="<?php echo htmlspecialchars($event['max_participants']); ?>" required>
        </div>

        <div class="form-group">
            <label for="image">Event Image</label>
            <input type="file" name="image" class="form-control-file">
            <?php if ($event['image']) { ?>
                <div class="mt-2">
                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" width="150">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($event['image']); ?>">
                </div>
            <?php } ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Event</button>
    </form>
</div>

<?php include_once "includes/footer.php"; ?>
