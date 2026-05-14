<?php
require_once 'header.php';

// Fetch EXACTLY 3 Featured Stories
try {
    $featuredStmt = $pdo->prepare("SELECT s.*, r.name as region_name, c.name as category_name 
                                    FROM stories s
                                    LEFT JOIN regions r ON s.region_id = r.id 
                                    LEFT JOIN categories c ON s.category_id = c.id 
                                    WHERE s.status = 'approved' AND s.is_featured = 1 
                                    ORDER BY s.created_at DESC 
                                    LIMIT 3");
    $featuredStmt->execute();
    $featuredStories = $featuredStmt->fetchAll();

    // Fetch Map Data & Categories
    $mapStmt = $pdo->query("SELECT s.title, s.slug, r.lat, r.lng, r.name as region FROM stories s JOIN regions r ON s.region_id = r.id WHERE s.status = 'approved'");
    $mapData = $mapStmt->fetchAll();
    
    $categoryStmt = $pdo->query("SELECT * FROM categories");
    $categories = $categoryStmt->fetchAll();

    // NEW: Fetch Articles for Insights & Reflections
    $articleStmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC LIMIT 3");
    $articles = $articleStmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $featuredStories = [];
    $mapData = [];
    $categories = [];
    $articles = [];
}

$leaflet_css = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />';
render_header("Home", "home", $leaflet_css);
?>

<section id="main-content" class="hero" style="background: url('relationalb.png') center top / cover no-repeat; position: relative;"> 
    <div class="container" style="z-index: 2; position: relative; height: 100%; display: flex; align-items: center;"> 
        
<div class="hero-content text-start pt-5" style="max-width: 700px; text-shadow: 0px 4px 20px rgba(0,0,0,0.6);"> 
    <span class="text-terracotta fw-bold tracking-wide text-uppercase mb-3 d-block animate-up">A Form of Ethical Witnessing</span>
    <h1 class="display-2 playfair fw-bold mb-4 animate-up" style="line-height: 1.1;">Documenting the global threads of social work.</h1>
    <p class="lead mb-5 animate-up opacity-90" style="font-size: 1.25rem;">Relational Lens is a collaborative archive of documentary films and critical reflections exploring the diverse landscapes of global social work practice.</p>
    
    <div class="d-flex gap-3 animate-up">
        <a href="archive.php" class="btn btn-terracotta btn-lg px-5 rounded-pill">Explore Archive</a>
        <a href="mission.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Our Mission</a>
    </div>
</div>
    </div>
    <!-- Subtle Gradient Overlay -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, rgba(14,58,71,0.8) 0%, rgba(14,58,71,0.2) 100%); z-index: 1;"></div>
