<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/pengajuan.php
 * FIXED VERSION - Pattern seperti Seminar Proposal
 * 
 * PERBAIKAN:
 * 1. Fixed undefined variable $is_edit dan $form_title
 * 2. Pattern form seperti seminar proposal (input judul baru)
 * 3. Upload file skripsi + surat penelitian
 * 4. Proper error handling untuk semua variables
 */

// ✅ FIXED: Ensure all variables are defined with default values
$is_edit = isset($is_edit) ? $is_edit : false;
$form_title = isset($form_title) ? $form_title : ($is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi');
$proposal = isset($proposal) ? $proposal : new stdClass();
$existing_seminar = isset($existing_seminar) ? $existing_seminar : null;
$eligibility = isset($eligibility) ? $eligibility : ['eligible' => true, 'errors' => []];
$current_judul = isset($current_judul) ? $current_judul : (isset($proposal->judul) ? $proposal->judul : '');
$judul_original = isset($judul_original) ? $judul_original : (isset($proposal->judul) ? $proposal->judul : '');
$form_action = isset($form_action) ? $form_action : base_url('mahasiswa/seminar_skripsi/submit_ajukan');

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

    <!-- Alert Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Proposal Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle mr-2"></i>
                Informasi Proposal
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Pembimbing:</strong> <?= htmlspecialchars($proposal->nama_pembimbing) ?></p>
                    <p><strong>Status Proposal:</strong> 
                        <span class="badge badge-success">Disetujui Kaprodi</span>
                    </p>
                </div>
                <div class="col-md-6">
                    <?php if ($is_edit && $existing_seminar): ?>
                        <p><strong>Status Seminar:</strong> 
                            <span class="badge badge-info"><?= ucfirst($existing_seminar->status) ?></span>
                        </p>
                        <p><strong>Tanggal Pengajuan:</strong> 
                            <?= date('d/m/Y H:i', strtotime($existing_seminar->tanggal_pengajuan)) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-edit mr-2"></i>
                <?= $is_edit ? 'Edit Data Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi' ?>
            </h6>
        </div>
        <div class="card-body">
            
            <?php if (!$is_edit): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Perhatian:</strong> Pastikan semua dokumen sudah siap sebelum mengajukan seminar skripsi. 
                Setelah diajukan, pengajuan akan direview oleh dosen pembimbing.
            </div>
            <?php endif; ?>

            <form method="post" action="<?= $form_action ?>" enctype="multipart/form-data" id="form-seminar-skripsi">
                <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                <input type="hidden" name="is_edit" value="<?= $is_edit ? '1' : '0' ?>">

                <!-- Judul Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-edit mr-2"></i>
                            Judul Skripsi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Catatan:</strong> Anda dapat mengubah judul jika selama penelitian ada perbaikan formulasi. 
                            Judul ini yang akan digunakan untuk undangan seminar, berita acara, dan dokumen resmi lainnya.
                        </div>
                        
                        <!-- Judul Original untuk Referensi -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-history mr-1"></i> Judul Proposal Original
                            </label>
                            <div class="form-control" style="background-color: #f8f9fe; border: 1px dashed #e3e6f0; min-height: 60px; padding: 15px;">
                                <?= htmlspecialchars($judul_original) ?>
                            </div>
                            <small class="text-muted">Judul dari proposal awal yang disetujui Kaprodi.</small>
                        </div>
                        
                        <!-- Field Edit Judul Skripsi -->
                        <div class="form-group">
                            <label for="judul_skripsi" class="form-label">
                                <i class="fas fa-edit mr-1"></i> Judul Skripsi <span class="text-danger">*</span>
                            </label>
                            <textarea name="judul_skripsi" id="judul_skripsi" class="form-control" 
                                      rows="4" maxlength="250" required 
                                      placeholder="Masukkan judul skripsi yang akan digunakan untuk seminar..."><?= htmlspecialchars($current_judul) ?></textarea>
                            
                            <div class="form-text">
                                <div class="d-flex justify-content-between">
                                    <span>Maksimal 250 karakter. Pastikan judul sudah sesuai hasil penelitian.</span>
                                    <span id="char-counter" class="text-muted">0/250</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Files Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Berkas
                        </h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- File Skripsi -->
                        <div class="form-group">
                            <label for="file_skripsi" class="form-label">
                                <i class="fas fa-file-pdf mr-1"></i> 
                                File Skripsi Lengkap <span class="text-danger">*</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file_skripsi" name="file_skripsi" 
                                       accept=".pdf,.doc,.docx" <?= !$is_edit ? 'required' : '' ?>>
                                <label class="custom-file-label" for="file_skripsi">
                                    <?php if ($is_edit && $existing_seminar && !empty($existing_seminar->file_skripsi)): ?>
                                        File saat ini: <?= htmlspecialchars($existing_seminar->file_skripsi) ?>
                                    <?php else: ?>
                                        Pilih file skripsi...
                                    <?php endif; ?>
                                </label>
                            </div>
                            <small class="text-muted">
                                Format: PDF, DOC, atau DOCX. Maksimal 5MB. 
                                <?= $is_edit ? 'Kosongkan jika tidak ingin mengubah file.' : '' ?>
                            </small>
                        </div>

                        <!-- Surat Keterangan Penelitian -->
                        <div class="form-group">
                            <label for="surat_penelitian" class="form-label">
                                <i class="fas fa-certificate mr-1"></i> 
                                Surat Keterangan Penelitian <span class="text-danger">*</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="surat_penelitian" name="surat_penelitian" 
                                       accept=".pdf,.jpg,.jpeg,.png" <?= !$is_edit ? 'required' : '' ?>>
                                <label class="custom-file-label" for="surat_penelitian">
                                    <?php if ($is_edit && $existing_seminar && !empty($existing_seminar->surat_penelitian)): ?>
                                        File saat ini: <?= htmlspecialchars($existing_seminar->surat_penelitian) ?>
                                    <?php else: ?>
                                        Pilih surat keterangan...
                                    <?php endif; ?>
                                </label>
                            </div>
                            <small class="text-muted">
                                Format: PDF, JPG, JPEG, atau PNG. Maksimal 3MB. Surat dari tempat penelitian.
                                <?= $is_edit ? 'Kosongkan jika tidak ingin mengubah file.' : '' ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Keterangan Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-comment mr-2"></i>
                            Keterangan Tambahan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="keterangan_mahasiswa" class="form-label">
                                Keterangan <span class="text-danger">*</span>
                            </label>
                            <textarea name="keterangan_mahasiswa" id="keterangan_mahasiswa" class="form-control" 
                                      rows="5" maxlength="1000" required 
                                      placeholder="Tuliskan keterangan tambahan, catatan khusus, atau hal penting yang perlu disampaikan terkait pengajuan seminar skripsi ini..."><?php if ($is_edit && $existing_seminar): echo htmlspecialchars($existing_seminar->keterangan_mahasiswa); endif; ?></textarea>
                            <small class="text-muted">
                                Minimal 10 karakter, maksimal 1000 karakter. 
                                Jelaskan progress penelitian, temuan penting, atau hal lain yang relevan.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <?= $is_edit ? 'Perbarui Pengajuan' : 'Ajukan Seminar Skripsi' ?>
                        </button>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary btn-lg ml-3 px-5">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Character counter for judul
    $('#judul_skripsi').on('input', function() {
        const length = $(this).val().length;
        $('#char-counter').text(length + '/250');
        
        if (length > 200) {
            $('#char-counter').addClass('text-warning');
        } else {
            $('#char-counter').removeClass('text-warning');
        }
        
        if (length >= 250) {
            $('#char-counter').addClass('text-danger');
        } else {
            $('#char-counter').removeClass('text-danger');
        }
    });
    
    // Initial character count
    $('#judul_skripsi').trigger('input');
    
    // File upload handling
    $('.custom-file-input').on('change', function() {
        const file = this.files[0];
        const label = $(this).next('.custom-file-label');
        
        if (file) {
            label.text(file.name);
            
            // Validate file size
            const maxSize = $(this).attr('id') === 'file_skripsi' ? 5242880 : 3145728; // 5MB or 3MB
            if (file.size > maxSize) {
                const maxSizeMB = maxSize / 1048576;
                alert(`Ukuran file terlalu besar. Maksimal ${maxSizeMB}MB.`);
                $(this).val('');
                label.text('Pilih file...');
            }
        }
    });
    
    // Form validation
    $('#form-seminar-skripsi').on('submit', function(e) {
        const judul = $('#judul_skripsi').val().trim();
        const keterangan = $('#keterangan_mahasiswa').val().trim();
        
        if (judul.length < 10) {
            alert('Judul skripsi minimal 10 karakter');
            e.preventDefault();
            return false;
        }
        
        if (keterangan.length < 10) {
            alert('Keterangan tambahan minimal 10 karakter');
            e.preventDefault();
            return false;
        }
        
        // Confirm submission
        const action = $('input[name="is_edit"]').val() === '1' ? 'memperbarui' : 'mengajukan';
        if (!confirm(`Apakah Anda yakin akan ${action} seminar skripsi ini?`)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>