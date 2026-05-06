<?php
    function checkAuth($requiredRole) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        if ($_SESSION['role'] !== $requiredRole) {
            header("Location: forbidden.php");
            exit();
        }
    }
?>