</section>

    <!-- Main Content -->
    <main class="container py-5 mt-5">
        
        <!-- Featured Stories Section -->
        <section id="featured" class="mb-5 pb-5">
            <div class="d-flex justify-content-between align-items-end mb-5 reveal">
                <div>
                    <h2 class="display-5 playfair fw-bold text-navy mb-0">Featured Stories</h2>
                    <p class="text-muted mt-2">Hand-picked narratives from our global collective.</p>
                </div>
                <a href="archive.php" class="text-terracotta fw-bold text-decoration-none mb-2">View All Stories &rarr;</a>
            </div>

            <div class="row g-4">
                <?php if (empty($featuredStories)): ?>
                    <div class="col-12 text-center py-5 reveal">
                        <i class="bi bi-camera-reels display-1 text-muted opacity-25 mb-4 d-block"></i>
                        <h3 class="playfair fw-bold text-muted">Awaiting Narratives</h3>
                        <p class="text-muted">New featured stories will be appearing here shortly. Explore the <a href="archive.php" class="text-terracotta fw-bold">full archive</a>.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredStories as $story): ?>
                        <div class="col-md-4 reveal">
                            <div class="story-card h-100 d-flex flex-column">
                                <div class="card-img-wrapper">
                                    <img src="<?= htmlspecialchars($story['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop') ?>" alt="<?= htmlspecialchars($story['title']) ?>" loading="lazy">
                                    <a href="story.php?slug=<?= $story['slug'] ?>" class="play-overlay"><i class="bi bi-play-circle"></i></a>
                                </div>
                                <div class="p-4 flex-grow-1 d-flex flex-column">
                                    <div class="article-meta mb-2">
                                        <span><?= htmlspecialchars($story['region_name'] ?: 'Global') ?></span>
                                        <span class="mx-1">&bull;</span>
                                        <span><?= htmlspecialchars($story['category_name'] ?: 'Documentary') ?></span>
                                    </div>
                                    <h4 class="story-title">
                                        <a href="story.php?slug=<?= $story['slug'] ?>" class="text-decoration-none text-navy">
                                            <?= htmlspecialchars($story['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="story-description"><?= htmlspecialchars(substr($story['description'], 0, 120)) ?>...</p>
                                    <a href="story.php?slug=<?= $story['slug'] ?>" class="btn-read mt-auto">Watch Story &rarr;</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

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

        <!-- Interactive Map Preview -->
        <section id="global-map" class="py-5 mb-5 reveal">
            <div class="bg-sand p-5 rounded-4 shadow-sm" style="min-height: 400px;">
                <div class="row align-items-center">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <h2 class="playfair fw-bold text-navy display-6 mb-4">A Global Perspective</h2>
                        <p class="mb-4">Navigate through stories by geography. Our archive spans continents, revealing the local nuances and global commonalities of social work.</p>
                        <a href="map.php" class="btn btn-navy text-white px-4 rounded-pill" style="background-color: var(--navy);">Open Global Map</a>
                    </div>
                    <div class="col-lg-8">
                        <div id="map" class="rounded-4 shadow-sm" style="height: 400px; background: #eee;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider reveal">
            <div class="divider-line"></div>
            <div class="divider-icon">
                <i class="bi bi-diamond-fill"></i>
            </div>
            <div class="divider-line"></div>
        </div>

        <!-- Insights & Reflections (Articles) -->
        <section id="insights" class="py-5">
            <div class="text-center mb-5 reveal">
                <h2 class="display-5 playfair fw-bold text-navy">The Gallery</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">Critical reflections and scholarly insights from practitioners and researchers.</p>
            </div>

            <div class="row g-4">
                <?php if (empty($articles)): ?>
                    <div class="col-12 text-center py-5 reveal">
                        <i class="bi bi-journal-text display-1 text-muted opacity-25 d-block mb-4"></i>
                        <h3 class="playfair fw-bold text-muted">Awaiting Reflections</h3>
                        <p class="text-muted mb-4">No articles found. Be the first to <a href="submit_article.php" class="text-terracotta fw-bold">contribute</a>.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="col-md-4 reveal">
                            <div class="article-card h-100">
                                <div class="card-img-wrapper">
                                    <a href="article.php?slug=<?= $article['slug'] ?>">
                                        <img src="<?= htmlspecialchars($article['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="article-body">
                                    <div class="article-meta">
                                        <span><?= date('M d, Y', strtotime($article['created_at'])) ?></span>
                                    </div>
                                    <h4 class="article-title">
                                        <a href="article.php?slug=<?= $article['slug'] ?>" class="text-decoration-none text-navy">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="article-excerpt"><?= htmlspecialchars(substr($article['excerpt'], 0, 100)) ?>...</p>
                                    <a href="article.php?slug=<?= $article['slug'] ?>" class="btn-read mt-auto">Read Reflection &rarr;</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

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

        <!-- Join the Movement -->
        <section id="action-cards" class="pt-5 mt-5 border-top reveal">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="p-4 glass-card rounded-4 shadow-sm h-100 d-flex flex-column">
                        <h5 class="playfair text-navy fw-bold mb-3">For Teaching & Practice</h5>
                        <p class="text-muted small mb-4">Access curated article collections and discussion guides in The Gallery.</p>
                        <a href="gallery.php" class="btn btn-navy text-white w-100 rounded-pill mt-auto" style="background-color: var(--navy);">Browse The Gallery</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="p-4 glass-card rounded-4 shadow-sm h-100">
                        <h5 class="playfair text-navy fw-bold mb-4">Why Storytelling?</h5>
                        <ul class="list-unstyled why-list text-muted small mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle fs-6 text-secondary me-2"></i> Ethical Witnessing</li>
                            <li class="mb-2"><i class="bi bi-check2-circle fs-6 text-secondary me-2"></i> Relational Practice</li>
                            <li class="mb-2"><i class="bi bi-check2-circle fs-6 text-secondary me-2"></i> Social Intervention</li>
                            <li class="mb-0"><i class="bi bi-check2-circle fs-6 text-secondary me-2"></i> Deepening Empathy</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="p-4 rounded-4 shadow-sm h-100 d-flex flex-column" style="background-color: var(--navy); color: white;">
                        <h5 class="playfair fw-bold mb-3 text-white">Your story matters</h5>
                        <p class="small opacity-75 mb-4">Contribute to the global archive of social work practice.</p>
                        <a href="submit.php" class="btn btn-terracotta w-100 rounded-pill mt-auto">Submit Now</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="p-4 glass-card rounded-4 shadow-sm h-100 d-flex flex-column">
                        <h5 class="playfair text-navy fw-bold mb-3">A Global Collective</h5>
                        <p class="small text-muted mb-4">Join 500+ filmmakers and practitioners.</p>
                        <a href="register.php" class="text-navy fw-bold text-decoration-none mt-auto">Become a Member &rarr;</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php 
$map_scripts = '
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const theme = document.documentElement.getAttribute("data-theme") || "light";
        const map = L.map("map").setView([20, 0], 2);
        
        const tileUrl = theme === "dark" 
            ? "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
            : "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";
            
        L.tileLayer(tileUrl, { maxZoom: 19 }).addTo(map);
        
        const mapData = ' . json_encode($mapData) . ';
        mapData.forEach(p => {
            if(p.lat && p.lng) L.marker([p.lat, p.lng]).addTo(map).bindPopup(`<b>${p.title}</b><br><a href="story.php?slug=${p.slug}">Watch</a>`);
        });
    </script>
';
render_footer($map_scripts); 
?>