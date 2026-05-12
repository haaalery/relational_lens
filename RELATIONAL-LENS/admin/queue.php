<?php
require_once '../config/db.php';
session_start();

// Basic Security Check: Only admins or reviewers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'reviewer'])) {
    header("Location: ../login.php");
    exit;
}

$message = "";
$messageType = "";

// Handle Actions (Approve/Reject/Under Review)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $story_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE stories SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?");
            $stmt->execute([$user_id, $story_id]);
            
            // Also update the review record
            $revStmt = $pdo->prepare("UPDATE reviews SET decision = 'approved', reviewed_at = NOW() WHERE story_id = ? AND reviewer_id = ?");
            $revStmt->execute([$story_id, $user_id]);
            
            $message = "Story approved and published successfully.";
            $messageType = "success";
        } elseif ($action === 'review') {
            $stmt = $pdo->prepare("UPDATE stories SET status = 'under_review' WHERE id = ?");
            $stmt->execute([$story_id]);
            
            // Create review record if it doesn't exist
            $checkRev = $pdo->prepare("SELECT id FROM reviews WHERE story_id = ? AND reviewer_id = ?");
            $checkRev->execute([$story_id, $user_id]);
            if (!$checkRev->fetch()) {
                $revStmt = $pdo->prepare("INSERT INTO reviews (story_id, reviewer_id, decision) VALUES (?, ?, 'pending')");
                $revStmt->execute([$story_id, $user_id]);
            }
            
            $message = "Story marked as 'Under Review'.";
            $messageType = "info";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE stories SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$story_id]);
            
            $message = "Story has been rejected.";
            $messageType = "warning";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Fetch Pending and Under Review Stories
try {
    $stmt = $pdo->query("
        SELECT s.*, r.name as region_name, c.name as category_name 
        FROM stories s
        LEFT JOIN regions r ON s.region_id = r.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE s.status IN ('pending', 'under_review', 'rejected')
        ORDER BY s.created_at DESC
    ");
    $queue = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $queue = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review Queue | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --navy:       #0E3A47;
            --teal:       #1E5B6A;
            --sage:       #4B8759;
            --sand:       #E5CBAA;
            --terracotta: #C57D54;
            --offwhite:   #F5F5F7;
            --charcoal:   #2C2C2C;
            --muted:      #6B7280;
        }

        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: var(--charcoal); }
        .admin-sidebar { background-color: var(--navy); min-height: 100vh; color: white; padding: 2rem 1rem; }
        .admin-sidebar .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; border-radius: 8px; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        
        .main-content { padding: 3rem; }
        .card-table { background: white; border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table thead th { background-color: #f8f9fa; color: var(--muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; border: none; padding: 1.2rem; }
        .table tbody td { padding: 1.2rem; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }

        .status-badge { padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-review { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .btn-action { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; margin-right: 5px; }
        .btn-approve { background-color: var(--sage); color: white; }
        .btn-review { background-color: var(--teal); color: white; }
        .btn-reject { background-color: var(--terracotta); color: white; }
        .btn-view { background-color: #eee; color: var(--navy); }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-lg-2 admin-sidebar">
                <div class="mb-5 px-3">
                    <h5 class="playfair mb-0">RELATIONAL LENS</h5>
                    <small class="opacity-50">Admin Portal</small>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="queue.php" class="nav-link active"><i class="bi bi-list-ul me-2"></i> Review Queue</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link"><i class="bi bi-graph-up me-2"></i> Analytics</a>
                    </li>
                    <li class="nav-item mt-5">
                        <a href="../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 class="playfair">Review Queue</h2>
                    <div class="d-flex align-items-center">
                        <span class="me-3 small">Hello, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
                        <div class="rounded-circle bg-sand" style="width: 40px; height: 40px;"></div>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-table">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Story Title</th>
                                    <th>Filmmaker</th>
                                    <th>Category / Region</th>
                                    <th>Ethics</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($queue)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No stories currently in the queue.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($queue as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div>
                                                <small class="text-muted">ID: #<?= $item['id'] ?></small>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($item['filmmaker_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($item['filmmaker_email']) ?></small>
                                            </td>
                                            <td>
                                                <div class="small">Cat: <?= htmlspecialchars($item['category_name']) ?></div>
                                                <div class="small text-muted">Reg: <?= htmlspecialchars($item['region_name']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $item['ethics_consent'] ? 'success' : 'danger' ?> rounded-pill">
                                                    <?= $item['ethics_consent'] ? 'Informed' : 'Pending' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = 'status-' . ($item['status'] === 'under_review' ? 'review' : $item['status']);
                                                    $statusLabel = ucfirst(str_replace('_', ' ', $item['status']));
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="../story.php?slug=<?= $item['slug'] ?>" target="_blank" class="btn-action btn-view" title="Preview"><i class="bi bi-eye"></i></a>
                                                
                                                <?php if ($item['status'] === 'pending'): ?>
                                                    <a href="queue.php?action=review&id=<?= $item['id'] ?>" class="btn-action btn-review" title="Assign to Review"><i class="bi bi-person-check"></i></a>
                                                <?php endif; ?>

                                                <?php if ($item['status'] === 'under_review' || $item['status'] === 'pending'): ?>
                                                    <a href="queue.php?action=approve&id=<?= $item['id'] ?>" class="btn-action btn-approve" title="Approve & Publish"><i class="bi bi-check-lg"></i></a>
                                                    <a href="queue.php?action=reject&id=<?= $item['id'] ?>" class="btn-action btn-reject" onclick="return confirm('Are you sure you want to reject this story?')" title="Reject"><i class="bi bi-x-lg"></i></a>
                                                <?php endif; ?>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
