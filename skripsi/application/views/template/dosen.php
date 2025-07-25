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
  </style>
</head>

<body>
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
          <!-- Search form -->
          <form class="navbar-search navbar-search-light form-inline mr-sm-3" id="navbar-search-main">
            <div class="form-group mb-0">
              <div class="input-group input-group-alternative input-group-merge">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input class="form-control" placeholder="Search" type="text">
              </div>
            </div>
            <button type="button" class="close" data-action="search-close" data-target="#navbar-search-main" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </form>
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
            <li class="nav-item d-sm-none">
              <a class="nav-link" href="#" data-action="search-show" data-target="#navbar-search-main">
                <i class="ni ni-zoom-split-in"></i>
              </a>
            </li>
          </ul>
          <ul class="navbar-nav align-items-center  ml-auto ml-md-0 ">
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
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
    
    // Set active menu on load
    document.addEventListener('DOMContentLoaded', setActiveMenu);
  </script>
</body>

</html>