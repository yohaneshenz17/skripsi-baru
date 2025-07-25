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
  
  <!-- FIXED: CSS untuk sidebar dengan kontras yang baik -->
  <style>
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
    
    /* PERBAIKAN: CSS untuk foto profil yang responsive */
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
    
    /* FIXED: Perbaikan untuk menu sidebar yang kontras */
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
       PERBAIKAN MOBILE LAYOUT - HEADER BUTTONS
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

    /* Mobile Large (576px - 767px) */
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

    /* Mobile Medium (480px - 575px) */
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
      
      /* Center align pada mobile */
      .col-12.text-right {
        text-align: center !important;
      }
    }

    /* Mobile Small (375px - 479px) */
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
      
      /* Adjust header padding */
      .header-body .py-4 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
      }
    }

    /* Mobile Extra Small (320px - 374px) */
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
      
      .col-12.text-right {
        text-align: center !important;
      }
      
      /* Kompak container */
      .container-fluid {
        padding-left: 10px;
        padding-right: 10px;
      }
      
      .header-body .col-12 {
        padding-left: 5px;
        padding-right: 5px;
      }
    }

    /* Untuk layar sangat kecil - stack vertikal */
    @media (max-width: 320px) {
      .header-button-group {
        flex-direction: column;
        gap: 3px;
        align-items: stretch;
        margin-top: 10px;
      }
      
      .header-btn {
        width: 100%;
        max-width: 120px;
        margin: 0 auto;
        text-align: center;
        font-size: 0.65rem;
        padding: 4px 8px;
      }
      
      .mobile-title {
        font-size: 0.9rem !important;
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

    /* Icon styling */
    .header-btn i {
      font-size: 0.875rem;
      margin-right: 4px;
    }

    @media (max-width: 575.98px) {
      .header-btn i {
        font-size: 0.75rem;
        margin-right: 2px;
      }
    }

    /* iPhone specific fixes */
    @media (max-width: 414px) and (orientation: portrait) {
      .header-body .row {
        margin-bottom: 0;
      }
      
      .header-button-group {
        margin-top: 8px;
      }
    }

    /* Landscape mobile */
    @media (max-height: 500px) and (orientation: landscape) {
      .header-body .py-4 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
      }
      
      .mobile-title {
        font-size: 1rem !important;
      }
      
      .header-btn {
        padding: 3px 6px;
        font-size: 0.7rem;
      }
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
  </style>
</head>

<body>
  <!-- ========================================
       TAMBAHAN: HEADER SISTEM YANG FORMAL
       ======================================== -->
  <div class="system-header">
    <div class="container-fluid">
      <div class="text-center">
        <div class="d-flex justify-content-center align-items-center">
          <i class="ni ni-hat-3 system-header-icon"></i>
          <div>
            <h1 class="system-title">SISTEM INFORMASI MANAJEMEN TUGAS AKHIR</h1>
            <p class="system-subtitle">Sekolah Tinggi Katolik Santo Yakobus Merauke</p>
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
              
              <!-- REMOVED: HK3 Menu - Dihapus karena tidak ada di workflow -->
              
              <!-- Divider -->
              <li class="nav-item">
                <hr class="my-3">
              </li>
              
            <?php } ?>
            
            <!-- 8. Profil - Dipindahkan ke bawah -->
            <li class="nav-item">
              <a class="nav-link <?= $this->uri->segment(2) == 'profil' ? 'active' : '' ?>" 
                 href="<?= base_url('mahasiswa/profil') ?>">
                <i class="ni ni-single-02 text-blue"></i><span class="nav-link-text">Profil</span>
              </a>
            </li>
            
            <!-- 9. Kontak Form - Fitur Baru -->
            <li class="nav-item">
              <a class="nav-link <?= $this->uri->segment(2) == 'kontak' ? 'active' : '' ?>" 
                 href="<?= base_url('mahasiswa/kontak') ?>">
                <i class="ni ni-email-83 text-pink"></i><span class="nav-link-text">Kontak Form</span>
              </a>
            </li>
          </ul>
          
          <!-- Profile Card - Seperti template dosen/kaprodi -->
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
  
  <div class="main-content" id="panel">
    <nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom">
      <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- TOMBOL HAMBURGER MENU UNTUK MOBILE - DITAMBAHKAN -->
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
            <!-- Notifications Dropdown (Enhanced) -->
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
                        // PERBAIKAN: Multi-layer fallback untuk foto
                        $mahasiswa_id = $this->session->userdata('id');
                        $foto_session = $this->session->userdata('foto');
                        
                        $foto_name = 'default.png';
                        
                        // Layer 1: Cek session
                        if (!empty($foto_session) && $foto_session !== 'default.png') {
                            $foto_name = $foto_session;
                        } else {
                            // Layer 2: Query database
                            try {
                                $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
                                if ($mahasiswa && !empty($mahasiswa->foto)) {
                                    $foto_name = $mahasiswa->foto;
                                    // Sync session dengan database
                                    $this->session->set_userdata('foto', $foto_name);
                                }
                            } catch (Exception $e) {
                                log_message('error', 'Error loading mahasiswa photo: ' . $e->getMessage());
                                $foto_name = 'default.png';
                            }
                        }
                        
                        // Cache busting dengan timestamp
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
    
    <!-- FIXED: Header dengan breadcrumb - MOBILE RESPONSIVE -->
    <div class="header bg-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <!-- FIXED: Grid system responsive -->
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
            <!-- FIXED: Button container responsive -->
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
  
  <!-- FIXED: Auto-load notifications dengan error handling -->
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
    // Load notifikasi untuk dropdown
    loadNotifikasiDropdown();
    
    // Refresh notifikasi setiap 5 menit
    setInterval(loadNotifikasiDropdown, 300000);
  });
  
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
      <!-- PERBAIKAN: JavaScript untuk update foto real-time -->
    <script>
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
    </script>
</body>
</html>