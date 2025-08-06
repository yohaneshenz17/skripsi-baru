<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Dashboard') ?>

<?php $this->app->section() ?>

<!-- Custom CSS untuk perbaikan UI -->
<style>
/* Perbaikan spacing dan layout */
.row {
    margin-left: -7.5px;
    margin-right: -7.5px;
}

.row > [class*="col-"] {
    padding-left: 7.5px;
    padding-right: 7.5px;
}

/* Mengurangi gap antar section */
.dashboard-section {
    margin-bottom: 1.5rem !important;
}

.dashboard-section:last-child {
    margin-bottom: 0 !important;
}

/* Optimasi card spacing */
.card {
    box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
    border: none;
    transition: all 0.15s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 2rem 0 rgba(136, 152, 170, 0.2);
}

/* Responsive optimization untuk laptop */
@media (min-width: 1200px) {
    .container-fluid {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    /* Mengurangi padding card untuk efisiensi ruang */
    .card-body {
        padding: 1.25rem;
    }
    
    /* Optimasi height card stats */
    .card-stats {
        min-height: 120px;
    }
    
    /* Progress chart height optimization */
    .progress-chart-container {
        min-height: 350px;
    }
}

/* Legend styling improvement */
.legend-color {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    flex-shrink: 0;
}

.progress-legend .legend-item {
    transition: all 0.2s ease;
    padding: 0.25rem 0;
}

.progress-legend .legend-item:hover {
    background-color: #f8f9fa;
    border-radius: 6px;
    padding: 0.5rem;
    margin: -0.25rem;
}

/* Table optimization */
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    color: #8898aa;
}

/* Avatar optimization */
.avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Timeline optimization */
.timeline-step {
    width: 2.25rem;
    height: 2.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.875rem;
}

/* Quick actions styling */
.list-group-item {
    border: 1px solid #e9ecef;
    transition: all 0.15s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    transform: translateX(2px);
}

.list-group-item.border-warning:hover {
    background-color: #fff3cd;
    border-color: #ffc107;
}

/* Icon optimization */
.icon-shape {
    padding: 12px;
    text-align: center;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-shape.icon-sm {
    width: 48px;
    height: 48px;
}

/* Responsive text */
@media (max-width: 1399px) {
    .h2 {
        font-size: 1.75rem;
    }
    
    .card-title {
        font-size: 0.8rem;
    }
}

/* Loading state */
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<!-- Welcome Message -->
<div class="dashboard-section">
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="text-white mb-0">👋 Selamat Datang, <?= $this->session->userdata('nama') ?>!</h3>
                            <p class="text-white mt-2 mb-0 opacity-8">
                                Kelola pembimbingan mahasiswa Anda dengan mudah melalui dashboard ini.
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                                <i class="fa fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - OPTIMIZED LAYOUT -->
<div class="dashboard-section">
    <div class="row">
        <!-- 1. MENUNGGU PERSETUJUAN (Prioritas Pertama) -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Persetujuan</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats_tambahan['menunggu_persetujuan']) ? $stats_tambahan['menunggu_persetujuan'] : '0' ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                                <i class="fa fa-hourglass-half"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-warning mr-2"><i class="fa fa-exclamation-triangle"></i></span>
                        <span class="text-nowrap">Perlu review</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- 2. MAHASISWA BIMBINGAN (Kedua) -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Mahasiswa Bimbingan</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($total_mahasiswa_bimbingan) ? $total_mahasiswa_bimbingan : '0' ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                        <span class="text-nowrap">Total aktif</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- 3. BIMBINGAN BULAN INI (Ketiga) -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Bimbingan Bulan Ini</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats_tambahan['bimbingan_bulan_ini']) ? $stats_tambahan['bimbingan_bulan_ini'] : '0' ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                                <i class="fa fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-info mr-2"><i class="fa fa-clock"></i></span>
                        <span class="text-nowrap"><?= date('F Y') ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- 4. SUDAH PUBLIKASI (Keempat) -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Sudah Publikasi</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats_tambahan['selesai_publikasi']) ? $stats_tambahan['selesai_publikasi'] : '0' ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                                <i class="fa fa-trophy"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                        <span class="text-nowrap">Selesai total</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OPTIMIZED: Progress & Quick Actions Layout -->
