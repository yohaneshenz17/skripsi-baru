<!-- 
PUBLIKASI TUGAS AKHIR - MAHASISWA DASHBOARD - FIXED UI KONSISTEN
File: application/views/mahasiswa/publikasi/index.php

MENGGUNAKAN TEMPLATE ADMINLTE YANG SUDAH ADA, DISESUAIKAN DENGAN WORKFLOW 9 LANGKAH:
1. Mahasiswa Memenuhi Syarat (16+ jurnal)
2. Isi Form Pengajuan
3. Kirim Ajuan  
4-6. Dosen Review
7. Staf Validasi
8. Download Surat
9. Format Surat Template Kampus
-->

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

<!-- Progress Workflow Header -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route mr-2"></i>
                    Progress Workflow Tugas Akhir
                </h3>
            </div>
            <div class="card-body">
                <!-- Progress Steps Horizontal -->
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
                    <!-- Step 1: Informasi Proposal -->
                    <div class="info-section mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-file-alt text-primary"></i> 
                            Informasi Proposal Anda
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
                                    <div class="info-label">Status Seminar Skripsi</div>
                                    <div class="info-value">
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Selesai
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Jurnal Bimbingan Tervalidasi</div>
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

                    <!-- Status Workflow Publikasi -->
                    <?php if ($eligible): ?>
                        <?php if (!$publikasi): ?>
                            <!-- Step 1 Completed: Ready untuk Step 2 -->
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-trophy"></i> ✅ Step 1 Selesai: Anda Memenuhi Syarat Publikasi!</h5>
                                <p><strong>Syarat terpenuhi:</strong></p>
                                <ul class="mb-3">
                                    <li>✅ Minimal 16 Jurnal Bimbingan Tervalidasi (<?= $jurnal_count ?>/16)</li>
                                    <li>✅ Seminar Skripsi telah selesai dan disetujui</li>
                                </ul>
                                <p class="mb-3"><strong>Lanjut ke Step 2:</strong> Isi Form Pengajuan Publikasi dengan 9 field yang diperlukan.</p>
                                <div class="mt-3">
                                    <a href="<?= base_url('mahasiswa/publikasi/ajukan/' . $proposal->id) ?>" class="btn btn-success btn-lg">
                                        <i class="fas fa-arrow-right"></i> Lanjut ke Step 2: Isi Form Pengajuan
                                    </a>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <!-- Step 2-9: Ada Pengajuan, Tampilkan Progress -->
                            <div class="status-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-clipboard-check text-success"></i>
                                    Progress Publikasi Anda (9 Langkah Workflow)
                                </h5>
                                
                                <!-- Progress Visual -->
                                <div class="workflow-progress mb-4">
                                    <?php
                                    // Tentukan step saat ini berdasarkan status
                                    $current_step = 2; // Default step 2 (form filled)
                                    
                                    if (isset($publikasi->status)) {
                                        switch($publikasi->status) {
                                            case 'draft': $current_step = 2; break;
                                            case 'submitted': case 'review_pembimbing': $current_step = 4; break;
                                            case 'approved_pembimbing': case 'review_staf': $current_step = 7; break;
                                            case 'completed': $current_step = 9; break;
                                            case 'rejected': $current_step = 6; break;
                                        }
                                    }
                                    ?>
                                    
                                    <div class="mini-progress">
                                        <?php for($i = 1; $i <= 9; $i++): ?>
                                            <div class="mini-step <?= $i <= $current_step ? 'completed' : ($i == $current_step + 1 ? 'current' : '') ?>">
                                                <span><?= $i ?></span>
                                            </div>
                                            <?php if($i < 9): ?>
                                                <div class="mini-line <?= $i < $current_step ? 'completed' : '' ?>"></div>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="step-info">
                                        <small class="text-muted">Step <?= $current_step ?> dari 9 langkah workflow</small>
                                    </div>
                                </div>
                                
                                <!-- Status Detail -->
                                <div class="alert alert-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'info') ?>">
                                    <?php if ($publikasi->status === 'draft'): ?>
                                        <h6><i class="fas fa-edit"></i> Step 2: Form Pengajuan Masih Draft</h6>
                                        <p>Anda telah memulai pengisian form publikasi. Silakan lengkapi dan lanjut ke Step 3 (Kirim Ajuan).</p>
                                        
                                    <?php elseif ($publikasi->status === 'submitted' || $publikasi->status === 'review_pembimbing'): ?>
                                        <h6><i class="fas fa-clock"></i> Step 4-6: Menunggu Review Dosen Pembimbing</h6>
                                        <p>✅ Step 2 & 3 selesai. Pengajuan Anda sedang menunggu review dari dosen pembimbing.</p>
                                        <small class="text-muted">Disubmit pada: <?= date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></small>
                                        
                                    <?php elseif ($publikasi->status === 'approved_pembimbing' || $publikasi->status === 'review_staf'): ?>
                                        <h6><i class="fas fa-user-tie"></i> Step 7: Review Staf</h6>
                                        <p>✅ Step 4-6 selesai. Dosen pembimbing telah menyetujui. Sedang menunggu validasi staf.</p>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <div class="mt-2">
                                                <strong>Komentar Dosen:</strong><br>
                                                <em>"<?= $publikasi->komentar_pembimbing ?>"</em>
                                            </div>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($publikasi->status === 'completed'): ?>
                                        <h6><i class="fas fa-check-circle"></i> Step 8-9: Publikasi Selesai! 🎉</h6>
                                        <p><strong>Selamat!</strong> Semua 9 langkah workflow telah selesai. Publikasi tugas akhir Anda telah berhasil diproses.</p>
                                        <?php if ($publikasi->link_repository): ?>
                                            <div class="mt-2">
                                                <strong>Link Repository:</strong><br>
                                                <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-external-link-alt"></i> Lihat Publikasi
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($publikasi->status === 'rejected'): ?>
                                        <h6><i class="fas fa-times-circle"></i> Step 6: Pengajuan Ditolak Dosen</h6>
                                        <p>Pengajuan ditolak dan perlu diperbaiki. Silakan perbaiki sesuai komentar dan kembali ke Step 2.</p>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <div class="mt-2">
                                                <strong>Alasan Penolakan:</strong><br>
                                                <em>"<?= $publikasi->komentar_pembimbing ?>"</em>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="action-buttons mt-3">
                                    <?php if ($publikasi->status === 'draft'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Lengkapi Form (Step 2)
                                        </a>
                                        
                                    <?php elseif ($publikasi->status === 'rejected'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Perbaiki & Ajukan Ulang (Kembali ke Step 2)
                                        </a>
                                        
                                    <?php elseif ($publikasi->status === 'completed'): ?>
                                        <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" class="btn btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Step 8: Download Surat Keterangan
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?= base_url('mahasiswa/publikasi/tracking/' . $publikasi->id) ?>" class="btn btn-info">
                                        <i class="fas fa-route"></i> Lihat Detail Workflow 9 Langkah
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <!-- Step 1 Belum Selesai: Belum Eligible -->
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> ❌ Step 1: Belum Memenuhi Syarat</h5>
                            <p>Anda belum dapat lanjut ke Step 2 karena syarat Step 1 belum terpenuhi:</p>
                            <ul class="mb-3">
                                <?php if ($jurnal_count < 16): ?>
                                    <li>❌ Minimal 16 jurnal bimbingan tervalidasi (saat ini: <?= $jurnal_count ?>/16)</li>
                                    <li class="text-muted">Silakan lanjutkan bimbingan dengan dosen pembimbing</li>
                                <?php endif; ?>
                            </ul>
                            <p><strong>Yang harus dilakukan:</strong> Lanjutkan bimbingan hingga mencapai minimal 16 jurnal yang tervalidasi dosen.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Kanan -->
    <div class="col-lg-4">
        <!-- 9 Langkah Workflow -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ol"></i>
                    9 Langkah Workflow Publikasi
                </h3>
            </div>
            <div class="card-body">
                <div class="workflow-steps">
                    <div class="workflow-step">
                        <div class="step-number <?= $eligible ? 'completed' : 'current' ?>">1</div>
                        <div class="step-content">
                            <div class="step-title">Mahasiswa Memenuhi Syarat</div>
                            <div class="step-desc">16+ Jurnal Bimbingan Tervalidasi</div>
                        </div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-number <?= $publikasi && in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'completed' : ($eligible && !$publikasi ? 'current' : '') ?>">2</div>
                        <div class="step-content">
                            <div class="step-title">Isi Form Pengajuan</div>
                            <div class="step-desc">9 field data publikasi</div>
                        </div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-number <?= $publikasi && in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'completed' : '' ?>">3</div>
                        <div class="step-content">
                            <div class="step-title">Kirim Ajuan</div>
                            <div class="step-desc">Submit ke dosen pembimbing</div> 
                        </div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-number <?= $publikasi && in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'completed' : ($publikasi && $publikasi->status == 'review_pembimbing' ? 'current' : '') ?>">4-6</div>
                        <div class="step-content">
                            <div class="step-title">Review Dosen</div>
                            <div class="step-desc">Approve/Reject pembimbing</div>
                        </div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-number <?= $publikasi && $publikasi->status == 'completed' ? 'completed' : ($publikasi && $publikasi->status == 'review_staf' ? 'current' : '') ?>">7</div>
                        <div class="step-content">
                            <div class="step-title">Validasi Staf</div>
                            <div class="step-desc">Input repository & validasi</div>
                        </div>
                    </div>
                    
                    <div class="workflow-step">
                        <div class="step-number <?= $publikasi && $publikasi->status == 'completed' ? 'completed' : '' ?>">8-9</div>
                        <div class="step-content">
                            <div class="step-title">Selesai & Download</div>
                            <div class="step-desc">Surat keterangan publikasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Syarat & Dokumen -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-pdf"></i>
                    Dokumen yang Diperlukan (Step 2)
                </h3>
            </div>
            <div class="card-body">
                <div class="dokumen-list">
                    <div class="dokumen-item">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span>Surat Perpustakaan (PDF, max 1MB)</span>
                    </div>
                    <div class="dokumen-item">
                        <i class="fas fa-file-pdf text-danger"></i>
                        <span>File Skripsi Final (PDF, max 5MB)</span>
                    </div>
                    <div class="dokumen-item">
                        <i class="fas fa-link text-info"></i>
                        <span>Link Repository (Opsional)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS untuk Workflow -->
<style>
/* Progress Steps Horizontal */
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
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Mini Progress untuk Status */
.workflow-progress {
    text-align: center;
}

.mini-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.mini-step {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
    color: #6c757d;
}

.mini-step.completed {
    background: #28a745;
    color: white;
}

.mini-step.current {
    background: #ffc107;
    color: white;
}

.mini-line {
    width: 20px;
    height: 2px;
    background: #e9ecef;
}

.mini-line.completed {
    background: #28a745;
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

/* Workflow Steps Sidebar */
.workflow-steps {
    position: relative;
}

.workflow-steps::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.workflow-step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    position: relative;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    margin-right: 15px;
    position: relative;
    z-index: 2;
}

.step-number.completed {
    background: #28a745;
    color: white;
}

.step-number.current {
    background: #ffc107;
    color: white;
    animation: pulse 2s infinite;
}

.step-content {
    flex: 1;
    padding-top: 2px;
}

.step-title {
    font-weight: 600;
    font-size: 14px;
    color: #495057;
    margin-bottom: 2px;
}

.step-desc {
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

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
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
    
    .mini-progress {
        flex-wrap: wrap;
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
    
    // Progress animation
    $('.step-icon, .step-number, .mini-step').each(function(index) {
        $(this).delay(index * 100).fadeIn(300);
    });
});
</script>