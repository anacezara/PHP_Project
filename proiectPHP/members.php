<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    $loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($loggedInUserId === null) {
        header('Location: login.php');
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM members ORDER BY role ASC, created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $membersByRole = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $role = $row['role'];
        if (!isset($membersByRole[$role])) {
            $membersByRole[$role] = [];
        }
        $membersByRole[$role][] = $row;
    }
?>

<div class="container mt-5">
    <h2>Members Directory</h2>

    <?php foreach ($membersByRole as $role => $members): ?>
        <h3 class="mt-4"><?php echo htmlspecialchars(ucfirst($role)); ?></h3>
        <div class="row">
            <?php foreach ($members as $member): ?>
                <div class="col-md-4 mb-4">
                    <div class="card member-card shadow-sm">
                        <img
                                src="<?php echo htmlspecialchars($member['profile_picture'] ?: 'uploads/default-profile.png'); ?>"
                                class="card-img-top"
                                alt="Profile Picture">

                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Profession:</strong>
                                <?php echo htmlspecialchars($member['profession']); ?><br>
                                <strong>Company:</strong>
                                <?php echo htmlspecialchars($member['company']); ?>
                            </p>
                            <a href="profile.php?id=<?php echo $member['id']; ?>" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php
    include_once "includes/footer.php";
?>
