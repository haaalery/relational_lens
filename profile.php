<?php
require_once 'header.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit;
}

try {
    // Fetch User Info
    $userStmt = $pdo->prepare("SELECT name, email, role, bio, avatar_url, created_at FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch();

    if (!$user) {
        header("Location: index.php");
        exit;
    }

    // Fetch Stories by this filmmaker (matching by email for now)
    $storyStmt = $pdo->prepare("SELECT s.*, r.name as region_name, c.name as category_name 
                                FROM stories s
                                LEFT JOIN regions r ON s.region_id = r.id
                                LEFT JOIN categories c ON s.category_id = c.id
                                WHERE s.filmmaker_email = ? AND s.status = 'approved'
                                ORDER BY s.created_at DESC");
    $storyStmt->execute([$user['email']]);
    $userStories = $storyStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("A system error occurred.");
}

$extra_head = '
    <style>
        .profile-hero {
            background-color: var(--navy);
            color: white;
            padding: 10rem 0 7rem;
            text-align: center;
        }
        .profile-avatar {
            width: 120px; height: 120px;
            background: var(--sand);
            color: var(--navy);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 700;
            margin: 0 auto 2rem;
            border: 4px solid rgba(255,255,255,0.1);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .profile-container {
            margin-top: -4rem;
            position: relative;
            z-index: 10;
        }
        .bio-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(14, 58, 71, 0.08);
            border: 1px solid var(--card-border);
            height: 100%;
            color: var(--text-color);
        }
        [data-theme="dark"] .profile-hero {
            background-color: #050f14;
        }
    </style>
';

render_header($user['name'], "profile", $extra_head);
?>

    <header class="profile-hero">
        <div class="container">
            <div class="profile-avatar animate-up">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h1 class="display-3 playfair fw-bold mb-2 animate-up"><?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-terracotta fw-bold text-uppercase tracking-wide animate-up"><?= ucfirst($user['role']) ?> Collective</p>
        </div>
    </header>

    <!-- Section Divider -->
    <div class="section-divider reveal">
        <div class="divider-line"></div>
        <div class="divider-icon">
            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
            <i class="bi bi-circle-fill"></i>
            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
        </div>
        <div class="divider-line"></div>
    </div>

    <main class="container profile-container mb-5 pb-5">
        <div class="row g-4">
            <!-- Bio Section -->
            <div class="col-lg-4">
                <div class="bio-card reveal">
                    <h4 class="playfair fw-bold mb-4 border-bottom pb-2">Professional Bio</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($user['bio'] ?: 'No bio provided.')) ?></p>
                    
                    <hr class="my-4 opacity-10">
                    
                    <div class="small mb-2"><i class="bi bi-calendar3 me-2"></i> Joined: <?= date('F Y', strtotime($user['created_at'])) ?></div>
                    <div class="small"><i class="bi bi-camera-reels me-2"></i> Contributions: <?= count($userStories) ?> Stories</div>
                </div>
            </div>

            <!-- Contributions Section -->
            <div class="col-lg-8">
                <div class="ps-lg-4">
                    <h2 class="playfair fw-bold text-navy mb-4 reveal">Contributions</h2>
                    
                    <div class="row g-4 reveal">
                        <?php if (empty($userStories)): ?>
                            <div class="col-12">
                                <div class="p-5 bg-card rounded-4 text-center border" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                                    <p class="text-muted mb-0">This collective member hasn't published any stories yet.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userStories as $story): ?>
                                <div class="col-md-6">
                                    <div class="story-card h-100">
                                        <div class="card-img-wrapper">
                                            <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" class="card-img-top">
                                            <a href="story.php?slug=<?= $story['slug'] ?>" class="play-overlay"><i class="bi bi-play-circle"></i></a>
                                        </div>
                                        <div class="p-4 d-flex flex-column">
                                            <div class="article-meta mb-2">
                                                <span><?= htmlspecialchars($story['region_name']) ?></span>
                                            </div>
                                            <h5 class="story-title"><?= htmlspecialchars($story['title']) ?></h5>
                                            <a href="story.php?slug=<?= $story['slug'] ?>" class="btn-read mt-auto">Watch Story &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php render_footer(); ?>