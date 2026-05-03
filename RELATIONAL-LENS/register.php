<?php
require_once 'config/db.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $bio = $_POST['bio'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "danger";
    } else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $message = "Email already registered.";
                $messageType = "danger";
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, bio) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->execute([$name, $email, $password_hash, $role, $bio]);

                $message = "Registration successful! You can now sign in.";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = "A system error occurred. Please try again later.";
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Collective | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

    <a href="index.php" class="back-link"><i class="bi bi-arrow-left me-2"></i> Back to Home</a>

    <div class="container">
        <div class="auth-card">
            <div class="logo-box">
                <div class="logo-icon bg-navy"></div>
                <div class="logo-text">RELATIONAL LENS</div>
                <p class="text-muted small mt-2">Become a member of the global collective.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> small py-2"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Confirm</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Primary Role</label>
                    <select name="role" class="form-select" required>
                        <option value="filmmaker">Filmmaker / Practitioner</option>
                        <option value="reviewer">Peer Reviewer</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label small">Short Bio</label>
                    <textarea name="bio" class="form-control" rows="2" placeholder="Tell the collective about your work..."></textarea>
                </div>
                <button type="submit" class="btn btn-auth">Create Account</button>
                <div class="text-center mt-4 small">
                    Already a member? <a href="login.php" class="text-terracotta fw-bold text-decoration-none">Sign In</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
