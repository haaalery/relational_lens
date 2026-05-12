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

$extra_head = '
    <style>
        .auth-wrapper {
            background-image: linear-gradient(rgba(14, 58, 71, 0.8), rgba(14, 58, 71, 0.6)), url("https://images.unsplash.com/photo-1511632765486-a01980e01a18?q=80&w=2000&auto=format&fit=crop");
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

render_header("Sign In", "login", $extra_head);
?>

    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="auth-card reveal">
                        <div class="text-center mb-5">
                            <h2 class="playfair fw-bold text-navy">Welcome Back</h2>
                            <p class="text-muted">Enter your credentials to access the Collective.</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?> small mb-4">
                                <i class="bi bi-exclamation-circle me-2"></i><?= $message ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <?php csrf_input(); ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label small fw-bold">Password</label>
                                    <a href="#" class="small text-decoration-none">Forgot?</a>
                                </div>
                                <input type="password" name="password" class="form-control" placeholder="Your password" required>
                            </div>
                            <button type="submit" class="btn btn-navy text-white w-100 rounded-pill mb-4" style="background-color: var(--navy);">Sign In</button>
                            
                            <div class="text-center small">
                            New to the collective? <a href="register.php" class="text-terracotta fw-bold text-decoration-none">Join Now &rarr;</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php render_footer(); ?>