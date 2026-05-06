<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    $view_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

    $query = "SELECT * FROM members WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $view_id);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    $isOwnProfile = ($_SESSION['user_id'] === $view_id);
    $isAdmin = ($_SESSION['role'] === 'admin');
?>

<div class="container mt-4">
    <h2><?php echo htmlspecialchars($isOwnProfile ? 'Your Profile' : $user['first_name'] . ' ' . $user['last_name']); ?></h2>
    <div class="card">
        <div class="card-body text-center">
            <?php if (!empty($user['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>"
                     alt="Profile Picture"
                     class="rounded-circle mb-3"
                     style="width: 150px; height: 150px; object-fit: cover;">
            <?php else: ?>
                <img src="uploads/default-profile.png"
                     alt="Profile Picture"
                     class="rounded-circle mb-3"
                     style="width: 150px; height: 150px; object-fit: cover;">
            <?php endif; ?>
            <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Profession:</strong> <?php echo htmlspecialchars($user['profession']); ?></p>
            <p><strong>Company:</strong> <?php echo htmlspecialchars($user['company']); ?></p>
            <p><strong>Expertise:</strong> <?php echo htmlspecialchars($user['expertise']); ?></p>
            <p><strong>LinkedIn:</strong>
                <a href="<?php echo htmlspecialchars($user['linkedin_profile']); ?>" target="_blank">View Profile</a>
            </p>
            <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>

            <?php if ($isOwnProfile || $isAdmin): ?>
                <div class="d-flex">
                    <a href="edit_member.php?id=<?php echo $view_id; ?>" class="btn btn-primary mr-2">Edit Profile</a>
                    <a href="delete_member.php?id=<?php echo $view_id; ?>"
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this profile?')">Delete Profile</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
    include_once "includes/footer.php";
?>
