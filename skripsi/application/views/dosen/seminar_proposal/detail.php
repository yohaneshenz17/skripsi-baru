<?php
/**
 * ✅ PERBAIKAN LENGKAP VIEW DETAIL SEMINAR PROPOSAL DOSEN
 * File: application/views/dosen/seminar_proposal/detail.php
 * 
 * PERBAIKAN:
 * 1. Akses data pembimbing yang benar
 * 2. Format data jurnal bimbingan yang sesuai dengan controller
 * 3. Handle case data tidak tersedia
 */

// Start output buffering untuk content
ob_start();
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

<div class="row">
    <!-- Left Column: Detail Proposal -->
    <div class="col-xl-8 col-lg-7">
        <!-- Proposal Details Card -->
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Detail Seminar Proposal
                        </h3>
                        <p class="text-white-50 mt-1 mb-0">
                            <?= htmlspecialchars($seminar->nama_mahasiswa) ?> (<?= htmlspecialchars($seminar->nim) ?>)
                        </p>
                    </div>
                    <div class="col-auto">
                        <!-- Status Badge -->
                        <?php if($seminar->status === 'scheduled'): ?>
                            <span class="badge badge-info badge-lg">
                                <i class="fas fa-calendar-check mr-1"></i>Terjadwal
                            </span>
                        <?php elseif($seminar->status === 'approved'): ?>
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check mr-1"></i>Disetujui
                            </span>
                        <?php elseif($seminar->status === 'review_kaprodi'): ?>
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock mr-1"></i>Review Kaprodi
                            </span>
                        <?php else: ?>
                            <span class="badge badge-secondary badge-lg">
                                <i class="fas fa-info mr-1"></i><?= ucfirst($seminar->status) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Mahasiswa Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">MAHASISWA</label>
                            <p class="mb-1 font-weight-600"><?= htmlspecialchars($seminar->nama_mahasiswa) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($seminar->nim) ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">PROGRAM STUDI</label>
                            <p class="mb-1 font-weight-600"><?= htmlspecialchars($seminar->nama_prodi) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- 🆕 UPDATED: Judul Proposal dengan Tracking Perubahan -->
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">
                        <i class="fas fa-file-alt mr-1"></i>
                        JUDUL PROPOSAL
                    </label>
                    
                    <!-- Judul untuk Seminar (Primary) -->
                    <div class="border rounded p-3 mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex: 1;">
                                <small class="text-white-50">Judul untuk Seminar Proposal:</small>
                                <h6 class="mb-0 text-white font-weight-600">
                                    <?php 
                                    // Logic yang sama: prioritas judul_seminar, fallback ke original
                                    $judul_display = '';
                                    if (isset($seminar->judul_seminar) && !empty($seminar->judul_seminar)) {
                                        $judul_display = $seminar->judul_seminar;
                                    } else {
                                        $judul_display = $seminar->judul;  // dari database view yang join ke proposal_mahasiswa
                                    }
                                    echo htmlspecialchars($judul_display); 
                                    ?>
                                </h6>
                            </div>
                            <div class="ml-3">
                                <i class="fas fa-bookmark fa-lg text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Perubahan Judul (jika ada) -->
                    <?php if (isset($seminar->judul_seminar) && !empty($seminar->judul_seminar) && $seminar->judul_seminar != $seminar->judul): ?>
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle mt-1 mr-2"></i>
                            <div>
                                <strong>Perubahan Judul Terdeteksi</strong><br>
                                <small>
                                    <strong>Judul Original:</strong> <?= htmlspecialchars($seminar->judul) ?><br>
                                    <strong>Judul Seminar:</strong> <?= htmlspecialchars($seminar->judul_seminar) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- ✅ ENHANCED: Detail Pelaksanaan Seminar (Jika Sudah Dijadwalkan) -->
                <?php if($seminar->status === 'scheduled' && !empty($seminar->tanggal_seminar)): ?>
                <div class="card mb-4" style="background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);">
                    <div class="card-body">
                        <h5 class="text-white mb-3">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Detail Pelaksanaan Seminar Proposal
                        </h5>
                        <div class="row text-white">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <small class="text-white-50">Tanggal Seminar:</small><br>
                                    <strong class="h6"><?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <small class="text-white-50">Jam Seminar:</small><br>
                                    <strong class="h6"><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <small class="text-white-50">Tempat:</small><br>
                                    <strong class="h6"><?= htmlspecialchars($seminar->tempat_seminar) ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ✅ FIXED: Tim Penguji yang Sudah Ditunjuk -->
                        <hr style="border-color: rgba(255,255,255,0.3);">
                        <h6 class="text-white mb-3">
                            <i class="fas fa-users mr-2"></i>
                            Tim Penguji
                        </h6>
                        <div class="row text-white">
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <small class="text-white-50">Pembimbing:</small><br>
                                    <!-- ✅ PERBAIKAN: Akses data pembimbing yang benar -->
                                    <strong><?= htmlspecialchars($seminar->nama_pembimbing ?? 'Belum ditetapkan') ?></strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <small class="text-white-50">Penguji 1:</small><br>
                                    <strong><?= htmlspecialchars($seminar->nama_penguji1 ?? 'Belum ditentukan') ?></strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">
                                    <small class="text-white-50">Penguji 2:</small><br>
                                    <strong><?= htmlspecialchars($seminar->nama_penguji2 ?? 'Belum ditentukan') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Tanggal dan Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">TANGGAL PENGAJUAN</label>
                            <p class="mb-0"><?= date('d F Y, H:i', strtotime($seminar->created_at)) ?> WIT</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="text-sm font-weight-bold text-muted mb-1">STATUS PEMBIMBING</label>
                            <p class="mb-0">
                                <?php if($seminar->status_pembimbing === 'approved'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check mr-1"></i>Disetujui
                                    </span>
                                <?php elseif($seminar->status_pembimbing === 'rejected'): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times mr-1"></i>Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock mr-1"></i>Menunggu
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- File Proposal -->
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">FILE PROPOSAL</label>
                    <?php if(!empty($seminar->file_proposal)): ?>
                        <div class="d-flex align-items-center p-3 border rounded bg-light">
                            <div class="mr-3">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1 font-weight-600">Proposal_<?= htmlspecialchars($seminar->nama_mahasiswa) ?>.pdf</p>
                                <small class="text-muted">File proposal untuk seminar</small>
                            </div>
                            <div>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   class="btn btn-outline-primary btn-sm mr-2" target="_blank">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </a>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   class="btn btn-outline-success btn-sm" download>
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            File proposal tidak tersedia
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Keterangan Mahasiswa -->
                <?php if(!empty($seminar->keterangan_mahasiswa)): ?>
                <div class="form-group mb-4">
                    <label class="text-sm font-weight-bold text-muted mb-2">KETERANGAN MAHASISWA</label>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Komentar Pembimbing (Jika Ada) -->
                <?php if(!empty($seminar->komentar_pembimbing)): ?>
                <div class="form-group mb-0">
                    <label class="text-sm font-weight-bold text-muted mb-2">KOMENTAR PEMBIMBING SEBELUMNYA</label>
                    <div class="alert alert-primary mb-0">
                        <i class="fas fa-comment-dots mr-2"></i>
                        <?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    <!-- ✅ FIXED: Jurnal Bimbingan Summary -->
    <div class="card shadow mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-book mr-2"></i>
                Status Jurnal Bimbingan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="text-center p-3">
                        <div class="display-4 font-weight-bold text-primary">
                            <!-- ✅ PERBAIKAN: Handle berbagai format data yang mungkin dikembalikan -->
                            <?php
                            $count = 0;
                            if (isset($jurnal_requirement['jurnal_validated_count'])) {
                                $count = $jurnal_requirement['jurnal_validated_count'];
                            } elseif (isset($jurnal_requirement['total_approved'])) {
                                $count = $jurnal_requirement['total_approved'];
                            } elseif (isset($jurnal_requirement['total_validated'])) {
                                $count = $jurnal_requirement['total_validated'];
                            }
                            echo $count;
                            ?>
                        </div>
                        <p class="text-muted mb-0">Total Pertemuan</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-3">
                        <!-- ✅ PERBAIKAN: Handle berbagai format status yang mungkin dikembalikan -->
                        <?php 
                        $is_sufficient = false;
                        if (isset($jurnal_requirement['eligible'])) {
                            $is_sufficient = $jurnal_requirement['eligible'];
                        } elseif (isset($jurnal_requirement['is_sufficient'])) {
                            $is_sufficient = $jurnal_requirement['is_sufficient'];
                        } elseif (isset($jurnal_requirement['sufficient'])) {
                            $is_sufficient = $jurnal_requirement['sufficient'];
                        }
                        
                        if ($is_sufficient): 
                        ?>
                            <div class="display-4 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="text-success mb-0 font-weight-600">Syarat Terpenuhi</p>
                        <?php else: ?>
                            <div class="display-4 text-warning">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <p class="text-warning mb-0 font-weight-600">
                                Butuh <?= max(0, 8 - $count) ?> lagi
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- ✅ PERBAIKAN: Alert message yang robust -->
            <?php if ($is_sufficient): ?>
                <div class="alert alert-success text-center mb-0">
                    <i class="fas fa-thumbs-up mr-2"></i>
                    Mahasiswa telah memenuhi syarat minimal 8 jurnal bimbingan
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    <?php 
                    $message = 'Mahasiswa belum memenuhi syarat minimal 8 jurnal bimbingan';
                    if (isset($jurnal_requirement['message'])) {
                        $message = $jurnal_requirement['message'];
                    }
                    echo $message;
                    ?>
                </div>
            <?php endif; ?>
                
                <!-- ✅ TAMBAHAN: Detail jurnal jika tersedia -->
                <?php if (!empty($jurnal_bimbingan)): ?>
                <hr>
                <h6 class="text-muted mb-3">Riwayat Jurnal Bimbingan Tervalidasi:</h6>
                <div class="row">
                    <?php foreach (array_slice($jurnal_bimbingan, 0, 6) as $jurnal): ?>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Pertemuan <?= $jurnal->pertemuan_ke ?? '-' ?>
                            <?php if (!empty($jurnal->tanggal_bimbingan)): ?>
                                <br><span class="text-xs"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($jurnal_bimbingan) > 6): ?>
                    <div class="col-12">
                        <small class="text-muted text-center">
                            <i class="fas fa-ellipsis-h"></i>
                            Dan <?= count($jurnal_bimbingan) - 6 ?> jurnal lainnya
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Form Rekomendasi -->
    <div class="col-xl-4 col-lg-5">
        <?php if($seminar->status === 'review_pembimbing' && $seminar->status_pembimbing === 'pending'): ?>
        <!-- FORM REKOMENDASI -->
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h5 class="text-white mb-0">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Form Rekomendasi
                </h5>
            </div>
            <div class="card-body">
                <?= form_open('dosen/seminar_proposal/rekomendasi', [
                    'id' => 'form-rekomendasi'
                ]) ?>
                
                    <?= form_hidden('seminar_id', $seminar->id) ?>
                    
                    <!-- Keputusan -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-gavel mr-1"></i>
                            Rekomendasi <span class="text-danger">*</span>
                        </label>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="approve" name="rekomendasi" value="approved" 
                                   class="custom-control-input" required>
                            <label class="custom-control-label" for="approve">
                                <span class="text-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Setujui untuk Seminar
                                </span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="reject" name="rekomendasi" value="rejected" 
                                   class="custom-control-input" required>
                            <label class="custom-control-label" for="reject">
                                <span class="text-danger">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Tolak & Minta Perbaikan
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Komentar -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-comment mr-1"></i>
                            Komentar & Catatan
                        </label>
                        <textarea class="form-control" name="komentar_pembimbing" rows="4" 
                                  placeholder="Berikan komentar atau catatan..."></textarea>
                        <small class="text-muted">Wajib diisi jika menolak proposal</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block" id="btn-submit">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Rekomendasi
                    </button>
                    
                <?= form_close() ?>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Status Information -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    Status Pengajuan
                </h5>
            </div>
            <div class="card-body">
                <?php if($seminar->status_pembimbing === 'approved'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Sudah Direkomendasi</strong>
                        <br>Anda telah memberikan rekomendasi untuk proposal ini.
                        
                        <?php if(!empty($seminar->tanggal_review_pembimbing)): ?>
                            <hr>
                            <small><strong>Tanggal rekomendasi:</strong> <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($seminar->status === 'scheduled'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-check mr-2"></i>
                            <strong>Seminar Terjadwal</strong>
                            <br>Seminar proposal sudah dijadwalkan oleh Kaprodi.
                        </div>
                    <?php elseif($seminar->status_kaprodi === 'pending'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Menunggu Review Kaprodi</strong>
                            <br>Proposal sedang menunggu validasi plagiarisme dari Kaprodi.
                        </div>
                    <?php endif; ?>
                    
                <?php elseif($seminar->status_pembimbing === 'rejected'): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-2"></i>
                        <strong>Proposal Ditolak</strong>
                        <br>Anda telah menolak proposal ini untuk perbaikan.
                        
                        <?php if(!empty($seminar->komentar_pembimbing)): ?>
                            <hr>
                            <strong>Catatan Anda:</strong><br>
                            <?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Status:</strong> <?= ucfirst($seminar->status) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- ✅ ENHANCED: Info Pembimbing Card -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-tie mr-2"></i>
                    Info Pembimbing
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar rounded-circle mr-3" style="background-color: #11cdef; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white font-weight-bold">
                            <?= strtoupper(substr($seminar->nama_pembimbing ?? 'B', 0, 1)) ?>
                        </span>
                    </div>
                    <div>
                        <p class="mb-1 font-weight-600">
                            <!-- ✅ PERBAIKAN: Display nama pembimbing yang benar -->
                            <?= htmlspecialchars($seminar->nama_pembimbing ?? 'Data pembimbing tidak tersedia') ?>
                        </p>
                        <?php if (!empty($seminar->email_pembimbing)): ?>
                        <small class="text-muted">
                            <i class="fas fa-envelope mr-1"></i>
                            <?= htmlspecialchars($seminar->email_pembimbing) ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Guidelines -->
        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Panduan Rekomendasi
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="icon-circle bg-success text-white mr-3 flex-shrink-0">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <strong>Jurnal Bimbingan</strong><br>
                        <small class="text-muted">Minimal 8 pertemuan sudah terpenuhi</small>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <div class="icon-circle bg-info text-white mr-3 flex-shrink-0">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <strong>Kelengkapan Proposal</strong><br>
                        <small class="text-muted">Periksa format dan substansi</small>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div class="icon-circle bg-warning text-white mr-3 flex-shrink-0">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <strong>Catatan Perbaikan</strong><br>
                        <small class="text-muted">Berikan feedback konstruktif</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <?php if($seminar->status === 'scheduled'): ?>
        <div class="card shadow mt-4">
            <div class="card-body text-center">
                <h6 class="mb-3">
                    <i class="fas fa-tools mr-2"></i>
                    Aksi Lanjutan
                </h6>
                <a href="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" 
                   class="btn btn-success btn-block">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Form Penilaian Seminar
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Custom CSS -->
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.icon-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.display-4 {
    font-size: 2.5rem;
}

.font-weight-600 {
    font-weight: 600;
}

.card {
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

@media (max-width: 991.98px) {
    .col-lg-5 {
        margin-top: 1.5rem;
    }
}
</style>

<?php
// Tangkap content
$content = ob_get_clean();

// Start script output buffering
ob_start();
?>

<script>
$(document).ready(function() {
    // Form validation
    $('#form-rekomendasi').on('submit', function(e) {
        const rekomendasi = $('input[name="rekomendasi"]:checked').val();
        const komentar = $('textarea[name="komentar_pembimbing"]').val().trim();
        
        // Validate comment for rejection
        if (rekomendasi === 'rejected' && komentar === '') {
            e.preventDefault();
            alert('Komentar wajib diisi untuk penolakan!');
            $('textarea[name="komentar_pembimbing"]').focus();
            return false;
        }
        
        // Confirm submission
        const message = rekomendasi === 'approved' ? 
            'Yakin menyetujui proposal ini untuk seminar?' :
            'Yakin menolak proposal ini? Mahasiswa akan diminta melakukan perbaikan.';
            
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $('#btn-submit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut();
    }, 5000);
});
</script>

<?php
// Tangkap script
$script = ob_get_clean();

// Output untuk template
echo $content;
?>