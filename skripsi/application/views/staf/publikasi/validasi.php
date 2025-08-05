<?php
/**
 * View Validasi Final Publikasi untuk Staf
 * File: application/views/staf/publikasi/validasi.php
 * Menggunakan template Argon AdminLTE yang konsisten dengan sistem
 */
?>

<!-- Page content -->
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col">
            
            <!-- Warning Alert -->
            <div class="row">
                <div class="col">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <h5 class="alert-title">Perhatian!</h5>
                            <p class="mb-2">Ini adalah tahap validasi final publikasi. Setelah Anda menyetujui:</p>
                            <ul class="mb-0">
                                <li>Status publikasi akan menjadi <strong>"SELESAI"</strong></li>
                                <li>Mahasiswa akan menerima notifikasi email</li>
                                <li>Mahasiswa dapat mendownload Surat Keterangan Publikasi</li>
                                <li>Proses tidak dapat dibatalkan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Data -->
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-clipboard-check text-info mr-2"></i>
                                Ringkasan Data Publikasi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-section">
                                        <div class="info-item">
                                            <div class="info-label">Nama Mahasiswa</div>
                                            <div class="info-value"><?= esc($publikasi->nama_mahasiswa) ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">NIM</div>
                                            <div class="info-value"><?= esc($publikasi->nim) ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Program Studi</div>
                                            <div class="info-value"><?= esc($publikasi->program_studi) ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Email Mahasiswa</div>
                                            <div class="info-value"><?= esc($publikasi->email_mahasiswa) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-section">
                                        <div class="info-item">
                                            <div class="info-label">Dosen Pembimbing</div>
                                            <div class="info-value"><?= esc($publikasi->nama_dosen_pembimbing) ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Status Dosen</div>
                                            <div class="info-value">
                                                <span class="badge badge-success">Disetujui</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Tanggal Ujian</div>
                                            <div class="info-value"><?= $publikasi->tanggal_ujian_skripsi ? date('d/m/Y', strtotime($publikasi->tanggal_ujian_skripsi)) : '-' ?></div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Tanggal Pengajuan</div>
                                            <div class="info-value"><?= date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col">
                                    <div class="judul-section">
                                        <h6 class="section-title">Judul Skripsi:</h6>
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

            <!-- Repository Info -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-link text-success mr-2"></i>
                                Repository yang Sudah Diinput
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="repository-display">
                                <div class="d-flex align-items-center">
                                    <div class="repo-icon mr-4">
                                        <?php if (strpos($publikasi->link_repository, 'github.com') !== false): ?>
                                            <i class="fab fa-github fa-3x text-dark"></i>
                                        <?php elseif (strpos($publikasi->link_repository, 'drive.google.com') !== false): ?>
                                            <i class="fab fa-google-drive fa-3x text-primary"></i>
                                        <?php elseif (strpos($publikasi->link_repository, 'gitlab.com') !== false): ?>
                                            <i class="fab fa-gitlab fa-3x text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-link fa-3x text-info"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="repo-details flex-grow-1">
                                        <h5 class="repo-title">Repository Skripsi</h5>
                                        <p class="repo-url"><?= esc($publikasi->link_repository) ?></p>
                                        <a href="<?= esc($publikasi->link_repository) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-external-link-alt mr-1"></i> Buka Repository
                                        </a>
                                    </div>
                                </div>
                                
                                <?php if (!empty($publikasi->komentar_staf)): ?>
                                <div class="mt-4">
                                    <h6 class="section-title">Keterangan yang Sudah Diinput:</h6>
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
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="mb-0">
                                <i class="fas fa-gavel text-warning mr-2"></i>
                                Keputusan Validasi Final - Step 4
                            </h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Validation Checklist -->
                            <div class="validation-checklist mb-4">
                                <h6 class="checklist-title">Checklist Validasi (Pastikan semua sudah sesuai):</h6>
                                <div class="checklist-items">
                                    <div class="checklist-item completed">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        Data mahasiswa dan skripsi sudah lengkap dan benar
                                    </div>
                                    <div class="checklist-item completed">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        Dosen pembimbing sudah memberikan persetujuan
                                    </div>
                                    <div class="checklist-item completed">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        Repository link sudah diinput dan dapat diakses
                                    </div>
                                    <div class="checklist-item completed">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        File skripsi final tersedia di repository
                                    </div>
                                </div>
                            </div>

                            <!-- Form -->
                            <?= form_open('staf/publikasi/validasi/' . $publikasi->id, ['id' => 'formValidasiFinal']) ?>
                                
                                <div class="form-group">
                                    <label class="form-control-label">
                                        Keputusan Validasi <span class="text-danger">*</span>
                                    </label>
                                    <div class="decision-options">
                                        <div class="custom-control custom-radio mb-3">
                                            <input type="radio" id="approve" name="action" value="approve" class="custom-control-input" required>
                                            <label class="custom-control-label decision-label approve-label" for="approve">
                                                <div class="decision-content">
                                                    <div class="decision-icon">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </div>
                                                    <div class="decision-text">
                                                        <strong>SETUJUI</strong>
                                                        <small>Publikasi selesai dan mahasiswa dapat download surat keterangan</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="reject" name="action" value="reject" class="custom-control-input" required>
                                            <label class="custom-control-label decision-label reject-label" for="reject">
                                                <div class="decision-content">
                                                    <div class="decision-icon">
                                                        <i class="fas fa-times-circle text-danger"></i>
                                                    </div>
                                                    <div class="decision-text">
                                                        <strong>TOLAK</strong>
                                                        <small>Ada yang perlu diperbaiki, dikembalikan ke mahasiswa</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label" for="komentar_staf">
                                        Komentar/Catatan Final
                                    </label>
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
                                    <label class="form-control-label">Preview Notifikasi Email</label>
                                    <div id="emailPreview" class="email-preview">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-envelope fa-2x mb-3"></i>
                                            <p>Pilih keputusan untuk melihat preview email yang akan dikirim</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group text-center">
                                    <a href="<?= base_url('staf/publikasi/detail/' . $publikasi->id) ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success" id="submitBtn" disabled onclick="return confirmAction()">
                                        <i class="fas fa-gavel mr-2"></i> <span id="submitText">Proses Validasi</span>
                                    </button>
                                </div>

                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Reference -->
            <div class="row mt-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">
                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                        Dokumen untuk Referensi
                                    </h3>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#documentsCollapse">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="documentsCollapse">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="doc-card">
                                            <div class="doc-icon">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            </div>
                                            <div class="doc-content">
                                                <h6 class="doc-title">Surat Keterangan Revisi</h6>
                                                <?php if (!empty($publikasi->file_surat_revisi)): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="downloadFile('surat_revisi', <?= $publikasi->id ?>)">
                                                        <i class="fas fa-download mr-1"></i> Download
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">File tidak tersedia</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="doc-card">
                                            <div class="doc-icon">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            </div>
                                            <div class="doc-content">
                                                <h6 class="doc-title">File Skripsi Final</h6>
                                                <?php if (!empty($publikasi->file_skripsi_final)): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="downloadFile('skripsi_final', <?= $publikasi->id ?>)">
                                                        <i class="fas fa-download mr-1"></i> Download
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">File tidak tersedia</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="doc-card">
                                            <div class="doc-icon">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            </div>
                                            <div class="doc-content">
                                                <h6 class="doc-title">Surat Perpustakaan</h6>
                                                <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="downloadFile('surat_perpustakaan', <?= $publikasi->id ?>)">
                                                        <i class="fas fa-download mr-1"></i> Download
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
/* Alert styling */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
}

