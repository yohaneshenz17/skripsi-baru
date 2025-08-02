<!-- 
File: application/views/mahasiswa/seminar_skripsi/index.php
FIXED VERSION - 3 Syarat Sederhana + Tombol Action Fix
-->

<!-- Page Header -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="m-b-10">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Seminar Skripsi
                    </h2>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active">Seminar Skripsi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-2"></i>
    <?= $this->session->flashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <?= $this->session->flashdata('error') ?>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="row">
    
    <!-- Main Action Card -->
    <div class="col-lg-8 col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Pengajuan Seminar Skripsi
                </h5>
            </div>
            <div class="card-body">
                
                <?php if (isset($has_eligible_proposals) && $has_eligible_proposals): ?>
                    <!-- ADA PROPOSAL ELIGIBLE -->
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-success text-white mb-3 mx-auto" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <h4 class="text-success mb-2">Siap Mengajukan Seminar Skripsi!</h4>
                        <p class="text-muted mb-4">
                            Anda memiliki proposal yang memenuhi syarat untuk seminar skripsi.<br>
                            Pastikan dokumen skripsi Anda sudah lengkap sebelum mengajukan.
                        </p>
                        
                        <!-- TOMBOL ACTION UTAMA -->
                        <a href="<?= isset($action_url) ? $action_url : '#' ?>" 
                           class="btn <?= isset($action_class) ? $action_class : 'btn-success' ?> btn-lg px-5 py-3">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <?= isset($action_text) ? $action_text : 'Ajukan Seminar Skripsi' ?>
                        </a>
                        
                        <?php if (isset($action_proposal_id)): ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Proposal ID: <?= $action_proposal_id ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Progress Info -->
                    <div class="alert alert-info border-left-info">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-info-circle fa-2x text-info"></i>
                            </div>
                            <div class="col">
                                <h6 class="mb-1">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    Progress Phase 5
                                </h6>
                                <p class="mb-0 text-sm">
                                    Setelah mengajukan seminar skripsi, Anda akan memasuki tahap akhir sebelum publikasi.
                                    Pastikan dokumen skripsi sudah final dan lengkap.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- BELUM MEMENUHI SYARAT -->
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-warning text-white mb-3 mx-auto" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <h4 class="text-warning mb-2">Belum Memenuhi Syarat</h4>
                        <p class="text-muted mb-4">
                            Silakan lengkapi persyaratan di samping terlebih dahulu.<br>
                            Semua syarat harus terpenuhi sebelum dapat mengajukan seminar skripsi.
                        </p>
                        
                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                    
                    <!-- Info Steps -->
                    <div class="alert alert-light border">
                        <h6><i class="fas fa-route mr-2"></i>Langkah Selanjutnya:</h6>
                        <ol class="mb-0 pl-3">
                            <li>Pastikan minimal 14 jurnal bimbingan sudah tervalidasi dosen</li>
                            <li>Selesaikan seminar proposal dan dapatkan nilai</li>
                            <li>Ajukan surat izin penelitian melalui menu Penelitian</li>
                            <li>Kembali ke halaman ini setelah semua syarat terpenuhi</li>
                        </ol>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Sidebar - Requirements & Status -->
    <div class="col-lg-4 col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-list-check mr-2"></i>
                    Persyaratan Seminar Skripsi
                </h6>
            </div>
            <div class="card-body">
                
                <?php if (isset($proposals_eligible) && !empty($proposals_eligible)): ?>
                    <!-- SHOW REQUIREMENTS STATUS DARI PROPOSAL ELIGIBLE -->
                    <?php 
                    $first_proposal = $proposals_eligible[0];
                    $requirements = $first_proposal->eligibility_details['requirements'] ?? [];
                    ?>
                    
                    <div class="requirements-list">
                        
                        <!-- Syarat 1: Jurnal Bimbingan -->
                        <?php if (isset($requirements['jurnal'])): ?>
                            <div class="requirement-item mb-3 p-3 border rounded <?= $requirements['jurnal']['met'] ? 'border-success bg-light-success' : 'border-warning bg-light-warning' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-book mr-2 <?= $requirements['jurnal']['met'] ? 'text-success' : 'text-warning' ?>"></i>
                                            <strong class="requirement-title">Jurnal Bimbingan</strong>
                                        </div>
                                        <p class="mb-1 text-sm text-muted">
                                            Minimal 14 jurnal bimbingan tervalidasi dosen pembimbing
                                        </p>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <?php 
                                            $jurnal_progress = min(100, ($requirements['jurnal']['current'] / $requirements['jurnal']['required']) * 100);
                                            ?>
                                            <div class="progress-bar <?= $requirements['jurnal']['met'] ? 'bg-success' : 'bg-warning' ?>" 
                                                 style="width: <?= $jurnal_progress ?>%;"></div>
                                        </div>
                                        <small class="text-muted">
                                            <?= $requirements['jurnal']['current'] ?> dari <?= $requirements['jurnal']['required'] ?> jurnal tervalidasi
                                        </small>
                                    </div>
                                    <div class="status-icon ml-2">
                                        <?php if ($requirements['jurnal']['met']): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock text-warning fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Syarat 2: Seminar Proposal -->
                        <?php if (isset($requirements['seminar_proposal'])): ?>
                            <div class="requirement-item mb-3 p-3 border rounded <?= $requirements['seminar_proposal']['met'] ? 'border-success bg-light-success' : 'border-warning bg-light-warning' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-presentation mr-2 <?= $requirements['seminar_proposal']['met'] ? 'text-success' : 'text-warning' ?>"></i>
                                            <strong class="requirement-title">Seminar Proposal</strong>
                                        </div>
                                        <p class="mb-1 text-sm text-muted">
                                            Telah lulus seminar proposal dengan nilai yang dipublikasikan
                                        </p>
                                        <small class="text-muted">
                                            Status: <?= $requirements['seminar_proposal']['met'] ? 'Lulus' : 'Belum lulus' ?>
                                        </small>
                                    </div>
                                    <div class="status-icon ml-2">
                                        <?php if ($requirements['seminar_proposal']['met']): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock text-warning fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Syarat 3: Surat Izin Penelitian -->
                        <?php if (isset($requirements['penelitian'])): ?>
                            <div class="requirement-item mb-3 p-3 border rounded <?= $requirements['penelitian']['met'] ? 'border-success bg-light-success' : 'border-warning bg-light-warning' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-file-contract mr-2 <?= $requirements['penelitian']['met'] ? 'text-success' : 'text-warning' ?>"></i>
                                            <strong class="requirement-title">Surat Izin Penelitian</strong>
                                        </div>
                                        <p class="mb-1 text-sm text-muted">
                                            Telah mengajukan surat izin penelitian (Phase 4)
                                        </p>
                                        <small class="text-muted">
                                            Status: <?= $requirements['penelitian']['met'] ? 'Sudah diajukan' : 'Belum diajukan' ?>
                                        </small>
                                    </div>
                                    <div class="status-icon ml-2">
                                        <?php if ($requirements['penelitian']['met']): ?>
                                            <i class="fas fa-check-circle text-success fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock text-warning fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Summary Status -->
                    <div class="mt-4 p-3 bg-primary text-white rounded">
                        <div class="text-center">
                            <i class="fas fa-info-circle mb-2"></i>
                            <p class="mb-0 font-weight-bold">
                                <?= $first_proposal->eligibility_details['summary'] ?? 'Status tidak diketahui' ?>
                            </p>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- SHOW STATIC REQUIREMENTS (BELUM ADA PROPOSAL ELIGIBLE) -->
                    <div class="requirements-list">
                        
                        <div class="requirement-item mb-3 p-3 border rounded border-secondary">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-book mr-2 text-muted"></i>
                                <strong>14 Jurnal Bimbingan</strong>
                            </div>
                            <p class="mb-0 text-sm text-muted">
                                Minimal 14 jurnal bimbingan yang sudah divalidasi oleh dosen pembimbing
                            </p>
                        </div>
                        
                        <div class="requirement-item mb-3 p-3 border rounded border-secondary">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-presentation mr-2 text-muted"></i>
                                <strong>Seminar Proposal Lulus</strong>
                            </div>
                            <p class="mb-0 text-sm text-muted">
                                Telah menyelesaikan seminar proposal dengan nilai yang dipublikasikan
                            </p>
                        </div>
                        
                        <div class="requirement-item mb-3 p-3 border rounded border-secondary">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-file-contract mr-2 text-muted"></i>
                                <strong>Surat Izin Penelitian</strong>
                            </div>
                            <p class="mb-0 text-sm text-muted">
                                Telah mengajukan surat izin penelitian melalui menu Penelitian
                            </p>
                        </div>
                        
                    </div>
                    
                    <!-- Help Links -->
                    <div class="mt-4">
                        <h6 class="font-weight-bold">Bantuan:</h6>
                        <div class="list-group list-group-flush">
                            <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="list-group-item list-group-item-action px-0">
                                <i class="fas fa-book mr-2 text-primary"></i>
                                <small>Kelola Jurnal Bimbingan</small>
                            </a>
                            <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" class="list-group-item list-group-item-action px-0">
                                <i class="fas fa-presentation mr-2 text-info"></i>
                                <small>Status Seminar Proposal</small>
                            </a>
                            <a href="<?= base_url('mahasiswa/penelitian') ?>" class="list-group-item list-group-item-action px-0">
                                <i class="fas fa-file-contract mr-2 text-warning"></i>
                                <small>Ajukan Surat Izin Penelitian</small>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Progress Summary Card -->
        <?php if (isset($progress_summary)): ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Progress Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-primary mb-1"><?= $progress_summary['eligible_proposals'] ?></h4>
                            <small class="text-muted">Proposal Siap</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <h4 class="text-success mb-1"><?= $progress_summary['submitted_seminars'] ?></h4>
                            <small class="text-muted">Sudah Diajukan</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-map-marked-alt mr-1"></i>
                        Phase: <?= $progress_summary['current_phase'] ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
