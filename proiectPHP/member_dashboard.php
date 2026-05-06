<?php
    include_once "auth.php";
    include_once "includes/header.php";
    include_once "config/database.php";

    checkAuth('member');

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        die("Database connection failed.");
    }

    $member_id = $_SESSION['user_id'];
    $queryUser = "
        SELECT profession, company, expertise
        FROM members
        WHERE id = :member_id
    ";
    $stmtUser = $db->prepare($queryUser);
    $stmtUser->bindParam(':member_id', $member_id);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Member profile not found.";
        exit();
    }

    if (empty($user['profession']) && empty($user['company']) && empty($user['expertise'])) {
        $recommendations = [];
    } else {
        $queryRecommendations = "
            SELECT id, first_name, last_name, profession, company, expertise, linkedin_profile, profile_picture
            FROM members
            WHERE id != :member_id
            AND (
                (profession LIKE :profession OR company LIKE :company) OR
                (profession LIKE :profession OR expertise LIKE :expertise) OR
                (company LIKE :company OR expertise LIKE :expertise)
            )
            LIMIT 15;
        ";

        $stmtRecommendations = $db->prepare($queryRecommendations);
        $stmtRecommendations->bindParam(':member_id', $member_id);
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

    $queryMentors = "
        SELECT id, first_name, last_name, expertise, profession 
        FROM members 
        WHERE status = 'mentor'
    ";
    $stmtMentors = $db->prepare($queryMentors);
    $stmtMentors->execute();
    $mentors = $stmtMentors->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mentor_id'])) {
        $mentorId = $_POST['mentor_id'];

        $queryCheck = "
        SELECT COUNT(*) AS match_count 
        FROM mentorship_matches 
        WHERE mentor_id = :mentor_id 
        AND mentee_id = :mentee_id
    ";
        $stmtCheck = $db->prepare($queryCheck);
        $stmtCheck->bindParam(':mentor_id', $mentorId);
        $stmtCheck->bindParam(':mentee_id', $member_id);
        $stmtCheck->execute();
        $matchCount = $stmtCheck->fetch(PDO::FETCH_ASSOC)['match_count'];

        if ($matchCount > 0) {
            echo "<p style='color: red;'>You have already sent a match request to this mentor.</p>";
        } else {
            $queryRequest = "
            INSERT INTO mentorship_matches (mentor_id, mentee_id, status) 
            VALUES (:mentor_id, :mentee_id, 'pending')
        ";
            $stmtRequest = $db->prepare($queryRequest);
            $stmtRequest->bindParam(':mentor_id', $mentorId);
            $stmtRequest->bindParam(':mentee_id', $member_id);

            if ($stmtRequest->execute()) {
                echo "<p style='color: green;'>Match request sent successfully!</p>";
            } else {
                echo "<p style='color: red;'>Failed to send match request.</p>";
            }
        }
    }

    $queryMatches = "
        SELECT 
            m.id AS match_id, 
            mentor.first_name AS mentor_name, 
            m.status 
        FROM mentorship_matches m
        JOIN members mentor ON m.mentor_id = mentor.id
        WHERE m.mentee_id = :mentee_id
    ";
    $stmtMatches = $db->prepare($queryMatches);
    $stmtMatches->bindParam(':mentee_id', $member_id);
    $stmtMatches->execute();
    $matches = $stmtMatches->fetchAll(PDO::FETCH_ASSOC);

    $queryMenteeSessions = "
SELECT s.id AS session_id, mentor.first_name, mentor.last_name, s.session_date, s.notes, s.feedback
FROM mentorship_sessions s
JOIN mentorship_matches m ON s.match_id = m.id
JOIN members mentor ON m.mentor_id = mentor.id
WHERE m.mentee_id = :mentee_id AND s.session_date >= NOW()
ORDER BY s.session_date ASC
";

    $stmtMenteeSessions = $db->prepare($queryMenteeSessions);
    $stmtMenteeSessions->bindParam(':mentee_id', $member_id);
    $stmtMenteeSessions->execute();
    $menteeSessions = $stmtMenteeSessions->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
        $sessionId = $_POST['session_id'];
        $feedback = $_POST['feedback'];

        $queryUpdateFeedback = "
        UPDATE mentorship_sessions
        SET feedback = :feedback
        WHERE id = :session_id
    ";
        $stmtUpdateFeedback = $db->prepare($queryUpdateFeedback);
        $stmtUpdateFeedback->bindParam(':feedback', $feedback);
        $stmtUpdateFeedback->bindParam(':session_id', $sessionId);

        if ($stmtUpdateFeedback->execute()) {
            echo "<p style='color: green;'>Feedback submitted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Failed to submit feedback.</p>";
        }
    }

    $queryMenteeProgress = "
    SELECT 
        s.id AS session_id, 
        mentor.first_name, 
        mentor.last_name, 
        s.session_date, 
        s.notes AS session_notes, 
        p.progress_percentage, 
        p.notes AS progress_notes, 
        p.is_completed 
    FROM mentorship_sessions s
    JOIN mentorship_matches m ON s.match_id = m.id
    JOIN members mentor ON m.mentor_id = mentor.id
    LEFT JOIN mentorship_progress p ON s.id = p.session_id
    WHERE m.mentee_id = :mentee_id
