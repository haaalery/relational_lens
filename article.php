<?php
require_once 'header.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    header("Location: gallery.php");
    exit;
}

try {
    // Fetch the specific article with author and category details
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as author_name, u.bio as author_bio, u.id as author_id, 
               c.name as category_name, c.icon as category_icon,
               s.title as related_story_title, s.slug as related_story_slug, s.thumbnail_url as related_story_thumb
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN stories s ON a.related_story_id = s.id
        WHERE a.slug = ? AND a.status = 'approved'
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();

    if (!$article) {
        header("Location: gallery.php");
        exit;
    }

    // Fetch more from the same author or general latest
    $moreStmt = $pdo->prepare("SELECT * FROM articles WHERE id != ? AND status = 'approved' ORDER BY created_at DESC LIMIT 2");
    $moreStmt->execute([$article['id']]);
    $moreArticles = $moreStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("A system error occurred.");
}

$extra_head = '
    <style>
        .article-hero {
            padding: 8rem 0 4rem;
            background-color: var(--bg-color);
        }
        .article-content {
            font-family: "Inter", sans-serif;
            font-size: 1.15rem;
            line-height: 2;
            color: var(--text-color);
        }
        .article-content h3 {
            font-family: "Playfair Display", serif;
            margin-top: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--navy);
        }
        [data-theme="dark"] .article-content h3 {
            color: var(--sand);
        }
        .author-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid var(--card-border);
            position: sticky;
            top: 100px;
        }
        .related-doc-card {
            background: var(--navy);
            color: white;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 2rem;
        }
        .related-doc-thumb {
            height: 150px;
            width: 100%;
            object-fit: cover;
            opacity: 0.8;
            transition: 0.3s;
        }
        .related-doc-card:hover .related-doc-thumb { opacity: 1; }
        
        .article-body-wrapper img {
            max-width: 100%;
            border-radius: 15px;
            margin: 2rem 0;
        }
    </style>
';

render_header($article['title'], "gallery", $extra_head);
?>

    <!-- Article Hero -->
    <header class="article-hero reveal">
        <div class="container text-center">
            <div class="mb-4">
                <span class="badge badge-sage px-3 py-2"><?= htmlspecialchars($article['category_name'] ?: 'Scholarship') ?></span>
            </div>
            <h1 class="display-3 playfair fw-bold text-navy mb-4"><?= htmlspecialchars($article['title']) ?></h1>
            <div class="d-flex justify-content-center align-items-center gap-3 text-muted small">
                <span>By <?= htmlspecialchars($article['author_name']) ?></span>
                <span class="opacity-50">|</span>
                <span><?= date('F j, Y', strtotime($article['created_at'])) ?></span>
            </div>
        </div>
    </header>

    <main class="container py-5">
        <div class="row g-5">
            <!-- Left Column: Content -->
            <div class="col-lg-8">
                <div class="article-body-wrapper reveal">
                    <?php if ($article['image_url']): ?>
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="Article Image" class="shadow-sm">
                    <?php endif; ?>

                    <div class="article-content">
                        <?= nl2br(htmlspecialchars($article['content'])) ?>
                    </div>
                </div>

                <!-- More Articles -->
                <section class="mt-5 pt-5 border-top reveal">
                    <h4 class="playfair fw-bold mb-4">Read More Reflections</h4>
                    <div class="row g-4">
                        <?php foreach ($moreArticles as $ma): ?>
                            <div class="col-md-6">
                                <a href="article.php?slug=<?= $ma['slug'] ?>" class="text-decoration-none text-dark">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-2"><?= htmlspecialchars($ma['title']) ?></h6>
                                            <p class="small text-muted mb-0"><?= htmlspecialchars(substr($ma['excerpt'], 0, 80)) ?>...</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- Right Column: Author & Related Story -->
            <div class="col-lg-4">
                <div class="author-card reveal">
                    <h5 class="playfair fw-bold mb-3 border-bottom pb-2">About the Author</h5>
                    <div class="fw-bold mb-2"><?= htmlspecialchars($article['author_name']) ?></div>
                    <p class="small text-muted mb-4"><?= nl2br(htmlspecialchars($article['author_bio'])) ?></p>
                    <a href="profile.php?id=<?= $article['author_id'] ?>" class="btn btn-outline-navy btn-sm w-100 rounded-pill">Author Profile</a>

                    <?php if ($article['related_story_id']): ?>
                    <div class="related-doc-card shadow-sm reveal">
                        <img src="<?= htmlspecialchars($article['related_story_thumb'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400') ?>" class="related-doc-thumb">
                        <div class="p-3">
                            <small class="text-uppercase opacity-50 tracking-wide mb-1 d-block" style="font-size: 0.65rem;">Related Documentary</small>
                            <h6 class="fw-bold mb-3 small"><?= htmlspecialchars($article['related_story_title']) ?></h6>
                            <a href="story.php?slug=<?= $article['related_story_slug'] ?>" class="btn btn-terracotta btn-sm w-100 rounded-pill">Watch Now</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="p-4 rounded-4 mt-4 reveal" style="border: 1px dashed var(--muted);">
                    <h6 class="fw-bold text-navy mb-3">Cite this Reflection</h6>
                    <p class="small text-muted mb-0 inter" style="font-size: 0.75rem;">
                        <?= htmlspecialchars($article['author_name']) ?>. (<?= date('Y', strtotime($article['created_at'])) ?>). <i><?= htmlspecialchars($article['title']) ?></i>. The Gallery: Relational Lens.
                    </p>
                </div>
            </div>
        </div>
    </main>

<?php render_footer(); ?>