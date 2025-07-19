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
            <li class="nav-item dropdown">
              <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="media align-items-center">
                  <span class="avatar avatar-sm rounded-circle">
                    <?php
                      // Ambil foto dosen dari database
                      $dosen_id = $this->session->userdata('id');
                      $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
                      $foto_name = ($dosen && !empty($dosen->foto)) ? $dosen->foto : 'default.png';
                      $foto_path = base_url('cdn/img/dosen/' . $foto_name);
                    ?>
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
</body>

</html>