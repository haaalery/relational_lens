<?php
require_once 'header.php';

// Get filter parameters
$category_filter = $_GET['category'] ?? null;
$region_filter = $_GET['region'] ?? null;
$search_query = $_GET['q'] ?? null;
$sort_by = $_GET['sort'] ?? 'newest';

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
        $query .= " AND (s.title LIKE ? OR s.description LIKE ? OR s.decolonial_tags LIKE ?)";
        $searchTerm = "%$search_query%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Sorting logic
    switch ($sort_by) {
        case 'oldest':
            $query .= " ORDER BY s.created_at ASC";
            break;
        case 'views':
            $query .= " ORDER BY s.views_count DESC";
            break;
        case 'title':
            $query .= " ORDER BY s.title ASC";
            break;
        case 'newest':
        default:
            $query .= " ORDER BY s.created_at DESC";
            break;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stories = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $stories = [];
}

render_header("Living Archive", "archive");
?>

    <!-- Hero Section -->
    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">Ethical Witnessing</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">The Living Archive</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 800px; font-size: 1.4rem;">Explore a global collection of documentary stories, honoring local wisdom and relational practice.</p>
            
            <div class="animate-up mt-4">
                <a href="submit.php" class="btn btn-terracotta rounded-pill px-4 py-2">
                    <i class="bi bi-plus-circle me-2"></i> Submit a Story
                </a>
            </div>
        </div>
    </header>

    <!-- Section Divider -->
    <div class="section-divider reveal">
        <div class="divider-line"></div>
        <div class="divider-icon">
            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
            <i class="bi bi-circle-fill"></i>
            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
        </div>
        <div class="divider-line"></div>
    </div>

    <!-- Filter & Search Section -->
    <div class="container mb-5">
        <div class="filter-section animate-up">
            <form action="archive.php" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-bold">Search Keywords</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. Healing, Land..." value="<?= htmlspecialchars($search_query ?? '') ?>">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-bold">Grand Challenge</label>
                    <select name="category" class="form-select">
                        <option value="">All Challenges</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>" <?= $category_filter == $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-bold">Region</label>
                    <select name="region" class="form-select">
                        <option value="">All Regions</option>
                        <?php foreach ($all_regions as $reg): ?>
                            <option value="<?= $reg['id'] ?>" <?= $region_filter == $reg['id'] ? 'selected' : '' ?>><?= htmlspecialchars($reg['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-bold">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="newest" <?= $sort_by == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort_by == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="views" <?= $sort_by == 'views' ? 'selected' : '' ?>>Most Viewed</option>
                        <option value="title" <?= $sort_by == 'title' ? 'selected' : '' ?>>Title A-Z</option>
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-navy text-white flex-grow-1 rounded-pill" style="background-color: var(--navy);">Filter Results</button>
                    <a href="archive.php" class="btn btn-outline-secondary rounded-pill" title="Clear Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Grid -->
    <main class="container py-5">
        <div class="row g-4 reveal">
            <?php if (empty($stories)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted opacity-25 mb-4"></i>
                    <h3 class="playfair">No stories found</h3>
                    <p class="text-muted">Try adjusting your filters or search keywords.</p>
                </div>
            <?php else: ?>
                <?php foreach ($stories as $story): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="story-card h-100">
                            <div class="card-img-wrapper">
                                <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" alt="Story Thumbnail" loading="lazy">
                                <a href="story.php?slug=<?= $story['slug'] ?>" class="play-overlay">
                                    <i class="bi bi-play-circle"></i>
                                </a>
                            </div>
                            <div class="p-4 flex-grow-1 d-flex flex-column">
                                <div class="article-meta mb-2">
                                    <span><?= htmlspecialchars($story['region_name'] ?: 'Global') ?></span>
                                    <span class="mx-1">&bull;</span>
                                    <span><?= htmlspecialchars($story['category_name'] ?: 'Documentary') ?></span>
                                </div>
                                <h4 class="story-title">
                                    <a href="story.php?slug=<?= $story['slug'] ?>" class="text-decoration-none text-navy">
                                        <?= htmlspecialchars($story['title']) ?>
                                    </a>
                                </h4>
                                <p class="story-description"><?= htmlspecialchars(substr($story['description'], 0, 140)) ?>...</p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><i class="bi bi-eye me-1"></i> <?= number_format($story['views_count']) ?> views</small>
                                    <a href="story.php?slug=<?= $story['slug'] ?>" class="btn-read">Watch Story <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<?php render_footer(); ?>