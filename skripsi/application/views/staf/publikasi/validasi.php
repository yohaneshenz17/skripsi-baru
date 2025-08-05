<?php
/**
 * View Validasi Publikasi untuk Staf
 * File: application/views/staf/publikasi/validasi.php
 * 
 * FEATURES:
 * - Form validasi final (approve/reject)
 * - Preview publikasi dan repository
 * - Checklist validasi
 * - Email notification trigger
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
                <li class="breadcrumb-item active">Validasi Final</li>
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
                    <i class="fas fa-check-double text-success mr-2"></i>
                    Validasi Final Publikasi
                </h2>
                <p class="text-muted mb-0">Review dan validasi publikasi tugas akhir mahasiswa</p>
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
    <!-- Left Column - Informasi & Form -->
    <div class="col-lg-8">
        <!-- Informasi Publikasi -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    Informasi Publikasi
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Mahasiswa</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_mahasiswa) ? htmlspecialchars($publikasi->nama_mahasiswa) : 'N/A' ?>
                                <?php if (isset($publikasi->nim)): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($publikasi->nim) ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Program Studi</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_prodi) ? htmlspecialchars($publikasi->nama_prodi) : 'N/A' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Dosen Pembimbing</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_dosen) ? htmlspecialchars($publikasi->nama_dosen) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Tanggal Pengajuan</label>
                            <p class="form-control-plaintext">
                                <?php 
                                $created_at = isset($publikasi->created_at) ? $publikasi->created_at : null;
                                echo $created_at ? date('d F Y, H:i', strtotime($created_at)) . ' WIB' : 'N/A';
                                ?>
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
                
                <div class="form-group mb-0">
                    <label class="form-control-label text-sm font-weight-bold">Link Repository</label>
                    <p class="form-control-plaintext">
                        <?php $link_repository = isset($publikasi->link_repository) ? $publikasi->link_repository : ''; ?>
                        <?php if (!empty($link_repository)): ?>
                            <a href="<?= htmlspecialchars($link_repository) ?>" 
                               target="_blank" 
                               class="text-primary d-inline-flex align-items-center">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                <?= htmlspecialchars($link_repository) ?>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-info ml-2" 
                                    id="btn-preview-repo">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                        <?php else: ?>
                            <span class="text-muted">Repository belum diinput</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Validasi -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-gavel text-success mr-2"></i>
                    Form Validasi
                </h4>
            </div>
            <div class="card-body">
                <?= form_open('staf/publikasi/validasi/' . $publikasi->id, [
                    'id' => 'form-validasi',
                    'class' => 'needs-validation',
                    'novalidate' => true
                ]) ?>
                
                <!-- Keputusan Validasi -->
                <div class="form-group">
                    <label class="form-control-label">
                        Keputusan Validasi 
                        <span class="text-danger">*</span>
                    </label>
                    <div class="mt-2">
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" 
                                   id="approved" 
                                   name="keputusan" 
                                   value="approved" 
                                   class="custom-control-input" 
                                   required>
                            <label class="custom-control-label" for="approved">
                                <span class="text-success font-weight-bold">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Disetujui - Publikasi Valid
                                </span>
                                <small class="d-block text-muted">
                                    Repository dapat diakses, format sesuai, dan memenuhi standar publikasi
                                </small>
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" 
                                   id="rejected" 
                                   name="keputusan" 
                                   value="rejected" 
                                   class="custom-control-input" 
                                   required>
                            <label class="custom-control-label" for="rejected">
                                <span class="text-danger font-weight-bold">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Ditolak - Perlu Perbaikan
                                </span>
                                <small class="d-block text-muted">
                                    Ada masalah dengan repository atau format yang perlu diperbaiki
                                </small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Catatan Validasi -->
                <div class="form-group">
                    <label for="catatan" class="form-control-label">
                        Catatan Validasi 
                        <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" 
                              id="catatan" 
                              name="catatan" 
                              rows="4"
                              placeholder="Berikan catatan detail untuk mahasiswa dan dosen pembimbing..."
                              required><?= set_value('catatan') ?></textarea>
                    <div class="invalid-feedback">
                        Catatan validasi wajib diisi.
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Catatan ini akan dikirim via email ke mahasiswa dan dosen pembimbing.
                        Berikan penjelasan yang jelas dan konstruktif.
                    </small>
                </div>

                <!-- Template Catatan -->
                <div class="form-group">
                    <label class="form-control-label text-sm">Template Catatan Cepat</label>
                    <div class="btn-group-toggle" data-toggle="buttons">
                        <button type="button" class="btn btn-sm btn-outline-success template-btn" data-template="approved">
                            <i class="fas fa-thumbs-up mr-1"></i> Disetujui
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning template-btn" data-template="format">
                            <i class="fas fa-file-alt mr-1"></i> Format
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info template-btn" data-template="metadata">
                            <i class="fas fa-tags mr-1"></i> Metadata
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger template-btn" data-template="access">
                            <i class="fas fa-lock mr-1"></i> Akses
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi -->
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirm-validation" required>
                        <label class="custom-control-label" for="confirm-validation">
                            Saya sudah mereview repository dengan teliti dan yakin dengan keputusan validasi ini
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group mb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-envelope mr-1"></i>
                                Notifikasi email akan dikirim otomatis setelah validasi
                            </small>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg" id="btn-submit">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Kirim Validasi & Notifikasi Email
                        </button>
                    </div>
                </div>
                
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Checklist & Preview -->
    <div class="col-lg-4">
        <!-- Checklist Validasi -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-success">
                <h5 class="text-white mb-0">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Checklist Validasi
                </h5>
            </div>
            <div class="card-body">
                <div class="validation-checklist">
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="check-access">
                        <label class="custom-control-label" for="check-access">
                            <strong>Repository dapat diakses</strong>
                            <small class="d-block text-muted">Link dapat dibuka dan tidak error</small>
                        </label>
                    </div>
                    
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="check-pdf">
                        <label class="custom-control-label" for="check-pdf">
                            <strong>File PDF tersedia</strong>
                            <small class="d-block text-muted">File skripsi dalam format PDF dapat didownload</small>
                        </label>
                    </div>
                    
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="check-format">
                        <label class="custom-control-label" for="check-format">
                            <strong>Format sesuai template</strong>
                            <small class="d-block text-muted">Struktur dan format mengikuti panduan kampus</small>
                        </label>
                    </div>
                    
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="check-metadata">
                        <label class="custom-control-label" for="check-metadata">
                            <strong>Metadata lengkap</strong>
                            <small class="d-block text-muted">Judul, author, abstract, dan keyword sesuai</small>
                        </label>
                    </div>
                    
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="check-plagiarism">
                        <label class="custom-control-label" for="check-plagiarism">
                            <strong>Bebas plagiarisme</strong>
                            <small class="d-block text-muted">Tidak ada indikasi plagiarisme yang signifikan</small>
                        </label>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan semua poin sudah dicek sebelum validasi
                    </small>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools text-primary mr-2"></i>
                    Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (!empty($link_repository)): ?>
                        <a href="<?= htmlspecialchars($link_repository) ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-block">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Buka Repository
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($publikasi->email_mahasiswa) && !empty($publikasi->email_mahasiswa)): ?>
                        <a href="mailto:<?= $publikasi->email_mahasiswa ?>?subject=Publikasi Tugas Akhir - <?= urlencode($publikasi->judul ?? '') ?>" 
                           class="btn btn-outline-info btn-block">
                            <i class="fas fa-envelope mr-2"></i>
                            Email Mahasiswa
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($publikasi->email_dosen) && !empty($publikasi->email_dosen)): ?>
                        <a href="mailto:<?= $publikasi->email_dosen ?>?subject=Publikasi Tugas Akhir - <?= urlencode($publikasi->judul ?? '') ?>" 
                           class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-envelope mr-2"></i>
                            Email Dosen
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>💡 Tips:</strong> 
                        Buka repository di tab baru untuk review sambil mengisi form validasi.
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
                <p class="text-muted">Data publikasi yang Anda cari tidak ditemukan atau repository belum diinput.</p>
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

<!-- CSS untuk Validasi Form -->
<style>
.form-control-plaintext {
    padding-left: 0;
    padding-right: 0;
    border: none;
    background: none;
    font-weight: 500;
    color: #525f7f;
}

.validation-checklist .custom-control {
    position: relative;
    padding-left: 1.5rem;
}

.validation-checklist .custom-control-label::before {
    border-color: #28a745;
}

.validation-checklist .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
}

.template-btn {
    margin: 2px;
    transition: all 0.2s ease;
}

.template-btn:hover {
    transform: translateY(-1px);
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

.custom-radio .custom-control-label {
    cursor: pointer;
    padding-left: 10px;
}

.custom-radio .custom-control-input:checked ~ .custom-control-label::before {
    border-color: #007bff;
    background-color: #007bff;
}

#catatan {
    min-height: 100px;
    resize: vertical;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-lg {
        width: 100%;
    }
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
</style>

<!-- JavaScript untuk Validasi dan Interaktivitas -->
<script>
$(document).ready(function() {
    const form = $('#form-validasi');
    const catatanField = $('#catatan');
    const submitBtn = $('#btn-submit');
    const confirmCheck = $('#confirm-validation');
    
    // Template catatan
    const templates = {
        approved: "Publikasi tugas akhir telah sesuai dengan standar yang ditetapkan. Repository dapat diakses dengan baik, format dokumen sesuai template, dan metadata lengkap. Publikasi disetujui untuk dipublikasikan.",
        format: "Format dokumen perlu diperbaiki. Pastikan mengikuti template yang telah ditetapkan kampus, termasuk struktur bab, format sitasi, dan layout halaman.",
        metadata: "Metadata pada repository perlu dilengkapi. Pastikan judul, nama author, abstract, keywords, dan informasi bibliografi sudah sesuai dan lengkap.",
        access: "Repository tidak dapat diakses atau terdapat masalah akses. Pastikan link repository benar dan dapat diakses secara public sesuai kebijakan perpustakaan."
    };

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Template button click handler
    $('.template-btn').on('click', function() {
        const templateKey = $(this).data('template');
        const currentText = catatanField.val().trim();
        const templateText = templates[templateKey];
        
        if (currentText && !confirm('Ini akan mengganti catatan yang sudah ada. Lanjutkan?')) {
            return;
        }
        
        catatanField.val(templateText);
        catatanField.trigger('input');
        
        // Visual feedback
        $(this).addClass('btn-success').removeClass('btn-outline-success btn-outline-warning btn-outline-info btn-outline-danger');
        setTimeout(() => {
            $(this).removeClass('btn-success').addClass('btn-outline-success btn-outline-warning btn-outline-info btn-outline-danger'.split(' ').find(cls => $(this).hasClass(cls.replace('btn-outline-', 'btn-outline-'))));
        }, 1000);
    });

    // Real-time validation feedback
    $('input[name="keputusan"]').on('change', function() {
        const decision = $(this).val();
        const placeholder = decision === 'approved' 
            ? 'Publikasi sudah memenuhi standar dan dapat dipublikasikan...'
            : 'Jelaskan masalah yang perlu diperbaiki dan saran perbaikan...';
            
        catatanField.attr('placeholder', placeholder);
        
        // Auto-suggest template
        if (decision === 'approved') {
            $('.template-btn[data-template="approved"]').addClass('btn-outline-success').removeClass('btn-outline-secondary');
        } else {
            $('.template-btn[data-template="approved"]').removeClass('btn-outline-success').addClass('btn-outline-secondary');
        }
    });

    // Character counter for catatan
    catatanField.on('input', function() {
        const length = $(this).val().length;
        const minLength = 20;
        
        if (length < minLength) {
            $(this).addClass('is-invalid').removeClass('is-valid');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    // Form validation
    form.on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }
        
        const decision = $('input[name="keputusan"]:checked').val();
        const catatan = catatanField.val().trim();
        
        const confirmText = decision === 'approved' 
            ? `Yakin ingin MENYETUJUI publikasi ini?\n\nCatatan: ${catatan.substring(0, 100)}...\n\nNotifikasi email akan dikirim ke mahasiswa dan dosen pembimbing.`
            : `Yakin ingin MENOLAK publikasi ini?\n\nCatatan: ${catatan.substring(0, 100)}...\n\nMahasiswa harus memperbaiki dan mengajukan ulang.`;
            
        if (!confirm(confirmText)) {
            return false;
        }
        
        // Show loading state
        submitBtn.addClass('btn-loading').prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses validasi...');
        
        // Submit form
        setTimeout(() => {
            this.submit();
        }, 1000);
    });

    // Preview repository
    $('#btn-preview-repo').on('click', function() {
        const url = '<?= isset($publikasi->link_repository) ? $publikasi->link_repository : "" ?>';
        if (url) {
            window.open(url, '_blank', 'width=1200,height=800');
        }
    });

    // Checklist progress tracking
    $('.validation-checklist input[type="checkbox"]').on('change', function() {
        const totalChecks = $('.validation-checklist input[type="checkbox"]').length;
        const checkedCount = $('.validation-checklist input[type="checkbox"]:checked').length;
        const progress = (checkedCount / totalChecks) * 100;
        
        // Update visual progress (you can add a progress bar here)
        console.log(`Validation progress: ${progress}%`);
        
        // Enable form submission suggestion when all checked
        if (progress === 100) {
            confirmCheck.closest('.form-group').addClass('highlight-ready');
        } else {
            confirmCheck.closest('.form-group').removeClass('highlight-ready');
        }
    });

    // Helper functions
    function validateForm() {
        let isValid = true;
        
        // Validate decision
        if (!$('input[name="keputusan"]:checked').length) {
            alert('Silakan pilih keputusan validasi (Disetujui atau Ditolak).');
            $('input[name="keputusan"]').first().focus();
            isValid = false;
        }
        
        // Validate catatan
        const catatan = catatanField.val().trim();
        if (!catatan || catatan.length < 20) {
            alert('Catatan validasi minimal 20 karakter. Berikan penjelasan yang detail.');
            catatanField.focus();
            isValid = false;
        }
        
        // Validate confirmation
        if (!confirmCheck.is(':checked')) {
            alert('Silakan centang konfirmasi bahwa Anda sudah mereview dengan teliti.');
            confirmCheck.focus();
            isValid = false;
        }
        
        return isValid;
    }

    // Initialize
    catatanField.trigger('input');
    
    // Auto-save draft (optional)
    let saveTimeout;
    catatanField.on('input', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function() {
            // Save to localStorage as draft
            localStorage.setItem('validasi_draft_' + '<?= isset($publikasi->id) ? $publikasi->id : "" ?>', catatanField.val());
        }, 2000);
    });
    
    // Load draft on page load
    const savedDraft = localStorage.getItem('validasi_draft_' + '<?= isset($publikasi->id) ? $publikasi->id : "" ?>');
    if (savedDraft && !catatanField.val()) {
        catatanField.val(savedDraft);
    }
    
    console.log('Validasi Form Loaded');
});

// Prevent accidental page leave
window.addEventListener('beforeunload', function(e) {
    const catatan = $('#catatan').val().trim();
    if (catatan && catatan.length > 10) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>