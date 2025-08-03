<?php
/**
 * View Form Ajukan Ulang Seminar Skripsi
 * 
 * File: application/views/mahasiswa/seminar_skripsi/resubmit.php
 * 
 * FEATURES:
 * ✅ Form pengajuan ulang dengan keterangan perbaikan
 * ✅ Display rejection reason dari dosen/kaprodi
 * ✅ Upload file skripsi baru (opsional)
 * ✅ Validasi form yang comprehensive
 * ✅ Design responsive dan user-friendly
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-redo mr-2"></i>
            Ajukan Ulang Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Ajukan Ulang</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Form Card -->
            <div class="card shadow">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>
                        Form Pengajuan Ulang Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Header Info -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="alert alert-info border-left-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Informasi Pengajuan Ulang
                                </h6>
                                <p class="mb-0">
                                    Lakukan perbaikan sesuai dengan komentar dari dosen pembimbing atau kaprodi, 
                                    kemudian ajukan ulang seminar skripsi Anda.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-redo fa-4x text-info mb-2"></i>
                                <h6 class="text-muted">Pengajuan ke-<?= (!empty($seminar->resubmission_count) ? $seminar->resubmission_count + 1 : 2) ?></h6>
                            </div>
                        </div>
                    </div>

                    <!-- Seminar Info -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-book mr-2"></i>
                                Informasi Seminar Skripsi
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item mb-2">
                                        <span class="text-muted">Judul:</span>
                                        <strong><?= $seminar->judul_skripsi ?></strong>
                                    </div>
                                    <div class="info-item mb-2">
                                        <span class="text-muted">Pengajuan Terakhir:</span>
                                        <strong><?= date('d/m/Y H:i', strtotime($seminar->updated_at)) ?> WIB</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-2">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge badge-danger">Ditolak</span>
                                    </div>
                                    <div class="info-item mb-2">
                                        <span class="text-muted">File Saat Ini:</span>
                                        <?php if (!empty($seminar->file_skripsi)): ?>
                                            <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="fas fa-download mr-1"></i>Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada file</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Reason -->
                    <?php if (!empty($rejection_reason)): ?>
                    <div class="card border-left-danger mb-4">
                        <div class="card-body">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Alasan Penolakan
                            </h6>
                            <div class="alert alert-danger">
                                <div class="rejection-content">
                                    <?= nl2br(htmlspecialchars($rejection_reason)) ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Pastikan untuk memperbaiki semua poin yang disebutkan di atas sebelum mengajukan ulang.
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Resubmission Form -->
                    <?= form_open_multipart($form_action, ['id' => 'resubmit-form']) ?>
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Keterangan Perbaikan -->
                                <div class="form-group">
                                    <label for="keterangan_mahasiswa" class="font-weight-bold text-primary">
                                        <i class="fas fa-edit mr-2"></i>
                                        Keterangan Perbaikan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="keterangan_mahasiswa" 
                                              name="keterangan_mahasiswa" 
                                              rows="6" 
                                              placeholder="Jelaskan secara detail perbaikan yang telah Anda lakukan berdasarkan komentar dosen/kaprodi..."
                                              required><?= set_value('keterangan_mahasiswa') ?></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Jelaskan dengan detail perbaikan apa saja yang telah dilakukan pada skripsi Anda.
                                        <strong>Maksimal 1000 karakter.</strong>
                                    </small>
                                    <?= form_error('keterangan_mahasiswa', '<div class="text-danger small mt-1">', '</div>') ?>
                                </div>

                                <!-- File Upload -->
                                <div class="form-group">
                                    <label for="file_skripsi" class="font-weight-bold text-secondary">
                                        <i class="fas fa-upload mr-2"></i>
                                        Upload File Skripsi Baru (Opsional)
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               class="custom-file-input" 
                                               id="file_skripsi" 
                                               name="file_skripsi" 
                                               accept=".pdf,.doc,.docx">
                                        <label class="custom-file-label" for="file_skripsi">Pilih file...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-file-pdf mr-1"></i>
                                        Format yang diterima: PDF, DOC, DOCX. Maksimal 5MB.
                                        <br>
                                        <strong>Kosongkan jika tidak ingin mengganti file yang sudah ada.</strong>
                                    </small>
                                </div>

                                <!-- Guidelines -->
                                <div class="alert alert-warning">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Panduan Pengajuan Ulang
                                    </h6>
                                    <ul class="mb-0">
                                        <li>Pastikan semua komentar dari dosen/kaprodi sudah diperbaiki</li>
                                        <li>Periksa kembali format penulisan, tata bahasa, dan referensi</li>
                                        <li>Jika upload file baru, pastikan file sudah final dan sesuai template</li>
                                        <li>Jelaskan dengan detail perbaikan yang telah dilakukan</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <!-- Progress Card -->
                                <div class="card border-left-info mb-4">
                                    <div class="card-body">
                                        <h6 class="text-info mb-3">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            Progress Workflow
                                        </h6>
                                        <div class="workflow-steps">
                                            <div class="step completed">
                                                <i class="fas fa-check-circle text-success mr-2"></i>
                                                <span>Pengajuan Awal</span>
                                            </div>
                                            <div class="step completed">
                                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                                <span>Review Ditolak</span>
                                            </div>
                                            <div class="step active">
                                                <i class="fas fa-clock text-warning mr-2"></i>
                                                <span class="font-weight-bold">Pengajuan Ulang</span>
                                            </div>
                                            <div class="step">
                                                <i class="fas fa-circle text-muted mr-2"></i>
                                                <span>Review Ulang</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tips Card -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-secondary mb-3">
                                            <i class="fas fa-lightbulb mr-2"></i>
                                            Tips Perbaikan
                                        </h6>
                                        <ul class="small text-muted mb-0">
                                            <li>Baca kembali feedback dengan teliti</li>
                                            <li>Konsultasi dengan dosen pembimbing</li>
                                            <li>Perbaiki satu per satu poin yang disebutkan</li>
                                            <li>Periksa kembali tata tulis dan referensi</li>
                                            <li>Pastikan argumen dan analisis diperkuat</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-4 border-top">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Batal
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Ajukan Ulang Seminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.workflow-steps .step {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px 0;
}

.workflow-steps .step.active {
    background-color: #fff3cd;
    border-radius: 5px;
    padding: 8px 10px;
}

.workflow-steps .step.completed {
    opacity: 0.7;
}

.rejection-content {
    white-space: pre-line;
    line-height: 1.6;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}

.custom-file-label::after {
    content: "Browse";
}

.form-actions {
    background-color: #f8f9fa;
    margin: 0 -1.25rem -1.25rem -1.25rem;
    padding: 1.25rem;
}

@media (max-width: 768px) {
    .form-actions .text-right {
        text-align: left !important;
        margin-top: 10px;
    }
}
</style>

<script>
$(document).ready(function() {
    // File input handling
    $('#file_skripsi').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih file...');
        
        // File size validation (5MB)
        if (this.files[0] && this.files[0].size > 5242880) {
            alert('File terlalu besar! Maksimal 5MB.');
            $(this).val('');
            $(this).next('.custom-file-label').html('Pilih file...');
        }
    });
    
    // Character counter for textarea
    $('#keterangan_mahasiswa').on('input', function() {
        const current = $(this).val().length;
        const max = 1000;
        const remaining = max - current;
        
        // Add counter if not exists
        if (!$(this).siblings('.char-counter').length) {
            $(this).after('<small class="char-counter form-text text-muted"></small>');
        }
        
        $(this).siblings('.char-counter').html(
            `<i class="fas fa-keyboard mr-1"></i>${current}/${max} karakter ${remaining >= 0 ? '' : '(melebihi batas!)'}`
        ).toggleClass('text-danger', remaining < 0);
        
        // Prevent typing if over limit
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, max));
        }
    });
    
    // Form validation
    $('#resubmit-form').on('submit', function(e) {
        const keterangan = $('#keterangan_mahasiswa').val().trim();
        
        if (keterangan.length < 50) {
            e.preventDefault();
            alert('Keterangan perbaikan terlalu singkat! Minimal 50 karakter.');
            $('#keterangan_mahasiswa').focus();
            return false;
        }
        
        if (!confirm('Yakin ingin mengajukan ulang seminar skripsi?\n\nPastikan semua perbaikan sudah dilakukan sesuai komentar dosen/kaprodi.')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $('#submit-btn').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...'
        );
        
        return true;
    });
    
    // Auto-save draft (optional enhancement)
    let autoSaveTimer;
    $('#keterangan_mahasiswa').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Could implement auto-save to localStorage here
            console.log('Auto-saving draft...');
        }, 2000);
    });
});
</script>