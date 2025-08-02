<?php
/**
 * Form Pengajuan Seminar Skripsi Mahasiswa (Phase 5)
 * File: application/views/mahasiswa/seminar_skripsi/pengajuan.php
 * 
 * Form untuk mengajukan seminar skripsi dengan upload file skripsi final
 * Menggunakan struktur yang konsisten dengan form seminar proposal
 */
?>

<style>
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
    .requirement-card {
        border-left: 4px solid #28a745;
        background-color: #f8f9fa;
    }
    .requirement-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .requirement-item:last-child {
        border-bottom: none;
    }
    .file-upload-area {
        border: 2px dashed #ced4da;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .file-upload-area:hover {
        border-color: #667eea;
        background-color: #f0f0ff;
    }
    .file-upload-area.drag-over {
        border-color: #667eea;
        background-color: #e7e9ff;
    }
    .progress-step {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #667eea;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 1rem;
    }
    .step-number.completed {
        background-color: #28a745;
    }
</style>

<!-- Content untuk template mahasiswa -->
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-upload mr-2"></i>
            <?= $form_title ?>
        </h1>
        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Form Column -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>
                        Form Pengajuan Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    
                    <!-- Proposal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded">
                                <h6 class="font-weight-bold mb-2">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                    Informasi Proposal
                                </h6>
                                <p class="mb-1"><strong>Judul:</strong> <?= $proposal->judul ?></p>
                                <p class="mb-1"><strong>Pembimbing:</strong> <?= $proposal->nama_pembimbing ?></p>
                                <p class="mb-0"><strong>Status Workflow:</strong> 
                                    <span class="badge badge-success">Seminar Skripsi</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <?= form_open_multipart('mahasiswa/seminar_skripsi/pengajuan/' . $proposal->id, [
                        'id' => 'seminarForm',
                        'data-is-edit' => $is_edit ? 'true' : 'false'
                    ]) ?>
                    
                        <!-- File Upload Section -->
                        <div class="form-group">
                            <label for="file_skripsi" class="font-weight-bold">
                                <i class="fas fa-cloud-upload-alt text-primary mr-2"></i>
                                File Skripsi Final
                                <?php if (!$is_edit || empty($existing_seminar->file_skripsi)): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <div class="file-upload-area" id="uploadArea">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Upload File Skripsi</h5>
                                    <p class="text-muted mb-3">
                                        Klik untuk memilih file atau drag & drop di sini
                                        <br>
                                        <small>Format: PDF, DOC, DOCX | Ukuran maksimal: 2MB</small>
                                    </p>
                                    <input type="file" 
                                           class="form-control-file" 
                                           id="file_skripsi" 
                                           name="file_skripsi" 
                                           accept=".pdf,.doc,.docx"
                                           style="display: none;">
                                    <button type="button" class="btn btn-primary" onclick="$('#file_skripsi').click()">
                                        <i class="fas fa-folder-open mr-2"></i>Pilih File
                                    </button>
                                </div>
                            </div>
                            
                            <!-- File Info Display -->
                            <div id="file-info" class="mt-2"></div>
                            
                            <!-- Existing File Info -->
                            <?php if ($is_edit && !empty($existing_seminar->file_skripsi)): ?>
                                <div class="alert alert-info mt-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>File saat ini:</strong> <?= $existing_seminar->file_skripsi ?>
                                    <br><small>Biarkan kosong jika tidak ingin mengubah file</small>
                                </div>
                            <?php endif; ?>
                            
                            <?= form_error('file_skripsi', '<small class="text-danger">', '</small>') ?>
                        </div>

                        <!-- Keterangan -->
                        <div class="form-group">
                            <label for="keterangan_mahasiswa" class="font-weight-bold">
                                <i class="fas fa-comment text-primary mr-2"></i>
                                Keterangan Tambahan
                            </label>
                            <textarea class="form-control" 
                                      id="keterangan_mahasiswa" 
                                      name="keterangan_mahasiswa" 
                                      rows="4" 
                                      placeholder="Tambahkan keterangan atau catatan khusus mengenai skripsi Anda (opsional)"><?= $is_edit && $existing_seminar ? $existing_seminar->keterangan_mahasiswa : set_value('keterangan_mahasiswa') ?></textarea>
                            <small class="form-text text-muted">
                                Contoh: menjelaskan perubahan signifikan dari proposal awal, hasil penelitian utama, dll.
                            </small>
                            <?= form_error('keterangan_mahasiswa', '<small class="text-danger">', '</small>') ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group text-right">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i>
                                <?= $is_edit ? 'Perbarui Pengajuan' : 'Kirim Pengajuan' ?>
                            </button>
                        </div>

                    <?= form_close() ?>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="col-lg-4">
            
            <!-- Requirements Check -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-check-circle mr-2"></i>
                        Kelengkapan Syarat
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($requirements['requirements'])): ?>
                        <?php foreach ($requirements['requirements'] as $req): ?>
                            <div class="requirement-item">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-<?= $req['met'] ? 'check-circle text-success' : 'times-circle text-danger' ?> mr-2"></i>
                                    <div class="flex-grow-1">
                                        <strong><?= $req['name'] ?></strong>
                                        <br><small class="text-muted">
                                            Diperlukan: <?= $req['required'] ?> | 
                                            Saat ini: <?= $req['current'] ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Progress Workflow -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-route mr-2"></i>
                        Progress Workflow
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress-step">
                        <div class="step-number completed">1</div>
                        <div>
                            <strong>Usulan Proposal</strong>
                            <br><small class="text-success">✓ Selesai</small>
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-number completed">2</div>
                        <div>
                            <strong>Bimbingan</strong>
                            <br><small class="text-success">✓ Selesai</small>
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-number completed">3</div>
                        <div>
                            <strong>Seminar Proposal</strong>
                            <br><small class="text-success">✓ Selesai</small>
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-number completed">4</div>
                        <div>
                            <strong>Penelitian</strong>
                            <br><small class="text-success">✓ Selesai</small>
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-number">5</div>
                        <div>
                            <strong>Seminar Skripsi</strong>
                            <br><small class="text-primary">⏳ Sedang Berlangsung</small>
                        </div>
                    </div>
                    
                    <div class="progress-step">
                        <div class="step-number" style="background-color: #6c757d;">6</div>
                        <div>
                            <strong>Publikasi</strong>
                            <br><small class="text-muted">⏭ Selanjutnya</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Guidelines -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-lightbulb mr-2"></i>
                        Panduan & Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">📋 Persiapan File Skripsi:</h6>
                        <ul class="small">
                            <li>Pastikan semua BAB 1-5 sudah lengkap</li>
                            <li>Format sesuai template yang diberikan</li>
                            <li>Referensi dan daftar pustaka sudah benar</li>
                            <li>File dalam format PDF atau Word</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">⏰ Proses Selanjutnya:</h6>
                        <ul class="small">
                            <li>Review oleh dosen pembimbing</li>
                            <li>Validasi Kaprodi + Turnitin check</li>
                            <li>Penjadwalan seminar</li>
                            <li>Penunjukan dosen penguji</li>
                            <li>Pelaksanaan seminar skripsi</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info p-2 small">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Estimasi waktu proses:</strong> 7-14 hari kerja
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- JavaScript untuk drag & drop dan validasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('file_skripsi');
    const fileInfo = document.getElementById('file-info');
    
    // Drag & Drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });
    
    // Click to upload
    uploadArea.addEventListener('click', function(e) {
        if (e.target.type !== 'file') {
            fileInput.click();
        }
    });
    
    // File selection handler
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            fileInfo.innerHTML = '<div class="alert alert-danger small"><i class="fas fa-exclamation-triangle mr-1"></i> File harus berformat PDF, DOC, atau DOCX.</div>';
            fileInput.value = '';
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            fileInfo.innerHTML = '<div class="alert alert-danger small"><i class="fas fa-exclamation-triangle mr-1"></i> Ukuran file maksimal 2MB.</div>';
            fileInput.value = '';
            return;
        }
        
        // Show success info
        const sizeInMB = (file.size / 1024 / 1024).toFixed(2);
        fileInfo.innerHTML = `
            <div class="alert alert-success small">
                <i class="fas fa-check-circle mr-1"></i> 
                <strong>File terpilih:</strong> ${file.name} (${sizeInMB} MB)
            </div>
        `;
        
        // Update upload area
        uploadArea.innerHTML = `
            <div class="upload-content">
                <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                <h6 class="text-success">File Terpilih</h6>
                <p class="text-muted mb-3">${file.name}<br><small>${sizeInMB} MB</small></p>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="$('#file_skripsi').click()">
                    <i class="fas fa-exchange-alt mr-1"></i>Ganti File
                </button>
            </div>
        `;
    }
});
</script>