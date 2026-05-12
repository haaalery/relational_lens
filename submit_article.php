<?php
require_once 'header.php';
require_once 'config/security.php';

// Security: Only logged-in users can submit articles
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch categories and existing stories for the form
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    $stories = $pdo->query("SELECT id, title FROM stories WHERE status = 'approved' ORDER BY title ASC")->fetchAll();
    
    // Fetch logged-in user data
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $currentUser = $userStmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $categories = [];
    $stories = [];
}

$message = "";
$messageType = "";

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $category_id = $_POST['category_id'] ?: null;
        $related_story_id = $_POST['related_story_id'] ?: null;
        $author_id = $_SESSION['user_id'];

        // Basic validation
        if (empty($title) || empty($content) || empty($excerpt)) {
            throw new Exception("Title, excerpt, and content are required.");
        }
        
        if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid image URL format.");
        }

        $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = $base_slug;
        $counter = 1;
        while (true) {
            $checkStmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if (!$checkStmt->fetch()) break;
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO articles (author_id, title, slug, excerpt, content, image_url, category_id, related_story_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $author_id, $title, $slug, $excerpt, $content, $image_url, $category_id, $related_story_id
        ]);

        $pdo->commit();

        $message = "Your article has been submitted for peer review! You can see its status on your profile.";
        $messageType = "success";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

$extra_head = '
    <style>
        .submit-hero { background-color: var(--navy); color: white; padding: 6rem 0; text-align: center; }
        .form-card { background: var(--card-bg); border-radius: 24px; padding: 3rem; box-shadow: 0 20px 60px rgba(0,0,0,0.1); border: 1px solid var(--card-border); margin-top: -4rem; position: relative; z-index: 10; color: var(--text-color); }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 3rem; position: relative; }
        .step-indicator::before { content: ""; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: #eee; z-index: 1; }
        .step { position: relative; z-index: 2; background: transparent; padding: 0 15px; text-align: center; }
        .step-num { width: 32px; height: 32px; background: var(--bg-color); border: 2px solid #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 700; font-size: 0.85rem; color: var(--muted); }
        .step.active .step-num { background: var(--terracotta); border-color: var(--terracotta); color: white; }
    </style>
';

render_header("Submit Article", "submit_article", $extra_head);
?>

    <section class="submit-hero">
        <div class="container">
            <h1 class="display-3 playfair fw-bold mb-3 animate-up">Contribute Scholarship</h1>
            <p class="lead opacity-75 animate-up">Add your critical reflections to The Gallery.</p>
        </div>
    </section>

    <main class="container mb-5 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show shadow-sm mb-5" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form id="submissionForm" method="POST" class="form-card reveal">
                    <?php csrf_input(); ?>
                    
                    <div class="step-indicator">
                        <div class="step active" id="step1-indicator">
                            <div class="step-num">1</div>
                            <span class="small fw-bold">Metadata</span>
                        </div>
                        <div class="step" id="step2-indicator">
                            <div class="step-num">2</div>
                            <span class="small fw-bold">Content</span>
                        </div>
                        <div class="step" id="step3-indicator">
                            <div class="step-num">3</div>
                            <span class="small fw-bold">Review</span>
                        </div>
                    </div>

                    <!-- Step 1: Metadata -->
                    <div class="form-step" id="step1">
                        <h4 class="playfair mb-4">Article Details</h4>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Article Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Critical Perspectives on Global Care" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Primary Category</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">Select Category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Related Documentary (Optional)</label>
                                <select name="related_story_id" id="related_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($stories as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Featured Image URL</label>
                            <input type="url" name="image_url" id="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-terracotta px-5 rounded-pill next-step" data-next="2">Next: Content &rarr;</button>
                        </div>
                    </div>

                    <!-- Step 2: Content -->
                    <div class="form-step d-none" id="step2">
                        <h4 class="playfair mb-4">Article Content</h4>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Short Excerpt (Teaser)</label>
                            <textarea name="excerpt" id="excerpt" class="form-control" rows="2" placeholder="Brief summary for the gallery grid..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Content (Markdown or HTML-safe Text)</label>
                            <textarea name="content" id="content" class="form-control" rows="12" placeholder="Write your reflection here..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step" data-prev="1">&larr; Back</button>
                            <button type="button" class="btn btn-terracotta px-5 rounded-pill next-step" data-next="3">Next: Review &rarr;</button>
                        </div>
                    </div>

                    <!-- Step 3: Review -->
                    <div class="form-step d-none" id="step3">
                        <h4 class="playfair mb-4">Review & Submit</h4>
                        <div class="p-4 rounded-4 mb-4 border" style="background: var(--bg-color);">
                            <div id="submission-preview" class="text-muted"></div>
                        </div>
                        <p class="small text-muted mb-4">By submitting, you agree to our terms of peer-review and scholarly contribution.</p>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step" data-prev="2">&larr; Back</button>
                            <button type="submit" class="btn btn-terracotta px-5 rounded-pill">Submit Article</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

<?php 
$extra_scripts = '
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const steps = document.querySelectorAll(".form-step");
        const indicators = document.querySelectorAll(".step");
        const nextBtns = document.querySelectorAll(".next-step");
        const prevBtns = document.querySelectorAll(".prev-step");

        nextBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                const next = btn.getAttribute("data-next");
                
                // Simple validation
                const currentStepDiv = document.getElementById("step" + (next-1));
                const requiredInputs = currentStepDiv.querySelectorAll("[required]");
                let valid = true;
                requiredInputs.forEach(input => {
                    if(!input.value) {
                        input.classList.add("is-invalid");
                        valid = false;
                    } else {
                        input.classList.remove("is-invalid");
                    }
                });

                if(!valid) {
                    alert("Please fill in all required fields.");
                    return;
                }

                steps.forEach(s => s.classList.add("d-none"));
                document.getElementById("step" + next).classList.remove("d-none");

                indicators.forEach(i => i.classList.remove("active"));
                document.getElementById("step" + next + "-indicator").classList.add("active");
                
                if(next == 3) updatePreview();
            });
        });

        prevBtns.forEach(btn => {
            btn.addEventListener("click", () => {
                const prev = btn.getAttribute("data-prev");
                steps.forEach(s => s.classList.add("d-none"));
                document.getElementById("step" + prev).classList.remove("d-none");

                indicators.forEach(i => i.classList.remove("active"));
                document.getElementById("step" + prev + "-indicator").classList.add("active");
            });
        });

        function updatePreview() {
            const title = document.getElementById("title").value;
            const cat = document.getElementById("category_id");
            const catName = cat.options[cat.selectedIndex].text;
            const excerpt = document.getElementById("excerpt").value;

            document.getElementById("submission-preview").innerHTML = `
                <div class="row">
                    <div class="col-md-8 mb-2"><small class="text-muted d-block">Article Title</small><strong>${title}</strong></div>
                    <div class="col-md-4 mb-2"><small class="text-muted d-block">Category</small><strong>${catName}</strong></div>
                    <div class="col-12"><small class="text-muted d-block">Short Excerpt</small><p class="small mb-0">${excerpt}</p></div>
                </div>
            `;
        }
    });
</script>
';
render_footer($extra_scripts); 
?>