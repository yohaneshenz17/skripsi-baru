<?php
/**
 * Dashboard Penelitian - Tahap 4 Workflow
 * View untuk mahasiswa mengelola permohonan izin penelitian
 * 
 * File: application/views/mahasiswa/penelitian/index.php
 */
?>

<style>
.card-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.card-stats-2 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(240, 147, 251, 0.3);
}

.card-stats-3 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
}

.workflow-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.workflow-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-weight: 600;
}

.status-submitted { background: #fff3cd; color: #856404; }
.status-review_pembimbing { background: #cce5ff; color: #004085; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-completed { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

.progress-bar-custom {
    height: 8px;
    border-radius: 10px;
    background: #e9ecef;
}

.progress-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?= $this->session->flashdata('warning') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-flask mr-2 text-primary"></i>
            Penelitian - Tahap 4
        </h1>
        <p class="text-muted mb-0">Kelola permohonan izin penelitian untuk pengumpulan data skripsi</p>
    </div>
    <div class="d-none d-lg-inline-block">
        <button class="btn btn-sm btn-outline-primary btn-refresh" data-toggle="tooltip" title="Refresh Data">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-stats h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Total Permohonan
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            <?= $statistics['total_permohonan'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-stats-2 h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Sedang Diproses
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            <?= $statistics['pending_permohonan'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card card-stats-3 h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">
                            Selesai
                        </div>
                        <div class="h5 mb-0 font-weight-bold">
                            <?= $statistics['completed_permohonan'] ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card workflow-card">
            <div class="card-header bg-gradient-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Ajukan Izin Penelitian Baru
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Pilih proposal yang sudah lulus seminar proposal untuk mengajukan izin penelitian
                </p>
                
                <?php if (!empty($proposal_list)): ?>
                    <div class="row">
                        <?php foreach ($proposal_list as $proposal): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-primary">
                                    <div class="card-body py-3">
                                        <h6 class="card-title mb-2">
                                            <strong><?= htmlspecialchars($proposal->judul) ?></strong>
                                        </h6>
                                        <p class="card-text small text-muted mb-2">
                                            Status: 
                                            <span class="badge badge-info">
                                                <?= ucfirst(str_replace('_', ' ', $proposal->workflow_status)) ?>
                                            </span>
                                        </p>
                                        <div class="mt-3">
                                            <a href="<?= base_url('mahasiswa/penelitian/ajukan/' . $proposal->id) ?>" 
                                               class="btn btn-gradient btn-sm">
                                                <i class="fas fa-plus mr-1"></i>
                                                Ajukan Izin Penelitian
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h5>Belum Ada Proposal</h5>
                        <p>Anda belum memiliki proposal. Silakan ajukan proposal terlebih dahulu.</p>
                        <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-gradient">
                            <i class="fas fa-plus mr-2"></i>
                            Ajukan Proposal
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Permohonan Izin Penelitian -->
<div class="row">
    <div class="col-12">
        <div class="card workflow-card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list mr-2"></i>
                        Riwayat Permohonan Izin Penelitian
                    </h6>
                    <?php if (!empty($permohonan_list)): ?>
                        <small class="text-muted">
                            Total: <?= count($permohonan_list) ?> permohonan
                        </small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($permohonan_list)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Judul Proposal</th>
                                    <th width="20%">Tempat Penelitian</th>
                                    <th width="15%">Tanggal Pengajuan</th>
                                    <th width="15%">Status</th>
                                    <th width="10%">Progress</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permohonan_list as $index => $permohonan): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark">
                                                <?= htmlspecialchars(substr($permohonan->judul_skripsi_terbaru, 0, 50)) ?>
                                                <?= strlen($permohonan->judul_skripsi_terbaru) > 50 ? '...' : '' ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($permohonan->program_studi) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-muted mr-1"></i>
                                            <?= htmlspecialchars($permohonan->tempat_penelitian) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar text-muted mr-1"></i>
                                            <?= date('d/m/Y', strtotime($permohonan->created_at)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($permohonan->created_at)) ?> WIT
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = 'status-' . $permohonan->status;
                                            $status_text = '';
                                            switch ($permohonan->status) {
                                                case 'submitted':
                                                    $status_text = 'Diajukan';
                                                    break;
                                                case 'review_pembimbing':
                                                    $status_text = 'Review Pembimbing';
                                                    break;
                                                case 'approved':
                                                    $status_text = 'Menunggu Staf';
                                                    break;
                                                case 'completed':
                                                    $status_text = 'Selesai';
                                                    break;
                                                case 'rejected':
                                                    $status_text = 'Ditolak';
                                                    break;
                                                default:
                                                    $status_text = ucfirst($permohonan->status);
                                            }
                                            ?>
                                            <span class="status-badge <?= $status_class ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $progress = 0;
                                            switch ($permohonan->status) {
                                                case 'submitted': $progress = 25; break;
                                                case 'review_pembimbing': $progress = 40; break;
                                                case 'approved': $progress = 60; break;
                                                case 'surat_ready': $progress = 80; break;
                                                case 'completed': $progress = 100; break;
                                                case 'rejected': $progress = 0; break;
                                                default: $progress = 10;
                                            }
                                            ?>
                                            <div class="progress-bar-custom">
                                                <div class="progress-fill bg-primary" style="width: <?= $progress ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $progress ?>%</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= base_url('mahasiswa/penelitian/detail/' . $permohonan->id) ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   data-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($permohonan->status == 'completed' && !empty($permohonan->file_surat_izin_staf)): ?>
                                                    <a href="<?= base_url('mahasiswa/penelitian/download_surat/' . $permohonan->id) ?>" 
                                                       class="btn btn-outline-success btn-sm" 
                                                       data-toggle="tooltip" title="Download Surat">
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
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h5>Belum Ada Permohonan</h5>
                        <p>Anda belum pernah mengajukan izin penelitian. Mulai dengan mengajukan permohonan pertama Anda.</p>
                        <?php if (!empty($proposal_list)): ?>
                            <a href="<?= base_url('mahasiswa/penelitian/ajukan/' . $proposal_list[0]->id) ?>" 
                               class="btn btn-gradient">
                                <i class="fas fa-plus mr-2"></i>
                                Ajukan Izin Penelitian
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Help Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-left-info">
            <div class="card-body">
                <h6 class="font-weight-bold text-info mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Tahap Penelitian
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Syarat Mengajukan Izin Penelitian:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success mr-2"></i>Seminar proposal sudah selesai</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Penilaian seminar sudah dipublikasi</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Minimal 9 jurnal bimbingan tervalidasi</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Alur Proses:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary mr-2"></i>Mahasiswa ajukan permohonan</li>
                            <li><i class="fas fa-arrow-right text-primary mr-2"></i>Dosen pembimbing review</li>
                            <li><i class="fas fa-arrow-right text-primary mr-2"></i>Staf proses surat izin</li>
                            <li><i class="fas fa-arrow-right text-primary mr-2"></i>Download surat izin</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>