<?php
$uri_string = str_replace("staf/", "", uri_string());
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus">
  <meta name="author" content="STK St. Yakobus">
  <title><?= $title ?> - SIM Tugas Akhir STK St. Yakobus</title>
  <!-- Favicon -->
  <link rel="icon" href="<?= base_url() ?>assets/img/brand/favicon.png" type="image/png">
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <!-- Icons -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/vendor/nucleo/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= base_url() ?>assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
  <!-- Argon CSS -->
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/argon.css?v=1.2.0" type="text/css">
  
  <!-- ========== TAMBAHAN CSS UNTUK SIDEBAR RAPAT ========== -->
  <style>
    /* Logo Kampus Styling */
    .navbar-brand img {
      max-width: 140px;
      max-height: 45px;
      width: auto;
      height: auto;
    }
    
    /* Sidebar Menu Rapat */
    .sidenav .navbar-nav .nav-item {
      margin-bottom: 2px !important; /* Sangat rapat */
    }
    
    .sidenav .navbar-nav .nav-item .nav-link {
      padding: 10px 20px;
      margin: 0;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    
    .sidenav .navbar-nav .nav-item .nav-link:hover {
      background-color: rgba(0,0,0,0.05);
      transform: translateX(3px);
    }
    
    /* Divider lebih tipis */
    .sidenav hr {
      margin: 8px 0 !important; /* Dari my-3 jadi sangat rapat */
      border-color: rgba(0,0,0,0.1);
    }
    
    /* Profile card lebih compact */
    .profile-card-compact {
      margin-top: 10px !important;
      padding: 15px !important;
    }
    
    .profile-card-compact .avatar {
      width: 50px !important;
      height: 50px !important;
    }
    
    .profile-card-compact h6 {
      font-size: 14px;
      margin: 8px 0 4px;
    }
    
    .profile-card-compact p {
      font-size: 12px;
      margin-bottom: 10px;
    }
    
    /* Submenu styling */
    .sidenav .collapse .nav {
      padding-left: 15px;
      padding-top: 5px;
      padding-bottom: 5px;
    }
    
    .sidenav .collapse .nav .nav-item {
      margin-bottom: 1px;
    }
    
    .sidenav .collapse .nav .nav-link {
      padding: 6px 15px;
      font-size: 13px;
    }
  </style>
  
  <!-- Custom CSS -->
  <?php include('_main/css.php') ?>
  <?= $css ?>
</head>

<body>
  <!-- Sidenav -->
  <nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand dengan Logo Kampus -->
      <div class="sidenav-header align-items-center">
        <a class="navbar-brand" href="<?= base_url() ?>staf/dashboard">
          <!-- GANTI LOGO: Dari blue.png ke logo kampus -->
          <img src="https://stkyakobus.ac.id/skripsi/cdn/img/icons/20250703062346.png" 
               class="navbar-brand-img" 
               alt="STK Santo Yakobus Logo">
        </a>
      </div>
      <div class="navbar-inner">
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
          <!-- Nav items -->
          <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
              <a class="nav-link <?= $uri_string == 'dashboard' ? 'active' : '' ?>" href="<?= base_url() ?>staf/dashboard">
                <i class="ni ni-tv-2 text-primary"></i>
                <span class="nav-link-text">Dashboard</span>
              </a>
            </li>
            
            <!-- Workflow Section -->
            <li class="nav-item">
              <a class="nav-link collapsed" href="#workflow-menu" data-toggle="collapse" role="button" aria-expanded="false">
                <i class="ni ni-collection text-orange"></i>
                <span class="nav-link-text">Workflow Tahapan</span>
              </a>
              <div class="collapse" id="workflow-menu">
                <ul class="nav nav-sm flex-column">
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/bimbingan" class="nav-link <?= strpos($uri_string, 'bimbingan') !== false ? 'active' : '' ?>">
                      <i class="fas fa-book-open"></i>
                      <span class="nav-link-text">Bimbingan</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/seminar-proposal" class="nav-link <?= strpos($uri_string, 'seminar_proposal') !== false ? 'active' : '' ?>">
                      <i class="fas fa-presentation"></i>
                      <span class="nav-link-text">Seminar Proposal</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/penelitian" class="nav-link <?= strpos($uri_string, 'penelitian') !== false ? 'active' : '' ?>">
                      <i class="fas fa-search"></i>
                      <span class="nav-link-text">Penelitian</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/seminar-skripsi" class="nav-link <?= strpos($uri_string, 'seminar_skripsi') !== false ? 'active' : '' ?>">
                      <i class="fas fa-graduation-cap"></i>
                      <span class="nav-link-text">Seminar Skripsi</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/publikasi" class="nav-link <?= strpos($uri_string, 'publikasi') !== false ? 'active' : '' ?>">
                      <i class="fas fa-globe"></i>
                      <span class="nav-link-text">Publikasi</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <!-- Divider RAPAT: Dari my-3 dikurangi -->
            <hr class="my-1">
            
            <!-- Data Management Section -->
            <li class="nav-item">
              <a class="nav-link collapsed" href="#data-menu" data-toggle="collapse" role="button" aria-expanded="false">
                <i class="ni ni-folder-17 text-green"></i>
                <span class="nav-link-text">Manajemen Data</span>
              </a>
              <div class="collapse" id="data-menu">
                <ul class="nav nav-sm flex-column">
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/mahasiswa" class="nav-link <?= strpos($uri_string, 'mahasiswa') !== false ? 'active' : '' ?>">
                      <i class="fas fa-users"></i>
                      <span class="nav-link-text">Daftar Mahasiswa</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/dosen" class="nav-link <?= strpos($uri_string, 'dosen') !== false ? 'active' : '' ?>">
                      <i class="fas fa-chalkboard-teacher"></i>
                      <span class="nav-link-text">Daftar Dosen</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/laporan" class="nav-link <?= strpos($uri_string, 'laporan') !== false ? 'active' : '' ?>">
                      <i class="fas fa-chart-bar"></i>
                      <span class="nav-link-text">Laporan</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <!-- Divider RAPAT: Dari my-3 dikurangi -->
            <hr class="my-1">
            
            <!-- Profil -->
            <li class="nav-item">
              <a class="nav-link <?= strpos($uri_string, 'profil') !== false ? 'active' : '' ?>" href="<?= base_url() ?>staf/profil">
                <i class="ni ni-single-02 text-yellow"></i>
                <span class="nav-link-text">Profil</span>
              </a>
            </li>
          </ul>
          
          <!-- Divider -->
          <hr class="my-2">
          
          <!-- Heading -->
          <h6 class="navbar-heading p-0 text-muted">
            <span class="docs-normal">Informasi</span>
          </h6>
          
          <!-- Profile Card in Sidebar - LEBIH COMPACT -->
          <div class="card border-0 bg-gradient-info profile-card-compact">
            <div class="card-body text-center text-white">
              <div class="avatar avatar-sm mx-auto">
                <?php if($this->session->userdata('foto')): ?>
                  <img alt="Profile" src="<?= base_url('uploads/staf/' . $this->session->userdata('foto')) ?>" class="rounded-circle">
                <?php else: ?>
                  <img alt="Profile" src="<?= base_url() ?>assets/img/theme/default-avatar.png" class="rounded-circle">
                <?php endif; ?>
              </div>
              <h6 class="text-white mt-2 mb-1"><?= $this->session->userdata('nama') ?></h6>
              <p class="text-white text-sm mb-0">Staf Akademik</p>
              <div class="mt-2">
                <a href="<?= base_url() ?>staf/profil" class="btn btn-sm btn-outline-white">
                  <i class="ni ni-settings-gear-65"></i> Profil
                </a>
              </div>
            </div>
          </div>
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
          <!-- Search form -->
          <form class="navbar-search navbar-search-light form-inline mr-sm-3" id="navbar-search-main">
            <div class="form-group mb-0">
              <div class="input-group input-group-alternative input-group-merge">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input class="form-control" placeholder="Cari mahasiswa, dosen..." type="text">
              </div>
            </div>
            <button class="close" type="button" data-action="search-close" data-target="#navbar-search-main" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </form>
          
          <!-- Navbar links -->
          <ul class="navbar-nav align-items-center ml-md-auto">
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
            
            <!-- User Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                    <?php if($this->session->userdata('foto')): ?>
                      <img alt="Profile" src="<?= base_url('uploads/staf/' . $this->session->userdata('foto')) ?>">
                    <?php else: ?>
                      <img alt="Profile" src="<?= base_url() ?>assets/img/theme/default-avatar.png">
                    <?php endif; ?>
                  </span>
                  <div class="media-body ml-2 d-none d-lg-block">
                    <span class="mb-0 text-sm font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                  </div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-header noti-title">
                  <h6 class="text-overflow m-0">Selamat Datang!</h6>
                </div>
                <a href="<?= base_url() ?>staf/profil" class="dropdown-item">
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
    
    <!-- Header -->
    <div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0"><?= $title ?></h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item"><a href="<?= base_url() ?>staf/dashboard"><i class="fas fa-home"></i></a></li>
                  <?php
                  $url = explode('/', str_replace("staf/", "", uri_string()));
                  $link = '';
                  for ($i = 0; $i < count($url); $i++) {
                    $link .= $url[$i] . '/';
                    if (($i + 1) !== count($url)) {
                      echo '<li class="breadcrumb-item"><a href="' . base_url("staf/" . $link) . '">' . ucfirst($url[$i]) . '</a></li>';
                    } else {
                      echo '<li class="breadcrumb-item active">' . ucfirst($url[$i]) . '</li>';
                    }
                  }
                  ?>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <button type="button" class="btn btn-sm btn-neutral" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> Kembali
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
            <div class="copyright text-center text-lg-left text-muted">
              &copy; 2025 <a href="https://stkyakobus.ac.id" class="font-weight-bold ml-1" target="_blank">STK St. Yakobus</a>
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="https://stkyakobus.ac.id" class="nav-link" target="_blank">Website</a>
              </li>
              <li class="nav-item">
                <a href="mailto:sipd@stkyakobus.ac.id" class="nav-link">Bantuan</a>
              </li>
            </ul>
          </div>
        </div>
      </footer>
    </div>
  </div>
  
  <!-- Argon Scripts -->
  <!-- Core -->
  <?php include('_main/js.php') ?>
  <?= $script ?>
  
  <!-- Custom Scripts for Staf -->
  <script>
    // Auto-collapse sidebar menus
    $(document).ready(function() {
        // Expand active menu
        $('.nav-link.active').closest('.collapse').addClass('show');
        
        // Set level for conditional displays
        level = '5'; // Staf level
        $('.staf').css('display', 'block');
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Smooth transitions untuk dropdown
        $('.nav-link[data-toggle="collapse"]').on('click', function() {
            var target = $(this).attr('href');
            $(target).on('show.bs.collapse', function() {
                $(this).css('transition', 'height 0.3s ease');
            });
        });
    });
  </script>
</body>

</html>