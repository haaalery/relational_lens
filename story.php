<?php
require_once 'header.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    header("Location: archive.php");
    exit;
}

try {
    // Fetch the story with region, category names, and filmmaker user id
    $stmt = $pdo->prepare("SELECT s.*, r.name as region_name, c.name as category_name, u.id as user_id
                           FROM stories s
                           LEFT JOIN regions r ON s.region_id = r.id
                           LEFT JOIN categories c ON s.category_id = c.id
                           LEFT JOIN users u ON s.filmmaker_email = u.email
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
    if (strpos($url, "youtube.com") !== false || strpos($url, "youtu.be") !== false) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return isset($match[1]) ? "https://www.youtube.com/embed/" . $match[1] . "?autoplay=0&rel=0" : $url;
    }
    if (strpos($url, "vimeo.com") !== false) {
        preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^\/]*)/videos/|album/(?:\d+)/video/|video/|)(\d+)(?:$|/|\?)%i', $url, $match);
        return isset($match[1]) ? "https://player.vimeo.com/video/" . $match[1] . "?color=C57D54&title=0&byline=0&portrait=0" : $url;
    }
    return $url;
}

$extra_head = '
    <style>
        .decolonial-tag {
            border: 1px solid var(--sage);
            color: var(--sage);
            padding: 0.2rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-right: 5px;
            display: inline-block;
            text-decoration: none;
        }
        .video-player-section {
            background-color: #000;
            padding: 4rem 0;
            box-shadow: inset 0 0 100px rgba(0,0,0,0.5);
            position: relative;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .video-container iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            border: none;
        }
        .transcript-box {
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 3rem;
            border: 1px solid var(--card-border);
            max-height: 500px;
            overflow-y: auto;
        }
        .author-card {
            background-color: var(--navy);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            position: sticky;
            top: 100px;
        }
        [data-theme="dark"] .author-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
        }
    </style>
';

render_header($story['title'], "archive", $extra_head);
?>

    <section class="video-player-section">
        <div class="container">
            <div class="video-container reveal">
                <iframe src="<?= getEmbedUrl($story['video_url']) ?>" allowfullscreen></iframe>
            </div>
        </div>
    </section>

    <main id="main-content" class="container py-5 mt-4">
        <div class="row g-5">
            <!-- Left Column: Story Content -->
            <div class="col-lg-8">
                <div class="mb-4 reveal">
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge badge-sage"><?= htmlspecialchars($story['region_name']) ?></span>
                        <span class="badge bg-light text-muted border"><?= htmlspecialchars($story['category_name']) ?></span>
                    </div>
                    <h1 class="display-4 playfair fw-bold text-navy mb-4"><?= htmlspecialchars($story['title']) ?></h1>
                    
                    <div class="mb-4">
                        <?php foreach ($tags as $tag): ?>
                            <span class="decolonial-tag mb-2"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <p class="lead text-muted mb-5"><?= nl2br(htmlspecialchars($story['description'])) ?></p>
                </div>

                <!-- Transcript Section -->
                <?php if ($story['transcript']): ?>
                <div class="transcript-section mb-5 reveal">
                    <h3 class="playfair fw-bold mb-4">Transcript</h3>
                    <div class="transcript-box inter">
                        <?= nl2br(htmlspecialchars($story['transcript'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Related Content -->
                <section class="related-stories pt-5 border-top reveal">
                    <h4 class="playfair fw-bold mb-4">More from <?= htmlspecialchars($story['category_name']) ?></h4>
                    <div class="row g-4">
                        <?php foreach ($relatedStories as $rs): ?>
                            <div class="col-md-6">
                                <a href="story.php?slug=<?= $rs['slug'] ?>" class="text-decoration-none">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                        <div class="card-img-wrapper">
                                            <img src="<?= htmlspecialchars($rs['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400') ?>" class="card-img-top">
                                        </div>
                                        <div class="card-body">
                                            <h6 class="fw-bold text-navy mb-0"><?= htmlspecialchars($rs['title']) ?></h6>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Right Column: Attribution & Info -->
            <div class="col-lg-4">
                <div class="author-card reveal">
                    <h5 class="playfair fw-bold mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.1) !important;">Filmmaker Perspective</h5>
                    <div class="mb-4">
                        <div class="fw-bold mb-1"><?= htmlspecialchars($story['filmmaker_name']) ?></div>
                        <p class="small opacity-75"><?= nl2br(htmlspecialchars($story['filmmaker_bio'])) ?></p>
                    </div>
                    
                    <?php if ($story['user_id']): ?>
                        <a href="profile.php?id=<?= $story['user_id'] ?>" class="btn btn-outline-light btn-sm w-100 rounded-pill">View Member Profile</a>
                    <?php endif; ?>

                    <hr class="my-4 opacity-10">
                    
                    <div class="small mb-2"><i class="bi bi-calendar3 me-2"></i> Published: <?= date('F j, Y', strtotime($story['created_at'])) ?></div>
                    <div class="small mb-2"><i class="bi bi-eye me-2"></i> <?= number_format($story['views_count']) ?> views</div>
                    <div class="small"><i class="bi bi-shield-check me-2"></i> Ethics Verified</div>
                </div>

                <div class="p-4 bg-light rounded-4 mt-4 reveal" style="border: 1px dashed #ccc;">
                    <h6 class="fw-bold text-navy mb-3">Cite this story</h6>
                    <p class="small text-muted mb-0 inter" style="font-size: 0.75rem;">
                        <?= htmlspecialchars($story['filmmaker_name']) ?>. (<?= date('Y', strtotime($story['created_at'])) ?>). <i><?= htmlspecialchars($story['title']) ?></i>. Relational Lens: A Global Social Work Documentary Platform.
                    </p>
                </div>
            </div>
        </div>
    </main>

<?php render_footer(); ?>