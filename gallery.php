<?php
require_once 'header.php';

// Get filter parameters (matching archive.php naming where possible)
$search_query = $_GET['q'] ?? '';
$category_filter = $_GET['category'] ?? '';
$region_filter = $_GET['region'] ?? '';
$sort_by = $_GET['sort'] ?? 'newest';

// Initialize variables for the UI
$all_categories = [];
$all_regions = [];
$articles = [];

try {
    // 1. Fetch categories for the filter dropdown
    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $all_categories = $catStmt->fetchAll();

    // 2. Fetch Regions for filter dropdown (Articles link to regions via related_story_id)
    $regStmt = $pdo->query("SELECT * FROM regions ORDER BY name ASC");
    $all_regions = $regStmt->fetchAll();

    // 3. Build the query for articles
    $query = "
        SELECT a.*, u.name as author_name, c.name as category_name, c.icon as category_icon, c.slug as category_slug, r.name as region_name
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN stories s ON a.related_story_id = s.id
        LEFT JOIN regions r ON s.region_id = r.id
        WHERE a.status = 'approved'
    ";
    
    $params = [];

    if ($search_query !== '') {
        $query .= " AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)";
        $searchTerm = "%$search_query%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($category_filter !== '') {
        $query .= " AND c.slug = ?";
        $params[] = $category_filter;
    }

    if ($region_filter !== '') {
        $query .= " AND r.id = ?";
        $params[] = $region_filter;
    }

    // Sorting logic (matching archive.php)
    switch ($sort_by) {
        case 'oldest':
            $query .= " ORDER BY a.created_at ASC";
            break;
        case 'title':
            $query .= " ORDER BY a.title ASC";
            break;
        case 'newest':
        default:
            $query .= " ORDER BY a.created_at DESC";
            break;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
}

render_header("The Gallery", "gallery");
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">Scholarly Dialogue</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">The Gallery</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 800px; font-size: 1.4rem;">Critical scholarship and artistic reflections exploring the ethical dimensions of global social work.</p>
            
            <div class="animate-up">
                <a href="submit_article.php" class="btn btn-terracotta rounded-pill px-4 py-2">
                    <i class="bi bi-plus-circle me-2"></i> Submit an Article
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

    <!-- Filter & Search Section (Exact match for archive.php layout) -->
    <div class="container mb-5">
        <div class="filter-section animate-up">
            <form action="gallery.php" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-bold">Search Keywords</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. Ethics, Witnessing..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-bold">Grand Challenge</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $category_filter === $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
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
                        <option value="title" <?= $sort_by == 'title' ? 'selected' : '' ?>>Title A-Z</option>
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-navy text-white flex-grow-1 rounded-pill" style="background-color: var(--navy);">Filter Results</button>
                    <a href="gallery.php" class="btn btn-outline-secondary rounded-pill" title="Clear Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <main id="main-content" class="container py-5">
        <div class="row g-4">
            <?php if (empty($articles)): ?>
                <div class="col-12 text-center py-5 reveal">
                    <i class="bi bi-journal-richtext display-1 text-muted opacity-25 mb-4 d-block"></i>
                    <h3 class="playfair fw-bold text-muted">Awaiting Reflections</h3>
                    <p class="text-muted">The gallery is currently being curated. Check back soon for new scholarly insights.</p>
                    <a href="submit_article.php" class="btn btn-terracotta rounded-pill px-4 mt-3">Submit an Article</a>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $art): ?>
                    <div class="col-lg-4 col-md-6 reveal">
                        <div class="article-card h-100">
                            <div class="card-img-wrapper">
                                <?php if ($art['category_name']): ?>
                                    <span class="category-badge shadow-sm"><?= htmlspecialchars($art['category_name']) ?></span>
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($art['image_url'] ?: 'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($art['title']) ?>" loading="lazy">
                            </div>
                            <div class="article-body">
                                <div class="article-meta">
                                    <span><?= htmlspecialchars($art['author_name']) ?></span>
                                    <span class="mx-1">&bull;</span>
                                    <span><?= date('M d', strtotime($art['created_at'])) ?></span>
                                </div>
                                <h3 class="article-title"><?= htmlspecialchars($art['title']) ?></h3>
                                <p class="article-excerpt">
                                    <?= htmlspecialchars($art['excerpt']) ?>
                                </p>
                                <a href="article.php?slug=<?= $art['slug'] ?>" class="btn-read mt-auto">
                                    Read Reflection <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<?php render_footer(); ?>