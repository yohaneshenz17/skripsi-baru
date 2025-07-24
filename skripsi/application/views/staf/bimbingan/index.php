<?php
// File: application/views/staf/bimbingan/index.php
// FIXED - Simple and robust view untuk halaman monitoring bimbingan mahasiswa
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= isset($title) ? $title : 'Monitoring Bimbingan' ?> - SIM TA STK</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            margin: 3px 0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
        }
        .table {
            font-size: 14px;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        .badge {
            font-size: 11px;
            padding: 5px 8px;
        }
        .progress {
            height: 6px;
            border-radius: 10px;
        }
        .btn-group-sm .btn {
            font-size: 12px;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed d-none d-md-block" style="width: 250px; z-index: 1000;">
        <div class="text-center py-4">
            <h4 class="text-white mb-0">
                <i class="fas fa-graduation-cap"></i> SIM-TA
            </h4>
            <small class="text-white-50">STK Santo Yakobus</small>
        </div>
        <ul class="nav flex-column px-3">
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('staf/dashboard') ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="<?= site_url('staf/bimbingan') ?>">
                    <i class="fas fa-book-open"></i> Bimbingan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('staf/seminar_proposal') ?>">
                    <i class="fas fa-presentation"></i> Seminar Proposal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('staf/penelitian') ?>">
                    <i class="fas fa-microscope"></i> Penelitian
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('staf/seminar_skripsi') ?>">
                    <i class="fas fa-graduation-cap"></i> Seminar Skripsi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('staf/publikasi') ?>">
                    <i class="fas fa-globe"></i> Publikasi
                </a>
            </li>
            <li class="nav-item mt-4 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <a class="nav-link" href="<?= site_url('auth/logout') ?>">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-book-open text-primary"></i>
                    <?= isset($title) ? $title : 'Monitoring Bimbingan' ?>
                </h2>
                <p class="text-muted mb-0">Kelola dan pantau jurnal bimbingan mahasiswa tugas akhir</p>
            </div>
            <div class="text-right">
                <span class="badge badge-info badge-lg">
                    <i class="fas fa-user-tie"></i> 
                    <?= $this->session->userdata('nama') ?: 'Staf Akademik' ?>
                </span>
                <br>
                <small class="text-muted">
                    <i class="fas fa-clock"></i> 
                    <?= date('d F Y, H:i') ?> WIB
                </small>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> 
                <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-white-50">Total Mahasiswa</h6>
                            <h3 class="mb-0 font-weight-bold">
                                <?= isset($statistics['total_mahasiswa']) ? $statistics['total_mahasiswa'] : 0 ?>
                            </h3>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-white-50">Tahap Bimbingan</h6>
                            <h3 class="mb-0 font-weight-bold">
                                <?= isset($statistics['bimbingan']) ? $statistics['bimbingan'] : 0 ?>
                            </h3>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-book-open fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-white-50">Seminar Proposal</h6>
                            <h3 class="mb-0 font-weight-bold">
                                <?= isset($statistics['seminar_proposal']) ? $statistics['seminar_proposal'] : 0 ?>
                            </h3>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-presentation fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-white-50">Tahap Penelitian</h6>
                            <h3 class="mb-0 font-weight-bold">
                                <?= isset($statistics['penelitian']) ? $statistics['penelitian'] : 0 ?>
                            </h3>
                        </div>
                        <div class="ml-3">
                            <i class="fas fa-microscope fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter text-primary"></i>
                    Filter & Pencarian Data
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('staf/bimbingan') ?>" class="row" id="filterForm">
                    <div class="col-md-3 mb-3">
                        <label for="prodi_id" class="form-label">Program Studi</label>
                        <select name="prodi_id" id="prodi_id" class="form-control">
                            <option value="">-- Semua Prodi --</option>
                            <?php if (isset($prodi_list) && is_array($prodi_list)): ?>
                                <?php foreach ($prodi_list as $prodi): ?>
                                    <option value="<?= $prodi->id ?>" <?= ($this->input->get('prodi_id') == $prodi->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prodi->nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="dosen_id" class="form-label">Dosen Pembimbing</label>
                        <select name="dosen_id" id="dosen_id" class="form-control">
                            <option value="">-- Semua Dosen --</option>
                            <?php if (isset($dosen_list) && is_array($dosen_list)): ?>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <option value="<?= $dosen->id ?>" <?= ($this->input->get('dosen_id') == $dosen->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dosen->nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status Workflow</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <?php if (isset($status_list) && is_array($status_list)): ?>
                                <?php foreach ($status_list as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= ($this->input->get('status') == $key) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Pencarian</label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Nama/NIM/Judul..." 
                                   value="<?= htmlspecialchars($this->input->get('search') ?: '') ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mt-2">
                    <a href="<?= site_url('staf/bimbingan') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i> Reset Filter
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportAllData()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-table text-primary"></i>
                            Daftar Mahasiswa Bimbingan
                        </h5>
                        <small class="text-muted">
                            Total: <?= isset($mahasiswa_bimbingan) ? count($mahasiswa_bimbingan) : 0 ?> mahasiswa
                        </small>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="dataTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">NIM</th>
                                <th width="15%">Mahasiswa</th>
                                <th width="12%">Prodi</th>
                                <th width="25%">Judul Proposal</th>
                                <th width="15%">Pembimbing</th>
                                <th width="8%">Status</th>
                                <th width="10%">Progress</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($mahasiswa_bimbingan) && count($mahasiswa_bimbingan) > 0): ?>
                                <?php foreach ($mahasiswa_bimbingan as $index => $mhs): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?= htmlspecialchars($mhs->nim) ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($mhs->nama_mahasiswa) ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope"></i> 
                                                    <?= htmlspecialchars($mhs->email_mahasiswa) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($mhs->nama_prodi) ?></small>
                                        </td>
                                        <td>
                                            <span title="<?= htmlspecialchars($mhs->judul) ?>" data-toggle="tooltip">
                                                <?= strlen($mhs->judul) > 50 ? substr(htmlspecialchars($mhs->judul), 0, 50) . '...' : htmlspecialchars($mhs->judul) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($mhs->nama_pembimbing)): ?>
                                                <strong class="text-success"><?= htmlspecialchars($mhs->nama_pembimbing) ?></strong>
                                            <?php else: ?>
                                                <small class="text-muted">Belum ditetapkan</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'bimbingan' => 'warning',
                                                'seminar_proposal' => 'info',
                                                'penelitian' => 'primary',
                                                'seminar_skripsi' => 'success',
                                                'publikasi' => 'dark'
                                            ];
                                            $status_labels = [
                                                'bimbingan' => 'Bimbingan',
                                                'seminar_proposal' => 'Seminar Proposal',
                                                'penelitian' => 'Penelitian',
                                                'seminar_skripsi' => 'Seminar Skripsi',
                                                'publikasi' => 'Publikasi'
                                            ];
                                            $status_class = isset($status_classes[$mhs->workflow_status]) ? $status_classes[$mhs->workflow_status] : 'secondary';
                                            $status_label = isset($status_labels[$mhs->workflow_status]) ? $status_labels[$mhs->workflow_status] : 'Unknown';
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $status_label ?></span>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <small class="d-block">
                                                    <span class="text-success">
                                                        <i class="fas fa-check"></i> <?= $mhs->jurnal_tervalidasi ?>
                                                    </span>
                                                    <span class="text-warning ml-1">
                                                        <i class="fas fa-clock"></i> <?= $mhs->jurnal_pending ?>
                                                    </span>
                                                </small>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <?php 
                                                    $total = max($mhs->total_jurnal, 1);
                                                    $pct_valid = ($mhs->jurnal_tervalidasi / $total) * 100;
                                                    $pct_pending = ($mhs->jurnal_pending / $total) * 100;
                                                    ?>
                                                    <div class="progress-bar bg-success" style="width: <?= $pct_valid ?>%"></div>
                                                    <div class="progress-bar bg-warning" style="width: <?= $pct_pending ?>%"></div>
                                                </div>
                                                <small class="text-muted">Total: <?= $mhs->total_jurnal ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('staf/bimbingan/detail_mahasiswa/' . $mhs->proposal_id) ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Detail Progress" 
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= site_url('staf/bimbingan/export_jurnal/' . $mhs->proposal_id) ?>" 
                                                   class="btn btn-outline-success" 
                                                   title="Export Jurnal PDF" 
                                                   data-toggle="tooltip"
                                                   target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle fa-3x mb-3 text-primary"></i><br>
                                            <h5>Belum ada mahasiswa dalam tahap bimbingan</h5>
                                            <p class="mb-0">Mahasiswa akan muncul di sini setelah pembimbing menyetujui penunjukan dan masuk tahap bimbingan.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted mt-5 pt-4 border-top">
            <p class="mb-0">
                <small>
                    &copy; 2025 STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                    <br>
                    <i class="fas fa-code"></i> Dikembangkan dengan <i class="fas fa-heart text-danger"></i> oleh Tim IT STK
                </small>
            </p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Auto-submit form on filter change
            $('#prodi_id, #dosen_id, #status').change(function() {
                $('#filterForm').submit();
            });
            
            // Loading state untuk tombol
            $('.btn').click(function() {
                var btn = $(this);
                if (!btn.hasClass('no-loading')) {
                    btn.prop('disabled', true);
                    setTimeout(function() {
                        btn.prop('disabled', false);
                    }, 2000);
                }
            });
            
            // Search on enter
            $('#search').keypress(function(e) {
                if (e.which == 13) {
                    $('#filterForm').submit();
                }
            });
        });
        
        function exportAllData() {
            if (confirm('Apakah Anda yakin ingin mengexport semua data bimbingan?')) {
                window.open('<?= site_url("staf/bimbingan/export_all") ?>', '_blank');
            }
        }
        
        function refreshData() {
            location.reload();
        }
        
        // Show loading when navigating
        $(document).on('click', 'a[href]:not([href="#"]):not([target="_blank"])', function() {
            $('body').append('<div id="loading" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        });
    </script>
</body>
</html>