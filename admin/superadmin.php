<?php
require_once '../config/db.php';
session_start();
require_once 'layout.php';

$message = "";
$messageType = "";

// Security Check: Superadmin only
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle Featured Story Toggle
if (isset($_POST['toggle_feature'])) {
    verify_csrf();
    $story_id = $_POST['story_id'];
    $currentFeaturedCount = $pdo->query("SELECT COUNT(*) FROM stories WHERE is_featured = 1 AND status = 'approved'")->fetchColumn();
    $isCurrentlyFeatured = $pdo->prepare("SELECT is_featured FROM stories WHERE id = ?");
    $isCurrentlyFeatured->execute([$story_id]);
    
    if ($isCurrentlyFeatured->fetchColumn()) {
        $pdo->prepare("UPDATE stories SET is_featured = 0 WHERE id = ?")->execute([$story_id]);
        $message = "Story removed from Featured section.";
        $messageType = "success";
    } else {
        if ($currentFeaturedCount >= 3) {
            $message = "You already have 3 featured stories. Unfeature one first.";
            $messageType = "warning";
        } else {
            $pdo->prepare("UPDATE stories SET is_featured = 1 WHERE id = ?")->execute([$story_id]);
            $message = "Story successfully added to Featured section!";
            $messageType = "success";
        }
    }
}

