<?php
// Prepare content with output buffering
ob_start();
?>

<div class="row">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_mahasiswa['total'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> <?= $total_mahasiswa['mengajukan_proposal'] ?></span>
                    <span class="text-nowrap">mengajukan proposal</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Dosen</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_dosen['total'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-arrow-up"></i> <?= $total_dosen['membimbing'] ?></span>
                    <span class="text-nowrap">sedang membimbing</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tugas Pending</h5>
                        <span class="h2 font-weight-bold mb-0"><?= array_sum($shortcuts) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
                    <span class="text-nowrap">perlu ditindaklanjuti</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tahun Akademik</h5>
                        <span class="h2 font-weight-bold mb-0"><?= date('Y') ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-calendar"></i></span>
                    <span class="text-nowrap">Ganjil <?= date('Y') ?>/<?= date('Y')+1 ?></span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <!-- Quick Shortcuts -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">⚡ Quick Shortcuts</h6>
                        <p class="text-sm mb-0">Akses cepat ke tugas-tugas yang perlu ditindaklanjuti</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-primary">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-primary mb-3">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <h4 class="text-white"><?= $shortcuts['bimbingan'] ?></h4>
                                <p class="text-white text-sm mb-0">Jurnal Bimbingan</p>
                                <a href="<?= base_url('staf/bimbingan') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-success">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-success mb-3">
                                    <i class="fas fa-presentation"></i>
                                </div>
                                <h4 class="text-white"><?= $shortcuts['seminar_proposal'] ?></h4>
                                <p class="text-white text-sm mb-0">Seminar Proposal</p>
                                <a href="<?= base_url('staf/seminar-proposal') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-info">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-info mb-3">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4 class="text-white"><?= $shortcuts['penelitian'] ?></h4>
                                <p class="text-white text-sm mb-0">Surat Izin Penelitian</p>
                                <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-warning">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-warning mb-3">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h4 class="text-white"><?= $shortcuts['seminar_skripsi'] ?></h4>
                                <p class="text-white text-sm mb-0">Seminar Skripsi</p>
                                <a href="<?= base_url('staf/seminar-skripsi') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-danger">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-danger mb-3">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <h4 class="text-white"><?= $shortcuts['publikasi'] ?></h4>
                                <p class="text-white text-sm mb-0">Publikasi Repository</p>
                                <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 bg-gradient-secondary">
                            <div class="card-body text-center text-white">
                                <div class="icon icon-shape icon-lg bg-white shadow rounded-circle text-secondary mb-3">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h4 class="text-white">∞</h4>
                                <p class="text-white text-sm mb-0">Laporan</p>
                                <a href="<?= base_url('staf/laporan') ?>" class="btn btn-sm btn-outline-white mt-3">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pengumuman Tahapan -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📢 Pengumuman Tahapan</h6>
                        <p class="text-sm mb-0">Deadline dan informasi penting</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if(!empty($pengumuman)): ?>
                    <?php foreach($pengumuman as $p): ?>
                        <div class="timeline-block mb-3">
                            <div class="timeline-step bg-gradient-primary">
                                <span class="text-white font-weight-bold"><?= $p->no ?></span>
                            </div>
                            <div class="timeline-content">
                                <h6 class="text-sm font-weight-bold mb-1"><?= $p->tahapan ?></h6>
                                <p class="text-xs text-muted mb-2">
                                    <i class="fas fa-calendar"></i> 
                                    Deadline: <?= date('d M Y', strtotime($p->tanggal_deadline)) ?>
                                </p>
                                <?php if($p->keterangan): ?>
                                    <p class="text-xs mb-0"><?= $p->keterangan ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Belum ada pengumuman tahapan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <!-- Workflow Statistics Chart -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📊 Statistik Workflow Mahasiswa</h6>
                        <p class="text-sm mb-0">Distribusi mahasiswa per tahapan tugas akhir</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="workflowChart" class="chart-canvas"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Workflow Progress -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">🎯 Progress Tahapan</h6>
            </div>
            <div class="card-body">
                <?php foreach($workflow_stats as $stat): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-sm font-weight-bold"><?= $stat['label'] ?></span>
                            <span class="badge badge-primary"><?= $stat['total'] ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-gradient-primary" role="progressbar" 
                                 style="width: <?= $stat['total'] > 0 ? ($stat['total'] / max(array_column($workflow_stats, 'total')) * 100) : 0 ?>%">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Capture content dan buat script untuk chart
$content = ob_get_clean();

// Script untuk chart
ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Workflow Chart
    var ctx = document.getElementById('workflowChart').getContext('2d');
    var workflowData = <?= json_encode($workflow_stats) ?>;
    
    var labels = workflowData.map(function(item) {
        return item.label;
    });
    
    var data = workflowData.map(function(item) {
        return item.total;
    });
    
    var colors = [
        '#5e72e4', '#11cdef', '#2dce89', '#fb6340', 
        '#f5365c', '#ffd600', '#6c757d'
    ];
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Auto refresh data setiap 5 menit
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
<?php 
$script = ob_get_clean();

// Load template dengan data yang sudah siap
$this->load->view('template/staf', [
    'title' => 'Dashboard Staf Akademik',
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>