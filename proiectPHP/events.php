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

    $query = "SELECT * FROM events ORDER BY event_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($events)) {
        echo "<p>No events found.</p>";
    }

    $groupedEvents = [];
    foreach ($events as $event) {
        $eventDate = strtotime($event['event_date']);
        $monthYear = date('F Y', $eventDate);
        $groupedEvents[$monthYear][] = $event;
    }

?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Events Calendar</h2>
    </div>

    <?php if ($isAdmin || $loggedInUserId): ?>
        <a href="add_event.php" class="btn btn-success">Add Event</a>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <?php if (!empty($groupedEvents)): ?>
        <?php foreach ($groupedEvents as $monthYear => $monthEvents): ?>

            <div class="month-header mt-4">
                <h3 class="text-primary"><?php echo htmlspecialchars($monthYear); ?></h3>
            </div>

            <div class="row">
                <?php foreach ($monthEvents as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php
                                $event_image = !empty($event['image']) ? $event['image'] : 'uploads/events/event.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($event_image); ?>" class="card-img-top" alt="Event Image">

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                                <p class="card-text text-muted"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p class="card-text text-muted"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            </div>

                            <div class="card-footer text-center">
                                <?php

                                    $registrationQuery = "SELECT * FROM event_registrations WHERE member_id = :member_id AND event_id = :event_id";
                                    $regStmt = $db->prepare($registrationQuery);
                                    $regStmt->bindParam(':member_id', $loggedInUserId);
                                    $regStmt->bindParam(':event_id', $event['id']);
                                    $regStmt->execute();
                                    $userRegistration = $regStmt->fetch(PDO::FETCH_ASSOC);

                                    $countQuery = "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = :event_id AND status = 'confirmed'";
                                    $countStmt = $db->prepare($countQuery);
                                    $countStmt->bindParam(':event_id', $event['id']);
                                    $countStmt->execute();
                                    $totalRegistrations = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

                                    $isFull = $totalRegistrations >= $event['max_participants'];
                                ?>

                                <?php if ($event['created_by'] == $loggedInUserId || $isAdmin): ?>
                                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                <?php endif; ?>

                                <?php if ($userRegistration): ?>
                                    <p>You are already registered.</p>
                                <?php elseif ($isFull): ?>
                                    <p class="text-danger">Event is full.</p>
                                <?php else: ?>
                                    <a href="register_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-success">Register</a>
                                <?php endif; ?>
                            </div>

                            <div class="feedback-section mt-3">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#feedback<?php echo $event['id']; ?>" aria-expanded="false" aria-controls="feedback<?php echo $event['id']; ?>">
                                    View Feedback and Add Yours
                                </button>

                                <div class="collapse" id="feedback<?php echo $event['id']; ?>">
                                    <div class="feedback-list border-top mt-3 pt-3">
                                        <?php
                                            $feedbackQuery = "
                                            SELECT ef.rating, ef.comments, ef.submitted_at, m.first_name, m.last_name
                                            FROM event_feedback ef
                                            JOIN members m ON ef.member_id = m.id
                                            WHERE ef.event_id = :event_id
                                            ORDER BY ef.submitted_at DESC
                                        ";
                                            $feedbackStmt = $db->prepare($feedbackQuery);
                                            $feedbackStmt->bindParam(':event_id', $event['id']);
                                            $feedbackStmt->execute();
                                            $feedbacks = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

                                            if (empty($feedbacks)) {
                                                echo "<p>No feedback yet. Be the first to leave a review!</p>";
                                            } else {
                                                foreach ($feedbacks as $feedback):
                                                    ?>
                                                    <div class="feedback-item mb-3">
                                                        <p><strong><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></strong> rated it <strong><?php echo $feedback['rating']; ?>/5</strong></p>
                                                        <p><?php echo htmlspecialchars($feedback['comments']); ?></p>
                                                        <p class="text-muted small"><?php echo date('F j, Y, g:i a', strtotime($feedback['submitted_at'])); ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php } ?>
                                    </div>

                                    <div class="add-feedback mt-3 border-top pt-3">
                                        <h6>Leave Your Feedback</h6>
                                        <form action="event_feedback.php" method="POST">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <input type="hidden" name="member_id" value="<?php echo $loggedInUserId; ?>">

                                            <div class="form-group">
                                                <label for="rating<?php echo $event['id']; ?>">Rating</label>
                                                <select class="form-control" id="rating<?php echo $event['id']; ?>" name="rating" required>
                                                    <option value="">Select Rating</option>
                                                    <option value="1">1 - Poor</option>
                                                    <option value="2">2 - Fair</option>
                                                    <option value="3">3 - Good</option>
                                                    <option value="4">4 - Very Good</option>
                                                    <option value="5">5 - Excellent</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="comments<?php echo $event['id']; ?>">Comments</label>
                                                <textarea class="form-control" id="comments<?php echo $event['id']; ?>" name="comments" rows="3" placeholder="Share your experience..." required></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm" style="display: block; margin: 0 auto;">Submit Feedback</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No events found.</p>
    <?php endif; ?>
</div>

<?php include_once "includes/footer.php"; ?>
