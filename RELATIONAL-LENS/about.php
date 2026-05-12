<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .about-hero {
            background-color: var(--navy);
            color: white;
            padding: 8rem 0;
            text-align: center;
        }
        .team-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
            transition: var(--transition);
        }
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
        }
        .team-avatar {
            width: 120px;
            height: 120px;
            background-color: var(--sand);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--navy);
        }
        .board-card {
            background: var(--offwhite);
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 4px solid var(--sage);
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
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
    <header class="about-hero reveal">
        <div class="container">
            <h1 class="display-3 playfair mb-3 animate-up">Built by practitioners.</h1>
            <p class="lead opacity-75 animate-up" style="animation-delay: 0.1s;">A global collective committed to ethical filmmaking and social justice.</p>
        </div>
    </header>

    <!-- The Story -->
    <section>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 reveal">
                    <h2 class="section-title">The Relational Lens Story</h2>
                    <p class="text-muted">
                        Relational Lens began as a small project among social work educators who realized that traditional academic text often failed to capture the profound, relational nuances of practice. We saw a need for a medium that could humanize complex systems and amplify the voices of the communities we serve.
                    </p>
                    <p class="text-muted">
                        In 2023, we launched this platform to bridge the gap between storytelling and intervention. Today, we host hundreds of peer-reviewed documentaries from over 20 countries, creating a site of shared resistance, healing, and knowledge mobilization.
                    </p>
                </div>
                <div class="col-lg-6 reveal">
                    <div class="ps-lg-5">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2071&auto=format&fit=crop" class="img-fluid rounded-4 shadow-lg" alt="The Collective">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Team -->
    <section class="bg-white">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <h2 class="display-5 playfair">The Core Collective</h2>
                <p class="text-muted">Meet the designers and practitioners guiding the platform.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div class="team-card">
                        <div class="team-avatar"><i class="bi bi-person"></i></div>
                        <h4 class="playfair">Elena Rodriguez</h4>
                        <p class="text-terracotta small fw-bold mb-3">EXECUTIVE DIRECTOR</p>
                        <p class="small text-muted mb-0">MSW with 15 years experience in visual sociology and community-led storytelling.</p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="team-card">
                        <div class="team-avatar" style="background-color: var(--sage); color: white;"><i class="bi bi-person"></i></div>
                        <h4 class="playfair">Dr. Samuel Chen</h4>
                        <p class="text-terracotta small fw-bold mb-3">HEAD OF RESEARCH</p>
                        <p class="small text-muted mb-0">Leading our peer-review framework and ethical witnessing standards across the globe.</p>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="team-card">
                        <div class="team-avatar" style="background-color: var(--teal); color: white;"><i class="bi bi-person"></i></div>
                        <h4 class="playfair">Aisha Bello</h4>
                        <p class="text-terracotta small fw-bold mb-3">CURRICULUM LEAD</p>
                        <p class="small text-muted mb-0">Bridging the archive with BSW/MSW classrooms through curated teaching toolkits.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Advisory Board -->
    <section>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 reveal">
                    <h2 class="playfair display-6 mb-4">Advisory Board</h2>
                    <p class="text-muted">Our board ensures every story remains grounded in dignity, accountability, and a commitment to justice.</p>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6 reveal">
                            <div class="board-card">
                                <h6 class="mb-1 fw-bold">International Ethics Committee</h6>
                                <p class="small text-muted mb-0">Overseeing informed consent protocols for vulnerable populations.</p>
                            </div>
                        </div>
                        <div class="col-md-6 reveal">
                            <div class="board-card">
                                <h6 class="mb-1 fw-bold">Global Practitioners Network</h6>
                                <p class="small text-muted mb-0">Ensuring relevance to front-line social work interventions.</p>
                            </div>
                        </div>
                        <div class="col-md-6 reveal">
                            <div class="board-card">
                                <h6 class="mb-1 fw-bold">Indigenous Media Collective</h6>
                                <p class="small text-muted mb-0">Guiding our decolonial tags and representation frameworks.</p>
                            </div>
                        </div>
                        <div class="col-md-6 reveal">
                            <div class="board-card">
                                <h6 class="mb-1 fw-bold">Student Advisory Panel</h6>
                                <p class="small text-muted mb-0">Highlighting the next generation of social work documentarians.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join CTA -->
    <section class="bg-navy text-white text-center">
        <div class="container py-4 reveal">
            <h2 class="display-5 playfair mb-4">Become part of the story.</h2>
            <p class="lead opacity-75 max-width-700 mx-auto mb-5">
                Whether you're a filmmaker, a practitioner, or an educator, your voice is needed in this collective.
            </p>
            <a href="register.php" class="btn btn-terracotta btn-lg rounded-pill px-5">Join the Collective</a>
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
                </div>
                <div class="col-md-2 ms-auto">
                    <h5>Explore</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="archive.php" class="nav-link">Archive</a></li>
                        <li class="nav-item"><a href="map.php" class="nav-link">Map</a></li>
                        <li class="nav-item"><a href="classroom.php" class="nav-link">Classroom</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Participate</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="submit.php" class="nav-link">Submit Story</a></li>
                        <li class="nav-item"><a href="register.php" class="nav-link">Join Collective</a></li>
                    </ul>
                </div>
            </div>
            <hr class="opacity-25">
            <div class="text-center small opacity-50 mt-4">
                &copy; <?= date('Y') ?> Relational Lens. | ♿ WCAG 2.1 AA Compliant
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
