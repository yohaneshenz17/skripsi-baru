<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/pengajuan.php
 * FIXED VERSION - Ready for Copy Paste Replace
 * 
 * PERBAIKAN:
 * 1. Fixed undefined variable $form_title
 * 2. Fixed undefined property nama_pembimbing
 * 3. Added proper error handling untuk semua variables
 * 4. Improved UI dan UX
 * 5. Added complete form validation
 */

// ✅ FIXED: Ensure all variables are defined with default values
$form_title = isset($form_title) ? $form_title : ($is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi');
$proposal = isset($proposal) ? $proposal : new stdClass();
$existing_seminar = isset($existing_seminar) ? $existing_seminar : null;
$is_edit = isset($is_edit) ? $is_edit : false;
$eligibility = isset($eligibility) ? $eligibility : ['eligible' => true, 'errors' => []];
$requirements = isset($requirements) ? $requirements : ['requirements' => [], 'all_met' => true];

// ✅ FIXED: Ensure proposal properties exist
if (!isset($proposal->nama_pembimbing)) {
    if (isset($proposal->dosen_id) && !empty($proposal->dosen_id)) {
        $CI =& get_instance();
        $dosen = $CI->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
        $proposal->nama_pembimbing = $dosen ? $dosen->nama : 'Belum ditetapkan';
    } else {
        $proposal->nama_pembimbing = 'Belum ditetapkan';
    }
}

if (!isset($proposal->judul)) {
    $proposal->judul = 'Judul tidak tersedia';
}

if (!isset($proposal->id)) {
    $proposal->id = 0;
}
?>

<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            <?= htmlspecialchars($form_title) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $is_edit ? 'Edit' : 'Pengajuan' ?></li>
            </ol>
        </nav>
    </div>
    
    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Form Column -->
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>
                        <?= htmlspecialchars($form_title) ?>
                    </h6>
                </div>
                <div class="card-body">
                    
                    <!-- Proposal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded border-left-primary">
                                <h6 class="font-weight-bold mb-2 text-primary">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Informasi Proposal
                                </h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>Judul:</strong> <?= htmlspecialchars($proposal->judul) ?></p>
                                        <p class="mb-1"><strong>Pembimbing:</strong> <?= htmlspecialchars($proposal->nama_pembimbing) ?></p>
                                    </div>
                                    <div class="col-md-4 text-md-right">
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-graduation-cap mr-1"></i>
                                            Seminar Skripsi
                                        </span>
                                    </div>
                                </div>
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
                                <?php if (!$is_edit || ($existing_seminar && empty($existing_seminar->file_skripsi))): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <!-- Custom File Upload Area -->
                            <div class="file-upload-wrapper">
                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input" 
                                           id="file_skripsi" 
                                           name="file_skripsi" 
                                           accept=".pdf,.doc,.docx"
                                           <?php if (!$is_edit || ($existing_seminar && empty($existing_seminar->file_skripsi))): ?>required<?php endif; ?>>
                                    <label class="custom-file-label" for="file_skripsi">
                                        Pilih file skripsi (PDF, DOC, DOCX)
                                    </label>
                                </div>
                                
                                <!-- File Info -->
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Format yang diterima: PDF, DOC, DOCX | Ukuran maksimal: 2MB
                                </small>
                                
                                <!-- Progress Bar (Hidden by default) -->
                                <div class="progress mt-2" id="uploadProgress" style="display: none;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <!-- Existing File Info -->
                            <?php if ($is_edit && $existing_seminar && !empty($existing_seminar->file_skripsi)): ?>
                                <div class="alert alert-info mt-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger fa-2x mr-3"></i>
                                        <div>
                                            <strong>File saat ini:</strong> <?= htmlspecialchars($existing_seminar->file_skripsi) ?>
                                            <br><small class="text-muted">Upload file baru jika ingin mengubah</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?= form_error('file_skripsi', '<small class="text-danger d-block mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>', '</small>') ?>
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
                                      placeholder="Tambahkan keterangan atau catatan khusus mengenai skripsi Anda..."><?= $is_edit && $existing_seminar && isset($existing_seminar->keterangan_mahasiswa) ? htmlspecialchars($existing_seminar->keterangan_mahasiswa) : set_value('keterangan_mahasiswa') ?></textarea>
                            <small class="form-text text-muted">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Contoh: perubahan dari proposal awal, hasil penelitian utama, kendala yang dihadapi, dll.
                            </small>
                            <?= form_error('keterangan_mahasiswa', '<small class="text-danger d-block mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>', '</small>') ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group text-right pt-3 border-top">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
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
                        Status Kelengkapan
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($requirements['requirements'])): ?>
                        <?php foreach ($requirements['requirements'] as $req): ?>
                            <div class="requirement-item mb-2 p-2 rounded" style="background-color: <?= isset($req['met']) && $req['met'] ? '#d4edda' : '#f8d7da' ?>;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-<?= isset($req['met']) && $req['met'] ? 'check-circle text-success' : 'times-circle text-danger' ?> mr-2"></i>
                                    <span class="<?= isset($req['met']) && $req['met'] ? 'text-success' : 'text-danger' ?> font-weight-bold">
                                        <?= htmlspecialchars($req['name'] ?? 'Requirement') ?>
                                    </span>
                                </div>
                                <?php if (isset($req['description'])): ?>
                                    <small class="text-muted ml-3 d-block"><?= htmlspecialchars($req['description']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h6 class="text-success mb-0">Semua Syarat Terpenuhi</h6>
                            <small class="text-muted">Anda dapat mengajukan seminar skripsi</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Help Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-question-circle mr-2"></i>
                        Petunjuk Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <h6 class="font-weight-bold text-primary mb-2">Persyaratan Seminar Skripsi:</h6>
                        <ul class="mb-3">
                            <li>Skripsi telah selesai (Bab 1-5)</li>
                            <li>Telah menyelesaikan penelitian</li>
                            <li>File dalam format PDF/DOC/DOCX</li>
                            <li>Sudah konsultasi dengan pembimbing</li>
                            <li>Proposal telah disetujui sebelumnya</li>
                        </ul>
                        
                        <h6 class="font-weight-bold text-primary mb-2">Alur Proses:</h6>
                        <ol class="mb-3">
                            <li><strong>Review Pembimbing</strong><br>
                                <small class="text-muted">Dosen pembimbing akan mereview pengajuan Anda</small>
                            </li>
                            <li><strong>Validasi Kaprodi</strong><br>
                                <small class="text-muted">Kaprodi akan memvalidasi dan menjadwalkan</small>
                            </li>
                            <li><strong>Pelaksanaan Seminar</strong><br>
                                <small class="text-muted">Presentasi skripsi di hadapan tim penguji</small>
                            </li>
                        </ol>
                        
                        <div class="alert alert-warning p-2">
                            <small>
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Penting:</strong> Pastikan semua data sudah benar sebelum mengirim pengajuan.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-phone mr-2"></i>
                        Butuh Bantuan?
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="mb-2">
                            <i class="fas fa-user-tie text-primary mr-2"></i>
                            <strong>Dosen Pembimbing:</strong><br>
                            <?= htmlspecialchars($proposal->nama_pembimbing) ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            <strong>Admin Akademik:</strong><br>
                            sipd@stkyakobus.ac.id
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            <strong>Telepon:</strong><br>
                            (0971) 333-0264
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.file-upload-wrapper .custom-file-label {
    border: 2px dashed #007bff;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-wrapper .custom-file-label:hover {
    border-color: #0056b3;
    background-color: #f8f9fa;
}

.file-upload-wrapper .custom-file-label::after {
    display: none;
}

.file-upload-wrapper .custom-file-input:focus ~ .custom-file-label {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.requirement-item {
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.requirement-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.border-left-primary {
    border-left: 0.25rem solid #007bff !important;
}
</style>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    
    // File input change handler
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        let fileSize = this.files[0] ? (this.files[0].size / (1024 * 1024)).toFixed(2) : 0;
        
        if (fileName) {
            $(this).next('.custom-file-label').addClass("selected").html(
                '<i class="fas fa-file-alt mr-2"></i>' + fileName + 
                '<br><small class="text-muted">Ukuran: ' + fileSize + ' MB</small>'
            );
        } else {
            $(this).next('.custom-file-label').removeClass("selected").html('Pilih file skripsi (PDF, DOC, DOCX)');
        }
    });
    
    // Form validation
    $('#seminarForm').on('submit', function(e) {
        let isEdit = $(this).data('is-edit');
        let fileInput = $('#file_skripsi')[0];
        let submitBtn = $('#submitBtn');
        
        // Check if file is required
        if (!isEdit && (!fileInput.files || fileInput.files.length === 0)) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'File Diperlukan',
                text: 'Harap upload file skripsi sebelum mengirim pengajuan!',
                confirmButtonColor: '#007bff'
            });
            return false;
        }
        
        // File size validation (2MB = 2 * 1024 * 1024 bytes)
        if (fileInput.files && fileInput.files.length > 0) {
            let fileSize = fileInput.files[0].size;
            let fileName = fileInput.files[0].name;
            let allowedTypes = ['.pdf', '.doc', '.docx'];
            let fileExt = fileName.toLowerCase().substring(fileName.lastIndexOf('.'));
            
            if (!allowedTypes.includes(fileExt)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Valid',
                    text: 'Hanya file PDF, DOC, dan DOCX yang diperbolehkan!',
                    confirmButtonColor: '#007bff'
                });
                return false;
            }
            
            if (fileSize > 2 * 1024 * 1024) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal 2MB. File Anda: ' + (fileSize / (1024 * 1024)).toFixed(2) + ' MB',
                    confirmButtonColor: '#007bff'
                });
                return false;
            }
        }
        
        // Show confirmation
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Pengajuan',
            text: isEdit ? 'Yakin ingin memperbarui pengajuan seminar skripsi?' : 'Yakin ingin mengirim pengajuan seminar skripsi?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, ' + (isEdit ? 'Perbarui' : 'Kirim'),
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...'
                );
                
                // Show progress bar
                $('#uploadProgress').show();
                
                // Simulate progress (for better UX)
                let progress = 0;
                let progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    $('#uploadProgress .progress-bar').css('width', progress + '%');
                }, 200);
                
                // Submit form after a short delay
                setTimeout(() => {
                    clearInterval(progressInterval);
                    $('#uploadProgress .progress-bar').css('width', '100%');
                    $('#seminarForm')[0].submit();
                }, 1500);
            }
        });
        
        return false;
    });
    
    // Drag and drop functionality
    let uploadArea = $('.custom-file-label');
    
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-light');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-light');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-light');
        
        let files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#file_skripsi')[0].files = files;
            $('#file_skripsi').trigger('change');
        }
    });
});

// Sweet Alert fallback if not loaded
if (typeof Swal === 'undefined') {
    window.Swal = {
        fire: function(options) {
            if (typeof options === 'object' && options.text) {
                alert(options.text);
            } else {
                alert(options);
            }
            return Promise.resolve({isConfirmed: true});
        }
    };
}
</script>

<!-- Load Sweet Alert if not already loaded -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>