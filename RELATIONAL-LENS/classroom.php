<?php
session_start();
require_once 'config/db.php';

try {
    // Fetch all classrooms
    $stmt = $pdo->query("SELECT * FROM classrooms ORDER BY created_at DESC");
    $classrooms = $stmt->fetchAll();

    // For each classroom, fetch a few story thumbnails as a preview
    foreach ($classrooms as &$room) {
        $previewStmt = $pdo->prepare("
            SELECT s.thumbnail_url 
            FROM stories s
            JOIN story_classroom sc ON s.id = sc.story_id
            WHERE sc.classroom_id = ?
            LIMIT 3
        ");
        $previewStmt->execute([$room['id']]);
        $room['previews'] = $previewStmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $classrooms = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Classroom | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li class="nav-item"><a class="nav-link active" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
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

    <!-- Header -->
    <header class="classroom-header text-center reveal">
        <div class="container">
            <h1 class="display-4 mb-3 animate-up">Shared Classroom</h1>
            <p class="lead opacity-75 max-width-700 mx-auto animate-up" style="animation-delay: 0.1s;">
                Curated collections and discussion guides designed to bridge the gap between storytelling and social work pedagogy.
            </p>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <h2 class="playfair mb-4 reveal">Teaching Collections</h2>
                
                <?php if (empty($classrooms)): ?>
                    <div class="p-5 text-center bg-white rounded-4 shadow-sm reveal">
                        <i class="bi bi-book display-1 text-muted mb-3 d-block"></i>
                        <h3>Collections Coming Soon</h3>
                        <p class="text-muted">Our editorial team is currently curating new teaching modules. Check back soon for guides on Migration, Climate Justice, and more.</p>
                        <a href="archive.php" class="btn btn-terracotta mt-3">Browse All Stories</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4 reveal">
                        <?php foreach ($classrooms as $room): ?>
                            <div class="col-md-6">
                                <div class="classroom-card">
                                    <div class="classroom-previews">
                                        <?php if (!empty($room['previews'])): ?>
                                            <?php foreach ($room['previews'] as $thumb): ?>
                                                <img src="<?= htmlspecialchars($thumb) ?>" class="preview-img" alt="Story Preview">
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-sand text-navy">
                                                <i class="bi bi-collection-play fs-1"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-4 flex-grow-1">
                                        <h4 class="playfair"><?= htmlspecialchars($room['title']) ?></h4>
                                        <p class="small text-muted mb-4"><?= htmlspecialchars($room['description']) ?></p>
                                        <div class="d-flex gap-2">
                                            <a href="collection.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-terracotta">View Stories</a>
                                            <?php if ($room['guide_url']): ?>
                                                <a href="<?= htmlspecialchars($room['guide_url']) ?>" class="btn btn-sm btn-outline-teal" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i> Guide
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-card mb-4 reveal">
                    <h5 class="playfair text-sand mb-3">For Educators</h5>
                    <p class="small opacity-75">
                        These collections are specifically designed for BSW and MSW classrooms. Each one includes:
                    </p>
                    <ul class="list-unstyled small mb-4">
                        <li class="mb-2"><i class="bi bi-check2-circle text-sage me-2"></i> Peer-reviewed documentaries</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-sage me-2"></i> Structured discussion prompts</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-sage me-2"></i> Ethical reflection exercises</li>
                        <li class="mb-0"><i class="bi bi-check2-circle text-sage me-2"></i> Links to policy & research</li>
                    </ul>
                    <a href="#" class="btn btn-sm btn-terracotta w-100">Download Teaching Toolkit</a>
                </div>

                <div class="p-4 bg-white rounded-3 shadow-sm reveal">
                    <h5 class="playfair">Suggest a Collection</h5>
                    <p class="small text-muted">Are you teaching a specific topic? Help us curate a story set for your syllabus.</p>
                    <hr>
                    <a href="mailto:education@relational-lens.org" class="text-teal fw-bold text-decoration-none small">Get in Touch &rarr;</a>
                </div>
            </div>
        </div>
    </div>

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
                    <h5>Resources</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="#" class="nav-link">Ethics Guide</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Teaching Toolkit</a></li>
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