.alert-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.alert-icon {
    float: left;
    font-size: 1.8rem;
    margin-right: 20px;
    margin-top: 5px;
}

.alert-content {
    overflow: hidden;
}

.alert-title {
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.alert ul {
    padding-left: 20px;
    margin-bottom: 0;
}

.alert li {
    margin-bottom: 8px;
    font-weight: 500;
}

/* Info sections */
.info-section {
    background: #f8f9fe;
    border-radius: 12px;
    padding: 25px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #dee2e6;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #32325d;
    flex: 0 0 40%;
}

.info-value {
    color: #525f7f;
    flex: 1;
    text-align: right;
}

.section-title {
    font-weight: 700;
    color: #32325d;
    margin-bottom: 15px;
}

.judul-section {
    background: #f8f9fe;
    border-radius: 12px;
    padding: 25px;
}

/* Repository display */
.repository-display {
    background: #f8f9fe;
    border-radius: 12px;
    padding: 30px;
    border-left: 4px solid #28a745;
}

.repo-icon {
    flex-shrink: 0;
}

.repo-title {
    font-weight: 700;
    color: #32325d;
    margin-bottom: 10px;
}

.repo-url {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 15px;
    word-break: break-all;
}

/* Validation checklist */
.validation-checklist {
    background: #f8f9fe;
    border: 1px solid #28a745;
    border-radius: 12px;
    padding: 25px;
}

.checklist-title {
    font-weight: 700;
    color: #32325d;
    margin-bottom: 20px;
}

.checklist-items {
    display: grid;
    gap: 15px;
}

.checklist-item {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: #525f7f;
    font-size: 14px;
}

.checklist-item.completed {
    color: #28a745;
}

/* Decision options */
.decision-options {
    background: #f8f9fe;
    border-radius: 12px;
    padding: 25px;
    margin-top: 15px;
}

.decision-label {
    cursor: pointer;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
    transition: all 0.3s ease;
    background: white;
}

.decision-label:hover {
    border-color: #5e72e4;
    box-shadow: 0 4px 12px rgba(94, 114, 228, 0.15);
}

.custom-control-input:checked + .approve-label {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.custom-control-input:checked + .reject-label {  
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.decision-content {
    display: flex;
    align-items: center;
}

.decision-icon {
    font-size: 1.5rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.decision-text strong {
    display: block;
    font-size: 16px;
    margin-bottom: 5px;
}

.decision-text small {
    color: #6c757d;
    font-size: 13px;
    line-height: 1.4;
}

/* Email preview */
.email-preview {
    background: #f8f9fe;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 25px;
    min-height: 120px;
}

/* Document cards */
.doc-card {
    text-align: center;
    padding: 25px;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background: #f8f9fe;
    transition: all 0.3s ease;
    height: 100%;
}

.doc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.doc-icon {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.doc-title {
    font-weight: 600;
    margin-bottom: 20px;
    color: #32325d;
}

/* Form controls */
.form-control-label {
    font-weight: 600;
    color: #32325d;
    margin-bottom: 10px;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #cad1d7;
    padding: 0.75rem 1rem;
}

.form-control:focus {
    border-color: #5e72e4;
    box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
}

/* Button styling */
.btn {
    font-weight: 600;
    letter-spacing: 0.025em;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-success {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .alert-icon {
        float: none;
        display: block;
        text-align: center;
        margin-bottom: 15px;
        margin-right: 0;
    }
    
    .repository-display .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .repo-icon {
        margin-bottom: 20px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px 0;
    }
    
    .info-value {
        text-align: left;
        margin-top: 5px;
    }
    
    .decision-content {
        flex-direction: column;
        text-align: center;
    }
    
    .decision-icon {
        margin-bottom: 15px;
        margin-right: 0;
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
        submitText.html('<i class="fas fa-check mr-2"></i>SETUJUI PUBLIKASI');
        komentarHelper.text('Komentar opsional untuk persetujuan');
        komentarField.attr('placeholder', 'Komentar tambahan (opsional)...');
        komentarField.prop('required', false);
    } else if (action === 'reject') {
        submitBtn.removeClass('btn-success').addClass('btn-danger');
        submitText.html('<i class="fas fa-times mr-2"></i>TOLAK PUBLIKASI');
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
            <div class="alert alert-success mb-0">
                <h6 class="alert-title"><i class="fas fa-envelope mr-2"></i>Email yang akan dikirim:</h6>
                <div class="email-details">
                    <p><strong>Kepada:</strong> ${mahasiswaName} (<?= esc($publikasi->email_mahasiswa) ?>)</p>
                    <p><strong>Subject:</strong> [SIM-TA] Publikasi Tugas Akhir Selesai</p>
                    <div class="email-body">
                        <strong>Isi Email:</strong><br>
                        <em>"Publikasi tugas akhir Anda telah selesai divalidasi dan disetujui oleh staf akademik.
                        <br>Repository: ${repositoryLink}
                        <br>Anda dapat mendownload Surat Keterangan Publikasi di dashboard mahasiswa."</em>
                    </div>
                </div>
            </div>
        `;
    } else if (action === 'reject') {
        previewContent = `
            <div class="alert alert-danger mb-0">
                <h6 class="alert-title"><i class="fas fa-envelope mr-2"></i>Email yang akan dikirim:</h6>
                <div class="email-details">
                    <p><strong>Kepada:</strong> ${mahasiswaName} (<?= esc($publikasi->email_mahasiswa) ?>)</p>
                    <p><strong>Subject:</strong> [SIM-TA] Publikasi Tugas Akhir Ditolak</p>
                    <div class="email-body">
                        <strong>Isi Email:</strong><br>
                        <em>"Publikasi tugas akhir Anda ditolak oleh staf akademik dengan alasan: [komentar Anda]
                        <br>Silakan perbaiki sesuai komentar dan ajukan kembali."</em>
                    </div>
                </div>
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