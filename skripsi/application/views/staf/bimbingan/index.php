<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bimbingan - SIM Tugas Akhir STK St. Yakobus</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
        }
        
        .filter-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.1);
        }
        
        .btn-action {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0">
                <div class="sidebar p-3">
                    <!-- Logo/Header -->
                    <div class="text-center mb-4">
                        <h5 class="mb-1">SIM-TA</h5>
                        <small>STK St. Yakobus</small>
                        <hr class="border-light">
                    </div>
                    
                    <!-- User Info -->
                    <div class="text-center mb-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="fas fa-user-tie fa-2x me-2"></i>
                            <div>
                                <div class="fw-bold"><?= $this->session->userdata('nama') ?></div>
                                <small class="opacity-75">Staf Akademik</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Menu -->
                    <nav class="nav flex-column">
                        <a class="nav-link" href="<?= base_url('staf/dashboard') ?>">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="<?= base_url('staf/bimbingan') ?>">
                            <i class="fas fa-book-open me-2"></i> Bimbingan
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/seminar-proposal') ?>">
                            <i class="fas fa-presentation me-2"></i> Seminar Proposal
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/penelitian') %>">
                            <i class="fas fa-search me-2"></i> Penelitian
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/seminar-skripsi') ?>">
                            <i class="fas fa-graduation-cap me-2"></i> Seminar Skripsi
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/publikasi') ?>">
                            <i class="fas fa-globe me-2"></i> Publikasi
                        </a>
                        <hr class="border-light">
                        <a class="nav-link" href="<?= base_url('staf/dashboard/daftar_mahasiswa') ?>">
                            <i class="fas fa-users me-2"></i> Daftar Mahasiswa
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/dashboard/daftar_dosen') ?>">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Daftar Dosen
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/dashboard/profil') ?>">
                            <i class="fas fa-user-cog me-2"></i> Profil
                        </a>
                        <hr class="border-light">
                        <a class="nav-link" href="<?= base_url('auth/logout') ?>">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <!-- Header -->
                <div class="bg-white shadow-sm p-3 mb-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-0">
                                <i class="fas fa-book-open text-primary me-2"></i>
                                Manajemen Bimbingan
                            </h4>
                            <small class="text-muted">Export jurnal bimbingan dan laporan</small>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-primary"><?= date('d F Y') ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Alert Messages -->
                <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                                <h4 class="text-primary"><?= $stats['total_jurnal'] ?></h4>
                                <small class="text-muted">Total Jurnal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="text-success"><?= $stats['jurnal_valid'] ?></h4>
                                <small class="text-muted">Jurnal Valid</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="text-warning"><?= $stats['jurnal_pending'] ?></h4>
                                <small class="text-muted">Jurnal Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h4 class="text-info"><?= $stats['mahasiswa_aktif'] ?></h4>
                                <small class="text-muted">Mahasiswa Aktif</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter & Export Section -->
                <div class="card filter-card mb-4">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filter & Export Data
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Program Studi</label>
                                <select name="prodi_id" class="form-select">
                                    <option value="">Semua Prodi</option>
                                    <?php foreach($prodi_list as $prodi): ?>
                                    <option value="<?= $prodi->id ?>" <?= $filters['prodi_id'] == $prodi->id ? 'selected' : '' ?>>
                                        <?= $prodi->nama ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status Validasi</label>
                                <select name="status_validasi" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="0" <?= $filters['status_validasi'] === '0' ? 'selected' : '' ?>>Pending</option>
                                    <option value="1" <?= $filters['status_validasi'] === '1' ? 'selected' : '' ?>>Valid</option>
                                    <option value="2" <?= $filters['status_validasi'] === '2' ? 'selected' : '' ?>>Revisi</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari nama, NIM, judul..." value="<?= $filters['search'] ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <!-- Export Section -->
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-2">Export Laporan:</h6>
                                <form method="POST" action="<?= base_url('staf/bimbingan/export_filtered_pdf') ?>" class="row g-2">
                                    <div class="col-md-3">
                                        <input type="hidden" name="prodi_id" value="<?= $filters['prodi_id'] ?>">
                                        <input type="hidden" name="status_validasi" value="<?= $filters['status_validasi'] ?>">
                                        <input type="date" name="tanggal_mulai" class="form-control" placeholder="Tanggal Mulai">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" name="tanggal_selesai" class="form-control" placeholder="Tanggal Selesai">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Table -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Data Jurnal Bimbingan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="bimbinganTable" class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Mahasiswa</th>
                                        <th>Judul Proposal</th>
                                        <th>Pembimbing</th>
                                        <th>Pertemuan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($bimbingan)): ?>
                                        <?php foreach($bimbingan as $i => $item): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= $item->nama_mahasiswa ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= $item->nim ?> | <?= $item->nama_prodi ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary" title="<?= $item->judul_proposal ?>">
                                                    <?= substr($item->judul_proposal, 0, 60) ?>...
                                                </span>
                                            </td>
                                            <td>
                                                <?= $item->nama_pembimbing ?: '-' ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    Ke-<?= $item->pertemuan_ke ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($item->tanggal_bimbingan)) ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                $status_text = '';
                                                switch($item->status_validasi) {
                                                    case '0':
                                                        $status_class = 'bg-warning text-dark';
                                                        $status_text = 'Pending';
                                                        break;
                                                    case '1':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'Valid';
                                                        break;
                                                    case '2':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'Revisi';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge status-badge <?= $status_class ?>">
                                                    <?= $status_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('staf/bimbingan/detail/' . $item->mahasiswa_id) ?>" 
                                                       class="btn btn-sm btn-outline-primary btn-action" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('staf/bimbingan/export_jurnal_pdf/' . $item->mahasiswa_id) ?>" 
                                                       class="btn btn-sm btn-outline-danger btn-action" title="Export PDF" target="_blank">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <br>
                                                <span class="text-muted">Tidak ada data jurnal bimbingan</span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#bimbinganTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[5, 'desc']], // Sort by tanggal bimbingan
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                columnDefs: [
                    { orderable: false, targets: [7] } // Disable sorting on action column
                ]
            });
            
            // Auto refresh every 5 minutes
            setTimeout(function(){
                location.reload();
            }, 300000);
        });
    </script>
</body>
</html>