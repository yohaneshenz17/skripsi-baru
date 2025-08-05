<?php
/**
 * ====================================================================
 * application/views/staf/publikasi/input_repository.php - SCRIPT LENGKAP
 * ====================================================================
 * 
 * PERBAIKAN:
 * - Tombol Test dan Simpan Link Repository berfungsi
 * - Pre-populate form dengan data yang sudah ada
 * - Form validation yang proper
 * - Controller integration yang benar
 */
?>

<?php
/**
 * View Input Repository untuk Staf
 * File: application/views/staf/publikasi/input_repository.php
 * 
 * FEATURES:
 * - Form input link repository perpustakaan
 * - Validation dan preview
 * - Panduan untuk staf
 * - Error handling yang baik
 */

// Start output buffering
ob_start();
?>

<!-- Breadcrumb Navigation -->
<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('staf/publikasi') ?>" class="text-primary">
                        <i class="fas fa-upload mr-1"></i>Publikasi
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('staf/publikasi/detail/' . (isset($publikasi->id) ? $publikasi->id : '')) ?>" class="text-primary">
                        Detail
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <?= empty($publikasi->link_repository) ? 'Input Repository' : 'Edit Repository' ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h3 mb-0">
                    <i class="fas fa-<?= empty($publikasi->link_repository) ? 'plus' : 'edit' ?> text-warning mr-2"></i>
                    <?= empty($publikasi->link_repository) ? 'Input Link Repository' : 'Edit Link Repository' ?>
                </h2>
                <p class="text-muted mb-0">
                    <?= empty($publikasi->link_repository) ? 'Masukkan' : 'Perbarui' ?> link repository perpustakaan digital untuk publikasi tugas akhir
                </p>
            </div>
            <a href="<?= base_url('staf/publikasi/detail/' . (isset($publikasi->id) ? $publikasi->id : '')) ?>" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> 
        <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Validation Error!</strong>
        <?= validation_errors() ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (isset($publikasi) && $publikasi): ?>

