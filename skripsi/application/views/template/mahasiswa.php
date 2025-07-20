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
  
  <!-- Custom CSS untuk sidebar -->
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
    
    .navbar-vertical .navbar-nav .nav-link.active {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        border-radius: 0.375rem;
        color: #fff;
    }
  </style>
</head>

<body>
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
              
              <!-- HK3 - dipindah kebawah -->
              <li class="nav-item">
                <a class="nav-link <?= $this->uri->segment(2) == 'hasilkegiatan' ? 'active' : '' ?>" 
                   href="<?= base_url() ?>mahasiswa/hasilkegiatan">
                  <i class="fa fa-crown text-pink"></i><span class="nav-link-text">HK3</span>
                </a>
              </li>
              
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
                  $avatar_path = base_url('cdn/img/mahasiswa/' . ($foto ? $foto : 'default.png'));
                  ?>
                  <img src="<?= $avatar_path ?>" alt="Foto Profile" class="avatar rounded-circle" 
                       style="width: 48px; height: 48px; object-fit: cover;">
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
                      $foto = $this->session->userdata('foto');
                      $foto_path = base_url('cdn/img/mahasiswa/' . ($foto ? $foto : 'default.png'));
                    ?>
                    <img alt="Image placeholder" src="<?= $foto_path ?>" style="width: 100%; height: 100%; object-fit: cover;">
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
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0"><?= isset($title) ? $title : 'Dashboard' ?></h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                  <li class="breadcrumb-item">
                    <a href="<?= base_url('mahasiswa/dashboard') ?>"><i class="fas fa-home"></i></a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page"><?= isset($title) ? $title : 'Dashboard' ?></li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <a href="<?= base_url('mahasiswa/kontak') ?>" class="btn btn-sm btn-neutral">Kontak</a>
              <a href="<?= base_url('mahasiswa/profil') ?>" class="btn btn-sm btn-neutral">Profil</a>
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
  
  <!-- Auto-load notifications -->
  <script>
  $(document).ready(function() {
    // Load notifikasi untuk dropdown
    loadNotifikasiDropdown();
    
    // Refresh notifikasi setiap 5 menit
    setInterval(loadNotifikasiDropdown, 300000);
  });
  
  function loadNotifikasiDropdown() {
    if (typeof call === 'function') {
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
                          <h4 class="mb-0 text-sm">${notif.judul}</h4>
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
      });
    }
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
  </script>
</body>
</html>