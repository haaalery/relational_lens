<?php
require_once 'auth_check.php';
require_once '../config/db.php';
session_start();
require_once 'layout.php';

// Check permissions: Only full admins can manage users
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: Superadmin privileges required to manage users.");
}

$message = "";
$messageType = "";

// 1. Handle Role Updates
if (isset($_POST['update_role'])) {
    verify_csrf();
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Prevent changing your own role
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot change your own role.";
        $messageType = "warning";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user_id]);
            $message = "User role updated successfully.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// 2. Handle Deletion
if (isset($_GET['delete_user'])) {
    verify_csrf();
    $delete_id = $_GET['delete_user'];
    
    if ($delete_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
        $messageType = "warning";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $message = "User deleted permanently.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Cannot delete user. They may have linked stories or articles.";
            $messageType = "danger";
        }
    }
}

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

render_admin_header("User Directory", "users");
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <i class="bi <?= $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <div>
            <h4 class="playfair fw-bold mb-1">Access Control</h4>
            <p class="text-muted small mb-0">Oversee community permissions and administrative roles.</p>
        </div>
        <span class="badge bg-navy text-white px-3 py-2"><?= count($users) ?> Members</span>
    </div>

    <!-- Search & Filter Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="userSearch" class="form-control border-start-0" placeholder="Search by name or email...">
            </div>
        </div>
        <div class="col-md-4">
            <select id="roleFilter" class="form-select">
                <option value="all">All Roles</option>
                <option value="filmmaker">Filmmakers</option>
                <option value="reviewer">Reviewers</option>
                <option value="admin">Administrators</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="userTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Permissions</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr class="user-row" data-role="<?= $u['role'] ?>" data-search="<?= strtolower($u['name'] . ' ' . $u['email']) ?>">
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-sand text-navy d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="text-muted small italic" style="font-size: 0.7rem;">Member ID: #<?= $u['id'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="text-muted small"><?= htmlspecialchars($u['email']) ?></span></td>
                    <td>
                        <form method="POST" class="d-flex gap-2 align-items-center">
                            <?php csrf_input(); ?>
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" class="form-select form-select-sm fw-bold role-select" style="width: 140px;" onchange="this.form.submit()">
                                <option value="filmmaker" <?= $u['role'] == 'filmmaker' ? 'selected' : '' ?>>Filmmaker</option>
                                <option value="reviewer" <?= $u['role'] == 'reviewer' ? 'selected' : '' ?>>Reviewer</option>
                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <input type="hidden" name="update_role" value="1">
                        </form>
                    </td>
                    <td><small class="text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></small></td>
                    <td class="text-end">
                        <a href="users.php?delete_user=<?= $u['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                           class="btn btn-sm btn-outline-danger border-0" 
                           title="Revoke Access"
                           onclick="return confirm('Permanently delete this user?')">
                            <i class="bi bi-person-x-fill fs-5"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');

    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleTerm = roleFilter.value;

        rows.forEach(row => {
            const matchesSearch = row.dataset.search.includes(searchTerm);
            const matchesRole = roleTerm === 'all' || row.dataset.role === roleTerm;
            
            if (matchesSearch && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);
});
</script>

<?php render_admin_footer(); ?>