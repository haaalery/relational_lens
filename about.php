<?php
require_once 'header.php';

$extra_head = '
    <style>
        .team-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid var(--card-border);
            transition: var(--transition);
        }
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
        }
        .team-avatar {
            width: 120px;
            height: 120px;
            background-color: var(--sand);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--navy);
        }
        .board-card {
            background: var(--bg-color);
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 4px solid var(--sage);
            border-top: 1px solid var(--card-border);
            border-right: 1px solid var(--card-border);
            border-bottom: 1px solid var(--card-border);
        }
    </style>
';

render_header("About Us", "about", $extra_head);
?>

    <header class="page-header" style="background-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2000&auto=format&fit=crop');">
        <div class="container">
            <span class="section-tag animate-up">The Collective</span>
            <h1 class="display-2 playfair fw-bold mb-4 animate-up">About Relational Lens</h1>
            <p class="lead opacity-90 animate-up mx-auto mb-5" style="max-width: 800px; font-size: 1.4rem;">A project dedicated to the ethical preservation and distribution of social work narratives.</p>
        </div>
    </header>

    <main class="container py-5">
        <!-- Our Story -->
        <section class="row align-items-center py-5 mb-5 reveal">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="playfair fw-bold text-navy mb-4">Born from Practice</h2>
                <p class="text-muted lead mb-4">The Relational Lens collective was founded in 2024 by a group of social work educators and documentary filmmakers who recognized a gap in how practitioners access and engage with global stories.</p>
                <p class="text-muted">Our platform serves as a digital meeting place where the art of filmmaking meets the science of social work, creating a unique space for "Ethical Witnessing."</p>
            </div>
            <div class="col-lg-6 ps-lg-5">
                <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1000&auto=format&fit=crop" class="img-fluid rounded-4 shadow-sm" alt="Our Team">
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

        <!-- The Team -->
        <section class="py-5 mb-5 reveal">
            <div class="text-center mb-5">
                <span class="text-terracotta fw-bold text-uppercase tracking-wide small">The Collective</span>
                <h2 class="playfair fw-bold text-navy display-5">Our Leadership</h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar"><i class="bi bi-person-fill"></i></div>
                        <h4 class="playfair fw-bold mb-1">Dr. Sarah Miller</h4>
                        <p class="text-terracotta small fw-bold mb-3">Founding Director</p>
                        <p class="text-muted small">Specializing in decolonial social work practice and international research.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar"><i class="bi bi-camera-reels-fill"></i></div>
                        <h4 class="playfair fw-bold mb-1">Marcus Thorne</h4>
                        <p class="text-terracotta small fw-bold mb-3">Creative Lead</p>
                        <p class="text-muted small">Award-winning documentary filmmaker focused on social intervention narratives.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="team-card">
                        <div class="team-avatar"><i class="bi bi-journal-check"></i></div>
                        <h4 class="playfair fw-bold mb-1">Elena Rodriguez</h4>
                        <p class="text-terracotta small fw-bold mb-3">Ethics Coordinator</p>
                        <p class="text-muted small">Expert in relational ethics and the protection of vulnerable subjects in media.</p>
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

        <!-- Advisory Board -->
        <section class="py-5 bg-sand rounded-4 p-5 mb-5 reveal">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h3 class="playfair fw-bold text-navy mb-3">Advisory Board</h3>
                    <p class="small text-muted">Our work is guided by an international board of practitioners, indigenous elders, and scholars.</p>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="board-card">
                                <h6 class="fw-bold mb-1">Prof. Kofi Mensah</h6>
                                <p class="small text-muted mb-0">University of Ghana</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="board-card">
                                <h6 class="fw-bold mb-1">Li Wei</h6>
                                <p class="small text-muted mb-0">Asia-Pacific Social Work Collective</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="board-card">
                                <h6 class="fw-bold mb-1">Dr. Amit Patel</h6>
                                <p class="small text-muted mb-0">Institute of Rural Development</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="board-card">
                                <h6 class="fw-bold mb-1">Jane Doe</h6>
                                <p class="small text-muted mb-0">Ethics Review Commission</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php render_footer(); ?>