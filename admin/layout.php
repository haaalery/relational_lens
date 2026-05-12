<?php
// admin/layout.php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/security.php';

function render_admin_header($title, $active_page) {
    $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Relational Lens Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --navy: #0E3A47;
            --navy-light: #164a5a;
            --terracotta: #C57D54;
            --terracotta-hover: #a8623d;
            --sand: #E5CBAA;
            --sage: #4B8759;
            --teal: #1E5B6A;
            --offwhite: #F8F9FA;
            --transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            
            /* Light Theme */
            --bg-color: #f4f7f8;
            --card-bg: #ffffff;
            --text-color: #2c3e50;
            --text-muted: #6c757d;
            --card-border: rgba(0,0,0,0.06);
            --sidebar-bg: var(--navy);
            --nav-link-color: rgba(255,255,255,0.7);
        }

        [data-theme='dark'] {
            --bg-color: #050f14;
            --card-bg: #0e2a33;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --card-border: rgba(255,255,255,0.08);
            --sidebar-bg: #030a0d;
            --nav-link-color: rgba(255,255,255,0.6);
            --offwhite: #0a191e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
            min-height: 100vh;
        }

        .playfair { font-family: 'Playfair Display', serif; }

        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1050;
            padding: 2.5rem 1.5rem;
            color: white;
            transition: var(--transition);
            border-right: 1px solid var(--card-border);
        }

        .sidebar-brand {
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-link {
            color: var(--nav-link-color) !important;
            padding: 0.9rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--terracotta);
            color: white !important;
            box-shadow: 0 8px 20px rgba(197, 125, 84, 0.3);
        }

        .nav-link i { font-size: 1.2rem; }

        /* Main Content */
        .main-wrapper {
            margin-left: 280px;
            padding: 3rem;
            transition: var(--transition);
        }

        /* Cards */
        .admin-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        [data-theme='dark'] .admin-card {
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        /* Stats */
        .stat-card {
            padding: 2rem;
            border-radius: 24px;
            color: white;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: none;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            top: -20%; right: -10%;
            width: 150px; height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            z-index: 0;
        }

        .stat-card:hover { transform: translateY(-8px); }
        .stat-card h2 { font-weight: 800; position: relative; z-index: 1; }
        .stat-card .label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; opacity: 0.8; position: relative; z-index: 1; }

        /* Tables */
        .table {
            color: var(--text-color);
        }

        .table thead th {
            background-color: transparent;
            border-bottom: 2px solid var(--card-border);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding: 1.25rem 1rem;
        }

        .table tbody td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--card-border);
            vertical-align: middle;
        }

        /* Forms */
        .form-control, .form-select {
            background-color: var(--bg-color);
            border: 1px solid var(--card-border);
            color: var(--text-color);
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--card-bg);
            border-color: var(--terracotta);
            box-shadow: 0 0 0 0.25rem rgba(197, 125, 84, 0.15);
            color: var(--text-color);
        }

        /* Badges & Status */
        .badge {
            padding: 0.6em 1.2em;
            border-radius: 50px;
            font-weight: 600;
        }

        .status-pending { background-color: var(--sand); color: var(--navy); }
        .status-approved { background-color: var(--sage); color: white; }
        .status-rejected { background-color: #e74c3c; color: white; }
        .status-under_review { background-color: var(--teal); color: white; }

        /* Buttons */
        .btn-terracotta { background-color: var(--terracotta); color: white; border: none; padding: 0.8rem 1.8rem; border-radius: 50px; font-weight: 600; transition: var(--transition); }
        .btn-terracotta:hover { background-color: var(--terracotta-hover); color: white; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(197, 125, 84, 0.3); }

        .btn-outline-navy { border: 2px solid var(--navy); color: var(--navy); border-radius: 50px; font-weight: 600; }
        [data-theme='dark'] .btn-outline-navy { border-color: var(--sand); color: var(--sand); }

        .btn-outline-teal { border: 2px solid var(--teal); color: var(--teal); border-radius: 50px; font-weight: 600; }
        [data-theme='dark'] .btn-outline-teal { border-color: var(--teal); color: var(--teal); }

        /* Theme Toggle (Floating) */
        .theme-toggle-admin {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 2000;
            width: 56px;
            height: 56px;
            background: var(--navy);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: var(--transition);
        }

        .theme-toggle-admin:hover { transform: scale(1.1) rotate(15deg); }
        [data-theme='dark'] .theme-toggle-admin { background: var(--terracotta); }
        [data-theme='dark'] .bi-sun-fill { display: block; }
        [data-theme='dark'] .bi-moon-stars-fill { display: none; }
        .bi-sun-fill { display: none; }

        /* Mobile Header */
        .mobile-header {
            display: none;
            background: var(--navy);
            color: white;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1100;
            justify-content: space-between;
            align-items: center;
        }

        [data-theme='dark'] .mobile-header {
            background: #ffffff;
            color: var(--navy);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        [data-theme='dark'] .mobile-header button i {
            color: var(--navy) !important;
        }

        @media (max-width: 992px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; padding: 2rem 1.5rem; }
            .mobile-header { display: flex; }
        }

        .sidebar-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1040;
            display: none;
        }
        .sidebar-overlay.show { display: block; }

        .user-badge {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert { border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }

        /* Utility Colors */
        .text-navy { color: var(--navy) !important; }
        .text-terracotta { color: var(--terracotta) !important; }
        .text-teal { color: var(--teal) !important; }
        .text-sage { color: var(--sage) !important; }

        .bg-navy { background-color: var(--navy) !important; }
        .bg-terracotta { background-color: var(--terracotta) !important; }
        .bg-sage { background-color: var(--sage) !important; }
        .bg-teal { background-color: var(--teal) !important; }
        .bg-sand { background-color: var(--sand) !important; }

        /* Soft Backgrounds */
        .bg-terracotta-soft { background-color: rgba(197, 125, 84, 0.1) !important; }
        .bg-teal-soft { background-color: rgba(30, 91, 106, 0.1) !important; }
        .bg-sage-soft { background-color: rgba(75, 135, 89, 0.1) !important; }
        .bg-navy-soft { background-color: rgba(14, 58, 71, 0.1) !important; }

        [data-theme='dark'] .bg-terracotta-soft { background-color: rgba(197, 125, 84, 0.2) !important; }
        [data-theme='dark'] .bg-teal-soft { background-color: rgba(30, 91, 106, 0.2) !important; }
        [data-theme='dark'] .bg-sage-soft { background-color: rgba(75, 135, 89, 0.2) !important; }
        [data-theme='dark'] .bg-navy-soft { background-color: rgba(14, 58, 71, 0.2) !important; }
    </style>
</head>
<body>

    <div class="mobile-header">
        <h5 class="playfair mb-0">RELATIONAL LENS</h5>
        <button class="btn text-white p-0" id="sidebarToggle">
            <i class="bi bi-list fs-2"></i>
        </button>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <h4 class="playfair fw-bold mb-1">RELATIONAL LENS</h4>
            <p class="small text-white-50 mb-0">Administrative Suite</p>
        </div>
        
        <nav class="nav flex-column">
            <a href="index.php" class="nav-link <?= $active_page == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="queue.php" class="nav-link <?= $active_page == 'queue' ? 'active' : '' ?>">
                <i class="bi bi-clock-fill"></i> Review Queue
            </a>
            <a href="taxonomy.php" class="nav-link <?= $active_page == 'taxonomy' ? 'active' : '' ?>">
                <i class="bi bi-tags-fill"></i> Taxonomy
            </a>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="mt-4 mb-2 ps-3 small text-white-50 text-uppercase fw-bold" style="letter-spacing: 1px;">System</div>
                <a href="superadmin.php" class="nav-link <?= $active_page == 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock-fill"></i> Controls
                </a>
                <a href="users.php" class="nav-link <?= $active_page == 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> User Directory
                </a>
            <?php endif; ?>
            
            <div class="mt-auto pt-5">
                <a href="../index.php" class="nav-link">
                    <i class="bi bi-box-arrow-up-right"></i> Live Site
                </a>
                <a href="../logout.php" class="nav-link text-danger">
                    <i class="bi bi-power"></i> Sign Out
                </a>
            </div>
        </nav>
    </div>

    <div class="main-wrapper">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="playfair fw-bold mb-1"><?= $title ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small mb-0">
                        <li class="breadcrumb-item text-muted">Admin</li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                    </ol>
                </nav>
            </div>
            
            <div class="user-badge d-none d-md-flex shadow-sm">
                <div class="text-end">
                    <div class="fw-bold small"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                    <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700;"><?= $_SESSION['user_role'] ?></div>
                </div>
                <div class="rounded-circle bg-sand text-navy d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                </div>
            </div>
        </header>

        <div class="theme-toggle-admin" id="theme-toggle">
            <i class="bi bi-moon-stars-fill"></i>
            <i class="bi bi-sun-fill"></i>
        </div>
<?php
}

function render_admin_footer() {
?>
    </div> <!-- End main-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Management
            const html = document.documentElement;
            const themeToggle = document.getElementById('theme-toggle');
            
            function setTheme(theme) {
                html.setAttribute('data-theme', theme);
                localStorage.setItem('theme', theme);
            }
            
            // Initialize theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
            
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                setTheme(currentTheme === 'light' ? 'dark' : 'light');
            });

            // Sidebar Toggle (Mobile)
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');
            
            if (toggle) {
                toggle.addEventListener('click', () => {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>
<?php
}
?>