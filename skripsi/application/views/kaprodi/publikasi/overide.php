<?php
// Mulai output buffering
ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            Override Publikasi Tugas Akhir
                        </h3>
                        <p class="text-white-75 mb-0">
                            Kewenangan khusus Kaprodi untuk menangani kasus darurat atau situasi khusus
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('kaprodi/publikasi/detail/' . $publikasi->id) ?>" 
                           class="btn btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="row mb-3">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi') ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/publikasi') ?>">Publikasi</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/publikasi/detail/' . $publikasi->id) ?>">Detail</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Override</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <!-- Warning Alert -->
        <div class="alert alert-warning">
            <h5><i class="fa fa-shield-alt"></i> Perhatian Penting!</h5>
            <p class="mb-0">
                <strong>Override</strong> adalah tindakan khusus yang hanya dapat dilakukan oleh Kaprodi dalam situasi darurat 
                atau kasus yang memerlukan intervensi khusus. Semua tindakan override akan dicatat dalam log sistem 
                dan dapat diaudit oleh Admin. Pastikan Anda memahami konsekuensi dari setiap tindakan.
            </p>
        </div>

        <!-- Main Override Form Card -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">Form Override Decision</h3>
            </div>
            <div class="card-body">
                
                <!-- Info Publikasi -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Informasi Publikasi:</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Mahasiswa:</strong></td>
                                <td><?= $publikasi->nama_mahasiswa ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM:</strong></td>
                                <td><?= $publikasi->nim ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pembimbing:</strong></td>
                                <td><?= $publikasi->nama_pembimbing ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status Saat Ini:</h6>
                        <div class="mb-3">
                            <?php
                            $status_badges = [
                                'draft' => 'badge-secondary',
                                'submitted' => 'badge-info',
                                'review_pembimbing' => 'badge-warning',
                                'approved_pembimbing' => 'badge-primary',
                                'review_staf' => 'badge-primary',
                                'completed' => 'badge-success',
                                'rejected' => 'badge-danger'
                            ];
                            
                            $status_text = [
                                'draft' => 'Draft',
                                'submitted' => 'Diajukan ke Dosen',
                                'review_pembimbing' => 'Review Dosen',
                                'approved_pembimbing' => 'Approved Dosen',
                                'review_staf' => 'Review Staf',
                                'completed' => 'Selesai',
                                'rejected' => 'Ditolak'
                            ];
                            
                            $badge_class = $status_badges[$publikasi->status] ?? 'badge-secondary';
                            $status_display = $status_text[$publikasi->status] ?? 'Unknown';
                            ?>
                            <span class="badge badge-lg <?= $badge_class ?>"><?= $status_display ?></span>
                        </div>
                        
                        <p class="text-sm">
                            <strong>Tanggal Pengajuan:</strong><br>
                            <?= date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan ?? $publikasi->created_at)) ?>
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Judul Tugas Akhir:</label>
                    <p class="form-control-plaintext"><?= $publikasi->judul_proposal ?></p>
                </div>

                <hr class="my-4">

                <!-- Override Form -->
                <form method="POST" action="<?= base_url('kaprodi/publikasi/override/' . $publikasi->id) ?>" 
                      id="override-form">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    Tindakan Override <span class="text-danger">*</span>
                                </label>
                                <select name="override_action" class="form-control" required id="override-action">
                                    <option value="">-- Pilih Tindakan Override --</option>
                                    <option value="approve">Force Approve (Setujui Langsung)</option>
                                    <option value="reject">Force Reject (Tolak Langsung)</option>
                                    <option value="reset">Reset ke Status Draft</option>
                                    <option value="skip_review">Skip Review Process</option>
                                </select>
                                <small class="form-text text-muted">
                                    Pilih tindakan override yang sesuai dengan situasi
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tingkat Prioritas</label>
                                <select name="priority_level" class="form-control">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                                <small class="form-text text-muted">
                                    Tingkat prioritas untuk tracking dan audit
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Action Description -->
                    <div class="alert alert-info" id="action-description" style="display: none;">
                        <h6 class="alert-heading">Deskripsi Tindakan:</h6>
                        <p class="mb-0" id="action-description-text"></p>
                    </div>

                    <div class="form-group">
                        <label class="form-control-label">
                            Komentar/Alasan Override <span class="text-danger">*</span>
                        </label>
                        <textarea name="komentar_kaprodi" class="form-control" rows="4" 
                                  placeholder="Jelaskan secara detail alasan melakukan override, situasi yang memerlukan intervensi, dan justifikasi keputusan..." 
                                  required minlength="20" maxlength="1000"></textarea>
                        <small class="form-text text-muted">
                            <span class="text-danger">*</span> Komentar ini akan dicatat dalam log sistem dan dapat dilihat oleh Admin. 
                            Minimal 20 karakter, maksimal 1000 karakter.
                        </small>
                        <div class="invalid-feedback">
                            Komentar harus diisi minimal 20 karakter
                        </div>
                    </div>

                    <!-- Confirmation -->
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm-override" 
                                   name="confirm_override" required>
                            <label class="custom-control-label" for="confirm-override">
                                <strong>Saya memahami dan bertanggung jawab atas tindakan override ini.</strong>
                                Saya telah mempertimbangkan konsekuensi dan memahami bahwa tindakan ini akan dicatat 
                                dalam sistem audit.
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?= base_url('kaprodi/publikasi/detail/' . $publikasi->id) ?>" 
                               class="btn btn-secondary btn-block">
                                <i class="fa fa-times"></i> Batal
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-warning btn-block" id="submit-override">
                                <i class="fa fa-shield-alt"></i> Eksekusi Override
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Override History (placeholder) -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-history"></i> Riwayat Override
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-4">
                    <i class="fa fa-history fa-2x mb-2"></i>
                    <p class="mb-0">Belum ada riwayat override untuk publikasi ini.</p>
                    <small>Override history akan ditampilkan di sini setelah ada tindakan override</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>

