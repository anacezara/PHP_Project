<?php
    include_once "config/database.php";

    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            header("Location: members.php");
            exit();
        }

        $database = new Database();
        $db = $database->getConnection();

        try {
            $query = "DELETE FROM members WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting member: " . $e->getMessage());
            header("Location: error.php");
            exit();
        }
    }

    header("Location: members.php");
    exit();
?>
