<?php
    include_once "auth.php";
    include_once "includes/header.php";
    include_once "config/database.php";

    checkAuth('mentor');

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        die("Database connection failed.");
    }

    $mentor_id = $_SESSION['user_id'];
    $queryUser = "
        SELECT profession, company, expertise
        FROM members
        WHERE id = :mentor_id
    ";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->bindParam(':mentor_id', $mentor_id);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Mentor profile not found.";
        exit();
    }

    if (empty($user['profession']) && empty($user['company']) && empty($user['expertise'])) {
        $recommendations = []; // No recommendations if profile is incomplete
    } else {
        $queryRecommendations = "
            SELECT id, first_name, last_name, profession, company, expertise, linkedin_profile, profile_picture
            FROM members
            WHERE id != :mentor_id
            AND (
                (profession LIKE :profession AND company LIKE :company) OR
                (profession LIKE :profession AND expertise LIKE :expertise) OR
                (company LIKE :company AND expertise LIKE :expertise)
            )
            LIMIT 15;
        ";

        $stmtRecommendations = $db->prepare($queryRecommendations);
        $stmtRecommendations->bindParam(':mentor_id', $mentor_id);
        $stmtRecommendations->bindValue(':profession', '%' . $user['profession'] . '%');
        $stmtRecommendations->bindValue(':company', '%' . $user['company'] . '%');
        $stmtRecommendations->bindValue(':expertise', '%' . $user['expertise'] . '%');
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

    if (isset($_GET['action']) && isset($_GET['match_id'])) {
        $matchId = $_GET['match_id'];
        $status = $_GET['action'] === 'approve' ? 'active' : 'declined';

        $query = "UPDATE mentorship_matches SET status = :status WHERE id = :match_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':match_id', $matchId);
        $stmt->execute();

        echo "<p style='color: green;'>Request $status successfully.</p>";
    }

    $queryPending = "
    SELECT m.id AS match_id, me.first_name, me.last_name 
    FROM mentorship_matches m
    JOIN members me ON m.mentee_id = me.id
    WHERE m.mentor_id = :mentor_id AND m.status = 'pending'
";
    $stmtPending = $db->prepare($queryPending);
    $stmtPending->bindParam(':mentor_id', $mentor_id);
    $stmtPending->execute();
    $pendingRequests = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

    $queryMatches = "
    SELECT 
        m.id AS match_id, 
        mentee.first_name AS mentee_name, 
        m.status 
    FROM mentorship_matches m
    JOIN members mentee ON m.mentee_id = mentee.id
    WHERE m.mentor_id = :mentor_id
";
    $stmtMatches = $db->prepare($queryMatches);
    $stmtMatches->bindParam(':mentor_id', $mentor_id);
    $stmtMatches->execute();
    $matches = $stmtMatches->fetchAll(PDO::FETCH_ASSOC);

    $queryActiveMatches = "
    SELECT m.id AS match_id, mentee.first_name, mentee.last_name 
    FROM mentorship_matches m
    JOIN members mentee ON m.mentee_id = mentee.id
    WHERE m.mentor_id = :mentor_id AND m.status = 'active'
";
    $stmtActiveMatches = $db->prepare($queryActiveMatches);
    $stmtActiveMatches->bindParam(':mentor_id', $mentor_id);
    $stmtActiveMatches->execute();
    $activeMatches = $stmtActiveMatches->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_session'])) {
        $matchId = $_POST['match_id'];
        $sessionDate = $_POST['session_date'];
        $notes = $_POST['notes'];

        $queryInsertSession = "
        INSERT INTO mentorship_sessions (match_id, session_date, notes)
        VALUES (:match_id, :session_date, :notes)
    ";
        $stmtInsertSession = $db->prepare($queryInsertSession);
        $stmtInsertSession->bindParam(':match_id', $matchId);
        $stmtInsertSession->bindParam(':session_date', $sessionDate);
        $stmtInsertSession->bindParam(':notes', $notes);

        if ($stmtInsertSession->execute()) {
            echo "<p style='color: green;'>Session scheduled successfully!</p>";
        } else {
            echo "<p style='color: red;'>Failed to schedule the session.</p>";
        }
    }

    $queryScheduledSessions = "
