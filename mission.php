<?php
require_once 'header.php';

$extra_head = '
    <style>
        .commitment-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border-bottom: 4px solid var(--terracotta);
            height: 100%;
            transition: var(--transition);
            border-left: 1px solid var(--card-border);
            border-right: 1px solid var(--card-border);
            border-top: 1px solid var(--card-border);
        }
        .commitment-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.08); }
        .commitment-icon { font-size: 2.5rem; color: var(--terracotta); margin-bottom: 1.5rem; display: block; }

        .vision-list li {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            color: var(--text-color);
            display: flex;
            align-items: flex-start;
            gap: 20px;
            line-height: 1.4;
        }
        .vision-list li i { color: var(--sage); font-size: 1.5rem; margin-top: 3px; }

        .belief-section {
            background-color: var(--navy);
            padding: 8rem 0;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .belief-text {
            font-family: "Playfair Display", serif;
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .belief-subtext {
            font-size: 1.25rem;
            opacity: 0.8;
            margin-top: 2rem;
            font-style: italic;
        }

        .philosophy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .manifesto-box {
            border-left: 1px solid var(--card-border);
            padding-left: 3rem;
        }
        
        @media (max-width: 768px) {
            .belief-text { font-size: 2rem; }
            .manifesto-box { border-left: none; padding-left: 0; margin-top: 3rem; }
        }
    </style>
';

render_header("Mission & Vision", "mission", $extra_head);
?>

    <!-- Hero Section -->
    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">The Purpose</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">Transforming Knowledge through Narrative.</h1>
            <p class="lead animate-up opacity-90 mx-auto mb-0" style="max-width: 800px; font-size: 1.4rem;">Relational Lens exists to transform how social work knowledge is created, shared, and mobilized.</p>
        </div>
    </header>

    <main id="main-content" class="container py-5 mt-5">
        <!-- Mission Section -->
        <section class="row g-5 align-items-center mb-5 pb-5 reveal">
            <div class="col-lg-6">
                <span class="text-terracotta fw-bold text-uppercase tracking-wide small mb-3 d-block">Our Mission</span>
                <h2 class="display-4 playfair fw-bold text-navy mb-4">A Form of Ethical Witnessing</h2>
                <p class="lead text-muted mb-4">This platform is a global, peer-reviewed repository of documentary storytelling by and for social workers.</p>
                <p class="text-muted mb-4">Through visual narratives, we seek to humanize complex systems, amplify marginalized voices, and deepen empathy across cultures and communities.</p>
                <div class="p-4 bg-sand rounded-4 mb-4" style="border-left: 5px solid var(--terracotta);">
                    <p class="mb-0 fw-bold text-navy">"We believe that storytelling is not simply a method of communication — it is a form of ethical witnessing, relational practice, and social intervention."</p>
                </div>
                <p class="text-muted">By integrating filmmaking into social work practice, we expand the profession's capacity to engage, document, and transform the world around us.</p>
            </div>
            <div class="col-lg-6">
                <div class="manifesto-box">
                    <h4 class="playfair fw-bold text-navy mb-4">Our Commitment</h4>
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <i class="bi bi-shield-check text-terracotta fs-4"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Dignity-Centered Storytelling</h6>
                                    <p class="small text-muted mb-0">Practicing representation that honors the inherent worth of every individual.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <i class="bi bi-people-fill text-terracotta fs-4"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Relational Accountability</h6>
                                    <p class="small text-muted mb-0">Honoring the sacred bond between the filmmaker and the community.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <i class="bi bi-globe-americas text-terracotta fs-4"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Anti-Oppressive & Decolonial</h6>
                                    <p class="small text-muted mb-0">Challenging dominant narratives and advancing justice through the lens.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3">
                                <i class="bi bi-heart-pulse-fill text-terracotta fs-4"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Stories WITH, not ABOUT</h6>
                                    <p class="small text-muted mb-0">Ensuring communities are active partners in their own narratives.</p>
                                </div>
                            </div>
                        </div>
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

        <!-- Vision Section -->
        <section class="py-5 mb-5 reveal">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1000&auto=format&fit=crop" class="img-fluid rounded-4 shadow-lg" alt="Collaboration">
                </div>
                <div class="col-lg-7 ps-lg-5">
                    <span class="text-sage fw-bold text-uppercase tracking-wide small mb-3 d-block">Our Vision</span>
                    <h2 class="display-4 playfair fw-bold text-navy mb-4">A Collaborative Future</h2>
                    <p class="lead text-muted mb-5">We envision a world where social workers are not only practitioners, but storytellers — bearing witness to human experience in ways that foster collective responsibility.</p>
                    
                    <ul class="list-unstyled vision-list">
                        <li>
                            <i class="bi bi-archive-fill"></i>
                            <div>
                                <span class="fw-bold d-block">A Living Archive</span>
                                <span class="small text-muted">A repository of global social work practice, preserved for future generations.</span>
                            </div>
                        </li>
                        <li>
                            <i class="bi bi-book-half"></i>
                            <div>
                                <span class="fw-bold d-block">A Shared Classroom</span>
                                <span class="small text-muted">A site of learning for students, educators, and communities worldwide.</span>
                            </div>
                        </li>
                        <li>
                            <i class="bi bi-lightning-charge-fill"></i>
                            <div>
                                <span class="fw-bold d-block">A Site of Resistance & Healing</span>
                                <span class="small text-muted">Where stories challenge injustice and inspire transformative action.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <!-- The Core Belief -->
    <section class="belief-section reveal">
        <div class="container">
            <div class="belief-text">
                "Stories do more than inform.<br>
                They connect. They reveal. They transform."
            </div>
            <p class="belief-subtext">And when held with care, they can change the world.</p>
        </div>
        <!-- Decorative background elements -->
        <div style="position: absolute; top: -10%; left: -5%; width: 300px; height: 300px; background: var(--terracotta); filter: blur(150px); opacity: 0.2;"></div>
        <div style="position: absolute; bottom: -10%; right: -5%; width: 300px; height: 300px; background: var(--sage); filter: blur(150px); opacity: 0.2;"></div>
    </section>

    <!-- Final CTA -->
    <section class="container py-5 my-5 reveal text-center">
        <h2 class="playfair fw-bold text-navy mb-4">Join the Movement</h2>
        <p class="text-muted mx-auto mb-5" style="max-width: 600px;">Visual storytelling is an essential form of social work knowledge — equal in value to written scholarship. Become a part of the collective.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-navy text-white px-5 rounded-pill" style="background: var(--navy);">Join the Collective</a>
            <a href="archive.php" class="btn btn-outline-terracotta px-5 rounded-pill" style="border: 2px solid var(--terracotta); color: var(--terracotta);">Explore the Archive</a>
        </div>
    </section>

<?php render_footer(); ?>