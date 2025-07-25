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
  
  <!-- ========== PERBAIKAN STYLING STAF (MINIMAL & CLEAN) ========== -->
  <style>
    /* Perbaikan Typography */
    body {
      font-family: 'Open Sans', sans-serif;
      font-size: 14px;
      line-height: 1.6;
    }

    /* Logo Kampus */
    .navbar-brand img {
      max-width: 140px;
      max-height: 45px;
      width: auto;
      height: auto;
    }
    
    /* Sidebar Menu Spacing */
    .sidenav .navbar-nav .nav-item {
      margin-bottom: 4px;
    }
    
    .sidenav .navbar-nav .nav-item .nav-link {
      padding: 12px 20px;
      margin: 0 8px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .sidenav .navbar-nav .nav-item .nav-link:hover {
      background-color: #f8f9fa;
      transform: translateX(2px);
    }
    
    .sidenav .navbar-nav .nav-item .nav-link.active {
      background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
      color: #fff;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11);
    }

    /* Submenu */
    .sidenav .collapse .nav {
      padding: 8px 0;
      margin-left: 12px;
      border-left: 2px solid #e9ecef;
    }
    
    .sidenav .collapse .nav .nav-link {
      padding: 8px 16px;
      margin: 0 4px;
      font-size: 13px;
      color: #6c757d;
    }
    
    .sidenav .collapse .nav .nav-link:hover {
      background-color: #f1f3f4;
      color: #5e72e4;
    }
    
    .sidenav .collapse .nav .nav-link.active {
      background-color: #e3f2fd;
      color: #2196f3;
      border-left: 3px solid #2196f3;
      font-weight: 600;
    }

    /* Profile Card Improvements */
    .profile-card-compact {
      margin: 16px 8px 0;
      border-radius: 12px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: 0;
    }

    .profile-card-compact .card-body {
      padding: 20px 16px;
    }

    .profile-card-compact .avatar {
      width: 56px !important;
      height: 56px !important;
      border: 3px solid rgba(255,255,255,0.2);
    }

    .profile-card-compact h6 {
      font-size: 15px;
      font-weight: 600;
      margin: 12px 0 4px;
      color: #fff;
    }

    .profile-card-compact p {
      font-size: 12px;
      margin-bottom: 12px;
      color: rgba(255,255,255,0.8);
    }

    .profile-card-compact .btn-outline-white {
      border-color: rgba(255,255,255,0.3);
      color: #fff;
      font-size: 12px;
      padding: 6px 16px;
      border-radius: 20px;
    }

    .profile-card-compact .btn-outline-white:hover {
      background-color: rgba(255,255,255,0.2);
      border-color: rgba(255,255,255,0.5);
    }

    /* Divider */
    .sidenav hr {
      margin: 16px 8px;
      border-color: #e9ecef;
    }

    /* Workflow icons */
    .workflow-icon {
      width: 16px;
      text-align: center;
      margin-right: 10px;
    }

    /* Card improvements */
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    /* Button improvements */
    .btn-neutral {
      background-color: #fff;
      border-color: #fff;
      color: #5e72e4;
      transition: all 0.3s ease;
    }

    .btn-neutral:hover {
      transform: translateY(-1px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1);
    }

    /* Alert improvements */
    .alert {
      border-radius: 8px;
      font-weight: 500;
    }

    /* Form improvements */
    .form-control {
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #5e72e4;
      box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
    }

    /* PERBAIKAN: CSS untuk foto profil staf yang konsisten */
    .staf-profile-photo {
        transition: all 0.3s ease;
        object-fit: cover;
    }
    
    .staf-profile-photo:hover {
        transform: scale(1.05);
    }
    
    .staf-header-avatar {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    .staf-sidebar-avatar {
        width: 56px !important;
        height: 56px !important;
        border-radius: 50%;
    }
    
    .loading-photo {
        opacity: 0.6;
        animation: pulse 1.5s ease-in-out infinite alternate;
    }
    
    @keyframes pulse {
        from { opacity: 0.6; }
        to { opacity: 1; }
    }
  </style>
  
  <!-- Custom CSS -->
  <?php include('_main/css.php') ?>
  <?= isset($css) ? $css : '' ?>
</head>

<body>
  <!-- Sidenav -->
  <nav class="sidenav navbar navbar-vertical fixed-left navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
      <!-- Brand dengan Logo Kampus -->
      <div class="sidenav-header align-items-center">
        <a class="navbar-brand" href="<?= base_url() ?>staf/dashboard">
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
                      <i class="fas fa-book-open workflow-icon text-info"></i>
                      <span class="nav-link-text">Bimbingan</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/seminar-proposal" class="nav-link <?= strpos($uri_string, 'seminar_proposal') !== false ? 'active' : '' ?>">
                      <i class="fas fa-chalkboard workflow-icon text-success"></i>
                      <span class="nav-link-text">Seminar Proposal</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/penelitian" class="nav-link <?= strpos($uri_string, 'penelitian') !== false ? 'active' : '' ?>">
                      <i class="fas fa-search workflow-icon text-primary"></i>
                      <span class="nav-link-text">Penelitian</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/seminar-skripsi" class="nav-link <?= strpos($uri_string, 'seminar_skripsi') !== false ? 'active' : '' ?>">
                      <i class="fas fa-graduation-cap workflow-icon text-warning"></i>
                      <span class="nav-link-text">Seminar Skripsi</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/publikasi" class="nav-link <?= strpos($uri_string, 'publikasi') !== false ? 'active' : '' ?>">
                      <i class="fas fa-globe workflow-icon text-danger"></i>
                      <span class="nav-link-text">Publikasi</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <!-- Divider -->
            <hr class="my-3">
            
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
                      <i class="fas fa-users workflow-icon"></i>
                      <span class="nav-link-text">Daftar Mahasiswa</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/dosen" class="nav-link <?= strpos($uri_string, 'dosen') !== false ? 'active' : '' ?>">
                      <i class="fas fa-chalkboard-teacher workflow-icon"></i>
                      <span class="nav-link-text">Daftar Dosen</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="<?= base_url() ?>staf/laporan" class="nav-link <?= strpos($uri_string, 'laporan') !== false ? 'active' : '' ?>">
                      <i class="fas fa-chart-bar workflow-icon"></i>
                      <span class="nav-link-text">Laporan</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <!-- Divider -->
            <hr class="my-3">
            
            <!-- Profil -->
            <li class="nav-item">
              <a class="nav-link <?= strpos($uri_string, 'profil') !== false ? 'active' : '' ?>" href="<?= base_url() ?>staf/profil">
                <i class="ni ni-single-02 text-yellow"></i>
                <span class="nav-link-text">Profil</span>
              </a>
            </li>
          </ul>
          
          <!-- Divider -->
          <hr class="my-3">
          
          <!-- Heading -->
          <h6 class="navbar-heading p-0 text-muted">
            <span class="docs-normal">Informasi</span>
          </h6>
          
          <!-- Profile Card in Sidebar -->
          <div class="card border-0 profile-card-compact">
            <div class="card-body text-center text-white">
              <div class="avatar avatar-sm mx-auto">
                <!-- PERBAIKAN: Tambahkan ID dan class yang konsisten -->
                <?php if($this->session->userdata('foto')): ?>
                  <img id="sidebar-staf-photo" 
                       class="staf-profile-photo staf-sidebar-avatar" 
                       alt="Profile" 
                       src="<?= base_url('cdn/img/staf/' . $this->session->userdata('foto')) ?>">
                <?php else: ?>
                  <img id="sidebar-staf-photo" 
                       class="staf-profile-photo staf-sidebar-avatar" 
                       alt="Profile" 
                       src="<?= base_url() ?>assets/img/theme/default-avatar.png">
                <?php endif; ?>
              </div>
              <h6 class="text-white mt-3 mb-1"><?= $this->session->userdata('nama') ?></h6>
              <p class="text-white-80 mb-0">Staf Akademik</p>
              <div class="mt-3">
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
                    <!-- PERBAIKAN: Tambahkan ID dan class yang konsisten -->
                    <?php if($this->session->userdata('foto')): ?>
                      <img id="header-staf-photo" 
                           class="staf-profile-photo staf-header-avatar" 
                           alt="Profile" 
                           src="<?= base_url('cdn/img/staf/' . $this->session->userdata('foto')) ?>">
                    <?php else: ?>
                      <img id="header-staf-photo" 
                           class="staf-profile-photo staf-header-avatar" 
                           alt="Profile" 
                           src="<?= base_url() ?>assets/img/theme/default-avatar.png">
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
  <?= isset($script) ? $script : '' ?>
  
  <!-- PERBAIKAN: JavaScript untuk update foto staf real-time -->
  <script>
    // Pastikan variabel global tersedia
    if (typeof base_url === 'undefined') {
        var base_url = '<?= base_url() ?>';
    }
    
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

        // Auto hide alerts
        $('.alert').each(function() {
            var alert = $(this);
            setTimeout(function() {
                alert.fadeOut('slow');
            }, 5000);
        });
        
        // PERBAIKAN: Auto-refresh foto jika ada flashdata update
        <?php if($this->session->flashdata('foto_updated')): ?>
            const updatedFoto = '<?= $this->session->flashdata('foto_updated') ?>';
            console.log('Foto updated detected:', updatedFoto);
            setTimeout(function() {
                updateStafProfilePhoto(updatedFoto);
            }, 1000);
        <?php endif; ?>
        
        <?php if($this->session->flashdata('foto_deleted')): ?>
            console.log('Foto deleted detected');
            setTimeout(function() {
                updateStafProfilePhoto('');
            }, 1000);
        <?php endif; ?>
    });
    
    // PERBAIKAN: Function global untuk update foto staf
    window.updateStafProfilePhoto = function(fotoName) {
        console.log('Updating staf profile photo:', fotoName);
        
        const timestamp = new Date().getTime();
        let fotoUrl;
        
        if (!fotoName || fotoName === '' || fotoName === 'default.png') {
            fotoUrl = base_url + 'assets/img/theme/default-avatar.png';
        } else {
            fotoUrl = base_url + 'cdn/img/staf/' + fotoName;
        }
        
        // Tambahkan timestamp untuk cache busting
        const finalUrl = fotoUrl + '?v=' + timestamp;
        
        console.log('Final photo URL:', finalUrl);
        
        // Update foto di header
        updatePhotoElement('#header-staf-photo', finalUrl);
        
        // Update foto di sidebar
        updatePhotoElement('#sidebar-staf-photo', finalUrl);
        
        // Update semua foto staf lainnya dengan class
        $('.staf-profile-photo').each(function() {
            updatePhotoElement(this, finalUrl);
        });
        
        console.log('All staf profile photos updated successfully');
    };
    
    // Helper function untuk update foto dengan loading state
    function updatePhotoElement(selector, url) {
        const $element = $(selector);
        if ($element.length > 0) {
            $element.addClass('loading-photo');
            
            // Test load gambar terlebih dahulu
            const testImg = new Image();
            testImg.onload = function() {
                $element.attr('src', url);
                $element.removeClass('loading-photo');
                console.log('Photo updated:', selector);
            };
            testImg.onerror = function() {
                console.warn('Failed to load photo:', url);
                $element.removeClass('loading-photo');
            };
            testImg.src = url;
        }
    }
  </script>
</body>

</html>