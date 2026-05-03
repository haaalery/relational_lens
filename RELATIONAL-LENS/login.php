<?php
require_once 'config/db.php';
session_start();

$message = "";
$messageType = "";

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid email or password.";
            $messageType = "danger";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $message = "A system error occurred. Please try again later.";
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Relational Lens</title>
    
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
                <p class="text-muted small mt-2">Welcome back to the collective.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> small py-2"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small mb-0">Password</label>
                        <a href="#" class="text-muted small text-decoration-none">Forgot?</a>
                    </div>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-auth">Sign In</button>
                <div class="text-center mt-4 small">
                    New to the collective? <a href="register.php" class="text-terracotta fw-bold text-decoration-none">Join Now</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
