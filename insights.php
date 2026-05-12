<?php
require_once 'header.php';

try {
    $stmt = $pdo->query("SELECT * FROM articles WHERE status = 'approved' ORDER BY created_at DESC");
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $articles = [];
}

render_header("Insights & Reflections", "insights");
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1455390582262-044cdead277a?q=80&w=2000&auto=format&fit=crop');">
        <div class="container text-center">
            <span class="section-tag animate-up">Our Philosophy</span>
            <h1 class="display-1 playfair fw-bold mb-4 animate-up">Insights & Reflections</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 700px; font-size: 1.5rem;">
                Critical dialogues on ethics, advocacy, and the power of narrative in global social work.
            </p>
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

    <main class="container py-5 mt-4">
        <div class="row g-4 reveal">
            <?php if (empty($articles)): ?>
                <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm border border-dashed">
                    <i class="bi bi-journal-text display-1 text-muted mb-3 d-block"></i>
                    <h3 class="playfair">No reflections found</h3>
                    <p class="text-muted">We're currently preparing new insights. Please check back soon.</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="article-card h-100">
                            <div class="card-img-wrapper">
                                <span class="category-badge">Reflection</span>
                                <img src="<?= htmlspecialchars($article['image_url'] ?: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                            </div>
                            <div class="article-body">
                                <div class="article-meta"><?= date('F j, Y', strtotime($article['created_at'])) ?></div>
                                <h4 class="article-title"><a href="article.php?slug=<?= $article['slug'] ?>" class="text-decoration-none text-navy"><?= htmlspecialchars($article['title']) ?></a></h4>
                                <p class="article-excerpt">
                                    <?= htmlspecialchars($article['excerpt']) ?>
                                </p>
                                <a href="article.php?slug=<?= $article['slug'] ?>" class="btn-read mt-auto">Read Full Insight &rarr;</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Section Divider -->
    <div class="section-divider reveal">
        <div class="divider-line"></div>
        <div class="divider-icon">
            <i class="bi bi-diamond-fill"></i>
        </div>
        <div class="divider-line"></div>
    </div>

    <!-- CTA Section -->
    <section class="bg-offwhite py-5 reveal">
        <div class="container py-4 text-center">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <h2 class="playfair mb-4">Have a perspective to share?</h2>
                    <p class="text-muted mb-5">We welcome critical reflections and advocacy pieces from practitioners, scholars, and community members.</p>
                    <a href="submit_article.php" class="btn btn-terracotta btn-lg px-5 rounded-pill">Submit a Reflection</a>
                </div>
            </div>
        </div>
    </section>

<?php render_footer(); ?>