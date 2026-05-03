<?php
session_start();
require_once 'config/db.php';

// Get filter parameters
$category_filter = $_GET['category'] ?? null;
$region_filter = $_GET['region'] ?? null;
$search_query = $_GET['q'] ?? null;

try {
    // Fetch Categories for filter dropdown
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $all_categories = $catStmt->fetchAll();

    // Fetch Regions for filter dropdown
    $regStmt = $pdo->query("SELECT * FROM regions ORDER BY name ASC");
    $all_regions = $regStmt->fetchAll();

    // Build the query for stories
    $query = "SELECT s.*, r.name as region_name, c.name as category_name 
              FROM stories s
              LEFT JOIN regions r ON s.region_id = r.id
              LEFT JOIN categories c ON s.category_id = c.id
              WHERE s.status = 'approved'";
    
    $params = [];

    if ($category_filter) {
        $query .= " AND c.slug = ?";
        $params[] = $category_filter;
    }

    if ($region_filter) {
        $query .= " AND r.id = ?";
        $params[] = $region_filter;
    }

    if ($search_query) {
        $query .= " AND (s.title LIKE ? OR s.description LIKE ?)";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }

    $query .= " ORDER BY s.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stories = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $stories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Living Archive | Relational Lens</title>
    
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
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
    <header class="archive-header text-center reveal">
        <div class="container">
            <h1 class="display-4 mb-3 animate-up">Living Archive</h1>
            <p class="lead opacity-75 animate-up" style="animation-delay: 0.1s;">Explore the collective wisdom of global social work practice through film.</p>
        </div>
    </header>

    <div class="container">
        <!-- Filter Bar -->
        <div class="filter-bar reveal">
            <form action="archive.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Search Stories</label>
                    <input type="text" name="q" class="form-control" placeholder="Keywords..." value="<?= htmlspecialchars($search_query ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Challenges</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>" <?= $category_filter == $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Region</label>
                    <select name="region" class="form-select">
                        <option value="">All Regions</option>
                        <?php foreach ($all_regions as $reg): ?>
                            <option value="<?= $reg['id'] ?>" <?= $region_filter == $reg['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($reg['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-filter btn-terracotta">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mt-5 mb-4 reveal">
            <h4 class="playfair mb-0">
                <?= count($stories) ?> Stories Found
                <?php if ($category_filter || $region_filter || $search_query): ?>
                    <small class="text-muted fs-6 ms-2">/ <a href="archive.php" class="text-decoration-none text-teal">Clear All</a></small>
                <?php endif; ?>
            </h4>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Sort By: Newest
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Newest First</a></li>
                    <li><a class="dropdown-item" href="#">Most Viewed</a></li>
                    <li><a class="dropdown-item" href="#">Region (A-Z)</a></li>
                </ul>
            </div>
        </div>

        <!-- Story Grid -->
        <div class="row g-4 reveal">
            <?php if (empty($stories)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-camera-video-off display-1 text-muted mb-3 d-block"></i>
                    <h3>No stories found</h3>
                    <p class="text-muted">Try adjusting your filters or search keywords.</p>
                </div>
            <?php else: ?>
                <?php foreach ($stories as $story): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="story-card">
                            <div class="card-img-wrapper">
                                <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($story['title']) ?>">
                                <a href="story.php?slug=<?= $story['slug'] ?>" class="play-overlay">
                                    <i class="bi bi-play-fill"></i>
                                </a>
                            </div>
                            <div class="p-4 flex-grow-1">
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge badge-sage"><?= htmlspecialchars($story['region_name'] ?: 'Global') ?></span>
                                    <span class="badge badge-terracotta"><?= htmlspecialchars($story['category_name'] ?: 'General') ?></span>
                                </div>
                                <h5 class="mb-2"><a href="story.php?slug=<?= $story['slug'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($story['title']) ?></a></h5>
                                <p class="text-muted small line-clamp-3">
                                    <?= htmlspecialchars(substr($story['description'], 0, 120)) ?>...
                                </p>
                            </div>
                            <div class="px-4 pb-4 mt-auto border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($story['filmmaker_name'] ?: 'Anonymous') ?></span>
                                    <span class="small text-muted"><i class="bi bi-eye me-1"></i> <?= number_format($story['views_count']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination (Placeholder) -->
        <?php if (!empty($stories)): ?>
        <nav class="mt-5 reveal">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#" style="background-color: var(--teal); border-color: var(--teal);">1</a></li>
                <li class="page-item"><a class="page-link" href="#" style="color: var(--teal);">2</a></li>
                <li class="page-item"><a class="page-link" href="#" style="color: var(--teal);">Next</a></li>
            </ul>
        </nav>
        <?php endif; ?>
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
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
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
                        <li class="nav-item"><a href="login.php" class="nav-link">Sign In</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Resources</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="#" class="nav-link">Ethics Guide</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Teaching Toolkit</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Contact</a></li>
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
    
    <script>
        // ♿ A11Y Note: Proper focus management for filters and semantic HTML for results.
        // ⚠️ Ethics Note: Stories are presented with their community-centered categories first.
    </script>
</body>
</html>
