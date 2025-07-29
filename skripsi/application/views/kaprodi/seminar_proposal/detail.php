<?php
/**
 * Detail Seminar Proposal & Form Validasi Plagiarisme
 * File: application/views/kaprodi/seminar_proposal/detail.php
 * 
 * Halaman untuk melihat detail pengajuan seminar proposal mahasiswa
 * dan melakukan validasi plagiarisme dengan upload file Turnitin
 */

// Get current URL for active menu
$current_url = uri_string();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?> - SIM-TA STK Santo Yakobus</title>
    
    <!-- CSS dari template kaprodi existing -->
    <link rel="icon" href="<?= base_url('assets/img/brand/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/nucleo/css/nucleo.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?= base_url('assets/css/argon.css?v=1.2.0') ?>" type="text/css">
    
    <style>
        .info-box {
            background: #f8f9fe;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .info-box h6 {
            color: #32325d;
            margin-bottom: 0.5rem;
        }
        .plagiarism-input {
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
        }
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .file-upload-area:hover {
            border-color: #5e72e4;
            background-color: #f8f9ff;
        }
        .file-upload-area.dragover {
            border-color: #5e72e4;
            background-color: #f8f9ff;
        }
        .percentage-display {
            font-size: 2rem;
            font-weight: 700;
        }
        .percentage-safe {
            color: #2dce89;
        }
        .percentage-warning {
            color: #fb6340;
        }
        .percentage-danger {
            color: #f5365c;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-success { background-color: #2dce89; }
        .status-warning { background-color: #fb6340; }
        .status-danger { background-color: #f5365c; }
        
        .jurnal-requirement {
            background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
            color: white;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .jurnal-requirement.not-eligible {
            background: linear-gradient(87deg, #fb6340 0, #fbb140 100%);
        }
    </style>
</head>

<body>
    <!-- Sidebar (gunakan existing template kaprodi) -->
    <?php $this->load->view('template/kaprodi_sidebar'); ?>
    
    <!-- Main content -->
    <div class="main-content" id="panel">
        <!-- Topnav (gunakan existing template kaprodi) -->
        <?php $this->load->view('template/kaprodi_topnav'); ?>
        
        <!-- Header -->
        <div class="header bg-primary pb-6">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center py-4">
                        <div class="col-lg-8 col-7">
                            <h6 class="h2 text-white d-inline-block mb-0"><?= $title ?></h6>
                            <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                    <li class="breadcrumb-item"><a href="<?= base_url('kaprodi') ?>"><i class="fas fa-home"></i></a></li>
                                    <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/seminar_proposal') ?>">Seminar Proposal</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-lg-4 col-5 text-right">
                            <a href="<?= base_url('kaprodi/seminar_proposal') ?>" class="btn btn-sm btn-neutral">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page content -->
        <div class="container-fluid mt--6">
            <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Detail Mahasiswa & Proposal -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0">Detail Pengajuan Seminar Proposal</h3>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-warning badge-lg">Perlu Review</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Info Mahasiswa -->
                            <h6 class="heading-small text-muted mb-4">Informasi Mahasiswa</h6>
                            <div class="pl-lg-4">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Nama Mahasiswa</label>
                                            <p class="form-control-static font-weight-bold">
                                                <?= htmlspecialchars($seminar->nama_mahasiswa) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">NIM</label>
                                            <p class="form-control-static">
                                                <?= htmlspecialchars($seminar->nim) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Email</label>
                                            <p class="form-control-static">
                                                <a href="mailto:<?= htmlspecialchars($seminar->email_mahasiswa) ?>">
                                                    <?= htmlspecialchars($seminar->email_mahasiswa) ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Dosen Pembimbing</label>
                                            <p class="form-control-static">
                                                <?= htmlspecialchars($seminar->nama_pembimbing ?? 'Belum ditetapkan') ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Info Proposal -->
                            <h6 class="heading-small text-muted mb-4">Informasi Proposal</h6>
                            <div class="pl-lg-4">
                                <div class="form-group">
                                    <label class="form-control-label">Judul Proposal</label>
                                    <p class="form-control-static font-weight-bold">
                                        <?= htmlspecialchars($seminar->judul) ?>
                                    </p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Tanggal Pengajuan</label>
                                            <p class="form-control-static">
                                                <?= date('d F Y, H:i', strtotime($seminar->created_at)) ?> WIT
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Status Pembimbing</label>
                                            <p class="form-control-static">
                                                <span class="badge badge-success">Disetujui</span>
                                                <small class="text-muted ml-2">
                                                    <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-control-label">File Proposal</label>
                                    <div>
                                        <?php if(!empty($seminar->file_proposal)): ?>
                                        <a href="<?= base_url('uploads/proposals/' . $seminar->file_proposal) ?>" 
                                           class="btn btn-outline-primary btn-sm" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Lihat Proposal
                                        </a>
                                        <a href="<?= base_url('uploads/proposals/' . $seminar->file_proposal) ?>" 
                                           class="btn btn-outline-secondary btn-sm" download>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">File tidak tersedia</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if(!empty($seminar->keterangan_mahasiswa)): ?>
                                <div class="form-group">
                                    <label class="form-control-label">Keterangan Mahasiswa</label>
                                    <div class="info-box">
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Validasi Plagiarisme -->
                <div class="col-lg-4">
                    <!-- Status Jurnal Bimbingan -->
                    <div class="card">
                        <div class="card-body">
                            <div class="jurnal-requirement <?= !$jurnal_requirement['eligible'] ? 'not-eligible' : '' ?>">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="text-white mb-0">
                                            <i class="fas fa-book mr-2"></i>
                                            Status Jurnal Bimbingan
                                        </h6>
                                        <span class="text-white-50">
                                            <?= $jurnal_requirement['jurnal_validated_count'] ?> dari <?= $jurnal_requirement['minimum_required'] ?> jurnal
                                        </span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas <?= $jurnal_requirement['eligible'] ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> text-white"></i>
                                    </div>
                                </div>
                                <div class="progress mt-3" style="height: 4px;">
                                    <div class="progress-bar <?= $jurnal_requirement['eligible'] ? 'bg-white' : 'bg-warning' ?>" 
                                         style="width: <?= ($jurnal_requirement['jurnal_validated_count'] / $jurnal_requirement['minimum_required']) * 100 ?>%"></div>
                                </div>
                                <small class="text-white-50 mt-2 d-block">
                                    <?= $jurnal_requirement['message'] ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Validasi Plagiarisme -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-search mr-2"></i>
                                Validasi Plagiarisme
                            </h3>
                        </div>
                        <div class="card-body">
                            <?= form_open_multipart('kaprodi/seminar_proposal/proses_validasi', ['id' => 'plagiarism-form']) ?>
                            <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                            
                            <div class="form-group">
                                <label class="form-control-label">
                                    Persentase Plagiarisme <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control plagiarism-input" 
                                           name="plagiarism_percentage" 
                                           id="plagiarism_percentage"
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           placeholder="0.0"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold">%</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Batas maksimal: 30%. Jika ≥30% akan otomatis ditolak.
                                </small>
                            </div>
                            
                            <!-- Preview Persentase -->
                            <div class="text-center mb-3" id="percentage-preview" style="display: none;">
                                <div class="percentage-display" id="percentage-value">0%</div>
                                <div id="percentage-status">
                                    <span class="status-indicator" id="status-dot"></span>
                                    <span id="status-text">Masukkan persentase</span>
                                </div>
                            </div>
                            
                            <!-- File Upload Area -->
                            <div class="form-group" id="file-upload-group" style="display: none;">
                                <label class="form-control-label">
                                    File Hasil Turnitin <span class="text-danger">*</span>
                                </label>
                                
                                <div class="file-upload-area" onclick="document.getElementById('file_turnitin').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <h5 class="text-muted">Klik untuk upload file</h5>
                                    <p class="text-sm text-muted mb-0">
                                        Format: PDF | Maksimal: 5MB
                                    </p>
                                    <input type="file" 
                                           class="d-none" 
                                           name="file_turnitin" 
                                           id="file_turnitin"
                                           accept=".pdf">
                                </div>
                                
                                <div id="file-info" class="mt-2" style="display: none;">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        <span id="file-name"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <small class="form-text text-danger font-weight-bold mt-2" id="file-required-text" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    WAJIB: Upload file hasil Turnitin karena plagiarisme ≥30%
                                </small>
                            </div>

                            <div class="form-group">
                                <label class="form-control-label">Komentar / Catatan</label>
                                <textarea class="form-control" 
                                          name="komentar_kaprodi" 
                                          rows="4" 
                                          placeholder="Berikan catatan tambahan untuk mahasiswa (opsional)"></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-block" id="submit-btn" disabled>
                                    <i class="fas fa-check mr-2"></i>
                                    <span id="submit-text">Masukkan Persentase Dulu</span>
                                </button>
                            </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-graduation-cap mr-2"></i>
                                    Proses Selanjutnya
                                </h6>
                                <ul class="mb-0 pl-3">
                                    <li>Jika plagiarisme <strong>&lt;30%</strong>: Lanjut ke penjadwalan seminar</li>
                                    <li>Jika plagiarisme <strong>≥30%</strong>: Pengajuan ditolak dan dikembalikan ke mahasiswa</li>
                                </ul>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-user-graduate text-primary"></i>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted">Mahasiswa</small>
                                            <br><strong><?= htmlspecialchars($seminar->nama_mahasiswa) ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-clock text-warning"></i>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted">Waktu Pengajuan</small>
                                            <br><strong><?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <i class="fas fa-user-check text-success"></i>
                                        </div>
                                        <div class="col">
                                            <small class="text-muted">Disetujui Pembimbing</small>
                                            <br><strong><?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php $this->load->view('template/kaprodi_footer'); ?>
        </div>
    </div>
    
    <!-- Core JS -->
    <script src="<?= base_url('assets/vendor/jquery/dist/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/js-cookie/js.cookie.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js') ?>"></script>
    
    <!-- Argon JS -->
    <script src="<?= base_url('assets/js/argon.js?v=1.2.0') ?>"></script>
    
    <script>
        $(document).ready(function() {
            const plagiarismInput = $('#plagiarism_percentage');
            const fileUploadGroup = $('#file-upload-group');
            const fileInput = $('#file_turnitin');
            const percentagePreview = $('#percentage-preview');
            const percentageValue = $('#percentage-value');
            const statusDot = $('#status-dot');
            const statusText = $('#status-text');
            const submitBtn = $('#submit-btn');
            const submitText = $('#submit-text');
            const fileRequiredText = $('#file-required-text');
            
            // Handle plagiarism percentage input
            plagiarismInput.on('input', function() {
                const percentage = parseFloat($(this).val()) || 0;
                updatePercentageDisplay(percentage);
                toggleFileUpload(percentage);
                updateSubmitButton(percentage);
            });
            
            // Handle file upload
            fileInput.on('change', function() {
                handleFileUpload(this);
            });
            
            // Form validation
            $('#plagiarism-form').on('submit', function(e) {
                const percentage = parseFloat(plagiarismInput.val()) || 0;
                
                if (percentage >= 30 && !fileInput[0].files.length) {
                    e.preventDefault();
                    alert('File hasil Turnitin wajib diupload karena plagiarisme ≥30%!');
                    return false;
                }
                
                if (!confirm('Apakah Anda yakin dengan hasil validasi ini?')) {
                    e.preventDefault();
                    return false;
                }
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
            });
            
            function updatePercentageDisplay(percentage) {
                percentagePreview.show();
                percentageValue.text(percentage.toFixed(1) + '%');
                
                if (percentage < 30) {
                    percentageValue.removeClass('percentage-warning percentage-danger').addClass('percentage-safe');
                    statusDot.removeClass('status-warning status-danger').addClass('status-success');
                    statusText.text('Dalam batas normal');
                } else if (percentage < 50) {
                    percentageValue.removeClass('percentage-safe percentage-danger').addClass('percentage-warning');
                    statusDot.removeClass('status-success status-danger').addClass('status-warning');
                    statusText.text('Melebihi batas - akan ditolak');
                } else {
                    percentageValue.removeClass('percentage-safe percentage-warning').addClass('percentage-danger');
                    statusDot.removeClass('status-success status-warning').addClass('status-danger');
                    statusText.text('Sangat tinggi - akan ditolak');
                }
            }
            
            function toggleFileUpload(percentage) {
                if (percentage >= 30) {
                    fileUploadGroup.show();
                    fileRequiredText.show();
                    fileInput.prop('required', true);
                } else {
                    fileUploadGroup.hide();
                    fileRequiredText.hide();
                    fileInput.prop('required', false);
                    removeFile();
                }
            }
            
            function updateSubmitButton(percentage) {
                if (percentage > 0) {
                    submitBtn.prop('disabled', false);
                    if (percentage >= 30) {
                        submitText.text('Tolak Pengajuan');
                        submitBtn.removeClass('btn-primary').addClass('btn-danger');
                    } else {
                        submitText.text('Lanjut ke Penjadwalan');
                        submitBtn.removeClass('btn-danger').addClass('btn-success');
                    }
                } else {
                    submitBtn.prop('disabled', true);
                    submitText.text('Masukkan Persentase Dulu');
                    submitBtn.removeClass('btn-success btn-danger').addClass('btn-primary');
                }
            }
            
            function handleFileUpload(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const fileSize = file.size / 1024 / 1024; // MB
                    
                    if (fileSize > 5) {
                        alert('File terlalu besar! Maksimal 5MB.');
                        input.value = '';
                        return;
                    }
                    
                    if (file.type !== 'application/pdf') {
                        alert('File harus berformat PDF!');
                        input.value = '';
                        return;
                    }
                    
                    $('#file-name').text(file.name + ' (' + fileSize.toFixed(2) + ' MB)');
                    $('#file-info').show();
                }
            }
            
            function removeFile() {
                fileInput.val('');
                $('#file-info').hide();
            }
            
            // Make removeFile function global
            window.removeFile = removeFile;
            
            // File drag and drop
            $('.file-upload-area').on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });
            
            $('.file-upload-area').on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });
            
            $('.file-upload-area').on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    fileInput[0].files = files;
                    handleFileUpload(fileInput[0]);
                }
            });
        });
    </script>
</body>
</html>