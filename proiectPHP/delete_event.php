<?php
    include_once "config/database.php";

    if (isset($_GET['id'])) {
        $event_id = $_GET['id'];

        $database = new Database();
        $db = $database->getConnection();

        $query = "DELETE FROM events WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$event_id]);

        header("Location: events.php?");
        exit();
    }
?>
