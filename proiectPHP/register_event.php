<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $loggedInUserId = $_SESSION['user_id'];
    $eventId = $_GET['event_id'];

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM events WHERE id = :event_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':event_id', $eventId);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header("Location: events.php?msg=Event not found.");
        exit();
    }

    $countQuery = "SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = :event_id AND status = 'confirmed'";
    $countStmt = $db->prepare($countQuery);
    $countStmt->bindParam(':event_id', $eventId);
    $countStmt->execute();
    $totalRegistrations = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($totalRegistrations >= $event['max_participants']) {
        header("Location: events.php?msg=Event is full.");
        exit();
    }

    $insertQuery = "INSERT INTO event_registrations (member_id, event_id) VALUES (:member_id, :event_id)";
    $insertStmt = $db->prepare($insertQuery);
    $insertStmt->bindParam(':member_id', $loggedInUserId);
    $insertStmt->bindParam(':event_id', $eventId);

    if ($insertStmt->execute()) {
        header("Location: events.php?");
    } else {
        header("Location: events.php?msg=Registration failed. Please try again.");
    }
?>
