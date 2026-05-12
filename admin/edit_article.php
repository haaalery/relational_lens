<?php
require_once 'auth_check.php';
require_once '../config/db.php';
session_start();
require_once 'layout.php';

// Check permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'reviewer'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: superadmin.php");
    exit;
}

$message = "";
$messageType = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $title = $_POST['title'];
        $excerpt = $_POST['excerpt'];
        $content = $_POST['content'];
        $image_url = $_POST['image_url'];
        $category_id = $_POST['category_id'] ?: null;
        $related_story_id = $_POST['related_story_id'] ?: null;
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE articles SET 
            title = ?, excerpt = ?, content = ?, image_url = ?, 
            category_id = ?, related_story_id = ?, status = ?, updated_at = NOW() 
            WHERE id = ?");
        
        $stmt->execute([
            $title, $excerpt, $content, $image_url, 
            $category_id, $related_story_id, $status, $id
        ]);

        $message = "Article updated successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error updating article: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Article Data
try {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    if (!$article) {
        header("Location: superadmin.php");
        exit;
    }

    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    $stories = $pdo->query("SELECT id, title FROM stories WHERE status = 'approved' ORDER BY title ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

render_admin_header("Edit Article", "settings");
?>

<div class="mb-4">
    <a href="superadmin.php" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Back to Settings
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-card">
    <form method="POST">
        <?php csrf_input(); ?>
        <div class="row g-4">
            <!-- Left Column: Primary Content -->
            <div class="col-lg-8">
                <div class="mb-4">
                    <label class="form-label fw-bold">Article Title</label>
                    <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($article['title']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3" required><?= htmlspecialchars($article['excerpt']) ?></textarea>
                    <small class="text-muted">A short summary displayed in the gallery.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Main Content (HTML Supported)</label>
                    <textarea name="content" class="form-control" rows="15" required><?= htmlspecialchars($article['content']) ?></textarea>
                </div>
            </div>

            <!-- Right Column: Metadata & Media -->
            <div class="col-lg-4">
                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Media Links</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Header Image URL</label>
                        <input type="url" name="image_url" class="form-control form-control-sm" value="<?= htmlspecialchars($article['image_url']) ?>" required>
                    </div>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Classification</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Related Documentary</label>
                        <select name="related_story_id" class="form-select form-select-sm">
                            <option value="">None (Standalone Article)</option>
                            <?php foreach ($stories as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $article['related_story_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Settings & Status</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Publication Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="pending" <?= $article['status'] == 'pending' ? 'selected' : '' ?>>Pending Review</option>
                            <option value="approved" <?= $article['status'] == 'approved' ? 'selected' : '' ?>>Approved (Live)</option>
                            <option value="rejected" <?= $article['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-teal w-100 py-3 fw-bold">
                    <i class="bi bi-save me-2"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<?php render_admin_footer(); ?>