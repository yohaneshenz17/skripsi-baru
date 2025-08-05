<?php
/**
 * View Index Publikasi untuk Staf
 * File: application/views/staf/publikasi/index.php
 * Menggunakan template Argon AdminLTE yang konsisten dengan sistem
 */
?>

<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            
            <!-- Statistics Cards Row -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Validasi</h5>
                                    <span class="h2 font-weight-bold mb-0"><?= $stats['menunggu_validasi'] ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <a href="#pengajuan-section" class="text-nowrap text-warning font-weight-600">
                                    <i class="fa fa-arrow-down mr-1"></i> Lihat Detail
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Sudah Divalidasi</h5>
                                    <span class="h2 font-weight-bold mb-0"><?= $stats['sudah_divalidasi'] ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <a href="#riwayat-section" class="text-nowrap text-primary font-weight-600">
                                    <i class="fa fa-arrow-down mr-1"></i> Lihat Riwayat
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Publikasi Selesai</h5>
                                    <span class="h2 font-weight-bold mb-0"><?= $stats['publikasi_selesai'] ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <span class="text-success mr-2">
                                    <i class="fa fa-arrow-up"></i> Total Keseluruhan
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Bulan Ini</h5>
                                    <span class="h2 font-weight-bold mb-0"><?= $stats['total_bulan_ini'] ?></span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <span class="text-info mr-2">
                                    <?= date('F Y') ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Workflow Info Card -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-info-circle text-info mr-2"></i>
                                        Workflow Publikasi - Tugas Staf
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="workflow-container">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="workflow-step completed">
                                            <div class="step-number bg-secondary">1-2</div>
                                            <div class="step-content">
                                                <h6 class="step-title">Mahasiswa & Dosen</h6>
                                                <p class="step-desc">Mahasiswa ajukan → Dosen approve</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="workflow-step current">
                                            <div class="step-number bg-warning">3</div>
                                            <div class="step-content">
                                                <h6 class="step-title">Input Repository</h6>
                                                <p class="step-desc">Staf input link repository skripsi</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="workflow-step current">
                                            <div class="step-number bg-warning">4</div>
                                            <div class="step-content">
                                                <h6 class="step-title">Validasi Final</h6>
                                                <p class="step-desc">Staf validasi & kirim notifikasi</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="workflow-step">
                                            <div class="step-number bg-success">5-6</div>
                                            <div class="step-content">
                                                <h6 class="step-title">Selesai</h6>
                                                <p class="step-desc">Mahasiswa download surat</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pengajuan Perlu Validasi -->
            <div class="row mt-4" id="pengajuan-section">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-clock text-warning mr-2"></i>
                                        Pengajuan Perlu Validasi (<?= count($pengajuan_validasi) ?>)
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($pengajuan_validasi)): ?>
                                <div class="table-responsive">
                                    <table class="table align-items-center table-flush" id="pengajuanTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Mahasiswa</th>
                                                <th scope="col">Judul Skripsi</th>
                                                <th scope="col">Dosen Pembimbing</th>
                                                <th scope="col">Tgl Approve Dosen</th>
                                                <th scope="col">Status Repository</th>
                                                <th scope="col" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <?php foreach ($pengajuan_validasi as $index => $pub): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="media align-items-center">
                                                        <div class="media-body">
                                                            <span class="name mb-0 text-sm font-weight-600"><?= esc($pub->nama_mahasiswa) ?></span><br>
                                                            <small class="text-muted"><?= esc($pub->nim) ?></small><br>
                                                            <small class="badge badge-soft-info"><?= esc($pub->program_studi) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-wrap" style="max-width: 300px; display: block;">
                                                        <?= esc(word_limiter($pub->judul_skripsi_final, 8)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-weight-600"><?= esc($pub->nama_dosen_pembimbing) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($pub->tanggal_review_pembimbing): ?>
                                                        <span class="text-sm"><?= date('d/m/Y H:i', strtotime($pub->tanggal_review_pembimbing)) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($pub->link_repository)): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-link mr-1"></i> Sudah Input
                                                        </span>
                                                        <div class="text-xs text-muted mt-1">Siap validasi</div>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i> Belum Input
                                                        </span>
                                                        <div class="text-xs text-muted mt-1">Perlu input link</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="<?= base_url('staf/publikasi/detail/' . $pub->id) ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if (empty($pub->link_repository)): ?>
                                                            <a href="<?= base_url('staf/publikasi/input_repository/' . $pub->id) ?>" 
                                                               class="btn btn-sm btn-warning" title="Input Repository">
                                                                <i class="fas fa-plus"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?= base_url('staf/publikasi/validasi/' . $pub->id) ?>" 
                                                               class="btn btn-sm btn-success" title="Validasi Final">
                                                                <i class="fas fa-check"></i>
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
                                <div class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Tidak ada pengajuan yang perlu divalidasi</h5>
                                        <p class="text-muted">Semua pengajuan publikasi sudah diproses atau belum ada yang diajukan.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Riwayat Validasi -->
            <div class="row mt-4" id="riwayat-section">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-history text-primary mr-2"></i>
                                        Riwayat Validasi Terakhir (10 Data)
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($riwayat_validasi)): ?>
                                <div class="table-responsive">
                                    <table class="table align-items-center table-flush" id="riwayatTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">Mahasiswa</th>
                                                <th scope="col">Judul Skripsi</th>
                                                <th scope="col">Tgl Validasi</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Repository Link</th>
                                                <th scope="col" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <?php foreach ($riwayat_validasi as $riwayat): ?>
                                            <tr>
                                                <td>
                                                    <div class="media align-items-center">
                                                        <div class="media-body">
                                                            <span class="name mb-0 text-sm font-weight-600"><?= esc($riwayat->nama_mahasiswa) ?></span><br>
                                                            <small class="text-muted"><?= esc($riwayat->nim) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-wrap" style="max-width: 250px; display: block;">
                                                        <?= esc(word_limiter($riwayat->judul_skripsi_final, 6)) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-sm"><?= date('d/m/Y H:i', strtotime($riwayat->tanggal_validasi_staf)) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($riwayat->status_staf === 'approved'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i> Disetujui
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times mr-1"></i> Ditolak
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($riwayat->link_repository)): ?>
                                                        <a href="<?= esc($riwayat->link_repository) ?>" 
                                                           target="_blank" class="btn btn-xs btn-outline-primary">
                                                            <i class="fas fa-external-link-alt mr-1"></i> Lihat
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="<?= base_url('staf/publikasi/detail/' . $riwayat->id) ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada riwayat validasi</h5>
                                        <p class="text-muted">Riwayat validasi akan muncul setelah Anda memproses pengajuan publikasi.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Footer spacer -->
    <div class="row">
        <div class="col">
            <div style="height: 100px;"></div>
        </div>
    </div>
