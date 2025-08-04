<?php
/**
 * Enhanced Views Seminar Skripsi Mahasiswa - STABLE + NEW FEATURES
 * 
 * EXISTING FEATURES (UNCHANGED):
 * - ✅ Display eligibility requirements 
 * - ✅ Show existing submission status
 * - ✅ Resubmit form for rejected submissions
 * - ✅ File download links
 * - ✅ Progress indicator
 * 
 * NEW FEATURES (ADDED):
 * - ✅ Input field untuk judul skripsi baru
 * - ✅ Upload surat keterangan penelitian
 * - ✅ Enhanced form validation
 * - ✅ Multiple file download support
 * - ✅ Better UI/UX
 * 
 * File: application/views/mahasiswa/seminar_skripsi/index.php
 * 
 * @version 4.1 (Enhanced - Stable + New Features)
 */
?>

<div class="container-fluid">
    
    <!-- Flash Messages (UNCHANGED) -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Page Heading (UNCHANGED) -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Seminar Skripsi</li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($error_message)): ?>
        <!-- Error Message / Requirements Not Met (UNCHANGED structure, enhanced display) -->
        <div class="row">
            <div class="col-12">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Persyaratan Belum Terpenuhi
                                </div>
                                <div class="h6 mb-0 text-gray-800"><?= htmlspecialchars($error_message) ?></div>
                                
                                <?php if (!empty($requirements)): ?>
                                    <div class="mt-3">
                                        <small class="text-muted">Status Persyaratan:</small>
                                        <?php foreach ($requirements as $req): ?>
                                            <div class="mt-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-<?= $req['met'] ? 'check-circle text-success' : 'times-circle text-danger' ?> mr-2"></i>
                                                    <span><?= $req['name'] ?>: <?= $req['current'] ?>/<?= $req['required'] ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($show_status && isset($seminar)): ?>
        <!-- Show Existing Status (ENHANCED with new file download options) -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle mr-2"></i>
                            Status Pengajuan Seminar Skripsi
                        </h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- Status Badge (UNCHANGED) -->
                        <div class="mb-3">
                            <?php
                            $status_info = $status_info ?? [];
                            $status_class = $status_info['class'] ?? 'secondary';
                            $status_text = $status_info['text'] ?? ucfirst($seminar->status);
                            ?>
                            <span class="badge badge-<?= $status_class ?> badge-lg">
                                <i class="fas fa-circle mr-1"></i>
                                <?= $status_text ?>
                            </span>
                        </div>

                        <!-- Detail Info (ENHANCED with judul_skripsi support) -->
                        <div class="row">
                            <div class="col-md-8">
                                <!-- ENHANCED: Show judul skripsi (baru) atau fallback ke judul proposal -->
                                <div class="row mb-2">
                                    <div class="col-sm-6">
                                        <strong>Judul Skripsi:</strong><br>
                                        <small><?= htmlspecialchars($seminar->judul_skripsi ?: $seminar->proposal_judul ?: 'N/A') ?></small>
                                        <?php if (!empty($seminar->judul_skripsi) && $seminar->judul_skripsi !== $seminar->proposal_judul): ?>
                                            <br><small class="text-info"><i class="fas fa-info-circle"></i> Judul diperbarui dari proposal</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Tanggal Pengajuan:</strong><br>
                                        <small><?= date('d F Y', strtotime($seminar->created_at)) ?></small>
                                    </div>
                                </div>
                                
                                <!-- ENHANCED: Multiple file download options -->
                                <?php if (!empty($seminar->file_skripsi) || !empty($seminar->surat_keterangan_penelitian)): ?>
                                    <div class="mt-3">
                                        <strong>File yang Diupload:</strong><br>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <?php if (!empty($seminar->file_skripsi)): ?>
                                                <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" 
                                                   class="btn btn-outline-primary btn-sm mb-1">
                                                    <i class="fas fa-download mr-1"></i> File Skripsi
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                                                <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/surat') ?>" 
                                                   class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-certificate mr-1"></i> Surat Keterangan Penelitian
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Rejection comments (UNCHANGED) -->
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

                                <!-- Keterangan mahasiswa (UNCHANGED) -->
                                <?php if (!empty($seminar->keterangan_mahasiswa)): ?>
                                    <div class="mt-3">
                                        <strong>Keterangan Tambahan:</strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($seminar->keterangan_mahasiswa) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Aksi</h6>
                                        
                                        <?php 
                                        // ✅ NEW: Cek apakah penilaian sudah dipublikasikan
                                        $has_published_penilaian = false;
                                        $penilaian_published = null;
                                    
                                        if (isset($seminar) && $seminar->status == 'completed') {
                                            $this->db->select('id, published_at');
                                            $this->db->from('penilaian_seminar_skripsi');
                                            $this->db->where('seminar_skripsi_id', $seminar->id);
                                            $this->db->where('status_penilaian', 'published');
                                            $this->db->where('published_at IS NOT NULL');
                                            $penilaian_published = $this->db->get()->row();
                                            $has_published_penilaian = !empty($penilaian_published);
                                        } elseif (isset($existing_seminar) && $existing_seminar->status == 'completed') {
                                            // Fallback jika variable bernama $existing_seminar
                                            $this->db->select('id, published_at');
                                            $this->db->from('penilaian_seminar_skripsi');
                                            $this->db->where('seminar_skripsi_id', $existing_seminar->id);
                                            $this->db->where('status_penilaian', 'published');
                                            $this->db->where('published_at IS NOT NULL');
                                            $penilaian_published = $this->db->get()->row();
                                            $has_published_penilaian = !empty($penilaian_published);
                                        }
                                        
                                        // Tentukan seminar object yang digunakan
                                        $current_seminar = isset($seminar) ? $seminar : (isset($existing_seminar) ? $existing_seminar : null);
                                        ?>
                                    
                                        <?php if ($has_published_penilaian && $current_seminar): ?>
                                            <!-- ✅ TOMBOL BARU: Lihat Hasil Penilaian -->
                                            <a href="<?= base_url('mahasiswa/seminar_skripsi/view_penilaian/' . $current_seminar->id) ?>" 
                                               class="btn btn-success btn-sm mb-2">
                                                <i class="fas fa-star mr-1"></i> Lihat Hasil Penilaian
                                            </a>
                                            <div class="alert alert-success p-2 mb-2">
                                                <small>
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Nilai dipublikasi: <?= date('d/m/Y H:i', strtotime($penilaian_published->published_at)) ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    
                                        <?php if ($current_seminar && $current_seminar->status == 'completed' && !$has_published_penilaian): ?>
                                            <!-- ✅ STATUS: Menunggu Publikasi Nilai -->
                                            <button class="btn btn-outline-secondary btn-sm mb-2" disabled>
                                                <i class="fas fa-clock mr-1"></i> Menunggu Publikasi Nilai
                                            </button>
                                            <div class="alert alert-info p-2 mb-2">
                                                <small>
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    Nilai akan segera dipublikasikan
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    
                                        <?php if ($can_resubmit ?? false): ?>
                                            <!-- ✅ TOMBOL EXISTING: Ajukan Ulang (TIDAK BERUBAH) -->
                                            <button type="button" class="btn btn-warning btn-sm mb-2" onclick="showResubmitForm()">
                                                <i class="fas fa-redo mr-1"></i> Ajukan Ulang
                                            </button>
                                        <?php endif; ?>
                                    
                                        <?php if (!($can_resubmit ?? false) && !$has_published_penilaian && (!$current_seminar || $current_seminar->status != 'completed')): ?>
                                            <!-- ✅ DEFAULT: Tidak ada aksi (hanya tampil jika tidak ada tombol lain) -->
                                            <small class="text-muted">Tidak ada aksi yang tersedia</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ENHANCED Resubmit Form (Hidden by default) -->
        <?php if ($can_resubmit ?? false): ?>
            <div class="row mt-4" id="resubmit-form" style="display: none;">
                <div class="col-12">
                    <div class="card border-left-warning">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-warning">
                                <i class="fas fa-edit mr-2"></i>
                                Pengajuan Ulang Seminar Skripsi
                            </h6>
                        </div>
                        <div class="card-body">
                            <?= form_open_multipart('mahasiswa/seminar_skripsi/resubmit/' . $seminar->id, ['id' => 'form-resubmit-seminar']) ?>
                            
                            <!-- ENHANCED: Judul Skripsi Baru (Opsional untuk resubmit) -->
                            <div class="form-group">
                                <label for="judul_skripsi_resubmit">
                                    <i class="fas fa-book mr-1"></i> 
                                    Judul Skripsi (Perbarui jika ada perubahan)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="judul_skripsi_resubmit" 
                                       name="judul_skripsi" 
                                       value="<?= htmlspecialchars($seminar->judul_skripsi ?: $seminar->proposal_judul ?: '') ?>"
                                       maxlength="250"
                                       placeholder="Perbarui judul skripsi jika ada perubahan berdasarkan masukan pembimbing">
                                <small class="text-muted">Kosongkan jika tidak ada perubahan judul. Maksimal 250 karakter.</small>
                                <div class="text-right">
                                    <small class="text-muted"><span id="char-counter-resubmit">0</span>/250</small>
                                </div>
                            </div>

                            <!-- File Skripsi Baru (ENHANCED - Opsional) -->
                            <div class="form-group">
                                <label for="file_skripsi_resubmit">
                                    <i class="fas fa-file-pdf mr-1"></i> 
                                    File Skripsi (Upload Baru jika Ada Perbaikan)
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file_skripsi_resubmit" name="file_skripsi" 
                                           accept=".pdf,.doc,.docx">
                                    <label class="custom-file-label" for="file_skripsi_resubmit">
                                        Pilih file skripsi yang sudah diperbaiki...
                                    </label>
                                </div>
                                <small class="text-muted">
                                    Format: PDF, DOC, atau DOCX. Maksimal 5MB. 
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

                            <!-- NEW: Surat Keterangan Penelitian Baru (Opsional) -->
                            <div class="form-group">
                                <label for="surat_penelitian_resubmit">
                                    <i class="fas fa-certificate mr-1"></i> 
                                    Surat Keterangan Penelitian (Upload Baru jika Ada)
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="surat_penelitian_resubmit" name="surat_penelitian" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <label class="custom-file-label" for="surat_penelitian_resubmit">
                                        Pilih surat keterangan penelitian yang baru...
                                    </label>
                                </div>
                                <small class="text-muted">
                                    Format: PDF, JPG, JPEG, atau PNG. Maksimal 3MB. 
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

                            <!-- Keterangan Perbaikan (UNCHANGED) -->
                            <div class="form-group">
                                <label for="keterangan_resubmit">
                                    <i class="fas fa-comment mr-1"></i> 
                                    Keterangan Perbaikan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="keterangan_resubmit" 
                                          name="keterangan" 
                                          rows="4" 
                                          maxlength="500" 
                                          required
                                          placeholder="Jelaskan perbaikan yang telah Anda lakukan berdasarkan masukan dari dosen pembimbing..."></textarea>
                                <small class="text-muted">Jelaskan perbaikan yang sudah dilakukan. Maksimal 500 karakter.</small>
                                <div class="text-right">
                                    <small class="text-muted"><span id="char-counter-keterangan-resubmit">0</span>/500</small>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary btn-block" onclick="hideResubmitForm()">
                                        <i class="fa fa-times mr-2"></i> Batal
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fa fa-paper-plane mr-2"></i> Ajukan Ulang
                                    </button>
                                </div>
                            </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($show_form && isset($proposal)): ?>
        <!-- ENHANCED: Show Form for New Submission -->
        <div class="row">
            <div class="col-12">
                
                <!-- Requirements Check (UNCHANGED) -->
                <?php if (!empty($requirements)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                Status Persyaratan
                            </h6>
                        </div>
                        <div class="card-body">
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

                <!-- Success Message (UNCHANGED) -->
                <div class="alert alert-success align-items-center">
                    <div class="row align-items-center">
                        <div class="col">
                            <i class="fas fa-thumbs-up mr-2"></i>
                            <strong>Selamat!</strong> Anda memenuhi syarat untuk mengajukan seminar skripsi.
                        </div>
                    </div>
                </div>
                
                <!-- ENHANCED: Form Pengajuan -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-edit mr-2"></i>
                            Form Pengajuan Seminar Skripsi
                        </h6>
                    </div>
                    <div class="card-body">
                        <?= form_open_multipart('mahasiswa/seminar_skripsi/submit', ['id' => 'form-seminar']) ?>
                        
                        <!-- Hidden proposal_id (UNCHANGED) -->
                        <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                        
                        <!-- Info Proposal (UNCHANGED) -->
                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Informasi Proposal
                                </h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong>Judul Proposal:</strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($proposal->judul) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NEW: Judul Skripsi (Bisa berbeda dari proposal) -->
                        <div class="form-group">
                            <label for="judul_skripsi">
                                <i class="fas fa-book mr-1"></i> 
                                Judul Skripsi <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="judul_skripsi" 
                                   name="judul_skripsi" 
                                   value="<?= htmlspecialchars($proposal->judul) ?>"
                                   maxlength="250" 
                                   required
                                   placeholder="Masukkan judul skripsi (bisa sama atau berbeda dari judul proposal)">
                            <small class="text-muted">
                                <strong>Penting:</strong> Judul skripsi bisa berbeda dari judul proposal jika ada perubahan selama penelitian. 
                                Pastikan judul sudah sesuai hasil penelitian. Maksimal 250 karakter.
                            </small>
                            <div class="text-right">
                                <small class="text-muted"><span id="char-counter">0</span>/250</small>
                            </div>
                        </div>

                        <!-- ENHANCED: Upload Files Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload Berkas Wajib
                                </h6>
                            </div>
                            <div class="card-body">
                                
                                <!-- File Skripsi (ENHANCED) -->
                                <div class="form-group">
                                    <label for="file_skripsi">
                                        <i class="fas fa-file-pdf mr-1"></i> 
                                        File Skripsi Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_skripsi" name="file_skripsi" 
                                               accept=".pdf,.doc,.docx" required>
                                        <label class="custom-file-label" for="file_skripsi">Pilih file skripsi...</label>
                                    </div>
                                    <small class="text-muted">
                                        <strong>Format:</strong> PDF, DOC, atau DOCX. <strong>Maksimal:</strong> 5MB.<br>
                                        <strong>Isi:</strong> Dokumen skripsi lengkap (Bab 1-5) yang siap untuk diseminarkan.
                                    </small>
                                </div>

                                <!-- NEW: Surat Keterangan Penelitian -->
                                <div class="form-group">
                                    <label for="surat_penelitian">
                                        <i class="fas fa-certificate mr-1"></i> 
                                        Surat Keterangan Penelitian <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="surat_penelitian" name="surat_penelitian" 
                                               accept=".pdf,.jpg,.jpeg,.png" required>
                                        <label class="custom-file-label" for="surat_penelitian">Pilih surat keterangan penelitian...</label>
                                    </div>
                                    <small class="text-muted">
                                        <strong>Format:</strong> PDF, JPG, JPEG, atau PNG. <strong>Maksimal:</strong> 3MB.<br>
                                        <strong>Isi:</strong> Surat keterangan selesai penelitian dari instansi/tempat penelitian.
                                    </small>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Catatan Penting:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Pastikan semua file dapat dibuka dan tidak corrupt</li>
                                        <li>File skripsi harus berisi konten lengkap dari Bab 1 sampai Bab 5</li>
                                        <li>Surat keterangan penelitian harus asli dari instansi penelitian</li>
                                        <li>Semua berkas akan direview oleh dosen pembimbing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Tambahan (ENHANCED - Made optional) -->
                        <div class="form-group">
                            <label for="keterangan">
                                <i class="fas fa-comment mr-1"></i> 
                                Keterangan Tambahan (Opsional)
                            </label>
                            <textarea class="form-control" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="4" 
                                      maxlength="500"
                                      placeholder="Tambahkan keterangan jika diperlukan (misal: hal penting yang perlu diperhatikan dosen pembimbing, perubahan metodologi, dll)"></textarea>
                            <small class="text-muted">Keterangan tambahan untuk dosen pembimbing. Maksimal 500 karakter.</small>
                            <div class="text-right">
                                <small class="text-muted"><span id="char-counter-keterangan">0</span>/500</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Submit Button (UNCHANGED) -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                                <i class="fas fa-paper-plane mr-2"></i> 
                                Ajukan Seminar Skripsi
                            </button>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pastikan semua data sudah benar sebelum mengirim. Dosen pembimbing akan mendapat notifikasi email.
                            </small>
                        </div>
                        
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ENHANCED JavaScript -->
<script>
$(document).ready(function() {
    
    // Custom file input labels (UNCHANGED)
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
    
    // ENHANCED: Character counters
    $('#judul_skripsi').on('input', function() {
        let length = $(this).val().length;
        $('#char-counter').text(length);
        
        if (length > 250) {
            $(this).addClass('is-invalid');
            $('#char-counter').addClass('text-danger');
        } else {
            $(this).removeClass('is-invalid');
            $('#char-counter').removeClass('text-danger');
        }
    });

    $('#judul_skripsi_resubmit').on('input', function() {
        let length = $(this).val().length;
        $('#char-counter-resubmit').text(length);
        
        if (length > 250) {
            $(this).addClass('is-invalid');
            $('#char-counter-resubmit').addClass('text-danger');
        } else {
            $(this).removeClass('is-invalid');
            $('#char-counter-resubmit').removeClass('text-danger');
        }
    });
    
    $('#keterangan').on('input', function() {
        let length = $(this).val().length;
        $('#char-counter-keterangan').text(length);
        
        if (length > 500) {
            $(this).addClass('is-invalid');
            $('#char-counter-keterangan').addClass('text-danger');
        } else {
            $(this).removeClass('is-invalid');
            $('#char-counter-keterangan').removeClass('text-danger');
        }
    });

    $('#keterangan_resubmit').on('input', function() {
        let length = $(this).val().length;
        $('#char-counter-keterangan-resubmit').text(length);
        
        if (length > 500) {
            $(this).addClass('is-invalid');
            $('#char-counter-keterangan-resubmit').addClass('text-danger');
        } else {
            $(this).removeClass('is-invalid');
            $('#char-counter-keterangan-resubmit').removeClass('text-danger');
        }
    });
    
    // Initialize character counters
    $('#judul_skripsi').trigger('input');
    $('#judul_skripsi_resubmit').trigger('input');
    $('#keterangan').trigger('input');
    $('#keterangan_resubmit').trigger('input');
    
    // ENHANCED: Form validation
    $('#form-seminar').on('submit', function(e) {
        let isValid = true;
        let errors = [];
        
        // Check judul skripsi
        let judul = $('#judul_skripsi').val().trim();
        if (judul.length < 10) {
            isValid = false;
            errors.push('Judul skripsi minimal 10 karakter');
        }
        
        // Check file uploads
        if (!$('#file_skripsi')[0].files.length) {
            isValid = false;
            errors.push('File skripsi wajib diupload');
        }
        
        if (!$('#surat_penelitian')[0].files.length) {
            isValid = false;
            errors.push('Surat keterangan penelitian wajib diupload');
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Error:\n- ' + errors.join('\n- '));
            return false;
        }
        
        // Confirm submission
        if (!confirm('Apakah Anda yakin akan mengajukan seminar skripsi?\n\nPastikan:\n✓ Judul skripsi sudah benar\n✓ File skripsi lengkap (Bab 1-5)\n✓ Surat keterangan penelitian valid')) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $('#btn-submit').html('<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...').prop('disabled', true);
    });

    // Resubmit form validation (ENHANCED)
    $('#form-resubmit-seminar').on('submit', function(e) {
        let keterangan = $('#keterangan_resubmit').val().trim();
        if (keterangan.length < 10) {
            e.preventDefault();
            alert('Keterangan perbaikan minimal 10 karakter');
            return false;
        }
        
        if (!confirm('Apakah Anda yakin akan mengajukan ulang seminar skripsi?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto dismiss alerts (UNCHANGED)
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 10000);
});

// Functions for resubmit form (UNCHANGED)
function showResubmitForm() {
    $('#resubmit-form').slideDown();
    $('#keterangan_resubmit').focus();
}

function hideResubmitForm() {
    $('#resubmit-form').slideUp();
}
</script>

<!-- ENHANCED Styles -->
<style>
.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.custom-file-label.selected {
    color: #495057;
}

.card .card-body ul {
    padding-left: 1.5rem;
}

.alert ul {
    margin-bottom: 0;
}

.text-muted small {
    font-size: 0.85rem;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.custom-file-input:focus ~ .custom-file-label {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* NEW: Enhanced button styles */
.btn-group-vertical .btn {
    text-align: left;
}

.btn-outline-primary:hover,
.btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);    
}

/* NEW: File upload area enhancement */
.custom-file {
    transition: all 0.3s ease;
}

.custom-file:hover {
    transform: translateY(-1px);
}

/* NEW: Character counter styles */
#char-counter, #char-counter-resubmit, 
#char-counter-keterangan, #char-counter-keterangan-resubmit {
    font-weight: 500;
}

.text-danger#char-counter, .text-danger#char-counter-resubmit,
.text-danger#char-counter-keterangan, .text-danger#char-counter-keterangan-resubmit {
    font-weight: 700;
}
</style>