<?php
/**
 * Relational Lens: Seeding Content Script
 * This script populates the database with the initial "Founding Stories" 
 * and "Scholarship Articles" to bring the site to life.
 */

require_once '../config/db.php';

echo "<h2>Relational Lens: Seeding Founding Content...</h2>";

try {
    // 1. CLEAR DUMMY DATA (Optional - keep user 1 & 2 if they are real admins)
    // We'll just append new ones to be safe.

    $stories = [
        [
            'title' => 'The River\'s Edge: Climate Guardians',
            'slug' => 'rivers-edge-climate-guardians',
            'description' => 'A deep look into the indigenous communities of the Amazon Basin fighting to protect their waterways from industrial pollution. This story explores the intersection of social work and environmental justice.',
            'filmmaker_name' => 'Maria Silva',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Placeholder
            'thumbnail_url' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?q=80&w=1000&auto=format&fit=crop',
            'category_id' => 3, // Climate Justice
            'region_id' => 3,    // Brazil
            'is_featured' => 1,
            'transcript' => '[00:01] Maria: The water used to be clear. [00:15] Now, we fight every day just to drink. Social workers are helping us organize our legal defense.'
        ],
        [
            'title' => 'Healing the Harvest',
            'slug' => 'healing-the-harvest',
            'description' => 'In rural India, farmers are facing an unprecedented mental health crisis. This documentary follows a team of social workers providing "Barefoot Counseling" to prevent suicide and build community resilience.',
            'filmmaker_name' => 'Arjun Prasad',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1000&auto=format&fit=crop',
            'category_id' => 2, // Mental Health
            'region_id' => 6,    // India
            'is_featured' => 1,
            'transcript' => '[00:10] Arjun: We don\'t have hospitals here, so we bring the healing to the fields.'
        ],
        [
            'title' => 'Urban Sanctuary',
            'slug' => 'urban-sanctuary',
            'description' => 'How a small community center in Chicago became a global model for refugee integration. A story of hope, migration, and the "Third Space" of social work intervention.',
            'filmmaker_name' => 'Sarah Jenkins',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1444212477490-ca407925329e?q=80&w=1000&auto=format&fit=crop',
            'category_id' => 1, // Migration
            'region_id' => 5,    // USA
            'is_featured' => 1,
            'transcript' => '[00:05] Sarah: Migration isn\'t just about moving; it\'s about landing.'
        ],
        [
            'title' => 'Threads of Resistance',
            'slug' => 'threads-of-resistance',
            'description' => 'A collective of survivors in Nairobi use traditional weaving to process trauma and achieve economic independence. A powerful testament to the role of art in social work.',
            'filmmaker_name' => 'Koffi Osei',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1488459739036-7c502c3828df?q=80&w=1000&auto=format&fit=crop',
            'category_id' => 5, // Violence & Trauma
            'region_id' => 2,    // Kenya
            'is_featured' => 0,
            'transcript' => '[00:20] Koffi: Every thread is a memory reclaimed.'
        ],
        [
            'title' => 'The Smallest Witness',
            'slug' => 'the-smallest-witness',
            'description' => 'Social workers in the Philippines use mobile technology to protect children in remote islands. This documentary captures the innovative "Lens of Care" approach.',
            'filmmaker_name' => 'Elena Cruz',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?q=80&w=1000&auto=format&fit=crop',
            'category_id' => 6, // Child Welfare
            'region_id' => 1,    // Philippines
            'is_featured' => 0,
            'transcript' => '[00:12] Elena: Technology doesn\'t save people; relationships do.'
        ]
    ];

    $insertStory = $pdo->prepare("INSERT INTO stories 
        (title, slug, description, filmmaker_name, video_url, thumbnail_url, category_id, region_id, status, is_featured, transcript, ethics_consent, approved_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?, ?, 1, NOW())");

    foreach ($stories as $s) {
        $insertStory->execute([
            $s['title'], $s['slug'], $s['description'], $s['filmmaker_name'], 
            $s['video_url'], $s['thumbnail_url'], $s['category_id'], 
            $s['region_id'], $s['is_featured'], $s['transcript']
        ]);
        echo "✅ Story Inserted: " . $s['title'] . "<br>";
    }

    echo "<br><strong>Done! All founding stories are now LIVE.</strong><br>";
    echo "<a href='index.php'>Return to Dashboard</a>";

} catch (Exception $e) {
    die("<br>❌ Error seeding content: " . $e->getMessage());
}
?>