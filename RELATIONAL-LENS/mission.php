<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission & Vision | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .mission-hero {
            position: relative;
            padding: 8rem 0;
            background: url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=2000&auto=format&fit=crop') center/cover no-repeat;
            color: white;
            text-align: center;
        }
        .mission-hero::before {
            content: '';
            position: absolute;
            top:0; left:0; right:0; bottom:0;
            background: rgba(14, 58, 71, 0.85);
        }
        .hero-content { position: relative; z-index: 1; max-width: 800px; margin: 0 auto; }
        .section-tag { color: var(--terracotta); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; margin-bottom: 1rem; display: block; }
        .philosophy-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            height: 100%;
            border-top: 5px solid var(--sage);
        }
        .philosophy-icon { font-size: 3rem; color: var(--sage); margin-bottom: 1.5rem; }
        .vision-statment {
            font-size: 2rem;
            color: var(--navy);
            font-style: italic;
            border-left: 8px solid var(--sand);
            padding-left: 2rem;
            margin: 3rem 0;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand animate-up" href="index.php">
                <span class="logo-icon"></span>
                RELATIONAL LENS
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link active" href="mission.php">Mission</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle btn-signin" href="#" role="button" data-bs-toggle="dropdown">
                                Hi, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <?php if (in_array($_SESSION['user_role'], ['admin', 'reviewer'])): ?>
                                    <li><a class="dropdown-item small" href="admin/queue.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item small text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn-signin ms-lg-3" href="login.php">Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="mission-hero reveal">
        <div class="container">
            <div class="hero-content">
                <span class="section-tag text-white opacity-75 animate-up">Our Purpose</span>
                <h1 class="display-3 playfair mb-4 animate-up" style="animation-delay: 0.1s;">A Form of Ethical Witnessing</h1>
                <p class="lead mb-0 animate-up" style="animation-delay: 0.2s;">Transforming how social work knowledge is created, shared, and mobilized through the power of global documentary storytelling.</p>
            </div>
        </div>
    </header>

    <!-- Mission Statement -->
    <section>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 reveal">
                    <span class="section-tag">The Mission</span>
                    <h2 class="display-5 mb-4">Humanizing complex systems, amplifying marginalized voices.</h2>
                    <p class="text-muted">
                        Relational Lens is a living archive of global social work practice. We believe that storytelling is not just a method of documentation, but a relational intervention that bridges borders and deepens empathy across cultures.
                    </p>
                    <p class="text-muted">
                        Every film in our repository is peer-reviewed by a collective of practitioners, educators, and community members to ensure it upholds the highest standards of dignity and accountability.
                    </p>
                </div>
                <div class="col-lg-5 offset-lg-1 reveal">
                    <div class="vision-statment animate-up" style="animation-delay: 0.3s;">
                        "Stories do more than inform. They connect. They reveal. They transform."
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Framework -->
    <section class="bg-white">
        <div class="container text-center reveal">
            <span class="section-tag">Our Framework</span>
            <h2 class="display-5 mb-5">The Relational Lens</h2>
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="philosophy-card reveal">
                        <i class="bi bi-eye philosophy-icon"></i>
                        <h4 class="mb-3">Ethical Witnessing</h4>
                        <p class="small text-muted">We see through a lens of accountability, ensuring that the act of filming is a collaborative process of shared dignity and informed consent.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="philosophy-card reveal" style="border-top-color: var(--terracotta);">
                        <i class="bi bi-people philosophy-icon" style="color: var(--terracotta);"></i>
                        <h4 class="mb-3">Relational Practice</h4>
                        <p class="small text-muted">Storytelling is an intervention. We prioritize stories made WITH communities, focusing on the relational bonds that sustain social change.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="philosophy-card reveal" style="border-top-color: var(--navy);">
                        <i class="bi bi-shield-check philosophy-icon" style="color: var(--navy);"></i>
                        <h4 class="mb-3">Social Intervention</h4>
                        <p class="small text-muted">Knowledge mobilization through film creates a shared site of resistance and healing, moving from individual narrative to systemic action.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commitment -->
    <section class="bg-navy text-white text-center reveal">
        <div class="container py-4">
            <h2 class="display-5 playfair mb-4">A bridge across borders.</h2>
            <p class="lead opacity-75 max-width-800 mx-auto mb-5">
                We are committed to decolonial practices, anti-racist pedagogy, and a global solidarity that recognizes social work as a site of both local resistance and universal human rights.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="archive.php" class="btn btn-terracotta btn-lg px-5 rounded-pill">Explore the Archive</a>
                <a href="register.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Join the Collective</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container reveal">
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white mb-3" href="index.php">
                        <span class="logo-icon bg-white"></span>
                        RELATIONAL LENS
                    </a>
                    <p class="small opacity-75">"Stories do more than inform. They connect. They reveal. They transform."</p>
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-2 ms-auto">
                    <h5>Participate</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="submit.php" class="nav-link">Submit Story</a></li>
                        <li class="nav-item"><a href="register.php" class="nav-link">Join Collective</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>About</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="mission.php" class="nav-link">Mission</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Team</a></li>
                    </ul>
                </div>
            </div>
            <hr class="opacity-25">
            <div class="text-center small opacity-50 mt-4">
                &copy; <?= date('Y') ?> Relational Lens. All rights reserved. | ♿ WCAG 2.1 AA Compliant
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
