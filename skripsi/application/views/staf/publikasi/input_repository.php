<?php
/**
 * View Input Repository untuk Staf
 * File: application/views/staf/publikasi/input_repository.php
 * Menggunakan template Argon AdminLTE yang konsisten dengan sistem
 */
?>

<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            
            <!-- Info Mahasiswa -->
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                Informasi Mahasiswa
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <strong>Nama Mahasiswa</strong>
                                            </div>
                                            <div class="col-7">
                                                : <?= esc($publikasi->nama_mahasiswa) ?>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <strong>NIM</strong>
                                            </div>
                                            <div class="col-7">
                                                : <?= esc($publikasi->nim) ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">
                                                <strong>Program Studi</strong>
                                            </div>
                                            <div class="col-7">
                                                : <?= esc($publikasi->program_studi) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group">
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <strong>Dosen Pembimbing</strong>
                                            </div>
                                            <div class="col-7">
                                                : <?= esc($publikasi->nama_dosen_pembimbing) ?>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-5">
                                                <strong>Status Dosen</strong>
                                            </div>
                                            <div class="col-7">
                                                : <span class="badge badge-success">Disetujui</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-5">
                                                <strong>Tanggal Approve</strong>
                                            </div>
                                            <div class="col-7">
                                                : <?= date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col">
                                    <div class="judul-section">
                                        <h6><strong>Judul Skripsi:</strong></h6>
                                        <div class="alert alert-light mb-0">
                                            <?= esc($publikasi->judul_skripsi_final) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Input Repository -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-link text-warning mr-2"></i>
                                Input Repository Link - Step 3
                            </h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Instructions -->
                            <div class="alert alert-info">
                                <div class="alert-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="alert-content">
                                    <h5 class="alert-title">Petunjuk Input Repository:</h5>
                                    <ul class="mb-0">
                                        <li>Pastikan link repository dapat diakses secara publik</li>
                                        <li>Verifikasi bahwa repository berisi file skripsi final yang lengkap</li>
                                        <li>Link harus berupa URL lengkap (dimulai dengan http:// atau https://)</li>
                                        <li>Contoh format: https://github.com/username/repository-name atau https://drive.google.com/...</li>
                                        <li>Setelah input repository, lanjutkan ke tahap validasi final</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Form -->
                            <?= form_open('staf/publikasi/input_repository/' . $publikasi->id, ['id' => 'formInputRepository']) ?>
                                
                                <div class="form-group">
                                    <label class="form-control-label" for="link_repository">
                                        Link Repository Skripsi <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-link"></i>
                                            </span>
                                        </div>
                                        <input type="url" 
                                               class="form-control <?= form_error('link_repository') ? 'is-invalid' : '' ?>" 
                                               id="link_repository" 
                                               name="link_repository" 
                                               value="<?= set_value('link_repository', $publikasi->link_repository ?? '') ?>"
                                               placeholder="https://github.com/username/repository-name atau https://drive.google.com/..."
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" onclick="testRepositoryLink()">
                                                <i class="fas fa-external-link-alt"></i> Test Link
                                            </button>
                                        </div>
                                    </div>
                                    <?php if (form_error('link_repository')): ?>
                                        <div class="invalid-feedback d-block">
                                            <?= form_error('link_repository') ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">
                                        Masukkan URL lengkap repository tempat file skripsi final disimpan
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label" for="keterangan_staf">
                                        Keterangan/Catatan (Opsional)
                                    </label>
                                    <textarea class="form-control" 
                                              id="keterangan_staf" 
                                              name="keterangan_staf" 
                                              rows="4" 
                                              placeholder="Tambahkan catatan atau keterangan terkait repository yang diinput..."><?= set_value('keterangan_staf', $publikasi->komentar_staf ?? '') ?></textarea>
                                    <small class="form-text text-muted">
                                        Opsional: Catatan mengenai repository atau hal-hal yang perlu diperhatikan
                                    </small>
                                </div>

                                <!-- Verification Checklist -->
                                <div class="form-group">
                                    <label class="form-control-label">
                                        Verifikasi Repository (Check semua sebelum submit):
                                    </label>
                                    <div class="verification-checklist">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="check1" required>
                                            <label class="custom-control-label" for="check1">
                                                Repository dapat diakses secara publik
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="check2" required>
                                            <label class="custom-control-label" for="check2">
                                                File skripsi final tersedia dan dapat dibuka
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="check3" required>
                                            <label class="custom-control-label" for="check3">
                                                Link repository sudah benar dan sesuai
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group text-center">
                                    <a href="<?= base_url('staf/publikasi/detail/' . $publikasi->id) ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-warning" id="submitBtn" disabled onclick="return confirmInputRepository()">
                                        <i class="fas fa-save mr-2"></i> Simpan Repository Link
                                    </button>
                                </div>

                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Repository Info -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-eye text-secondary mr-2"></i>
                                        Preview Repository Info
                                    </h3>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#previewCollapse">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="previewCollapse">
                            <div class="card-body">
                                <div id="repositoryPreview" class="repository-preview">
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                                        <p>Masukkan link repository terlebih dahulu untuk melihat preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Skripsi untuk Referensi -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-book text-primary mr-2"></i>
                                        Data Skripsi untuk Referensi
                                    </h3>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#referensiCollapse">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="referensiCollapse">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row mb-3">
                                            <div class="col-4">
                                                <strong>Tanggal Ujian Skripsi</strong>
                                            </div>
                                            <div class="col-8">
                                                : <?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <strong>File Skripsi Final</strong>
                                            </div>
                                            <div class="col-8">
                                                : 
                                                <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                                        <i class="fas fa-download mr-1"></i> Download File Skripsi
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">File tidak tersedia</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                                <div class="row mt-4">
                                    <div class="col">
                                        <h6><strong>Keterangan dari Mahasiswa:</strong></h6>
                                        <div class="alert alert-light">
                                            <?= nl2br(esc($publikasi->keterangan_mahasiswa)) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
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
/* Form styling */
.form-control-label {
    font-weight: 600;
    color: #32325d;
}

.form-group {
    margin-bottom: 2rem;
}

/* Alert styling */
.alert {
    border: none;
    border-radius: 12px;
}

.alert-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.alert-icon {
    float: left;
    font-size: 1.5rem;
    margin-right: 15px;
    margin-top: 5px;
}

.alert-content {
    overflow: hidden;
}

.alert-title {
    font-weight: 600;
    margin-bottom: 15px;
}

.alert ul {
    padding-left: 20px;
}

.alert li {
    margin-bottom: 8px;
}

/* Verification checklist */
.verification-checklist {
    background: #f8f9fe;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 25px;
    margin-top: 15px;
}

.verification-checklist .custom-control {
    margin-bottom: 15px;
}

.verification-checklist .custom-control:last-child {
    margin-bottom: 0;
}

.custom-control-label {
    font-weight: 500;
    color: #525f7f;
    cursor: pointer;
    padding-left: 8px;
}

.custom-control-label::before {
    border-radius: 6px;
}

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #5e72e4;
    border-color: #5e72e4;
}

