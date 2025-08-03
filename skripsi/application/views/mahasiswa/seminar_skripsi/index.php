<?php
// File: application/views/mahasiswa/seminar_skripsi/index.php
// SIMPLE VIEW - No complex logic, no DataTables errors
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-presentation"></i> 
                        <?= $page_title ?? 'Seminar Skripsi' ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($error_message) && $error_message): ?>
                        <!-- Error Message -->
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_status ?? false): ?>
                        <!-- EXISTING SEMINAR - Show Status -->
                        <?php $status = $status_info ?? ['text' => 'Unknown', 'class' => 'secondary', 'progress' => 0]; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card border-<?= $status['class'] ?>">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle"></i> 
                                            Status Pengajuan Seminar Skripsi
                                        </h5>
                                        
                                        <div class="mb-3">
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-<?= $status['class'] ?>" 
                                                     role="progressbar" 
                                                     style="width: <?= $status['progress'] ?>%">
                                                    <?= $status['progress'] ?>%
                                                </div>
                                            </div>
                                            <small class="text-muted">Progress pengajuan</small>
                                        </div>
                                        
                                        <p class="card-text">
                                            <span class="badge badge-<?= $status['class'] ?> badge-lg">
                                                <?= $status['text'] ?>
                                            </span>
                                        </p>
                                        
                                        <?php if (isset($seminar)): ?>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <strong>Judul:</strong><br>
                                                    <small><?= htmlspecialchars($seminar->proposal_judul ?? 'N/A') ?></small>
                                                </div>
                                                <div class="col-sm-6">
                                                    <strong>Tanggal Pengajuan:</strong><br>
                                                    <small><?= date('d F Y', strtotime($seminar->created_at)) ?></small>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($seminar->file_skripsi)): ?>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id) ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-download"></i> Download File Skripsi
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Rejection comments -->
                                            <?php if ($seminar->status == 'rejected'): ?>
                                                <div class="mt-3">
                                                    <div class="alert alert-warning">
                                                        <h6><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</h6>
                                                        <p class="mb-0">
                                                            <?= htmlspecialchars($seminar->komentar_pembimbing ?: $seminar->komentar_kaprodi ?: 'Tidak ada komentar') ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Aksi</h6>
                                        
                                        <?php if ($can_resubmit ?? false): ?>
                                            <a href="<?= base_url('mahasiswa/seminar_skripsi/resubmit/' . $seminar->id) ?>" 
                                               class="btn btn-warning btn-block">
                                                <i class="fas fa-redo"></i> Ajukan Ulang
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                Tunggu proses review
                                            </p>
                                        <?php endif; ?>
                                        
                                        <hr>
                                        <small class="text-muted">
                                            Anda akan mendapat notifikasi email 
                                            untuk setiap update status.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($show_form ?? false): ?>
                        <!-- FORM PENGAJUAN BARU -->
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Selamat!</strong> Anda memenuhi syarat untuk mengajukan seminar skripsi.
                        </div>
                        
                        <!-- Requirements Check -->
                        <?php if (isset($requirements) && !empty($requirements)): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h6><i class="fas fa-list-check"></i> Status Persyaratan</h6>
                                    <div class="row">
                                        <?php foreach ($requirements as $req): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-<?= $req['met'] ? 'check-circle text-success' : 'times-circle text-danger' ?> mr-2"></i>
                                                    <span><?= $req['name'] ?>: <?= $req['current'] ?>/<?= $req['required'] ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Form Pengajuan -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-edit"></i> Form Pengajuan Seminar Skripsi</h5>
                            </div>
                            <div class="card-body">
                                <?= form_open_multipart('mahasiswa/seminar_skripsi/submit', ['id' => 'form-seminar']) ?>
                                
                                <?php if (isset($proposal)): ?>
                                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                                    
                                    <div class="form-group">
                                        <label><i class="fas fa-book"></i> Judul Proposal</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <?= htmlspecialchars($proposal->judul) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="form-group">
                                    <label for="file_skripsi">
                                        <i class="fas fa-file-pdf"></i> File Skripsi 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" 
                                           class="form-control-file" 
                                           id="file_skripsi" 
                                           name="file_skripsi" 
                                           accept=".pdf,.doc,.docx" 
                                           required>
                                    <small class="text-muted">
                                        Format: PDF, DOC, DOCX. Maksimal 5MB.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="keterangan">
                                        <i class="fas fa-comment"></i> Keterangan Tambahan 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="keterangan" 
                                              name="keterangan" 
                                              rows="4" 
                                              maxlength="500" 
                                              placeholder="Jelaskan ringkasan skripsi atau hal penting lainnya..."
                                              required></textarea>
                                    <small class="text-muted">Maksimal 500 karakter</small>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> 
                                        Ajukan Seminar Skripsi
                                    </button>
                                    <small class="form-text text-muted">
                                        Pastikan semua data sudah benar sebelum mengirim. 
                                        Dosen pembimbing akan mendapat notifikasi email.
                                    </small>
                                </div>
                                
                                <?= form_close() ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- TIDAK MEMENUHI SYARAT -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Belum Memenuhi Syarat</strong><br>
                            Anda belum dapat mengajukan seminar skripsi karena belum memenuhi persyaratan berikut:
                        </div>
                        
                        <?php if (isset($requirements) && !empty($requirements)): ?>
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-list-check"></i> Daftar Persyaratan</h6>
                                    
                                    <?php foreach ($requirements as $req): ?>
                                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                            <div class="mr-3">
                                                <i class="fas fa-<?= $req['met'] ? 'check-circle text-success' : 'times-circle text-danger' ?> fa-2x"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= $req['name'] ?></h6>
                                                <div class="progress mb-1" style="height: 20px;">
                                                    <div class="progress-bar <?= $req['met'] ? 'bg-success' : 'bg-warning' ?>" 
                                                         style="width: <?= ($req['current']/$req['required'])*100 ?>%">
                                                        <?= $req['current'] ?>/<?= $req['required'] ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php if ($req['met']): ?>
                                                        ✅ Persyaratan terpenuhi
                                                    <?php else: ?>
                                                        ❌ Kurang <?= $req['required'] - $req['current'] ?> lagi
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="mt-4 text-center">
                                        <p class="text-muted">
                                            <i class="fas fa-info-circle"></i>
                                            Lengkapi persyaratan di atas untuk dapat mengajukan seminar skripsi.
                                        </p>
                                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple JavaScript - No DataTables -->
<script>
$(document).ready(function() {
    
    // File upload validation
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
        
        // Show character count (optional)
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
    
    // Form submission
    $('#form-seminar').submit(function(e) {
        var fileInput = $('#file_skripsi');
        var keteranganInput = $('#keterangan');
        
        if (fileInput.get(0).files.length === 0) {
            e.preventDefault();
            alert('Harap pilih file skripsi!');
            return false;
        }
        
        if (keteranganInput.val().trim().length < 10) {
            e.preventDefault();
            alert('Keterangan minimal 10 karakter!');
            keteranganInput.focus();
            return false;
        }
        
        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
        
        return true;
    });
    
});
</script>