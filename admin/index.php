<?php
require_once 'auth_check.php';
require_once '../config/db.php';
require_once 'layout.php';

// Fetch Basic Stats
try {
    $pendingStories = $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'pending'")->fetchColumn();
    $pendingArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'pending'")->fetchColumn();
    $totalApproved = $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'approved'")->fetchColumn();
    $totalArticles = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'approved'")->fetchColumn();
    
    // Latest Activity (Approved items)
    $latestStories = $pdo->query("SELECT title, created_at, 'story' as type FROM stories WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $latestArticles = $pdo->query("SELECT title, created_at, 'article' as type FROM articles WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    $activity = array_merge($latestStories, $latestArticles);
    usort($activity, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $activity = array_slice($activity, 0, 5);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $pendingStories = 0;
    $pendingArticles = 0;
    $totalApproved = 0;
    $totalArticles = 0;
    $activity = [];
}

render_admin_header("Admin Dashboard", "dashboard");
?>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card bg-navy">
            <div class="label">Pending Stories</div>
            <h2 class="mb-0"><?= $pendingStories ?></h2>
            <i class="bi bi-collection-play position-absolute opacity-25" style="font-size: 5rem; bottom: -10px; right: -10px;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-terracotta">
            <div class="label">Pending Articles</div>
            <h2 class="mb-0"><?= $pendingArticles ?></h2>
            <i class="bi bi-journal-richtext position-absolute opacity-25" style="font-size: 5rem; bottom: -10px; right: -10px;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-sage">
            <div class="label">Approved Videos</div>
            <h2 class="mb-0"><?= $totalApproved ?></h2>
            <i class="bi bi-check-circle position-absolute opacity-25" style="font-size: 5rem; bottom: -10px; right: -10px;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-teal">
            <div class="label">Total Articles</div>
            <h2 class="mb-0"><?= $totalArticles ?></h2>
            <i class="bi bi-journal-text position-absolute opacity-25" style="font-size: 5rem; bottom: -10px; right: -10px;"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card">
            <h4 class="playfair fw-bold mb-4">Recent Activity</h4>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Content Title</th>
                            <th>Type</th>
                            <th>Date Published</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activity)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted italic">No recent activity detected.</td></tr>
                        <?php else: ?>
                            <?php foreach ($activity as $item): ?>
                                <tr>
                                    <td><div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div></td>
                                    <td><span class="badge bg-light text-dark px-3"><?= ucfirst($item['type']) ?></span></td>
                                    <td><small class="text-muted"><?= date('M d, Y', strtotime($item['created_at'])) ?></small></td>
                                    <td class="text-end"><span class="badge status-approved">Live</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="admin-card">
            <h4 class="playfair fw-bold mb-4">Quick Navigation</h4>
            <div class="d-grid gap-3">
                <a href="queue.php" class="btn btn-outline-navy text-start p-3 d-flex align-items-center justify-content-between rounded-4">
                    <span><i class="bi bi-clock-history me-3"></i> Review Submissions</span>
                    <i class="bi bi-chevron-right small opacity-50"></i>
                </a>
                <a href="superadmin.php" class="btn btn-outline-navy text-start p-3 d-flex align-items-center justify-content-between rounded-4">
                    <span><i class="bi bi-shield-lock me-3"></i> System Controls</span>
                    <i class="bi bi-chevron-right small opacity-50"></i>
                </a>
                <a href="users.php" class="btn btn-outline-navy text-start p-3 d-flex align-items-center justify-content-between rounded-4">
                    <span><i class="bi bi-people me-3"></i> User Directory</span>
                    <i class="bi bi-chevron-right small opacity-50"></i>
                </a>
                <hr class="my-1">
                <a href="../submit.php" class="btn btn-terracotta p-3 rounded-4 fw-bold">
                    <i class="bi bi-plus-circle me-2"></i> Submit New Story
                </a>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>