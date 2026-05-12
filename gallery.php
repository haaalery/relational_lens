<?php
require_once 'header.php';

try {
    // Fetch all approved articles for the Gallery
    $stmt = $pdo->query("
        SELECT a.*, u.name as author_name, c.name as category_name, c.icon as category_icon
        FROM articles a
        JOIN users u ON a.author_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'approved'
        ORDER BY a.created_at DESC
    ");
    $articles = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $articles = [];
}

render_header("The Gallery", "gallery");
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">Scholarly Dialogue</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">The Gallery</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 800px; font-size: 1.4rem;">Critical scholarship and artistic reflections exploring the ethical dimensions of global social work.</p>
            
            <div class="animate-up">
                <a href="submit_article.php" class="btn btn-terracotta rounded-pill px-4 py-2">
                    <i class="bi bi-plus-circle me-2"></i> Submit an Article
                </a>
            </div>
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

    <main class="container py-5">
        <div class="row g-4 reveal">
            <?php if (empty($articles)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No scholarly articles have been published yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $art): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="article-card h-100">
                            <div class="card-img-wrapper">
                                <?php if ($art['category_name']): ?>
                                    <span class="category-badge shadow-sm"><?= htmlspecialchars($art['category_name']) ?></span>
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($art['image_url'] ?: 'https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($art['title']) ?>" loading="lazy">
                            </div>
                            <div class="article-body">
                                <div class="article-meta">
                                    <span><?= htmlspecialchars($art['author_name']) ?></span>
                                    <span class="mx-1">&bull;</span>
                                    <span><?= date('M d', strtotime($art['created_at'])) ?></span>
                                </div>
                                <h3 class="article-title"><?= htmlspecialchars($art['title']) ?></h3>
                                <p class="article-excerpt">
                                    <?= htmlspecialchars($art['excerpt']) ?>
                                </p>
                                <a href="article.php?slug=<?= $art['slug'] ?>" class="btn-read mt-auto">
                                    Read Reflection <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<?php render_footer(); ?>