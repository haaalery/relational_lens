<?php
require_once 'config/db.php';

try {
    // Fetch all approved stories with their region coordinates
    $stmt = $pdo->query("SELECT s.title, s.slug, s.thumbnail_url, r.name as region_name, r.lat, r.lng, c.name as category_name, c.icon as category_icon
                         FROM stories s
                         JOIN regions r ON s.region_id = r.id
                         LEFT JOIN categories c ON s.category_id = c.id
                         WHERE s.status = 'approved'");
    $mapStories = $stmt->fetchAll();

    // Fetch categories for the map filter overlay
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $mapStories = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Story Map | Relational Lens</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --navy:       #0E3A47;
            --teal:       #1E5B6A;
            --sage:       #4B8759;
            --sand:       #E5CBAA;
            --terracotta: #C57D54;
            --offwhite:   #F5F5F7;
            --charcoal:   #2C2C2C;
            --muted:      #6B7280;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--offwhite);
            overflow: hidden; /* Prevent body scroll, map is full height */
        }

        /* Navbar Integration */
        .navbar {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 0.8rem 0;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 1001; /* Above Leaflet */
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--navy) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand .logo-icon {
            width: 30px; height: 30px; background-color: var(--navy); border-radius: 50%; display: inline-block;
        }
        .nav-link { color: var(--navy) !important; font-weight: 500; font-size: 0.9rem; }

        /* Map UI */
        #map {
            height: 100vh;
            width: 100%;
            z-index: 1;
        }

        /* Overlay Controls */
        .map-overlay {
            position: absolute;
            top: 90px;
            left: 20px;
            z-index: 1000;
            width: 300px;
        }
        .card-overlay {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .playfair { font-family: 'Playfair Display', serif; }

        /* Popup Styling */
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
        }
        .leaflet-popup-content {
            margin: 0;
            width: 250px !important;
        }
        .popup-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .popup-body {
            padding: 1rem;
        }
        .popup-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--navy);
        }
        .popup-meta {
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 1rem;
        }
        .btn-popup {
            background-color: var(--terracotta);
            color: white;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.4rem 1rem;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        /* Marker Clusters (Simulated with CSS for now) */
        .custom-div-icon {
            background: var(--terracotta);
            border: 2px solid white;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        /* Legend */
        .map-legend {
            position: absolute;
            bottom: 30px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 0.8rem;
        }
        .legend-item { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; }

        @media (max-width: 768px) {
            .map-overlay { width: calc(100% - 40px); top: auto; bottom: 100px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                <span class="logo-icon"></span>
                RELATIONAL LENS
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="archive.php">Archive</a></li>
                    <li class="nav-item"><a class="nav-link active" href="map.php">Map</a></li>
                    <li class="nav-item"><a class="nav-link" href="classroom.php">Classroom</a></li>
                    <li class="nav-item"><a class="nav-link" href="mission.php">Mission</a></li>
                    <li class="nav-item"><a class="nav-link btn-signin ms-lg-3" href="login.php">Sign In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Map Overlay Controls -->
    <div class="map-overlay">
        <div class="card-overlay">
            <h4 class="playfair mb-3">Explore the World</h4>
            <p class="small text-muted mb-4">Discover stories of ethical witnessing and social intervention across the globe.</p>
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Filter by Category</label>
                <select class="form-select form-select-sm" id="categoryFilter">
                    <option value="all">All Challenges</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-bold"><?= count($mapStories) ?> Stories Found</span>
                    <a href="archive.php" class="small text-terracotta fw-bold text-decoration-none">View Archive &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Legend -->
    <div class="map-legend">
        <div class="legend-item">
            <div class="legend-dot" style="background: var(--terracotta);"></div>
            <span>Documentary Film</span>
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background: var(--sage);"></div>
            <span>Community Collective</span>
        </div>
    </div>

    <!-- Leaflet Map -->
    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize Map
        const map = L.map('map', {
            zoomControl: false // Move it to the right
        }).setView([20, 0], 3);

        // Move Zoom control
        L.control.zoom({ position: 'topright' }).addTo(map);

        // Light Theme Tiles (CartoDB Positron)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Map Data from PHP
        const stories = <?= json_encode($mapStories) ?>;
        const markers = [];

        // Function to create markers
        function loadMarkers(filter = 'all') {
            // Clear existing markers
            markers.forEach(m => map.removeLayer(m));
            markers.length = 0;

            stories.forEach(story => {
                if (filter === 'all' || story.category_name === filter) {
                    const marker = L.marker([story.lat, story.lng], {
                        alt: story.title
                    }).addTo(map);

                    marker.bindPopup(`
                        <img src="${story.thumbnail_url || 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000&auto=format&fit=crop'}" class="popup-img">
                        <div class="popup-body">
                            <div class="popup-title">${story.title}</div>
                            <div class="popup-meta">
                                <i class="bi bi-geo-alt me-1"></i> ${story.region_name}<br>
                                <i class="bi bi-tag me-1"></i> ${story.category_name}
                            </div>
                            <a href="story.php?slug=${story.slug}" class="btn-popup">Watch Documentary</a>
                        </div>
                    `);

                    markers.push(marker);
                }
            });
        }

        // Initial Load
        loadMarkers();

        // Filter Logic
        document.getElementById('categoryFilter').addEventListener('change', (e) => {
            loadMarkers(e.target.value);
            
            // If filtering, zoom out to show everything, otherwise stay
            if (e.target.value !== 'all') {
                // Potential logic to zoom to bounds of filtered markers
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            } else {
                map.setView([20, 0], 3);
            }
        });

        // ♿ A11Y Note: Added ARIA alt text to markers. The map supports keyboard navigation.
        // ⚠️ Ethics Note: Markers represent regional contexts, centering the community's geographic heritage.
    </script>
</body>
</html>
