<?php
/**
 * Relational Lens: Emergency Password Reset Tool
 * USAGE: 
 * 1. Open this file in your browser.
 * 2. Enter the email and the new password you want.
 * 3. Click Reset.
 * 4. IMPORTANT: Delete this file from your server immediately after use!
 */

require_once 'config/db.php';

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['password'];
    
    try {
        // 1. Generate new hash
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // 2. Update database
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$new_hash, $email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Success! The password for <strong>$email</strong> has been updated.";
            $messageType = "success";
        } else {
            $message = "Error: No user found with that email address.";
            $messageType = "danger";
        }
    } catch (Exception $e) {
        $message = "System Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Tool | Relational Lens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .brand { font-family: 'Playfair Display', serif; color: #0E3A47; font-weight: 700; margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="reset-card">
        <h2 class="brand">RELATIONAL LENS</h2>
        <h5 class="mb-4 text-center">Emergency Password Reset</h5>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Target Email</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. admin@example.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">New Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background: #0E3A47; border: none;">Update Password</button>
        </form>

        <div class="mt-4 p-3 bg-light rounded text-center">
            <small class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> SECURITY WARNING:</small><br>
            <small class="text-muted">Delete this file (<code>reset_tool.php</code>) immediately after you regain access!</small>
        </div>
    </div>
</body>
</html>