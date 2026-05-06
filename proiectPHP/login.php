<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT id, first_name, last_name, email, password, role, status FROM members WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $row['password'])) {
                // Check mentor status
                if ($row['role'] === 'mentor' && $row['status'] !== 'mentor') {
                    $error_message = "Your mentor account is pending approval.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['first_name'] = $row['first_name'];
                    $_SESSION['last_name'] = $row['last_name'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['role'] = $row['role'];

                    // Redirect based on role
                    if ($row['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($row['role'] === 'mentor') {
                        header("Location: mentor_dashboard.php");
                    } else {
                        header("Location: member_dashboard.php");
                    }
                    exit();
                }
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "User not found.";
        }
    }
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Women in FinTech</title>
</head>
<body>
<div class="container mt-5">
    <h2>Login</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>

<?php
    include_once "includes/footer.php";
?>
