<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $job_id = $_POST['job_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $company = $_POST['company'];
        $location = $_POST['location'];
        $job_type = $_POST['job_type'];

        $query = "UPDATE jobs SET title = ?, description = ?, company = ?, location = ?, job_type = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$title, $description, $company, $location, $job_type, $job_id]);

        header("Location: list_jobs.php?msg=Job updated successfully.");
        exit();
    }

    if (isset($_GET['id'])) {
        $query = "SELECT * FROM jobs WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET['id']]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            header("Location: list_jobs.php?msg=Job not found.");
            exit();
        }
    } else {
        header("Location: list_jobs.php?msg=No job ID provided.");
        exit();
    }
?>

<div class="container mt-5">
    <h2>Edit Job</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">

        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" name="title" class="form-control" id="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" id="description" required><?php echo htmlspecialchars($job['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="company">Company</label>
            <input type="text" name="company" class="form-control" id="company" value="<?php echo htmlspecialchars($job['company']); ?>" required>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control" id="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
        </div>

        <div class="form-group">
            <label for="job_type">Job Type</label>
            <select name="job_type" class="form-control" id="job_type" required>
                <option value="full-time" <?php echo ($job['job_type'] == 'full-time' ? 'selected' : ''); ?>>Full-Time</option>
                <option value="part-time" <?php echo ($job['job_type'] == 'part-time' ? 'selected' : ''); ?>>Part-Time</option>
                <option value="internship" <?php echo ($job['job_type'] == 'internship' ? 'selected' : ''); ?>>Internship</option>
                <option value="freelance" <?php echo ($job['job_type'] == 'freelance' ? 'selected' : ''); ?>>Freelance</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Job</button>
    </form>
</div>

<?php include_once "includes/footer.php"; ?>
