<?php
    include_once "auth.php";
    include_once "includes/header.php";
    include_once "config/database.php";

    checkAuth('admin');

    $database = new Database();
    $db = $database->getConnection();

    if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
        $mentorId = $_GET['approve'];
        $query = "UPDATE members SET status = 'mentor', role = 'mentor' WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $mentorId, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: admin_dashboard.php?approved=true");
        exit();
    }

    $query = "SELECT id, first_name, last_name, email, profession, company FROM members WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pendingMentors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $admin_id = $_SESSION['user_id'];
    $queryUser = "
        SELECT profession, company, expertise
        FROM members
        WHERE id = :admin_id
    ";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->bindParam(':admin_id', $admin_id);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Admin profile not found.";
        exit();
    }

    if (empty($user['profession']) && empty($user['company']) && empty($user['expertise'])) {
        $recommendations = [];
    } else {
        $queryRecommendations = "
            SELECT id, first_name, last_name, profession, company, expertise, linkedin_profile, profile_picture
            FROM members
            WHERE id != :admin_id
            AND (
                (profession LIKE :profession AND :profession IS NOT NULL) OR
                (company LIKE :company AND :company IS NOT NULL) OR
                (expertise LIKE :expertise AND :expertise IS NOT NULL)
            )
            LIMIT 15;
        ";

        $stmtRecommendations = $db->prepare($queryRecommendations);
        $stmtRecommendations->bindParam(':admin_id', $admin_id);

        $stmtRecommendations->bindValue(':profession', !empty($user['profession']) ? '%' . $user['profession'] . '%' : null, PDO::PARAM_STR);
        $stmtRecommendations->bindValue(':company', !empty($user['company']) ? '%' . $user['company'] . '%' : null, PDO::PARAM_STR);
        $stmtRecommendations->bindValue(':expertise', !empty($user['expertise']) ? '%' . $user['expertise'] . '%' : null, PDO::PARAM_STR);

        $stmtRecommendations->execute();
        $recommendations = $stmtRecommendations->fetchAll(PDO::FETCH_ASSOC);
    }

    $jobRecommendations = [];
    if (!empty($user['profession']) || !empty($user['expertise'])) {
        $queryJobRecommendations = "
        SELECT id, title, description, company, location, job_type
        FROM jobs
        WHERE
            (title LIKE :profession OR description LIKE :profession)
            OR
            (title LIKE :expertise OR description LIKE :expertise)
        LIMIT 10;
    ";
        $stmtJobRecommendations = $db->prepare($queryJobRecommendations);

        $professionSearchTerm = !empty($user['profession']) ? '%' . $user['profession'] . '%' : null;
        $expertiseSearchTerm = !empty($user['expertise']) ? '%' . $user['expertise'] . '%' : null;

        $stmtJobRecommendations->bindValue(':profession', $professionSearchTerm, $professionSearchTerm ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmtJobRecommendations->bindValue(':expertise', $expertiseSearchTerm, $expertiseSearchTerm ? PDO::PARAM_STR : PDO::PARAM_NULL);

        $stmtJobRecommendations->execute();
        $jobRecommendations = $stmtJobRecommendations->fetchAll(PDO::FETCH_ASSOC);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
<div class="container mt-5">

    <div class="jumbotron text-center">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
        <p class="lead">Manage mentors, view recommendations, and enhance your network.</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Pending Mentor Approvals</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['approved'])): ?>
                <div class="alert alert-success">Mentor account approved successfully!</div>
            <?php endif; ?>
            <?php if (count($pendingMentors) > 0): ?>
                <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="text-white">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Profession</th>
                        <th>Company</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendingMentors as $mentor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mentor['first_name'] . ' ' . $mentor['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($mentor['email']); ?></td>
                            <td><?php echo htmlspecialchars($mentor['profession']); ?></td>
                            <td><?php echo htmlspecialchars($mentor['company']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?approve=<?php echo $mentor['id']; ?>" class="btn btn-success btn-sm">
                                    Approve
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No pending mentor approvals.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Recommended Connections Based on Your Profile</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <p class="text-muted">No recommended members found. Please update your profile to get tailored recommendations!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($recommendations as $rec): ?>
                        <div class="col-md-4">
                            <div class="card member-card">
                                <img src="<?php echo htmlspecialchars($rec['profile_picture'] ?: 'uploads/default-profile.png'); ?>"
                                     class="card-img-top rounded-circle mx-auto d-block mt-3"
                                     alt="Profile Picture"
                                     style="width: 150px; height: 150px;">
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($rec['first_name'] . ' ' . $rec['last_name']); ?>
                                    </h5>
                                    <p class="card-text">
                                        <strong>Profession:</strong> <?php echo htmlspecialchars($rec['profession']); ?><br>
                                        <strong>Company:</strong> <?php echo htmlspecialchars($rec['company']); ?><br>
                                        <strong>Expertise:</strong> <?php echo htmlspecialchars($rec['expertise']); ?>
                                    </p>
                                    <a href="profile.php?id=<?php echo $rec['id']; ?>" class="btn btn-primary btn-sm">View Profile</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Recommended Jobs</h3>
        </div>
        <div class="card-body">
            <?php if (empty($jobRecommendations)): ?>
                <p class="text-muted">No job recommendations found. Please update your profile for better matches!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($jobRecommendations as $job): ?>
                        <div class="col-md-4">
                            <div class="card job-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                    <p class="card-text">
                                        <strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                                        <strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?>
                                    </p>
                                    <a href="list_jobs.php?job_id=<?php echo $job['id']; ?>#job-<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                                        View Job
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>


<?php
    include_once "includes/footer.php";
?>
