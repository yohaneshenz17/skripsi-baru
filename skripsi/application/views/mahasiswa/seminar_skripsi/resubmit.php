<?php
// File: application/views/mahasiswa/seminar_skripsi/resubmit.php
// ENHANCED RESUBMIT FORM - Support judul skripsi + 2 file uploads
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%); color: #2d3436;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-redo mr-2"></i> 
                        Ajukan Ulang Seminar Skripsi
                    </h6>
                    <div class="card-tools float-right">
                        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($rejection_reason) && $rejection_reason): ?>
                        <!-- Alasan Penolakan -->
                        <div class="alert alert-warning border-left-warning">
                            <h6><i class="fas fa-exclamation-triangle mr-2"></i>Alasan Penolakan Sebelumnya:</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info border-left-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Petunjuk Pengajuan Ulang:</h6>
                        <ul class="mb-0">
                            <li>Perbaiki dokumen dan keterangan sesuai dengan feedback dosen</li>
                            <li>Anda dapat mengubah judul skripsi jika diperlukan</li>
                            <li>Upload file baru hanya jika ada perubahan</li>
                            <li>Berikan keterangan detail perbaikan yang telah dilakukan</li>
                        </ul>
                    </div>
                    
                    <!-- Current Seminar Info -->
                    <?php if (isset($seminar)): ?>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6><i class="fas fa-info mr-2"></i>Informasi Pengajuan Sebelumnya</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small><strong>Judul Skripsi:</strong></small><br>
                                        <small><?= htmlspecialchars($seminar->judul_skripsi ?: $seminar->proposal_judul ?: 'N/A') ?></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small><strong>Tanggal Pengajuan:</strong></small><br>
                                        <small><?= date('d F Y', strtotime($seminar->created_at)) ?></small>
                                    </div>
                                </div>
                                
                                <?php if (!empty($seminar->file_skripsi) || !empty($seminar->surat_keterangan_penelitian)): ?>
                                    <div class="mt-3">
                                        <small><strong>File Sebelumnya:</strong></small><br>
                                        <div class="btn-group-vertical btn-group-sm mt-1">
                                            <?php if (!empty($seminar->file_skripsi)): ?>
                                                <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" 
                                                   class="btn btn-outline-primary btn-sm mb-1" target="_blank">
                                                    <i class="fas fa-download mr-1"></i>File Skripsi Lama
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                                                <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/surat') ?>" 
                                                   class="btn btn-outline-success btn-sm" target="_blank">
                                                    <i class="fas fa-certificate mr-1"></i>Surat Penelitian Lama
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Enhanced Resubmit Form -->
                    <div class="card border-left-warning">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0"><i class="fas fa-edit mr-2"></i>Form Pengajuan Ulang</h6>
                        </div>
                        <div class="card-body">
                            <?= form_open_multipart('mahasiswa/seminar_skripsi/resubmit/' . ($seminar->id ?? ''), ['id' => 'form-resubmit']) ?>
                            
                            <!-- ENHANCED: Judul Skripsi (bisa diubah) -->
                            <div class="form-group">
                                <label for="judul_skripsi">
                                    <i class="fas fa-book mr-1"></i>
                                    Judul Skripsi (Perbarui jika ada perubahan)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="judul_skripsi" 
                                       name="judul_skripsi" 
                                       value="<?= htmlspecialchars($seminar->judul_skripsi ?: $seminar->proposal_judul ?: '') ?>"
                                       maxlength="250"
                                       placeholder="Perbarui judul skripsi jika ada perubahan berdasarkan masukan pembimbing">
                                <small class="text-muted">
                                    Kosongkan jika tidak ada perubahan judul. Maksimal 250 karakter.
                                </small>
                                <div class="text-right">
                                    <small class="text-muted"><span id="char-counter-judul">0</span>/250</small>
                                </div>
                            </div>

                            <!-- File Upload Section -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-upload mr-2"></i>
                                        Upload File (Opsional - jika ada perubahan)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- File Skripsi -->
                                    <div class="form-group">
                                        <label for="file_skripsi">
                                            <i class="fas fa-file-pdf mr-1"></i>
                                            File Skripsi Terbaru
                                            <small class="text-muted">(Upload jika ada perubahan)</small>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" 
                                                   class="custom-file-input" 
                                                   id="file_skripsi" 
                                                   name="file_skripsi" 
                                                   accept=".pdf,.doc,.docx">
                                            <label class="custom-file-label" for="file_skripsi">
                                                Pilih file skripsi yang sudah diperbaiki...
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            Format: PDF, DOC, DOCX. Maksimal 5MB. 
                                            <strong>Kosongkan jika tidak ada perubahan file.</strong>
                                        </small>
                                        <?php if (!empty($seminar->file_skripsi)): ?>
                                            <div class="mt-2">
                                                <small class="text-info">
                                                    <i class="fa fa-info-circle"></i> 
                                                    File saat ini: <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" target="_blank" class="text-info">Download file lama</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- ENHANCED: Surat Keterangan Penelitian -->
                                    <div class="form-group">
                                        <label for="surat_penelitian">
                                            <i class="fas fa-certificate mr-1"></i>
                                            Surat Keterangan Penelitian
                                            <small class="text-muted">(Upload jika ada perubahan)</small>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" 
                                                   class="custom-file-input" 
                                                   id="surat_penelitian" 
                                                   name="surat_penelitian" 
                                                   accept=".pdf,.jpg,.jpeg,.png">
                                            <label class="custom-file-label" for="surat_penelitian">
                                                Pilih surat keterangan penelitian yang baru...
                                            </label>
                                        </div>
                                        <small class="text-muted">
                                            Format: PDF, JPG, JPEG, PNG. Maksimal 3MB. 
                                            <strong>Kosongkan jika tidak ada perubahan.</strong>
                                        </small>
                                        <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                                            <div class="mt-2">
                                                <small class="text-info">
                                                    <i class="fa fa-info-circle"></i> 
                                                    File saat ini: <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/surat') ?>" target="_blank" class="text-info">Download file lama</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <strong>Catatan:</strong> Upload file baru hanya jika ada perubahan berdasarkan masukan dosen. 
                                        File lama akan otomatis terhapus jika Anda upload file baru.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Keterangan Perbaikan -->
                            <div class="form-group">
                                <label for="keterangan">
                                    <i class="fas fa-comment-dots mr-1"></i>
                                    Keterangan Perbaikan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="keterangan" 
                                          name="keterangan" 
                                          rows="5" 
                                          maxlength="500" 
                                          placeholder="Jelaskan secara detail perbaikan yang telah Anda lakukan berdasarkan feedback dosen pembimbing..."
                                          required></textarea>
                                <small class="text-muted">
                                    Jelaskan secara detail perbaikan yang telah dilakukan. Maksimal 500 karakter.
                                </small>
                                <div class="text-right">
                                    <small class="text-muted"><span id="char-counter-keterangan">0</span>/500</small>
                                </div>
                            </div>
                            
                            <!-- Konfirmasi -->
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm-resubmit" required>
                                    <label class="custom-control-label" for="confirm-resubmit">
                                        <strong>Saya menyatakan bahwa perbaikan telah dilakukan sesuai dengan feedback dosen pembimbing</strong>
                                    </label>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary btn-block btn-lg">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning btn-block btn-lg">
                                        <i class="fas fa-redo mr-2"></i>Ajukan Ulang
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Setelah pengajuan ulang, dosen pembimbing akan mendapat notifikasi untuk review kembali.
                                </small>
                            </div>
                            
                            <?= form_close() ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript -->