<div class="dashboard-section">
    <div class="row">
        <!-- Progress Bimbingan Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card progress-chart-container">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">📊 Progress Bimbingan Mahasiswa</h3>
                            <p class="text-sm mb-0 text-muted">Distribusi tahapan skripsi mahasiswa bimbingan Anda</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8 col-md-7">
                            <canvas id="progress-chart" style="max-height: 280px;"></canvas>
                        </div>
                        <div class="col-lg-4 col-md-5">
                            <!-- Progress Legend -->
                            <div class="progress-legend">
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-warning mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['pengajuan_proposal']) ? $progress_bimbingan['pengajuan_proposal'] : '0' ?></span>
                                            <small class="d-block text-muted">Pengajuan Proposal</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-info mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['bimbingan_proposal']) ? $progress_bimbingan['bimbingan_proposal'] : '0' ?></span>
                                            <small class="d-block text-muted">Bimbingan Proposal</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-primary mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['seminar_proposal']) ? $progress_bimbingan['seminar_proposal'] : '0' ?></span>
                                            <small class="d-block text-muted">Seminar Proposal</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-secondary mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['penelitian']) ? $progress_bimbingan['penelitian'] : '0' ?></span>
                                            <small class="d-block text-muted">Penelitian</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-danger mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['seminar_skripsi']) ? $progress_bimbingan['seminar_skripsi'] : '0' ?></span>
                                            <small class="d-block text-muted">Seminar Skripsi</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="legend-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-color bg-success mr-3"></div>
                                        <div>
                                            <span class="font-weight-bold"><?= isset($progress_bimbingan['publikasi']) ? $progress_bimbingan['publikasi'] : '0' ?></span>
                                            <small class="d-block text-muted">Publikasi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">⚡ Quick Actions</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        
                        <!-- Usulan Proposal -->
                        <a href="<?= base_url('dosen/usulan_proposal') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-warning text-white rounded-circle">
                                        <i class="fa fa-file-alt"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Usulan Proposal</span>
                                    <p class="text-sm text-muted mb-0">Review proposal mahasiswa</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Bimbingan -->
                        <a href="<?= base_url('dosen/bimbingan') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-info text-white rounded-circle">
                                        <i class="fa fa-users"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Bimbingan</span>
                                    <p class="text-sm text-muted mb-0">Validasi jurnal bimbingan</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Seminar Proposal -->
                        <a href="<?= base_url('dosen/seminar_proposal') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-success text-white rounded-circle">
                                        <i class="fa fa-chalkboard-teacher"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Seminar Proposal</span>
                                    <p class="text-sm text-muted mb-0">Rekomendasi seminar</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Penelitian -->
                        <a href="<?= base_url('dosen/penelitian') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-purple text-white rounded-circle">
                                        <i class="fa fa-flask"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Penelitian</span>
                                    <p class="text-sm text-muted mb-0">Rekomendasi surat izin</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Seminar Skripsi -->
                        <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-danger text-white rounded-circle">
                                        <i class="fa fa-graduation-cap"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Seminar Skripsi</span>
                                    <p class="text-sm text-muted mb-0">Penilaian seminar akhir</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Publikasi -->
                        <a href="<?= base_url('dosen/publikasi') ?>" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-primary text-white rounded-circle">
                                        <i class="fa fa-book-open"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold">Publikasi</span>
                                    <p class="text-sm text-muted mb-0">Review publikasi tugas akhir</p>
                                </div>
                            </div>
                        </a>

                        <!-- TAMBAHAN: Download Panduan Skripsi -->
                        <a href="https://stkyakobus.ac.id/wp-content/uploads/2020/10/PANDUAN-PENULISAN-SKRIPSI-2018.pdf" 
                           target="_blank" 
                           class="list-group-item list-group-item-action border-warning">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="icon icon-shape icon-sm bg-warning text-white rounded-circle">
                                        <i class="ni ni-archive-2"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <span class="font-weight-bold text-warning">Download Panduan Skripsi</span>
                                    <p class="text-sm text-muted mb-0">Panduan penulisan skripsi terbaru</p>
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-external-link-alt text-warning"></i>
                                </div>
                            </div>
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OPTIMIZED: Mahasiswa Bimbingan & Recent Activities -->
<div class="dashboard-section">
    <div class="row">
        <!-- Mahasiswa Bimbingan -->
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">👥 Mahasiswa Bimbingan</h3>
                            <p class="text-sm mb-0 text-muted">Daftar mahasiswa yang sedang Anda bimbing</p>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('dosen/bimbingan') ?>" class="btn btn-sm btn-primary">
                                Lihat Semua
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($mahasiswa_bimbingan)): ?>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mahasiswa_bimbingan as $mhs): ?>
                                    <tr>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="avatar rounded-circle mr-3">
                                                    <?php
                                                    $foto_mhs = (!empty($mhs->foto)) ? $mhs->foto : 'default.png';
                                                    $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mhs);
                                                    ?>
                                                    <img alt="Foto <?= $mhs->nama_mahasiswa ?>" src="<?= $foto_path ?>" 
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold"><?= $mhs->nama_mahasiswa ?></span>
                                                    <br>
                                                    <small class="text-muted">NIM: <?= $mhs->nim ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm">
                                                <?= substr($mhs->judul, 0, 50) ?><?= strlen($mhs->judul) > 50 ? '...' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= isset($mhs->status_color) ? $mhs->status_color : 'secondary' ?>">
                                                <?= isset($mhs->status_display) ? $mhs->status_display : 'Unknown' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" 
                                                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <a class="dropdown-item" href="<?= base_url('dosen/bimbingan/detail_mahasiswa/' . $mhs->id) ?>">
                                                        <i class="fa fa-eye"></i> Detail Bimbingan
                                                    </a>
                                                    <?php if (isset($mhs->status_pembimbing) && $mhs->status_pembimbing == '1'): ?>
                                                        <a class="dropdown-item" href="<?= base_url('dosen/bimbingan') ?>">
                                                            <i class="fa fa-comments"></i> Jurnal Bimbingan
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa fa-users fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">Belum Ada Mahasiswa Bimbingan</h4>
                            <p class="text-muted">Saat ini Anda belum memiliki mahasiswa yang dibimbing.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">📝 Aktivitas Terbaru</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="timeline timeline-one-side">
                            <?php foreach (array_slice($recent_activities, 0, 6) as $activity): ?>
                            <div class="timeline-block mb-3">
                                <span class="timeline-step badge-<?= $activity->activity_type == 'jurnal_bimbingan' ? 'info' : 'success' ?>">
                                    <i class="fa fa-<?= $activity->activity_type == 'jurnal_bimbingan' ? 'comments' : 'check' ?>"></i>
                                </span>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        <?= date('d M Y', strtotime($activity->created_at)) ?>
                                    </small>
                                    <h6 class="text-sm mt-0 mb-1">
                                        <?= substr($activity->materi_bimbingan, 0, 40) ?><?= strlen($activity->materi_bimbingan) > 40 ? '...' : '' ?>
                                    </h6>
                                    <p class="text-muted text-xs mt-1 mb-0">
                                        <strong><?= $activity->nama_mahasiswa ?></strong> (<?= $activity->nim ?>)
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fa fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada aktivitas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/chartjs/Chart.min.css">
<script src="<?= base_url() ?>cdn/plugins/chartjs/Chart.min.js"></script>

