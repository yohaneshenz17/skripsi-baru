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
            <li class="nav-item">
              <a class="nav-link" href="<?= base_url('mahasiswa/dashboard') ?>">
                <i class="ni ni-tv-2 text-primary"></i><span class="nav-link-text">Dashboard</span>
              </a>
            </li>
            <?php if ($verifikasi == 1) { ?>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>mahasiswa/proposal">
                  <i class="ni ni-send text-orange"></i><span class="nav-link-text">Usulan Proposal</span>
                </a>
              </li>
              <li class="nav-item">
              <a class="nav-link" href="<?= base_url() ?>mahasiswa/bimbingan">
                <i class="ni ni-books text-purple"></i><span class="nav-link-text">Bimbingan</span>
              </a>
            </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>mahasiswa/seminar">
                  <i class="ni ni-calendar-grid-58 text-info"></i><span class="nav-link-text">Seminar Proposal</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>mahasiswa/penelitian">
                  <i class="ni ni-single-copy-04 text-green"></i><span class="nav-link-text">Penelitian</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>mahasiswa/skripsi">
                  <i class="ni ni-collection text-yellow"></i><span class="nav-link-text">Seminar Akhir / Skripsi</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?= base_url() ?>mahasiswa/hasilkegiatan">
                  <i class="fa fa-crown text-warning"></i><span class="nav-link-text">HK3</span>
                </a>
              </li> 
            <?php } ?>
          </ul>
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
    <div class="header bg-primary pb-6">
        </div>
    <div class="container-fluid mt--6">
      <?= isset($content) ? $content : '' ?>
      <footer class="footer pt-0">
          </footer>
    </div>
  </div>
  <?php include('_main/js.php') ?>
  <?= isset($script) ? $script : '' ?>
</body>
</html>