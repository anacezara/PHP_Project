<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women Tech Power Platform</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="logo/logo.png" alt="logo" class="logo">
        </a>
        <a class="navbar-brand" href="index.php">Women Tech Power platform</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="members.php">Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_resources.php">Resources</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="list_jobs.php">Jobs</a>
                </li>
                <?php
                    session_start();
                    if (isset($_SESSION['user_id'])):
                        $dashboardLink = "";
                        if ($_SESSION['role'] === 'admin') {
                            $dashboardLink = "admin_dashboard.php";
                        } elseif ($_SESSION['role'] === 'mentor') {
                            $dashboardLink = "mentor_dashboard.php";
                        } else {
                            $dashboardLink = "member_dashboard.php";
                        }
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $dashboardLink; ?>">Dashboard</a>
                        </li>
                    <?php endif; ?>
            </ul>
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_member.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


<div class="container mt-4">
