<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    $userId = $_SESSION['user_id'];

    $database = new Database();
    $db = $database->getConnection();

    // Handle adding a new job
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_job'])) {
            // Handle adding a new job
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $company = trim($_POST['company']);
            $location = trim($_POST['location']);
            $job_type = $_POST['job_type'];

            if (!empty($title) && !empty($description) && !empty($company)) {
                $addJobQuery = "INSERT INTO jobs (title, description, company, location, job_type, posted_by)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $addJobStmt = $db->prepare($addJobQuery);
                $addJobStmt->execute([$title, $description, $company, $location, $job_type, $isAdmin ? $userId : null]);
                header("Location: list_jobs.php?msg=Job added successfully.");
                exit();
            } else {
                $error = "All required fields must be filled out.";
            }
        } elseif (isset($_POST['apply_job'])) {
            // Handle job application
            $jobId = $_POST['job_id'];

            if (!empty($jobId)) {
                $applyQuery = "INSERT INTO job_applications (member_id, job_id) VALUES (?, ?)";
                $applyStmt = $db->prepare($applyQuery);
                $applyStmt->execute([$userId, $jobId]);
                header("Location: list_jobs.php?msg=Successfully applied for the job.");
                exit();
            } else {
                $error = "Job ID is missing.";
            }
        }
    }

    $searchTitle = isset($_GET['title']) ? $_GET['title'] : '';
    $searchCompany = isset($_GET['company']) ? $_GET['company'] : '';
    $searchLocation = isset($_GET['location']) ? $_GET['location'] : '';
    $job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';

    $query = "SELECT * FROM jobs WHERE 1=1";
    $params = [];

    if (!empty($searchTitle)) {
        $query .= " AND title LIKE ?";
        $params[] = '%' . $searchTitle . '%';
    }

    if (!empty($searchCompany)) {
        $query .= " AND company LIKE ?";
        $params[] = '%' . $searchCompany . '%';
    }

    if (!empty($searchLocation)) {
        $query .= " AND location LIKE ?";
        $params[] = '%' . $searchLocation . '%';
    }

    if (!empty($job_type)) {
        $query .= " AND job_type = ?";
        $params[] = $job_type;
    }

    $query .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    function hasApplied($db, $userId, $jobId) {
        $checkQuery = "SELECT COUNT(*) FROM job_applications WHERE member_id = ? AND job_id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$userId, $jobId]);
        return $checkStmt->fetchColumn() > 0;
    }
?>

<div class="container mt-5">
    <h2>Jobs Board</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <p class="alert alert-danger"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <input type="text" name="title" class="form-control" placeholder="Search by title" value="<?php echo htmlspecialchars($searchTitle); ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <input type="text" name="company" class="form-control" placeholder="Search by company" value="<?php echo htmlspecialchars($searchCompany); ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <input type="text" name="location" class="form-control" placeholder="Search by location" value="<?php echo htmlspecialchars($searchLocation); ?>">
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <select name="job_type" class="form-control">
                    <option value="">Select Job Type</option>
                    <option value="full-time" <?php echo ($job_type === 'full-time') ? 'selected' : ''; ?>>Full-Time</option>
                    <option value="part-time" <?php echo ($job_type === 'part-time') ? 'selected' : ''; ?>>Part-Time</option>
                    <option value="internship" <?php echo ($job_type === 'internship') ? 'selected' : ''; ?>>Internship</option>
                    <option value="freelance" <?php echo ($job_type === 'freelance') ? 'selected' : ''; ?>>Freelance</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-block">Search & Filter</button>
            </div>
        </div>
    </form>


    <?php if ($isAdmin): ?>
        <button class="btn btn-success mb-3" data-toggle="collapse" data-target="#addJobForm">Add New Job</button>
    <?php endif; ?>

    <div id="addJobForm" class="collapse">
        <form method="POST">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" name="title" class="form-control" id="title" placeholder="Enter job title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" id="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" name="company" class="form-control" id="company" required>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" class="form-control" id="location">
            </div>
            <div class="form-group">
                <label for="job_type">Job Type</label>
                <select name="job_type" class="form-control" id="job_type" required>
                    <option value="full-time">Full-Time</option>
                    <option value="part-time">Part-Time</option>
                    <option value="internship">Internship</option>
                    <option value="freelance">Freelance</option>
                </select>
            </div>
            <button type="submit" name="add_job" class="btn btn-primary">Add Job</button>
        </form>
    </div>

    <!-- Jobs Listing -->
    <div class="row mt-4">
        <?php while ($job = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($job['description']); ?></p>
                        <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>

                        <?php if (hasApplied($db, $userId, $job['id'])): ?>
                            <button class="btn btn-secondary btn-block" disabled>Already Applied</button>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <button type="submit" name="apply_job" class="btn btn-primary btn-block">Apply</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($isAdmin): ?>
                        <div class="card-footer text-center">
                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_job.php?id=<?php echo $job['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this job?');">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
