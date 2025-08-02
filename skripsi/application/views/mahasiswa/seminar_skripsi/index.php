<?php
/**
 * Dashboard Seminar Skripsi Mahasiswa (Phase 5)
 * File: application/views/mahasiswa/seminar_skripsi/index.php
 * 
 * Dashboard untuk mengelola pengajuan seminar skripsi mahasiswa
 * Menggunakan struktur template yang konsisten dengan seminar proposal
 * 
 * Flow: Usulan Proposal > Bimbingan > Seminar Proposal > Penelitian > Seminar Skripsi > Publikasi
 */
?>

<style>
    .card-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .card-stats:hover {
        transform: translateY(-5px);
    }
    .card-stats-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .card-stats-2:hover {
        transform: translateY(-5px);
    }
    .card-stats-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .card-stats-3:hover {
        transform: translateY(-5px);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s;
    }
    .btn-action {
        margin: 0 2px;
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
    .progress-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, #28a745 var(--progress), #e9ecef var(--progress));
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .progress-circle::before {
        content: '';
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: white;
        position: absolute;
    }
    .progress-text {
        position: relative;
        z-index: 1;
        font-weight: bold;
        font-size: 0.8rem;
    }
</style>

<!-- Content untuk template mahasiswa -->
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Seminar Skripsi
        </h1>
        <div class="d-none d-lg-inline-block">
            <span class="text-muted">Kelola pengajuan seminar skripsi dan pantau progress Anda</span>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <?= $this->session->flashdata('info') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats h-100">
                <div class="card-body text-center">
                    <div class="icon icon-shape bg-white text-primary rounded-circle shadow mb-3 mx-auto" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-file-upload fa-2x"></i>
                    </div>
                    <h3 class="font-weight-bold mb-0"><?= $progress_summary['total'] ?? 0 ?></h3>
                    <p class="mb-0 opacity-8">Total Pengajuan</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats-2 h-100">
                <div class="card-body text-center">
                    <div class="icon icon-shape bg-white text-primary rounded-circle shadow mb-3 mx-auto" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="font-weight-bold mb-0"><?= $progress_summary['submitted'] ?? 0 ?></h3>
                    <p class="mb-0 opacity-8">Sedang Diproses</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats-3 h-100">
                <div class="card-body text-center">
                    <div class="icon icon-shape bg-white text-primary rounded-circle shadow mb-3 mx-auto" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h3 class="font-weight-bold mb-0"><?= $progress_summary['scheduled'] ?? 0 ?></h3>
                    <p class="mb-0 opacity-8">Terjadwal</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <div class="icon icon-shape bg-white text-success rounded-circle shadow mb-3 mx-auto" style="width: 60px; height: 60px; line-height: 60px;">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="font-weight-bold mb-0"><?= $progress_summary['completed'] ?? 0 ?></h3>
                    <p class="mb-0 opacity-8">Selesai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        
        <!-- Proposals Eligible untuk Seminar Skripsi -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Proposal Siap Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($proposals_eligible)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Judul Proposal</th>
                                        <th width="100">Status</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($proposals_eligible as $proposal): ?>
                                        <tr>
                                            <td>
                                                <strong><?= character_limiter($proposal->judul, 50) ?></strong>
                                                <br><small class="text-muted">ID: <?= $proposal->id ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-success status-badge">
                                                    Ready
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                // Check apakah sudah ada pengajuan
                                                $has_submission = false;
                                                foreach ($existing_seminars as $seminar) {
                                                    if ($seminar->proposal_id == $proposal->id) {
                                                        $has_submission = true;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                
                                                <?php if (!$has_submission): ?>
                                                    <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $proposal->id) ?>" 
                                                       class="btn btn-primary btn-sm btn-action" 
                                                       title="Ajukan Seminar Skripsi">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge badge-info">Sudah Diajukan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p class="mb-2">Belum ada proposal yang siap untuk seminar skripsi</p>
                            <small>Pastikan Anda telah menyelesaikan tahap penelitian</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Existing Seminar Skripsi -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list mr-2"></i>
                        Riwayat Pengajuan Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($existing_seminars)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Judul</th>
                                        <th width="100">Status</th>
                                        <th width="80">Progress</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($existing_seminars as $seminar): ?>
                                        <tr>
                                            <td>
                                                <strong><?= character_limiter($seminar->judul, 40) ?></strong>
                                                <br><small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                $status_text = 'Draft';
                                                
                                                switch ($seminar->status) {
                                                    case 'submitted':
                                                    case 'review_pembimbing':
                                                        $status_class = 'warning';
                                                        $status_text = 'Review';
                                                        break;
                                                    case 'review_kaprodi':
                                                        $status_class = 'info';
                                                        $status_text = 'Validasi';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'primary';
                                                        $status_text = 'Disetujui';
                                                        break;
                                                    case 'scheduled':
                                                        $status_class = 'success';
                                                        $status_text = 'Terjadwal';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'success';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        $status_text = 'Ditolak';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?> status-badge">
                                                    <?= $status_text ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $progress = 0;
                                                switch ($seminar->status) {
                                                    case 'draft': $progress = 0; break;
                                                    case 'submitted':
                                                    case 'review_pembimbing': $progress = 20; break;
                                                    case 'review_kaprodi': $progress = 40; break;
                                                    case 'approved': $progress = 60; break;
                                                    case 'scheduled': $progress = 80; break;
                                                    case 'completed': $progress = 100; break;
                                                }
                                                ?>
                                                <div class="progress-circle" style="--progress: <?= $progress ?>%;">
                                                    <div class="progress-text"><?= $progress ?>%</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <a href="<?= base_url('mahasiswa/seminar_skripsi/detail/' . $seminar->id) ?>" 
                                                       class="btn btn-info btn-sm btn-action" 
                                                       title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                                                        <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                                                           class="btn btn-warning btn-sm btn-action" 
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($seminar->file_skripsi)): ?>
                                                        <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                                           class="btn btn-success btn-sm btn-action" 
                                                           title="Download File"
                                                           target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p class="mb-2">Belum ada pengajuan seminar skripsi</p>
                            <small>Mulai dengan mengajukan seminar skripsi dari proposal yang sudah siap</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-gradient-primary">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-route mr-2"></i>
                        Alur Workflow Seminar Skripsi (Phase 5)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold">Usulan Proposal</h6>
                            <small class="text-muted">Proposal disetujui</small>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold">Bimbingan</h6>
                            <small class="text-muted">Min 14x jurnal</small>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold">Seminar Proposal</h6>
                            <small class="text-muted">Sudah lulus</small>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold">Penelitian</h6>
                            <small class="text-muted">Izin disetujui</small>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold text-primary">Seminar Skripsi</h6>
                            <small class="text-muted">Tahap saat ini</small>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-book"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold">Publikasi</h6>
                            <small class="text-muted">Tahap selanjutnya</small>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                Syarat untuk Mengajukan Seminar Skripsi:
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Sudah lulus seminar proposal</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Minimal 14 jurnal bimbingan yang divalidasi</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Surat izin penelitian disetujui</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> File skripsi final (PDF/Word, max 2MB)</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success mr-2"></i> Workflow status = 'seminar_skripsi'</li>
                                        <li><i class="fas fa-check text-success mr-2"></i> Keterangan penelitian sudah lengkap</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->