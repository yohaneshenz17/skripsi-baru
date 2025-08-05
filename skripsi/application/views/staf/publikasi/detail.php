<?php
/**
 * View Detail Publikasi untuk Staf
 * File: application/views/staf/publikasi/detail.php
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Publikasi Tugas Akhir</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/publikasi') ?>">Publikasi</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                            <div>
                                <?php if ($publikasi->status_pembimbing === 'approved' && $publikasi->status_staf === 'pending'): ?>
                                    <?php if (empty($publikasi->link_repository)): ?>
                                        <a href="<?= base_url('staf/publikasi/input_repository/' . $publikasi->id) ?>" 
                                           class="btn btn-warning">
                                            <i class="fas fa-plus"></i> Input Repository
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('staf/publikasi/validasi/' . $publikasi->id) ?>" 
                                           class="btn btn-success">
                                            <i class="fas fa-check"></i> Validasi Final
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Timeline -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-timeline"></i>
                            Status Workflow
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Step 1: Pengajuan Mahasiswa -->
                            <div class="timeline-item <?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'completed' : '' ?>">
                                <div class="timeline-marker bg-primary">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Pengajuan Mahasiswa</h6>
                                    <p class="text-muted mb-1">
                                        <?= $publikasi->tanggal_pengajuan ? date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) : 'Belum diajukan' ?>
                                    </p>
                                    <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                                        <small class="text-info"><?= esc($publikasi->keterangan_mahasiswa) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Step 2: Review Dosen -->
                            <div class="timeline-item <?= in_array($publikasi->status_pembimbing, ['approved']) ? 'completed' : ($publikasi->status_pembimbing === 'rejected' ? 'rejected' : '') ?>">
                                <div class="timeline-marker <?= $publikasi->status_pembimbing === 'approved' ? 'bg-success' : ($publikasi->status_pembimbing === 'rejected' ? 'bg-danger' : 'bg-secondary') ?>">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Review Dosen Pembimbing</h6>
                                    <p class="text-muted mb-1">
                                        <?= $publikasi->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : 'Menunggu review' ?>
                                    </p>
                                    <?php if ($publikasi->status_pembimbing === 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php elseif ($publikasi->status_pembimbing === 'rejected'): ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                    <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                                        <br><small class="text-info"><?= esc($publikasi->komentar_pembimbing) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Step 3: Validasi Staf -->
                            <div class="timeline-item <?= $publikasi->status === 'completed' ? 'completed' : ($publikasi->status === 'review_staf' ? 'current' : '') ?>">
                                <div class="timeline-marker <?= $publikasi->status === 'completed' ? 'bg-success' : ($publikasi->status === 'review_staf' ? 'bg-warning' : 'bg-secondary') ?>">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Validasi Staf</h6>
                                    <p class="text-muted mb-1">
                                        <?= $publikasi->tanggal_validasi_staf ? date('d/m/Y H:i', strtotime($publikasi->tanggal_validasi_staf)) : 'Menunggu validasi' ?>
                                    </p>
                                    <?php if ($publikasi->status_staf === 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php elseif ($publikasi->status_staf === 'rejected'): ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                    <?php if (!empty($publikasi->validated_by_staf_name)): ?>
                                        <br><small class="text-info">Oleh: <?= esc($publikasi->validated_by_staf_name) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Step 4: Selesai -->
                            <div class="timeline-item <?= $publikasi->status === 'completed' ? 'completed' : '' ?>">
                                <div class="timeline-marker <?= $publikasi->status === 'completed' ? 'bg-success' : 'bg-secondary' ?>">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Publikasi Selesai</h6>
                                    <p class="text-muted mb-1">
                                        <?= $publikasi->tanggal_selesai ? date('d/m/Y H:i', strtotime($publikasi->tanggal_selesai)) : 'Belum selesai' ?>
                                    </p>
                                    <?php if ($publikasi->status === 'completed'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                        <br><small class="text-success">Mahasiswa dapat download surat keterangan</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Data Mahasiswa -->
            <div class="col-md-6">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-graduate"></i>
                            Data Mahasiswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Nama</strong></td>
                                <td>: <?= esc($publikasi->nama_mahasiswa) ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM</strong></td>
                                <td>: <?= esc($publikasi->nim) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?= esc($publikasi->program_studi) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: <?= esc($publikasi->email_mahasiswa) ?></td>
                            </tr>
                            <?php if (!empty($publikasi->nomor_telepon)): ?>
                            <tr>
                                <td><strong>No. Telepon</strong></td>
                                <td>: <?= esc($publikasi->nomor_telepon) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Data Dosen Pembimbing -->
            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Dosen Pembimbing
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Nama Dosen</strong></td>
                                <td>: <?= esc($publikasi->nama_dosen_pembimbing) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: <?= esc($publikasi->email_pembimbing ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status Review</strong></td>
                                <td>: 
                                    <?php if ($publikasi->status_pembimbing === 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php elseif ($publikasi->status_pembimbing === 'rejected'): ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Review</strong></td>
                                <td>: <?= $publikasi->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Skripsi -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book"></i>
                            Data Skripsi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-sm">
                                    <tr>
                                        <td width="25%"><strong>Judul Skripsi Final</strong></td>
                                        <td>: <?= esc($publikasi->judul_skripsi_final) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Ujian</strong></td>
                                        <td>: <?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Link Repository</strong></td>
                                        <td>: 
                                            <?php if (!empty($publikasi->link_repository)): ?>
                                                <a href="<?= esc($publikasi->link_repository) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt"></i> Lihat Repository
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum diinput</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen -->
        <?php if ($show_files): ?>
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-pdf"></i>
                            Dokumen Publikasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- File Surat Revisi -->
                            <div class="col-md-4">
                                <div class="file-item">
                                    <div class="file-icon">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </div>
                                    <div class="file-info">
                                        <h6>Surat Keterangan Revisi</h6>
                                        <?php if (!empty($publikasi->file_surat_revisi)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="downloadFile('surat_revisi', <?= $publikasi->id ?>)">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- File Skripsi Final -->
                            <div class="col-md-4">
                                <div class="file-item">
                                    <div class="file-icon">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </div>
                                    <div class="file-info">
                                        <h6>File Skripsi Final</h6>
                                        <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- File Surat Perpustakaan -->
                            <div class="col-md-4">
                                <div class="file-item">
                                    <div class="file-icon">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    </div>
                                    <div class="file-info">
                                        <h6>Surat Perpustakaan</h6>
                                        <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="downloadFile('surat_perpustakaan', <?= $publikasi->id ?>)">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Komentar/Catatan -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-comments"></i>
                            Komentar & Catatan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Keterangan Mahasiswa -->
                            <div class="col-md-4">
                                <div class="comment-section">
                                    <h6 class="text-primary">
                                        <i class="fas fa-user-graduate"></i> Keterangan Mahasiswa
                                    </h6>
                                    <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                                        <div class="comment-box">
                                            <?= nl2br(esc($publikasi->keterangan_mahasiswa)) ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Tidak ada keterangan</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Komentar Dosen -->
                            <div class="col-md-4">
                                <div class="comment-section">
                                    <h6 class="text-warning">
                                        <i class="fas fa-chalkboard-teacher"></i> Komentar Dosen
                                    </h6>
                                    <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                                        <div class="comment-box">
                                            <?= nl2br(esc($publikasi->komentar_pembimbing)) ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Tidak ada komentar</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Komentar Staf -->
                            <div class="col-md-4">
                                <div class="comment-section">
                                    <h6 class="text-success">
                                        <i class="fas fa-user-tie"></i> Komentar Staf
                                    </h6>
                                    <?php if (!empty($publikasi->komentar_staf)): ?>
                                        <div class="comment-box">
                                            <?= nl2br(esc($publikasi->komentar_staf)) ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Belum ada komentar</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 40px;
}

.timeline-item.completed .timeline-marker {
    background-color: #28a745 !important;
}

.timeline-item.current .timeline-marker {
    background-color: #ffc107 !important;
    animation: pulse 2s infinite;
}

.timeline-item.rejected .timeline-marker {
    background-color: #dc3545 !important;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    z-index: 1;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    font-weight: 600;
    margin-bottom: 5px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* File Items */
.file-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #f8f9fa;
}

.file-icon {
    font-size: 24px;
    margin-right: 15px;
}

.file-info h6 {
    margin-bottom: 10px;
    font-weight: 600;
}

/* Comments */
.comment-section {
    margin-bottom: 20px;
}

.comment-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    font-size: 14px;
    line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-item {
        padding-left: 30px;
    }
    
    .timeline-marker {
        left: -20px;
        width: 25px;
        height: 25px;
        font-size: 10px;
    }
    
    .file-item {
        flex-direction: column;
        text-align: center;
    }
    
    .file-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>