/* Input group */
.input-group-text {
    background-color: #5e72e4;
    color: white;
    border-color: #5e72e4;
    font-weight: 600;
}

/* Repository preview */
.repository-preview {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    background: #f8f9fe;
    transition: all 0.3s ease;
}

.repository-preview.loaded {
    border-color: #28a745;
    background: #d4edda;
}

.repository-preview.error {
    border-color: #dc3545;
    background: #f8d7da;
}

/* Info sections */
.info-group {
    background: #f8f9fe;
    border-radius: 8px;
    padding: 20px;
}

.judul-section {
    background: #f8f9fe;
    border-radius: 8px;
    padding: 20px;
}

/* Button styling */
.btn {
    font-weight: 600;
    letter-spacing: 0.025em;
    border-radius: 8px;
    padding: 0.625rem 1.25rem;
}

.btn-warning {
    background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
    border: none;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 216, 155, 0.4);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .verification-checklist {
        padding: 20px 15px;
    }
    
    .info-group {
        margin-bottom: 20px;
        padding: 15px;
    }
    
    .alert-icon {
        float: none;
        display: block;
        text-align: center;
        margin-bottom: 15px;
        margin-right: 0;
    }
    
    .alert-content {
        text-align: left;
    }
}
</style>

<script>
$(document).ready(function() {
    // Enable submit button only when all checkboxes are checked
    $('.verification-checklist input[type="checkbox"]').change(function() {
        const allChecked = $('.verification-checklist input[type="checkbox"]:checked').length === 3;
        const hasUrl = $('#link_repository').val().trim() !== '';
        $('#submitBtn').prop('disabled', !(allChecked && hasUrl));
    });
    
    // Check URL input
    $('#link_repository').on('input', function() {
        const allChecked = $('.verification-checklist input[type="checkbox"]:checked').length === 3;
        const hasUrl = $(this).val().trim() !== '';
        $('#submitBtn').prop('disabled', !(allChecked && hasUrl));
        
        // Update preview
        updateRepositoryPreview($(this).val());
    });
    
    // Form validation
    $('#formInputRepository').on('submit', function(e) {
        const url = $('#link_repository').val().trim();
        if (!isValidUrl(url)) {
            e.preventDefault();
            alert('Format URL tidak valid. Pastikan URL dimulai dengan http:// atau https://');
            return false;
        }
    });
});

