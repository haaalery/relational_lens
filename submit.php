<?php
require_once 'header.php';
require_once 'config/security.php';
require_once 'config/uploads.php';

// Fetch regions and categories for dropdowns
try {
    $regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    
    // Fetch logged-in user data if available
    $currentUser = null;
    if (isset($_SESSION['user_id'])) {
        $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $currentUser = $userStmt->fetch();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $regions = [];
    $categories = [];
}

$message = "";
$messageType = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    verify_csrf();
    try {
        // 1. Basic Story Data & Validation
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $thumbnail_url = ''; 
        
        // Handle Thumbnail Upload
        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_image($_FILES['thumbnail_file'], 'uploads/stories/');
            if ($upload_result['success']) {
                $thumbnail_url = $upload_result['path'];
            } else {
                throw new Exception("Thumbnail Upload Error: " . $upload_result['message']);
            }
        }

        if (empty($title) || empty($description) || empty($video_url)) {
            throw new Exception("Title, description, and video URL are required.");
        }
        
        if (!filter_var($video_url, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid video URL format.");
        }
        
        $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = $base_slug;
        $counter = 1;
        while (true) {
            $checkStmt = $pdo->prepare("SELECT id FROM stories WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if (!$checkStmt->fetch()) break;
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        $transcript = $_POST['transcript'] ?? '';
        $language = $_POST['language'] ?? 'English';
        $subtitles = isset($_POST['subtitles']) ? 1 : 0;
        
        // 2. Filmmaker Data
        $f_name = trim($_POST['f_name'] ?? '');
        $f_email = trim($_POST['f_email'] ?? '');
        $f_bio = trim($_POST['f_bio'] ?? '');
        
        if (empty($f_name) || empty($f_email)) {
            throw new Exception("Filmmaker name and email are required.");
        }

        // 3. Metadata
        $region_id = $_POST['region_id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        
        if (empty($region_id) || empty($category_id)) {
            throw new Exception("Region and category are required.");
        }

        // 4. Ethics
        $community_credit = trim($_POST['community_credit'] ?? '');
        $decolonial_tags = json_encode($_POST['decolonial_tags'] ?? []);
        $ethics_consent = isset($_POST['ethics_consent']) ? 1 : 0;

        if (!$ethics_consent || empty($community_credit)) {
            throw new Exception("Ethics consent and community credit are required to submit.");
        }

        $pdo->beginTransaction();
        $sql = "INSERT INTO stories (title, slug, description, transcript, video_url, thumbnail_url, 
                                     filmmaker_name, filmmaker_email, filmmaker_bio, 
                                     community_credit, region_id, category_id, language, 
                                     subtitles_available, status, ethics_consent, decolonial_tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 1, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $title, $slug, $description, $transcript, $video_url, $thumbnail_url,
            $f_name, $f_email, $f_bio,
            $community_credit, $region_id, $category_id, $language,
            $subtitles, $decolonial_tags
        ]);

        $story_id = $pdo->lastInsertId();

        // Insert into consent_records
        $consent_stmt = $pdo->prepare("INSERT INTO consent_records (story_id, filmmaker_confirmed, community_informed) VALUES (?, 1, 1)");
        $consent_stmt->execute([$story_id]);

        $pdo->commit();
        $message = "Story submitted successfully! Our reviewers will now evaluate your contribution.";
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

render_header("Submit Story", "submit", $extra_head);
?>

    <section class="submit-hero">
        <div class="container">
            <h1 class="display-3 playfair fw-bold mb-3 animate-up">Share Your Narrative</h1>
            <p class="lead opacity-75 animate-up">Join our global collective of ethical storytellers.</p>
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

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="form-card text-center reveal">
                        <i class="bi bi-lock display-1 text-muted opacity-25 mb-4"></i>
                        <h2 class="playfair">Members Only</h2>
                        <p class="text-muted mb-4">Please sign in or register to submit a story to the archive.</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="login.php" class="btn btn-terracotta px-4 rounded-pill">Sign In</a>
                            <a href="register.php" class="btn btn-outline-navy px-4 rounded-pill">Join Collective</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form id="submissionForm" method="POST" class="form-card reveal">
                        <?php csrf_input(); ?>
                        
                        <div class="step-indicator">
                            <div class="step active" id="step1-indicator">
                                <div class="step-num">1</div>
                                <span class="small fw-bold">The Narrative</span>
                            </div>
                            <div class="step" id="step2-indicator">
                                <div class="step-num">2</div>
                                <span class="small fw-bold">Metadata</span>
                            </div>
                            <div class="step" id="step3-indicator">
                                <div class="step-num">3</div>
                                <span class="small fw-bold">Ethics & Consent</span>
                            </div>
                        </div>

                        <!-- Step 1: Basic Info -->
                        <div class="form-step" id="step1">
                            <h4 class="playfair mb-4">Part 1: Story Details</h4>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Story Title</label>
                                <input type="text" name="title" id="title" class="form-control" placeholder="e.g. The Threads of Connection" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Full Description / Abstract</label>
                                <textarea name="description" id="description" class="form-control" rows="5" placeholder="Provide context and summary of the story..." required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Video URL (YouTube/Vimeo)</label>
                                    <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Thumbnail Image</label>
                                    <input type="file" name="thumbnail_file" class="form-control" accept="image/*">
                                    <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, WEBP.</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Full Transcript (Highly Recommended)</label>
                                <textarea name="transcript" class="form-control" rows="4" placeholder="Paste video transcript here for accessibility..."></textarea>
                            </div>
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-terracotta px-5 rounded-pill next-step" data-next="2">Next: Metadata &rarr;</button>
                            </div>
                        </div>

                        <!-- Step 2: Metadata -->
                        <div class="form-step d-none" id="step2">
                            <h4 class="playfair mb-4">Part 2: Categorization</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Primary Region</label>
                                    <select name="region_id" id="region_id" class="form-select" required>
                                        <option value="">Select a region...</option>
                                        <?php foreach ($regions as $r): ?>
                                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Primary Grand Challenge</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">Select a category...</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Decolonial Practice Tags (Select all that apply)</label>
                                <div class="row g-2">
                                    <?php 
                                    $tags = ["Relationality", "Place-based", "Ethics of Care", "Indigenous Wisdom", "Language Preservation", "Reciprocity"];
                                    foreach ($tags as $tag): ?>
                                        <div class="col-md-4">
                                            <div class="form-check small">
                                                <input class="form-check-input" type="checkbox" name="decolonial_tags[]" value="<?= $tag ?>" id="tag_<?= $tag ?>">
                                                <label class="form-check-label" for="tag_<?= $tag ?>"><?= $tag ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Primary Language</label>
                                    <input type="text" name="language" class="form-control" placeholder="e.g. English, Swahili, Spanish" value="English">
                                </div>
                                <div class="col-md-6 mb-3 pt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subtitles" id="subtitles" checked>
                                        <label class="form-check-label small" for="subtitles">Subtitles available in video?</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="playfair mb-3">Filmmaker Attribution</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Name</label>
                                    <input type="text" name="f_name" class="form-control" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Email</label>
                                    <input type="email" name="f_email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bio</label>
                                <textarea name="f_bio" class="form-control" rows="2" required><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step" data-prev="1">&larr; Back</button>
                                <button type="button" class="btn btn-terracotta px-5 rounded-pill next-step" data-next="3">Next: Ethics &rarr;</button>
                            </div>
                        </div>

                        <!-- Step 3: Ethics -->
                        <div class="form-step d-none" id="step3">
                            <h4 class="playfair mb-4">Part 3: Ethical Witnessing & Consent</h4>
                            <div class="p-4 rounded-4 mb-4 border" style="background: var(--bg-color); border-left: 5px solid var(--terracotta) !important;">
                                <h6 class="fw-bold mb-2">Relational Lens Ethics Statement</h6>
                                <p class="small mb-0">By submitting this story, I confirm that the individuals and communities depicted have provided informed consent for their stories to be shared globally. I acknowledge that the relationship between filmmaker and subject is based on mutual respect and reciprocity.</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Community Credit & Benefit</label>
                                <textarea name="community_credit" id="community_credit" class="form-control" rows="3" placeholder="How was the community involved in the making of this film? How do they benefit from its distribution?" required></textarea>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="ethics_consent" id="ethics_consent" required>
                                <label class="form-check-label small fw-bold" for="ethics_consent">
                                    I agree to the Relational Lens Ethics Statement and confirm that I have secured all necessary permissions.
                                </label>
                            </div>

                            <div class="p-4 rounded-4 mb-4 border" style="background: var(--bg-color);">
                                <h6 class="fw-bold mb-3">Review Your Submission</h6>
                                <div id="submission-preview" class="small text-muted"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill prev-step" data-prev="2">&larr; Back</button>
                                <button type="submit" class="btn btn-terracotta px-5 rounded-pill">Submit to Collective</button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
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
                
                // Simple validation for required fields in current step
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
            const reg = document.getElementById("region_id");
            const regName = reg.options[reg.selectedIndex].text;
            const cat = document.getElementById("category_id");
            const catName = cat.options[cat.selectedIndex].text;
            const community = document.getElementById("community_credit").value;

            document.getElementById("submission-preview").innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Title</small><strong>${title}</strong></div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Region</small><strong>${regName}</strong></div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Challenge</small><strong>${catName}</strong></div>
                    <div class="col-12"><small class="text-muted d-block">Community Credit</small><strong>${community}</strong></div>
                </div>
            `;
        }
    });
</script>
';
render_footer($extra_scripts); 
?>