<?php
// =================================================================
// File: application/views/mahasiswa/seminar_proposal/form_ajukan.php
// =================================================================
?>

<!-- Form Pengajuan Seminar Proposal -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0"><?= $title ?></h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>"><i class="ni ni-tv-2"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_proposal') ?>">Seminar Proposal</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= $is_edit ? 'Edit' : 'Ajukan' ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" class="btn btn-sm btn-neutral">
                        <i class="ni ni-bold-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-8 order-xl-1">
            <!-- Form Card -->
            <div class="card bg-secondary shadow">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">
                                <i class="ni ni-send text-primary"></i>
                                <?= $is_edit ? 'Edit Pengajuan' : 'Form Pengajuan' ?>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    <div id="alert-container"></div>
                    
                    <!-- Form -->
                    <form id="form-seminar-proposal" enctype="multipart/form-data">
                        <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                        <?php if ($existing_seminar): ?>
                        <input type="hidden" name="existing_id" value="<?= $existing_seminar->id ?>">
                        <?php endif; ?>
                        
                        <h6 class="heading-small text-muted mb-4">INFORMASI PROPOSAL</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Judul Proposal</label>
                                        <div class="input-group input-group-alternative">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="ni ni-book-bookmark"></i></span>
                                            </div>
                                            <input class="form-control" type="text" 
                                                   value="<?= $proposal->judul ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Pembimbing</label>
                                        <div class="input-group input-group-alternative">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="ni ni-single-02"></i></span>
                                            </div>
                                            <input class="form-control" type="text" 
                                                   value="<?= $proposal->nama_pembimbing ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Email Pembimbing</label>
                                        <div class="input-group input-group-alternative">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                            </div>
                                            <input class="form-control" type="email" 
                                                   value="<?= $proposal->email_pembimbing ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="heading-small text-muted mb-4">UPLOAD FILE PROPOSAL</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if ($existing_seminar && $existing_seminar->file_proposal): ?>
                                    <div class="alert alert-info" role="alert">
                                        <strong><i class="ni ni-archive-2"></i> File Saat Ini:</strong>
                                        <a href="<?= base_url('uploads/seminar_proposal/' . $existing_seminar->file_proposal) ?>" 
                                           target="_blank" class="text-primary ml-2">
                                            <i class="ni ni-cloud-download-95"></i> Download File
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label class="form-control-label">
                                            File Proposal Seminar <span class="text-danger">*</span>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="file_proposal_seminar" 
                                                   name="file_proposal_seminar" accept=".pdf,.doc,.docx" 
                                                   <?= !$existing_seminar ? 'required' : '' ?>>
                                            <label class="custom-file-label" for="file_proposal_seminar">
                                                <?= $existing_seminar ? 'Upload file baru (opsional)' : 'Pilih file proposal...' ?>
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="ni ni-notification-70"></i>
                                            Format: PDF, DOC, DOCX | Maksimal: 1MB
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="heading-small text-muted mb-4">KETERANGAN TAMBAHAN</h6>
                        <div class="pl-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Keterangan Tambahan</label>
                                <textarea class="form-control form-control-alternative" 
                                          name="keterangan_tambahan" rows="4" 
                                          placeholder="Tuliskan keterangan tambahan jika ada..."><?= $existing_seminar->keterangan_mahasiswa ?? '' ?></textarea>
                                <small class="form-text text-muted">
                                    Opsional: Informasi tambahan yang ingin disampaikan kepada pembimbing
                                </small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                                <i class="ni ni-send"></i>
                                <span id="btn-text"><?= $is_edit ? 'Update Pengajuan' : 'Ajukan Seminar Proposal' ?></span>
                                <i class="fa fa-spinner fa-spin d-none" id="btn-spinner"></i>
                            </button>
                            <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" class="btn btn-secondary btn-lg ml-2">
                                <i class="ni ni-bold-left"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Information -->
        <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
            <!-- Syarat Card -->
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Syarat & Ketentuan</h3>
                </div>
                <div class="card-body">
                    <h6 class="heading-small text-muted mb-2">SYARAT PENGAJUAN</h6>
                    <ul class="list-unstyled">
                        <li class="py-2">
                            <div class="d-flex align-items-center">
                                <i class="ni ni-check-bold text-success mr-3"></i>
                                <span>Minimal 8 jurnal bimbingan tervalidasi</span>
                            </div>
                        </li>
                        <li class="py-2">
                            <div class="d-flex align-items-center">
                                <i class="ni ni-check-bold text-success mr-3"></i>
                                <span>Proposal sudah disetujui pembimbing</span>
                            </div>
                        </li>
                        <li class="py-2">
                            <div class="d-flex align-items-center">
                                <i class="ni ni-check-bold text-success mr-3"></i>
                                <span>File proposal dalam format PDF/DOC/DOCX</span>
                            </div>
                        </li>
                        <li class="py-2">
                            <div class="d-flex align-items-center">
                                <i class="ni ni-check-bold text-success mr-3"></i>
                                <span>Ukuran file maksimal 1MB</span>
                            </div>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="heading-small text-muted mb-2">STATUS SAAT INI</h6>
                    <div class="progress-wrapper">
                        <div class="progress-info">
                            <div class="progress-label">
                                <span>Jurnal Bimbingan</span>
                            </div>
                            <div class="progress-percentage">
                                <span><?= $syarat_jurnal['jurnal_validated_count'] ?>/8</span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?= $syarat_jurnal['eligible'] ? 'success' : 'warning' ?>" 
                                 role="progressbar" 
                                 style="width: <?= ($syarat_jurnal['jurnal_validated_count'] / 8) * 100 ?>%">
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($syarat_jurnal['eligible']): ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <strong><i class="ni ni-check-bold"></i> Siap untuk Pengajuan!</strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jurnal Validasi Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Jurnal Tervalidasi</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($jurnal_validasi)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Pertemuan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jurnal_validasi as $jurnal): ?>
                                <tr>
                                    <td><?= $jurnal->pertemuan_ke ?></td>
                                    <td><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                                    <td><span class="badge badge-success">Valid</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="ni ni-notification-70 fa-2x text-warning mb-2"></i>
                        <p class="text-muted">Belum ada jurnal yang divalidasi</p>
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-outline-primary btn-sm btn-block mt-3">
                        <i class="ni ni-books"></i> Lihat Semua Jurnal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // File input label update
    $('#file_proposal_seminar').on('change', function() {
        let fileName = this.files[0] ? this.files[0].name : 'Pilih file proposal...';
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Form submission
    $('#form-seminar-proposal').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $('#btn-submit');
        let btnText = $('#btn-text');
        let btnSpinner = $('#btn-spinner');
        
        // Disable submit button
        submitBtn.prop('disabled', true);
        btnText.text('Memproses...');
        btnSpinner.removeClass('d-none');
        
        // Clear previous alerts
        $('#alert-container').empty();
        
        // Submit form
        let formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url("mahasiswa/seminar_proposal/proses_pengajuan") ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    showAlert('danger', response.message, response.errors);
                } else {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.href = response.redirect || '<?= base_url("mahasiswa/seminar_proposal") ?>';
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showAlert('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false);
                btnText.text('<?= $is_edit ? "Update Pengajuan" : "Ajukan Seminar Proposal" ?>');
                btnSpinner.addClass('d-none');
            }
        });
    });
    
    function showAlert(type, message, errors = null) {
        let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        let iconClass = type === 'success' ? 'ni-check-bold' : 'ni-notification-70';
        
        let html = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <span class="alert-icon"><i class="ni ${iconClass}"></i></span>
                <span class="alert-text"><strong>${type === 'success' ? 'Berhasil!' : 'Error!'}</strong> ${message}</span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        if (errors) {
            html += '<ul class="mb-0">';
            $.each(errors, function(field, error) {
                html += `<li>${error}</li>`;
            });
            html += '</ul>';
        }
        
        $('#alert-container').html(html);
        
        // Scroll to alert
        $('html, body').animate({
            scrollTop: $('#alert-container').offset().top - 100
        }, 500);
    }
});
</script>