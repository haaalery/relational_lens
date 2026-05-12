<?php
session_start();
require_once 'config/db.php';

$collection_id = $_GET['id'] ?? null;

if (!$collection_id) {
    header("Location: gallery.php");
    exit;
}

try {
    // Fetch collection details (Note: uses the old 'classrooms' table for now until migrated)
    $stmt = $pdo->prepare("SELECT * FROM classrooms WHERE id = ?");
    $stmt->execute([$collection_id]);
    $classroom = $stmt->fetch();

    if (!$classroom) {
        header("Location: gallery.php");
        exit;
    }

    // Fetch stories in this collection
    $storiesStmt = $pdo->prepare("
        SELECT s.*, r.name as region_name, c.name as category_name
        FROM stories s
        JOIN story_classroom sc ON s.id = sc.story_id
        LEFT JOIN regions r ON s.region_id = r.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE sc.classroom_id = ?
        ORDER BY sc.sort_order ASC
    ");
    $storiesStmt->execute([$collection_id]);
    $stories = $storiesStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("A system error occurred.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($classroom['title']) ?> | The Gallery</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .collection-hero {
            background-color: var(--navy);
            color: white;
            padding: 8rem 0 6rem;
        }
        .story-list-item {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
            transition: var(--transition);
        }
        .story-list-item:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }
        .story-list-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link active" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle btn-signin" href="#" role="button" data-bs-toggle="dropdown">
                                Hi, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li><a class="dropdown-item small" href="profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="bi bi-person-circle me-2"></i> My Profile</a></li>
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

    <header class="collection-hero reveal">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="gallery.php" class="text-white opacity-75">Gallery</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Collection</li>
                </ol>
            </nav>
            <h1 class="display-3 playfair mb-3 animate-up"><?= htmlspecialchars($classroom['title']) ?></h1>
            <p class="lead opacity-75 max-width-700 animate-up" style="animation-delay: 0.1s;">
                <?= htmlspecialchars($classroom['description']) ?>
            </p>
            <?php if ($classroom['guide_url']): ?>
                <a href="<?= htmlspecialchars($classroom['guide_url']) ?>" class="btn btn-terracotta btn-lg rounded-pill px-4 mt-4 animate-up" style="animation-delay: 0.2s;" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Download Discussion Guide
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-9 mx-auto">
                <?php if (empty($stories)): ?>
                    <div class="text-center py-5 reveal">
                        <i class="bi bi-collection-play display-1 text-muted mb-3"></i>
                        <p class="text-muted">No stories have been added to this collection yet.</p>
                        <a href="gallery.php" class="btn btn-outline-teal mt-3">&larr; Back to Gallery</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($stories as $story): ?>
                        <div class="story-list-item reveal">
                            <div class="row g-0 align-items-center">
                                <div class="col-md-4">
                                    <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" class="story-list-img" alt="<?= htmlspecialchars($story['title']) ?>">
                                </div>
                                <div class="col-md-8">
                                    <div class="p-4">
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="small text-sage fw-bold text-uppercase"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($story['region_name'] ?: 'Global') ?></span>
                                            <span class="small text-terracotta fw-bold text-uppercase"><?= htmlspecialchars($story['category_name'] ?: 'General') ?></span>
                                        </div>
                                        <h3 class="playfair mb-3"><?= htmlspecialchars($story['title']) ?></h3>
                                        <p class="text-muted small mb-4 line-clamp-2"><?= htmlspecialchars($story['description']) ?></p>
                                        <a href="story.php?slug=<?= $story['slug'] ?>" class="btn btn-navy text-white rounded-pill px-4" style="background-color: var(--navy);">Watch Story</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer style="background-color: var(--navy); color: white; padding: 4rem 0;">
        <div class="container reveal">
            <div class="text-center small opacity-50">
                &copy; <?= date('Y') ?> Relational Lens. | ♿ The Gallery
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>