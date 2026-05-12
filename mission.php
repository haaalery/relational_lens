<?php
require_once 'header.php';

$extra_head = '
    <style>
        .commitment-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border-bottom: 4px solid var(--sage);
            height: 100%;
            transition: transform 0.3s ease;
            border-left: 1px solid var(--card-border);
            border-right: 1px solid var(--card-border);
            border-top: 1px solid var(--card-border);
        }
        .commitment-card:hover { transform: translateY(-5px); }
        .commitment-icon { font-size: 2.5rem; color: var(--sage); margin-bottom: 1rem; }

        .vision-list li {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .vision-list li i { color: var(--terracotta); font-size: 1.5rem; }

        .belief-section {
            background-color: var(--sand);
            padding: 6rem 0;
            text-align: center;
            color: var(--navy);
        }
        [data-theme="dark"] .belief-section {
            background-color: var(--navy);
            color: white;
        }
        .belief-text {
            font-family: "Playfair Display", serif;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.4;
            max-width: 800px;
            margin: 0 auto;
        }

        .scholarship-box {
            background-color: var(--navy);
            color: white;
            padding: 4rem;
            border-radius: 30px;
            margin-top: -5rem;
            position: relative;
            z-index: 2;
        }
        [data-theme="dark"] .scholarship-box {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
        }
    </style>
';

render_header("Mission & Vision", "mission", $extra_head);
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">Our Philosophy</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">Stories connect us.</h1>
            <p class="lead animate-up opacity-90 mx-auto" style="max-width: 800px; font-size: 1.4rem;">We believe that the most profound insights into social work come from the lived experiences of those on the ground.</p>
        </div>
    </header>

    <main class="container py-5 mt-5">
        <!-- The Core Commitment -->
        <section class="row g-4 mb-5 pb-5 reveal">
            <div class="col-lg-4">
                <div class="commitment-card">
                    <i class="bi bi-heart-pulse commitment-icon"></i>
                    <h4 class="playfair fw-bold mb-3">Relational Ethics</h4>
                    <p class="text-muted small">We prioritize the relationship between the storyteller and the subject, ensuring every narrative is built on trust, consent, and mutual respect.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="commitment-card">
                    <i class="bi bi-globe-americas commitment-icon"></i>
                    <h4 class="playfair fw-bold mb-3">Global Witnessing</h4>
                    <p class="text-muted small">We strive to document the diverse threads of social work across the globe, honoring the local context while revealing universal commonalities.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="commitment-card">
                    <i class="bi bi-feather commitment-icon"></i>
                    <h4 class="playfair fw-bold mb-3">Critical Reflection</h4>
                    <p class="text-muted small">We use storytelling as a tool for critical reflection, encouraging practitioners to examine their own biases and the structural forces at play.</p>
                </div>
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

        <!-- Our Vision -->
        <section class="row align-items-center py-5 mb-5 reveal">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1000&auto=format&fit=crop" class="img-fluid rounded-4 shadow" alt="Collaboration">
            </div>
            <div class="col-lg-6 ps-lg-5">
                <span class="section-tag">Our Vision</span>
                <h2 class="display-4 playfair fw-bold text-navy mb-4">A Collaborative Future</h2>
                <p class="lead text-muted mb-5">Relational Lens is more than an archive; it's a movement toward a more reflexive and ethically-grounded global social work community.</p>
                
                <ul class="list-unstyled vision-list">
                    <li><i class="bi bi-check-circle-fill"></i> Empowering grassroots storytellers.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Bridging the gap between theory and practice.</li>
                    <li><i class="bi bi-check-circle-fill"></i> Challenging dominant colonial narratives.</li>
                </ul>
            </div>
        </section>
    </main>

    <!-- Section Divider -->
    <div class="section-divider reveal">
        <div class="divider-line"></div>
        <div class="divider-icon">
            <i class="bi bi-diamond-fill"></i>
        </div>
        <div class="divider-line"></div>
    </div>

    <!-- The Core Belief -->
    <section class="belief-section reveal">
        <div class="container">
            <blockquote class="belief-text italic mb-0">
                "To tell a story is to bear witness. To listen is to honor the thread that connects us all."
            </blockquote>
        </div>
    </section>

    <!-- Scholarly Integration -->
    <section class="container pb-5 mb-5 reveal">
        <div class="scholarship-box shadow-lg">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h2 class="playfair fw-bold display-5 mb-4">Bridging to The Gallery</h2>
                    <p class="opacity-75 mb-4">Every documentary story in our archive serves as a catalyst for critical reflection. We invite practitioners and researchers to contribute scholarly articles that deepen our understanding of these narratives.</p>
                    <a href="gallery.php" class="btn btn-terracotta rounded-pill px-5 py-3">Explore Scholarly Reflections</a>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <i class="bi bi-journal-text display-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </section>

<?php render_footer(); ?>