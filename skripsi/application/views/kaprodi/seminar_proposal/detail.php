<?php
/**
 * FINAL FIXED - Kaprodi Seminar Proposal Detail View (NO HEADER CONFLICT)
 * 
 * PERBAIKAN FINAL:
 * 1. REMOVE SEMUA header dan breadcrumb - template sudah handle
 * 2. Langsung start dengan content utama
 * 3. No wrapper tambahan yang konflik dengan template
 * 4. Clean structure yang align dengan container template
 * 
 * File: application/views/kaprodi/seminar_proposal/detail.php
 */

// Start output buffering untuk content
ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- ======================================
     MAIN CONTENT - NO HEADER CONFLICT
     ====================================== -->
<div class="row">
    <!-- Left Column: Detail Proposal -->
    <div class="col-xl-8 col-lg-7">
        <!-- Proposal Details Card -->
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Detail Proposal Seminar
                        </h3>
                        <p class="text-white-50 mt-1 mb-0">
                            <?= htmlspecialchars($seminar->nama_mahasiswa) ?> (<?= htmlspecialchars($seminar->nim) ?>)
                        </p>
                    </div>
                    <div class="col-auto">
                        <!-- Status Badge -->
                        <?php if($seminar->status_kaprodi === 'approved'): ?>
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check mr-1"></i>Disetujui
                            </span>
                        <?php elseif($seminar->status_kaprodi === 'rejected'): ?>
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-times mr-1"></i>Ditolak
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock mr-1"></i>Menunggu Review
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Mahasiswa Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">MAHASISWA</label>
                            <p class="mb-1 font-weight-600"><?= htmlspecialchars($seminar->nama_mahasiswa) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($seminar->nim) ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">DOSEN PEMBIMBING</label>
                            <p class="mb-1 font-weight-600">
                                <?= isset($seminar->nama_pembimbing) ? htmlspecialchars($seminar->nama_pembimbing) : 'Belum ditetapkan' ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Judul Proposal -->
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">JUDUL PROPOSAL</label>
                    <div class="border rounded p-3 bg-light">
                        <h6 class="mb-0 text-dark"><?= htmlspecialchars($seminar->judul) ?></h6>
                    </div>
                </div>
                
                <!-- Tanggal dan Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">TANGGAL PENGAJUAN</label>
                            <p class="mb-0"><?= date('d F Y, H:i', strtotime($seminar->created_at)) ?> WIT</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">STATUS PEMBIMBING</label>
                            <p class="mb-0">
                                <?php if($seminar->status_pembimbing === 'approved'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check mr-1"></i>Disetujui
                                    </span>
                                <?php elseif($seminar->status_pembimbing === 'rejected'): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times mr-1"></i>Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock mr-1"></i>Menunggu
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- File Proposal -->
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">FILE PROPOSAL</label>
                    <?php if(!empty($seminar->file_proposal)): ?>
                        <div class="d-flex align-items-center p-3 border rounded bg-light">
                            <div class="mr-3">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1 font-weight-600">Proposal_<?= htmlspecialchars($seminar->nama_mahasiswa) ?>.pdf</p>
                                <small class="text-muted">File proposal untuk seminar</small>
                            </div>
                            <div>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   class="btn btn-outline-primary btn-sm mr-2" target="_blank">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </a>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   class="btn btn-outline-success btn-sm" download>
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            File proposal tidak tersedia
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Keterangan Mahasiswa -->
                <?php if(!empty($seminar->keterangan_mahasiswa)): ?>
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">KETERANGAN MAHASISWA</label>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Komentar Pembimbing -->
                <?php if(!empty($seminar->komentar_pembimbing)): ?>
                <div class="form-group mb-0">
                    <label class="text-sm font-weight-bold text-muted mb-2">KOMENTAR PEMBIMBING</label>
                    <div class="alert alert-primary mb-0">
                        <i class="fas fa-comment-dots mr-2"></i>
                        <?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Jurnal Bimbingan Summary -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-book mr-2"></i>
                    Status Jurnal Bimbingan
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="text-center p-3">
                            <div class="display-4 font-weight-bold text-primary">
                                <?= $jurnal_bimbingan_count ?>
                            </div>
                            <p class="text-muted mb-0">Total Pertemuan</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center p-3">
                            <?php if($is_jurnal_sufficient): ?>
                                <div class="display-4 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <p class="text-success mb-0 font-weight-600">Syarat Terpenuhi</p>
                            <?php else: ?>
                                <div class="display-4 text-warning">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <p class="text-warning mb-0 font-weight-600">Butuh <?= (10 - $jurnal_bimbingan_count) ?> lagi</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if($is_jurnal_sufficient): ?>
                    <div class="alert alert-success text-center mb-0">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Mahasiswa telah memenuhi syarat minimal 8 jurnal bimbingan
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        Mahasiswa belum memenuhi syarat minimal 8 jurnal bimbingan
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Form Validasi -->
    <div class="col-xl-4 col-lg-5">
        <?php if($seminar->status === 'review_kaprodi' && $seminar->status_pembimbing === 'approved'): ?>
        <!-- FORM VALIDASI PLAGIARISME -->
        <div class="card shadow">
            <div class="card-header bg-gradient-warning">
                <h5 class="text-white mb-0">
                    <i class="fas fa-search mr-2"></i>
                    Validasi Plagiarisme
                </h5>
            </div>
            <div class="card-body">
                <?= form_open_multipart('kaprodi/seminar_proposal/validasi_plagiarisme', [
                    'id' => 'form-validasi'
                ]) ?>
                
                    <?= form_hidden('seminar_id', $seminar->id) ?>
                    
                    <!-- File Upload -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-file-upload mr-1"></i>
                            File Hasil Turnitin <span class="text-danger">*</span>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="file_turnitin" 
                                   id="file_turnitin" accept=".pdf" required>
                            <label class="custom-file-label" for="file_turnitin">Pilih file PDF...</label>
                        </div>
                        <small class="text-muted">Format: PDF, Maksimal: 5MB</small>
                    </div>
                    
                    <!-- Persentase Plagiarisme -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-percentage mr-1"></i>
                            Persentase Plagiarisme <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="plagiarism_percentage" 
                                   id="plagiarism_percentage" min="0" max="100" step="0.1" 
                                   placeholder="0.0" required>
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Batas maksimal: <strong class="text-danger">30%</strong>
                        </small>
                        
                        <!-- Indicator -->
                        <div class="mt-2" id="plagiarism-indicator" style="display: none;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="progress-fill" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-center d-block mt-1" id="indicator-text"></small>
                        </div>
                    </div>
                    
                    <!-- Keputusan -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-gavel mr-1"></i>
                            Keputusan <span class="text-danger">*</span>
                        </label>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="approve" name="keputusan" value="approve" 
                                   class="custom-control-input" required>
                            <label class="custom-control-label" for="approve">
                                <span class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Setujui Seminar
                                </span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="reject" name="keputusan" value="reject" 
                                   class="custom-control-input" required>
                            <label class="custom-control-label" for="reject">
                                <span class="text-danger">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Tolak & Minta Perbaikan
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Komentar -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-comment mr-1"></i>
                            Komentar & Catatan
                        </label>
                        <textarea class="form-control" name="komentar_kaprodi" rows="4" 
                                  placeholder="Berikan komentar atau catatan..."></textarea>
                        <small class="text-muted">Wajib diisi jika menolak proposal</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block" id="btn-submit">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Simpan Validasi
                    </button>
                    
                <?= form_close() ?>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Status Information -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    Status Pengajuan
                </h5>
            </div>
            <div class="card-body">
                <?php if($seminar->status_pembimbing !== 'approved'): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-clock mr-2"></i>
                        <strong>Menunggu Review Pembimbing</strong>
                        <br>Pengajuan belum dapat diproses karena masih menunggu persetujuan dari dosen pembimbing.
                    </div>
                <?php elseif($seminar->status_kaprodi === 'approved'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Sudah Disetujui</strong>
                        <br>Seminar proposal telah disetujui.
                        
                        <?php if(!empty($seminar->plagiarism_percentage)): ?>
                            <hr>
                            <small><strong>Plagiarisme:</strong> <?= $seminar->plagiarism_percentage ?>%</small>
                        <?php endif; ?>
                        
                        <?php if(!empty($seminar->tanggal_review_kaprodi)): ?>
                            <br><small><strong>Tanggal review:</strong> <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) ?></small>
                        <?php endif; ?>
                    </div>
                <?php elseif($seminar->status_kaprodi === 'rejected'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-2"></i>
                        <strong>Ditolak</strong>
                        <br>Proposal memerlukan perbaikan.
                        
                        <?php if(!empty($seminar->komentar_kaprodi)): ?>
                            <hr>
                            <strong>Catatan:</strong><br>
                            <?= nl2br(htmlspecialchars($seminar->komentar_kaprodi)) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- File Turnitin jika sudah ada -->
                <?php if(!empty($seminar->file_turnitin)): ?>
                    <div class="mt-3">
                        <label class="font-weight-bold">File Turnitin</label>
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="fas fa-file-pdf fa-2x text-danger mr-3"></i>
                            <div class="flex-grow-1">
                                <p class="mb-0 font-weight-600">Turnitin_Result.pdf</p>
                            </div>
                            <a href="<?= base_url('uploads/turnitin/' . $seminar->file_turnitin) ?>" 
                               class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Guidelines -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Panduan Validasi
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-success text-white mr-3">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <strong>Plagiarisme &lt; 30%</strong><br>
                        <small class="text-muted">Dapat disetujui untuk seminar</small>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-danger text-white mr-3">
                        <i class="fas fa-times"></i>
                    </div>
                    <div>
                        <strong>Plagiarisme ≥ 30%</strong><br>
                        <small class="text-muted">Harus ditolak dan diperbaiki</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-info text-white mr-3">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div>
                        <strong>File Turnitin</strong><br>
                        <small class="text-muted">Wajib upload hasil PDF</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS untuk layout yang clean dan align dengan template -->
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.display-4 {
    font-size: 2.5rem;
}

.font-weight-600 {
    font-weight: 600;
}

.progress {
    transition: all 0.3s ease;
}

.custom-file-label::after {
    content: "Browse";
}

/* Responsive adjustments yang align dengan template */
@media (max-width: 991.98px) {
    .col-lg-5 {
        margin-top: 1.5rem;
    }
}

@media (max-width: 767.98px) {
    .card-body {
        padding: 1.25rem;
    }
}

/* Ensure proper spacing dengan template container */
.card {
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>

<?php
// Tangkap content
$content = ob_get_clean();

// Start script output buffering
ob_start();
?>

<script>
$(document).ready(function() {
    // File input handling
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
    
    // Real-time plagiarism validation
    $('#plagiarism_percentage').on('input', function() {
        const percentage = parseFloat($(this).val()) || 0;
        const indicator = $('#plagiarism-indicator');
        const progressFill = $('#progress-fill');
        const indicatorText = $('#indicator-text');
        const approveOption = $('#approve');
        const approveLabel = $('label[for="approve"]');
        
        if (percentage > 0) {
            indicator.show();
            progressFill.css('width', Math.min(percentage, 100) + '%');
            
            if (percentage < 15) {
                progressFill.removeClass().addClass('progress-bar bg-success');
                indicatorText.text('Excellent - Sangat Baik').removeClass().addClass('text-success');
            } else if (percentage < 25) {
                progressFill.removeClass().addClass('progress-bar bg-warning');
                indicatorText.text('Good - Baik').removeClass().addClass('text-warning');
            } else if (percentage < 30) {
                progressFill.removeClass().addClass('progress-bar bg-orange');
                indicatorText.text('Acceptable - Dapat Diterima').removeClass().addClass('text-warning');
            } else {
                progressFill.removeClass().addClass('progress-bar bg-danger');
                indicatorText.text('Too High - Terlalu Tinggi').removeClass().addClass('text-danger');
            }
            
            // Disable approve option if >= 30%
            if (percentage >= 30) {
                approveOption.prop('disabled', true);
                approveLabel.css('opacity', '0.5');
                $('#reject').prop('checked', true);
            } else {
                approveOption.prop('disabled', false);
                approveLabel.css('opacity', '1');
            }
        } else {
            indicator.hide();
        }
    });
    
    // Form validation
    $('#form-validasi').on('submit', function(e) {
        const percentage = parseFloat($('#plagiarism_percentage').val()) || 0;
        const decision = $('input[name="keputusan"]:checked').val();
        const comment = $('textarea[name="komentar_kaprodi"]').val().trim();
        
        // Validate comment for rejection
        if (decision === 'reject' && comment === '') {
            e.preventDefault();
            alert('Komentar wajib diisi untuk penolakan!');
            $('textarea[name="komentar_kaprodi"]').focus();
            return false;
        }
        
        // Confirm submission
        const message = decision === 'approve' ? 
            `Yakin menyetujui seminar proposal dengan tingkat plagiarisme ${percentage}%?` :
            'Yakin menolak proposal ini? Mahasiswa akan diminta melakukan perbaikan.';
            
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $('#btn-submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut();
    }, 5000);
});
</script>

<?php
// Tangkap script
$script = ob_get_clean();

// Load template kaprodi - TITLE AKAN MENJADI HEADER
$this->load->view('template/kaprodi', [
    'title' => 'Review Seminar Proposal - ' . htmlspecialchars($seminar->nama_mahasiswa),
    'content' => $content,
    'script' => $script
]);
?>