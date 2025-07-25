<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Start your development with a Dashboard for Bootstrap 4.">
  <meta name="author" content="Creative Tim">
  <title>Skripsi - <?= $title ?></title>
  
  <?php include('_main/css.php') ?>
  
  <!-- 🎯 INLINE CSS ONLY - NO EXTERNAL FILES -->
  <style>
    .admin {
      display: none;
    }

    .dosen {
      display: none;
    }
    
    /* ============================================
       🔥 HEADER CONTRAST FIX - INLINE ONLY
       ============================================ */
    
    /* Header Background */
    .header.bg-primary {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
      background-color: #1e3a8a !important;
    }
    
    /* Title Header */
    .header.bg-primary h6.h2,
    .header.bg-primary .h2 {
      color: #ffffff !important;
      font-weight: 700 !important;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7) !important;
    }
    
    /* Breadcrumb */
    .header.bg-primary .breadcrumb-item,
    .header.bg-primary .breadcrumb-item a {
      color: rgba(255, 255, 255, 0.9) !important;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
    }
    
    .header.bg-primary .breadcrumb-item.active {
      color: #ffffff !important;
      font-weight: 600 !important;
    }
    
    /* Home Icon */
    .header.bg-primary .fas.fa-home {
      color: #ffffff !important;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
    }
    
    /* Back Button */
    .header.bg-primary .btn-neutral {
      background-color: #ffffff !important;
      color: #1e3a8a !important;
      border: 2px solid #ffffff !important;
      font-weight: 600 !important;
    }
    
    .header.bg-primary .btn-neutral:hover {
      background-color: #f8fafc !important;
      color: #1e3a8a !important;
    }
    
    /* Navbar Top */
    .navbar-top.bg-primary {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
    }
    
    /* User Name in Navbar */
    .navbar-top.bg-primary .nav-link .media-body span {
      color: #ffffff !important;
      font-weight: 600 !important;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;
    }

    /* ========================================
       TAMBAHAN: CSS UNTUK HEADER SISTEM BARU
       ======================================== */
    
    .system-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 12px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative;
      z-index: 1000;
      /* PERBAIKAN: Offset untuk sidebar pada desktop */
      margin-left: 0;
      transition: margin-left 0.15s ease;
    }
    
    /* Desktop: Offset untuk sidebar */
    @media (min-width: 1200px) {
      .system-header {
        margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
      }
    }
    
    /* Tablet landscape: Offset yang lebih kecil */
    @media (min-width: 992px) and (max-width: 1199.98px) {
      .system-header {
        margin-left: 250px;
      }
    }
    
    /* Tablet dan mobile: Full width */
    @media (max-width: 991.98px) {
      .system-header {
        margin-left: 0;
      }
    }
    
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
    
    .dosen-profile-header {
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 25px;
      padding: 8px 16px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .dosen-profile-header:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Dropdown styling untuk header dosen */
    .system-header .dropdown-menu {
      background-color: #ffffff;
      border: 1px solid rgba(0, 0, 0, 0.15);
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      margin-top: 0.5rem;
      min-width: 160px;
      display: none; /* Hidden by default */
      position: absolute;
      top: 100%;
      right: 0;
      z-index: 1000;
    }
    
    .system-header .dropdown-menu.show {
      display: block; /* Show when active */
    }
    
    .system-header .dropdown {
      position: relative;
    }
    
    .system-header .dropdown-header {
      padding: 0.5rem 1rem;
      margin-bottom: 0;
      font-size: 0.875rem;
      color: #6c757d;
      white-space: nowrap;
      background-color: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
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
    
    .system-header .dropdown-divider {
      height: 0;
      margin: 0.5rem 0;
      overflow: hidden;
      border-top: 1px solid #e9ecef;
    }
    
    .dosen-avatar-header {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 12px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      object-fit: cover;
    }
    
    .dosen-info-header {
      text-align: right;
    }
    
    .dosen-name-header {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
      color: white;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }
    
    .dosen-role-header {
      font-size: 11px;
      margin: 0;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 400;
    }
    
    /* Desktop: Enable click functionality */
    @media (min-width: 769px) {
      .dosen-profile-header {
        pointer-events: auto !important;
        cursor: pointer !important;
      }
      
      .system-header .dropdown-menu {
        display: none; /* Hidden by default on desktop */
      }
      
      .system-header .dropdown-menu.show {
        display: block !important; /* Show when active on desktop */
      }
    }
    
    /* Responsive untuk system header */
    @media (max-width: 768px) {
      .system-header {
        padding: 10px 0;
      }
      
      .system-header-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }
      
      .system-info {
        justify-content: center;
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
      
      .dosen-profile-header {
        justify-content: center;
        cursor: default !important; /* Disable cursor di mobile */
        pointer-events: none; /* Disable click events di mobile */
      }
      
      .dosen-profile-header:hover {
        transform: none !important; /* Disable hover effect di mobile */
        background: rgba(255, 255, 255, 0.1) !important;
      }
      
      .dosen-info-header {
        text-align: center;
      }
      
      /* Hide dropdown di mobile */
      .system-header .dropdown-menu {
        display: none !important;
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
      
      .dosen-avatar-header {
        width: 35px;
        height: 35px;
        margin-right: 10px;
      }
      
      .dosen-name-header {
        font-size: 13px;
      }
      
      .dosen-role-header {
        font-size: 10px;
      }
    }
    
    @media (max-width: 400px) {
      .system-title {
        font-size: 13px;
      }
      
      .system-subtitle {
        font-size: 10px;
      }
    }
    
    /* Animasi untuk header */
    .system-header {
      animation: slideDown 0.5s ease-out;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
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
  </style>
</head>

<body>
  <!-- ========================================
       TAMBAHAN: HEADER SISTEM YANG FORMAL
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
          <div class="dosen-profile-header" id="dosenProfileDropdown" style="cursor: pointer;">
            <?php
              // Ambil foto dosen dari database atau session
              $foto_dosen = $this->session->userdata('foto');
              if (empty($foto_dosen)) {
                $dosen_id = $this->session->userdata('id');
                $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
                $foto_dosen = ($dosen && !empty($dosen->foto)) ? $dosen->foto : 'default.png';
              } else {
                $foto_dosen = !empty($foto_dosen) ? $foto_dosen : 'default.png';
              }
              $foto_path = base_url('cdn/img/dosen/' . $foto_dosen);
            ?>
            <img src="<?= $foto_path ?>" alt="Foto <?= $this->session->userdata('nama') ?>" class="dosen-avatar-header">
            <div class="dosen-info-header">
              <p class="dosen-name-header"><?= $this->session->userdata('nama') ?></p>
              <p class="dosen-role-header">Dosen</p>
            </div>
          </div>
          <div class="dropdown-menu" id="dosenDropdownMenu">
            <div class="dropdown-header">
              <h6 class="text-overflow m-0">Selamat Datang!</h6>
            </div>
            <a href="<?= base_url() ?>dosen/profil" class="dropdown-item">
              <i class="ni ni-single-02"></i>
              <span>Profil</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= base_url() ?>auth/logout" class="dropdown-item">
              <i class="ni ni-user-run"></i>
              <span>Logout</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidenav -->
  <nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand -->
      <div class="sidenav-header  align-items-center">
        <a class="navbar-brand" href="javascript:void(0)">
          <img src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" class="navbar-brand-img" alt="...">
        </a>
      </div>
      <div class="navbar-inner">
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
          <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/dashboard">
                <i class="ni ni-tv-2 text-primary"></i>
                <span class="nav-link-text">Dashboard</span>
              </a>
            </li>
            
            <!-- 1. Usulan Proposal -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/usulan_proposal">
                <i class="ni ni-app text-red"></i>
                <span class="nav-link-text">Usulan Proposal</span>
              </a>
            </li>
            
            <!-- 2. Bimbingan -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/bimbingan">
                <i class="ni ni-pin-3 text-success"></i>
                <span class="nav-link-text">Bimbingan</span>
              </a>
            </li>
            
            <!-- 3. Seminar Proposal -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/seminar_proposal">
                <i class="ni ni-books text-danger"></i>
                <span class="nav-link-text">Seminar Proposal</span>
              </a>
            </li>
            
            <!-- 4. Penelitian -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/penelitian">
                <i class="ni ni-bulb-61 text-purple"></i>
                <span class="nav-link-text">Penelitian</span>
              </a>
            </li>
            
            <!-- 5. Seminar Skripsi -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/seminar_skripsi">
                <i class="fa fa-list text-primary"></i>
                <span class="nav-link-text">Seminar Skripsi</span>
              </a>
            </li>
            
            <!-- 6. Publikasi -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/publikasi">
                <i class="fa fa-crown text-warning"></i>
                <span class="nav-link-text">Publikasi</span>
              </a>
            </li>
            
            <!-- Profil -->
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>dosen/profil">
                <i class="fa fa-user text-info"></i>
                <span class="nav-link-text">Profil</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
  
  <!-- Main content -->
  <div class="main-content" id="panel">
    <!-- Topnav -->
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- REMOVED: Search form dihapus sesuai permintaan -->
          
          <!-- Navbar links -->
          <ul class="navbar-nav align-items-center  ml-md-auto ">
            <li class="nav-item d-xl-none">
              <!-- Sidenav toggler -->
              <div class="pr-3 sidenav-toggler sidenav-toggler-dark" data-action="sidenav-pin" data-target="#sidenav-main">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </div>
            </li>
            <!-- REMOVED: Search button juga dihapus -->
          </ul>
          <ul class="navbar-nav align-items-center  ml-auto ml-md-0 ">
            <!-- User profile - hanya tampil di mobile -->
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                        <img alt="Foto <?= $this->session->userdata('nama') ?>" src="<?= $foto_path ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </span>
                    <div class="media-body ml-2 d-none d-lg-block">
                        <span class="mb-0 text-sm font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                    </div>
                </div>
              </a>
              <div class="dropdown-menu  dropdown-menu-right ">
                <div class="dropdown-header noti-title">
                  <h6 class="text-overflow m-0">Selamat Datang!</h6>
                </div>
                <a href="<?= base_url() ?>dosen/profil" class="dropdown-item">
                  <i class="ni ni-single-02"></i>
                  <span>Profil</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url() ?>auth/logout" class="dropdown-item">
                  <i class="ni ni-user-run"></i>
                  <span>Logout</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
    <!-- 🎯 Header with Inline Styles -->
    <div class="header bg-primary pb-6" 
         style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important; background-color: #1e3a8a !important;">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0" 
                  style="color: #ffffff !important; font-weight: 700 !important; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7) !important;">
                <?= $title ?>
              </h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark" style="background: transparent !important;">
                  <li class="breadcrumb-item" 
                      style="color: rgba(255, 255, 255, 0.9) !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;">
                    <a href="<?= base_url() ?>dosen/dashboard" 
                       style="color: #ffffff !important; text-decoration: none !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;">
                      <i class="fas fa-home" style="color: #ffffff !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;"></i>
                    </a>
                  </li>
                  <?php
                  $url = explode('/', str_replace("dosen/", "", uri_string()));
                  $link = '';
                  for ($i = 0; $i < count($url); $i++) {
                    $link .= $url[$i] . '/';
                    if (($i + 1) !== count($url)) {
                      echo '<li class="breadcrumb-item" style="color: rgba(255, 255, 255, 0.9) !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;">
                              <a href="' . base_url("dosen/" . $link) . '" style="color: rgba(255, 255, 255, 0.9) !important; text-decoration: none !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;">' . ucfirst($url[$i]) . '</a>
                            </li>';
                    } else {
                      echo '<li class="breadcrumb-item active" style="color: #ffffff !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5) !important;">' . ucfirst($url[$i]) . '</li>';
                    }
                  }
                  ?>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button type="button" class="btn btn-sm btn-neutral" onclick="window.history.back()" 
                      style="background-color: #ffffff !important; color: #1e3a8a !important; border: 2px solid #ffffff !important; font-weight: 600 !important;">
                Back
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Page content -->
    <div class="container-fluid mt--6">
      <?= $content ?>
      <!-- Footer -->
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6">
            <div class="copyright text-center  text-lg-left  text-muted">
              &copy; 2025 <a href="https://stkyakobus.ac.id" class="font-weight-bold ml-1" target="_blank">STK St. Yakobus</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  
  <!-- Argon Scripts -->
  <!-- Core -->
  <?php include('_main/js.php') ?>
  <?= $script ?>
  
  <!-- 🎯 INLINE JAVASCRIPT ONLY -->
  <script>
    level = '<?= $this->session->userdata('level') ?>';
    if (level == '1') {
      $('.admin').css('display', 'block');
    } else if (level == '2') {
      $('.dosen').css('display', 'block');
    }
    
    // Inline JavaScript untuk memastikan styling
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Applying inline header styling...');
      
      // Function untuk apply styling secara inline
      function applyInlineHeaderStyling() {
        // Header background
        var headers = document.querySelectorAll('.header.bg-primary');
        headers.forEach(function(header) {
          header.style.background = 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)';
          header.style.backgroundColor = '#1e3a8a';
        });
        
        // Title styling
        var titles = document.querySelectorAll('.header.bg-primary h6.h2, .header.bg-primary .h2');
        titles.forEach(function(title) {
          title.style.color = '#ffffff';
          title.style.fontWeight = '700';
          title.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.7)';
        });
        
        // Breadcrumb styling
        var breadcrumbs = document.querySelectorAll('.header.bg-primary .breadcrumb-item, .header.bg-primary .breadcrumb-item a');
        breadcrumbs.forEach(function(item) {
          item.style.color = 'rgba(255, 255, 255, 0.9)';
          item.style.textShadow = '1px 1px 2px rgba(0, 0, 0, 0.5)';
        });
        
        // Home icon
        var homeIcons = document.querySelectorAll('.header.bg-primary .fas.fa-home');
        homeIcons.forEach(function(icon) {
          icon.style.color = '#ffffff';
          icon.style.textShadow = '1px 1px 2px rgba(0, 0, 0, 0.5)';
        });
        
        // Back button
        var backButtons = document.querySelectorAll('.header.bg-primary .btn-neutral');
        backButtons.forEach(function(btn) {
          btn.style.backgroundColor = '#ffffff';
          btn.style.color = '#1e3a8a';
          btn.style.border = '2px solid #ffffff';
          btn.style.fontWeight = '600';
        });
        
        // Navbar top
        var navbarTop = document.querySelectorAll('.navbar-top.bg-primary');
        navbarTop.forEach(function(nav) {
          nav.style.background = 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)';
        });
        
        // User name
        var userNames = document.querySelectorAll('.navbar-top.bg-primary .nav-link .media-body span');
        userNames.forEach(function(span) {
          span.style.color = '#ffffff';
          span.style.fontWeight = '600';
          span.style.textShadow = '1px 1px 2px rgba(0, 0, 0, 0.5)';
        });
        
        console.log('Inline header styling applied successfully!');
      }
      
      // Apply immediately
      applyInlineHeaderStyling();
      
      // Apply after delays untuk catch dynamic content
      setTimeout(applyInlineHeaderStyling, 100);
      setTimeout(applyInlineHeaderStyling, 500);
      
      // Apply on window load
      window.addEventListener('load', applyInlineHeaderStyling);
    });
    
    // Enhanced active menu state
    function setActiveMenu() {
      var currentPath = window.location.pathname;
      var menuItems = document.querySelectorAll('.sidenav .nav-link');
      
      menuItems.forEach(function(item) {
        var href = item.getAttribute('href');
        if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
          item.classList.add('active');
        }
      });
    }
    
    // Function untuk adjust header berdasarkan sidebar
    function adjustSystemHeader() {
      var systemHeader = document.querySelector('.system-header');
      var sidebar = document.querySelector('.sidenav');
      
      if (systemHeader && sidebar) {
        var sidebarWidth = sidebar.offsetWidth;
        var windowWidth = window.innerWidth;
        
        // Jika desktop (>= 1200px) dan sidebar visible
        if (windowWidth >= 1200) {
          systemHeader.style.marginLeft = sidebarWidth + 'px';
        } 
        // Jika tablet landscape (>= 992px)
        else if (windowWidth >= 992) {
          systemHeader.style.marginLeft = sidebarWidth + 'px';
        }
        // Mobile dan tablet portrait
        else {
          systemHeader.style.marginLeft = '0px';
        }
      }
    }
    
    // Function untuk initialize dropdown di header sistem
    function initializeHeaderDropdown() {
      var dropdownToggle = document.getElementById('dosenProfileDropdown');
      var dropdownMenu = document.getElementById('dosenDropdownMenu');
      
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
            document.querySelectorAll('.dosen-profile-header.dropdown-active').forEach(function(toggle) {
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
    
    // Set active menu on load
    document.addEventListener('DOMContentLoaded', function() {
      setActiveMenu();
      adjustSystemHeader();
      initializeHeaderDropdown();
    });
    
    // Adjust on window resize
    window.addEventListener('resize', adjustSystemHeader);
  </script>
</body>

</html>