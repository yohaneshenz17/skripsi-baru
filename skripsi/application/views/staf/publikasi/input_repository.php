<?php
/**
 * View Input Repository untuk Staf
 * File: application/views/staf/publikasi/input_repository.php
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Input Repository Skripsi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/publikasi') ?>">Publikasi</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/publikasi/detail/' . $publikasi->id) ?>">Detail</a></li>
                    <li class="breadcrumb-item active">Input Repository</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Info Mahasiswa -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Informasi Mahasiswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Nama Mahasiswa</strong></td>
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
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Dosen Pembimbing</strong></td>
                                        <td>: <?= esc($publikasi->nama_dosen_pembimbing) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status Dosen</strong></td>
                                        <td>: <span class="badge badge-success">Disetujui</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Approve</strong></td>
                                        <td>: <?= date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Judul Skripsi:</strong></h6>
                                <p class="text-justify"><?= esc($publikasi->judul_skripsi_final) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Input Repository -->
        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-link"></i>
                            Input Repository Link - Step 3
                        </h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Petunjuk Input Repository:</h5>
                            <ol>
                                <li>Pastikan link repository dapat diakses secara publik</li>
                                <li>Verifikasi bahwa repository berisi file skripsi final yang lengkap</li>
                                <li>Link harus berupa URL lengkap (dimulai dengan http:// atau https://)</li>
                                <li>Contoh format: https://github.com/username/repository-name atau https://drive.google.com/...</li>
                                <li>Setelah input repository, lanjutkan ke tahap validasi final</li>
                            </ol>
                        </div>

                        <!-- Form -->
                        <?= form_open('staf/publikasi/input_repository/' . $publikasi->id, ['id' => 'formInputRepository']) ?>
                            
                            <div class="form-group">
                                <label for="link_repository" class="required">Link Repository Skripsi</label>
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
                                <label for="keterangan_staf">Keterangan/Catatan (Opsional)</label>
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
                                <label>Verifikasi Repository (Check semua sebelum submit):</label>
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
                                   class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-warning" id="submitBtn" disabled onclick="return confirmInputRepository()">
                                    <i class="fas fa-save"></i> Simpan Repository Link
                                </button>
                            </div>

                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Repository Info -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-eye"></i>
                            Preview Repository Info
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="repositoryPreview" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i>
                            Masukkan link repository terlebih dahulu untuk melihat preview
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Skripsi untuk Referensi -->
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book"></i>
                            Data Skripsi untuk Referensi
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-sm">
                                    <tr>
                                        <td width="25%"><strong>Tanggal Ujian Skripsi</strong></td>
                                        <td>: <?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>File Skripsi Final</strong></td>
                                        <td>: 
                                            <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                                    <i class="fas fa-download"></i> Download File Skripsi
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-warning">File tidak tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                        <div class="row mt-3">
                            <div class="col-12">
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
</section>

<style>
.required::after {
    content: " *";
    color: red;
}

.verification-checklist {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-top: 10px;
}

.verification-checklist .custom-control {
    margin-bottom: 10px;
}

.verification-checklist .custom-control:last-child {
    margin-bottom: 0;
}

.input-group-text {
    background-color: #17a2b8;
    color: white;
    border-color: #17a2b8;
}

#repositoryPreview {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

#repositoryPreview.loaded {
    border-color: #28a745;
    background: #d4edda;
}

#repositoryPreview.error {
    border-color: #dc3545;
    background: #f8d7da;
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
        preview.html('<i class="fas fa-info-circle"></i> Masukkan link repository terlebih dahulu untuk melihat preview')
               .removeClass('loaded error');
        return;
    }
    
    if (!isValidUrl(url)) {
        preview.html('<i class="fas fa-exclamation-triangle text-danger"></i> Format URL tidak valid')
               .removeClass('loaded').addClass('error');
        return;
    }
    
    // Show repository info
    let repoInfo = '';
    if (url.includes('github.com')) {
        repoInfo = '<i class="fab fa-github"></i> GitHub Repository';
    } else if (url.includes('drive.google.com')) {
        repoInfo = '<i class="fab fa-google-drive"></i> Google Drive';
    } else if (url.includes('gitlab.com')) {
        repoInfo = '<i class="fab fa-gitlab"></i> GitLab Repository';
    } else {
        repoInfo = '<i class="fas fa-link"></i> Repository Link';
    }
    
    preview.html(`
        <div>
            <h5>${repoInfo}</h5>
            <p class="mb-2">${url}</p>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.open('${url}', '_blank')">
                <i class="fas fa-external-link-alt"></i> Buka Repository
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