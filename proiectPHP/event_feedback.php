<?php
    include_once "config/database.php";

    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $event_id = $_POST['event_id'];
        $member_id = $_POST['member_id'];
        $rating = $_POST['rating'];
        $comments = $_POST['comments'];

        $query = "
        INSERT INTO event_feedback (event_id, member_id, rating, comments)
        VALUES (:event_id, :member_id, :rating, :comments)
    ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comments', $comments);

        if ($stmt->execute()) {
            header("Location: events.php?");
        } else {
            echo "Error submitting feedback.";
        }
    }
?>
