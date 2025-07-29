<?php
/**
 * Form Penjadwalan Seminar Proposal & Penunjukan Penguji
 * File: application/views/kaprodi/seminar_proposal/jadwal.php
 * 
 * Halaman untuk menjadwalkan seminar proposal dan menunjuk dosen penguji
 * setelah validasi plagiarisme passed (<30%)
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
    
    <!-- Additional CSS for better UX -->
    <style>
        .validation-success {
            background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
            color: white;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .schedule-form {
            background: #f8f9fe;
            border-radius: 0.375rem;
            padding: 1.5rem;
        }
        .penguji-selection {
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s;
        }
        .penguji-selection.selected {
            border-color: #5e72e4;
            background-color: #f8f9ff;
        }
        .datetime-input {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.75rem;
        }
        .datetime-input:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25);
        }
        .conflict-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }
        .info-card {
            background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
            color: white;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .timeline-item {
            border-left: 2px solid #dee2e6;
            padding-left: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #5e72e4;
        }
        .timeline-item.completed::before {
            background-color: #2dce89;
        }
        .timeline-item.current::before {
            background-color: #fb6340;
            box-shadow: 0 0 0 3px rgba(251, 99, 64, 0.3);
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
                                    <li class="breadcrumb-item active" aria-current="page">Penjadwalan</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-lg-4 col-5 text-right">
                            <a href="<?= base_url('kaprodi/seminar_proposal/detail/' . $seminar->id) ?>" class="btn btn-sm btn-neutral">
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
                <!-- Form Penjadwalan -->
                <div class="col-lg-8">
                    <!-- Status Validasi -->
                    <div class="validation-success">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-white mb-0">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Validasi Plagiarisme Passed
                                </h5>
                                <p class="text-white-50 mb-0 mt-1">
                                    Plagiarisme: <strong><?= number_format($seminar->plagiarism_percentage, 1) ?>%</strong> 
                                    (Di bawah batas maksimal 30%)
                                </p>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                                    <i class="fas fa-thumbs-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Penjadwalan -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Penjadwalan Seminar Proposal
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="schedule-form">
                                <?= form_open('kaprodi/seminar_proposal/simpan_jadwal', ['id' => 'jadwal-form']) ?>
                                <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                                
                                <!-- Informasi Mahasiswa -->
                                <div class="info-card mb-4">
                                    <h6 class="text-white mb-2">
                                        <i class="fas fa-user-graduate mr-2"></i>
                                        Informasi Mahasiswa
                                    </h6>
                                    <div class="row text-white-50">
                                        <div class="col-md-6">
                                            <small>Nama:</small><br>
                                            <strong class="text-white"><?= htmlspecialchars($seminar->nama_mahasiswa) ?></strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small>NIM:</small><br>
                                            <strong class="text-white"><?= htmlspecialchars($seminar->nim) ?></strong>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small>Judul:</small><br>
                                        <strong class="text-white"><?= htmlspecialchars($seminar->judul) ?></strong>
                                    </div>
                                </div>
                                
                                <!-- Jadwal Seminar -->
                                <h6 class="heading-small text-muted mb-4">Jadwal Seminar</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">
                                                Tanggal Seminar <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control datetime-input" 
                                                   name="tanggal_seminar" 
                                                   id="tanggal_seminar"
                                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                                                   required>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Minimal H+1 dari hari ini
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">
                                                Waktu Seminar <span class="text-danger">*</span>
                                            </label>
                                            <input type="time" 
                                                   class="form-control datetime-input" 
                                                   name="jam_seminar" 
                                                   id="jam_seminar"
                                                   required>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-clock"></i>
                                                Waktu Indonesia Timur (WIT)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label">
                                        Tempat Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="tempat_seminar" 
                                           id="tempat_seminar"
                                           placeholder="Contoh: Ruang Kuliah 1, STK Santo Yakobus Merauke" 
                                           required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Sebutkan nama ruang dan lokasi yang spesifik
                                    </small>
                                </div>
                                
                                <!-- Penunjukan Penguji -->
                                <hr class="my-4">
                                <h6 class="heading-small text-muted mb-4">Penunjukan Tim Penguji</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">
                                                Dosen Penguji 1 <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" name="penguji1_id" id="penguji1_id" required>
                                                <option value="">Pilih Dosen Penguji 1</option>
                                                <?php foreach($dosen_list as $dosen): ?>
                                                <option value="<?= $dosen->id ?>" data-nama="<?= htmlspecialchars($dosen->nama) ?>">
                                                    <?= htmlspecialchars($dosen->nama) ?> 
                                                    <?php if(!empty($dosen->nip)): ?>
                                                    (<?= htmlspecialchars($dosen->nip) ?>)
                                                    <?php endif; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label">
                                                Dosen Penguji 2 <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control" name="penguji2_id" id="penguji2_id" required>
                                                <option value="">Pilih Dosen Penguji 2</option>
                                                <?php foreach($dosen_list as $dosen): ?>
                                                <option value="<?= $dosen->id ?>" data-nama="<?= htmlspecialchars($dosen->nama) ?>">
                                                    <?= htmlspecialchars($dosen->nama) ?> 
                                                    <?php if(!empty($dosen->nip)): ?>
                                                    (<?= htmlspecialchars($dosen->nip) ?>)
                                                    <?php endif; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Preview Tim Penguji -->
                                <div id="tim-penguji-preview" class="alert alert-light" style="display: none;">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-users mr-2"></i>
                                        Preview Tim Penguji
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">Pembimbing:</small><br>
                                            <strong><?= htmlspecialchars($seminar->nama_pembimbing) ?></strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Penguji 1:</small><br>
                                            <strong id="preview-penguji1">-</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Penguji 2:</small><br>
                                            <strong id="preview-penguji2">-</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Catatan Tambahan -->
                                <div class="form-group">
                                    <label class="form-control-label">Catatan Tambahan</label>
                                    <textarea class="form-control" 
                                              name="catatan_kaprodi" 
                                              rows="3" 
                                              placeholder="Catatan atau instruksi khusus untuk seminar (opsional)"></textarea>
                                </div>

                                <div class="text-right">
                                    <a href="<?= base_url('kaprodi/seminar_proposal/detail/' . $seminar->id) ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-success" id="submit-btn">
                                        <i class="fas fa-calendar-check mr-2"></i>
                                        Jadwalkan Seminar
                                    </button>
                                </div>
                                <?= form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Info Panel -->
                <div class="col-lg-4">
                    <!-- Progress Timeline -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-list-check mr-2"></i>
                                Progress Workflow
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline-item completed">
                                <h6 class="mb-1">Pengajuan Mahasiswa</h6>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?>
                                </small>
                            </div>
                            
                            <div class="timeline-item completed">
                                <h6 class="mb-1">Review Pembimbing</h6>
                                <small class="text-muted">
                                    Disetujui - <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                </small>
                            </div>
                            
                            <div class="timeline-item completed">
                                <h6 class="mb-1">Validasi Plagiarisme</h6>
                                <small class="text-muted">
                                    <?= number_format($seminar->plagiarism_percentage, 1) ?>% - Passed
                                </small>
                            </div>
                            
                            <div class="timeline-item current">
                                <h6 class="mb-1">Penjadwalan Seminar</h6>
                                <small class="text-warning">
                                    <i class="fas fa-spinner fa-pulse"></i> Sedang diproses
                                </small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6 class="mb-1">Pelaksanaan Seminar</h6>
                                <small class="text-muted">Menunggu penjadwalan</small>
                            </div>
                            
                            <div class="timeline-item">
                                <h6 class="mb-1">Penelitian</h6>
                                <small class="text-muted">Tahap selanjutnya</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informasi Penting -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Penting
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading">
                                    <i class="fas fa-bell mr-2"></i>
                                    Notifikasi Otomatis
                                </h6>
                                <p class="mb-0">
                                    Setelah penjadwalan, sistem akan mengirim email otomatis ke:
                                </p>
                                <ul class="mb-0 mt-2">
                                    <li>Mahasiswa</li>
                                    <li>Dosen pembimbing</li>
                                    <li>Dosen penguji 1 & 2</li>
                                    <li>Staf administrasi</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning mb-3">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Kebijakan Penunjukan
                                </h6>
                                <p class="mb-0">
                                    Sesuai kebijakan STK Santo Yakobus, penunjukan dosen penguji tidak memerlukan konfirmasi kesediaan.
                                </p>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <div>
                                        <h6 class="mb-1">Durasi Seminar</h6>
                                        <small class="text-muted">Estimasi 60-90 menit</small>
                                    </div>
                                </div>
                                
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <div>
                                        <h6 class="mb-1">Persiapan Ruang</h6>
                                        <small class="text-muted">Koordinasi dengan staf</small>
                                    </div>
                                </div>
                                
                                <div class="list-group-item d-flex justify-content-between px-0">
                                    <div>
                                        <h6 class="mb-1">Berita Acara</h6>
                                        <small class="text-muted">Akan disiapkan staf</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <span class="h2 font-weight-bold mb-0" id="total-dosen"><?= count($dosen_list) ?></span>
                                    <span class="text-sm text-muted d-block">Dosen Tersedia</span>
                                </div>
                                <div class="col-6 text-center">
                                    <span class="h2 font-weight-bold mb-0 text-success"><?= number_format($seminar->plagiarism_percentage, 1) ?>%</span>
                                    <span class="text-sm text-muted d-block">Plagiarisme</span>
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
            const penguji1Select = $('#penguji1_id');
            const penguji2Select = $('#penguji2_id');
            const timPengujiPreview = $('#tim-penguji-preview');
            const previewPenguji1 = $('#preview-penguji1');
            const previewPenguji2 = $('#preview-penguji2');
            const submitBtn = $('#submit-btn');
            
            // Handle penguji selection
            penguji1Select.on('change', function() {
                updatePengujiOptions();
                updateTimPreview();
            });
            
            penguji2Select.on('change', function() {
                updatePengujiOptions();
                updateTimPreview();
            });
            
            // Form validation
            $('#jadwal-form').on('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                if (!confirm('Apakah Anda yakin ingin menjadwalkan seminar proposal ini? Notifikasi akan dikirim ke semua pihak terkait.')) {
                    e.preventDefault();
                    return false;
                }
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Menjadwalkan...');
            });
            
            // Date validation
            $('#tanggal_seminar').on('change', function() {
                validateDate();
            });
            
            function updatePengujiOptions() {
                const selected1 = penguji1Select.val();
                const selected2 = penguji2Select.val();
                
                // Reset all options
                penguji1Select.find('option').prop('disabled', false);
                penguji2Select.find('option').prop('disabled', false);
                
                // Disable selected options in the other select
                if (selected1) {
                    penguji2Select.find(`option[value="${selected1}"]`).prop('disabled', true);
                }
                
                if (selected2) {
                    penguji1Select.find(`option[value="${selected2}"]`).prop('disabled', true);
                }
                
                // Visual feedback for disabled options
                penguji1Select.find('option:disabled').addClass('text-muted');
                penguji2Select.find('option:disabled').addClass('text-muted');
            }
            
            function updateTimPreview() {
                const penguji1Text = penguji1Select.find('option:selected').data('nama') || '-';
                const penguji2Text = penguji2Select.find('option:selected').data('nama') || '-';
                
                previewPenguji1.text(penguji1Text);
                previewPenguji2.text(penguji2Text);
                
                if (penguji1Select.val() || penguji2Select.val()) {
                    timPengujiPreview.show();
                } else {
                    timPengujiPreview.hide();
                }
            }
            
            function validateDate() {
                const selectedDate = new Date($('#tanggal_seminar').val());
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(0, 0, 0, 0);
                
                const selectedDay = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday
                
                // Remove existing warnings
                $('.conflict-warning').remove();
                
                if (selectedDate < tomorrow) {
                    showDateWarning('Tanggal seminar harus minimal H+1 dari hari ini.', 'danger');
                    return false;
                }
                
                if (selectedDay === 0) {
                    showDateWarning('Seminar pada hari Minggu mungkin tidak ideal. Pertimbangkan hari kerja.', 'warning');
                }
                
                if (selectedDay === 6) {
                    showDateWarning('Seminar pada hari Sabtu mungkin tidak ideal. Pertimbangkan hari kerja.', 'warning');
                }
                
                return true;
            }
            
            function showDateWarning(message, type) {
                const alertClass = type === 'danger' ? 'alert-danger' : 'alert-warning';
                const icon = type === 'danger' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';
                
                const warningHtml = `
                    <div class="alert ${alertClass} conflict-warning mt-2">
                        <i class="fas ${icon} mr-2"></i>
                        ${message}
                    </div>
                `;
                
                $('#tanggal_seminar').parent().append(warningHtml);
            }
            
            function validateForm() {
                const requiredFields = [
                    '#tanggal_seminar',
                    '#jam_seminar',
                    '#tempat_seminar',
                    '#penguji1_id',
                    '#penguji2_id'
                ];
                
                let isValid = true;
                
                // Check required fields
                requiredFields.forEach(function(field) {
                    const $field = $(field);
                    if (!$field.val().trim()) {
                        $field.addClass('is-invalid');
                        isValid = false;
                    } else {
                        $field.removeClass('is-invalid');
                    }
                });
                
                // Check if penguji are different
                if (penguji1Select.val() && penguji2Select.val() && penguji1Select.val() === penguji2Select.val()) {
                    alert('Penguji 1 dan Penguji 2 tidak boleh sama!');
                    penguji2Select.addClass('is-invalid');
                    isValid = false;
                }
                
                // Validate date
                if (!validateDate()) {
                    $('#tanggal_seminar').addClass('is-invalid');
                    isValid = false;
                }
                
                return isValid;
            }
            
            // Remove validation classes on input
            $('input, select, textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Set default time to office hours
            const now = new Date();
            const currentHour = now.getHours();
            
            if (currentHour < 8) {
                $('#jam_seminar').val('08:00');
            } else if (currentHour < 10) {
                $('#jam_seminar').val('10:00');
            } else if (currentHour < 13) {
                $('#jam_seminar').val('13:00');
            } else {
                $('#jam_seminar').val('14:00');
            }
        });
    </script>
</body>
</html>