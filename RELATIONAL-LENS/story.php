<?php
session_start();
require_once 'config/db.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    header("Location: archive.php");
    exit;
}

try {
    // Fetch the story with region and category names
    $stmt = $pdo->prepare("SELECT s.*, r.name as region_name, c.name as category_name 
                           FROM stories s
                           LEFT JOIN regions r ON s.region_id = r.id
                           LEFT JOIN categories c ON s.category_id = c.id
                           WHERE s.slug = ? AND s.status = 'approved'");
    $stmt->execute([$slug]);
    $story = $stmt->fetch();

    if (!$story) {
        // Story not found or not approved
        header("Location: archive.php");
        exit;
    }

    // Increment view count
    $updateStmt = $pdo->prepare("UPDATE stories SET views_count = views_count + 1 WHERE id = ?");
    $updateStmt->execute([$story['id']]);

    // Fetch related stories (same category, excluding current)
    $relatedStmt = $pdo->prepare("SELECT * FROM stories 
                                   WHERE category_id = ? AND id != ? AND status = 'approved' 
                                   LIMIT 3");
    $relatedStmt->execute([$story['category_id'], $story['id']]);
    $relatedStories = $relatedStmt->fetchAll();

    // Decode decolonial tags if they exist
    $tags = json_decode($story['decolonial_tags'], true) ?: [];

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("A system error occurred. Please try again later.");
}

/**
 * Helper to convert YouTube/Vimeo URLs to embed URLs
 */
function getEmbedUrl($url) {
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return isset($match[1]) ? 'https://www.youtube.com/embed/' . $match[1] : $url;
    }
    if (strpos($url, 'vimeo.com') !== false) {
        preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^\/]*)/videos/|album/(?:\d+)/video/|video/|)(\d+)(?:$|/|\?)%i', $url, $match);
        return isset($match[1]) ? 'https://player.vimeo.com/video/' . $match[1] : $url;
    }
    return $url;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($story['title']) ?> | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .decolonial-tag {
            border: 1px solid var(--sage);
            color: var(--sage);
            padding: 0.2rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-right: 5px;
            display: inline-block;
        }

        .video-container {
            background-color: black;
            padding: 2rem 0;
            margin-bottom: 3rem;
        }

        .nav-tabs { border-bottom: 2px solid var(--sand); }
        .nav-tabs .nav-link {
            border: none;
            color: var(--muted);
            font-weight: 600;
            padding: 1rem 2rem;
        }
        .nav-tabs .nav-link.active {
            background: transparent;
            color: var(--navy);
            border-bottom: 3px solid var(--terracotta);
        }
        .tab-content {
            padding: 2rem 0;
            line-height: 1.8;
        }

        .sidebar-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        .filmmaker-avatar {
            width: 60px; height: 60px;
            background-color: var(--sand);
            border-radius: 50%;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 1.5rem;
            color: var(--navy);
            margin-bottom: 1rem;
        }

        .related-card {
            background: white; border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s;
        }
        .related-card:hover { transform: translateY(-3px); }
        .related-card img { height: 120px; object-fit: cover; }

        .transcript-box {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-height: 600px;
            overflow-y: auto;
            white-space: pre-wrap;
            border: 1px solid var(--sand);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand animate-up" href="index.php">
                <span class="logo-icon"></span>
                RELATIONAL LENS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
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

    <!-- Video Player Section -->
    <div class="video-container reveal">
        <div class="container">
            <div class="ratio ratio-16x9 animate-up">
                <iframe src="<?= getEmbedUrl($story['video_url']) ?>" title="<?= htmlspecialchars($story['title']) ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="d-flex gap-2 mb-3 reveal">
                    <span class="badge badge-sage"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($story['region_name'] ?: 'Global') ?></span>
                    <span class="badge badge-terracotta"><?= htmlspecialchars($story['category_name'] ?: 'General') ?></span>
                </div>
                <h1 class="display-5 mb-4 animate-up" style="animation-delay: 0.1s;"><?= htmlspecialchars($story['title']) ?></h1>

                <div class="mb-4 reveal">
                    <?php foreach ($tags as $tag): ?>
                        <span class="decolonial-tag">#<?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>

                <!-- Tabs for Description/Transcript -->
                <ul class="nav nav-tabs reveal" id="storyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About the Story</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="transcript-tab" data-bs-toggle="tab" data-bs-target="#transcript" type="button" role="tab">Transcript</button>
                    </li>
                </ul>
                <div class="tab-content reveal" id="storyTabsContent">
                    <div class="tab-pane fade show active" id="about" role="tabpanel">
                        <div class="fs-5 mb-4">
                            <?= nl2br(htmlspecialchars($story['description'])) ?>
                        </div>
                        
                        <div class="p-4 bg-sand bg-opacity-25 rounded-3 mb-4">
                            <h5 class="playfair mb-3">⚠️ Ethics & Accountability</h5>
                            <p class="mb-0"><strong>Community Credit:</strong> <?= htmlspecialchars($story['community_credit'] ?: 'This story was created with the informed participation of the community depicted.') ?></p>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="transcript" role="tabpanel">
                        <div class="transcript-box">
                            <?= $story['transcript'] ? htmlspecialchars($story['transcript']) : '<p class="text-muted italic">Transcript not available for this story.</p>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-card reveal">
                    <h5 class="playfair mb-3">Filmmaker</h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="filmmaker-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($story['filmmaker_name'] ?: 'Anonymous') ?></h6>
                            <small class="text-muted">Social Work Practitioner</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">
                        <?= nl2br(htmlspecialchars($story['filmmaker_bio'] ?: 'A dedicated practitioner contributing to the global archive of social work knowledge.')) ?>
                    </p>
                </div>

                <div class="sidebar-card reveal">
                    <h5 class="playfair mb-3">Metadata</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Language:</span>
                            <span class="fw-bold"><?= htmlspecialchars($story['language']) ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Subtitles:</span>
                            <span class="fw-bold"><?= $story['subtitles_available'] ? 'Yes' : 'No' ?></span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Views:</span>
                            <span class="fw-bold"><?= number_format($story['views_count']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Approved:</span>
                            <span class="fw-bold"><?= date('M d, Y', strtotime($story['approved_at'])) ?></span>
                        </li>
                    </ul>
                </div>

                <h5 class="playfair mb-3 mt-4 reveal">Related Stories</h5>
                <?php foreach ($relatedStories as $related): ?>
                    <a href="story.php?slug=<?= $related['slug'] ?>" class="text-decoration-none text-dark reveal">
                        <div class="related-card mb-3">
                            <div class="row g-0">
                                <div class="col-4">
                                    <img src="<?= htmlspecialchars($related['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" class="img-fluid rounded-start w-100 h-100" alt="<?= htmlspecialchars($related['title']) ?>">
                                </div>
                                <div class="col-8">
                                    <div class="p-2">
                                        <h6 class="small mb-1 text-truncate"><?= htmlspecialchars($related['title']) ?></h6>
                                        <small class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($related['region_id'] ? 'View Region' : 'Global') ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($relatedStories)): ?>
                    <p class="small text-muted reveal">No related stories in this category.</p>
                <?php endif; ?>
            </div>
        </div>
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
                    <div class="social-icons">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
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
                        <li class="nav-item"><a href="login.php" class="nav-link">Sign In</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Resources</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a href="#" class="nav-link">Ethics Guide</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Teaching Toolkit</a></li>
                        <li class="nav-item"><a href="#" class="nav-link">Contact</a></li>
                    </ul>
                </div>
            </div>
            <hr class="opacity-25">
            <div class="text-center small opacity-50 mt-4">
                &copy; <?= date('Y') ?> Relational Lens. All rights reserved. | ♿ WCAG 2.1 AA Compliant
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