<script>
$(document).ready(function() {
    // Action descriptions
    const actionDescriptions = {
        'approve': 'Menyetujui publikasi langsung tanpa melalui proses review normal. Publikasi akan langsung berstatus "completed".',
        'reject': 'Menolak publikasi langsung. Mahasiswa harus mengajukan ulang dari awal.',
        'reset': 'Mengembalikan status publikasi ke draft. Mahasiswa dapat memperbaiki dan mengajukan kembali.',
        'skip_review': 'Melewati tahap review tertentu dan langsung ke tahap berikutnya.'
    };
    
    // Show action description
    $('#override-action').on('change', function() {
        const action = $(this).val();
        if (action && actionDescriptions[action]) {
            $('#action-description-text').text(actionDescriptions[action]);
            $('#action-description').show();
        } else {
            $('#action-description').hide();
        }
    });
    
    // Form validation
    $('#override-form').on('submit', function(e) {
        const action = $('#override-action').val();
        const comment = $('textarea[name="komentar_kaprodi"]').val();
        const confirmed = $('#confirm-override').is(':checked');
        
        // Basic validation
        if (!action || !comment || !confirmed) {
            e.preventDefault();
            alert('Harap lengkapi semua field yang wajib diisi.');
            return false;
        }
        
        // Comment length validation
        if (comment.length < 20) {
            e.preventDefault();
            alert('Komentar harus minimal 20 karakter.');
            $('textarea[name="komentar_kaprodi"]').focus();
            return false;
        }
        
        // Final confirmation
        const actionText = actionDescriptions[action] || action;
        const confirmMessage = `Yakin ingin melakukan override "${action}"?\n\n` +
                              `Deskripsi: ${actionText}\n\n` +
                              `Tindakan ini akan dicatat dalam log sistem dan tidak dapat dibatalkan.`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
        
        // Disable submit button to prevent double submission
        $('#submit-override').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
        
        return true;
    });
    
    // Character counter for textarea
    $('textarea[name="komentar_kaprodi"]').on('input', function() {
        const current = $(this).val().length;
        const max = 1000;
        const min = 20;
        
        let counterText = `${current}/${max} karakter`;
        let counterClass = 'text-muted';
        
        if (current < min) {
            counterText += ` (minimal ${min})`;
            counterClass = 'text-danger';
        } else if (current > max * 0.9) {
            counterClass = 'text-warning';
        }
        
        // Update or create counter
        let counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            counter = $('<small class="char-counter"></small>');
            $(this).after(counter);
        }
        
        counter.removeClass('text-muted text-danger text-warning').addClass(counterClass).text(counterText);
    });
});
</script>