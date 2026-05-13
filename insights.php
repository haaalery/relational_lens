<?php
require_once 'header.php';

// Initialize variables
$stats = [
    'total_stories' => 0,
    'total_articles' => 0,
    'total_regions' => 0,
    'total_users' => 0
];
$region_stats = [];
$category_stats = [];
$latest_articles = [];

try {
    // 1. Basic Stats
    $stats['total_stories'] = $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'approved'")->fetchColumn();
    $stats['total_articles'] = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'approved'")->fetchColumn();
    $stats['total_regions'] = $pdo->query("SELECT COUNT(DISTINCT region_id) FROM stories WHERE status = 'approved'")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // 2. Stories by Region
    $region_stats = $pdo->query("
        SELECT r.name, COUNT(s.id) as count 
        FROM regions r 
        LEFT JOIN stories s ON r.id = s.region_id AND s.status = 'approved'
        GROUP BY r.id 
        HAVING count > 0
        ORDER BY count DESC
    ")->fetchAll();

    // 3. Stories by Category (Grand Challenge)
    $category_stats = $pdo->query("
        SELECT c.name, COUNT(s.id) as count 
        FROM categories c 
        LEFT JOIN stories s ON c.id = s.category_id AND s.status = 'approved'
        GROUP BY c.id 
        HAVING count > 0
        ORDER BY count DESC
    ")->fetchAll();

    // 4. Latest Articles (for the bottom section)
    $latest_articles = $pdo->query("SELECT * FROM articles WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3")->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
}

$extra_head = '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-num { font-size: 3.5rem; font-weight: 700; color: var(--terracotta); line-height: 1; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.85rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; color: var(--muted); }
        
        .chart-container {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            height: 100%;
        }
    </style>
';

render_header("Archive Insights", "insights", $extra_head);
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2000&auto=format&fit=crop');">
        <div class="container text-center">
            <span class="section-tag animate-up">The Insights Layer</span>
            <h1 class="display-1 playfair fw-bold mb-4 animate-up">Impact & Reach</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 800px; font-size: 1.4rem;">
                Visualizing the global threads of social work narratives and scholarly reflections.
            </p>
        </div>
    </header>

    <main id="main-content" class="container py-5">
        
        <!-- Key Metrics -->
        <div class="row g-4 mb-5 reveal">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_stories'] ?></div>
                    <div class="stat-label">Stories</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_articles'] ?></div>
                    <div class="stat-label">Articles</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_regions'] ?></div>
                    <div class="stat-label">Regions</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-num"><?= $stats['total_users'] ?></div>
                    <div class="stat-label">Members</div>
                </div>
            </div>
        </div>

        <!-- Visualizations -->
        <div class="row g-4 mb-5 pb-5">
            <div class="col-lg-7 reveal">
                <div class="chart-container">
                    <h4 class="playfair fw-bold mb-4">Global Reach by Region</h4>
                    <canvas id="regionChart"></canvas>
                </div>
            </div>
            <div class="col-lg-5 reveal" data-delay="200">
                <div class="chart-container">
                    <h4 class="playfair fw-bold mb-4">Thematic Distribution</h4>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Section Divider -->
        <div class="section-divider reveal">
            <div class="divider-line"></div>
            <div class="divider-icon"><i class="bi bi-journal-richtext"></i></div>
            <div class="divider-line"></div>
        </div>

        <!-- Latest Reflections -->
        <section class="py-5 reveal">
            <div class="text-center mb-5">
                <span class="text-terracotta fw-bold text-uppercase tracking-wide small">Scholarly Dialogue</span>
                <h2 class="playfair fw-bold text-navy display-5">Latest Reflections</h2>
            </div>

            <div class="row g-4">
                <?php if (empty($latest_articles)): ?>
                    <div class="col-12 text-center text-muted py-4">No reflections published yet.</div>
                <?php else: ?>
                    <?php foreach ($latest_articles as $article): ?>
                        <div class="col-lg-4 col-md-6">
                            <article class="article-card h-100">
                                <div class="card-img-wrapper">
                                    <img src="<?= htmlspecialchars($article['image_url'] ?: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                                </div>
                                <div class="article-body">
                                    <h4 class="article-title small"><a href="article.php?slug=<?= $article['slug'] ?>" class="text-decoration-none text-navy"><?= htmlspecialchars($article['title']) ?></a></h4>
                                    <p class="article-excerpt small mb-3">
                                        <?= htmlspecialchars($article['excerpt']) ?>
                                    </p>
                                    <a href="article.php?slug=<?= $article['slug'] ?>" class="btn-read mt-auto" style="font-size: 0.75rem;">Read Full Insight &rarr;</a>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="text-center mt-5">
                <a href="gallery.php" class="btn btn-outline-navy rounded-pill px-5">Browse All Reflections</a>
            </div>
        </section>

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
    </main>

<?php 
$regionNames = json_encode(array_column($region_stats, 'name'));
$regionCounts = json_encode(array_column($region_stats, 'count'));
$catNames = json_encode(array_column($category_stats, 'name'));
$catCounts = json_encode(array_column($category_stats, 'count'));

$extra_scripts = "
<script>
    // Access theme colors from CSS variables
    const themeColor = '#C57D54';
    const navyColor = '#0E3A47';
    const textColor = '#666';

    // Region Chart (Bar)
    new Chart(document.getElementById('regionChart'), {
        type: 'bar',
        data: {
            labels: $regionNames,
            datasets: [{
                label: 'Stories',
                data: $regionCounts,
                backgroundColor: themeColor,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: { 
                    backgroundColor: navyColor,
                    titleFont: { family: 'Playfair Display' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { display: false }, ticks: { color: textColor } }
            }
        }
    });

    // Category Chart (Doughnut)
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: $catNames,
            datasets: [{
                data: $catCounts,
                backgroundColor: [themeColor, '#5E7D73', '#9BB1A1', '#E8D5B7', navyColor],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor, padding: 20, usePointStyle: true, font: { size: 11 } }
                }
            },
            cutout: '70%'
        }
    });
</script>
";
render_footer($extra_scripts); 
?>