<?php
/**
 * FIXED Detail Seminar Skripsi Kaprodi - Complete Version
 * File: application/views/kaprodi/seminar_skripsi/detail.php
 * 
 * PERBAIKAN:
 * 1. Text styling agar tidak overflow pada informasi mahasiswa  
 * 2. Menambahkan section download file skripsi mahasiswa
 * 3. Menambahkan section download surat keterangan penelitian
 * 4. Path upload yang benar untuk semua file
 * 5. Responsive design yang lebih baik
 * 
 * PATH YANG DIGUNAKAN:
 * - File Skripsi: uploads/seminar_skripsi/skripsi_files/
 * - Surat Penelitian: uploads/seminar_skripsi/surat_penelitian/
 * - File Turnitin: uploads/turnitin/
 * 
 * CONTROLLER YANG PERLU DIBUAT/UPDATE:
 * - kaprodi/seminar_skripsi/validasi_turnitin (POST) - upload file ke uploads/turnitin/
 * - Pastikan folder uploads sudah ada dan writable
 */
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Back Button -->
<div class="row mb-3">
    <div class="col">
        <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<!-- Enhanced Header with Status -->
<div class="row mb-4">
    <div class="col">
        <div class="card gradient-header text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1"><i class="fas fa-graduation-cap mr-2"></i>Detail Seminar Skripsi</h4>
                        <p class="mb-0"><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?> (<?= htmlspecialchars($seminar->nim ?? 'N/A') ?>) - Review & Validasi Kaprodi</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php 
                        $status_class = 'secondary';
                        $status_text = 'DRAFT';
                        $progress = 25;
                        
                        switch($seminar->status_kaprodi ?? 'pending') {
                            case 'pending':
                                $status_class = 'warning';
                                $status_text = 'MENUNGGU REVIEW';
                                $progress = 50;
                                break;
                            case 'approved':
                                $status_class = 'success';
                                $status_text = 'DISETUJUI';
                                $progress = 75;
                                break;
                            case 'rejected':
                                $status_class = 'danger';
                                $status_text = 'DITOLAK';
                                $progress = 25;
                                break;
                        }
                        ?>
                        <span class="badge badge-<?= $status_class ?> badge-status">
                            <i class="fas fa-<?= $status_class == 'warning' ? 'clock' : ($status_class == 'success' ? 'check' : 'times') ?> mr-1"></i><?= $status_text ?>
                        </span>
                        <div class="mt-2">
                            <small>Progress Seminar: <?= $progress ?>%</small>
                            <div class="progress progress-indicator mt-1">
                                <div class="progress-bar bg-<?= $status_class ?>" style="width: <?= $progress ?>%"></div>
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
        
        <!-- BERKAS MAHASISWA SECTION -->
        <div class="card shadow mb-4">
            <div class="card-header bg-info">
                <h5 class="text-white mb-0">
                    <i class="fas fa-folder-open mr-2"></i>
                    Berkas Mahasiswa
                </h5>
            </div>
            <div class="card-body">
                
                <!-- FILE SKRIPSI -->
                <div class="border-bottom pb-3 mb-3">
                    <h6><i class="fas fa-file-alt mr-2"></i>File Skripsi</h6>
                    <?php if(!empty($seminar->file_skripsi)): ?>
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="media-object">
                                        <i class="fas fa-file-pdf fa-2x text-danger mr-3"></i>
                                    </div>
                                    <div class="media-body">
                                        <p class="mb-1 font-weight-bold">File Skripsi Tersedia</p>
                                        <p class="text-muted mb-1">
                                            <small><i class="fas fa-file mr-1"></i> <?= htmlspecialchars($seminar->file_skripsi) ?></small>
                                        </p>
                                        <p class="text-muted mb-0">
                                            <small><i class="fas fa-clock mr-1"></i> Diupload: <?= $seminar->created_at ? date('d/m/Y H:i', strtotime($seminar->created_at)) : '-' ?></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('uploads/seminar_skripsi/skripsi_files/' . urlencode($seminar->file_skripsi)) ?>" 
                                   class="btn btn-primary btn-sm mb-1" 
                                   download="<?= 'Skripsi_' . $seminar->nim . '.pdf' ?>"
                                   title="Download File Skripsi">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                                <br>
                                <a href="<?= base_url('uploads/seminar_skripsi/skripsi_files/' . urlencode($seminar->file_skripsi)) ?>" 
                                   class="btn btn-outline-primary btn-sm" 
                                   target="_blank"
                                   title="Lihat File di Browser">
                                    <i class="fas fa-eye mr-1"></i> Lihat File
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-file-excel fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">File skripsi belum diupload</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- SURAT KETERANGAN PENELITIAN -->
                <div class="pb-3 mb-3">
                    <h6><i class="fas fa-certificate mr-2"></i>Surat Keterangan Penelitian</h6>
                    <?php if(!empty($seminar->surat_keterangan_penelitian)): ?>
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="media">
                                    <div class="media-object">
                                        <i class="fas fa-certificate fa-2x text-success mr-3"></i>
                                    </div>
                                    <div class="media-body">
                                        <p class="mb-1 font-weight-bold">Surat Keterangan Tersedia</p>
                                        <p class="text-muted mb-1">
                                            <small><i class="fas fa-file mr-1"></i> <?= htmlspecialchars($seminar->surat_keterangan_penelitian) ?></small>
                                        </p>
                                        <p class="text-muted mb-0">
                                            <small><i class="fas fa-info-circle mr-1"></i> Surat keterangan selesai penelitian</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('uploads/seminar_skripsi/surat_penelitian/' . urlencode($seminar->surat_keterangan_penelitian)) ?>" 
                                   class="btn btn-success btn-sm mb-1" 
                                   download="<?= 'Surat_Penelitian_' . $seminar->nim . '.pdf' ?>"
                                   title="Download Surat Keterangan Penelitian">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                                <br>
                                <a href="<?= base_url('uploads/seminar_skripsi/surat_penelitian/' . urlencode($seminar->surat_keterangan_penelitian)) ?>" 
                                   class="btn btn-outline-success btn-sm" 
                                   target="_blank"
                                   title="Lihat Surat di Browser">
                                    <i class="fas fa-eye mr-1"></i> Lihat Surat
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-certificate fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Surat keterangan penelitian belum diupload</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- INFORMASI TAMBAHAN -->
                <?php if(!empty($seminar->file_skripsi) || !empty($seminar->surat_keterangan_penelitian)): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6><i class="fas fa-info-circle mr-1"></i> Informasi Pengajuan:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Judul Skripsi:</strong><br>
                                <?= htmlspecialchars($seminar->judul_skripsi ?? $seminar->judul ?? 'N/A') ?>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Keterangan Mahasiswa:</strong><br>
                                <?= htmlspecialchars($seminar->keterangan_mahasiswa ?? 'Tidak ada keterangan') ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

            <!-- Enhanced: Perbandingan Judul Skripsi -->
            <div class="card shadow mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-book mr-2"></i>
                        Perbandingan Judul Skripsi
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($seminar->is_judul_changed) && $seminar->is_judul_changed): ?>
                        <!-- Ada perubahan judul -->
                        <div class="alert alert-warning border-left-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>
                                <div>
                                    <h6 class="mb-1">
                                        <strong>Mahasiswa Mengubah Judul!</strong>
                                    </h6>
                                    <p class="mb-0">
                                        Judul skripsi berbeda dari proposal awal. 
                                        <?php if (isset($seminar->judul_similarity)): ?>
                                            <strong>Kemiripan: <?= $seminar->judul_similarity ?>%</strong>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-left-secondary">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">
                                            <i class="fas fa-history mr-1"></i>
                                            Judul Proposal (Original):
                                        </h6>
                                        <div class="bg-light p-3 rounded">
                                            <small class="text-muted d-block">Judul awal dari proposal:</small>
                                            <div class="mt-1">
                                                <?= htmlspecialchars($seminar->judul_proposal_original ?? 'N/A') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-left-primary">
                                    <div class="card-body">
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-edit mr-1"></i>
                                            Judul Skripsi (Baru):
                                        </h6>
                                        <div class="bg-primary text-white p-3 rounded">
                                            <small class="d-block" style="opacity: 0.8;">Judul yang diubah mahasiswa:</small>
                                            <div class="mt-1 font-weight-bold">
                                                <?= htmlspecialchars($seminar->judul_skripsi ?? 'N/A') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                        <!-- Similarity Analysis -->
                        <?php if (isset($seminar->judul_similarity)): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="mb-2">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Analisis Kemiripan:
                            </h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <?php 
                                $similarity = $seminar->judul_similarity;
                                $progress_class = $similarity >= 70 ? 'bg-success' : ($similarity >= 40 ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="progress-bar <?= $progress_class ?>" 
                                     style="width: <?= $similarity ?>%">
                                    <?= $similarity ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if ($similarity >= 70): ?>
                                    <i class="fas fa-check-circle text-success mr-1"></i>
                                    Perubahan minor - masih dalam konteks yang sama
                                <?php elseif ($similarity >= 40): ?>
                                    <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                    Perubahan signifikan - perlu evaluasi lebih lanjut
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger mr-1"></i>
                                    Perubahan major - hampir sepenuhnya berbeda
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>
            
                    <?php else: ?>
                        <!-- Tidak ada perubahan judul -->
                        <div class="alert alert-success border-left-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x text-success mr-3"></i>
                                <div>
                                    <h6 class="mb-1">
                                        <strong>Judul Konsisten</strong>
                                    </h6>
                                    <p class="mb-0">
                                        Mahasiswa menggunakan judul yang sama dengan proposal awal.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-book mr-1"></i>
                                Judul Skripsi:
                            </h6>
                            <div class="font-weight-bold">
                                <?= htmlspecialchars($seminar->judul_current ?? $seminar->judul_proposal_original ?? 'N/A') ?>
                            </div>
                        </div>
                    <?php endif; ?>
            
                    <!-- Additional Information -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="border-right">
                                    <i class="fas fa-calendar text-muted"></i>
                                    <div class="mt-1">
                                        <small class="text-muted d-block">Tanggal Submit</small>
                                        <strong><?= $seminar->created_at ? date('d/m/Y', strtotime($seminar->created_at)) : '-' ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border-right">
                                    <i class="fas fa-user-tie text-muted"></i>
                                    <div class="mt-1">
                                        <small class="text-muted d-block">Pembimbing</small>
                                        <strong><?= htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-file-alt text-muted"></i>
                                <div class="mt-1">
                                    <small class="text-muted d-block">Status Berkas</small>
                                    <strong class="text-<?= (!empty($seminar->file_skripsi) && !empty($seminar->surat_keterangan_penelitian)) ? 'success' : 'warning' ?>">
                                        <?= (!empty($seminar->file_skripsi) && !empty($seminar->surat_keterangan_penelitian)) ? 'Lengkap' : 'Belum Lengkap' ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- REKOMENDASI PEMBIMBING -->
        <?php if(!empty($seminar->status_pembimbing) && $seminar->status_pembimbing != 'pending'): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                <h5 class="text-white mb-0">
                    <i class="fas fa-user-check mr-2"></i>
                    Rekomendasi Dosen Pembimbing
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                    <i class="fas fa-<?= $seminar->status_pembimbing == 'approved' ? 'check-circle' : 'times-circle' ?> mr-2"></i>
                    <strong>Status:</strong> <?= $seminar->status_pembimbing == 'approved' ? 'Direkomendasikan' : 'Ditolak' ?>
                </div>
                
                <?php if(!empty($seminar->komentar_pembimbing)): ?>
                <div class="mt-3">
                    <strong>Komentar Pembimbing:</strong>
                    <p class="text-muted"><?= htmlspecialchars($seminar->komentar_pembimbing) ?></p>
                </div>
                <?php endif; ?>
                
                <small class="text-muted">
                    <i class="fas fa-clock mr-1"></i>
                    Direview: <?= $seminar->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) : '-' ?>
                </small>
            </div>
        </div>
        <?php endif; ?>

        <!-- VALIDASI TURNITIN SECTION -->
        <?php if($seminar->status_pembimbing == 'approved' && ($seminar->status_kaprodi ?? 'pending') == 'pending'): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-warning">
                <h5 class="text-white mb-0">
                    <i class="fas fa-search mr-2"></i>
                    Validasi Plagiarisme Turnitin
                </h5>
            </div>
            <div class="card-body form-section">
                <form id="form-turnitin-validation" method="POST" action="<?= base_url('kaprodi/seminar_skripsi/validasi_turnitin') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="seminar_id" value="<?= $seminar->id ?? '' ?>">
                    
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
                        <small class="text-info d-block">
                            <i class="fas fa-info-circle mr-1"></i>
                            File akan disimpan di: uploads/turnitin/
                        </small>
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
                    
                    <!-- PENJADWALAN SECTION (Hidden by default) -->
                    <div id="penjadwalan-section" style="display: none;">
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Penjadwalan Seminar Skripsi
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Tanggal Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_seminar" 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-clock mr-1"></i>
                                        Jam Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" class="form-control" name="jam_seminar">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        Tempat Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="tempat_seminar" 
                                           placeholder="e.g. Ruang Sidang A">
                                </div>
                            </div>
                        </div>
                        
                        <!-- PENUNJUKAN DOSEN PENGUJI -->
                        <h6 class="font-weight-bold text-info mb-3">
                            <i class="fas fa-users mr-2"></i>
                            Penunjukan Dosen Penguji
                        </h6>
                        
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
                                    <select class="form-control" name="dosen_penguji1_id">
                                        <option value="">-- Pilih Dosen Penguji 1 --</option>
                                        <!-- Dynamic options dari controller -->
                                        <option value="1">Dr. Ahmad Wijaya, M.Pd</option>
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
                                    <select class="form-control" name="dosen_penguji2_id">
                                        <option value="">-- Pilih Dosen Penguji 2 --</option>
                                        <!-- Dynamic options dari controller -->
                                        <option value="4">Dr. Maria Magdalena, M.Pd</option>
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
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg" id="btn-submit">
                            <i class="fas fa-check mr-2"></i>
                            Simpan & Proses Validasi
                        </button>
                        <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- STATUS TELAH DIVALIDASI -->
        <?php if(($seminar->status_kaprodi ?? 'pending') != 'pending'): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-<?= $seminar->status_kaprodi == 'approved' ? 'success' : 'danger' ?>">
                <h5 class="text-white mb-0">
                    <i class="fas fa-<?= $seminar->status_kaprodi == 'approved' ? 'check-circle' : 'times-circle' ?> mr-2"></i>
                    Status Validasi Kaprodi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-<?= $seminar->status_kaprodi == 'approved' ? 'success' : 'danger' ?>">
                    <i class="fas fa-<?= $seminar->status_kaprodi == 'approved' ? 'check-circle' : 'times-circle' ?> mr-2"></i>
                    <strong>Keputusan:</strong> <?= $seminar->status_kaprodi == 'approved' ? 'DISETUJUI' : 'DITOLAK' ?>
                </div>
                
                <?php if(!empty($seminar->plagiarism_percentage)): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Persentase Plagiarisme:</strong>
                        <span class="badge badge-<?= $seminar->plagiarism_percentage <= 30 ? 'success' : 'danger' ?> ml-2">
                            <?= number_format($seminar->plagiarism_percentage, 1) ?>%
                        </span>
                    </div>
                    <div class="col-md-6">
                        <?php if(!empty($seminar->file_turnitin)): ?>
                        <a href="<?= base_url('uploads/turnitin/' . urlencode($seminar->file_turnitin)) ?>" 
                           class="btn btn-outline-primary btn-sm"
                           download="<?= 'Turnitin_' . $seminar->nim . '.pdf' ?>"
                           title="Download File Turnitin">
                            <i class="fas fa-download mr-1"></i> Download File Turnitin
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($seminar->komentar_kaprodi)): ?>
                <div class="mt-3">
                    <strong>Komentar Kaprodi:</strong>
                    <p class="text-muted"><?= htmlspecialchars($seminar->komentar_kaprodi) ?></p>
                </div>
                <?php endif; ?>
                
                <small class="text-muted">
                    <i class="fas fa-clock mr-1"></i>
                    Divalidasi: <?= $seminar->tanggal_review_kaprodi ? date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) : '-' ?>
                </small>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Information -->
    <div class="col-lg-4">
        
        <!-- Info Mahasiswa - FIXED STYLING -->
        <div class="card shadow mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Informasi Mahasiswa</h6>
            </div>
            <div class="card-body">
                <div class="info-mahasiswa">
                    <div class="row mb-2">
                        <div class="col-4"><strong>Nama:</strong></div>
                        <div class="col-8 text-break"><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>NIM:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($seminar->nim ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Email:</strong></div>
                        <div class="col-8 text-break">
                            <small><?= htmlspecialchars($seminar->email_mahasiswa ?? 'N/A') ?></small>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Pembimbing:</strong></div>
                        <div class="col-8 text-break">
                            <small><?= htmlspecialchars($seminar->nama_pembimbing ?? 'Belum ditentukan') ?></small>
                        </div>
                    </div>
                </div>
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
                        <div class="bg-<?= ($seminar->status_pembimbing ?? 'pending') == 'approved' ? 'success' : 'secondary' ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="fas fa-<?= ($seminar->status_pembimbing ?? 'pending') == 'approved' ? 'check' : 'clock' ?>" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h6 class="mb-0">Rekomendasi Pembimbing</h6>
                        <small class="text-muted">
                            <?= ($seminar->status_pembimbing ?? 'pending') == 'approved' ? 'Disetujui pembimbing' : 'Menunggu rekomendasi' ?>
                        </small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-<?= ($seminar->status_kaprodi ?? 'pending') == 'pending' ? 'warning' : (($seminar->status_kaprodi ?? 'pending') == 'approved' ? 'success' : 'danger') ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="fas fa-<?= ($seminar->status_kaprodi ?? 'pending') == 'pending' ? 'clock' : (($seminar->status_kaprodi ?? 'pending') == 'approved' ? 'check' : 'times') ?>" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h6 class="mb-0">Review Kaprodi</h6>
                        <small class="text-<?= ($seminar->status_kaprodi ?? 'pending') == 'pending' ? 'warning' : (($seminar->status_kaprodi ?? 'pending') == 'approved' ? 'success' : 'danger') ?>">
                            <?= ($seminar->status_kaprodi ?? 'pending') == 'pending' ? 'Sedang diproses' : (($seminar->status_kaprodi ?? 'pending') == 'approved' ? 'Disetujui' : 'Ditolak') ?>
                        </small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-<?= !empty($seminar->tanggal_seminar) ? 'success' : 'secondary' ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="fas fa-<?= !empty($seminar->tanggal_seminar) ? 'check' : 'calendar' ?>" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h6 class="mb-0">Penjadwalan</h6>
                        <small class="text-muted">
                            <?= !empty($seminar->tanggal_seminar) ? 'Sudah dijadwalkan' : 'Menunggu penjadwalan' ?>
                        </small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            <i class="fas fa-graduation-cap" style="font-size: 12px;"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ml-3">
                        <h6 class="mb-0">Pelaksanaan Seminar</h6>
                        <small class="text-muted">Menunggu jadwal</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Seminar (jika sudah ada) -->
        <?php if(!empty($seminar->tanggal_seminar)): ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-success">
                <h6 class="text-white mb-0"><i class="fas fa-calendar-check mr-2"></i>Jadwal Seminar</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-4"><strong>Tanggal:</strong></div>
                    <div class="col-8"><?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Waktu:</strong></div>
                    <div class="col-8"><?= $seminar->jam_seminar ? date('H:i', strtotime($seminar->jam_seminar)) : '-' ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Tempat:</strong></div>
                    <div class="col-8"><?= htmlspecialchars($seminar->tempat_seminar ?? '-') ?></div>
                </div>
                
                <?php if(!empty($seminar->nama_penguji1) || !empty($seminar->nama_penguji2)): ?>
                <hr>
                <h6>Dosen Penguji:</h6>
                <?php if(!empty($seminar->nama_penguji1)): ?>
                <small class="d-block text-muted">1. <?= htmlspecialchars($seminar->nama_penguji1) ?></small>
                <?php endif; ?>
                <?php if(!empty($seminar->nama_penguji2)): ?>
                <small class="d-block text-muted">2. <?= htmlspecialchars($seminar->nama_penguji2) ?></small>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="card shadow">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="card card-stats bg-gradient-primary text-white">
                            <div class="card-body py-3">
                                <h4 class="mb-0"><?= $progress ?>%</h4>
                                <span class="small">Progress</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card card-stats bg-gradient-success text-white">
                            <div class="card-body py-3">
                                <h4 class="mb-0">5</h4>
                                <span class="small">Phase</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.gradient-header { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
}
.badge-status { 
    font-size: 0.9em; 
    padding: 0.5em 1em; 
}
.progress-indicator { 
    height: 8px; 
    border-radius: 4px; 
}
.card-stats { 
    transition: transform 0.2s; 
    border: none;
}
.card-stats:hover { 
    transform: translateY(-2px); 
}
.form-section { 
    border-left: 4px solid #667eea; 
    background: #f8f9fa; 
}
.action-buttons .btn { 
    margin: 0 5px; 
}
.turnitin-result { 
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); 
}

