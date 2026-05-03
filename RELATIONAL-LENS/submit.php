<?php
session_start();
require_once 'config/db.php';

// Fetch regions and categories for dropdowns
try {
    $regions = $pdo->query("SELECT * FROM regions ORDER BY name ASC")->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $regions = [];
    $categories = [];
}

$message = "";
$messageType = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Basic Story Data
        $title = $_POST['title'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $description = $_POST['description'];
        $transcript = $_POST['transcript'];
        $video_url = $_POST['video_url'];
        $thumbnail_url = $_POST['thumbnail_url']; // In a real app, handle file upload
        $language = $_POST['language'];
        $subtitles = isset($_POST['subtitles']) ? 1 : 0;
        
        // 2. Filmmaker Data
        $f_name = $_POST['f_name'];
        $f_email = $_POST['f_email'];
        $f_bio = $_POST['f_bio'];

        // 3. Metadata
        $region_id = $_POST['region_id'];
        $category_id = $_POST['category_id'];
        
        // 4. Ethics
        $community_credit = $_POST['community_credit'];
        $decolonial_tags = json_encode($_POST['decolonial_tags'] ?? []);
        $ethics_consent = isset($_POST['ethics_consent']) ? 1 : 0;

        if (!$ethics_consent) {
            throw new Exception("Ethics consent is required to submit.");
        }

        // Insert into stories
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
        $message = "Story submitted successfully! It is now in the review queue.";
        $messageType = "success";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Story | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand animate-up" href="index.php">
                <span class="logo-icon"></span>
                RELATIONAL LENS
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle btn-signin" href="#" role="button" data-bs-toggle="dropdown">
                                Hi, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <?php if (in_array($_SESSION['user_role'], ['admin', 'reviewer'])): ?>
                                    <li><a class="dropdown-item small" href="admin/queue.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item small text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn-signin ms-lg-3" href="login.php">Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container wizard-container reveal">
        <div class="text-center mb-5 animate-up">
            <h1 class="display-5">Submit Your Story</h1>
            <p class="text-muted">Join a global collective of social work documentary filmmakers.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4 animate-up" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div class="step-indicator animate-up" style="animation-delay: 0.1s;">
            <div class="step active" id="step1-indicator">
                <div class="step-num">1</div>
                <span class="small">Story Details</span>
            </div>
            <div class="step" id="step2-indicator">
                <div class="step-num">2</div>
                <span class="small">Ethics & Consent</span>
            </div>
            <div class="step" id="step3-indicator">
                <div class="step-num">3</div>
                <span class="small">Review</span>
            </div>
        </div>

        <form id="submissionForm" method="POST" class="form-card animate-up" style="animation-delay: 0.2s;">
            
            <!-- Step 1: Story Details -->
            <div class="wizard-step active" id="step1">
                <h4 class="playfair mb-4">Step 1: Primary Information</h4>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Story Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Voices of the River" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <select name="region_id" class="form-select" required>
                            <option value="">Select Region...</option>
                            <?php foreach ($regions as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category (Grand Challenge)</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / Abstract</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Briefly describe the story and its significance to social work practice..." required></textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Video URL (YouTube/Vimeo)</label>
                        <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thumbnail URL (Optional)</label>
                        <input type="text" name="thumbnail_url" class="form-control" placeholder="https://image-link.com/photo.jpg">
                    </div>
                    <hr class="my-4">
                    <h5 class="playfair mb-3">Filmmaker Information</h5>
                    <div class="col-md-6">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="f_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Your Email</label>
                        <input type="email" name="f_email" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Professional Bio</label>
                        <textarea name="f_bio" class="form-control" rows="2" placeholder="Tell us about your role in social work..."></textarea>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-next" onclick="nextStep(2)">Next Step &rarr;</button>
                </div>
            </div>

            <!-- Step 2: Ethics & Consent -->
            <div class="wizard-step" id="step2">
                <h4 class="playfair mb-4">Step 2: Ethical Accountability</h4>
                
                <div class="ethics-box">
                    <h6 class="fw-bold mb-2">Relational Practice Notice</h6>
                    <p class="small mb-0">Storytelling in social work is an act of ethical witnessing. We prioritize stories made <strong>with</strong> communities, not just <strong>about</strong> them.</p>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Community Credit</label>
                        <p class="small text-muted mb-2">Who is this story WITH? Name the community, collective, or partners involved.</p>
                        <input type="text" name="community_credit" class="form-control" placeholder="Created with the [Community Name] collective..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Decolonial Tags</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php $tags = ["Indigenous", "Land Rights", "Displacement", "Resistance", "Healing", "Anti-Colonial", "Mental Health", "Labor Rights"]; 
                            foreach ($tags as $tag): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="decolonial_tags[]" value="<?= $tag ?>" id="tag_<?= $tag ?>">
                                    <label class="form-check-label small" for="tag_<?= $tag ?>"><?= $tag ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input ms-0 me-3" type="checkbox" name="ethics_consent" value="1" id="consentCheck" required>
                            <label class="form-check-label small fw-bold" for="consentCheck">
                                I confirm this story was created with the informed consent and active participation of the community depicted. I have followed ethical guidelines for visual representation in social work.
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Transcript (Highly Recommended for Accessibility)</label>
                        <textarea name="transcript" class="form-control" rows="6" placeholder="Paste full transcript here..."></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-back" onclick="nextStep(1)">&larr; Back</button>
                    <button type="button" class="btn btn-next" onclick="nextStep(3)">Review Submission &rarr;</button>
                </div>
            </div>

            <!-- Step 3: Review & Submit -->
            <div class="wizard-step" id="step3">
                <h4 class="playfair mb-4">Step 3: Review Your Submission</h4>
                <div class="p-4 bg-light rounded mb-4" id="reviewSummary">
                    <!-- Summary populated by JS -->
                </div>
                <p class="text-muted small">By clicking submit, your story will enter our peer-review queue. Our team of reviewers will reach out within 14 days.</p>
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-back" onclick="nextStep(2)">&larr; Back</button>
                    <button type="submit" class="btn btn-submit">Submit to Archive</button>
                </div>
            </div>

        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container reveal">
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <a class="navbar-brand text-white mb-3" href="index.php">
                        <span class="logo-icon bg-white"></span>
                        RELATIONAL LENS
                    </a>
                    <p class="small opacity-75">"Stories do more than inform. They connect. They reveal. They transform."</p>
                </div>
                <div class="col-md-2 ms-auto">
                    <h5>Explore</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="archive.php" class="nav-link">Archive</a></li>
                        <li class="nav-item"><a href="map.php" class="nav-link">Map</a></li>
                        <li class="nav-item"><a href="classroom.php" class="nav-link">Classroom</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Participate</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="submit.php" class="nav-link">Submit Story</a></li>
                        <li class="nav-item"><a href="register.php" class="nav-link">Join Collective</a></li>
                    </ul>
                </div>
            </div>
            <hr class="opacity-25">
            <div class="text-center small opacity-50 mt-4">
                &copy; <?= date('Y') ?> Relational Lens. | ♿ WCAG 2.1 AA Compliant | ⚠️ Ethics First Platform
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <script>
        function nextStep(step) {
            // Validate current step before proceeding
            const currentStep = document.querySelector('.wizard-step.active');
            const inputs = currentStep.querySelectorAll('[required]');
            let valid = true;
            
            if (step > parseInt(currentStep.id.replace('step', ''))) {
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        valid = false;
                    }
                });
            }

            if (!valid) return;

            // Hide all steps
            document.querySelectorAll('.wizard-step').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));

            // Show target step
            document.getElementById('step' + step).classList.add('active');
            document.getElementById('step' + step + '-indicator').classList.add('active');

            if (step === 3) populateReview();
        }

        function populateReview() {
            const form = document.getElementById('submissionForm');
            const summary = document.getElementById('reviewSummary');
            
            const title = form.querySelector('[name="title"]').value;
            const filmmaker = form.querySelector('[name="f_name"]').value;
            const category = form.querySelector('[name="category_id"]');
            const catName = category.options[category.selectedIndex].text;
            const community = form.querySelector('[name="community_credit"]').value;

            summary.innerHTML = `
                <h5>${title}</h5>
                <p class="mb-1 text-muted">Submitted by: ${filmmaker}</p>
                <p class="mb-1 text-muted">Category: ${catName}</p>
                <p class="mb-0 text-muted">Community Credit: ${community}</p>
            `;
        }
    </script>
</body>
</html>
