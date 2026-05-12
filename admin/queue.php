<?php
require_once '../config/db.php';
require_once '../config/security.php';
session_start();
require_once 'layout.php';

$message = "";
$messageType = "";

// Handle Actions (Approve/Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    verify_csrf();
    $action = $_GET['action'];
    $item_id = $_GET['id'];
    $item_type = $_GET['type'] ?? 'story'; 
    $user_id = $_SESSION['user_id'];
    $table = ($item_type === 'article') ? 'articles' : 'stories';

    try {
        if ($action === 'approve') {
            if ($item_type === 'story') {
                $sql = "UPDATE stories SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id, $item_id]);
            } else {
                $sql = "UPDATE articles SET status = 'approved', updated_at = NOW() WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$item_id]);
            }
            
            $message = ucfirst($item_type) . " approved and published successfully.";
            $messageType = "success";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$item_id]);
            
            $message = ucfirst($item_type) . " has been rejected.";
            $messageType = "warning";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Pending Stories
try {
    $stmt = $pdo->query("
        SELECT s.*, r.name as region_name, c.name as category_name 
        FROM stories s
        LEFT JOIN regions r ON s.region_id = r.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE s.status IN ('pending', 'under_review')
        ORDER BY s.created_at DESC
    ");
    $storyQueue = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $storyQueue = [];
}

// Fetch Pending Articles
try {
    $stmt = $pdo->query("
        SELECT a.*, u.name as author_name, c.name as category_name 
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status IN ('pending')
        ORDER BY a.created_at DESC
    ");
    $articleQueue = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $articleQueue = [];
}

render_admin_header("Review Queue", "queue");
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <i class="bi <?= $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Stories Queue -->
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="playfair fw-bold mb-1">Story Submissions</h4>
                    <p class="text-muted small mb-0">Review documentaries awaiting ethical clearance.</p>
                </div>
                <span class="badge bg-navy text-white px-3"><?= count($storyQueue) ?> Pending</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title & Date</th>
                            <th>Contributor</th>
                            <th>Context</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Moderation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($storyQueue)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted italic">The story queue is currently empty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($storyQueue as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($item['created_at'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($item['filmmaker_name']) ?></td>
                                    <td>
                                        <span class="badge status-under_review small me-1"><?= htmlspecialchars($item['region_name']) ?></span>
                                        <span class="badge bg-light text-muted small"><?= htmlspecialchars($item['category_name']) ?></span>
                                    </td>
                                    <td class="text-center"><span class="badge status-<?= $item['status'] ?>"><?= ucfirst(str_replace('_', ' ', $item['status'])) ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="../story.php?slug=<?= $item['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-eye-fill"></i></a>
                                            <a href="queue.php?action=approve&id=<?= $item['id'] ?>&type=story&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-success border-0"><i class="bi bi-check-circle-fill"></i></a>
                                            <a href="queue.php?action=reject&id=<?= $item['id'] ?>&type=story&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Reject this story?')"><i class="bi bi-x-circle-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Articles Queue -->
    <div class="col-12">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h4 class="playfair fw-bold mb-1">Gallery Reflections</h4>
                    <p class="text-muted small mb-0">Scholarly articles awaiting peer review.</p>
                </div>
                <span class="badge bg-teal text-white px-3"><?= count($articleQueue) ?> Pending</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Article Title</th>
                            <th>Author</th>
                            <th>Classification</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Moderation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($articleQueue)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted italic">The article queue is currently empty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($articleQueue as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($item['created_at'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($item['author_name']) ?></td>
                                    <td><span class="badge bg-light text-teal"><?= htmlspecialchars($item['category_name']) ?></span></td>
                                    <td class="text-center"><span class="badge status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="../article.php?slug=<?= $item['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-eye-fill"></i></a>
                                            <a href="queue.php?action=approve&id=<?= $item['id'] ?>&type=article&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-success border-0"><i class="bi bi-check-circle-fill"></i></a>
                                            <a href="queue.php?action=reject&id=<?= $item['id'] ?>&type=article&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Reject this article?')"><i class="bi bi-x-circle-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>