// Handle Deletion
if (isset($_GET['delete_id']) && isset($_GET['type'])) {
    verify_csrf();
    $delete_id = $_GET['delete_id'];
    $type = $_GET['type'];
    $table = ($type === 'article') ? 'articles' : 'stories';
    
    try {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = ucfirst($type) . " deleted permanently.";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error deleting: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Handle Quick Publication
if (isset($_POST['quick_pub'])) {
    verify_csrf();
    $title = $_POST['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $resource_url = $_POST['resource_url'];
    $category_id = $_POST['category_id'];
    $pub_type = $_POST['pub_type'];
    
    try {
        if ($pub_type === 'story') {
            $stmt = $pdo->prepare("INSERT INTO stories (title, slug, description, video_url, thumbnail_url, category_id, status, filmmaker_name, ethics_consent, approved_at) VALUES (?, ?, 'Direct admin upload', ?, ?, ?, 'approved', 'Relational Lens Admin', 1, NOW())");
            $stmt->execute([$title, $slug, $resource_url, $resource_url, $category_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, image_url, category_id, status) VALUES (?, ?, 'Quickly published via Admin', ?, ?, 'approved')");
            $stmt->execute([$title, $slug, $resource_url, $category_id]);
        }
        $message = "Content published successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Data
$approvedStories = $pdo->query("SELECT s.*, c.name as category_name FROM stories s LEFT JOIN categories c ON s.category_id = c.id WHERE s.status = 'approved' ORDER BY s.created_at DESC")->fetchAll();
$approvedArticles = $pdo->query("SELECT a.*, c.name as category_name FROM articles a LEFT JOIN categories c ON a.category_id = c.id WHERE a.status = 'approved' ORDER BY a.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

render_admin_header("System Controls", "settings");
?>

<style>
    .admin-card {
        background: var(--card-bg);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid var(--card-border);
        margin-bottom: 2rem;
    }

    .icon-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
    }
</style>
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <i class="bi <?= $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Content Management -->
    <div class="col-lg-8">
        <div class="admin-card">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="playfair fw-bold mb-0">Live Content Management</h4>
                <div class="d-flex gap-3">
                    <span class="badge bg-navy text-white px-3 py-2 rounded-pill small">
                        <i class="bi bi-collection-play-fill me-1"></i> <?= count($approvedStories) ?> Stories
                    </span>
                    <span class="badge bg-teal text-white px-3 py-2 rounded-pill small">
                        <i class="bi bi-journal-richtext me-1"></i> <?= count($approvedArticles) ?> Articles
                    </span>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="row g-3 mb-5 p-3 rounded-4" style="background: var(--bg-color);">
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted">Search Records</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 border-0 shadow-sm" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="contentSearch" class="form-control border-start-0 border-0 shadow-sm py-2" placeholder="Search by title or filmmaker..." style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter by Category</label>
                    <select id="categoryFilter" class="form-select border-0 shadow-sm py-2" style="border-radius: 12px;">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Stories Section -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-box bg-navy text-white rounded-3 me-3 p-2">
                        <i class="bi bi-collection-play fs-5"></i>
                    </div>
                    <h5 class="playfair fw-bold mb-0">Published Stories</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="storyTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>TITLE & DATE</th>
                                <th>CATEGORY</th>
                                <th class="text-center">FEATURED</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedStories as $story): ?>
                            <tr class="content-row" data-category="<?= htmlspecialchars($story['category_name']) ?>" data-search="<?= strtolower($story['title'] . ' ' . $story['filmmaker_name']) ?>">
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($story['title']) ?></div>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($story['created_at'])) ?></small>
                                </td>
                                <td><span class="badge bg-terracotta-soft text-terracotta px-3"><?= htmlspecialchars($story['category_name']) ?></span></td>
                                <td class="text-center">
                                    <form method="POST" style="display:inline;">
                                        <?php csrf_input(); ?>
                                        <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                        <button type="submit" name="toggle_feature" class="btn btn-sm <?= $story['is_featured'] ? 'btn-terracotta' : 'btn-outline-secondary' ?> rounded-pill px-3">
                                            <i class="bi <?= $story['is_featured'] ? 'bi-star-fill' : 'bi-star' ?> me-1"></i>
                                            <?= $story['is_featured'] ? 'Featured' : 'Promote' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="edit_story.php?id=<?= $story['id'] ?>" class="btn btn-sm btn-outline-navy border-0 rounded-circle" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <a href="superadmin.php?delete_id=<?= $story['id'] ?>&type=story&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="return confirm('Permanently delete this story?')" title="Delete"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="my-5 opacity-5">

            <!-- Articles Section -->
            <div>
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-box bg-teal text-white rounded-3 me-3 p-2">
                        <i class="bi bi-journal-richtext fs-5"></i>
                    </div>
                    <h5 class="playfair fw-bold mb-0">Gallery Articles</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="articleTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>TITLE & DATE</th>
                                <th>CATEGORY</th>
                                <th class="text-end">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedArticles as $article): ?>
                            <tr class="content-row" data-category="<?= htmlspecialchars($article['category_name'] ?: 'General') ?>" data-search="<?= strtolower($article['title']) ?>">
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($article['title']) ?></div>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($article['created_at'])) ?></small>
                                </td>
                                <td><span class="badge bg-teal-soft text-teal px-3"><?= htmlspecialchars($article['category_name'] ?: 'General') ?></span></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-sm btn-outline-teal border-0 rounded-circle" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <a href="superadmin.php?delete_id=<?= $article['id'] ?>&type=article&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="return confirm('Permanently delete this article?')" title="Delete"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contentSearch');
    const catFilter = document.getElementById('categoryFilter');
    
    function filterContent() {
        const searchTerm = searchInput.value.toLowerCase();
        const catTerm = catFilter.value;
        const rows = document.querySelectorAll('.content-row');

        rows.forEach(row => {
            const matchesSearch = row.dataset.search.includes(searchTerm);
            const matchesCat = catTerm === 'all' || row.dataset.category === catTerm;
            
            if (matchesSearch && matchesCat) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterContent);
    catFilter.addEventListener('change', filterContent);
});
</script>

    <!-- Right Column: Quick Entry -->
    <div class="col-lg-4">
        <div class="admin-card bg-navy text-white border-0 shadow-lg">
            <h4 class="playfair fw-bold mb-3 text-white">Direct Entry</h4>
            <p class="opacity-75 small mb-4">Fast-track high-priority content directly to the live site.</p>
            
            <form method="POST" action="superadmin.php">
                <?php csrf_input(); ?>
                <div class="mb-3">
                    <label class="form-label small text-white-50">Content Title</label>
                    <input type="text" name="title" class="form-control border-0 py-2" placeholder="Enter title..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-white-50">Resource URL</label>
                    <input type="url" name="resource_url" class="form-control border-0 py-2" placeholder="https://..." required>
                </div>
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label small text-white-50">Category</label>
                        <select name="category_id" class="form-select border-0 py-2" required>
                            <option value="">Select...</option>
                            <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-white-50">Type</label>
                        <select name="pub_type" class="form-select border-0 py-2" required>
                            <option value="story">Story</option>
                            <option value="article">Article</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="quick_pub" class="btn btn-terracotta w-100 py-3 fw-bold">
                    <i class="bi bi-lightning-charge-fill me-2"></i> Publish Now
                </button>
            </form>
        </div>

        <div class="admin-card">
            <h5 class="playfair fw-bold mb-4">Platform Stats</h5>
            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                <span class="text-muted small">Server Pulse</span>
                <span class="badge bg-success text-white px-3">Active</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                <span class="text-muted small">Content Flow</span>
                <span class="fw-bold small">Optimized</span>
            </div>
            <div class="alert alert-warning py-3 small mb-0 mt-3 border-0">
                <i class="bi bi-shield-exclamation me-2"></i> Integrity check recommended.
            </div>
        </div>
    </div>
</div>

<?php 
render_admin_footer(); 
?>