<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

    if (!$loggedInUserId) {
        header('Location: login.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $type = $_POST['type'];
        $resource_url = trim($_POST['resource_url']);
        $file_path = $_POST['current_file_path'];

        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
            $file_tmp = $_FILES['resource_file']['tmp_name'];
            $file_name = basename($_FILES['resource_file']['name']);
            $file_dir = 'uploads/';
            $file_path = $file_dir . $file_name;

            if (!move_uploaded_file($file_tmp, $file_path)) {
                $error = "Failed to upload the file.";
            }
        }

        $query = "UPDATE resources SET title=?, description=?, type=?, resource_url=?, file_path=? WHERE id=?";
        $stmt = $db->prepare($query);
        $stmt->execute([$title, $description, $type, $resource_url, $file_path, $_GET['id']]);

        header("Location: list_resources.php?msg=Resource updated successfully.");
        exit();
    }

    $query = "SELECT * FROM resources WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource || (!$isAdmin && $resource['created_by'] != $loggedInUserId)) {
        header("Location: list_resources.php?msg=Unauthorized access.");
        exit();
    }
?>

<div class="container mt-5">
    <h2>Edit Resource</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" class="form-control" id="title" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" id="description" required><?php echo htmlspecialchars($resource['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" class="form-control" id="type" required>
                <option value="article" <?php echo ($resource['type'] == 'article') ? 'selected' : ''; ?>>Article</option>
                <option value="video" <?php echo ($resource['type'] == 'video') ? 'selected' : ''; ?>>Video</option>
                <option value="podcast" <?php echo ($resource['type'] == 'podcast') ? 'selected' : ''; ?>>Podcast</option>
                <option value="downloadable" <?php echo ($resource['type'] == 'downloadable') ? 'selected' : ''; ?>>Downloadable</option>
            </select>
        </div>
        <div class="form-group">
            <label for="resource_url">Resource URL</label>
            <input type="url" name="resource_url" class="form-control" id="resource_url" value="<?php echo htmlspecialchars($resource['resource_url']); ?>">
        </div>
        <div class="form-group">
            <label for="resource_file">Upload File</label>
            <input type="file" name="resource_file" class="form-control-file" id="resource_file">
            <?php if (!empty($resource['file_path'])): ?>
                <p>Current file: <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank">Download</a></p>
                <input type="hidden" name="current_file_path" value="<?php echo htmlspecialchars($resource['file_path']); ?>">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Resource</button>
    </form>
</div>

<?php include_once "includes/footer.php"; ?>
