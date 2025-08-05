<?php
/**
 * View Detail Publikasi untuk Staf
 * File: application/views/staf/publikasi/detail.php
 * Menggunakan template Argon AdminLTE yang konsisten dengan sistem
 */
?>

<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            
            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                </div>
                                <div>
                                    <?php if ($publikasi->status_pembimbing === 'approved' && $publikasi->status_staf === 'pending'): ?>
                                        <?php if (empty($publikasi->link_repository)): ?>
                                            <a href="<?= base_url('staf/publikasi/input_repository/' . $publikasi->id) ?>" 
                                               class="btn btn-warning">
                                                <i class="fas fa-plus mr-2"></i> Input Repository
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('staf/publikasi/validasi/' . $publikasi->id) ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-check mr-2"></i> Validasi Final
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
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-timeline text-primary mr-2"></i>
                                Status Workflow Publikasi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline-container">
                                <!-- Step 1: Pengajuan Mahasiswa -->
                                <div class="timeline-item <?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'completed' : '' ?>">
                                    <div class="timeline-marker bg-primary">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">Pengajuan Mahasiswa</h6>
                                            <small class="text-muted">
                                                <?= $publikasi->tanggal_pengajuan ? date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) : 'Belum diajukan' ?>
                                            </small>
                                        </div>
                                        <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                                            <div class="timeline-desc">
                                                <small class="text-info"><?= esc($publikasi->keterangan_mahasiswa) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Step 2: Review Dosen -->
                                <div class="timeline-item <?= in_array($publikasi->status_pembimbing, ['approved']) ? 'completed' : ($publikasi->status_pembimbing === 'rejected' ? 'rejected' : '') ?>">
                                    <div class="timeline-marker <?= $publikasi->status_pembimbing === 'approved' ? 'bg-success' : ($publikasi->status_pembimbing === 'rejected' ? 'bg-danger' : 'bg-secondary') ?>">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">Review Dosen Pembimbing</h6>
                                            <small class="text-muted">
                                                <?= $publikasi->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : 'Menunggu review' ?>
                                            </small>
                                        </div>
                                        <div class="timeline-status">
                                            <?php if ($publikasi->status_pembimbing === 'approved'): ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php elseif ($publikasi->status_pembimbing === 'rejected'): ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                                            <div class="timeline-desc">
                                                <small class="text-info"><?= esc($publikasi->komentar_pembimbing) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Step 3: Validasi Staf -->
                                <div class="timeline-item <?= $publikasi->status === 'completed' ? 'completed' : ($publikasi->status === 'review_staf' ? 'current' : '') ?>">
                                    <div class="timeline-marker <?= $publikasi->status === 'completed' ? 'bg-success' : ($publikasi->status === 'review_staf' ? 'bg-warning' : 'bg-secondary') ?>">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">Validasi Staf</h6>
                                            <small class="text-muted">
                                                <?= $publikasi->tanggal_validasi_staf ? date('d/m/Y H:i', strtotime($publikasi->tanggal_validasi_staf)) : 'Menunggu validasi' ?>
                                            </small>
                                        </div>
                                        <div class="timeline-status">
                                            <?php if ($publikasi->status_staf === 'approved'): ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php elseif ($publikasi->status_staf === 'rejected'): ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($publikasi->validated_by_staf_name)): ?>
                                            <div class="timeline-desc">
                                                <small class="text-info">Oleh: <?= esc($publikasi->validated_by_staf_name) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Step 4: Selesai -->
                                <div class="timeline-item <?= $publikasi->status === 'completed' ? 'completed' : '' ?>">
                                    <div class="timeline-marker <?= $publikasi->status === 'completed' ? 'bg-success' : 'bg-secondary' ?>">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">Publikasi Selesai</h6>
                                            <small class="text-muted">
                                                <?= $publikasi->tanggal_selesai ? date('d/m/Y H:i', strtotime($publikasi->tanggal_selesai)) : 'Belum selesai' ?>
                                            </small>
                                        </div>
                                        <?php if ($publikasi->status === 'completed'): ?>
                                            <div class="timeline-status">
                                                <span class="badge badge-success">Selesai</span>
                                            </div>
                                            <div class="timeline-desc">
                                                <small class="text-success">Mahasiswa dapat download surat keterangan</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Data Mahasiswa -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-user-graduate text-info mr-2"></i>
                                Data Mahasiswa
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Nama</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->nama_mahasiswa) ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>NIM</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->nim) ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Program Studi</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->program_studi) ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Email</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->email_mahasiswa) ?>
                                </div>
                            </div>
                            <?php if (!empty($publikasi->nomor_telepon)): ?>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>No. Telepon</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->nomor_telepon) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Data Dosen Pembimbing -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-chalkboard-teacher text-warning mr-2"></i>
                                Dosen Pembimbing
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Nama Dosen</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->nama_dosen_pembimbing) ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Email</strong>
                                </div>
                                <div class="col-7">
                                    : <?= esc($publikasi->email_pembimbing ?: '-') ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Status Review</strong>
                                </div>
                                <div class="col-7">
                                    : 
                                    <?php if ($publikasi->status_pembimbing === 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php elseif ($publikasi->status_pembimbing === 'rejected'): ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-5">
                                    <strong>Tanggal Review</strong>
                                </div>
                                <div class="col-7">
                                    : <?= $publikasi->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : '-' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Skripsi -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-book text-success mr-2"></i>
                                Data Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Judul Skripsi Final</strong>
                                </div>
                                <div class="col-md-9">
                                    : <?= esc($publikasi->judul_skripsi_final) ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Tanggal Ujian</strong>
                                </div>
                                <div class="col-md-9">
                                    : <?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Link Repository</strong>
                                </div>
                                <div class="col-md-9">
                                    : 
                                    <?php if (!empty($publikasi->link_repository)): ?>
                                        <a href="<?= esc($publikasi->link_repository) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt mr-1"></i> Lihat Repository
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Belum diinput</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dokumen -->
            <?php if ($show_files): ?>
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-file-pdf text-danger mr-2"></i>
                                Dokumen Publikasi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- File Surat Revisi -->
                                <div class="col-md-4">
                                    <div class="file-card">
                                        <div class="file-icon">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </div>
                                        <div class="file-content">
                                            <h6 class="file-title">Surat Keterangan Revisi</h6>
                                            <?php if (!empty($publikasi->file_surat_revisi)): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="downloadFile('surat_revisi', <?= $publikasi->id ?>)">
                                                    <i class="fas fa-download mr-1"></i> Download
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Tidak ada file</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Skripsi Final -->
                                <div class="col-md-4">
                                    <div class="file-card">
                                        <div class="file-icon">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </div>
                                        <div class="file-content">
                                            <h6 class="file-title">File Skripsi Final</h6>
                                            <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                                    <i class="fas fa-download mr-1"></i> Download
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Tidak ada file</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Surat Perpustakaan -->
                                <div class="col-md-4">
                                    <div class="file-card">
                                        <div class="file-icon">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        </div>
                                        <div class="file-content">
                                            <h6 class="file-title">Surat Perpustakaan</h6>
                                            <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="downloadFile('surat_perpustakaan', <?= $publikasi->id ?>)">
                                                    <i class="fas fa-download mr-1"></i> Download
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
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-comments text-secondary mr-2"></i>
                                Komentar & Catatan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Keterangan Mahasiswa -->
                                <div class="col-md-4">
                                    <div class="comment-section">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-user-graduate mr-1"></i> Keterangan Mahasiswa
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
                                        <h6 class="text-warning mb-3">
                                            <i class="fas fa-chalkboard-teacher mr-1"></i> Komentar Dosen
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
                                        <h6 class="text-success mb-3">
                                            <i class="fas fa-user-tie mr-1"></i> Komentar Staf
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
    </div>
    
    <!-- Footer spacer -->
    <div class="row">
        <div class="col">
            <div style="height: 100px;"></div>
        </div>
    </div>
</div>

<style>
/* Timeline Styles */
.timeline-container {
    position: relative;
    padding: 20px 0;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 20px;
    bottom: 20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;  
    margin-bottom: 30px;
    padding-left: 80px;
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
    left: 15px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    z-index: 1;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.timeline-content {
    background: #f8f9fe;
    border-radius: 8px;
    padding: 20px;
    border-left: 3px solid #5e72e4;
}

.timeline-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.timeline-title {
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #32325d;
}

.timeline-status {
    margin: 10px 0;
}

.timeline-desc {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #dee2e6;
}

@keyframes pulse {
    0% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* File Cards */
.file-card {
    text-align: center;
    padding: 30px 20px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    margin-bottom: 20px;
    background: #f8f9fe;
    transition: all 0.3s ease;
}

.file-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.file-icon {
    font-size: 32px;
    margin-bottom: 15px;
}

.file-title {
    font-weight: 600;
    margin-bottom: 15px;
    color: #32325d;
}

/* Comments */
.comment-section {
    margin-bottom: 30px;
}

.comment-box {
    background: #f8f9fe;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    font-size: 14px;
    line-height: 1.6;
    color: #525f7f;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .timeline-container::before {
        left: 20px;
    }
    
    .timeline-item {
        padding-left: 60px;
    }
    
    .timeline-marker {
        left: 5px;
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .timeline-header {
        flex-direction: column;
    }
    
    .file-card {
        margin-bottom: 15px;
        padding: 20px 15px;
    }
}
</style>

<script>
// Download file function
function downloadFile(type, id) {
    window.open('<?= base_url('staf/publikasi/download_file/') ?>' + type + '/' + id, '_blank');
}
</script>