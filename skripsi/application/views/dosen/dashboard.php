<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Dashboard') ?>

<?php $this->app->section() ?>

<!-- Welcome Message -->
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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Mahasiswa Bimbingan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_mahasiswa_bimbingan ?></span>
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
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Bimbingan Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats_tambahan['bimbingan_bulan_ini'] ?></span>
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
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats_tambahan['selesai_publikasi'] ?></span>
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
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Persetujuan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats_tambahan['menunggu_persetujuan'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
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
</div>

<div class="row">
    <!-- Progress Bimbingan Chart -->
    <div class="col-xl-8">
        <div class="card">
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
                    <div class="col-md-8">
                        <canvas id="progress-chart" style="max-height: 300px;"></canvas>
                    </div>
                    <div class="col-md-4">
                        <!-- Progress Legend -->
                        <div class="progress-legend">
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-warning mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['pengajuan_proposal'] ?></span>
                                        <small class="d-block text-muted">Pengajuan Proposal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-info mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['bimbingan_proposal'] ?></span>
                                        <small class="d-block text-muted">Bimbingan Proposal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-primary mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['seminar_proposal'] ?></span>
                                        <small class="d-block text-muted">Seminar Proposal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-secondary mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['penelitian'] ?></span>
                                        <small class="d-block text-muted">Penelitian</small>
                                    </div>
                                </div>
                            </div>
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-danger mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['seminar_skripsi'] ?></span>
                                        <small class="d-block text-muted">Seminar Skripsi</small>
                                    </div>
                                </div>
                            </div>
                            <div class="legend-item mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="legend-color bg-success mr-2"></div>
                                    <div>
                                        <span class="font-weight-bold"><?= $progress_bimbingan['publikasi'] ?></span>
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
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">⚡ Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
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
                            <div class="col-auto">
                                <?php if ($stats_tambahan['menunggu_persetujuan'] > 0): ?>
                                    <span class="badge badge-warning"><?= $stats_tambahan['menunggu_persetujuan'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url('dosen/bimbingan') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-shape icon-sm bg-info text-white rounded-circle">
                                    <i class="fa fa-comments"></i>
                                </div>
                            </div>
                            <div class="col">
                                <span class="font-weight-bold">Bimbingan</span>
                                <p class="text-sm text-muted mb-0">Kelola jurnal bimbingan</p>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url('dosen/seminar_proposal') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-shape icon-sm bg-primary text-white rounded-circle">
                                    <i class="fa fa-presentation"></i>
                                </div>
                            </div>
                            <div class="col">
                                <span class="font-weight-bold">Seminar Proposal</span>
                                <p class="text-sm text-muted mb-0">Jadwal dan penilaian</p>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="icon icon-shape icon-sm bg-danger text-white rounded-circle">
                                    <i class="fa fa-graduation-cap"></i>
                                </div>
                            </div>
                            <div class="col">
                                <span class="font-weight-bold">Seminar Skripsi</span>
                                <p class="text-sm text-muted mb-0">Ujian akhir skripsi</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mahasiswa Bimbingan & Recent Activities -->
<div class="row">
    <!-- Mahasiswa Bimbingan -->
    <div class="col-xl-8">
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
                                        <span class="badge badge-<?= $mhs->status_color ?>"><?= $mhs->status_display ?></span>
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
                                                <?php if ($mhs->status_pembimbing == '1'): ?>
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
    <div class="col-xl-4">
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

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/chartjs/Chart.min.css">
<script src="<?= base_url() ?>cdn/plugins/chartjs/Chart.min.js"></script>
<style>
.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}
.progress-legend .legend-item {
    transition: all 0.3s ease;
}
.progress-legend .legend-item:hover {
    background-color: #f8f9fa;
    border-radius: 5px;
    padding: 5px;
    margin: -5px;
}
.card-stats .card-body {
    padding: 1.5rem;
}
.timeline-step {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<script>
$(document).ready(function() {
    // Progress Chart
    var progressData = [
        <?= $progress_bimbingan['pengajuan_proposal'] ?>,
        <?= $progress_bimbingan['bimbingan_proposal'] ?>,
        <?= $progress_bimbingan['seminar_proposal'] ?>,
        <?= $progress_bimbingan['penelitian'] ?>,
        <?= $progress_bimbingan['seminar_skripsi'] ?>,
        <?= $progress_bimbingan['publikasi'] ?>
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
        var ctx = document.getElementById('progress-chart').getContext('2d');
        var progressChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: progressLabels,
                datasets: [{
                    data: progressData,
                    backgroundColor: progressColors,
                    borderWidth: 2,
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
    } else {
        // Show empty state
        $('#progress-chart').parent().html(`
            <div class="text-center py-5">
                <i class="fa fa-chart-pie fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Belum Ada Data</h5>
                <p class="text-muted mb-0">Grafik akan muncul setelah ada mahasiswa bimbingan</p>
            </div>
        `);
    }
    
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto refresh setiap 5 menit
    setTimeout(function() {
        location.reload();
    }, 300000);
});
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>