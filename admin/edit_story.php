<?php
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
        $description = $_POST['description'];
        $transcript = $_POST['transcript'];
        $video_url = $_POST['video_url'];
        $thumbnail_url = $_POST['thumbnail_url'];
        $category_id = $_POST['category_id'];
        $region_id = $_POST['region_id'];
        $status = $_POST['status'];
        $language = $_POST['language'];
        $subtitles = isset($_POST['subtitles']) ? 1 : 0;
        $f_name = $_POST['f_name'];
        $f_email = $_POST['f_email'];
        $f_bio = $_POST['f_bio'];
        $community_credit = $_POST['community_credit'];
        $decolonial_tags = json_encode($_POST['decolonial_tags'] ?? []);

        $stmt = $pdo->prepare("UPDATE stories SET 
            title = ?, description = ?, transcript = ?, video_url = ?, 
            thumbnail_url = ?, category_id = ?, region_id = ?, 
            status = ?, language = ?, subtitles_available = ?, 
            filmmaker_name = ?, filmmaker_email = ?, filmmaker_bio = ?,
            community_credit = ?, decolonial_tags = ?, updated_at = NOW() 
            WHERE id = ?");
        
        $stmt->execute([
            $title, $description, $transcript, $video_url, 
            $thumbnail_url, $category_id, $region_id, 
            $status, $language, $subtitles,
            $f_name, $f_email, $f_bio,
            $community_credit, $decolonial_tags, $id
        ]);

        $message = "Story updated successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error updating story: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Story Data
try {
    $stmt = $pdo->prepare("SELECT * FROM stories WHERE id = ?");
    $stmt->execute([$id]);
    $story = $stmt->fetch();

    if (!$story) {
        header("Location: superadmin.php");
        exit;
    }

    $current_tags = json_decode($story['decolonial_tags'] ?? '[]', true) ?: [];

    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    $regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

render_admin_header("Edit Story", "settings");
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
                    <label class="form-label fw-bold">Story Title</label>
                    <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($story['title']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Description / Narrative</label>
                    <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($story['description']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Video Transcript</label>
                    <textarea name="transcript" class="form-control" rows="10"><?= htmlspecialchars($story['transcript']) ?></textarea>
                    <small class="text-muted">Useful for accessibility and SEO.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Community Credit & Benefit</label>
                    <textarea name="community_credit" class="form-control" rows="4"><?= htmlspecialchars($story['community_credit']) ?></textarea>
                    <small class="text-muted">How the community is involved and how they benefit.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold d-block">Decolonial Practice Tags</label>
                    <div class="row g-2">
                        <?php 
                        $available_tags = ["Relationality", "Place-based", "Ethics of Care", "Indigenous Wisdom", "Language Preservation", "Reciprocity"];
                        foreach ($available_tags as $tag): ?>
                            <div class="col-md-4">
                                <div class="form-check small">
                                    <input class="form-check-input" type="checkbox" name="decolonial_tags[]" value="<?= $tag ?>" id="tag_<?= $tag ?>" <?= in_array($tag, $current_tags) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tag_<?= $tag ?>"><?= $tag ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Metadata & Media -->
            <div class="col-lg-4">
                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Media Links</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Video URL (YouTube/Vimeo)</label>
                        <input type="url" name="video_url" class="form-control form-control-sm" value="<?= htmlspecialchars($story['video_url']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Thumbnail URL</label>
                        <input type="url" name="thumbnail_url" class="form-control form-control-sm" value="<?= htmlspecialchars($story['thumbnail_url']) ?>" required>
                    </div>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Classification</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category_id" class="form-select form-select-sm" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $story['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Region</label>
                        <select name="region_id" class="form-select form-select-sm" required>
                            <?php foreach ($regions as $reg): ?>
                                <option value="<?= $reg['id'] ?>" <?= $story['region_id'] == $reg['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($reg['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Filmmaker Attribution</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name</label>
                        <input type="text" name="f_name" class="form-control form-control-sm" value="<?= htmlspecialchars($story['filmmaker_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="f_email" class="form-control form-control-sm" value="<?= htmlspecialchars($story['filmmaker_email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bio</label>
                        <textarea name="f_bio" class="form-control form-control-sm" rows="3" required><?= htmlspecialchars($story['filmmaker_bio']) ?></textarea>
                    </div>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <h5 class="playfair mb-3">Settings & Status</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Publication Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="pending" <?= $story['status'] == 'pending' ? 'selected' : '' ?>>Pending Review</option>
                            <option value="under_review" <?= $story['status'] == 'under_review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="approved" <?= $story['status'] == 'approved' ? 'selected' : '' ?>>Approved (Live)</option>
                            <option value="rejected" <?= $story['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Primary Language</label>
                        <input type="text" name="language" class="form-control form-control-sm" value="<?= htmlspecialchars($story['language']) ?>">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="subtitles" id="subtitles" <?= $story['subtitles_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="subtitles">Subtitles Available</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-navy w-100 py-3 fw-bold">
                    <i class="bi bi-save me-2"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<?php render_admin_footer(); ?>