<div class="row">
    <!-- Left Column - Form -->
    <div class="col-lg-8">
        <!-- Informasi Mahasiswa -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Informasi Mahasiswa
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Nama Mahasiswa</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_mahasiswa) ? htmlspecialchars($publikasi->nama_mahasiswa) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">NIM</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nim) ? htmlspecialchars($publikasi->nim) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Program Studi</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_prodi) ? htmlspecialchars($publikasi->nama_prodi) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Dosen Pembimbing</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_dosen) ? htmlspecialchars($publikasi->nama_dosen) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-control-label text-sm font-weight-bold">Judul Tugas Akhir</label>
                    <p class="form-control-plaintext">
                        <?= isset($publikasi->judul) ? htmlspecialchars($publikasi->judul) : 'N/A' ?>
                    </p>
                </div>
                
                <!-- Status Link Repository Saat Ini -->
                <?php if (!empty($publikasi->link_repository)): ?>
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle mr-1"></i>
                        Repository Saat Ini
                    </h6>
                    <p class="mb-0">
                        <a href="<?= htmlspecialchars($publikasi->link_repository) ?>" 
                           target="_blank" 
                           class="text-primary">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            <?= htmlspecialchars($publikasi->link_repository) ?>
                        </a>
                    </p>
                    <small class="text-muted">Anda dapat memperbarui link repository di bawah ini.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form Input Repository -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-cloud-upload-alt text-warning mr-2"></i>
                    Form <?= empty($publikasi->link_repository) ? 'Input' : 'Edit' ?> Repository
                </h4>
            </div>
            <div class="card-body">
                <!-- ===== FORM DENGAN ACTION DAN METHOD YANG BENAR ===== -->
                <?= form_open('staf/publikasi/input_repository/' . $publikasi->id, [
                    'id' => 'form-repository',
                    'class' => 'needs-validation',
                    'method' => 'POST',
                    'novalidate' => true
                ]) ?>
                
                <div class="form-group">
                    <label for="link_repository" class="form-control-label">
                        Link Repository Perpustakaan 
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-link"></i>
                            </span>
                        </div>
                        
                        <!-- ===== PRE-POPULATE DENGAN DATA YANG SUDAH ADA ===== -->
                        <input type="url" 
                               class="form-control" 
                               id="link_repository" 
                               name="link_repository" 
                               placeholder="https://repository.stkyakobus.ac.id/handle/..." 
                               value="<?= set_value('link_repository', isset($publikasi->link_repository) ? $publikasi->link_repository : '') ?>"
                               required
                               autocomplete="off">
                               
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-info" id="btn-test-link">
                                <i class="fas fa-external-link-alt"></i> Test
                            </button>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        Silakan masukkan URL repository yang valid.
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Masukkan URL lengkap ke repository perpustakaan digital kampus. 
                        Contoh: https://repository.stkyakobus.ac.id/handle/123456789/123
                    </small>
                </div>
                
                <!-- URL Preview -->
                <div id="url-preview" class="form-group" style="display: none;">
                    <label class="form-control-label text-sm font-weight-bold">Preview Link</label>
                    <div class="card bg-light">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-globe text-primary mr-2"></i>
                                <a href="#" id="preview-link" target="_blank" class="text-primary">
                                    <span id="preview-text"></span>
                                </a>
                                <span id="link-status" class="ml-auto"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="catatan_staf" class="form-control-label">
                        Catatan Tambahan 
                        <small class="text-muted">(Opsional)</small>
                    </label>
                    <textarea class="form-control" 
                              id="catatan_staf" 
                              name="catatan_staf" 
                              rows="3"
                              placeholder="Catatan atau keterangan tambahan untuk mahasiswa..."><?= set_value('catatan_staf', isset($publikasi->catatan_staf) ? $publikasi->catatan_staf : '') ?></textarea>
                    <small class="form-text text-muted">
                        Catatan ini akan disimpan dalam sistem untuk referensi mahasiswa.
                    </small>
                </div>
                
                <!-- ===== CHECKBOX KONFIRMASI DAN SUBMIT BUTTON ===== -->
                <div class="form-group">
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="confirm-check" required>
                        <label class="custom-control-label" for="confirm-check">
                            Saya sudah memverifikasi bahwa link repository dapat diakses dan berisi file yang sesuai
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                <?= empty($publikasi->link_repository) ? 'Link repository akan disimpan' : 'Link repository akan diperbarui' ?> dalam sistem
                            </small>
                        </div>
                        
                        <!-- ===== TOMBOL SUBMIT YANG DIPERBAIKI ===== -->
                        <button type="submit" class="btn btn-warning btn-lg" id="btn-submit">
                            <i class="fas fa-save mr-2"></i>
                            <?= empty($publikasi->link_repository) ? 'Simpan' : 'Update' ?> Link Repository
                        </button>
                    </div>
                </div>
                
                <?= form_close() ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Panduan -->
    <div class="col-lg-4">
        <!-- Status Current -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary">
                <h5 class="text-white mb-0">
                    <i class="fas fa-info mr-2"></i>
                    Status Repository
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($publikasi->link_repository)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-plus-circle fa-3x text-warning mb-2"></i>
                        <h6 class="text-warning">Belum Ada Repository</h6>
                        <p class="text-muted small mb-0">
                            Silakan input link repository perpustakaan digital untuk publikasi tugas akhir mahasiswa.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-edit fa-3x text-info mb-2"></i>
                        <h6 class="text-info">Mode Edit Repository</h6>
                        <p class="text-muted small mb-0">
                            Anda sedang mengedit link repository yang sudah ada. 
                            Link baru akan menggantikan yang lama.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Panduan Input Repository -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-info">
                <h5 class="text-white mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Panduan <?= empty($publikasi->link_repository) ? 'Input' : 'Edit' ?> Repository
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">1. Verifikasi Link</h6>
                            <p class="timeline-text">Pastikan link repository dapat diakses dan menuju ke halaman yang benar.</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">2. Check Format</h6>
                            <p class="timeline-text">Link harus menggunakan format https:// dan menuju domain resmi kampus.</p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">3. Verifikasi Konten</h6>
                            <p class="timeline-text">Pastikan repository berisi file PDF skripsi lengkap dan metadata yang sesuai.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips & Troubleshooting -->
        <div class="card">
            <div class="card-header bg-gradient-warning">
                <h5 class="text-white mb-0">
                    <i class="fas fa-tools mr-2"></i>
                    Tips & Troubleshooting
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark"><i class="fas fa-check-circle text-success mr-1"></i> Format URL yang Benar:</h6>
                    <small class="text-muted">
                        • https://repository.stkyakobus.ac.id/handle/...<br>
                        • https://repo.stkyakobus.ac.id/...<br>
                        • Pastikan protokol https://
                    </small>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-dark"><i class="fas fa-times-circle text-danger mr-1"></i> Hindari:</h6>
                    <small class="text-muted">
                        • Link yang tidak lengkap<br>
                        • URL temporary atau redirect<br>
                        • Link yang memerlukan login khusus
                    </small>
                </div>
                
                <div class="alert alert-info p-2">
                    <small>
                        <strong>💡 Pro Tip:</strong> 
                        Gunakan tombol "Test" untuk memverifikasi link sebelum menyimpan.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Error State -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                <h3>Data Tidak Ditemukan</h3>
                <p class="text-muted">Data publikasi yang Anda cari tidak ditemukan.</p>
                <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