/* FIXED: Styling untuk informasi mahasiswa */
.info-mahasiswa .row {
    margin-bottom: 0.5rem;
}
.info-mahasiswa .text-break {
    word-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}
.info-mahasiswa small {
    font-size: 0.85rem;
    line-height: 1.3;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.border-left-secondary {
    border-left: 4px solid #6c757d !important;
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.6s ease;
}

.card-body .row .col-md-4:not(:last-child) .border-right {
    border-right: 1px solid #dee2e6;
    padding-right: 15px;
}

.card-body .row .col-md-4:not(:first-child) {
    padding-left: 15px;
}

@media (max-width: 768px) {
    .card-body .row .col-md-4:not(:last-child) .border-right {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

/* Responsive improvements */
@media (max-width: 768px) {
    .info-mahasiswa .col-4,
    .info-mahasiswa .col-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .info-mahasiswa .col-4 {
        margin-bottom: 0.25rem;
    }
    .info-mahasiswa .col-8 {
        margin-bottom: 0.75rem;
        padding-left: 1rem;
    }
}
</style>

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
        } else {
            $('#penjadwalan-section').slideUp();
        }
    });

    // Form validation
    $('#form-turnitin-validation').on('submit', function(e) {
        var decision = $('input[name="decision"]:checked').val();
        var percentage = $('#plagiarism_percentage').val();
        
        if (!decision) {
            e.preventDefault();
            alert('Silakan pilih keputusan validasi terlebih dahulu!');
            return false;
        }
        
        if (!percentage) {
            e.preventDefault();
            alert('Persentase plagiarisme harus diisi!');
            return false;
        }

        if (decision === 'approved') {
            var tanggal = $('input[name="tanggal_seminar"]').val();
            var jam = $('input[name="jam_seminar"]').val();
            var tempat = $('input[name="tempat_seminar"]').val();
            var penguji1 = $('select[name="dosen_penguji1_id"]').val();
            var penguji2 = $('select[name="dosen_penguji2_id"]').val();
            
            if (!tanggal || !jam || !tempat || !penguji1 || !penguji2) {
                e.preventDefault();
                alert('Semua field penjadwalan dan penunjukan dosen penguji harus diisi untuk persetujuan!');
                return false;
            }
        }

        // Show loading state
        $('#btn-submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
        
        return true;
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});
</script>