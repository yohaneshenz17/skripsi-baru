<?php
// File: application/views/mahasiswa/seminar_skripsi/resubmit.php
// SIMPLE RESUBMIT FORM
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-redo"></i> Ajukan Ulang Seminar Skripsi
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($rejection_reason) && $rejection_reason): ?>
                        <!-- Alasan Penolakan -->
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan Sebelumnya:</h5>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Petunjuk:</strong> Perbaiki dokumen dan keterangan Anda sesuai dengan feedback yang diberikan, 
                        kemudian ajukan kembali seminar skripsi.
                    </div>
                    
                    <!-- Current Seminar Info -->
                    <?php if (isset($seminar)): ?>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6><i class="fas fa-info"></i> Informasi Pengajuan Sebelumnya</h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small><strong>Judul:</strong></small><br>
                                        <small><?= htmlspecialchars($seminar->proposal_judul ?? 'N/A') ?></small>
                                    </div>
                                    <div class="col-sm-6">
                                        <small><strong>Tanggal Pengajuan:</strong></small><br>
                                        <small><?= date('d F Y', strtotime($seminar->created_at)) ?></small>
                                    </div>
                                </div>
                                <?php if (!empty($seminar->file_skripsi)): ?>
                                    <div class="mt-2">
                                        <small><strong>File Sebelumnya:</strong></small><br>
                                        <small class="text-muted"><?= htmlspecialchars($seminar->file_skripsi) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Resubmit Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-edit"></i> Form Pengajuan Ulang</h5>
                        </div>
                        <div class="card-body">
                            <?= form_open_multipart('mahasiswa/seminar_skripsi/resubmit/' . ($seminar->id ?? ''), ['id' => 'form-resubmit']) ?>
                            
                            <div class="form-group">
                                <label for="keterangan">
                                    <i class="fas fa-comment-dots"></i> Keterangan Perbaikan 
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="keterangan" 
                                          name="keterangan" 
                                          rows="5" 
                                          maxlength="500" 
                                          placeholder="Jelaskan perbaikan yang telah Anda lakukan berdasarkan feedback dosen..."
                                          required></textarea>
                                <small class="text-muted">
                                    Jelaskan secara detail perbaikan yang telah dilakukan. Maksimal 500 karakter.
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="file_skripsi">
                                    <i class="fas fa-file-pdf"></i> File Skripsi Terbaru
                                    <small class="text-muted">(Opsional - biarkan kosong jika tidak ada perubahan file)</small>
                                </label>
                                <input type="file" 
                                       class="form-control-file" 
                                       id="file_skripsi" 
                                       name="file_skripsi" 
                                       accept=".pdf,.doc,.docx">
                                <small class="text-muted">
                                    Format: PDF, DOC, DOCX. Maksimal 5MB. 
                                    <strong>Upload hanya jika ada perubahan pada file skripsi.</strong>
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="confirm-resubmit" required>
                                    <label class="custom-control-label" for="confirm-resubmit">
                                        Saya menyatakan bahwa perbaikan telah dilakukan sesuai dengan feedback dosen
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-redo"></i> 
                                    Ajukan Ulang Seminar Skripsi
                                </button>
                                <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                                
                                <div class="mt-2">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Setelah pengajuan ulang, dosen pembimbing akan mendapat notifikasi untuk review kembali.
                                    </small>
                                </div>
                            </div>
                            
                            <?= form_close() ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple JavaScript -->
<script>
$(document).ready(function() {
    
    // File upload validation (optional file)
    $('#file_skripsi').change(function() {
        var file = this.files[0];
        if (file) {
            var fileSize = file.size;
            var maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileSize > maxSize) {
                alert('File terlalu besar! Maksimal 5MB.');
                $(this).val('');
                return;
            }
            
            var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Format file tidak diizinkan! Gunakan PDF, DOC, atau DOCX.');
                $(this).val('');
                return;
            }
        }
    });
    
    // Character counter for textarea
    $('#keterangan').on('input', function() {
        var current = $(this).val().length;
        var max = 500;
        var remaining = max - current;
        
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, max));
            remaining = 0;
        }
        
        // Show character count
        var counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            counter = $('<small class="char-counter text-muted"></small>');
            $(this).after(counter);
        }
        counter.text(remaining + ' karakter tersisa');
        
        if (remaining < 50) {
            counter.removeClass('text-muted').addClass('text-warning');
        } else {
            counter.removeClass('text-warning').addClass('text-muted');
        }
    });
    
    // Form submission validation
    $('#form-resubmit').submit(function(e) {
        var keteranganInput = $('#keterangan');
        var confirmCheckbox = $('#confirm-resubmit');
        
        if (keteranganInput.val().trim().length < 20) {
            e.preventDefault();
            alert('Keterangan perbaikan minimal 20 karakter untuk memberikan penjelasan yang memadai!');
            keteranganInput.focus();
            return false;
        }
        
        if (!confirmCheckbox.is(':checked')) {
            e.preventDefault();
            alert('Harap centang konfirmasi bahwa perbaikan telah dilakukan!');
            confirmCheckbox.focus();
            return false;
        }
        
        // Confirm submission
        if (!confirm('Apakah Anda yakin ingin mengajukan ulang seminar skripsi? Pastikan perbaikan sudah sesuai feedback dosen.')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
        
        return true;
    });
    
});
</script>