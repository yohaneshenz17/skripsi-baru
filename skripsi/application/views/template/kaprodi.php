<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))) ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistem Informasi Skripsi - Kaprodi">
  <meta name="author" content="STK St. Yakobus">
  <title><?= $app->nama ?> - <?= $title ?></title>
  <?php include('_main/css.php') ?>
  
  <style>
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
    
    .system-header .dropdown {
      position: relative;
    }
    
    .kaprodi-profile-header {
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
    
    .kaprodi-profile-header:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .kaprodi-profile-header:active {
      transform: translateY(0);
    }
    
    /* Visual feedback saat dropdown active */
    .kaprodi-profile-header.dropdown-active {
      background: rgba(255, 255, 255, 0.2) !important;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .kaprodi-avatar-header {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 12px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      object-fit: cover;
    }
    
    .kaprodi-info-header {
      text-align: right;
    }
    
    .kaprodi-name-header {
      font-size: 14px;
      font-weight: 600;
      margin: 0;
      color: white;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }
    
    .kaprodi-role-header {
      font-size: 11px;
      margin: 0;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 400;
    }
    
    /* Dropdown styling untuk header kaprodi */
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
    
    /* Desktop: Enable click functionality */
    @media (min-width: 769px) {
      .kaprodi-profile-header {
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
      
      .kaprodi-profile-header {
        justify-content: center;
        cursor: default !important; /* Disable cursor di mobile */
        pointer-events: none; /* Disable click events di mobile */
      }
      
      .kaprodi-profile-header:hover {
        transform: none !important; /* Disable hover effect di mobile */
        background: rgba(255, 255, 255, 0.1) !important;
      }
      
      .kaprodi-info-header {
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
      
      .kaprodi-avatar-header {
        width: 35px;
        height: 35px;
        margin-right: 10px;
      }
      
      .kaprodi-name-header {
        font-size: 13px;
      }
      
      .kaprodi-role-header {
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
          <div class="kaprodi-profile-header" id="kaprodiProfileDropdown" style="cursor: pointer;">
            <?php
              // Ambil foto dosen dari database
              $dosen_id = $this->session->userdata('id');
              $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
              $foto_name = ($dosen && !empty($dosen->foto)) ? $dosen->foto : 'default.png';
              $foto_path = base_url('cdn/img/dosen/' . $foto_name);
            ?>
            <img src="<?= $foto_path ?>" alt="Foto <?= $this->session->userdata('nama') ?>" class="kaprodi-avatar-header">
            <div class="kaprodi-info-header">
              <p class="kaprodi-name-header"><?= $this->session->userdata('nama') ?></p>
              <p class="kaprodi-role-header">Kaprodi</p>
            </div>
          </div>
          <div class="dropdown-menu" id="kaprodiDropdownMenu">
            <div class="dropdown-header">
              <h6 class="text-overflow m-0">Selamat Datang!</h6>
            </div>
            <a href="<?= base_url() ?>kaprodi/profil" class="dropdown-item">
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
              <!-- Dashboard -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/dashboard">
                  <i class="ni ni-tv-2 text-primary"></i>
                  <span class="nav-link-text">Dashboard</span>
                </a>
              </li>
              
              <!-- Pengumuman Tahapan -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/pengumuman">
                  <i class="ni ni-bell-55 text-warning"></i>
                  <span class="nav-link-text">Pengumuman Tahapan</span>
                </a>
              </li>
              
              <!-- Usulan Proposal (sebelumnya Penetapan Pembimbing) -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/proposal">
                  <i class="ni ni-paper-diploma text-orange"></i>
                  <span class="nav-link-text">Usulan Proposal</span>
                </a>
              </li>
              
              <!-- Seminar Proposal -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/seminar_proposal">
                  <i class="ni ni-hat-3 text-blue"></i>
                  <span class="nav-link-text">Seminar Proposal</span>
                </a>
              </li>
              
              <!-- Seminar Skripsi -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/seminar_skripsi">
                  <i class="ni ni-books text-green"></i>
                  <span class="nav-link-text">Seminar Skripsi</span>
                </a>
              </li>
              
              <!-- Publikasi -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/publikasi">
                  <i class="fa fa-crown text-warning"></i>
                  <span class="nav-link-text">Publikasi</span>
                </a>
              </li>
              
              <!-- Daftar Mahasiswa -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/mahasiswa">
                  <i class="fa fa-users text-info"></i>
                  <span class="nav-link-text">Daftar Mahasiswa</span>
                </a>
              </li>
              
              <!-- Daftar Dosen -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/dosen">
                  <i class="fa fa-user-tie text-purple"></i>
                  <span class="nav-link-text">Daftar Dosen</span>
                </a>
              </li>
              
              <!-- Laporan -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/laporan">
                  <i class="ni ni-chart-bar-32 text-red"></i>
                  <span class="nav-link-text">Laporan</span>
                </a>
              </li>
              
              <!-- Profil -->
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>kaprodi/profil">
                  <i class="fa fa-user text-info"></i>
                  <span class="nav-link-text">Profil</span>
                </a>
              </li>
            </ul>
        </div>
      </div>
    </div>
  </nav>
  <div class="main-content" id="panel">
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav align-items-center  ml-md-auto ">
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
            <!-- User profile - hanya tampil di mobile -->
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                    <img alt="Image placeholder" src="<?= $foto_path ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  </span>
                  <div class="media-body  ml-2  d-none d-lg-block">
                    <span class="mb-0 text-sm  font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                  </div>
                </div>
              </a>
              <div class="dropdown-menu  dropdown-menu-right ">
                <div class="dropdown-header noti-title">
                  <h6 class="text-overflow m-0">Selamat Datang!</h6>
                </div>
                <a href="<?= base_url() ?>kaprodi/profil" class="dropdown-item">
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
    <div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0"><?= $title ?></h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item"><a href="<?= base_url() ?>kaprodi/dashboard"><i class="fas fa-home"></i></a></li>
                  <?php
                  $url = explode('/', str_replace("kaprodi/", "", uri_string()));
                  $link = '';
                  for ($i = 0; $i < count($url); $i++) {
                    $link .= $url[$i] . '/';
                    if (($i + 1) !== count($url)) {
                      echo '<li class="breadcrumb-item"><a href="' . base_url("kaprodi/" . $link) . '">' . ucfirst($url[$i]) . '</a></li>';
                    } else {
                      echo '<li class="breadcrumb-item active">' . ucfirst($url[$i]) . '</li>';
                    }
                  }
                  ?>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button type="button" class="btn btn-sm btn-neutral" onclick="window.history.back()">Back</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--6">
      <?= $content ?>
      <footer class="footer pt-0">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6">
            <div class="copyright text-center  text-lg-left  text-muted">
              &copy; <?= date('Y') ?> <a href="#" class="font-weight-bold ml-1">STK St. Yakobus Merauke</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <?php include('_main/js.php') ?>
  <?= isset($script) ? $script : '' ?>
  
  <script>
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
      var dropdownToggle = document.getElementById('kaprodiProfileDropdown');
      var dropdownMenu = document.getElementById('kaprodiDropdownMenu');
      
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
            document.querySelectorAll('.kaprodi-profile-header.dropdown-active').forEach(function(toggle) {
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