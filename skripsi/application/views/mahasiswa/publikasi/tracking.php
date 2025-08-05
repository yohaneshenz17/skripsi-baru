<?php
// PERBAIKAN UNTUK: application/views/mahasiswa/publikasi/tracking.php
// Progress tracking sesuai workflow 9 langkah
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tracking Publikasi Tugas Akhir</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/publikasi') ?>">Publikasi</a></li>
                        <li class="breadcrumb-item active">Tracking</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Progress Steps -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tasks"></i> Progress Publikasi</h3>
                </div>
                <div class="card-body">
                    
                    <!-- Progress Bar Visual -->
                    <div class="progress mb-4" style="height: 20px;">
                        <?php 
                        $progress_percentage = 0;
                        switch($publikasi->status) {
                            case 'draft': $progress_percentage = 10; break;
                            case 'submitted': case 'review_pembimbing': $progress_percentage = 30; break;
                            case 'review_staf': $progress_percentage = 60; break;
                            case 'completed': $progress_percentage = 100; break;
                            case 'rejected': $progress_percentage = 20; break;
                        }
                        ?>
                        <div class="progress-bar bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'primary') ?>" 
                             style="width: <?= $progress_percentage ?>%">
                            <?= $progress_percentage ?>%
                        </div>
                    </div>

                    <!-- Timeline Steps -->
                    <div class="timeline">
                        
                        <!-- Step 1: Syarat Terpenuhi -->
                        <div class="time-label">
                            <span class="bg-success">Step 1</span>
                        </div>
                        <div>
                            <i class="fas fa-check bg-success"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Syarat Publikasi Terpenuhi</h3>
                                <div class="timeline-body">
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Minimal 16 Jurnal Bimbingan Tervalidasi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Form Diisi -->
                        <div class="time-label">
                            <span class="bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>">Step 2</span>
                        </div>
                        <div>
                            <i class="fas fa-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'check' : 'clock' ?> 
                               bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> <?= $publikasi->tanggal_pengajuan ? date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) : '-' ?></span>
                                <h3 class="timeline-header">Form Pengajuan Publikasi Diisi</h3>
                                <div class="timeline-body">
                                    <?php if ($publikasi->status == 'draft'): ?>
                                        <p class="text-warning">Masih dalam tahap draft. Silakan lengkapi dan submit form.</p>
                                    <?php else: ?>
                                        <p class="text-success">Form pengajuan telah disubmit dengan lengkap.</p>
                                        <strong>Judul:</strong> <?= $publikasi->judul_skripsi_final ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Kirim Ajuan -->
                        <div class="time-label">
                            <span class="bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>">Step 3</span>
                        </div>
                        <div>
                            <i class="fas fa-paper-plane bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Ajuan Dikirim ke Dosen Pembimbing</h3>
                                <div class="timeline-body">
                                    <?php if (in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed'])): ?>
                                        <p class="text-success">Ajuan telah dikirim dan menunggu review dosen pembimbing.</p>
                                    <?php else: ?>
                                        <p class="text-muted">Belum dikirim.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4-6: Review Dosen -->
                        <div class="time-label">
                            <span class="bg-<?= in_array($publikasi->status, ['review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : ($publikasi->status == 'review_pembimbing' ? 'warning' : 'secondary')) ?>">Step 4-6</span>
                        </div>
                        <div>
                            <i class="fas fa-<?= in_array($publikasi->status, ['review_staf', 'completed']) ? 'check' : ($publikasi->status == 'rejected' ? 'times' : 'clock') ?> 
                               bg-<?= in_array($publikasi->status, ['review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : ($publikasi->status == 'review_pembimbing' ? 'warning' : 'secondary')) ?>"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> <?= $publikasi->tanggal_review_pembimbing ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : '-' ?></span>
                                <h3 class="timeline-header">Review Dosen Pembimbing</h3>
                                <div class="timeline-body">
                                    <?php if ($publikasi->status == 'review_pembimbing'): ?>
                                        <div class="alert alert-warning">
                                            <i class="icon fas fa-clock"></i> Sedang menunggu review dari dosen pembimbing.
                                        </div>
                                    <?php elseif (in_array($publikasi->status, ['review_staf', 'completed'])): ?>
                                        <div class="alert alert-success">
                                            <i class="icon fas fa-check"></i> Dosen pembimbing telah menyetujui publikasi.
                                        </div>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <strong>Komentar Dosen:</strong><br>
                                            <em>"<?= $publikasi->komentar_pembimbing ?>"</em>
                                        <?php endif; ?>
                                    <?php elseif ($publikasi->status == 'rejected'): ?>
                                        <div class="alert alert-danger">
                                            <i class="icon fas fa-times"></i> Publikasi ditolak oleh dosen pembimbing.
                                        </div>
                                        <?php if ($publikasi->komentar_pembimbing): ?>
                                            <strong>Alasan Penolakan:</strong><br>
                                            <em>"<?= $publikasi->komentar_pembimbing ?>"</em><br><br>
                                            <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Perbaiki dan Ajukan Ulang
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Menunggu pengajuan.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 7-8: Staf Validasi -->
                        <div class="time-label">
                            <span class="bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'review_staf' ? 'warning' : 'secondary') ?>">Step 7-8</span>
                        </div>
                        <div>
                            <i class="fas fa-<?= $publikasi->status == 'completed' ? 'check' : ($publikasi->status == 'review_staf' ? 'clock' : 'clock') ?> 
                               bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'review_staf' ? 'warning' : 'secondary') ?>"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> <?= $publikasi->tanggal_validasi_staf ? date('d/m/Y H:i', strtotime($publikasi->tanggal_validasi_staf)) : '-' ?></span>
                                <h3 class="timeline-header">Staf Input Repository dan Validasi</h3>
                                <div class="timeline-body">
                                    <?php if ($publikasi->status == 'review_staf'): ?>
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i> Sedang diproses oleh staf untuk input repository dan validasi final.
                                        </div>
                                    <?php elseif ($publikasi->status == 'completed'): ?>
                                        <div class="alert alert-success">
                                            <i class="icon fas fa-check"></i> Publikasi telah divalidasi oleh staf dan selesai diproses.
                                        </div>
                                        <?php if ($publikasi->link_repository): ?>
                                            <strong>Link Repository:</strong><br>
                                            <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-external-link-alt"></i> Lihat Publikasi
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($publikasi->catatan_staf): ?>
                                            <br><br><strong>Catatan Staf:</strong><br>
                                            <em>"<?= $publikasi->catatan_staf ?>"</em>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted">Menunggu tahap sebelumnya selesai.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 9: Selesai - Download Surat -->
                        <div class="time-label">
                            <span class="bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>">Step 9</span>
                        </div>
                        <div>
                            <i class="fas fa-trophy bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>"></i>
                            <div class="timeline-item">
                                <h3 class="timeline-header">Download Surat Keterangan Publikasi</h3>
                                <div class="timeline-body">
                                    <?php if ($publikasi->status == 'completed'): ?>
                                        <div class="alert alert-success">
                                            <i class="icon fas fa-trophy"></i> 
                                            <strong>Selamat!</strong> Publikasi tugas akhir Anda telah selesai diproses.
                                        </div>
                                        <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" 
                                           class="btn btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Download Surat Keterangan Publikasi
                                        </a>
                                    <?php else: ?>
                                        <p class="text-muted">Surat keterangan akan tersedia setelah semua tahap selesai.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- End Timeline -->
                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Detail Publikasi -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Detail Publikasi</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Mahasiswa</strong></td>
                                    <td>: <?= $publikasi->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM</strong></td>
                                    <td>: <?= $publikasi->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Judul Skripsi</strong></td>
                                    <td>: <?= $publikasi->judul_skripsi_final ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Dosen Pembimbing</strong></td>
                                    <td>: <?= $publikasi->nama_pembimbing ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Status</strong></td>
                                    <td>: 
                                        <span class="badge badge-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= ucwords(str_replace('_', ' ', $publikasi->status)) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pengajuan</strong></td>
                                    <td>: <?= $publikasi->tanggal_pengajuan ? date('d F Y', strtotime($publikasi->tanggal_pengajuan)) : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Repository</strong></td>
                                    <td>: 
                                        <?php if ($publikasi->link_repository): ?>
                                            <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-xs btn-info">
                                                <i class="fas fa-external-link-alt"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <em class="text-muted">Belum tersedia</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <?php if ($publikasi->status == 'rejected'): ?>
                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Perbaiki Pengajuan
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
</div>

<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50px;
    width: 2px;
    background: #adb5bd;
}

.timeline > li {
    position: relative;
    margin-right: 10px;
    margin-bottom: 15px;
}

.timeline > li > .timeline-item {
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}

.timeline > li > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}

.timeline > li > .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-weight: 600;
    font-size: 16px;
}

.timeline > li > .timeline-item > .timeline-body,
.timeline > li > .timeline-item > .timeline-footer {
    padding: 10px;
}

.timeline > li > .fa,
.timeline > li > .fas,
.timeline > li > .far,
.timeline > li > .fab,
.timeline > li > .fal,
.timeline > li > .fad,
.timeline > li > .svg-inline--fa {
    width: 30px;
    height: 30px;
    font-size: 15px;
    line-height: 30px;
    position: absolute;
    color: #666;
    background: #d2d6de;
    border-radius: 50%;
    text-align: center;
    left: 35px;
    top: 0;
}

.time-label > span {
    font-weight: 600;
    color: #fff;
    border-radius: 4px;
    padding: 5px 10px;
}
</style>