";
    $stmtMenteeProgress = $db->prepare($queryMenteeProgress);
    $stmtMenteeProgress->bindParam(':mentee_id', $member_id);
    $stmtMenteeProgress->execute();
    $menteeProgress = $stmtMenteeProgress->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
</head>
<body>
<div class="container mt-5">
    <div class="jumbotron text-center">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
        <p class="lead">Discover mentorship opportunities, track your progress, and explore job recommendations.</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Available Mentors</h3>
        </div>
        <div class="card-body">
            <?php if (empty($mentors)): ?>
                <p class="text-muted">No mentors available at the moment.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($mentors as $mentor): ?>
                        <div class="col-md-4">
                            <div class="card member-card mb-4">
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($mentor['first_name'] . ' ' . $mentor['last_name']); ?>
                                    </h5>
                                    <p class="card-text">
                                        <strong>Profession:</strong> <?php echo htmlspecialchars($mentor['profession']); ?><br>
                                        <strong>Expertise:</strong> <?php echo htmlspecialchars($mentor['expertise']); ?>
                                    </p>
                                    <form method="POST" action="">
                                        <input type="hidden" name="mentor_id" value="<?php echo $mentor['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">Request Match</button>
                                    </form>
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
            <h3 class="text-center">Your Matches</h3>
        </div>
        <div class="card-body">
            <?php if (empty($matches)): ?>
                <p class="text-muted">You have no matches yet.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($matches as $match): ?>
                        <li class="list-group-item">
                            Mentor: <strong><?php echo htmlspecialchars($match['mentor_name']); ?></strong><br>
                            Status: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($match['status']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Your Scheduled Sessions</h3>
        </div>
        <div class="card-body">
            <?php if (empty($menteeSessions)): ?>
                <p class="text-muted">No upcoming sessions scheduled.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($menteeSessions as $session): ?>
                        <li class="list-group-item">
                            <strong>Mentor:</strong> <?php echo htmlspecialchars($session['first_name'] . ' ' . $session['last_name']); ?><br>
                            <strong>Date:</strong> <?php echo htmlspecialchars($session['session_date']); ?><br>
                            <strong>Notes:</strong> <?php echo htmlspecialchars($session['notes']); ?><br>
                            <strong>Feedback:</strong>
                            <?php if ($session['feedback']): ?>
                                <?php echo htmlspecialchars($session['feedback']); ?>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                    <textarea name="feedback" class="form-control mb-2" placeholder="Provide feedback" required></textarea>
                                    <button type="submit" name="submit_feedback" class="btn btn-primary btn-sm">Submit Feedback</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="text-center">Session Progress</h3>
        </div>
        <div class="card-body">
            <?php if (empty($menteeProgress)): ?>
                <p class="text-muted">No progress tracked for your sessions yet.</p>
            <?php else: ?>
                <?php
                $groupedProgress = [];
                foreach ($menteeProgress as $progress) {
                    $mentorName = $progress['first_name'] . ' ' . $progress['last_name'];
                    if (!isset($groupedProgress[$mentorName])) {
                        $groupedProgress[$mentorName] = [];
                    }
                    $groupedProgress[$mentorName][] = $progress;
                }
                ?>

                <?php foreach ($groupedProgress as $mentorName => $sessions): ?>
                    <div class="mb-4">
                        <h4><?php echo htmlspecialchars($mentorName); ?></h4>
                        <ul class="list-group">
                            <?php foreach ($sessions as $progress): ?>
                                <li class="list-group-item">
                                    <strong>Session Date:</strong> <?php echo htmlspecialchars($progress['session_date']); ?><br>
                                    <strong>Session Notes:</strong> <?php echo htmlspecialchars($progress['session_notes']); ?><br>
                                    <strong>Progress:</strong> <?php echo htmlspecialchars($progress['progress_percentage'] ?? 0); ?>%<br>
                                    <strong>Progress Notes:</strong> <?php echo htmlspecialchars($progress['progress_notes'] ?? 'No notes'); ?><br>
                                    <strong>Status:</strong> <?php echo $progress['is_completed'] ? 'Completed' : 'In Progress'; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
</body>
</html>

<?php
    include_once "includes/footer.php";
?>
