<?php
include_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $query = "DELETE FROM jobs WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    echo "<p style='color:green;'>Job deleted successfully!</p>";
    header("Location: list_jobs.php");
    exit;
} else {
    echo "<p style='color:red;'>No job ID provided!</p>";
    exit;
}
?>
