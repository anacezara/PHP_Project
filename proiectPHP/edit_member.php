<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $database = new Database();
    $db = $database->getConnection();

    $isAdmin = ($_SESSION['role'] === 'admin');
    $edit_id = isset($_GET['id']) && $isAdmin ? $_GET['id'] : $_SESSION['user_id'];
    $query = "SELECT * FROM members WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $edit_id);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        echo "User not found.";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $profile_picture = $member['profile_picture'];
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
        }

        $query = "UPDATE members
                  SET first_name = ?, last_name = ?, email = ?, profession = ?, company = ?, expertise = ?, linkedin_profile = ?, bio = ?, profile_picture = ?
                  WHERE id = ?";

        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['profession'],
            $_POST['company'],
            $_POST['expertise'],
            $_POST['linkedin_profile'],
            $_POST['bio'],
            $profile_picture,
            $edit_id
        ]);

        header("Location: profile.php?id=" . $edit_id);
        exit();
    }
?>

<div class="form-container">
    <h2>Edit Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Profession</label>
            <input type="text" name="profession" class="form-control" value="<?php echo htmlspecialchars($member['profession']); ?>">
        </div>
        <div class="form-group">
            <label>Company</label>
            <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($member['company']); ?>">
        </div>
        <div class="form-group">
            <label>Expertise</label>
            <textarea name="expertise" class="form-control"><?php echo htmlspecialchars($member['expertise']); ?></textarea>
        </div>
        <div class="form-group">
            <label>LinkedIn Profile</label>
            <input type="url" name="linkedin_profile" class="form-control" value="<?php echo htmlspecialchars($member['linkedin_profile']); ?>">
        </div>
        <div class="form-group">
            <label>Profile Picture</label>
            <?php if ($member['profile_picture']): ?>
                <p>Current Picture: <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" style="width: 100px;"></p>
            <?php endif; ?>
            <input type="file" name="profile_picture" class="form-control">
        </div>
        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" class="form-control"><?php echo htmlspecialchars($member['bio']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php
    include_once "includes/footer.php";
?>
