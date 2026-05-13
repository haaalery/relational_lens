<?php
require_once 'header.php';
require_once 'config/security.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "danger";
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Delete any old tokens for this email
                $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $delStmt->execute([$email]);

                // Store new token
                $insStmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $insStmt->execute([$email, $token, $expires]);

                // Generate the link
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token . "&email=" . urlencode($email);

                // Send the email
                require_once 'config/mail.php';
                $subject = "Password Reset Request - Relational Lens";
                $body = "
                    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                        <h2 style='color: #0E3A47;'>Password Reset Request</h2>
                        <p>We received a request to reset your password for your Relational Lens account.</p>
                        <p>Click the button below to set a new password. This link will expire in 1 hour.</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$resetLink' style='background-color: #C57D54; color: white; padding: 12px 25px; text-decoration: none; border-radius: 50px; font-weight: bold;'>Reset Password</a>
                        </div>
                        <p style='font-size: 0.8rem; color: #666;'>If you didn't request this, you can safely ignore this email.</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 0.7rem; color: #999;'>Relational Lens | Global Social Work Archive</p>
                    </div>
                ";

                if (send_mail($email, $subject, $body)) {
                    $message = "A password reset link has been sent to your email address.";
                    $messageType = "success";
                } else {
                    $message = "We could not send the reset email. Please try again later or contact support.";
                    $messageType = "danger";
                }
            } else {
                // We show success even if email doesn't exist for security (prevent email enumeration)
                $message = "If this email is registered, a password reset link has been sent.";
                $messageType = "success";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $message = "An error occurred. Please try again later.";
            $messageType = "danger";
        }
    }
}

render_header("Forgot Password", "login");
?>

<div class="auth-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="auth-card reveal">
                    <div class="logo-box">
                        <img src="logo.png" alt="Logo" height="60" class="mb-3">
                        <h2 class="playfair fw-bold h3">Reset Your Password</h2>
                        <p class="text-muted small">Enter your email and we'll send you a link to get back into your account.</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?> small mb-4" role="alert">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php csrf_input(); ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. name@example.com" required autofocus>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 py-3 fw-bold rounded-3 mb-3">
                            Send Reset Link
                        </button>

                        <div class="text-center mt-3 small">
                            <a href="login.php" class="text-decoration-none">&larr; Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>