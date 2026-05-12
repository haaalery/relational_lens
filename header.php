<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/db.php';

function render_header($title = "Relational Lens", $active_page = "home", $extra_head = "") {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | Relational Lens</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=1.2">
    
    <?= $extra_head ?>
    
    <script>
        // Prevent theme flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand animate-up" href="index.php">
                <div class="nav-logo-wrapper"><img src="logo.png" alt="Relational Lens Logo"></div>
                RELATIONAL LENS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'home' ? 'active' : '' ?>" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'archive' ? 'active' : '' ?>" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'gallery' ? 'active' : '' ?>" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'mission' ? 'active' : '' ?>" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'about' ? 'active' : '' ?>" href="about.php">About</a></li>
                    
                    <li class="nav-item"><a class="nav-link <?= $active_page == 'map' ? 'active' : '' ?>" href="map.php">Map</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($active_page == 'submit' || $active_page == 'submit_article') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                            Submit
                        </a>
                        <ul class="dropdown-menu border-0 shadow-lg" style="min-width: 280px;">
                            <li>
                                <a class="dropdown-item mb-2" href="submit.php">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-terracotta-soft text-terracotta rounded-circle me-3">
                                            <i class="bi bi-film fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">Submit Story</div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Share your visual journey</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="submit_article.php">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-teal-soft text-teal rounded-circle me-3">
                                            <i class="bi bi-journal-text fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small">Submit Article</div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Write a deep dive insight</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle btn-signin" href="#" role="button" data-bs-toggle="dropdown">
                                Hi, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="min-width: 240px;">
                                <li>
                                    <a class="dropdown-item mb-2" href="profile.php?id=<?= $_SESSION['user_id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-teal-soft text-teal rounded-circle me-3">
                                                <i class="bi bi-person-circle fs-5"></i>
                                            </div>
                                            <div class="fw-bold small">My Profile</div>
                                        </div>
                                    </a>
                                </li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item mb-2" href="admin/superadmin.php">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box bg-terracotta-soft text-terracotta rounded-circle me-3">
                                                    <i class="bi bi-star fs-5"></i>
                                                </div>
                                                <div class="fw-bold small">Superadmin</div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'reviewer'])): ?>
                                    <li>
                                        <a class="dropdown-item mb-2" href="admin/queue.php">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box bg-sage-soft text-sage rounded-circle me-3">
                                                    <i class="bi bi-speedometer2 fs-5"></i>
                                                </div>
                                                <div class="fw-bold small">Review Queue</div>
                                            </div>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider opacity-50"></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-danger-subtle text-danger rounded-circle me-3">
                                                <i class="bi bi-box-arrow-left fs-5"></i>
                                            </div>
                                            <div class="fw-bold small">Logout</div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn-signin ms-lg-3" href="login.php">Sign In</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <div id="theme-toggle" class="theme-toggle" title="Toggle Dark/Light Mode">
                            <i class="bi bi-moon-stars-fill"></i>
                            <i class="bi bi-sun-fill"></i>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php
}

function render_footer($extra_scripts = "") {
?>
    <footer style="background-color: var(--navy); color: white; padding: 5rem 0 3rem;">
        <div class="container reveal">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="nav-logo-wrapper mb-4" style="background: transparent;">
                        <img src="logo.png" alt="Logo" style="filter: brightness(0) invert(1); height: 40px;">
                    </div>
                    <p class="small opacity-75 pe-lg-5">"Stories do more than inform. They connect. They reveal. They transform."</p>
                </div>
                <div class="col-lg-2 ms-auto">
                    <h5 class="fw-bold mb-4 playfair">Explore</h5>
                    <ul class="list-unstyled small opacity-75">
                        <li class="mb-2"><a href="archive.php" class="text-white text-decoration-none">Archive</a></li>
                        <li class="mb-2"><a href="map.php" class="text-white text-decoration-none">Map</a></li>
                        <li class="mb-2"><a href="gallery.php" class="text-white text-decoration-none">Gallery</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-4 playfair">Participate</h5>
                    <ul class="list-unstyled small opacity-75">
                        <li class="mb-2"><a href="submit.php" class="text-white text-decoration-none">Submit Story</a></li>
                        <li class="mb-2"><a href="register.php" class="text-white text-decoration-none">Join Collective</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-5 mb-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center small opacity-50">
                &copy; <?= date('Y') ?> Relational Lens. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js?v=1.2"></script>
    <?= $extra_scripts ?>
</body>
</html>
<?php
}
?>