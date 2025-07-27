<?php 
/**
 * Template Mahasiswa Sederhana - STK Santo Yakobus
 * Menggantikan template kompleks dengan wrapper yang simple dan self-contained
 * Tidak memerlukan Bootstrap, jQuery, atau dependencies eksternal
 */
$app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus">
    <title>SIM-TA STK Yakobus - <?= $title ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" type="image/png">
    
    <!-- CSS Internal - Menggantikan Bootstrap & Dependencies -->
    <style>
        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8f9fe;
            color: #525f7f;
            line-height: 1.6;
        }
        
        /* ===== LAYOUT SYSTEM ===== */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* ===== SIDEBAR MAHASISWA ===== */
        .mahasiswa-sidebar {
            width: 250px;
            background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand img {
            width: 45px;
            height: 45px;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-brand h5 {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 1rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.15s ease;
            margin: 0 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(2px);
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        /* ===== MAIN CONTENT AREA ===== */
        .main-content {
            flex: 1;
            margin-left: 250px;
            background: #f8f9fe;
        }
        
        /* ===== HEADER MAHASISWA ===== */
        .main-header {
            background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
            padding: 2rem 0 4rem;
            position: relative;
            overflow: hidden;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-info h1 {
            color: white;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .header-breadcrumb {
            color: rgba(255,255,255,0.8);
            font-size: 0.875rem;
        }
        
        .header-breadcrumb a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }
        
        .header-breadcrumb a:hover {
            color: white;
        }
        
        /* Profile Header */
        .header-profile {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .header-profile:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }
        
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.75rem;
            border: 2px solid rgba(255,255,255,0.3);
            object-fit: cover;
        }
        
        .profile-details h6 {
            color: white;
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .profile-details small {
            color: rgba(255,255,255,0.8);
            font-size: 0.75rem;
        }
        
        /* ===== CONTENT WRAPPER ===== */
        .content-wrapper {
            margin-top: -3rem;
            padding: 0 1.5rem 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            z-index: 10;
        }
        
        /* ===== CARD SYSTEM ===== */
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #32325d;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* ===== BUTTON SYSTEM ===== */
        .btn {
            display: inline-block;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            text-align: center;
            font-weight: 600;
        }
        
        .btn-primary {
            background: #5e72e4;
            border-color: #5e72e4;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4c63d2;
            border-color: #4c63d2;
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        
        .btn-success {
            background: #2dce89;
            border-color: #2dce89;
            color: white;
        }
        
        .btn-warning {
            background: #fb6340;
            border-color: #fb6340;
            color: white;
        }
        
        .btn-info {
            background: #11cdef;
            border-color: #11cdef;
            color: white;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }
        
        /* ===== PROGRESS BAR ===== */
        .progress-wrapper {
            margin-bottom: 1.5rem;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .progress-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #32325d;
        }
        
        .progress-percentage {
            font-size: 0.875rem;
            color: #8898aa;
        }
        
        .progress {
            height: 8px;
            background: #e9ecef;
            border-radius: 0.25rem;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
            border-radius: 0.25rem;
            transition: width 0.6s ease;
        }
        
        /* ===== ALERT SYSTEM ===== */
        .alert {
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            position: relative;
        }
        
        .alert-success {
            color: #155724;
            background: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert-warning {
            color: #856404;
            background: #fff3cd;
            border-color: #ffeaa7;
        }
        
        .alert-info {
            color: #0c5460;
            background: #d1ecf1;
            border-color: #bee5eb;
        }
        
        /* ===== DROPDOWN SYSTEM ===== */
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid rgba(0,0,0,0.15);
            border-radius: 0.5rem;
            box-shadow: 0 50px 100px rgba(50, 50, 93, 0.1), 0 15px 35px rgba(50, 50, 93, 0.15), 0 5px 15px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            color: #212529;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #16181b;
        }
        
        .dropdown-item i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 0;
            margin: 0.5rem 0;
            border-top: 1px solid #e9ecef;
        }
        
        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 991.98px) {
            .mahasiswa-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .mahasiswa-sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: #5e72e4;
                color: white;
                border: none;
                padding: 0.5rem;
                border-radius: 0.375rem;
                cursor: pointer;
            }
            
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .content-wrapper {
                padding: 0 1rem 2rem;
            }
        }
        
        .mobile-toggle {
            display: none;
        }
        
        /* ===== UTILITY CLASSES ===== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0 !important; }
        .mb-1 { margin-bottom: 0.25rem !important; }
        .mb-2 { margin-bottom: 0.5rem !important; }
        .mb-3 { margin-bottom: 1rem !important; }
        .mt-2 { margin-top: 0.5rem !important; }
        .mt-3 { margin-top: 1rem !important; }
        .p-3 { padding: 1rem !important; }
        .d-flex { display: flex !important; }
        .align-items-center { align-items: center !important; }
        .justify-content-between { justify-content: space-between !important; }
        
        /* ===== GRID SYSTEM ===== */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -0.75rem;
            margin-left: -0.75rem;
        }
        
        .col, .col-md-6, .col-lg-4, .col-xl-3 {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .col { flex: 1; }
        .col-md-6 { flex: 0 0 50%; max-width: 50%; }
        .col-lg-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
        .col-xl-3 { flex: 0 0 25%; max-width: 25%; }
        
        @media (max-width: 767.98px) {
            .col-md-6, .col-lg-4, .col-xl-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="app-wrapper">
        <!-- Sidebar Mahasiswa -->
        <nav class="mahasiswa-sidebar" id="sidebarMahasiswa">
            <div class="sidebar-brand">
                <img src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" alt="Logo STK Yakobus">
                <h5>SIM-TA<br>STK Yakobus</h5>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/dashboard" class="nav-link <?= ($this->uri->segment(2) == 'dashboard') ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/proposal" class="nav-link <?= ($this->uri->segment(2) == 'proposal') ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Usulan Proposal</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/bimbingan" class="nav-link <?= ($this->uri->segment(2) == 'bimbingan') ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Bimbingan</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/seminar" class="nav-link <?= ($this->uri->segment(2) == 'seminar') ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Seminar</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/penelitian" class="nav-link <?= ($this->uri->segment(2) == 'penelitian') ? 'active' : '' ?>">
                        <i class="fas fa-search"></i>
                        <span>Penelitian</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/publikasi" class="nav-link <?= ($this->uri->segment(2) == 'publikasi') ? 'active' : '' ?>">
                        <i class="fas fa-globe"></i>
                        <span>Publikasi</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= base_url() ?>mahasiswa/profil" class="nav-link <?= ($this->uri->segment(2) == 'profil') ? 'active' : '' ?>">
                        <i class="fas fa-user"></i>
                        <span>Profil Saya</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-container">
                    <div class="header-info">
                        <h1><?= $title ?></h1>
                        <nav class="header-breadcrumb">
                            <a href="<?= base_url() ?>mahasiswa/dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                            <?php if ($this->uri->segment(2) != 'dashboard'): ?>
                                <span> / <?= ucfirst($this->uri->segment(2)) ?></span>
                            <?php endif; ?>
                        </nav>
                    </div>
                    
                    <div class="dropdown">
                        <div class="header-profile" onclick="toggleProfileDropdown()">
                            <img src="<?= base_url() ?>cdn/img/avatar/<?= $this->session->userdata('foto') ?: 'default.png' ?>" 
                                 alt="Avatar" class="profile-avatar">
                            <div class="profile-details">
                                <h6><?= $this->session->userdata('nama') ?></h6>
                                <small><?= $this->session->userdata('nim') ?></small>
                            </div>
                            <i class="fas fa-chevron-down" style="margin-left: 0.5rem; font-size: 0.75rem;"></i>
                        </div>
                        
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="<?= base_url() ?>mahasiswa/profil" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Profil Saya
                            </a>
                            <a href="<?= base_url() ?>mahasiswa/bantuan" class="dropdown-item">
                                <i class="fas fa-question-circle"></i>
                                Bantuan
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= base_url() ?>auth/logout" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-wrapper">
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- JavaScript - Self-contained & Minimal -->
    <script>
        // Global State
        let sidebarOpen = false;
        let dropdownOpen = false;
        
        // Toggle Sidebar (Mobile)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMahasiswa');
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.add('show');
            } else {
                sidebar.classList.remove('show');
            }
        }
        
        // Toggle Profile Dropdown
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdownOpen = !dropdownOpen;
            
            if (dropdownOpen) {
                dropdown.classList.add('show');
            } else {
                dropdown.classList.remove('show');
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const profileDropdown = document.getElementById('profileDropdown');
            const profileButton = event.target.closest('.header-profile');
            
            // Close profile dropdown if clicked outside
            if (!profileButton && dropdownOpen) {
                profileDropdown.classList.remove('show');
                dropdownOpen = false;
            }
            
            // Close sidebar on mobile if clicked outside
            const sidebar = document.getElementById('sidebarMahasiswa');
            const mobileToggle = event.target.closest('.mobile-toggle');
            const sidebarContent = event.target.closest('.mahasiswa-sidebar');
            
            if (window.innerWidth <= 991.98 && !mobileToggle && !sidebarContent && sidebarOpen) {
                sidebar.classList.remove('show');
                sidebarOpen = false;
            }
        });
        
        // Auto-hide alerts
        function autoHideAlerts() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        }
        
        // Initialize progress bars animation
        function initProgressBars() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(function(bar) {
                const width = bar.style.width || bar.getAttribute('data-width');
                if (width) {
                    bar.style.width = '0%';
                    setTimeout(function() {
                        bar.style.width = width;
                    }, 500);
                }
            });
        }
        
        // Smooth scroll for anchor links
        function initSmoothScroll() {
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });
        }
        
        // Form validation helper
        function validateRequired(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.style.borderColor = '#f5365c';
                    isValid = false;
                } else {
                    field.style.borderColor = '#dee2e6';
                }
            });
            
            return isValid;
        }
        
        // Show notification
        function showNotification(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(function() {
                notification.style.opacity = '0';
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, duration);
        }
        
        // Initialize everything when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            autoHideAlerts();
            initProgressBars();
            initSmoothScroll();
            
            // Set active navigation
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                const href = link.getAttribute('href');
                if (href && currentPath.includes(href.split('/').pop())) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            console.log('SIM-TA Mahasiswa Template Loaded Successfully');
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98 && sidebarOpen) {
                const sidebar = document.getElementById('sidebarMahasiswa');
                sidebar.classList.remove('show');
                sidebarOpen = false;
            }
        });
        
        // Prevent form double submission
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                }, 3000);
            }
        });
        
        // Simple file upload preview
        function previewFile(input, previewId) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem;">`;
                    } else {
                        preview.innerHTML = `<div class="alert alert-info"><i class="fas fa-file"></i> ${file.name}</div>`;
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>