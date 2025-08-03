<?php
/**
 * FIXED DETAIL VIEW - Kaprodi Seminar Skripsi Detail
 * File: application/views/kaprodi/seminar_skripsi/detail.php
 * 
 * PERBAIKAN: Hapus template call untuk mengatasi double header
 * Hanya berisi pure content HTML tanpa ob_start dan template loading
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

<!-- Main Content -->
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <!-- Detail Seminar Card -->
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Detail Seminar Skripsi
                        </h3>
                        <p class="text-white-50 mt-1 mb-0">
                            <?= htmlspecialchars($seminar->nama_mahasiswa) ?> (<?= htmlspecialchars($seminar->nim) ?>)
                        </p>
                    </div>
                    <div class="col-auto">
                        <!-- Status Badge -->
                        <?php if($seminar->status_kaprodi === 'approved'): ?>
                            <span class="badge badge-success badge-lg">
                                <i class="fas fa-check mr-1"></i>Disetujui
                            </span>
                        <?php elseif($seminar->status_kaprodi === 'rejected'): ?>
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-times mr-1"></i>Ditolak
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-clock mr-1"></i>Menunggu Review
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Info Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="heading-small text-muted mb-3">Informasi Mahasiswa</h6>
                        <div class="pl-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Nama Mahasiswa</label>
                                <p class="form-control-static"><?= htmlspecialchars($seminar->nama_mahasiswa) ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">NIM</label>
                                <p class="form-control-static"><?= htmlspecialchars($seminar->nim) ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Email</label>
                                <p class="form-control-static"><?= htmlspecialchars($seminar->email_mahasiswa) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="heading-small text-muted mb-3">Informasi Pembimbing</h6>
                        <div class="pl-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Dosen Pembimbing</label>
                                <p class="form-control-static"><?= htmlspecialchars($seminar->nama_pembimbing ?? '-') ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Pengajuan</label>
                                <p class="form-control-static">
                                    <?= isset($seminar->created_at) ? date('d F Y, H:i', strtotime($seminar->created_at)) : '-' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Judul Skripsi -->
                <div class="row">
                    <div class="col">
                        <h6 class="heading-small text-muted mb-3">Judul Skripsi</h6>
                        <div class="pl-lg-2">
                            <div class="form-group">
                                <p class="form-control-static font-weight-bold">
                                    <?= htmlspecialchars($seminar->judul ?? '-') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- File Skripsi -->
                <div class="row">
                    <div class="col">
                        <h6 class="heading-small text-muted mb-3">File Skripsi</h6>
                        <div class="pl-lg-2">
                            <?php if (!empty($seminar->file_skripsi)): ?>
                                <div class="form-group">
                                    <a href="<?= base_url('uploads/seminar_skripsi/' . $seminar->file_skripsi) ?>" 
                                       class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fas fa-file-pdf mr-1"></i>
                                        Lihat File Skripsi
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">File skripsi belum diupload</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Actions -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Aksi Kaprodi</h5>
            </div>
            <div class="card-body">
                <?php if ($seminar->status_kaprodi === 'pending'): ?>
                    <div class="text-center">
                        <p class="text-muted mb-3">Seminar ini menunggu review dari Kaprodi</p>
                        
                        <button class="btn btn-success btn-sm mr-2" onclick="approveSubmission()">
                            <i class="fas fa-check mr-1"></i>
                            Setujui
                        </button>
                        
                        <button class="btn btn-danger btn-sm" onclick="rejectSubmission()">
                            <i class="fas fa-times mr-1"></i>
                            Tolak
                        </button>
                    </div>
                <?php elseif ($seminar->status_kaprodi === 'approved'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-1"></i>
                        <strong>Disetujui</strong>
                        <br>
                        <small>Seminar sudah disetujui Kaprodi</small>
                    </div>
                    
                    <?php if (empty($seminar->tanggal_seminar)): ?>
                        <div class="text-center">
                            <p class="text-muted mb-3">Perlu dijadwalkan</p>
                            <button class="btn btn-primary btn-sm" onclick="scheduleModal()">
                                <i class="fas fa-calendar-plus mr-1"></i>
                                Jadwalkan Seminar
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-check mr-1"></i>
                            <strong>Terjadwal</strong>
                            <br>
                            <small><?= date('d F Y, H:i', strtotime($seminar->tanggal_seminar . ' ' . $seminar->jam_seminar)) ?></small>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-1"></i>
                        <strong>Ditolak</strong>
                        <br>
                        <small>Seminar ditolak oleh Kaprodi</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="card shadow mt-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">📋 Status Progress</h6>
                <div class="progress-wrapper">
                    <div class="progress-info">
                        <div class="progress-label">
                            <span>Progress Seminar</span>
                        </div>
                        <div class="progress-percentage">
                            <span>
                                <?php if ($seminar->status_kaprodi === 'approved' && !empty($seminar->tanggal_seminar)): ?>
                                    100%
                                <?php elseif ($seminar->status_kaprodi === 'approved'): ?>
                                    75%
                                <?php elseif ($seminar->status_kaprodi === 'pending'): ?>
                                    50%
                                <?php else: ?>
                                    25%
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?php 
                                if ($seminar->status_kaprodi === 'approved' && !empty($seminar->tanggal_seminar)) echo '100';
                                elseif ($seminar->status_kaprodi === 'approved') echo '75';
                                elseif ($seminar->status_kaprodi === 'pending') echo '50';
                                else echo '25';
                             ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveSubmission() {
    if (confirm('Yakin ingin menyetujui seminar skripsi ini?')) {
        // TODO: Implement approval logic
        alert('Fitur approval akan diimplementasi sesuai kebutuhan');
    }
}

function rejectSubmission() {
    const reason = prompt('Alasan penolakan:');
    if (reason && reason.trim()) {
        // TODO: Implement rejection logic
        alert('Fitur rejection akan diimplementasi sesuai kebutuhan');
    }
}

function scheduleModal() {
    // TODO: Implement scheduling modal
    alert('Fitur penjadwalan akan diimplementasi sesuai kebutuhan');
}
</script>