<script>
$(document).ready(function() {
    
    // Custom file input labels
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
    
    // File upload validation
    $('#file_skripsi').change(function() {
        var file = this.files[0];
        if (file) {
            var fileSize = file.size;
            var maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileSize > maxSize) {
                alert('File skripsi terlalu besar! Maksimal 5MB.');
                $(this).val('');
                return;
            }
            
            var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Format file skripsi tidak diizinkan! Gunakan PDF, DOC, atau DOCX.');
                $(this).val('');
                return;
            }
        }
    });

    $('#surat_penelitian').change(function() {
        var file = this.files[0];
        if (file) {
            var fileSize = file.size;
            var maxSize = 3 * 1024 * 1024; // 3MB
            
            if (fileSize > maxSize) {
                alert('File surat penelitian terlalu besar! Maksimal 3MB.');
                $(this).val('');
                return;
            }
            
            var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Format file surat penelitian tidak diizinkan! Gunakan PDF, JPG, JPEG, atau PNG.');
                $(this).val('');
                return;
            }
        }
    });
    
    // Character counters
    $('#judul_skripsi').on('input', function() {
        var current = $(this).val().length;
        var max = 250;
        var remaining = max - current;
        
        $('#char-counter-judul').text(current);
        
        if (current > max) {
            $(this).val($(this).val().substring(0, max));
            $('#char-counter-judul').text(max).addClass('text-danger');
        } else if (remaining < 50) {
            $('#char-counter-judul').removeClass('text-muted text-danger').addClass('text-warning');
        } else {
            $('#char-counter-judul').removeClass('text-warning text-danger').addClass('text-muted');
        }
    });

    $('#keterangan').on('input', function() {
        var current = $(this).val().length;
        var max = 500;
        var remaining = max - current;
        
        $('#char-counter-keterangan').text(current);
        
        if (current > max) {
            $(this).val($(this).val().substring(0, max));
            $('#char-counter-keterangan').text(max).addClass('text-danger');
        } else if (remaining < 50) {
            $('#char-counter-keterangan').removeClass('text-muted text-danger').addClass('text-warning');
        } else {
            $('#char-counter-keterangan').removeClass('text-warning text-danger').addClass('text-muted');
        }
    });
    
    // Initialize character counters
    $('#judul_skripsi').trigger('input');
    $('#keterangan').trigger('input');
    
    // Form submission validation
    $('#form-resubmit').submit(function(e) {
        var judul = $('#judul_skripsi').val().trim();
        var keterangan = $('#keterangan').val().trim();
        var confirmCheckbox = $('#confirm-resubmit');
        var errors = [];
        
        // Validate judul (optional but if filled, minimum length)
        if (judul.length > 0 && judul.length < 10) {
            errors.push('Judul skripsi minimal 10 karakter jika diisi');
        }
        
        // Validate keterangan (required)
        if (keterangan.length < 20) {
            errors.push('Keterangan perbaikan minimal 20 karakter untuk memberikan penjelasan yang memadai');
        }
        
        // Validate confirmation
        if (!confirmCheckbox.is(':checked')) {
            errors.push('Harap centang konfirmasi bahwa perbaikan telah dilakukan');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            alert('Error:\n- ' + errors.join('\n- '));
            return false;
        }
        
        // Confirm submission
        var confirmMessage = 'Apakah Anda yakin ingin mengajukan ulang seminar skripsi?\n\n';
        confirmMessage += 'Pastikan:\n';
        confirmMessage += '✓ Perbaikan sudah sesuai feedback dosen\n';
        if (judul !== '') confirmMessage += '✓ Judul skripsi sudah benar\n';
        if ($('#file_skripsi')[0].files.length > 0) confirmMessage += '✓ File skripsi sudah diperbaiki\n';
        if ($('#surat_penelitian')[0].files.length > 0) confirmMessage += '✓ Surat penelitian sudah diperbaiki\n';
        confirmMessage += '✓ Keterangan perbaikan sudah lengkap';
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...');
        
        return true;
    });
    
});
</script>

<style>
.custom-file-label.selected {
    color: #495057;
    background-color: #e9ecef;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.char-counter {
    font-weight: 500;
}

.text-danger.char-counter {
    font-weight: 700;
}

.btn-group-vertical .btn {
    text-align: left;
}

.card .card-body ul {
    padding-left: 1.5rem;
}

.alert ul {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }
    
    .card-tools {
        margin-top: 0.5rem;
    }
}
</style>