</div>

<style>
/* Workflow Steps */
.workflow-container {
    background: #f8f9fe;
    border-radius: 12px;
    padding: 30px 20px;
}

.workflow-step {
    text-align: center;
    position: relative;
    margin-bottom: 20px;
}

.workflow-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 25px;
    right: -50%;
    width: 100%;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}

.workflow-step.current:not(:last-child)::after {
    background: linear-gradient(to right, #ffc107 50%, #dee2e6 50%);
}

.workflow-step.completed:not(:last-child)::after {
    background: #28a745;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.step-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #32325d;
}

.step-desc {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 0;
    line-height: 1.4;
}

/* Empty State */
.empty-state {
    padding: 40px 20px;
}

/* Badge improvements */
.badge-soft-info {
    color: #2dce89;
    background-color: rgba(45, 206, 137, 0.1);
}

/* Button group */
.btn-group .btn {
    margin-right: 4px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Table improvements */
.table th {
    border-top: 0;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.table td {
    font-size: 14px;
    vertical-align: middle;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .workflow-step:not(:last-child)::after {
        display: none;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        font-size: 12px;
    }
    
    .step-title {
        font-size: 14px;
    }
    
    .step-desc {
        font-size: 12px;
    }
    
    .workflow-container {
        padding: 20px 15px;
    }
}

/* DataTables custom styling */
#pengajuanTable_wrapper .dataTables_length,
#pengajuanTable_wrapper .dataTables_filter,
#riwayatTable_wrapper .dataTables_length,
#riwayatTable_wrapper .dataTables_filter {
    margin-bottom: 20px;
}

#pengajuanTable_wrapper .dataTables_info,
#pengajuanTable_wrapper .dataTables_paginate,
#riwayatTable_wrapper .dataTables_info,
#riwayatTable_wrapper .dataTables_paginate {
    margin-top: 20px;
}
</style>