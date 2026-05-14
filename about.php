<?php
require_once 'header.php';

$extra_head = '
    <style>
        .value-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid var(--card-border);
            height: 100%;
            transition: var(--transition);
        }
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
        }
        .value-icon {
            font-size: 3rem;
            color: var(--terracotta);
            margin-bottom: 1.5rem;
            display: block;
        }
        .bio-section {
            border-left: 4px solid var(--sage);
            padding-left: 2rem;
            margin-top: 2rem;
        }
        .quote-box {
            font-family: "Playfair Display", serif;
            font-size: 1.5rem;
            font-style: italic;
            color: var(--navy);
            line-height: 1.4;
            margin-bottom: 2rem;
            position: relative;
        }
        .quote-box::before {
            content: "“";
            font-size: 4rem;
            color: var(--sand);
            position: absolute;
            top: -20px;
            left: -30px;
            opacity: 0.5;
        }
    </style>
';

render_header("About Relational Lens", "about", $extra_head);
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">The Collective</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">Bearing Witness Together.</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-0" style="max-width: 800px; font-size: 1.4rem;">Relational Lens is a collaborative initiative dedicated to the ethical preservation and distribution of social work narratives.</p>
        </div>
    </header>

    <main id="main-content" class="container py-5">
        <!-- The Why -->
        <section class="row align-items-center py-5 mb-5 reveal">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="text-terracotta fw-bold text-uppercase tracking-wide small mb-3 d-block">Our Origin</span>
                <h2 class="playfair fw-bold text-navy mb-4">Expanding the Profession's Capacity</h2>
                <p class="text-muted lead mb-4">We imagine a future where visual storytelling is recognized as a legitimate and essential form of social work knowledge — equal in value to written scholarship.</p>
                <p class="text-muted">By integrating filmmaking into practice, we go beyond simple communication. We engage in <strong>Relational Practice</strong> — a site of resistance and healing where local realities connect to global movements.</p>
                <div class="bio-section">
                    <p class="small text-muted mb-0">Our platform ensures that stories are created <strong>with</strong>, not <strong>about</strong>, communities — honoring the dignity and accountability that every human experience deserves.</p>
                </div>
            </div>
            <div class="col-lg-6 ps-lg-5">
                <div class="p-2 border rounded-4 shadow-sm">
                    <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1000&auto=format&fit=crop" class="img-fluid rounded-3" alt="Collaborative Storytelling">
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider reveal">
            <div class="divider-line"></div>
            <div class="divider-icon"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i><i class="bi bi-circle-fill"></i><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i></div>
            <div class="divider-line"></div>
        </div>

        <!-- Core Values -->
        <section class="py-5 mb-5 reveal">
            <div class="text-center mb-5">
                <span class="text-terracotta fw-bold text-uppercase tracking-wide small">Our Philosophy</span>
                <h2 class="playfair fw-bold text-navy display-5">What Guides Us</h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="bi bi-heart-pulse-fill value-icon"></i>
                        <h4 class="playfair fw-bold mb-3">Dignity-Centered</h4>
                        <p class="text-muted small">We prioritize the inherent worth of every individual, ensuring their story is held with the utmost care and respect.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="bi bi-people-fill value-icon"></i>
                        <h4 class="playfair fw-bold mb-3">Relational</h4>
                        <p class="text-muted small">We honor the sacred accountability between the filmmaker and the community, fostering long-term trust.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="bi bi-shield-lock-fill value-icon"></i>
                        <h4 class="playfair fw-bold mb-3">Anti-Oppressive</h4>
                        <p class="text-muted small">We use the lens to challenge structural injustices and amplify marginalized voices that are often silenced.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider reveal">
            <div class="divider-line"></div>
            <div class="divider-icon"><i class="bi bi-diamond-fill"></i></div>
            <div class="divider-line"></div>
        </div>

        <!-- The Belief Block -->
        <section class="py-5 mb-5 reveal">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="quote-box">
                        "Stories do more than inform. They connect. They reveal. They transform. And when held with care, they can change the world."
                    </div>
                    <p class="text-muted">This belief is at the heart of everything we do. It drives our commitment to creating a living archive that inspires action and fosters global empathy.</p>
                    <a href="mission.php" class="btn btn-terracotta rounded-pill px-5 py-3 mt-4">View Full Mission & Vision</a>
                </div>
            </div>
        </section>
    </main>

<?php render_footer(); ?>