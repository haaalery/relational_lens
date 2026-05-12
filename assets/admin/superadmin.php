    <?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: Superadmin privileges required.");
}

$message = "";
$messageType = "";

// 1. Handle Featured Story Toggle
if (isset($_POST['toggle_feature'])) {
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

// 2. Handle Direct Video Upload
if (isset($_POST['admin_upload'])) {
    $title = $_POST['title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $video_url = $_POST['video_url'];
    $category_id = $_POST['category_id'];
    $region_id = $_POST['region_id'];
    $thumbnail_url = $_POST['thumbnail_url'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO stories (title, slug, description, video_url, thumbnail_url, category_id, region_id, status, filmmaker_name, ethics_consent, approved_at) VALUES (?, ?, 'Direct admin upload', ?, ?, ?, ?, 'approved', 'Relational Lens Admin', 1, NOW())");
        $stmt->execute([$title, $slug, $video_url, $thumbnail_url, $category_id, $region_id]);
        $message = "Video directly uploaded and published to archive!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error uploading: " . $e->getMessage();
        $messageType = "danger";
    }
}

// 3. Handle Article/Insight Upload
if (isset($_POST['upload_article'])) {
    $title = $_POST['article_title'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $excerpt = $_POST['article_excerpt'];
    $image_url = $_POST['article_image'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $excerpt, $image_url]);
        $message = "Article published to Insights successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error publishing article: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Data for UI
$approvedStories = $pdo->query("SELECT s.*, c.name as category_name FROM stories s LEFT JOIN categories c ON s.category_id = c.id WHERE s.status = 'approved' ORDER BY s.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$regions = $pdo->query("SELECT * FROM regions")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Superadmin Dashboard | Relational Lens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #f5f5f7; }
        .admin-panel { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 2rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-navy mb-5 p-3">
        <div class="container">
            <a class="navbar-brand text-white" href="../index.php">&larr; Back to Website</a>
            <span class="text-white fw-bold">Superadmin Dashboard</span>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-panel h-100">
                    <h4 class="playfair mb-1">Manage Featured Stories</h4>
                    <p class="text-muted small mb-4">Select exactly 3 stories to feature on the homepage.</p>
                    
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th class="text-center">Featured Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedStories as $story): ?>
                            <tr>
                                <td><a href="../story.php?slug=<?= $story['slug'] ?>" target="_blank" class="text-dark fw-bold text-decoration-none"><?= htmlspecialchars($story['title']) ?></a></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($story['category_name']) ?></span></td>
                                <td class="text-center">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                        <?php if ($story['is_featured']): ?>
                                            <button type="submit" name="toggle_feature" class="btn btn-sm btn-terracotta w-100">★ Featured (Click to Remove)</button>
                                        <?php else: ?>
                                            <button type="submit" name="toggle_feature" class="btn btn-sm btn-outline-secondary w-100">Make Featured</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-5">
                
                <div class="admin-panel mb-4">
                    <h4 class="playfair mb-1">Upload Video to Archive</h4>
                    <p class="text-muted small mb-4">Bypass peer review and publish directly.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Video Title" required>
                        </div>
                        <div class="mb-3">
                            <input type="url" name="video_url" class="form-control form-control-sm" placeholder="Video URL (YouTube/Vimeo)" required>
                        </div>
                        <div class="mb-3">
                            <input type="url" name="thumbnail_url" class="form-control form-control-sm" placeholder="Thumbnail URL" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <option value="">Category...</option>
                                    <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <select name="region_id" class="form-select form-select-sm" required>
                                    <option value="">Region...</option>
                                    <?php foreach ($regions as $reg): ?><option value="<?= $reg['id'] ?>"><?= $reg['name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="admin_upload" class="btn btn-navy btn-sm w-100" style="background-color: var(--navy); color: white;">Publish Video</button>
                    </form>
                </div>

                <div class="admin-panel">
                    <h4 class="playfair mb-1">Publish Insight Article</h4>
                    <p class="text-muted small mb-4">Post an article to the Insights & Reflections section.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="article_title" class="form-control form-control-sm" placeholder="Article Title" required>
                        </div>
                        <div class="mb-3">
                            <input type="url" name="article_image" class="form-control form-control-sm" placeholder="Header Photo URL" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="article_excerpt" class="form-control form-control-sm" rows="3" placeholder="Short excerpt/description..." required></textarea>
                        </div>
                        <button type="submit" name="upload_article" class="btn btn-outline-teal btn-sm w-100">Publish Article</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 