SELECT s.id AS session_id, mentee.first_name, mentee.last_name, s.session_date, s.notes, s.feedback
FROM mentorship_sessions s
JOIN mentorship_matches m ON s.match_id = m.id
JOIN members mentee ON m.mentee_id = mentee.id
WHERE m.mentor_id = :mentor_id AND s.session_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
ORDER BY s.session_date ASC
";
    $stmtScheduledSessions = $db->prepare($queryScheduledSessions);
    $stmtScheduledSessions->bindParam(':mentor_id', $mentor_id);
    $stmtScheduledSessions->execute();
    $scheduledSessions = $stmtScheduledSessions->fetchAll(PDO::FETCH_ASSOC);

    $querySessionProgress = "
    SELECT 
        s.id AS session_id, 
        mentee.first_name, 
        mentee.last_name, 
        s.session_date, 
        s.notes AS session_notes, 
        p.progress_percentage, 
        p.notes AS progress_notes, 
        p.is_completed 
    FROM mentorship_sessions s
    JOIN mentorship_matches m ON s.match_id = m.id
    JOIN members mentee ON m.mentee_id = mentee.id
    LEFT JOIN mentorship_progress p ON s.id = p.session_id
    WHERE m.mentor_id = :mentor_id
";
    $stmtSessionProgress = $db->prepare($querySessionProgress);
    $stmtSessionProgress->bindParam(':mentor_id', $mentor_id);
    $stmtSessionProgress->execute();
    $sessionProgress = $stmtSessionProgress->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress'])) {
        $sessionId = $_POST['session_id'];
        $progressPercentage = $_POST['progress_percentage'];
        $progressNotes = $_POST['progress_notes'];
        $isCompleted = isset($_POST['is_completed']) ? 1 : 0;

        $queryUpdateProgress = "
        INSERT INTO mentorship_progress (session_id, progress_percentage, notes, is_completed)
        VALUES (:session_id, :progress_percentage, :progress_notes, :is_completed)
        ON DUPLICATE KEY UPDATE
        progress_percentage = :progress_percentage,
        notes = :progress_notes,
        is_completed = :is_completed
    ";
        $stmtUpdateProgress = $db->prepare($queryUpdateProgress);
        $stmtUpdateProgress->bindParam(':session_id', $sessionId);
        $stmtUpdateProgress->bindParam(':progress_percentage', $progressPercentage);
        $stmtUpdateProgress->bindParam(':progress_notes', $progressNotes);
        $stmtUpdateProgress->bindParam(':is_completed', $isCompleted);

        if ($stmtUpdateProgress->execute()) {
            echo "<p style='color: green;'>Progress updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Failed to update progress.</p>";
        }
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard</title>
</head>
<body>
<div class="container mt-5">
    <div class="jumbotron text-center">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
        <p class="lead">Guide mentees, track progress, and schedule sessions.</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Pending Mentee Requests</h3>
        </div>
        <div class="card-body">
            <?php if (empty($pendingRequests)): ?>
                <p class="text-muted">No pending requests.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($pendingRequests as $request): ?>
                        <li class="list-group-item">
                            <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                            <a href="mentor_dashboard.php?action=approve&match_id=<?php echo $request['match_id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="mentor_dashboard.php?action=decline&match_id=<?php echo $request['match_id']; ?>" class="btn btn-danger btn-sm">Decline</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Your Matches</h3>
        </div>
        <div class="card-body">
            <?php if (empty($matches)): ?>
                <p class="text-muted">You have no matches yet.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($matches as $match): ?>
                        <li class="list-group-item">
                            Mentee: <strong><?php echo htmlspecialchars($match['mentee_name']); ?></strong><br>
                            Status: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($match['status']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Active Matches</h3>
        </div>
        <div class="card-body">
            <?php if (empty($activeMatches)): ?>
                <p class="text-muted">No active matches available.</p>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="match_id" class="form-label">Select Mentee:</label>
                        <select name="match_id" class="form-select" required>
                            <?php foreach ($activeMatches as $match): ?>
                                <option value="<?php echo $match['match_id']; ?>">
                                    <?php echo htmlspecialchars($match['first_name'] . ' ' . $match['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="session_date" class="form-label">Session Date:</label>
                        <input type="datetime-local" name="session_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes:</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                    <button type="submit" name="schedule_session" class="btn btn-primary">Schedule Session</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Scheduled Sessions</h3>
        </div>
        <div class="card-body">
            <?php if (empty($scheduledSessions)): ?>
                <p class="text-muted">No upcoming sessions scheduled.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($scheduledSessions as $session): ?>
                        <li class="list-group-item">
                            <strong>Mentee:</strong> <?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?><br>
                            <strong>Date:</strong> <?php echo htmlspecialchars($session['session_date']); ?><br>
                            <strong>Notes:</strong> <?php echo htmlspecialchars($session['notes']); ?><br>
                            <strong>Feedback:</strong> <?php echo htmlspecialchars($session['feedback']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4 ">
        <div class="card-header">
            <h3 class="text-center">Progress Tracking</h3>
        </div>
        <div class="card-body">
            <?php if (empty($sessionProgress)): ?>
                <p class="text-muted">No sessions available for progress tracking.</p>
            <?php else: ?>
                <?php
                $groupedProgress = [];
                foreach ($sessionProgress as $progress) {
                    $menteeId = $progress['first_name'] . ' ' . $progress['last_name'];
                    if (!isset($groupedProgress[$menteeId])) {
                        $groupedProgress[$menteeId] = [];
                    }
                    $groupedProgress[$menteeId][] = $progress;
                }
                ?>

                <?php foreach ($groupedProgress as $menteeName => $sessions): ?>
                    <div class="mb-4">
                        <h4><?php echo htmlspecialchars($menteeName); ?></h4>
                        <ul class="list-group">
                            <?php foreach ($sessions as $progress): ?>
                                <li class="list-group-item">
                                    <strong>Session Date:</strong> <?php echo htmlspecialchars($progress['session_date']); ?><br>
                                    <strong>Session Notes:</strong> <?php echo htmlspecialchars($progress['session_notes']); ?><br>
                                    <strong>Progress:</strong> <?php echo htmlspecialchars($progress['progress_percentage'] ?? 0); ?>%<br>
                                    <strong>Progress Notes:</strong> <?php echo htmlspecialchars($progress['progress_notes'] ?? 'No notes'); ?><br>
                                    <strong>Status:</strong> <?php echo $progress['is_completed'] ? 'Completed' : 'In Progress'; ?><br>
                                    <form method="POST" action="">
                                        <input type="hidden" name="session_id" value="<?php echo $progress['session_id']; ?>">
                                        <div class="mb-3">
                                            <label for="progress_percentage" class="form-label">Progress (%):</label>
                                            <input type="number" name="progress_percentage" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($progress['progress_percentage'] ?? 0); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="progress_notes" class="form-label">Progress Notes:</label>
                                            <textarea name="progress_notes" class="form-control"><?php echo htmlspecialchars($progress['progress_notes'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="is_completed" class="form-check-input" <?php echo $progress['is_completed'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_completed">Mark as Completed</label>
                                        </div>
                                        <button type="submit" name="update_progress" class="btn btn-primary btn-sm">Update Progress</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Recommended Connections</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recommendations)): ?>
                <p class="text-muted">No recommended members found. Update your profile to improve recommendations!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($recommendations as $rec): ?>
                        <div class="col-md-4">
                            <div class="card member-card mb-4">
                                <img src="<?php echo htmlspecialchars($rec['profile_picture'] ?: 'uploads/default-profile.png'); ?>"
                                     class="card-img-top rounded-circle mx-auto d-block mt-3"
                                     alt="Profile Picture"
                                     style="width: 150px; height: 150px;">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($rec['first_name'] . ' ' . $rec['last_name']); ?></h5>
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
            <h3 class="text-center">Recommended Jobs</h3>
        </div>
        <div class="card-body">
            <?php if (empty($jobRecommendations)): ?>
                <p class="text-muted">No job recommendations found. Please update your profile for better matches!</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($jobRecommendations as $job): ?>
                        <div class="col-md-4">
                            <div class="card job-card mb-4">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                    <p class="card-text">
                                        <strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?><br>
                                        <strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?>
                                    </p>
                                    <a href="list_jobs.php?job_id=<?php echo $job['id']; ?>#job-<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">View Job</a>
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