echo $content;
?>

<!-- CSS untuk Timeline dan Form -->
<style>
.form-control-plaintext {
    padding-left: 0;
    padding-right: 0;
    border: none;
    background: none;
    font-weight: 500;
    color: #525f7f;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3ebf0;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e3ebf0;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: #32325d;
}

.timeline-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
    line-height: 1.4;
}

.input-group-text {
    background-color: #f6f9fc;
    border-color: #e3ebf0;
}

.was-validated .form-control:valid {
    border-color: #28a745;
    background-image: none;
}

.was-validated .form-control:invalid {
    border-color: #dc3545;
    background-image: none;
}

#url-preview .card {
    border: 1px dashed #dee2e6;
    transition: all 0.3s ease;
}

#url-preview .card:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

/* Loading state */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -17px;
        width: 10px;
        height: 10px;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-lg {
        width: 100%;
    }
}
</style>

<!-- ===== JAVASCRIPT YANG DIPERBAIKI ===== -->
<script>
$(document).ready(function() {
    const form = $('#form-repository');
    const linkInput = $('#link_repository');
    const previewDiv = $('#url-preview');
    const previewLink = $('#preview-link');
    const previewText = $('#preview-text');
    const linkStatus = $('#link-status');
    const confirmCheck = $('#confirm-check');
    const submitBtn = $('#btn-submit');
    const testBtn = $('#btn-test-link');

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // ===== INITIALIZE FORM STATE =====
    function initializeForm() {
        // Trigger validation on page load if there's existing value
        if (linkInput.val()) {
            linkInput.trigger('input');
        }
        
        // Update submit button state
        updateSubmitButton();
    }

    // ===== REAL-TIME VALIDATION =====
    linkInput.on('input', function() {
        const url = $(this).val().trim();
        
        if (url && isValidUrl(url)) {
            showPreview(url);
            $(this).removeClass('is-invalid').addClass('is-valid');
            linkStatus.html('<span class="badge badge-success"><i class="fas fa-check"></i> Valid URL</span>');
        } else {
            hidePreview();
            if (url) {
                $(this).removeClass('is-valid').addClass('is-invalid');
                linkStatus.html('<span class="badge badge-danger"><i class="fas fa-times"></i> Invalid URL</span>');
            } else {
                $(this).removeClass('is-valid is-invalid');
                linkStatus.empty();
            }
        }
        
        updateSubmitButton();
    });

    // ===== TEST LINK BUTTON - DIPERBAIKI =====
    testBtn.on('click', function(e) {
        e.preventDefault();
        
        const url = linkInput.val().trim();
        
        if (!url) {
            alert('Silakan masukkan URL terlebih dahulu.');
            linkInput.focus();
            return;
        }
        
        if (!isValidUrl(url)) {
            alert('Format URL tidak valid. Pastikan dimulai dengan http:// atau https://');
            linkInput.focus();
            return;
        }
        
        // Show loading state
        const originalText = testBtn.html();
        testBtn.html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        testBtn.prop('disabled', true);
        
        // Test link accessibility (simulate server check)
        setTimeout(function() {
            // Reset button
            testBtn.html(originalText);
            testBtn.prop('disabled', false);
            
            // Open link in new tab for manual verification
            const testWindow = window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            
            if (testWindow) {
                // Update status
                linkStatus.html('<span class="badge badge-info"><i class="fas fa-external-link-alt"></i> Opened for Testing</span>');
                
                // Show success message
                setTimeout(function() {
                    if (confirm('Apakah link repository dapat diakses dengan baik?\n\nKlik OK jika ya, Cancel jika ada masalah.')) {
                        linkStatus.html('<span class="badge badge-success"><i class="fas fa-check-double"></i> Verified</span>');
                        confirmCheck.prop('checked', true);
                        updateSubmitButton();
                    } else {
                        linkStatus.html('<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Needs Review</span>');
                    }
                }, 2000);
            } else {
                linkStatus.html('<span class="badge badge-danger"><i class="fas fa-times"></i> Popup Blocked</span>');
                alert('Popup diblokir. Silakan copy URL dan buka di tab baru secara manual:\n\n' + url);
            }
        }, 1000);
    });

    // ===== CONFIRM CHECKBOX HANDLER =====
    confirmCheck.on('change', function() {
        updateSubmitButton();
    });

    // ===== FORM SUBMISSION - DIPERBAIKI =====
    form.on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        const url = linkInput.val().trim();
        const isEdit = '<?= !empty($publikasi->link_repository) ? "true" : "false" ?>' === 'true';
        const action = isEdit ? 'memperbarui' : 'menyimpan';
        
        // Show confirmation
        const confirmText = `Yakin ingin ${action} link repository ini?\n\nURL: ${url}\n\nPastikan link sudah diverifikasi dan dapat diakses dengan baik.`;
        
        if (confirm(confirmText)) {
            // Show loading
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            submitBtn.prop('disabled', true);
            
            // Submit form normally (will be handled by CodeIgniter)
            setTimeout(() => {
                // Create a temporary form with all data to ensure proper submission
                const tempForm = $('<form>', {
                    'method': 'POST',
                    'action': form.attr('action')
                });
                
                // Add CSRF token if exists
                if ($('input[name="csrf_token"]').length) {
                    tempForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'csrf_token',
                        'value': $('input[name="csrf_token"]').val()
                    }));
                }
                
                // Add form data
                tempForm.append($('<input>', {
                    'type': 'hidden',
                    'name': 'link_repository',
                    'value': linkInput.val()
                }));
                
                tempForm.append($('<input>', {
                    'type': 'hidden',
                    'name': 'catatan_staf',
                    'value': $('#catatan_staf').val()
                }));
                
                // Submit temporary form
                tempForm.appendTo('body').submit();
            }, 500);
        } else {
            // Reset button if user cancels
            updateSubmitButton();
        }
    });

    // ===== HELPER FUNCTIONS =====
    function isValidUrl(string) {
        try {
            const url = new URL(string);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (_) {
            return false;
        }
    }

    function showPreview(url) {
        previewLink.attr('href', url);
        previewText.text(url);
        previewDiv.slideDown('fast');
    }

    function hidePreview() {
        previewDiv.slideUp('fast');
    }

    function updateSubmitButton() {
        const urlValid = linkInput.hasClass('is-valid') && linkInput.val().trim() !== '';
        const confirmed = confirmCheck.is(':checked');
        
        if (urlValid && confirmed) {
            submitBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-warning');
        } else {
            submitBtn.prop('disabled', true).removeClass('btn-warning').addClass('btn-secondary');
        }
    }

    function validateForm() {
        let isValid = true;
        
        // Validate URL
        const url = linkInput.val().trim();
        if (!url || !isValidUrl(url)) {
            linkInput.addClass('is-invalid');
            linkInput.focus();
            alert('Silakan masukkan URL repository yang valid.');
            isValid = false;
        }
        
        // Validate confirmation
        if (!confirmCheck.is(':checked')) {
            confirmCheck.focus();
            alert('Silakan centang konfirmasi bahwa Anda sudah memverifikasi link repository.');
            isValid = false;
        }
        
        return isValid;
    }

    // ===== ENTER KEY HANDLER =====
    linkInput.on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            testBtn.click();
        }
    });

    // ===== INITIALIZE =====
    initializeForm();
    
    console.log('Input Repository Form Loaded Successfully');
    console.log('Form Action:', form.attr('action'));
    console.log('Initial URL:', linkInput.val());
});

