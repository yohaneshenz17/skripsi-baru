<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Seminar Skripsi - Enhanced Interface</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .badge-status { font-size: 0.9em; padding: 0.5em 1em; }
        .progress-indicator { height: 8px; border-radius: 4px; }
        .card-stats { transition: transform 0.2s; }
        .card-stats:hover { transform: translateY(-5px); }
        .form-section { border-left: 4px solid #667eea; background: #f8f9fa; }
        .action-buttons .btn { margin: 0 5px; }
        .turnitin-result { background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        
        <!-- Enhanced Header with Status -->
        <div class="row mb-4">
            <div class="col">
                <div class="card gradient-header text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h4 class="mb-1"><i class="fas fa-graduation-cap mr-2"></i>Detail Seminar Skripsi</h4>
                                <p class="mb-0">Mahasiswa Contoh 3 (12345677) - Review & Validasi Kaprodi</p>
                            </div>
                            <div class="col-4 text-right">
                                <span class="badge badge-warning badge-status">
                                    <i class="fas fa-clock mr-1"></i>MENUNGGU REVIEW
                                </span>
                                <div class="mt-2">
                                    <small>Progress Seminar: 50%</small>
                                    <div class="progress progress-indicator mt-1">
                                        <div class="progress-bar" style="width: 50%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- 1. VALIDASI TURNITIN SECTION -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-search mr-2"></i>
                            Validasi Plagiarisme Turnitin
                        </h5>
                    </div>
                    <div class="card-body form-section">
                        <form id="form-turnitin-validation">
                            <input type="hidden" name="seminar_id" value="12">
                            
                            <!-- File Upload Turnitin -->
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-file-upload mr-1"></i>
                                    Upload File Hasil Turnitin <span class="text-muted">(Opsional)</span>
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="file_turnitin" id="file_turnitin" accept=".pdf">
                                    <label class="custom-file-label" for="file_turnitin">Pilih file PDF...</label>
                                </div>
                                <small class="text-muted">Format: PDF, Maksimal: 5MB</small>
                            </div>
                            
                            <!-- Input Persentase -->
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-percentage mr-1"></i>
                                    Persentase Plagiarisme <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="plagiarism_percentage" 
                                           id="plagiarism_percentage" min="0" max="100" step="0.1" 
                                           placeholder="0.0" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div id="plagiarism-indicator" class="turnitin-result p-2 rounded" style="display: none;">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span id="plagiarism-status"></span>
                                            </div>
                                            <div>
                                                <span id="plagiarism-badge" class="badge"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Maksimal 30% untuk dapat disetujui
                                </small>
                            </div>
                            
                            <!-- Catatan Kaprodi -->
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-comment mr-1"></i>
                                    Catatan/Komentar <span class="text-muted">(Opsional)</span>
                                </label>
                                <textarea class="form-control" name="komentar_kaprodi" rows="3" 
                                          placeholder="Berikan catatan atau komentar untuk mahasiswa..."></textarea>
                            </div>
                            
                            <!-- Keputusan -->
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-gavel mr-1"></i>
                                    Keputusan Validasi <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" name="decision" id="approve" value="approved">
                                            <label class="custom-control-label text-success font-weight-bold" for="approve">
                                                <i class="fas fa-check-circle mr-1"></i> Setujui
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" name="decision" id="reject" value="rejected">
                                            <label class="custom-control-label text-danger font-weight-bold" for="reject">
                                                <i class="fas fa-times-circle mr-1"></i> Tolak
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 2. PENJADWALAN SECTION -->
                <div class="card shadow mb-4" id="penjadwalan-section" style="display: none;">
                    <div class="card-header bg-primary">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Penjadwalan Seminar Skripsi
                        </h5>
                    </div>
                    <div class="card-body form-section">
                        <form id="form-penjadwalan">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Tanggal Seminar <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" name="tanggal_seminar" 
                                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-clock mr-1"></i>
                                            Jam Seminar <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" class="form-control" name="jam_seminar" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            Tempat Seminar <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="tempat_seminar" 
                                               placeholder="e.g. Ruang Sidang A" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 3. PENUNJUKAN DOSEN PENGUJI SECTION -->
                <div class="card shadow mb-4" id="penguji-section" style="display: none;">
                    <div class="card-header bg-info">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-users mr-2"></i>
                            Penunjukan Dosen Penguji
                        </h5>
                    </div>
                    <div class="card-body form-section">
                        <form id="form-penguji">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Auto Rekomendasi:</strong> Sistem merekomendasikan dosen penguji yang sama dengan seminar proposal sebelumnya.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            Dosen Penguji 1 <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" name="dosen_penguji1_id" required>
                                            <option value="">-- Pilih Dosen Penguji 1 --</option>
                                            <option value="1" selected>Dr. Ahmad Wijaya, M.Pd (Rekomendasi)</option>
                                            <option value="2">Dr. Siti Nurhaliza, S.Pd., M.Pd</option>
                                            <option value="3">Prof. Budi Santoso, M.A</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            Dosen Penguji 2 <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" name="dosen_penguji2_id" required>
                                            <option value="">-- Pilih Dosen Penguji 2 --</option>
                                            <option value="4" selected>Dr. Maria Magdalena, M.Pd (Rekomendasi)</option>
                                            <option value="5">Dr. Yohanes Pratama, S.Pd., M.Pd</option>
                                            <option value="6">Prof. Christina Wulandari, M.A</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Kebijakan STK Santo Yakobus:</strong> Dosen penguji tidak perlu konfirmasi kesediaan (langsung ditunjuk Kaprodi).
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="action-buttons text-center">
                            <button type="button" class="btn btn-success btn-lg" id="btn-submit">
                                <i class="fas fa-check mr-2"></i>
                                Simpan & Proses Validasi
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Kembali
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                
                <!-- Info Mahasiswa -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Informasi Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td>Mahasiswa Contoh 3</td>
                            </tr>
                            <tr>
                                <td><strong>NIM:</strong></td>
                                <td>12345677</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>danielpuraka@student.stkyakobus.ac.id</td>
                            </tr>
                            <tr>
                                <td><strong>Pembimbing:</strong></td>
                                <td>Yohanes Hendro Pranyoto, S.Pd., M.Pd.</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Progress Workflow -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-tasks mr-2"></i>Progress Workflow</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fas fa-check" style="font-size: 12px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 ml-3">
                                <h6 class="mb-0">Rekomendasi Pembimbing</h6>
                                <small class="text-muted">Disetujui pembimbing</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fas fa-clock" style="font-size: 12px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 ml-3">
                                <h6 class="mb-0">Review Kaprodi</h6>
                                <small class="text-warning">Sedang diproses</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fas fa-calendar" style="font-size: 12px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 ml-3">
                                <h6 class="mb-0">Penjadwalan</h6>
                                <small class="text-muted">Menunggu approval</small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fas fa-graduation-cap" style="font-size: 12px;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3 ml-3">
                                <h6 class="mb-0">Pelaksanaan Seminar</h6>
                                <small class="text-muted">Menunggu jadwal</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Quick Stats</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="card card-stats bg-gradient-primary text-white">
                                    <div class="card-body">
                                        <h3 class="mb-0">50%</h3>
                                        <span class="small">Progress</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card card-stats bg-gradient-success text-white">
                                    <div class="card-body">
                                        <h3 class="mb-0">15</h3>
                                        <span class="small">Bimbingan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // File input label update
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Pilih file PDF...');
            });

            // Plagiarism percentage indicator
            $('#plagiarism_percentage').on('input', function() {
                var percentage = parseFloat($(this).val());
                var indicator = $('#plagiarism-indicator');
                var status = $('#plagiarism-status');
                var badge = $('#plagiarism-badge');

                if (!isNaN(percentage)) {
                    indicator.show();
                    
                    if (percentage <= 30) {
                        status.html('<i class="fas fa-check-circle text-success mr-1"></i>Memenuhi syarat untuk disetujui');
                        badge.removeClass().addClass('badge badge-success').text(percentage + '%');
                    } else {
                        status.html('<i class="fas fa-exclamation-triangle text-danger mr-1"></i>Melebihi batas maksimal (30%)');
                        badge.removeClass().addClass('badge badge-danger').text(percentage + '%');
                    }
                } else {
                    indicator.hide();
                }
            });

            // Decision change handler
            $('input[name="decision"]').on('change', function() {
                var decision = $(this).val();
                
                if (decision === 'approved') {
                    $('#penjadwalan-section').slideDown();
                    $('#penguji-section').slideDown();
                } else {
                    $('#penjadwalan-section').slideUp();
                    $('#penguji-section').slideUp();
                }
            });

            // Form submission
            $('#btn-submit').on('click', function() {
                var decision = $('input[name="decision"]:checked').val();
                var percentage = $('#plagiarism_percentage').val();
                
                if (!decision) {
                    alert('Silakan pilih keputusan validasi terlebih dahulu!');
                    return;
                }
                
                if (!percentage) {
                    alert('Persentase plagiarisme harus diisi!');
                    return;
                }

                if (decision === 'approved') {
                    var tanggal = $('input[name="tanggal_seminar"]').val();
                    var jam = $('input[name="jam_seminar"]').val();
                    var tempat = $('input[name="tempat_seminar"]').val();
                    var penguji1 = $('select[name="dosen_penguji1_id"]').val();
                    var penguji2 = $('select[name="dosen_penguji2_id"]').val();
                    
                    if (!tanggal || !jam || !tempat || !penguji1 || !penguji2) {
                        alert('Semua field penjadwalan dan penunjukan dosen penguji harus diisi untuk persetujuan!');
                        return;
                    }
                }

                // Simulate form submission
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
                
                setTimeout(function() {
                    alert('Validasi berhasil diproses!\n\nNotifikasi email akan dikirim ke:\n- Mahasiswa\n- Dosen Pembimbing\n- Dosen Penguji 1 & 2\n- Staf Administrasi');
                    // window.location.href = 'kaprodi/seminar_skripsi';
                }, 2000);
            });
        });
    </script>
</body>
</html>