</div>

<!-- Existing Seminars Section -->
<?php if (isset($existing_seminars) && !empty($existing_seminars)): ?>
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-history mr-2"></i>
                    Riwayat Pengajuan Seminar Skripsi
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="50">No</th>
                                <th>Proposal</th>
                                <th width="150">Status</th>
                                <th width="120">Tanggal</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($existing_seminars as $index => $seminar): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= character_limiter($seminar->judul ?? 'Tidak ada judul', 60) ?></strong>
                                    <br><small class="text-muted">ID: <?= $seminar->proposal_id ?></small>
                                </td>
                                <td>
                                    <?php
                                    $status_classes = [
                                        'draft' => 'secondary',
                                        'submitted' => 'primary', 
                                        'review_pembimbing' => 'info',
                                        'review_kaprodi' => 'warning',
                                        'approved' => 'success',
                                        'scheduled' => 'success',
                                        'completed' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $status_texts = [
                                        'draft' => 'Draft',
                                        'submitted' => 'Terkirim',
                                        'review_pembimbing' => 'Review Dosen',
                                        'review_kaprodi' => 'Review Kaprodi', 
                                        'approved' => 'Disetujui',
                                        'scheduled' => 'Terjadwal',
                                        'completed' => 'Selesai',
                                        'rejected' => 'Ditolak'
                                    ];
                                    $status_class = $status_classes[$seminar->status] ?? 'secondary';
                                    $status_text = $status_texts[$seminar->status] ?? 'Unknown';
                                    ?>
                                    <span class="badge badge-<?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($seminar->created_at ?? 'now')) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="<?= base_url('mahasiswa/seminar_skripsi/detail/' . $seminar->id) ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                                        <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                                           class="btn btn-sm btn-outline-warning ml-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Custom CSS -->
<style>
.icon-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.bg-light-success {
    background-color: rgba(40, 167, 69, 0.1) !important;
}

.bg-light-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.requirement-item {
    transition: all 0.3s ease;
}

.requirement-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.requirements-list .progress {
    border-radius: 10px;
    overflow: hidden;
}

.stat-item h4 {
    font-weight: bold;
}

.btn-lg {
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.alert.border-left-info {
    border-left: 4px solid #17a2b8;
}

.card {
    border-radius: 10px;
    border: none;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

@media (max-width: 768px) {
    .icon-circle {
        width: 60px !important;
        height: 60px !important;
    }
    
    .btn-lg {
        font-size: 1rem;
        padding: 12px 30px;
    }
    
    .requirement-item {
        margin-bottom: 15px;
    }
}
</style>