<?php
// KONSISTEN UI UNTUK: application/views/mahasiswa/publikasi/index.php
// Mengikuti pattern template mahasiswa.php yang sudah ada di phase 1-5
?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check"></i> <?= $this->session->flashdata('success') ?>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-ban"></i> <?= $this->session->flashdata('error') ?>
</div>
<?php endif; ?>

<!-- Header Section -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-globe mr-2"></i>
                    Progress Workflow Tugas Akhir
                </h3>
            </div>
            <div class="card-body">
                <!-- Progress Steps -->
                <div class="progress-wrapper">
                    <div class="step-progress">
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-label">Proposal</div>
                        </div>
                        <div class="step-line completed"></div>
                        
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-label">Bimbingan</div>
                        </div>
                        <div class="step-line completed"></div>
                        
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-label">Seminar Proposal</div>
                        </div>
                        <div class="step-line completed"></div>
                        
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-label">Penelitian</div>
                        </div>
                        <div class="step-line completed"></div>
                        
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="step-label">Seminar Skripsi</div>
                        </div>
                        <div class="step-line active"></div>
                        
                        <div class="step active">
                            <div class="step-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="step-label">Publikasi</div>
                        </div>
                        <div class="step-line"></div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="step-label">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Status Publikasi Tugas Akhir
                </h3>
            </div>
            <div class="card-body">
                
                <?php if (!$proposal): ?>
                    <!-- Belum Ada Proposal -->
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Informasi</h5>
                        Anda belum memiliki proposal yang disetujui. Silakan ajukan proposal terlebih dahulu untuk dapat mengajukan publikasi.
                        <br><br>
                        <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajukan Proposal
                        </a>
                    </div>
                    
                <?php else: ?>
                    <!-- Data Proposal -->
                    <div class="info-section mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-file-alt text-primary"></i> 
                            Informasi Proposal
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Judul Skripsi</div>
                                    <div class="info-value"><?= character_limiter($proposal->judul, 100) ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Dosen Pembimbing</div>
                                    <div class="info-value"><?= $proposal->nama_pembimbing ?? 'Belum ditetapkan' ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <div class="info-label">Status Proposal</div>
                                    <div class="info-value">
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Disetujui
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Jurnal Bimbingan</div>
                                    <div class="info-value">
                                        <?php if ($jurnal_count >= 16): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> <?= $jurnal_count ?>/16 Tervalidasi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> <?= $jurnal_count ?>/16 Tervalidasi
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Publikasi -->
                    <?php if ($eligible): ?>
                        <?php if (!$publikasi): ?>
                            <!-- Belum Ada Pengajuan -->
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-trophy"></i> Selamat! Anda Eligible untuk Publikasi</h5>
                                <p>Semua syarat publikasi telah terpenuhi. Anda dapat mengajukan publikasi tugas akhir sekarang.</p>
                                <div class="mt-3">
                                    <a href="<?= base_url('mahasiswa/publikasi/ajukan/' . $proposal->id) ?>" class="btn btn-success btn-lg">
                                        <i class="fas fa-paper-plane"></i> Ajukan Publikasi Sekarang
                                    </a>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Ada Pengajuan -->
                            <div class="status-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-clipboard-check text-success"></i>
                                    Status Pengajuan Publikasi
                                </h5>
                                
                                <div class="alert alert-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'info') ?>">
                                    <?php if ($publikasi->status === 'draft'): ?>
                                        <h6><i class="fas fa-edit"></i> Status: Draft</h6>
                                        <p>Pengajuan masih dalam tahap draft. Silakan lengkapi dan submit pengajuan Anda.</p>
                                        
                                    <?php elseif ($publikasi->status === 'submitted' || $publikasi->status === 'review_pembimbing'): ?>
                                        <h6><i class="fas fa-clock"></i> Status: Menunggu Review Dosen</h6>
                                        <p>Pengajuan Anda sedang menunggu review dari dosen pembimbing.</p>
                                        <small class="text-muted">Disubmit pada: <?= date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></small>
                                        
                                    <?php elseif ($publikasi->status === 'review_staf'): ?>
                                        <h6><i class="fas fa-user-tie"></i> Status: Review Staf</h6>
                                        <p>Pengajuan telah disetujui dosen pembimbing dan sedang menunggu validasi staf.</p>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <div class="mt-2">
                                                <strong>Komentar Dosen:</strong><br>
                                                <em><?= $publikasi->komentar_pembimbing ?></em>
                                            </div>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($publikasi->status === 'completed'): ?>
                                        <h6><i class="fas fa-check-circle"></i> Status: Publikasi Selesai</h6>
                                        <p>🎉 Selamat! Publikasi tugas akhir Anda telah selesai diproses.</p>
                                        <?php if ($publikasi->link_repository): ?>
                                            <div class="mt-2">
                                                <strong>Link Repository:</strong><br>
                                                <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-external-link-alt"></i> Lihat Publikasi
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($publikasi->status === 'rejected'): ?>
                                        <h6><i class="fas fa-times-circle"></i> Status: Ditolak</h6>
                                        <p>Pengajuan ditolak dan perlu diperbaiki. Silakan perbaiki sesuai komentar dan ajukan kembali.</p>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <div class="mt-2">
                                                <strong>Komentar Dosen:</strong><br>
                                                <em><?= $publikasi->komentar_pembimbing ?></em>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="action-buttons mt-3">
                                    <?php if ($publikasi->status === 'draft'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Lengkapi Pengajuan
                                        </a>
                                        
                                    <?php elseif ($publikasi->status === 'rejected'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Perbaiki Pengajuan
                                        </a>
                                        
                                    <?php elseif ($publikasi->status === 'completed'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" class="btn btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Download Surat Keterangan
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?= base_url('mahasiswa/publikasi/tracking/' . $publikasi->id) ?>" class="btn btn-info">
                                        <i class="fas fa-eye"></i> Tracking Progress
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- Belum Eligible -->
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Belum Memenuhi Syarat</h5>
                            <p>Anda belum dapat mengajukan publikasi karena syarat berikut belum terpenuhi:</p>
                            <ul class="mb-0">
                                <?php if ($jurnal_count < 16): ?>
                                    <li>Minimal 16 jurnal bimbingan tervalidasi (saat ini: <?= $jurnal_count ?>)</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Syarat Publikasi -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-check"></i>
                    Syarat Publikasi
                </h3>
            </div>
            <div class="card-body">
                <div class="syarat-list">
                    <div class="syarat-item">
                        <div class="syarat-check">
                            <?php if ($jurnal_count >= 16): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i>
                            <?php endif; ?>
                        </div>
                        <div class="syarat-text">
                            <div class="syarat-title">Minimal 16 jurnal bimbingan tervalidasi</div>
                            <div class="syarat-status">
                                <?= $jurnal_count ?>/16
                            </div>
                        </div>
                    </div>
                    
                    <div class="syarat-item">
                        <div class="syarat-check">
                            <?php if ($proposal): ?>
                                <i class="fas fa-check-circle text-success"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i>
                            <?php endif; ?>
                        </div>
                        <div class="syarat-text">
                            <div class="syarat-title">Proposal telah disetujui</div>
                            <div class="syarat-status">
                                <?= $proposal ? 'Disetujui' : 'Belum disetujui' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokumen yang Diperlukan -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-pdf"></i>
                    Dokumen yang Diperlukan
                </h3>
            </div>
            <div class="card-body">
                <div class="dokumen-list">
                    <div class="dokumen-item">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span>Surat Keterangan Revisi (PDF, max 1MB)</span>
                    </div>
                    <div class="dokumen-item">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span>File Skripsi Final (PDF, max 5MB)</span>
                    </div>
                    <div class="dokumen-item">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span>Surat Perpustakaan (PDF, max 1MB)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alur Proses -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route"></i>
                    Alur Proses
                </h3>
            </div>
            <div class="card-body">
                <div class="alur-list">
                    <div class="alur-item">
                        <div class="alur-number">1</div>
                        <div class="alur-text">Mahasiswa mengajukan</div>
                    </div>
                    <div class="alur-item">
                        <div class="alur-number">2</div>
                        <div class="alur-text">Review dosen pembimbing</div>
                    </div>
                    <div class="alur-item">
                        <div class="alur-number">3</div>
                        <div class="alur-text">Validasi staf</div>
                    </div>
                    <div class="alur-item">
                        <div class="alur-number">4</div>
                        <div class="alur-text">Publikasi selesai</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
/* Progress Steps */
.progress-wrapper {
    padding: 20px 0;
}

.step-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    min-width: 80px;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.step.completed .step-icon {
    background: #28a745;
    color: white;
}

.step.active .step-icon {
    background: #ffc107;
    color: white;
    animation: pulse 2s infinite;
}

.step-label {
    font-size: 12px;
    font-weight: 600;
    text-align: center;
    color: #6c757d;
}

.step-line {
    width: 50px;
    height: 3px;
    background: #e9ecef;
    margin: 0 5px;
}

.step-line.completed {
    background: #28a745;
}

.step-line.active {
    background: linear-gradient(to right, #28a745, #ffc107);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

/* Info Section */
.info-section {
    border-left: 4px solid #007bff;
    padding-left: 15px;
}

.info-item {
    margin-bottom: 15px;
}

.info-label {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 4px;
}

.info-value {
    font-size: 14px;
    color: #495057;
}

/* Syarat List */
.syarat-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f8f9fa;
}

.syarat-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.syarat-check {
    margin-right: 12px;
    font-size: 18px;
    margin-top: 2px;
}

.syarat-text {
    flex: 1;
}

.syarat-title {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 2px;
}

.syarat-status {
    font-size: 12px;
    color: #6c757d;
}

/* Dokumen List */
.dokumen-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-size: 14px;
}

.dokumen-item i {
    margin-right: 10px;
    width: 16px;
}

/* Alur List */
.alur-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
}

.alur-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    margin-right: 12px;
}

.alur-text {
    font-size: 14px;
    color: #495057;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Status Section */
.status-section {
    border-left: 4px solid #28a745;
    padding-left: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .step-progress {
        flex-direction: column;
    }
    
    .step-line {
        width: 3px;
        height: 30px;
        margin: 5px 0;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script>