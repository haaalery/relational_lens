<?php
require_once 'header.php';
require_once 'config/security.php';

$message = "";
$messageType = "";

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $bio = trim($_POST['bio'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $message = "All required fields must be filled.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "danger";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "danger";
    } elseif ($password !== $confirm_password) {
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

$extra_head = '
    <style>
        .auth-wrapper {
            background-image: linear-gradient(rgba(30, 91, 106, 0.8), rgba(30, 91, 106, 0.6)), url("https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2000&auto=format&fit=crop");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 0 5rem; /* Increased top padding for floating navbar */
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 3.5rem;
            color: var(--charcoal);
        }
        [data-theme="dark"] .auth-card {
            background: rgba(14, 42, 51, 0.9);
            color: var(--offwhite);
            border-color: rgba(255,255,255,0.1);
        }
    </style>
';

render_header("Join the Collective", "register", $extra_head);
?>

    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-10">
                    <div class="auth-card reveal">
                        <div class="text-center mb-5">
                            <h2 class="playfair fw-bold text-navy">Join the Collective</h2>
                            <p class="text-muted">Become a contributor to the global social work archive.</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?> small mb-4">
                                <i class="bi <?= $messageType == 'success' ? 'bi-check-circle' : 'bi-exclamation-circle' ?> me-2"></i><?= $message ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <?php csrf_input(); ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Primary Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="filmmaker">Filmmaker / Content Creator</option>
                                    <option value="reviewer">Scholar / Peer Reviewer</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Professional Bio</label>
                                <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about your work..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-navy text-white w-100 rounded-pill mb-4" style="background-color: var(--navy);">Create Account</button>
                            
                            <div class="text-center small">
                                Already a member? <a href="login.php" class="text-terracotta fw-bold text-decoration-none">Sign In Here &rarr;</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php render_footer(); ?>