<?php
/**
 * Dashboard Seminar Proposal Kaprodi
 * File: application/views/kaprodi/seminar_proposal/index.php
 * 
 * Dashboard untuk mengelola seminar proposal dari perspektif Kaprodi
 * Menampilkan statistik, list pengajuan perlu review, dan recent activities
 */

// Get current URL for active menu
$current_url = uri_string();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?> - SIM-TA STK Santo Yakobus</title>
    
    <!-- CSS dari template kaprodi existing -->
    <link rel="icon" href="<?= base_url('assets/img/brand/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/nucleo/css/nucleo.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?= base_url('assets/css/argon.css?v=1.2.0') ?>" type="text/css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #8898aa;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>

<body>
    <!-- Sidebar (gunakan existing template kaprodi) -->
    <?php $this->load->view('template/kaprodi_sidebar'); ?>
    
    <!-- Main content -->
    <div class="main-content" id="panel">
        <!-- Topnav (gunakan existing template kaprodi) -->
        <?php $this->load->view('template/kaprodi_topnav'); ?>
        
        <!-- Header -->
        <div class="header bg-primary pb-6">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center py-4">
                        <div class="col-lg-6 col-7">
                            <h6 class="h2 text-white d-inline-block mb-0"><?= $title ?></h6>
                            <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                    <li class="breadcrumb-item"><a href="<?= base_url('kaprodi') ?>"><i class="fas fa-home"></i></a></li>
                                    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-lg-6 col-5 text-right">
                            <button class="btn btn-sm btn-neutral" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats stat-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                                            <span class="h2 font-weight-bold mb-0"><?= $statistics['pending_review'] ?? 0 ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-sm">
                                        <span class="text-warning mr-2"><i class="fas fa-arrow-up"></i></span>
                                        <span class="text-nowrap">Menunggu validasi</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats stat-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Disetujui</h5>
                                            <span class="h2 font-weight-bold mb-0"><?= $statistics['approved_month'] ?? 0 ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-sm">
                                        <span class="text-success mr-2"><i class="fas fa-arrow-up"></i></span>
                                        <span class="text-nowrap">Bulan ini</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats stat-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Ditolak</h5>
                                            <span class="h2 font-weight-bold mb-0"><?= $statistics['rejected_month'] ?? 0 ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                                <i class="fas fa-times-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-sm">
                                        <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i></span>
                                        <span class="text-nowrap">Bulan ini</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-stats stat-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h5 class="card-title text-uppercase text-muted mb-0">Terjadwal</h5>
                                            <span class="h2 font-weight-bold mb-0"><?= $statistics['scheduled_month'] ?? 0 ?></span>
                                        </div>
                                        <div class="col-auto">
                                            <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-3 mb-0 text-sm">
                                        <span class="text-info mr-2"><i class="fas fa-arrow-up"></i></span>
                                        <span class="text-nowrap">Bulan ini</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page content -->
        <div class="container-fluid mt--6">
            <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-xl-8">
                    <!-- Pengajuan Perlu Review -->
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">Pengajuan Perlu Review</h3>
                                </div>
                                <div class="col text-right">
                                    <span class="badge badge-warning badge-pill">
                                        <?= count($pending_reviews) ?> pengajuan
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if(!empty($pending_reviews)): ?>
                            <div class="table-responsive">
                                <table class="table table-flush" id="pengajuan-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Judul</th>
                                            <th>Pembimbing</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($pending_reviews as $review): ?>
                                        <tr>
                                            <td>
                                                <div class="media align-items-center">
                                                    <div class="media-body">
                                                        <span class="name mb-0 text-sm font-weight-bold">
                                                            <?= htmlspecialchars($review->nama_mahasiswa) ?>
                                                        </span>
                                                        <br><small class="text-muted"><?= htmlspecialchars($review->nim) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm" data-toggle="tooltip" title="<?= htmlspecialchars($review->judul) ?>">
                                                    <?= substr(htmlspecialchars($review->judul), 0, 50) ?>...
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-sm"><?= htmlspecialchars($review->nama_pembimbing ?? 'Belum ada') ?></span>
                                            </td>
                                            <td>
                                                <span class="text-sm">
                                                    <?= date('d/m/Y H:i', strtotime($review->tanggal_pengajuan)) ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <a href="<?= base_url('kaprodi/seminar_proposal/detail/' . $review->id) ?>" 
                                                   class="btn btn-sm btn-primary" data-toggle="tooltip" title="Review Pengajuan">
                                                    <i class="fas fa-eye"></i> Review
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>Tidak Ada Pengajuan</h4>
                                <p>Saat ini tidak ada pengajuan seminar proposal yang perlu direview.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <!-- Recent Approved -->
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">Baru Disetujui</h3>
                                </div>
                                <div class="col text-right">
                                    <a href="#" class="btn btn-sm btn-primary">Lihat Semua</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if(!empty($recent_approved)): ?>
                            <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                                <?php foreach($recent_approved as $approved): ?>
                                <div class="timeline-block">
                                    <span class="timeline-step badge-success">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <small class="text-muted font-weight-bold">
                                            <?= date('d/m/Y H:i', strtotime($approved->tanggal_review_kaprodi)) ?>
                                        </small>
                                        <h5 class="mt-3 mb-0"><?= htmlspecialchars($approved->nama_mahasiswa) ?></h5>
                                        <p class="text-sm mt-1 mb-0">
                                            <?= substr(htmlspecialchars($approved->judul), 0, 60) ?>...
                                        </p>
                                        <div class="mt-2">
                                            <span class="badge badge-success">Disetujui</span>
                                            <?php if(!empty($approved->tanggal_seminar)): ?>
                                            <small class="text-muted ml-2">
                                                <i class="fas fa-calendar"></i> 
                                                <?= date('d/m/Y', strtotime($approved->tanggal_seminar)) ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-check"></i>
                                <h5>Belum Ada</h5>
                                <p class="text-sm">Belum ada seminar proposal yang disetujui baru-baru ini.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="<?= base_url('kaprodi/proposal') ?>" class="list-group-item list-group-item-action">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-file-alt text-primary"></i>
                                        </div>
                                        <div class="col ml--2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-0 text-sm">Usulan Proposal</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="<?= base_url('kaprodi/mahasiswa') ?>" class="list-group-item list-group-item-action">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-users text-info"></i>
                                        </div>
                                        <div class="col ml--2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-0 text-sm">Daftar Mahasiswa</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="<?= base_url('kaprodi/dosen') ?>" class="list-group-item list-group-item-action">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-chalkboard-teacher text-success"></i>
                                        </div>
                                        <div class="col ml--2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-0 text-sm">Daftar Dosen</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                
                                <a href="<?= base_url('kaprodi/laporan') ?>" class="list-group-item list-group-item-action">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-chart-bar text-warning"></i>
                                        </div>
                                        <div class="col ml--2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4 class="mb-0 text-sm">Laporan</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php $this->load->view('template/kaprodi_footer'); ?>
        </div>
    </div>
    
    <!-- Core JS -->
    <script src="<?= base_url('assets/vendor/jquery/dist/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/js-cookie/js.cookie.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js') ?>"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <!-- Argon JS -->
    <script src="<?= base_url('assets/js/argon.js?v=1.2.0') ?>"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#pengajuan-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
                },
                "pageLength": 10,
                "order": [[3, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [4] }
                ]
            });
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Auto refresh every 5 minutes
            setInterval(function() {
                if (confirm('Refresh data terbaru?')) {
                    location.reload();
                }
            }, 300000);
        });
    </script>
</body>
</html>