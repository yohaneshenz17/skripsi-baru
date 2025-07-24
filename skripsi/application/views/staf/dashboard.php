<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staf - SIM Tugas Akhir STK St. Yakobus</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 15px;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .shortcut-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .shortcut-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .pengumuman-card {
            border-left: 4px solid #667eea;
            border-radius: 10px;
        }
        
        .workflow-progress {
            height: 300px;
        }
        
        .badge-custom {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
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
                        <a class="nav-link active" href="<?= base_url('staf/dashboard') ?>">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/bimbingan') ?>">
                            <i class="fas fa-book-open me-2"></i> Bimbingan
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/seminar-proposal') ?>">
                            <i class="fas fa-presentation me-2"></i> Seminar Proposal
                        </a>
                        <a class="nav-link" href="<?= base_url('staf/penelitian') ?>">
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
                            <h4 class="mb-0">Dashboard Staf Akademik</h4>
                            <small class="text-muted">Sistem Informasi Manajemen Tugas Akhir</small>
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
                
                <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card stats-card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h2 class="mb-1"><?= $total_mahasiswa['total'] ?></h2>
                                <p class="mb-1">Total Mahasiswa</p>
                                <small><?= $total_mahasiswa['mengajukan_proposal'] ?> mengajukan proposal</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card stats-card h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="card-body text-center">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-3"></i>
                                <h2 class="mb-1"><?= $total_dosen['total'] ?></h2>
                                <p class="mb-1">Total Dosen</p>
                                <small><?= $total_dosen['membimbing'] ?> sedang membimbing</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Shortcuts -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            Quick Shortcuts
                        </h5>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <a href="<?= base_url('staf/bimbingan') ?>" class="text-decoration-none">
                            <div class="card shortcut-card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-book-open fa-2x text-primary mb-2"></i>
                                    <h6>Bimbingan</h6>
                                    <span class="badge bg-primary badge-custom"><?= $shortcuts['bimbingan'] ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <a href="<?= base_url('staf/seminar-proposal') ?>" class="text-decoration-none">
                            <div class="card shortcut-card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-presentation fa-2x text-success mb-2"></i>
                                    <h6>Seminar Proposal</h6>
                                    <span class="badge bg-success badge-custom"><?= $shortcuts['seminar_proposal'] ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <a href="<?= base_url('staf/penelitian') ?>" class="text-decoration-none">
                            <div class="card shortcut-card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-search fa-2x text-info mb-2"></i>
                                    <h6>Penelitian</h6>
                                    <span class="badge bg-info badge-custom"><?= $shortcuts['penelitian'] ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <a href="<?= base_url('staf/seminar-skripsi') ?>" class="text-decoration-none">
                            <div class="card shortcut-card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-graduation-cap fa-2x text-warning mb-2"></i>
                                    <h6>Seminar Skripsi</h6>
                                    <span class="badge bg-warning badge-custom"><?= $shortcuts['seminar_skripsi'] ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <a href="<?= base_url('staf/publikasi') ?>" class="text-decoration-none">
                            <div class="card shortcut-card text-center h-100">
                                <div class="card-body">
                                    <i class="fas fa-globe fa-2x text-danger mb-2"></i>
                                    <h6>Publikasi</h6>
                                    <span class="badge bg-danger badge-custom"><?= $shortcuts['publikasi'] ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Main Content Row -->
                <div class="row">
                    <!-- Pengumuman Tahapan -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Pengumuman Tahapan
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <?php if(!empty($pengumuman)): ?>
                                    <?php foreach($pengumuman as $item): ?>
                                    <div class="pengumuman-card p-3 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= $item->tahapan ?></h6>
                                                <small class="text-muted"><?= $item->keterangan ?></small>
                                            </div>
                                            <span class="badge bg-warning text-dark">
                                                <?= date('d/m/Y', strtotime($item->tanggal_deadline)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p>Tidak ada pengumuman</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Infografis Workflow -->
                    <div class="col-md-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Infografis Data Tahapan Workflow
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="workflow-progress">
                                    <canvas id="workflowChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Workflow Statistics Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Detail Statistik Workflow
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Tahapan Workflow</th>
                                                <th>Jumlah Mahasiswa</th>
                                                <th>Persentase</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_all = array_sum(array_column($workflow_stats, 'total'));
                                            foreach($workflow_stats as $i => $stat): 
                                                $percentage = $total_all > 0 ? round(($stat['total'] / $total_all) * 100, 1) : 0;
                                                $status_class = '';
                                                switch($stat['stage']) {
                                                    case 'proposal': $status_class = 'bg-secondary'; break;
                                                    case 'bimbingan': $status_class = 'bg-primary'; break;
                                                    case 'seminar_proposal': $status_class = 'bg-info'; break;
                                                    case 'penelitian': $status_class = 'bg-warning'; break;
                                                    case 'seminar_skripsi': $status_class = 'bg-danger'; break;
                                                    case 'publikasi': $status_class = 'bg-dark'; break;
                                                    case 'selesai': $status_class = 'bg-success'; break;
                                                }
                                            ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <strong><?= $stat['label'] ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $status_class ?> fs-6">
                                                        <?= $stat['total'] ?> mahasiswa
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar <?= str_replace('bg-', 'bg-', $status_class) ?>" 
                                                             style="width: <?= $percentage ?>%">
                                                            <?= $percentage ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($stat['total'] > 0): ?>
                                                        <i class="fas fa-check-circle text-success"></i> Aktif
                                                    <?php else: ?>
                                                        <i class="fas fa-minus-circle text-muted"></i> Kosong
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-dark">
                                            <tr>
                                                <th colspan="2">Total Keseluruhan</th>
                                                <th>
                                                    <span class="badge bg-light text-dark fs-6">
                                                        <?= $total_all ?> mahasiswa
                                                    </span>
                                                </th>
                                                <th>100%</th>
                                                <th>
                                                    <i class="fas fa-chart-bar text-light"></i> Semua Tahapan
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js Implementation -->
    <script>
        // Workflow Chart
        const workflowData = <?= json_encode($workflow_stats) ?>;
        
        const ctx = document.getElementById('workflowChart').getContext('2d');
        const workflowChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: workflowData.map(item => item.label),
                datasets: [{
                    data: workflowData.map(item => item.total),
                    backgroundColor: [
                        '#6c757d', // proposal - secondary
                        '#0d6efd', // bimbingan - primary  
                        '#0dcaf0', // seminar_proposal - info
                        '#ffc107', // penelitian - warning
                        '#dc3545', // seminar_skripsi - danger
                        '#212529', // publikasi - dark
                        '#198754'  // selesai - success
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' mahasiswa (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Auto refresh setiap 5 menit
        setTimeout(function(){
            location.reload();
        }, 300000);
    </script>
</body>
</html>