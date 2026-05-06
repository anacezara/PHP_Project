<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $profession = $_POST['profession'];
        $company = $_POST['company'];
        $expertise = $_POST['expertise'];
        $linkedin_profile = $_POST['linkedin_profile'];
        $bio = $_POST['bio'];
        $role = $_POST['role'] === 'mentor' ? 'mentor' : 'member';
        $status = $role === 'mentor' ? 'pending' : 'active';

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $profile_picture = 'uploads/' . basename($_FILES['profile_picture']['name']);
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
        }

        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id FROM members WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error_message = "This email is already registered.";
        } else {
            $query = "INSERT INTO members 
                  (first_name, last_name, email, password, profession, company, expertise, linkedin_profile, profile_picture, bio, role, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($query);
            $stmt->execute([
                $first_name,
                $last_name,
                $email,
                $hashed_password,
                $profession,
                $company,
                $expertise,
                $linkedin_profile,
                $profile_picture,
                $bio,
                $role,
                $status
            ]);

            if ($role === 'mentor') {
                $success_message = "Registration successful. Your mentor application is pending approval.";
            } else {
                header("Location: members.php");
                exit();
            }
        }
    }
?>

<div class="form-container">
    <h2>Register New Member</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php elseif (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Profession</label>
            <input type="text" name="profession" class="form-control">
        </div>

        <div class="form-group">
            <label>Company</label>
            <input type="text" name="company" class="form-control">
        </div>

        <div class="form-group">
            <label>Expertise</label>
            <textarea name="expertise" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>LinkedIn Profile</label>
            <input type="url" name="linkedin_profile" class="form-control">
        </div>

        <div class="form-group">
            <label>Profile Picture</label>
            <input type="file" name="profile_picture" class="form-control">
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Register as</label>
            <select name="role" class="form-control">
                <option value="member">Member</option>
                <option value="mentor">Mentor</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include_once "includes/footer.php"; ?>
