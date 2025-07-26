<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
<?php
$id_user = $this->session->userdata('id');
$dataUser = $this->db->get_where('mahasiswa', array('id' => $id_user))->row();
$verifikasi = $dataUser ? $dataUser->status : '';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistem Informasi Skripsi - Mahasiswa">
  <meta name="author" content="STK St. Yakobus">
  <title><?= $app->nama ?> - <?= isset($title) ? $title : 'Mahasiswa' ?></title>
  <?php include('_main/css.php') ?>
  <?= isset($styles) ? $styles : '' ?>
  
  <!-- ========================================
       🔧 FIXED CSS - SIDEBAR & LAYOUT RESPONSIVE
       ======================================== -->
  <style>
    /* ORIGINAL STYLES - PRESERVED */
    .sidebar-profile {
        background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
        border-radius: 0.5rem;
        padding: 1rem;
        margin: 1rem;
        color: white;
    }
    
    .sidebar-profile .avatar {
        width: 48px;
        height: 48px;
    }
    
    .contact-form-card {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
        border: none;
        color: white;
        margin: 1rem;
    }
    
    .contact-form-card .btn-outline-white {
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
    }
    
    .contact-form-card .btn-outline-white:hover {
        background-color: white;
        color: #2dce89;
    }
    
    /* CSS untuk foto profil yang responsive */
    #header-profile-photo, #sidebar-profile-photo {
        transition: all 0.3s ease;
    }
    
    #header-profile-photo:hover {
        transform: scale(1.05);
    }
    
    .loading-photo {
        opacity: 0.6;
        animation: pulse 1.5s ease-in-out infinite alternate;
    }
    
    @keyframes pulse {
        from { opacity: 0.6; }
        to { opacity: 1; }
    }    
    
    /* Perbaikan untuk menu sidebar yang kontras */
    .navbar-vertical .navbar-nav .nav-link {
        color: #525f7f;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    
    .navbar-vertical .navbar-nav .nav-link:hover {
        color: #5e72e4;
        background-color: rgba(94, 114, 228, 0.05);
        border-radius: 0.375rem;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
        border-radius: 0.375rem !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .navbar-vertical .navbar-nav .nav-link.active i {
        color: #fff !important;
    }
    
    .navbar-vertical .navbar-nav .nav-link.active .nav-link-text {
        color: #fff !important;
    }
    
    /* Hover effect untuk menu active */
    .navbar-vertical .navbar-nav .nav-link.active:hover {
        background: linear-gradient(87deg, #4c6ef5 0, #7048e8 100%) !important;
        color: #fff !important;
    }
    
    /* Better spacing untuk menu items */
    .navbar-vertical .navbar-nav .nav-item {
        margin-bottom: 0.25rem;
    }
    
    .navbar-vertical .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
        margin: 0 0.5rem;
    }
    
    /* Icon colors */
    .navbar-vertical .navbar-nav .nav-link i {
        margin-right: 0.75rem;
        font-size: 1rem;
    }
    
    /* Responsive improvements */
    @media (max-width: 1199.98px) {
        .navbar-vertical.navbar-collapse .navbar-nav .nav-link {
            padding: 0.875rem 1.5rem;
        }
    }

    /* ========================================
       🔧 FIXED: LAYOUT SIDEBAR & MAIN CONTENT
       ======================================== */

    /* 1. PERBAIKAN UTAMA: Main Content Margin untuk Sidebar */
    @media (min-width: 1200px) {
        /* Desktop: Main content harus memiliki margin-left sebesar lebar sidebar */
        .main-content {
            margin-left: 250px !important;
            transition: margin-left 0.3s ease;
            width: calc(100vw - 250px);
            overflow-x: auto;
        }
        
        /* System header juga perlu margin-left yang sama */
        .system-header {
            margin-left: 250px !important;
            transition: margin-left 0.3s ease;
        }
        
        /* Saat sidebar tersembunyi */
        body.g-sidenav-hidden .main-content,
        body.g-sidenav-hidden .system-header {
            margin-left: 0 !important;
            width: 100vw;
        }
        
        /* Container adjustments untuk desktop */
        .main-content .container-fluid {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        /* Hide hamburger di desktop */
        .sidenav-toggler {
            display: none !important;
        }
        
        /* Navbar top adjustment */
        .navbar-top {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
    }

    /* 2. TABLET LANDSCAPE (992px - 1199px) */
    @media (min-width: 992px) and (max-width: 1199.98px) {
        .main-content {
            margin-left: 0 !important;
            width: 100vw;
        }
        
        .system-header {
            margin-left: 0 !important;
        }
        
        /* Sidebar overlay mode */
        .sidenav {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed;
            z-index: 1050;
        }
        
        /* Show sidebar saat toggle active */
        body.g-sidenav-show .sidenav {
            transform: translateX(0);
        }
        
        /* Show hamburger di tablet */
        .sidenav-toggler {
            display: inline-block !important;
        }
    }

    /* 3. TABLET PORTRAIT & MOBILE (768px - 991px) */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .main-content {
            margin-left: 0 !important;
            width: 100vw;
        }
        
        .system-header {
            margin-left: 0 !important;
        }
        
        /* Sidebar overlay mode */
        .sidenav {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed;
            z-index: 1050;
            width: 280px !important;
        }
        
        /* Show sidebar saat toggle active */
        body.g-sidenav-show .sidenav {
            transform: translateX(0);
        }
        
        /* Show hamburger */
        .sidenav-toggler {
            display: inline-block !important;
        }
    }

    /* 4. MOBILE (max-width: 767px) */
    @media (max-width: 767.98px) {
        .main-content {
            margin-left: 0 !important;
            padding-left: 0;
            padding-right: 0;
            width: 100vw;
        }
        
        .system-header {
            margin-left: 0 !important;
        }
        
        /* Sidebar full overlay pada mobile */
        .sidenav {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px !important;
            height: 100vh;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1050;
        }
        
        body.g-sidenav-show .sidenav {
            transform: translateX(0);
        }
        
        /* Container fluid adjustments untuk mobile */
        .main-content .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        /* Show hamburger */
        .sidenav-toggler {
            display: inline-block !important;
        }
    }

    /* 5. BACKDROP UNTUK OVERLAY */
    .backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        display: none;
    }
    
    body.g-sidenav-show .backdrop {
        display: block;
    }

    /* ========================================
       MOBILE LAYOUT - HEADER BUTTONS (PRESERVED)
       ======================================== */

    /* Container untuk button group */
    .header-button-group {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      align-items: center;
      flex-wrap: nowrap;
    }

    /* Styling untuk header buttons */
    .header-btn {
      white-space: nowrap;
      font-size: 0.875rem;
      padding: 6px 12px;
      border-radius: 4px;
      flex-shrink: 0;
      transition: all 0.15s ease;
      min-width: auto;
    }

    /* Desktop dan Tablet */
    @media (min-width: 768px) {
      .header-button-group {
        gap: 10px;
      }
      
      .header-btn {
        font-size: 0.875rem;
        padding: 8px 16px;
        min-width: 80px;
      }
    }

    /* Mobile responsive untuk header buttons */
    @media (max-width: 767.98px) and (min-width: 576px) {
      .header-button-group {
        gap: 6px;
      }
      
      .header-btn {
        font-size: 0.8rem;
        padding: 6px 10px;
        min-width: 70px;
      }
      
      .mobile-title {
        font-size: 1.3rem !important;
      }
    }

    @media (max-width: 575.98px) and (min-width: 480px) {
      .header-button-group {
        gap: 5px;
        justify-content: center;
        margin-top: 5px;
      }
      
      .header-btn {
        font-size: 0.75rem;
        padding: 5px 8px;
        min-width: 65px;
        flex: 0 1 auto;
      }
      
      .mobile-title {
        font-size: 1.2rem !important;
      }
      
      .col-12.text-right {
        text-align: center !important;
      }
    }

    @media (max-width: 479.98px) and (min-width: 375px) {
      .header-button-group {
        gap: 4px;
        justify-content: center;
        margin-top: 8px;
      }
      
      .header-btn {
        font-size: 0.7rem;
        padding: 4px 6px;
        min-width: 55px;
        flex: 0 1 auto;
      }
      
      .mobile-title {
        font-size: 1.1rem !important;
        text-align: center;
        display: block !important;
      }
      
      .col-12.text-right {
        text-align: center !important;
      }
    }

    @media (max-width: 374.98px) {
      .header-button-group {
        gap: 3px;
        justify-content: center;
        margin-top: 10px;
        flex-direction: row;
      }
      
      .header-btn {
        font-size: 0.65rem;
        padding: 3px 5px;
        min-width: 50px;
        flex: 0 1 auto;
      }
      
      .mobile-title {
        font-size: 1rem !important;
        text-align: center;
        display: block !important;
      }
    }

    /* Hover effects */
    .header-btn:hover {
      background-color: rgba(255, 255, 255, 0.9) !important;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .header-btn:active {
      transform: translateY(0);
      box-shadow: none;
    }

    /* ========================================
       SYSTEM HEADER STYLES (PRESERVED & ENHANCED)
       ======================================== */
    
    .system-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 12px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative;
      z-index: 1000;
      /* Removed fixed margin-left - now handled by responsive CSS above */
    }
    
    .system-title {
      font-size: 18px;
      font-weight: 700;
      margin: 0;
      line-height: 1.2;
      letter-spacing: 0.5px;
    }
    
    .system-subtitle {
      font-size: 13px;
      margin: 2px 0 0 0;
      opacity: 0.95;
      font-weight: 500;
    }
    
    .system-header-icon {
      font-size: 24px;
      margin-right: 12px;
      opacity: 0.9;
    }
    
    /* Responsive untuk system header */
    @media (max-width: 768px) {
      .system-header {
        padding: 10px 0;
      }
      
      .system-title {
        font-size: 16px;
      }
      
      .system-subtitle {
        font-size: 12px;
      }
      
      .system-header-icon {
        font-size: 20px;
        margin-right: 8px;
      }
    }
    
    @media (max-width: 576px) {
      .system-header {
        padding: 8px 0;
      }
      
      .system-title {
        font-size: 14px;
        line-height: 1.3;
      }
      
      .system-subtitle {
        font-size: 11px;
      }
      
      .system-header-icon {
        font-size: 18px;
        margin-right: 6px;
      }
    }
    
    /* Layout header mahasiswa */
    .system-header-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }
    
    .system-info {
      display: flex;
      align-items: center;
      flex: 1;
    }
    
    /* Profil mahasiswa di header */
    .mahasiswa-profile-header {
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 25px;
      padding: 8px 16px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      cursor: pointer;
      user-select: none;
    }
    
    .mahasiswa-profile-header:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .mahasiswa-avatar-header {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 12px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      object-fit: cover;
    }
    
    .mahasiswa-info-header {
      text-align: right;
    }
    
    .mahasiswa-name-header {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
      color: white;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }
    
    .mahasiswa-role-header {
      font-size: 11px;
      margin: 0;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 400;
    }
    
    /* Dropdown styling untuk header mahasiswa */
    .system-header .dropdown {
      position: relative;
    }
    
    .system-header .dropdown-menu {
      background-color: #ffffff;
      border: 1px solid rgba(0, 0, 0, 0.15);
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      margin-top: 0.5rem;
      min-width: 160px;
      display: none;
      position: absolute;
      top: 100%;
      right: 0;
      z-index: 1000;
    }
    
    .system-header .dropdown-menu.show {
      display: block;
    }
    
    .system-header .dropdown-item {
      display: block;
      width: 100%;
      padding: 0.5rem 1rem;
      clear: both;
      font-weight: 400;
      color: #212529;
      text-align: inherit;
      text-decoration: none;
      white-space: nowrap;
      background-color: transparent;
      border: 0;
      transition: all 0.15s ease;
    }
    
    .system-header .dropdown-item:hover,
    .system-header .dropdown-item:focus {
      color: #16181b;
      background-color: #f8f9fa;
    }
    
    .system-header .dropdown-item i {
      margin-right: 0.5rem;
      font-size: 0.875rem;
    }
    
    /* Desktop: Enable dropdown functionality */
    @media (min-width: 769px) {
      .mahasiswa-profile-header {
        pointer-events: auto !important;
        cursor: pointer !important;
      }
    }
    
    /* Mobile: Centered layout */
    @media (max-width: 768px) {
      .system-header-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }
      
      .system-info {
        justify-content: center;
      }
      
      .mahasiswa-profile-header {
        justify-content: center;
        cursor: default !important;
        pointer-events: none;
      }
      
      .mahasiswa-profile-header:hover {
        transform: none !important;
        background: rgba(255, 255, 255, 0.1) !important;
      }
      
      .mahasiswa-info-header {
        text-align: center;
      }
      
      .system-header .dropdown-menu {
        display: none !important;
      }
    }
    
    @media (max-width: 576px) {
      .mahasiswa-avatar-header {
        width: 35px;
        height: 35px;
        margin-right: 10px;
      }
      
      .mahasiswa-name-header {
        font-size: 13px;
      }
      
      .mahasiswa-role-header {
        font-size: 10px;
      }
    }
    
    /* Hide original navbar user profile on larger screens */
    @media (min-width: 769px) {
      .navbar-top .nav-item.dropdown {
        display: none !important;
      }
    }
    
    /* Show navbar user profile only on mobile */
    @media (max-width: 768px) {
      .navbar-top .nav-item.dropdown {
        display: block !important;
      }
    }

    /* ========================================
       PERFORMANCE & ACCESSIBILITY
       ======================================== */
    
    /* Performance optimizations */
    .sidenav,
    .main-content,
    .system-header {
      will-change: transform, margin-left;
      backface-visibility: hidden;
    }
    
    /* Accessibility improvements */
    @media (prefers-reduced-motion: reduce) {
      .sidenav,
      .main-content,
      .system-header {
        transition: none !important;
      }
    }
    
    /* Prevent horizontal scroll */
    body {
      overflow-x: hidden;
    }
    
    /* General improvements */
    .main-content {
      min-height: 100vh;
      position: relative;
    }
    
    .header.bg-primary {
      position: relative;
      z-index: 1;
    }
    
    .footer {
      margin-top: 2rem;
      padding-bottom: 1rem;
    }
    
    @media (min-width: 1200px) {
      .footer {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
      }
    }

    /* PERBAIKAN UTAMA: Header Dashboard Terlalu Besar */
    .header.bg-primary {
        padding-bottom: 4rem !important; /* Kurangi dari pb-6 (6rem) */
        min-height: auto !important;
        overflow: visible !important;
    }
    
    /* Container dengan negative margin yang tepat */
    .container-fluid.mt--6 {
        margin-top: -4rem !important; /* Sesuaikan dengan padding-bottom header */
        position: relative;
        z-index: 10;
    }
    
    /* Header body padding adjustment */
    .header .header-body {
        padding-top: 1.5rem !important;
        padding-bottom: 1.5rem !important;
    }
    
    /* Welcome card styling */
    .card.bg-gradient-info {
        margin-bottom: 1.5rem !important;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        border: none !important;
    }
    
    /* Text visibility ensure */
    .text-white, .text-white-50 {
        color: #ffffff !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3) !important;
    }
    
    /* RESPONSIVE ADJUSTMENTS */
    @media (max-width: 768px) {
        .header.bg-primary {
            padding-bottom: 3rem !important;
        }
        
        .container-fluid.mt--6 {
            margin-top: -3rem !important;
        }
        
        .header .header-body {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        
        .card.bg-gradient-info h3 {
            font-size: 1.375rem !important;
        }
        
        .card.bg-gradient-info p {
            font-size: 0.9rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .header.bg-primary {
            padding-bottom: 2.5rem !important;
        }
        
        .container-fluid.mt--6 {
            margin-top: -2.5rem !important;
        }
        
        .card.bg-gradient-info h3 {
            font-size: 1.25rem !important;
        }
        
        .card.bg-gradient-info p {
            font-size: 0.875rem !important;
        }
    }
    
    /* Ensure proper stacking order */
    .header.bg-primary {
        position: relative;
        z-index: 1;
    }
    
    .container-fluid.mt--6 {
        position: relative;
        z-index: 2;
    }
    
    /* Icon adjustments for mobile */
    @media (max-width: 576px) {
        .icon.icon-shape {
            width: 40px !important;
            height: 40px !important;
        }
        
        .icon.icon-shape i {
            font-size: 1rem !important;
        }
    }

  </style>
</head>

<body>
  <!-- ========================================
       HEADER SISTEM YANG FORMAL
       ======================================== -->
    <div class="system-header">
      <div class="container-fluid">
        <div class="system-header-container">
          <div class="system-info">
            <i class="ni ni-hat-3 system-header-icon"></i>
            <div>
              <h1 class="system-title">SISTEM INFORMASI MANAJEMEN TUGAS AKHIR</h1>
              <p class="system-subtitle">Sekolah Tinggi Katolik Santo Yakobus Merauke</p>
            </div>
          </div>
          
          <div class="dropdown">
            <div class="mahasiswa-profile-header" id="mahasiswaProfileDropdown" style="cursor: pointer;">
              <?php
                // Ambil foto mahasiswa dari database atau session
                $foto_mahasiswa = $this->session->userdata('foto');
                if (empty($foto_mahasiswa) || $foto_mahasiswa == 'default.png') {
                  $mahasiswa_id = $this->session->userdata('id');
                  $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
                  $foto_mahasiswa = ($mahasiswa && !empty($mahasiswa->foto)) ? $mahasiswa->foto : 'default.png';
                } else {
                  $foto_mahasiswa = !empty($foto_mahasiswa) ? $foto_mahasiswa : 'default.png';
                }
                $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mahasiswa);
              ?>
              <img src="<?= $foto_path ?>" alt="Foto <?= $this->session->userdata('nama') ?>" class="mahasiswa-avatar-header">
              <div class="mahasiswa-info-header">
                <p class="mahasiswa-name-header"><?= $this->session->userdata('nama') ?></p>
                <p class="mahasiswa-role-header">Mahasiswa</p>
              </div>
            </div>
            <div class="dropdown-menu" id="mahasiswaDropdownMenu">
              <div class="dropdown-header">
                <h6 class="text-overflow m-0">Selamat Datang!</h6>
              </div>
              <a href="<?= base_url('mahasiswa/profil') ?>" class="dropdown-item">
                <i class="ni ni-single-02"></i>
                <span>Profil</span>
              </a>
              <a href="<?= base_url('mahasiswa/dashboard') ?>" class="dropdown-item">
                <i class="ni ni-tv-2"></i>
                <span>Dashboard</span>
              </a>
              <a href="<?= base_url('mahasiswa/kontak') ?>" class="dropdown-item">
                <i class="ni ni-email-83"></i>
                <span>Kontak Form</span>
              </a>
              <div class="dropdown-divider"></div>
              <a href="<?= base_url('auth/logout') ?>" class="dropdown-item">
                <i class="ni ni-user-run"></i>
                <span>Logout</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

  <nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <div class="sidenav-header  align-items-center">
        <a class="navbar-brand" href="javascript:void(0)">
          <img src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" class="navbar-brand-img" alt="...">
        </a>
      </div>
      <div class="navbar-inner">
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <ul class="navbar-nav">
            <!-- 1. Dashboard -->
            <li class="nav-item">
              <a class="nav-link <?= ($this->uri->segment(2) == 'dashboard' || $this->uri->segment(2) == '') ? 'active' : '' ?>" 
                 href="<?= base_url('mahasiswa/dashboard') ?>">
                <i class="ni ni-tv-2 text-primary"></i><span class="nav-link-text">Dashboard</span>
              </a>
            </li>
            
            <?php if ($verifikasi == 1) { ?>
              <!-- 2. Usulan Proposal -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'proposal' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/proposal">
                  <i class="ni ni-single-copy-04 text-orange"></i><span class="nav-link-text">Usulan Proposal</span>
                </a>
              </li>
              
              <!-- 3. Bimbingan -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'bimbingan' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/bimbingan">
                  <i class="ni ni-books text-info"></i><span class="nav-link-text">Bimbingan</span>
                </a>
              </li>
              
              <!-- 4. Seminar Proposal -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'seminar' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/seminar">
                  <i class="ni ni-calendar-grid-58 text-success"></i><span class="nav-link-text">Seminar Proposal</span>
                </a>
              </li>
              
              <!-- 5. Penelitian -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'penelitian' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/penelitian">
                  <i class="ni ni-atom text-warning"></i><span class="nav-link-text">Penelitian</span>
                </a>
              </li>
              
              <!-- 6. Seminar Skripsi -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'skripsi' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/skripsi">
                  <i class="ni ni-hat-3 text-danger"></i><span class="nav-link-text">Seminar Skripsi</span>
                </a>
              </li>
              
              <!-- 7. Publikasi Skripsi -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'publikasi' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/publikasi">
                  <i class="ni ni-trophy text-success"></i><span class="nav-link-text">Publikasi Skripsi</span>
                </a>
              </li>
              
              <!-- Divider -->
              <li class="nav-item">
                <hr class="my-3">
              </li>
              
            <?php } ?>
            
            <!-- 8. Profil -->
            <li class="nav-item">
              <a class="nav-link <?= $this->uri->segment(2) == 'profil' ? 'active' : '' ?>" 
                 href="<?= base_url('mahasiswa/profil') ?>">
                <i class="ni ni-single-02 text-blue"></i><span class="nav-link-text">Profil</span>
              </a>
            </li>
            
            <!-- 9. Kontak Form -->
            <li class="nav-item">
              <a class="nav-link <?= $this->uri->segment(2) == 'kontak' ? 'active' : '' ?>" 
                 href="<?= base_url('mahasiswa/kontak') ?>">
                <i class="ni ni-email-83 text-pink"></i><span class="nav-link-text">Kontak Form</span>
              </a>
            </li>
          </ul>
          
          <!-- Profile Card -->
          <?php if ($verifikasi == 1) { ?>
          <div class="sidebar-profile mt-4">
            <div class="row align-items-center">
              <div class="col-auto">
                <a href="<?= base_url('mahasiswa/profil') ?>">
                    <?php 
                    $foto = $this->session->userdata('foto');
                    $avatar_path = base_url('cdn/img/mahasiswa/' . ($foto ? $foto : 'default.png')) . '?v=' . time();
                    ?>
                    <img src="<?= $avatar_path ?>" alt="Foto Profile" 
                         class="avatar rounded-circle sidebar-profile-photo" 
                         id="sidebar-profile-photo"
                         style="width: 48px; height: 48px; object-fit: cover;"
                         onerror="this.src='<?= base_url('cdn/img/mahasiswa/default.png') ?>'">
                </a>
              </div>
              <div class="col ml-n2">
                <h6 class="mb-0 text-white">
                  <?= $this->session->userdata('nama') ?>
                </h6>
                <p class="text-sm text-white-50 mb-0">Mahasiswa</p>
              </div>
            </div>
          </div>
          
          <!-- Contact Form Card -->
          <div class="card contact-form-card mt-3">
            <div class="card-body text-center">
              <i class="ni ni-email-83 fa-2x mb-2"></i>
              <h6 class="text-white mb-2">Butuh Bantuan?</h6>
              <p class="text-white-50 text-sm mb-3">
                Kirim pesan ke dosen pembimbing atau kaprodi
              </p>
              <a href="<?= base_url('mahasiswa/kontak') ?>" class="btn btn-outline-white btn-sm">
                Kirim Pesan
              </a>
            </div>
          </div>
          <?php } ?>
          
        </div>
      </div>
    </div>
  </nav>
  
  <div class="main-content sidebar-responsive" id="panel">
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- TOMBOL HAMBURGER MENU UNTUK MOBILE -->
          <ul class="navbar-nav align-items-center ml-md-auto">
            <li class="nav-item d-xl-none">
              <div class="pr-3 sidenav-toggler sidenav-toggler-dark" data-action="sidenav-pin" data-target="#sidenav-main">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </div>
            </li>
          </ul>
          <ul class="navbar-nav align-items-center  ml-auto ml-md-0 ">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                <i class="ni ni-bell-55"></i>
                <span class="badge badge-danger badge-sm" id="notif-count" style="display: none;">0</span>
              </a>
              <div class="dropdown-menu dropdown-menu-xl dropdown-menu-right py-0 overflow-hidden">
                <div class="px-3 py-3">
                  <h6 class="text-sm text-muted m-0">Anda memiliki <strong class="text-primary">0</strong> notifikasi.</h6>
                </div>
                <div id="dropdown-notifications">
                  <div class="text-center py-3">
                    <p class="text-muted mb-0">Tidak ada notifikasi</p>
                  </div>
                </div>
                <a href="<?= base_url('mahasiswa/kontak') ?>" class="dropdown-item text-center text-primary font-weight-bold py-3">
                  Kirim Pesan Baru
                </a>
              </div>
            </li>
            
            <!-- User Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                    <span class="avatar avatar-sm rounded-circle">
                      <?php
                        $mahasiswa_id = $this->session->userdata('id');
                        $foto_session = $this->session->userdata('foto');
                        
                        $foto_name = 'default.png';
                        
                        if (!empty($foto_session) && $foto_session !== 'default.png') {
                            $foto_name = $foto_session;
                        } else {
                            try {
                                $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
                                if ($mahasiswa && !empty($mahasiswa->foto)) {
                                    $foto_name = $mahasiswa->foto;
                                    $this->session->set_userdata('foto', $foto_name);
                                }
                            } catch (Exception $e) {
                                log_message('error', 'Error loading mahasiswa photo: ' . $e->getMessage());
                                $foto_name = 'default.png';
                            }
                        }
                        
                        $timestamp = time();
                        $foto_url = base_url('cdn/img/mahasiswa/' . $foto_name) . '?v=' . $timestamp;
                      ?>
                      <img alt="Foto Profil" src="<?= $foto_url ?>" 
                           id="header-profile-photo"
                           style="width: 100%; height: 100%; object-fit: cover;"
                           onerror="this.src='<?= base_url('cdn/img/mahasiswa/default.png') ?>'">
                    </span>                 
                  <div class="media-body ml-2">
                    <span class="mb-0 text-sm font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                  </div>
                </div>
              </a>
              <div class="dropdown-menu  dropdown-menu-right ">
                <div class="dropdown-header noti-title">
                  <h6 class="text-overflow m-0">Selamat Datang!</h6>
                </div>
                <a href="<?= base_url('mahasiswa/profil') ?>" class="dropdown-item">
                  <i class="ni ni-single-02"></i><span>Profil</span>
                </a>
                <a href="<?= base_url('mahasiswa/dashboard') ?>" class="dropdown-item">
                  <i class="ni ni-tv-2"></i><span>Dashboard</span>
                </a>
                <a href="<?= base_url('mahasiswa/kontak') ?>" class="dropdown-item">
                  <i class="ni ni-email-83"></i><span>Kontak Form</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('auth/logout') ?>" class="dropdown-item">
                  <i class="ni ni-user-run"></i><span>Logout</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
    <!-- Header dengan breadcrumb -->
    <div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-md-7 col-sm-6 col-12 mb-2 mb-sm-0">
              <h6 class="h2 text-white d-inline-block mb-0 mobile-title"><?= isset($title) ? $title : 'Dashboard' ?></h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item">
                    <a href="<?= base_url('mahasiswa/dashboard') ?>"><i class="fas fa-home"></i></a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page"><?= isset($title) ? $title : 'Dashboard' ?></li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-md-5 col-sm-6 col-12 text-right">
              <div class="header-button-group">
                <a href="<?= base_url('mahasiswa/kontak') ?>" class="btn btn-sm btn-neutral header-btn">
                  <i class="ni ni-email-83 d-inline d-sm-none"></i>
                  <span class="d-none d-sm-inline">Kontak</span>
                  <span class="d-inline d-sm-none">Kontak</span>
                </a>
                <a href="<?= base_url('mahasiswa/profil') ?>" class="btn btn-sm btn-neutral header-btn">
                  <i class="ni ni-single-02 d-inline d-sm-none"></i>
                  <span class="d-none d-sm-inline">Profil</span>
                  <span class="d-inline d-sm-none">Profil</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="container-fluid mt--6">
      <?= isset($content) ? $content : '' ?>
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6">
            <div class="copyright text-center text-lg-left text-muted">
              &copy; <?= date('Y') ?> <a href="https://www.stkyakobus.ac.id" class="font-weight-bold ml-1" target="_blank">STK Santo Yakobus Merauke</a>
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="#" class="nav-link" target="_blank">SIM Tugas Akhir</a>
              </li>
              <li class="nav-item">
                <a href="<?= base_url('mahasiswa/kontak') ?>" class="nav-link">Bantuan</a>
              </li>
            </ul>
          </div>
        </div>
      </footer>
    </div>
  </div>
  
  <?php include('_main/js.php') ?>
  <?= isset($script) ? $script : '' ?>
  
  <!-- ========================================
       🔧 FIXED JAVASCRIPT - SIDEBAR & NOTIFICATIONS
       ======================================== -->
  <script>
  // Definisi base_url global
  var base_url = '<?= base_url() ?>';
  
  // Fungsi call yang benar
  function call(url, data = null) {
      return $.ajax({
          url: base_url + url,
          type: data ? 'POST' : 'GET',
          data: data,
          dataType: 'json'
      });
  }
  
  $(document).ready(function() {
    
    // ========================================
    // 1. SIDEBAR RESPONSIVE FUNCTIONALITY
    // ========================================
    
    // Initialize sidebar state
    function initializeSidebar() {
        const windowWidth = $(window).width();
        
        if (windowWidth >= 1200) {
            // Desktop: Sidebar always visible
            $('body').removeClass('g-sidenav-hidden').addClass('g-sidenav-show g-sidenav-pinned');
            $('.sidenav-toggler').hide();
        } else {
            // Mobile/Tablet: Sidebar hidden by default
            $('body').removeClass('g-sidenav-show g-sidenav-pinned').addClass('g-sidenav-hidden');
            $('.sidenav-toggler').show();
        }
    }
    
    // Sidebar toggle functionality
    $(document).on('click', '.sidenav-toggler', function(e) {
        e.preventDefault();
        const windowWidth = $(window).width();
        
        if (windowWidth < 1200) {
            // Mobile/Tablet toggle
            if ($('body').hasClass('g-sidenav-show')) {
                // Hide sidebar
                $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
                $('.backdrop').remove();
            } else {
                // Show sidebar
                $('body').removeClass('g-sidenav-hidden').addClass('g-sidenav-show');
                
                // Add backdrop
                if (!$('.backdrop').length) {
                    $('body').append('<div class="backdrop"></div>');
                }
            }
        }
    });
    
    // Backdrop click to close
    $(document).on('click', '.backdrop', function() {
        $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
        $(this).remove();
    });
    
    // Escape key to close sidebar
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('body').hasClass('g-sidenav-show')) {
            const windowWidth = $(window).width();
            if (windowWidth < 1200) {
                $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
                $('.backdrop').remove();
            }
        }
    });
    
    // Window resize handler
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const windowWidth = $(window).width();
            
            if (windowWidth >= 1200) {
                // Desktop: Show sidebar, remove backdrop
                $('body').removeClass('g-sidenav-hidden').addClass('g-sidenav-show g-sidenav-pinned');
                $('.backdrop').remove();
                $('.sidenav-toggler').hide();
            } else {
                // Mobile/Tablet: Hide sidebar unless manually opened
                if (!$('body').hasClass('g-sidenav-show')) {
                    $('body').removeClass('g-sidenav-pinned').addClass('g-sidenav-hidden');
                }
                $('.sidenav-toggler').show();
            }
        }, 250);
    });
    
    // Auto-close sidebar on mobile after clicking a link
    $('.sidenav .nav-link').on('click', function() {
        const windowWidth = $(window).width();
        
        if (windowWidth < 1200 && $('body').hasClass('g-sidenav-show')) {
            setTimeout(function() {
                $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
                $('.backdrop').remove();
            }, 150);
        }
    });
    
    // Prevent sidebar close on internal clicks
    $('.sidenav').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Main content click to close sidebar (Mobile only)
    $('.main-content').on('click', function() {
        const windowWidth = $(window).width();
        if (windowWidth < 1200 && $('body').hasClass('g-sidenav-show')) {
            $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
            $('.backdrop').remove();
        }
    });
    
    // Active menu highlighting
    function setActiveMenu() {
        const currentPath = window.location.pathname;
        const currentPage = currentPath.split('/').pop() || 'dashboard';
        
        // Remove all active classes
        $('.sidenav .nav-link').removeClass('active');
        
        // Add active class to current page
        $('.sidenav .nav-link').each(function() {
            const href = $(this).attr('href');
            if (href && (href.includes(currentPage) || 
                (currentPage === 'dashboard' && href.includes('dashboard')))) {
                $(this).addClass('active');
            }
        });
    }
    
    // ========================================
    // 2. MAHASISWA HEADER DROPDOWN
    // ========================================
    
    function initializeMahasiswaHeaderDropdown() {
      var dropdownToggle = document.getElementById('mahasiswaProfileDropdown');
      var dropdownMenu = document.getElementById('mahasiswaDropdownMenu');
      
      if (dropdownToggle && dropdownMenu) {
        // Handle click pada desktop
        dropdownToggle.addEventListener('click', function(e) {
          var windowWidth = window.innerWidth;
          
          // Hanya aktifkan dropdown di desktop/laptop
          if (windowWidth > 768) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle dropdown
            var isShowing = dropdownMenu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
              menu.classList.remove('show');
            });
            document.querySelectorAll('.mahasiswa-profile-header.dropdown-active').forEach(function(toggle) {
              toggle.classList.remove('dropdown-active');
            });
            
            // Toggle current dropdown
            if (!isShowing) {
              dropdownMenu.classList.add('show');
              dropdownToggle.classList.add('dropdown-active');
            } else {
              dropdownToggle.classList.remove('dropdown-active');
            }
          }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
          if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.classList.remove('dropdown-active');
          }
        });
        
        // Close dropdown on window resize to mobile
        window.addEventListener('resize', function() {
          var windowWidth = window.innerWidth;
          if (windowWidth <= 768) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.classList.remove('dropdown-active');
          }
        });
        
        // Close dropdown when pressing ESC
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            dropdownMenu.classList.remove('show');
            dropdownToggle.classList.remove('dropdown-active');
          }
        });
      }
    }
    
    // ========================================
    // 3. NOTIFICATIONS
    // ========================================
    
    // Load notifikasi untuk dropdown
    function loadNotifikasiDropdown() {
      call('mahasiswa/dashboard/get_notifikasi').done(function(response) {
        if (response.status === 'success') {
          const notifikasi = response.data;
          const count = notifikasi.length;
          
          // Update badge count
          if (count > 0) {
            $('#notif-count').text(count).show();
          } else {
            $('#notif-count').hide();
          }
          
          // Update dropdown content
          let html = '';
          if (count > 0) {
            notifikasi.forEach(function(notif) {
              html += `
                <a href="#!" class="list-group-item list-group-item-action">
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <i class="ni ni-bell-55 text-primary"></i>
                    </div>
                    <div class="col ml-n2">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <h4 class="mb-0 text-sm">${notif.judul.substring(0, 30)}${notif.judul.length > 30 ? '...' : ''}</h4>
                        </div>
                        <div class="text-right text-muted">
                          <small>${formatTimeAgo(notif.created_at)}</small>
                        </div>
                      </div>
                      <p class="text-sm mb-0">${notif.pesan.substring(0, 50)}...</p>
                    </div>
                  </div>
                </a>
              `;
            });
          } else {
            html = `
              <div class="text-center py-3">
                <p class="text-muted mb-0">Tidak ada notifikasi</p>
              </div>
            `;
          }
          
          $('#dropdown-notifications').html(html);
        }
      }).fail(function() {
        console.log('Error loading notifications dropdown');
      });
    }
    
    function formatTimeAgo(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMins / 60);
      const diffDays = Math.floor(diffHours / 24);
      
      if (diffMins < 60) {
        return diffMins + ' menit yang lalu';
      } else if (diffHours < 24) {
        return diffHours + ' jam yang lalu';
      } else {
        return diffDays + ' hari yang lalu';
      }
    }
    
    // ========================================
    // 4. PROFILE PHOTO UPDATE
    // ========================================
    
    // Global function untuk update foto di header dan sidebar
    window.updateHeaderProfilePhoto = function(newFotoName) {
        console.log('Global updateHeaderProfilePhoto called with:', newFotoName);
        
        if (!newFotoName) {
            newFotoName = 'default.png';
        }
        
        const timestamp = new Date().getTime();
        const newFotoUrl = base_url + 'cdn/img/mahasiswa/' + newFotoName + '?v=' + timestamp;
        
        console.log('Updating header photo to:', newFotoUrl);
        
        // Update header photo
        const headerImg = document.getElementById('header-profile-photo');
        if (headerImg) {
            headerImg.src = newFotoUrl;
            console.log('Header photo updated');
        }
        
        // Update sidebar photo
        const sidebarImg = document.getElementById('sidebar-profile-photo');
        if (sidebarImg) {
            sidebarImg.src = newFotoUrl;
            console.log('Sidebar photo updated');
        }
        
        // Update semua foto profil lain di halaman
        $('.foto-profil, .header-avatar-img').attr('src', newFotoUrl);
    };
    
    // Event listener untuk profile photo update
    $(document).on('profilePhotoUpdated', function(event, fotoName) {
        if (typeof window.updateHeaderProfilePhoto === 'function') {
            window.updateHeaderProfilePhoto(fotoName);
        }
    });
    
    // ========================================
    // 5. INITIALIZE ALL
    // ========================================
    
    // Initialize pada load
    initializeSidebar();
    setActiveMenu();
    initializeMahasiswaHeaderDropdown();
    loadNotifikasiDropdown();
    
    // Refresh notifikasi setiap 5 menit
    setInterval(loadNotifikasiDropdown, 300000);
    
    // Update active menu on navigation
    $(window).on('popstate', function() {
        setActiveMenu();
    });
    
  });

  // ========================================
  // 6. UTILITY FUNCTIONS
  // ========================================
  
  // Utility functions untuk sidebar
  window.SidebarUtils = {
    open: function() {
        $('body').removeClass('g-sidenav-hidden').addClass('g-sidenav-show');
        if ($(window).width() < 1200 && !$('.backdrop').length) {
            $('body').append('<div class="backdrop"></div>');
        }
    },
    
    close: function() {
        $('body').removeClass('g-sidenav-show').addClass('g-sidenav-hidden');
        $('.backdrop').remove();
    },
    
    toggle: function() {
        if ($('body').hasClass('g-sidenav-show')) {
            this.close();
        } else {
            this.open();
        }
    },
    
    isOpen: function() {
        return $('body').hasClass('g-sidenav-show');
    }
  };
  
  </script>
    
</body>
</html>