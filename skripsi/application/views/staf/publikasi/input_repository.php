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
                <li class="breadcrumb-item active">Input Repository</li>
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
                    <i class="fas fa-link text-warning mr-2"></i>
                    Input Link Repository
                </h2>
                <p class="text-muted mb-0">Masukkan link repository perpustakaan digital untuk publikasi tugas akhir</p>
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
            </div>
        </div>

        <!-- Form Input Repository -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-cloud-upload-alt text-warning mr-2"></i>
                    Form Input Repository
                </h4>
            </div>
            <div class="card-body">
                <?= form_open('staf/publikasi/input_repository/' . $publikasi->id, [
                    'id' => 'form-repository',
                    'class' => 'needs-validation',
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
                        <input type="url" 
                               class="form-control" 
                               id="link_repository" 
                               name="link_repository" 
                               placeholder="https://repository.stkyakobus.ac.id/handle/..." 
                               value="<?= set_value('link_repository') ?>"
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
                              placeholder="Catatan atau keterangan tambahan untuk mahasiswa..."><?= set_value('catatan_staf') ?></textarea>
                    <small class="form-text text-muted">
                        Catatan ini akan disimpan dalam sistem untuk referensi mahasiswa.
                    </small>
                </div>
                
                <div class="form-group mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm-check" required>
                            <label class="custom-control-label" for="confirm-check">
                                Saya sudah memverifikasi bahwa link repository dapat diakses dan berisi file yang sesuai
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning" id="btn-submit" disabled>
                            <i class="fas fa-save mr-2"></i>
                            Simpan Link Repository
                        </button>
                    </div>
                </div>
                
                <?= form_close() ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Panduan -->
    <div class="col-lg-4">
        <!-- Panduan Input Repository -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-info">
                <h5 class="text-white mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Panduan Input Repository
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

@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -17px;
        width: 10px;
        height: 10px;
    }
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<!-- JavaScript untuk Form Validation dan Interaktivitas -->
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

    // Link input change handler
    linkInput.on('input', function() {
        const url = $(this).val().trim();
        
        if (url && isValidUrl(url)) {
            showPreview(url);
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            hidePreview();
            if (url) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        }
        
        updateSubmitButton();
    });

    // Test link button
    testBtn.on('click', function() {
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
        
        // Test link accessibility
        testBtn.html('<i class="fas fa-spinner fa-spin"></i> Testing...');
        testBtn.prop('disabled', true);
        
        // Simulate test (in real implementation, you might want to do server-side check)
        setTimeout(function() {
            testBtn.html('<i class="fas fa-external-link-alt"></i> Test');
            testBtn.prop('disabled', false);
            
            // Open link in new tab for manual verification
            window.open(url, '_blank');
            
            // Update status
            linkStatus.html('<span class="badge badge-success"><i class="fas fa-check"></i> Tested</span>');
        }, 1500);
    });

    // Confirm checkbox handler
    confirmCheck.on('change', function() {
        updateSubmitButton();
    });

    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        // Show confirmation
        if (confirm('Yakin ingin menyimpan link repository ini?\n\nPastikan link sudah diverifikasi dan dapat diakses dengan baik.')) {
            // Show loading
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');
            submitBtn.prop('disabled', true);
            
            // Submit form
            this.submit();
        }
    });

    // Helper functions
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
        previewDiv.show('fast');
    }

    function hidePreview() {
        previewDiv.hide('fast');
        linkStatus.empty();
    }

    function updateSubmitButton() {
        const urlValid = linkInput.hasClass('is-valid');
        const confirmed = confirmCheck.is(':checked');
        
        submitBtn.prop('disabled', !(urlValid && confirmed));
    }

    function validateForm() {
        let isValid = true;
        
        // Validate URL
        const url = linkInput.val().trim();
        if (!url || !isValidUrl(url)) {
            linkInput.addClass('is-invalid');
            isValid = false;
        }
        
        // Validate confirmation
        if (!confirmCheck.is(':checked')) {
            alert('Silakan centang konfirmasi bahwa Anda sudah memverifikasi link repository.');
            isValid = false;
        }
        
        return isValid;
    }

    // Initialize
    linkInput.trigger('input');
    
    console.log('Input Repository Form Loaded');
});

// Prevent form submission on Enter key in URL field
$(document).on('keypress', '#link_repository', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        $('#btn-test-link').click();
    }
});
</script>