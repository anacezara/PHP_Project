<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

    if ($loggedInUserId === null) {
        header('Location: login.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    // Handle adding a resource
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $type = $_POST['type'];
        $resource_url = trim($_POST['resource_url']);
        $file_path = null;

        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] == 0) {
            $file_tmp = $_FILES['resource_file']['tmp_name'];
            $file_name = basename($_FILES['resource_file']['name']);
            $file_dir = 'uploads/';
            $file_path = $file_dir . $file_name;

            if (!move_uploaded_file($file_tmp, $file_path)) {
                $error = "Failed to upload the file.";
            }
        }

        if (!empty($title) && !empty($description) && ($type == 'downloadable' || filter_var($resource_url, FILTER_VALIDATE_URL))) {
            $query = "INSERT INTO resources (title, description, type, resource_url, created_by, file_path) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$title, $description, $type, $resource_url, $loggedInUserId, $file_path]);

            header("Location: list_resources.php?msg=Resource added successfully.");
            exit();
        } else {
            $error = "Invalid input. Please check your fields.";
        }
    }

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';

    $query = "SELECT * FROM resources WHERE 1";
    if (!empty($search)) {
        $query .= " AND (title LIKE :search OR description LIKE :search)";
    }
    if (!empty($filter_type)) {
        $query .= " AND type = :filter_type";
    }
    $query .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%');
    }
    if (!empty($filter_type)) {
        $stmt->bindValue(':filter_type', $filter_type);
    }
    $stmt->execute();
?>

<div class="container mt-5">
    <h2>Resources Directory</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="alert alert-danger"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select name="filter_type" class="form-control">
                    <option value="">Select Type</option>
                    <option value="article" <?php echo ($filter_type == 'article' ? 'selected' : ''); ?>>Article</option>
                    <option value="video" <?php echo ($filter_type == 'video' ? 'selected' : ''); ?>>Video</option>
                    <option value="podcast" <?php echo ($filter_type == 'podcast' ? 'selected' : ''); ?>>Podcast</option>
                    <option value="downloadable" <?php echo ($filter_type == 'downloadable' ? 'selected' : ''); ?>>Downloadable</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Search & Filter</button>
            </div>
        </div>
    </form>

    <?php if ($loggedInUserId): ?>
        <button class="btn btn-success mb-3" data-toggle="collapse" data-target="#addResourceForm">Add New Resource</button>
    <?php endif; ?>


    <div id="addResourceForm" class="collapse">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" class="form-control" id="title" placeholder="Enter resource title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" id="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" class="form-control" id="type" required>
                    <option value="article">Article</option>
                    <option value="video">Video</option>
                    <option value="podcast">Podcast</option>
                    <option value="downloadable">Downloadable</option>
                </select>
            </div>
            <div class="form-group">
                <label for="resource_url">Resource URL</label>
                <input type="url" name="resource_url" class="form-control" id="resource_url">
            </div>
            <div class="form-group">
                <label for="resource_file">Upload File</label>
                <input type="file" name="resource_file" class="form-control-file" id="resource_file">
            </div>
            <button type="submit" class="btn btn-primary">Add Resource</button>
        </form>
    </div>

    <div class="row mt-4">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>

                        <?php
                            $url = htmlspecialchars($row['resource_url']);
                            $file_path = htmlspecialchars($row['file_path']);
                            $type = $row['type'];
                            if ($type == 'article') {
                                echo "<p><a href=\"$url\" target=\"_blank\">Read the article</a></p>";
                            } elseif ($type == 'video') {
                                $videoId = null;

                                if (preg_match('/(?:youtu\.be\/|youtube\.com(?:\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/?|\S*\?v=)))([a-zA-Z0-9_-]+)/', $url, $matches)) {
                                    $videoId = $matches[1];
                                }

                                if ($videoId) {
                                    echo "<div class='video-container'>
                                                <iframe src=\"https://www.youtube.com/embed/$videoId\" frameborder=\"0\" allowfullscreen></iframe>
                                              </div>";
                                } else {
                                    echo "<p>Invalid YouTube video URL</p>";
                                }
                            } elseif ($type == 'podcast') {
                                $audio_extensions = ['mp3', 'm4a', 'ogg', 'wav'];
                                $extension = pathinfo($url, PATHINFO_EXTENSION);

                                if (in_array(strtolower($extension), $audio_extensions)) {
                                    echo "<audio controls><source src=\"$url\" type=\"audio/" . strtolower($extension) . "\">Your browser does not support the audio element.</audio>";
                                } else {
                                    echo "<p><a href=\"$url\" target=\"_blank\">Listen to the podcast</a></p>";
                                }
                            } elseif ($type == 'downloadable') {
                                if (!empty($file_path)) {
                                    echo "<p><a href=\"$file_path\" download>Download Resource</a></p>";
                                } elseif (!empty($url)) {
                                    echo "<p><a href=\"$url\" download>Download Resource</a></p>";
                                } else {
                                    echo "<p>Downloadable resource not available.</p>";
                                }
                            }

                        ?>

                    </div>

                    <?php if ($isAdmin || $row['created_by'] == $loggedInUserId): ?>
                        <div class="card-footer text-center">
                            <a href="edit_resource.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_resource.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this resource?')">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
