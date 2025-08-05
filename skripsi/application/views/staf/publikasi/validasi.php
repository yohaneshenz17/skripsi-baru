<?php
/**
 * View Validasi Final Publikasi untuk Staf
 * File: application/views/staf/publikasi/validasi.php
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Validasi Final Publikasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/publikasi') ?>">Publikasi</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/publikasi/detail/' . $publikasi->id) ?>">Detail</a></li>
                    <li class="breadcrumb-item active">Validasi Final</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Warning Alert -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    <p class="mb-1">Ini adalah tahap validasi final publikasi. Setelah Anda menyetujui:</p>
                    <ul class="mb-1">
                        <li>Status publikasi akan menjadi <strong>"SELESAI"</strong></li>
                        <li>Mahasiswa akan menerima notifikasi email</li>
                        <li>Mahasiswa dapat mendownload Surat Keterangan Publikasi</li>
                        <li>Proses tidak dapat dibatalkan</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Summary Data -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-check"></i>
                            Ringkasan Data Publikasi
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
                                    <tr>
                                        <td><strong>Email Mahasiswa</strong></td>
                                        <td>: <?= esc($publikasi->email_mahasiswa) ?></td>
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
                                        <td><strong>Tanggal Ujian</strong></td>
                                        <td>: <?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Pengajuan</strong></td>
                                        <td>: <?= date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Judul Skripsi:</strong></h6>
                                <div class="alert alert-light mb-2">
                                    <?= esc($publikasi->judul_skripsi_final) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Repository Info -->
        <div class="row">
            <div class="col-12">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-link"></i>
                            Repository yang Sudah Diinput
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="repository-info">
                            <div class="d-flex align-items-center mb-3">
                                <div class="repository-icon">
                                    <?php if (strpos($publikasi->link_repository, 'github.com') !== false): ?>
                                        <i class="fab fa-github fa-2x text-dark"></i>
                                    <?php elseif (strpos($publikasi->link_repository, 'drive.google.com') !== false): ?>
                                        <i class="fab fa-google-drive fa-2x text-primary"></i>
                                    <?php elseif (strpos($publikasi->link_repository, 'gitlab.com') !== false): ?>
                                        <i class="fab fa-gitlab fa-2x text-warning"></i>
                                    <?php else: ?>
                                        <i class="fas fa-link fa-2x text-info"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="repository-details ml-3">
                                    <h5 class="mb-1">Repository Skripsi</h5>
                                    <p class="mb-2 text-muted"><?= esc($publikasi->link_repository) ?></p>
                                    <a href="<?= esc($publikasi->link_repository) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Buka Repository
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($publikasi->komentar_staf)): ?>
                            <div class="mt-3">
                                <h6><strong>Keterangan yang Sudah Diinput:</strong></h6>
                                <div class="alert alert-info">
                                    <?= nl2br(esc($publikasi->komentar_staf)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Validation Form -->
        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-gavel"></i>
                            Keputusan Validasi Final - Step 4
                        </h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Validation Checklist -->
                        <div class="validation-checklist mb-4">
                            <h6><strong>Checklist Validasi (Pastikan semua sudah sesuai):</strong></h6>
                            <div class="checklist-items">
                                <div class="checklist-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Data mahasiswa dan skripsi sudah lengkap dan benar
                                </div>
                                <div class="checklist-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Dosen pembimbing sudah memberikan persetujuan
                                </div>
                                <div class="checklist-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Repository link sudah diinput dan dapat diakses
                                </div>
                                <div class="checklist-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    File skripsi final tersedia di repository
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <?= form_open('staf/publikasi/validasi/' . $publikasi->id, ['id' => 'formValidasiFinal']) ?>
                            
                            <div class="form-group">
                                <label class="required">Keputusan Validasi</label>
                                <div class="decision-options">
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="approve" name="action" value="approve" class="custom-control-input" required>
                                        <label class="custom-control-label" for="approve">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <strong>SETUJUI</strong> - Publikasi selesai dan mahasiswa dapat download surat keterangan
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="reject" name="action" value="reject" class="custom-control-input" required>
                                        <label class="custom-control-label" for="reject">
                                            <i class="fas fa-times-circle text-danger"></i>
                                            <strong>TOLAK</strong> - Ada yang perlu diperbaiki, dikembalikan ke mahasiswa
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="komentar_staf">Komentar/Catatan Final</label>
                                <textarea class="form-control" 
                                          id="komentar_staf" 
                                          name="komentar_staf" 
                                          rows="5" 
                                          placeholder="Masukkan komentar final Anda..."><?= set_value('komentar_staf') ?></textarea>
                                <small class="form-text text-muted">
                                    <span id="komentarHelper">Komentar opsional untuk persetujuan, wajib diisi untuk penolakan</span>
                                </small>
                            </div>

                            <!-- Notification Preview -->
                            <div class="form-group">
                                <label>Preview Notifikasi Email</label>
                                <div id="emailPreview" class="email-preview">
                                    <div class="alert alert-light">
                                        <i class="fas fa-info-circle"></i>
                                        Pilih keputusan untuk melihat preview email yang akan dikirim
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <a href="<?= base_url('staf/publikasi/detail/' . $publikasi->id) ?>" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn" disabled onclick="return confirmAction()">
                                    <i class="fas fa-gavel"></i> <span id="submitText">Proses Validasi</span>
                                </button>
                            </div>

                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Reference -->
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-pdf"></i>
                            Dokumen untuk Referensi
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="doc-item">
                                    <h6><i class="fas fa-file-pdf text-danger"></i> Surat Keterangan Revisi</h6>
                                    <?php if (!empty($publikasi->file_surat_revisi)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="downloadFile('surat_revisi', <?= $publikasi->id ?>)">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-warning">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="doc-item">
                                    <h6><i class="fas fa-file-pdf text-danger"></i> File Skripsi Final</h6>
                                    <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-warning">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="doc-item">
                                    <h6><i class="fas fa-file-pdf text-danger"></i> Surat Perpustakaan</h6>
                                    <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="downloadFile('surat_perpustakaan', <?= $publikasi->id ?>)">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-warning">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
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

.validation-checklist {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
}

.checklist-items {
    margin-top: 15px;
}

.checklist-item {
    margin-bottom: 10px;
    padding: 8px 0;
    font-size: 14px;
}

.checklist-item:last-child {
    margin-bottom: 0;
}

.decision-options {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-top: 10px;
}

.decision-options .custom-control-label {
    font-size: 14px;
    font-weight: normal;
    cursor: pointer;
    padding: 10px;
    border-radius: 6px;
    transition: background-color 0.2s;
}

.decision-options .custom-control-label:hover {
    background-color: rgba(0,123,255,0.1);
}

.decision-options .custom-control-input:checked + .custom-control-label {
    background-color: rgba(40,167,69,0.1);
    border: 1px solid #28a745;
}

.decision-options .custom-control-input[value="reject"]:checked + .custom-control-label {
    background-color: rgba(220,53,69,0.1);
    border: 1px solid #dc3545;
}

.repository-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
}

.repository-icon {
    text-align: center;
    width: 60px;
}

.email-preview {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    min-height: 100px;
}

.doc-item {
    text-align: center;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #f8f9fa;
}

.doc-item h6 {
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .repository-info .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .repository-details {
        margin-left: 0 !important;
        margin-top: 15px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Handle decision change
    $('input[name="action"]').change(function() {
        const action = $(this).val();
        updateFormBasedOnAction(action);
        updateEmailPreview(action);
        
        // Enable submit button
        $('#submitBtn').prop('disabled', false);
    });
    
    // Real-time validation for reject action
    $('#komentar_staf').on('input', function() {
        const action = $('input[name="action"]:checked').val();
        if (action === 'reject') {
            const komentar = $(this).val().trim();
            $('#submitBtn').prop('disabled', komentar === '');
        }
    });
});

function updateFormBasedOnAction(action) {
    const submitBtn = $('#submitBtn');
    const submitText = $('#submitText');
    const komentarHelper = $('#komentarHelper');
    const komentarField = $('#komentar_staf');
    
    if (action === 'approve') {
        submitBtn.removeClass('btn-danger').addClass('btn-success');
        submitText.html('<i class="fas fa-check"></i> SETUJUI PUBLIKASI');
        komentarHelper.text('Komentar opsional untuk persetujuan');
        komentarField.attr('placeholder', 'Komentar tambahan (opsional)...');
        komentarField.prop('required', false);
    } else if (action === 'reject') {
        submitBtn.removeClass('btn-success').addClass('btn-danger');
        submitText.html('<i class="fas fa-times"></i> TOLAK PUBLIKASI');
        komentarHelper.text('Komentar WAJIB diisi untuk penolakan - jelaskan alasan penolakan');
        komentarField.attr('placeholder', 'Jelaskan alasan penolakan dengan detail...');
        komentarField.prop('required', true);
        
        // Check if comment is filled for reject
        const komentar = komentarField.val().trim();
        $('#submitBtn').prop('disabled', komentar === '');
    }
}

function updateEmailPreview(action) {
    const emailPreview = $('#emailPreview');
    const mahasiswaName = '<?= esc($publikasi->nama_mahasiswa) ?>';
    const judulSkripsi = '<?= esc($publikasi->judul_skripsi_final) ?>';
    const repositoryLink = '<?= esc($publikasi->link_repository) ?>';
    
    let previewContent = '';
    
    if (action === 'approve') {
        previewContent = `
            <div class="alert alert-success">
                <h6><i class="fas fa-envelope"></i> Email yang akan dikirim:</h6>
                <strong>Kepada:</strong> ${mahasiswaName} (<?= esc($publikasi->email_mahasiswa) ?>)<br>
                <strong>Subject:</strong> [SIM-TA] Publikasi Tugas Akhir Selesai<br><br>
                <strong>Isi Email:</strong><br>
                <em>"Publikasi tugas akhir Anda telah selesai divalidasi dan disetujui oleh staf akademik.
                <br>Repository: ${repositoryLink}
                <br>Anda dapat mendownload Surat Keterangan Publikasi di dashboard mahasiswa."</em>
            </div>
        `;
    } else if (action === 'reject') {
        previewContent = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-envelope"></i> Email yang akan dikirim:</h6>
                <strong>Kepada:</strong> ${mahasiswaName} (<?= esc($publikasi->email_mahasiswa) ?>)<br>
                <strong>Subject:</strong> [SIM-TA] Publikasi Tugas Akhir Ditolak<br><br>
                <strong>Isi Email:</strong><br>
                <em>"Publikasi tugas akhir Anda ditolak oleh staf akademik dengan alasan: [komentar Anda]
                <br>Silakan perbaiki sesuai komentar dan ajukan kembali."</em>
            </div>
        `;
    }
    
    emailPreview.html(previewContent);
}

function confirmAction() {
    const action = $('input[name="action"]:checked').val();
    const komentar = $('#komentar_staf').val().trim();
    
    if (action === 'approve') {
        return confirm('Yakin ingin MENYETUJUI publikasi ini?\n\nSetelah disetujui:\n- Status menjadi SELESAI\n- Mahasiswa menerima notifikasi\n- Mahasiswa dapat download surat\n- Proses tidak dapat dibatalkan\n\nLanjutkan?');
    } else if (action === 'reject') {
        if (komentar === '') {
            alert('Komentar penolakan harus diisi!');
            return false;
        }
        return confirm('Yakin ingin MENOLAK publikasi ini?\n\nAlasan: ' + komentar + '\n\nPublikasi akan dikembalikan ke mahasiswa untuk diperbaiki.');
    }
    
    return false;
}

function downloadFile(type, id) {
    window.open('<?= base_url('staf/publikasi/download_file/') ?>' + type + '/' + id, '_blank');
}
</script>