<?php
require_once 'header.php';

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

$extra_head = '
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden; 
        }
        #map {
            height: calc(100vh - 80px);
            width: 100%;
            z-index: 1;
        }
        .map-overlay {
            position: absolute;
            top: 100px;
            left: 20px;
            z-index: 1000;
            width: 300px;
        }
        .map-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid var(--card-border);
        }
        .map-legend {
            position: absolute;
            bottom: 30px;
            right: 20px;
            z-index: 1000;
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 0.8rem;
            color: var(--text-color);
            border: 1px solid var(--card-border);
        }
        .legend-item { display: flex; align-items: center; gap: 8px; margin-bottom: 5px; }
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; }
        
        @media (max-width: 768px) {
            .map-overlay { width: calc(100% - 40px); top: auto; bottom: 100px; }
        }
    </style>
';

render_header("Global Story Map", "map", $extra_head);
?>

    <!-- Overlay Controls -->
    <div class="map-overlay animate-up">
        <div class="map-card">
            <h4 class="playfair fw-bold text-navy mb-3">Explore the Archive</h4>
            <p class="small text-muted mb-4">Discover stories of practice and resilience across the globe.</p>
            
            <div class="mb-3">
                <label class="form-label small fw-bold">Filter by Challenge</label>
                <select id="category-filter" class="form-select form-select-sm rounded-pill">
                    <option value="all">All Challenges</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-grid">
                <a href="archive.php" class="btn btn-sm btn-outline-navy rounded-pill">View List Archive</a>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="map-legend reveal">
        <h6 class="fw-bold mb-2">Legend</h6>
        <div class="legend-item">
            <div class="legend-dot" style="background: var(--terracotta);"></div>
            <span>Documentary Story</span>
        </div>
        <div class="legend-item">
            <div class="legend-dot" style="background: var(--sage);"></div>
            <span>Community Collective</span>
        </div>
    </div>

    <!-- Leaflet Map -->
    <div id="map"></div>

<?php
$map_scripts = '
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const theme = document.documentElement.getAttribute("data-theme") || "light";
        const map = L.map("map", {
            zoomControl: false,
            minZoom: 2.5,
            maxBounds: [[-85, -180], [85, 180]],
            maxBoundsViscosity: 1.0
        }).setView([20, 0], 3);

        L.control.zoom({ position: "topright" }).addTo(map);

        const tileUrl = theme === "dark" 
            ? "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
            : "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";

        L.tileLayer(tileUrl, {
            attribution: "&copy; OpenStreetMap contributors &copy; CARTO"
        }).addTo(map);

        const stories = ' . json_encode($mapStories) . ';
        const markers = [];

        stories.forEach(s => {
            if(s.lat && s.lng) {
                const marker = L.circleMarker([s.lat, s.lng], {
                    radius: 8,
                    fillColor: "#C57D54",
                    color: "#fff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);

                marker.bindPopup(`
                    <div style="width: 200px;">
                        <img src="${s.thumbnail_url || "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400"}" 
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        <h6 class="fw-bold mb-1">${s.title}</h6>
                        <p class="small text-muted mb-2">${s.region_name} | ${s.category_name}</p>
                        <a href="story.php?slug=${s.slug}" class="btn btn-sm btn-navy text-white w-100 rounded-pill" style="background: #0E3A47;">Watch Story</a>
                    </div>
                `);
                
                marker.category = s.category_name;
                markers.push(marker);
            }
        });

        document.getElementById("category-filter").addEventListener("change", function(e) {
            const val = e.target.value;
            markers.forEach(m => {
                if(val === "all" || m.category === val) {
                    map.addLayer(m);
                } else {
                    map.removeLayer(m);
                }
            });
        });
    </script>
';
render_footer($map_scripts);
?>