<script>
$(document).ready(function() {
    // Progress Chart dengan error handling
    var progressData = [
        <?= isset($progress_bimbingan['pengajuan_proposal']) ? $progress_bimbingan['pengajuan_proposal'] : 0 ?>,
        <?= isset($progress_bimbingan['bimbingan_proposal']) ? $progress_bimbingan['bimbingan_proposal'] : 0 ?>,
        <?= isset($progress_bimbingan['seminar_proposal']) ? $progress_bimbingan['seminar_proposal'] : 0 ?>,
        <?= isset($progress_bimbingan['penelitian']) ? $progress_bimbingan['penelitian'] : 0 ?>,
        <?= isset($progress_bimbingan['seminar_skripsi']) ? $progress_bimbingan['seminar_skripsi'] : 0 ?>,
        <?= isset($progress_bimbingan['publikasi']) ? $progress_bimbingan['publikasi'] : 0 ?>
    ];
    
    var progressLabels = [
        'Pengajuan Proposal',
        'Bimbingan Proposal', 
        'Seminar Proposal',
        'Penelitian',
        'Seminar Skripsi',
        'Publikasi'
    ];
    
    var progressColors = [
        '#ffc107', // warning
        '#17a2b8', // info
        '#007bff', // primary
        '#6c757d', // secondary
        '#dc3545', // danger
        '#28a745'  // success
    ];
    
    // Only show chart if there's data
    var totalData = progressData.reduce((a, b) => a + b, 0);
    
    if (totalData > 0) {
        var ctx = document.getElementById('progress-chart');
        if (ctx) {
            ctx = ctx.getContext('2d');
            var progressChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: progressLabels,
                    datasets: [{
                        data: progressData,
                        backgroundColor: progressColors,
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var meta = dataset._meta[Object.keys(dataset._meta)[0]];
                                var total = meta.total;
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = parseFloat((currentValue/total*100).toFixed(1));
                                return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });
        }
    } else {
        // Show empty state
        var chartContainer = document.getElementById('progress-chart');
        if (chartContainer && chartContainer.parentNode) {
            chartContainer.parentNode.innerHTML = `
                <div class="text-center py-5">
                    <i class="fa fa-chart-pie fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum Ada Data</h5>
                    <p class="text-muted mb-0">Grafik akan muncul setelah ada mahasiswa bimbingan</p>
                </div>
            `;
        }
    }
    
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Smooth scroll untuk internal links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 300);
        }
    });
    
    // Auto refresh setiap 5 menit (opsional)
    // setTimeout(function() {
    //     location.reload();
    // }, 300000);
});

// Loading state untuk chart
function showChartLoading() {
    var chartContainer = document.getElementById('progress-chart');
    if (chartContainer && chartContainer.parentNode) {
        chartContainer.parentNode.innerHTML = `
            <div class="text-center py-5">
                <div class="loading-skeleton" style="width: 200px; height: 200px; border-radius: 50%; margin: 0 auto;"></div>
                <p class="text-muted mt-3">Memuat data...</p>
            </div>
        `;
    }
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>