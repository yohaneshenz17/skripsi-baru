<?php
// File: application/views/staf/laporan/index.php
// Halaman Laporan untuk Staf Akademik

// Capture content untuk template
ob_start();
?>

<!-- Header dengan informasi dan waktu -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow mr-3">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <h2 class="mb-1 text-dark font-weight-bold"><?= isset($title) ? $title : 'Laporan Tugas Akhir' ?></h2>
                <p class="text-secondary mb-0 font-weight-medium">Laporan dan statistik tugas akhir mahasiswa</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-right">
        <div class="card bg-gradient-warning border-0">
            <div class="card-body p-3">
                <div class="text-white">
                    <h6 class="text-white mb-1">
                        <i class="fas fa-user-tie"></i> 
                        <?= $this->session->userdata('nama') ?: 'Staf Akademik' ?>
                    </h6>
                    <small>
                        <i class="fas fa-clock"></i> 
                        <?= date('d F Y, H:i') ?> WIB
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> 
        <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Proposal</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($summary['total_proposal']) ? $summary['total_proposal'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-calendar"></i>
                    </span>
                    <span class="text-nowrap">Periode <?= isset($current_periode) ? $current_periode : date('Y') ?></span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tahap Bimbingan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($summary['bimbingan']) ? $summary['bimbingan'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="text-nowrap">Sedang bimbingan</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tahap Penelitian</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($summary['penelitian']) ? $summary['penelitian'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2">
                        <i class="fas fa-search"></i>
                    </span>
                    <span class="text-nowrap">Meneliti</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($summary['publikasi']) ? $summary['publikasi'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="text-nowrap">Tahap akhir</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-filter text-primary"></i>
                            Filter Laporan
                        </h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('staf/laporan') ?>" class="row" id="filterForm">
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="periode" class="form-control-label">Periode Tahun</label>
                            <select name="periode" id="periode" class="form-control">
                                <?php if (isset($tahun_list) && is_array($tahun_list)): ?>
                                    <?php foreach ($tahun_list as $tahun): ?>
                                        <option value="<?= $tahun ?>" <?= ($current_periode == $tahun) ? 'selected' : '' ?>>
                                            <?= $tahun ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="prodi_id" class="form-control-label">Program Studi</label>
                            <select name="prodi_id" id="prodi_id" class="form-control">
                                <option value="">-- Semua Program Studi --</option>
                                <?php if (isset($prodi_list) && is_array($prodi_list)): ?>
                                    <?php foreach ($prodi_list as $prodi): ?>
                                        <option value="<?= $prodi->id ?>" <?= ($current_prodi == $prodi->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($prodi->nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="mt-2">
                    <button type="submit" form="filterForm" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Update Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menu Laporan -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-list text-primary"></i>
                    Menu Laporan
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-gradient-default shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow mb-3 mx-auto">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h5 class="text-white">Laporan per Tahapan</h5>
                                <p class="text-white-50 mb-3">
                                    Lihat detail mahasiswa berdasarkan tahapan workflow tugas akhir
                                </p>
                                <a href="<?= site_url('staf/laporan/tahapan') ?>" class="btn btn-white btn-sm">
                                    <i class="fas fa-eye"></i> Lihat Laporan
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-gradient-info shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow mb-3 mx-auto">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="text-white">Laporan Progress</h5>
                                <p class="text-white-50 mb-3">
                                    Pantau progress dan perkembangan tugas akhir mahasiswa
                                </p>
                                <a href="<?= site_url('staf/laporan/progress') ?>" class="btn btn-white btn-sm">
                                    <i class="fas fa-chart-line"></i> Lihat Progress
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card bg-gradient-success shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon icon-shape bg-white text-dark rounded-circle shadow mb-3 mx-auto">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h5 class="text-white">Statistik</h5>
                                <p class="text-white-50 mb-3">
                                    Analisis statistik dan trend tugas akhir mahasiswa
                                </p>
                                <a href="<?= site_url('staf/laporan/statistik') ?>" class="btn btn-white btn-sm">
                                    <i class="fas fa-chart-bar"></i> Lihat Statistik
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Statistics Chart -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-chart-pie text-primary"></i>
                    Distribusi Tahapan Workflow
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($workflow_stats) && count($workflow_stats) > 0): ?>
                    <div class="row">
                        <?php foreach ($workflow_stats as $stat): ?>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <div class="card bg-gradient-light border-0">
                                    <div class="card-body text-center p-3">
                                        <h4 class="text-dark font-weight-bold mb-1"><?= $stat['total'] ?></h4>
                                        <p class="text-muted mb-0 text-sm"><?= $stat['label'] ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                        <h5 class="text-muted">Belum ada data statistik</h5>
                        <p class="text-muted mb-0">Data akan muncul setelah ada mahasiswa yang mengajukan proposal.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Export Section -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-1">Export Laporan</h4>
                        <p class="text-muted mb-0">Download laporan dalam format Excel untuk analisis lebih lanjut</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <a href="<?= site_url('staf/laporan/export?type=summary&periode=' . $current_periode . '&prodi_id=' . $current_prodi) ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-file-excel"></i> Summary
                            </a>
                            <a href="<?= site_url('staf/laporan/export?type=progress&prodi_id=' . $current_prodi) ?>" 
                               class="btn btn-info" target="_blank">
                                <i class="fas fa-chart-line"></i> Progress
                            </a>
                            <a href="<?= site_url('staf/laporan/export?type=statistik&periode=' . $current_periode) ?>" 
                               class="btn btn-warning" target="_blank">
                                <i class="fas fa-chart-bar"></i> Statistik
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Development -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-info-circle fa-2x"></i>
                </div>
                <div class="col">
                    <h5 class="mb-1">Status Pengembangan</h5>
                    <p class="mb-0">
                        Halaman laporan ini masih dalam tahap pengembangan. 
                        Fitur-fitur laporan detail dan chart interaktif akan ditambahkan secara bertahap.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();

// Script untuk interaktivitas
ob_start();
?>
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Auto-submit form on filter change
    $('#periode, #prodi_id').change(function() {
        $('#filterForm').submit();
    });
});
</script>
<?php 
$script = ob_get_clean();

// Load template staf dengan data yang sudah disiapkan
$this->load->view('template/staf', [
    'title' => isset($title) ? $title : 'Laporan Tugas Akhir',
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>