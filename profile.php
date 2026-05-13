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

    // Fetch Stories by this filmmaker
    $isOwnProfile = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id);
    
    // Logic for Story visibility
    $storyQuery = "SELECT s.*, r.name as region_name, c.name as category_name 
                    FROM stories s
                    LEFT JOIN regions r ON s.region_id = r.id
                    LEFT JOIN categories c ON s.category_id = c.id
                    WHERE s.filmmaker_email = ?";
    
    if (!$isOwnProfile) {
        $storyQuery .= " AND s.status = 'approved'";
    }
    
    $storyQuery .= " ORDER BY s.created_at DESC";
    
    $storyStmt = $pdo->prepare($storyQuery);
    $storyStmt->execute([$user['email']]);
    $userStories = $storyStmt->fetchAll();

    // Fetch Articles by this author
    $articleQuery = "SELECT a.*, c.name as category_name 
                      FROM articles a
                      LEFT JOIN categories c ON a.category_id = c.id
                      WHERE a.author_id = ?";
                      
    if (!$isOwnProfile) {
        $articleQuery .= " AND a.status = 'approved'";
    }
    
    $articleQuery .= " ORDER BY a.created_at DESC";
    
    $articleStmt = $pdo->prepare($articleQuery);
    $articleStmt->execute([$user_id]);
    $userArticles = $articleStmt->fetchAll();

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
        .status-badge {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            letter-spacing: 0.5px;
        }
        .badge-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-rejected { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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

    <main id="main-content" class="container profile-container mb-5 pb-5">
        <div class="row g-4">
            <!-- Bio Section -->
            <div class="col-lg-4">
                <div class="bio-card reveal">
                    <h4 class="playfair fw-bold mb-4 border-bottom pb-2">Professional Bio</h4>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($user['bio'] ?: 'No bio provided.')) ?></p>
                    
                    <hr class="my-4 opacity-10">
                    
                    <div class="small mb-2"><i class="bi bi-calendar3 me-2"></i> Joined: <?= date('F Y', strtotime($user['created_at'])) ?></div>
                    <div class="small mb-2"><i class="bi bi-camera-reels me-2"></i> Stories: <?= count($userStories) ?></div>
                    <div class="small"><i class="bi bi-journal-text me-2"></i> Articles: <?= count($userArticles) ?></div>
                </div>
            </div>

            <!-- Contributions Section -->
            <div class="col-lg-8">
                <div class="ps-lg-4">
                    <h2 class="playfair fw-bold text-navy mb-4 reveal"><?= $isOwnProfile ? 'My Submission History' : 'Contributions' ?></h2>
                    
                    <!-- Stories Grid -->
                    <h5 class="text-muted small fw-bold text-uppercase mb-3 reveal">Documentary Stories</h5>
                    <div class="row g-4 mb-5 reveal">
                        <?php if (empty($userStories)): ?>
                            <div class="col-12">
                                <div class="p-4 bg-card rounded-4 text-center border" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                                    <p class="text-muted small mb-0">No stories published yet.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userStories as $story): ?>
                                <div class="col-md-6">
                                    <div class="story-card h-100">
                                        <div class="card-img-wrapper">
                                            <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" class="card-img-top">
                                            <?php if ($story['status'] === 'approved'): ?>
                                                <a href="story.php?slug=<?= $story['slug'] ?>" class="play-overlay"><i class="bi bi-play-circle"></i></a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="p-4 d-flex flex-column">
                                            <div class="article-meta mb-2 d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($story['region_name']) ?></span>
                                                <?php if ($isOwnProfile): ?>
                                                    <span class="status-badge badge-<?= $story['status'] ?>"><?= $story['status'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="story-title" style="font-size: 1.1rem;"><?= htmlspecialchars($story['title']) ?></h5>
                                            <?php if ($story['status'] === 'approved'): ?>
                                                <a href="story.php?slug=<?= $story['slug'] ?>" class="btn-read mt-auto">Watch Story &rarr;</a>
                                            <?php else: ?>
                                                <div class="mt-auto small text-muted italic">In Review</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Articles Grid -->
                    <h5 class="text-muted small fw-bold text-uppercase mb-3 reveal">Scholarly Reflections</h5>
                    <div class="row g-4 reveal">
                        <?php if (empty($userArticles)): ?>
                            <div class="col-12">
                                <div class="p-4 bg-card rounded-4 text-center border" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                                    <p class="text-muted small mb-0">No articles published yet.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userArticles as $article): ?>
                                <div class="col-md-6">
                                    <div class="story-card h-100">
                                        <div class="card-img-wrapper">
                                            <img src="<?= htmlspecialchars($article['image_url'] ?: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=1000&auto=format&fit=crop') ?>" class="card-img-top">
                                        </div>
                                        <div class="p-4 d-flex flex-column">
                                            <div class="article-meta mb-2 d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($article['category_name']) ?></span>
                                                <?php if ($isOwnProfile): ?>
                                                    <span class="status-badge badge-<?= $article['status'] ?>"><?= $article['status'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="story-title" style="font-size: 1.1rem;"><?= htmlspecialchars($article['title']) ?></h5>
                                            <?php if ($article['status'] === 'approved'): ?>
                                                <a href="article.php?slug=<?= $article['slug'] ?>" class="btn-read mt-auto">Read Article &rarr;</a>
                                            <?php else: ?>
                                                <div class="mt-auto small text-muted italic">In Peer Review</div>
                                            <?php endif; ?>
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