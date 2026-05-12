<?php
require_once '../config/db.php';
session_start();
require_once 'layout.php';

// Check permissions
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: Superadmin privileges required.");
}

$message = "";
$messageType = "";

// --- HANDLE CATEGORY ACTIONS ---
if (isset($_POST['add_category'])) {
    verify_csrf();
    $name = $_POST['name'];
    $icon = $_POST['icon'] ?: 'bi-tag';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon, slug) VALUES (?, ?, ?)");
        $stmt->execute([$name, $icon, $slug]);
        $message = "Category '$name' added successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

if (isset($_GET['delete_cat'])) {
    verify_csrf();
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_GET['delete_cat']]);
        $message = "Category deleted.";
        $messageType = "warning";
    } catch (Exception $e) {
        $message = "Cannot delete: This category is likely linked to existing stories.";
        $messageType = "danger";
    }
}

// --- HANDLE REGION ACTIONS ---
if (isset($_POST['add_region'])) {
    verify_csrf();
    $name = $_POST['name'];
    $continent = $_POST['continent'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO regions (name, continent, lat, lng) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $continent, $lat, $lng]);
        $message = "Region '$name' added successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

if (isset($_GET['delete_reg'])) {
    verify_csrf();
    try {
        $stmt = $pdo->prepare("DELETE FROM regions WHERE id = ?");
        $stmt->execute([$_GET['delete_reg']]);
        $message = "Region deleted.";
        $messageType = "warning";
    } catch (Exception $e) {
        $message = "Cannot delete: This region is likely linked to existing stories.";
        $messageType = "danger";
    }
}

// Fetch Data
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll();

render_admin_header("Taxonomy & Tags", "taxonomy");
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi <?= $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <div><?= $message ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Categories Column -->
    <div class="col-lg-6">
        <div class="admin-card border-0 mb-4 bg-navy text-white shadow-lg">
            <h4 class="playfair fw-bold mb-1 text-white"><i class="bi bi-tag-fill me-2 text-sand"></i>Define New Category</h4>
            <p class="opacity-75 small mb-4">Create a new global tag for content classification.</p>
            <form method="POST" class="row g-2">
                <?php csrf_input(); ?>
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control border-0 py-2 shadow-sm" placeholder="e.g. Climate Justice" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="icon" class="form-control border-0 py-2 shadow-sm" placeholder="Icon (bi-globe)">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_category" class="btn btn-terracotta w-100 py-2"><i class="bi bi-plus-lg"></i></button>
                </div>
            </form>
        </div>

        <div class="admin-card border-0">
            <h4 class="playfair fw-bold mb-4 text-navy">Global Categories</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 rounded-start">Icon</th>
                            <th class="border-0">Tag Name</th>
                            <th class="border-0 text-end rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi <?= $cat['icon'] ?> text-teal h5 mb-0"></i></div></td>
                            <td>
                                <span class="fw-bold text-navy"><?= htmlspecialchars($cat['name']) ?></span>
                                <br><small class="text-muted">Slug: <?= $cat['slug'] ?></small>
                            </td>
                            <td class="text-end">
                                <a href="taxonomy.php?delete_cat=<?= $cat['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn-action btn-reject" onclick="return confirm('Delete this category?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Regions Column -->
    <div class="col-lg-6">
        <div class="admin-card border-0 mb-4 bg-teal text-white shadow-lg">
            <h4 class="playfair fw-bold mb-1 text-white"><i class="bi bi-geo-alt-fill me-2 text-sand"></i>Add Map Region</h4>
            <p class="opacity-75 small mb-4">Register a new geographic location for story mapping.</p>
            <form method="POST">
                <?php csrf_input(); ?>
                <div class="row g-2 mb-2">
                    <div class="col-md-7">
                        <input type="text" name="name" class="form-control border-0 py-2 shadow-sm" placeholder="Country/City Name" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="continent" class="form-control border-0 py-2 shadow-sm" placeholder="Continent" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="number" step="any" name="lat" class="form-control border-0 py-2 shadow-sm" placeholder="Latitude" required>
                    </div>
                    <div class="col-md-5">
                        <input type="number" step="any" name="lng" class="form-control border-0 py-2 shadow-sm" placeholder="Longitude" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="add_region" class="btn btn-navy w-100 py-2"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="admin-card border-0">
            <h4 class="playfair fw-bold mb-4 text-navy">Mapped Regions</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 rounded-start">Region Details</th>
                            <th class="border-0 text-center">GPS Coordinates</th>
                            <th class="border-0 text-end rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($regions as $reg): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-navy"><?= htmlspecialchars($reg['name']) ?></span>
                                <br><small class="text-muted"><?= $reg['continent'] ?></small>
                            </td>
                            <td class="text-center"><small class="font-monospace text-muted bg-light px-2 py-1 rounded"><?= $reg['lat'] ?>, <?= $reg['lng'] ?></small></td>
                            <td class="text-end">
                                <a href="taxonomy.php?delete_reg=<?= $reg['id'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" class="btn-action btn-reject" onclick="return confirm('Delete this region?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>