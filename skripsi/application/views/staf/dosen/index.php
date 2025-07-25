<?php
// File: application/views/staf/dosen/index.php
// Daftar Dosen untuk Staf Akademik - FOKUS HANYA DOSEN LEVEL 2

// Capture content untuk template
ob_start();
?>

<!-- Header dengan informasi dan waktu -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow mr-3">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <h2 class="mb-1 text-dark font-weight-bold"><?= isset($title) ? $title : 'Daftar Dosen' ?></h2>
                <p class="text-secondary mb-0 font-weight-medium">Daftar dosen aktif semua program studi</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-right">
        <div class="card bg-gradient-info border-0">
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

<!-- Statistics Cards - FOKUS PADA DATA RELEVAN -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Dosen Aktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total_dosen']) ? $statistics['total_dosen'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="text-nowrap">Dosen level 2</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Kaprodi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total_kaprodi']) ? $statistics['total_kaprodi'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2">
                        <i class="fas fa-crown"></i>
                    </span>
                    <span class="text-nowrap">Mengelola prodi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sedang Membimbing</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total_membimbing']) ? $statistics['total_membimbing'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-user-friends"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-handshake"></i>
                    </span>
                    <span class="text-nowrap">Aktif membimbing</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section - TANPA FILTER LEVEL -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-search text-primary"></i>
                            Pencarian Dosen
                        </h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('staf/dosen') ?>" class="row" id="filterForm">
                    <div class="col-md-8 mb-3">
                        <div class="form-group">
                            <label for="search" class="form-control-label">Pencarian</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Nama/NIP/Email dosen..." 
                                       value="<?= htmlspecialchars($this->input->get('search') ?: '') ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <a href="<?= site_url('staf/dosen') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                            <a href="<?= site_url('staf/dosen/export') ?>" class="btn btn-outline-success" target="_blank">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table - TANPA AKSI DETAIL -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-table text-primary"></i>
                            Data Dosen Aktif
                        </h3>
                        <p class="text-sm mb-0 text-muted">
                            Total: <?= isset($dosen_list) ? count($dosen_list) : 0 ?> dosen aktif
                        </p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm no-loading" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NIP</th>
                                <th width="30%">Nama Dosen</th>
                                <th width="25%">Email</th>
                                <th width="15%">No. Telepon</th>
                                <th width="8%">Bimbingan</th>
                                <th width="5%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dosen_list) && count($dosen_list) > 0): ?>
                                <?php foreach ($dosen_list as $index => $dsn): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($dsn->nip) ?></span>
                                        </td>
                                        <td>
                                            <div class="media align-items-center">
                                                <span class="avatar avatar-sm rounded-circle mr-2">
                                                    <?php if (!empty($dsn->foto)): ?>
                                                        <img alt="Foto" src="<?= base_url('uploads/dosen/' . $dsn->foto) ?>">
                                                    <?php else: ?>
                                                        <img alt="Foto" src="<?= base_url('assets/img/theme/default-avatar.png') ?>">
                                                    <?php endif; ?>
                                                </span>
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold">
                                                        <?= htmlspecialchars($dsn->nama) ?>
                                                    </span>
                                                    <?php if ($dsn->is_kaprodi > 0): ?>
                                                        <br><small class="text-warning"><i class="fas fa-crown"></i> Kaprodi</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= htmlspecialchars($dsn->email) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= htmlspecialchars($dsn->nomor_telepon ?: '-') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dsn->total_bimbingan > 0): ?>
                                                <span class="badge badge-success"><?= $dsn->total_bimbingan ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success">Aktif</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                                            <h5 class="text-muted">Tidak ada data dosen</h5>
                                            <p class="text-muted mb-0">
                                                <?php if ($this->input->get('search')): ?>
                                                    Tidak ditemukan dosen dengan kata kunci: <strong><?= htmlspecialchars($this->input->get('search')) ?></strong>
                                                <?php else: ?>
                                                    Belum ada dosen aktif terdaftar di sistem.
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Sistem -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-info-circle fa-2x"></i>
                </div>
                <div class="col">
                    <h5 class="mb-1">Informasi Daftar Dosen</h5>
                    <p class="mb-0">
                        Halaman ini menampilkan <strong>dosen aktif</strong> dengan level 2 di database. 
                        Admin dan super admin tidak ditampilkan dalam daftar ini. 
                        Status Kaprodi ditunjukkan dengan ikon crown pada nama dosen.
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
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Search on enter
    $('#search').keypress(function(e) {
        if (e.which == 13) {
            $('#filterForm').submit();
        }
    });
    
    // Focus pada search input
    $('#search').focus();
});
</script>
<?php 
$script = ob_get_clean();

// Load template staf dengan data yang sudah disiapkan
$this->load->view('template/staf', [
    'title' => isset($title) ? $title : 'Daftar Dosen',
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>