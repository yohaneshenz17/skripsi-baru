<?php
/**
 * View Form Pengajuan Permohonan Izin Penelitian - Mahasiswa
 * File: application/views/mahasiswa/penelitian/form_ajukan.php
 * 
 * Form untuk mengajukan permohonan izin penelitian dengan validasi syarat
 * Mengikuti design pattern existing dengan Bootstrap 4
 */
?>

<!-- Header dengan Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('mahasiswa/penelitian') ?>">Penelitian</a>
                </li>
                <li class="breadcrumb-item active">Ajukan Permohonan</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <?= $this->session->flashdata('error') ?>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-2"></i>
    <?= $this->session->flashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Form Pengajuan -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-plus mr-2"></i>Form Permohonan Izin Penelitian
                </h6>
            </div>
            <div class="card-body">
                <?= form_open_multipart('mahasiswa/penelitian/ajukan', ['id' => 'formPenelitian']) ?>
                
                <!-- Data Mahasiswa -->
                <div class="form-section mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-user mr-2"></i>Data Mahasiswa
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_mahasiswa">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_mahasiswa" name="nama_mahasiswa" 
                                       value="<?= strtoupper($mahasiswa->nama) ?>" required>
                                <small class="text-muted">Nama akan otomatis dalam huruf kapital</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nim">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nim" name="nim" 
                                       value="<?= $mahasiswa->nim ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="semester">Semester <span class="text-danger">*</span></label>
                                <select class="form-control" id="semester" name="semester" required>
                                    <option value="">Pilih Semester</option>
                                    <option value="VII">VII (Tujuh)</option>
                                    <option value="VIII">VIII (Delapan)</option>
                                    <option value="IX">IX (Sembilan)</option>
                                    <option value="X">X (Sepuluh)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="program_studi">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-control" id="program_studi" name="program_studi" required>
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Pendidikan Keagamaan Katolik" 
                                            <?= $mahasiswa->nama_prodi == 'Pendidikan Keagamaan Katolik' ? 'selected' : '' ?>>
                                        Pendidikan Keagamaan Katolik
                                    </option>
                                    <option value="Pendidikan Guru Sekolah Dasar"
                                            <?= $mahasiswa->nama_prodi == 'Pendidikan Guru Sekolah Dasar' ? 'selected' : '' ?>>
                                        Pendidikan Guru Sekolah Dasar
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Proposal -->
                <div class="form-section mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-file-alt mr-2"></i>Data Proposal & Penelitian
                    </h6>
                    <div class="form-group">
                        <label for="judul_skripsi_terbaru">Judul Skripsi Terbaru <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="judul_skripsi_terbaru" name="judul_skripsi_terbaru" 
                                  rows="3" required><?= $proposal->judul ?></textarea>
                        <small class="text-muted">Judul setelah revisi seminar proposal (jika ada perubahan)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="tempat_penelitian">Tempat/Lokasi Penelitian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tempat_penelitian" name="tempat_penelitian" 
                               placeholder="Contoh: Keuskupan Agung Jakarta, SD Katolik St. Yakobus, dll" required>
                        <small class="text-muted">Sebutkan nama instansi/wilayah yang akan menjadi lokasi penelitian</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai_penelitian">Tanggal Mulai Penelitian <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai_penelitian" 
                                       name="tanggal_mulai_penelitian" min="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_selesai_penelitian">Tanggal Selesai Penelitian <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai_penelitian" 
                                       name="tanggal_selesai_penelitian" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload File -->
                <div class="form-section mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-upload mr-2"></i>Dokumen Pendukung
                    </h6>
                    <div class="form-group">
                        <label for="file_proposal_revisi">Proposal Revisi (Opsional)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_proposal_revisi" 
                                   name="file_proposal_revisi" accept=".pdf,.doc,.docx">
                            <label class="custom-file-label" for="file_proposal_revisi">Pilih file...</label>
                        </div>
                        <small class="text-muted">
                            Upload proposal yang sudah direvisi (jika ada). Format: PDF, DOC, DOCX. Maksimal 2MB.
                        </small>
                    </div>
                </div>

                <!-- Info Pembimbing -->
                <div class="form-section mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">
                        <i class="fas fa-user-tie mr-2"></i>Dosen Pembimbing
                    </h6>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong><?= $proposal->nama_pembimbing ?></strong><br>
                        Permohonan akan dikirim ke dosen pembimbing untuk review dan persetujuan.
                    </div>
                </div>

                <!-- Buttons -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-paper-plane mr-2"></i>Ajukan Permohonan
                    </button>
                    <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status Syarat -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-check-circle mr-2"></i>Syarat Terpenuhi
                </h6>
            </div>
            <div class="card-body">
                <?php foreach ($eligibility['requirements'] as $req_key => $requirement): ?>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check text-success mr-2"></i>
                    <small><?= ucwords(str_replace('_', ' ', $req_key)) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Workflow Info -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-body">
                <h6 class="text-info">
                    <i class="fas fa-info-circle mr-2"></i>Alur Proses
                </h6>
                <ol class="pl-3 mb-0">
                    <li><small>Anda mengajukan permohonan</small></li>
                    <li><small>Dosen pembimbing review & approve</small></li>
                    <li><small>Staf menerbitkan surat izin</small></li>
                    <li><small>Anda download surat izin</small></li>
                </ol>
            </div>
        </div>

        <!-- Tips -->
        <div class="card shadow border-left-warning">
            <div class="card-body">
                <h6 class="text-warning">
                    <i class="fas fa-lightbulb mr-2"></i>Tips Pengajuan
                </h6>
                <ul class="pl-3 mb-0">
                    <li><small>Pastikan data lengkap dan benar</small></li>
                    <li><small>Koordinasi dengan tempat penelitian terlebih dahulu</small></li>
                    <li><small>Siapkan jadwal penelitian yang realistis</small></li>
                    <li><small>Upload proposal revisi jika ada perubahan</small></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName || 'Pilih file...');
    });

    // Validasi tanggal
    $('#tanggal_mulai_penelitian').on('change', function() {
        let startDate = new Date($(this).val());
        let minEndDate = new Date(startDate);
        minEndDate.setDate(minEndDate.getDate() + 1);
        
        $('#tanggal_selesai_penelitian').attr('min', formatDate(minEndDate));
        
        // Reset end date if it's before start date
        let endDate = new Date($('#tanggal_selesai_penelitian').val());
        if (endDate <= startDate) {
            $('#tanggal_selesai_penelitian').val('');
        }
    });

    function formatDate(date) {
        let day = ('0' + date.getDate()).slice(-2);
        let month = ('0' + (date.getMonth() + 1)).slice(-2);
        let year = date.getFullYear();
        return year + '-' + month + '-' + day;
    }

    // Form validation
    $('#formPenelitian').on('submit', function(e) {
        let valid = true;
        let errors = [];

        // Validasi tanggal
        let startDate = new Date($('#tanggal_mulai_penelitian').val());
        let endDate = new Date($('#tanggal_selesai_penelitian').val());
        
        if (endDate <= startDate) {
            valid = false;
            errors.push('Tanggal selesai harus lebih dari tanggal mulai penelitian');
        }

        // Validasi file size jika ada upload
        let fileInput = $('#file_proposal_revisi')[0];
        if (fileInput.files.length > 0) {
            let fileSize = fileInput.files[0].size / 1024 / 1024; // Convert to MB
            if (fileSize > 2) {
                valid = false;
                errors.push('Ukuran file tidak boleh lebih dari 2MB');
            }
        }

        if (!valid) {
            e.preventDefault();
            alert('Error:\n' + errors.join('\n'));
            return false;
        }

        // Show loading state
        $('#btnSubmit').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...'
        );
    });

    // Auto uppercase nama
    $('#nama_mahasiswa').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
});
</script>

<style>
.form-section {
    position: relative;
}

.form-section h6 {
    color: #5a5c69;
    font-weight: 600;
}

.custom-file-label.selected {
    color: #495057;
}

.alert-info {
    border-left: 4px solid #36b9cc;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.text-danger {
    color: #e74a3b !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-section h6 {
        font-size: 14px;
    }
}
</style>