function testRepositoryLink() {
    const url = $('#link_repository').val().trim();
    if (!url) {
        alert('Masukkan link repository terlebih dahulu');
        return;
    }
    
    if (!isValidUrl(url)) {
        alert('Format URL tidak valid');
        return;
    }
    
    // Open in new tab
    window.open(url, '_blank');
}

function updateRepositoryPreview(url) {
    const preview = $('#repositoryPreview');
    
    if (!url || url.trim() === '') {
        preview.html(`
            <div class="text-center text-muted py-4">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <p>Masukkan link repository terlebih dahulu untuk melihat preview</p>
            </div>
        `).removeClass('loaded error');
        return;
    }
    
    if (!isValidUrl(url)) {
        preview.html(`
            <div class="text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>Format URL tidak valid</p>
            </div>
        `).removeClass('loaded').addClass('error');
        return;
    }
    
    // Show repository info
    let repoInfo = '';
    let iconClass = '';
    
    if (url.includes('github.com')) {
        repoInfo = 'GitHub Repository';
        iconClass = 'fab fa-github';
    } else if (url.includes('drive.google.com')) {
        repoInfo = 'Google Drive';
        iconClass = 'fab fa-google-drive';
    } else if (url.includes('gitlab.com')) {
        repoInfo = 'GitLab Repository';
        iconClass = 'fab fa-gitlab';
    } else {
        repoInfo = 'Repository Link';
        iconClass = 'fas fa-link';
    }
    
    preview.html(`
        <div class="text-center py-4">
            <i class="${iconClass} fa-3x text-success mb-3"></i>
            <h5 class="text-success">${repoInfo}</h5>
            <p class="text-muted mb-3">${url}</p>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.open('${url}', '_blank')">
                <i class="fas fa-external-link-alt mr-1"></i> Buka Repository
            </button>
        </div>
    `).removeClass('error').addClass('loaded');
}

function isValidUrl(string) {
    try {
        const url = new URL(string);
        return url.protocol === "http:" || url.protocol === "https:";
    } catch (_) {
        return false;
    }
}

function confirmInputRepository() {
    const url = $('#link_repository').val().trim();
    return confirm(`Yakin ingin menyimpan repository link?\n\n${url}\n\nSetelah disimpan, Anda akan dialihkan ke halaman validasi final.`);
}

function downloadFile(type, id) {
    window.open('<?= base_url('staf/publikasi/download_file/') ?>' + type + '/' + id, '_blank');
}
</script>