// ===== PREVENT ACCIDENTAL PAGE LEAVE =====
window.addEventListener('beforeunload', function(e) {
    const url = $('#link_repository').val().trim();
    const originalUrl = '<?= isset($publikasi->link_repository) ? addslashes($publikasi->link_repository) : "" ?>';
    
    // Only warn if there are unsaved changes
    if (url && url !== originalUrl) {
        e.preventDefault();
        e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
        return e.returnValue;
    }
});
</script>

<?php
/**
 * ====================================================================
 * CATATAN UNTUK CONTROLLER
 * ====================================================================
 * 
 * Pastikan controller memiliki method berikut:
 * 
 * public function input_repository($id = null) {
 *     // GET: Tampilkan form
 *     if ($this->input->method() === 'get') {
 *         $data['publikasi'] = $this->get_publikasi_by_id($id);
 *         $this->load->view('staf/publikasi/input_repository', $data);
 *     }
 *     
 *     // POST: Proses simpan
 *     if ($this->input->method() === 'post') {
 *         $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url');
 *         
 *         if ($this->form_validation->run()) {
 *             $update_data = [
 *                 'link_repository' => $this->input->post('link_repository'),
 *                 'catatan_staf' => $this->input->post('catatan_staf'),
 *                 'updated_at' => date('Y-m-d H:i:s')
 *             ];
 *             
 *             if ($this->update_publikasi($id, $update_data)) {
 *                 $this->session->set_flashdata('success', 'Link repository berhasil disimpan.');
 *             } else {
 *                 $this->session->set_flashdata('error', 'Gagal menyimpan link repository.');
 *             }
 *         }
 *         
 *         redirect('staf/publikasi/detail/' . $id);
 *     }
 * }
 */
?>