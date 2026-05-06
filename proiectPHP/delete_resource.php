<?php
    include_once "config/database.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    if (isset($_GET['id'])) {
        $database = new Database();
        $db = $database->getConnection();

        $db->prepare("DELETE FROM resources WHERE id=?")->execute([$_GET['id']]);
    }

    header("Location: list_resources.php?msg=Resource deleted successfully.");
    exit();
?>
