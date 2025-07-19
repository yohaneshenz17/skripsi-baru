<?php
// ============================================
// FILE: application/views/template/kaprodi.php (DIPERBAIKI)
// ============================================

defined('BASEPATH') OR exit('No direct script access allowed');

// PERBAIKAN: Cek session login dan level
if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
    redirect('auth/login');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= isset($title) ? $title : 'Dashboard Kaprodi' ?> | SIM Tugas Akhir STK Santo Yakobus</title>
    
    <!-- Bootstrap CSS -->
    <link href="<?= base_url() ?>cdn/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Argon CSS -->
    <link href="<?= base_url() ?>cdn/css/argon.css?v=1.0.0" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="<?= base_url() ?>cdn/vendor/datatables/datatables.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?= base_url() ?>cdn/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    
    <style>
        .sidebar-brand-img {
            max-height: 2rem;
        }
        .navbar-brand img {
            max-height: 30px;
        }
        .progress-wrapper {
            position: relative;
        }
        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .progress-percentage {
            font-size: 0.875rem;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-default">
    <!-- Sidenav -->
    <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
        <div class="container-fluid">
            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Brand -->
            <a class="navbar-brand pt-0" href="<?= base_url('kaprodi/dashboard') ?>">
                <img src="<?= base_url() ?>cdn/img/brand/blue.png" class="navbar-brand-img" alt="STK Santo Yakobus">
            </a>
            
            <!-- User Info -->
            <ul class="nav align-items-center d-md-none">
                <li class="nav-item dropdown">
                    <a class="nav-link nav-link-icon" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ni ni-bell-55"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="media align-items-center">
                            <span class="avatar avatar-sm rounded-circle">
                                <?php 
                                $foto = $this->session->userdata('foto');
                                $foto_path = !empty($foto) && $foto != 'default.png' ? 
                                    base_url('cdn/img/dosen/' . $foto) : 
                                    base_url('cdn/img/theme/team-4-800x800.jpg');
                                ?>
                                <img alt="Foto Profil" src="<?= $foto_path ?>">
                            </span>
                        </div>
                    </a>
                </li>
            </ul>
            
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Collapse header -->
                <div class="navbar-collapse-header d-md-none">
                    <div class="row">
                        <div class="col-6 collapse-brand">
                            <a href="<?= base_url('kaprodi/dashboard') ?>">
                                <img src="<?= base_url() ?>cdn/img/brand/blue.png" alt="STK Santo Yakobus">
                            </a>
                        </div>
                        <div class="col-6 collapse-close">
                            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <ul class="navbar-nav">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'dashboard' || $this->uri->segment(2) == '') ? 'active' : '' ?>" href="<?= base_url('kaprodi/dashboard') ?>">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Pengumuman Tahapan -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'pengumuman') ? 'active' : '' ?>" href="<?= base_url('kaprodi/pengumuman') ?>">
                            <i class="ni ni-bell-55 text-warning"></i> Pengumuman Tahapan
                        </a>
                    </li>
                    
                    <!-- Usulan Proposal -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'proposal' || $this->uri->segment(2) == 'review_proposal') ? 'active' : '' ?>" href="<?= base_url('kaprodi/proposal') ?>">
                            <i class="ni ni-paper-diploma text-info"></i> Usulan Proposal
                        </a>
                    </li>
                    
                    <!-- Seminar Proposal -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'seminar_proposal') ? 'active' : '' ?>" href="<?= base_url('kaprodi/seminar_proposal') ?>">
                            <i class="ni ni-chart-pie-35 text-orange"></i> Seminar Proposal
                        </a>
                    </li>
                    
                    <!-- Seminar Skripsi -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'seminar_skripsi') ? 'active' : '' ?>" href="<?= base_url('kaprodi/seminar_skripsi') ?>">
                            <i class="ni ni-hat-3 text-success"></i> Seminar Skripsi
                        </a>
                    </li>
                    
                    <!-- Publikasi -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'publikasi') ? 'active' : '' ?>" href="<?= base_url('kaprodi/publikasi') ?>">
                            <i class="ni ni-trophy text-yellow"></i> Publikasi
                        </a>
                    </li>
                    
                    <!-- Daftar Mahasiswa -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'mahasiswa') ? 'active' : '' ?>" href="<?= base_url('kaprodi/mahasiswa') ?>">
                            <i class="ni ni-bullet-list-67 text-blue"></i> Daftar Mahasiswa
                        </a>
                    </li>
                    
                    <!-- Daftar Dosen -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'dosen') ? 'active' : '' ?>" href="<?= base_url('kaprodi/dosen') ?>">
                            <i class="ni ni-single-02 text-pink"></i> Daftar Dosen
                        </a>
                    </li>
                    
                    <!-- Laporan -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'laporan') ? 'active' : '' ?>" href="<?= base_url('kaprodi/laporan') ?>">
                            <i class="ni ni-chart-bar-32 text-purple"></i> Laporan
                        </a>
                    </li>
                    
                    <!-- Profil -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($this->uri->segment(2) == 'profil') ? 'active' : '' ?>" href="<?= base_url('kaprodi/profil') ?>">
                            <i class="ni ni-badge text-gray"></i> Profil
                        </a>
                    </li>
                </ul>
                
                <!-- Divider -->
                <hr class="my-3">
                
                <!-- Logout -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('auth/logout') ?>" onclick="return confirm('Yakin ingin logout?')">
                            <i class="ni ni-user-run text-danger"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="#">
                    <?= isset($title) ? $title : 'Dashboard Kaprodi' ?>
                </a>
                
                <!-- User -->
                <ul class="navbar-nav align-items-center d-none d-md-flex">
                    <li class="nav-item dropdown">
                        <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="media align-items-center">
                                <span class="avatar avatar-sm rounded-circle">
                                    <img alt="Foto Profil" src="<?= $foto_path ?>">
                                </span>
                                <div class="media-body ml-2 d-none d-lg-block">
                                    <span class="mb-0 text-sm font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                                    <small class="text-muted d-block">Kaprodi</small>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow m-0">Selamat datang!</h6>
                            </div>
                            <a href="<?= base_url('kaprodi/profil') ?>" class="dropdown-item">
                                <i class="ni ni-single-02"></i>
                                <span>Profil Saya</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= base_url('auth/logout') ?>" class="dropdown-item" onclick="return confirm('Yakin ingin logout?')">
                                <i class="ni ni-user-run"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Header -->
        <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
            <div class="container-fluid">
                <div class="header-body">
                    <!-- PERBAIKAN: Flash Messages dengan Method yang Benar -->
                    <?php
                    // FIXED: Gunakan flashdata() method yang benar
                    $flash_success = $this->session->flashdata('success');
                    $flash_error = $this->session->flashdata('error');
                    $flash_warning = $this->session->flashdata('warning');
                    $flash_info = $this->session->flashdata('info');
                    ?>
                    
                    <?php if($flash_success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
                        <span class="alert-text"><?= $flash_success ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($flash_error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
                        <span class="alert-text"><?= $flash_error ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($flash_warning): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-bell-55"></i></span>
                        <span class="alert-text"><?= $flash_warning ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($flash_info): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-bell-55"></i></span>
                        <span class="alert-text"><?= $flash_info ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Page content -->
        <div class="container-fluid mt--7">
            <?= isset($content) ? $content : '' ?>
            
            <!-- Footer -->
            <footer class="footer">
                <div class="row align-items-center justify-content-xl-between">
                    <div class="col-xl-6 m-auto text-center">
                        <div class="copyright">
                            <p>&copy; <?= date('Y') ?> <a href="#" class="font-weight-bold ml-1" target="_blank">STK Santo Yakobus</a> - Sistem Informasi Manajemen Tugas Akhir</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="<?= base_url() ?>cdn/vendor/jquery/jquery.min.js"></script>
    <script src="<?= base_url() ?>cdn/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>cdn/vendor/datatables/datatables.min.js"></script>
    <script src="<?= base_url() ?>cdn/js/argon.min.js?v=1.0.0"></script>
    
    <!-- Global Scripts -->
    <script>
        // Global base_url for JavaScript
        var base_url = '<?= base_url() ?>';
        
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Initialize DataTables if tables exist
            if (typeof $.fn.DataTable !== 'undefined') {
                $('.datatable').DataTable({
                    "pageLength": 25,
                    "responsive": true,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    }
                });
            }
        });
        
        // Helper function for AJAX calls
        function call(url, data = {}, method = 'GET') {
            return $.ajax({
                url: base_url + url,
                method: method,
                data: data,
                dataType: 'json'
            });
        }
        
        // Helper function for notifications
        function notif(message, type = 'success', reload = false) {
            var alertClass = 'alert-' + (type === 'error' ? 'danger' : type);
            var icon = type === 'success' ? 'ni-check-bold' : 
                      type === 'error' ? 'ni-fat-remove' : 'ni-bell-55';
            
            var alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <span class="alert-icon"><i class="ni ${icon}"></i></span>
                    <span class="alert-text">${message}</span>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            $('.header-body').prepend(alertHtml);
            
            if (reload) {
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        }
    </script>
    
    <!-- Custom Scripts -->
    <?= isset($script) ? $script : '' ?>
</body>
</html>