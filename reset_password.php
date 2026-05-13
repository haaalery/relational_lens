<?php
require_once 'header.php';
require_once 'config/security.php';

$message = "";
$messageType = "";
$validToken = false;

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (!$token || !$email) {
    header("Location: login.php");
    exit;
}

try {
    // Validate token and expiry
    $stmt = $pdo->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
    $stmt->execute([$email, $token]);
    if ($stmt->fetch()) {
        $validToken = true;
    } else {
        $message = "Invalid or expired reset link. Please request a new one.";
        $messageType = "danger";
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $message = "A system error occurred.";
    $messageType = "danger";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    verify_csrf();
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "danger";
    } else {
        try {
            $pdo->beginTransaction();

            // Update user password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $updateStmt->execute([$hashedPassword, $email]);

            // Delete the token
            $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $delStmt->execute([$email]);

            $pdo->commit();
            $message = "Password updated successfully! You can now <a href='login.php'>Login</a>.";
            $messageType = "success";
            $validToken = false; // Hide form after success
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log($e->getMessage());
            $message = "Failed to update password.";
            $messageType = "danger";
        }
    }
}

render_header("Reset Password", "login");
?>

<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="auth-card reveal">
                    <div class="logo-box">
                        <img src="logo.png" alt="Logo" height="60" class="mb-3">
                        <h2 class="playfair fw-bold h3">Set New Password</h2>
                        <p class="text-muted small">Choose a strong password to secure your account.</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?> small mb-4" role="alert">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($validToken): ?>
                        <form method="POST">
                            <?php csrf_input(); ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">New Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8" autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8">
                            </div>

                            <button type="submit" class="btn btn-navy w-100 py-3 fw-bold rounded-3 mb-3">
                                Update Password
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3 small">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>