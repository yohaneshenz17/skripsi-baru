<?php
// File: application/views/staf/mahasiswa/index.php
// Daftar Mahasiswa untuk Staf Akademik

// Capture content untuk template
ob_start();
?>

<!-- Header dengan informasi dan waktu -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow mr-3">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h2 class="mb-1 text-dark font-weight-bold"><?= isset($title) ? $title : 'Daftar Mahasiswa' ?></h2>
                <p class="text-secondary mb-0 font-weight-medium">Kelola dan pantau data mahasiswa semua program studi</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-right">
        <div class="card bg-gradient-success border-0">
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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total']) ? $statistics['total'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-chart-up"></i>
                    </span>
                    <span class="text-nowrap">Terdaftar</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Status Aktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['aktif']) ? $statistics['aktif'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="text-nowrap">Mahasiswa aktif</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Mengerjakan TA</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['mengerjakan_ta']) ? $statistics['mengerjakan_ta'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <span class="text-nowrap">Tugas akhir</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Status Nonaktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['nonaktif']) ? $statistics['nonaktif'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2">
                        <i class="fas fa-pause"></i>
                    </span>
                    <span class="text-nowrap">Tidak aktif</span>
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
                            Filter & Pencarian Data
                        </h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= site_url('staf/mahasiswa') ?>" class="row" id="filterForm">
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="prodi_id" class="form-control-label">Program Studi</label>
                            <select name="prodi_id" id="prodi_id" class="form-control">
                                <option value="">-- Semua Program Studi --</option>
                                <?php if (isset($prodi_list) && is_array($prodi_list)): ?>
                                    <?php foreach ($prodi_list as $prodi): ?>
                                        <option value="<?= $prodi->id ?>" <?= ($this->input->get('prodi_id') == $prodi->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($prodi->nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="status" class="form-control-label">Status Mahasiswa</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="1" <?= ($this->input->get('status') == '1') ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= ($this->input->get('status') == '0') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="search" class="form-control-label">Pencarian</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Nama/NIM/Email..." 
                                       value="<?= htmlspecialchars($this->input->get('search') ?: '') ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mt-2">
                    <a href="<?= site_url('staf/mahasiswa') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i> Reset Filter
                    </a>
                    <a href="<?= site_url('staf/mahasiswa/export') ?>" class="btn btn-outline-success" target="_blank">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-table text-primary"></i>
                            Data Mahasiswa
                        </h3>
                        <p class="text-sm mb-0 text-muted">
                            Total: <?= isset($mahasiswa_list) ? count($mahasiswa_list) : 0 ?> mahasiswa
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
                                <th width="10%">NIM</th>
                                <th width="20%">Nama Mahasiswa</th>
                                <th width="15%">Program Studi</th>
                                <th width="10%">Jenis Kelamin</th>
                                <th width="15%">Email</th>
                                <th width="10%">Status</th>
                                <th width="10%">Workflow</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($mahasiswa_list) && count($mahasiswa_list) > 0): ?>
                                <?php foreach ($mahasiswa_list as $index => $mhs): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?= htmlspecialchars($mhs->nim) ?></span>
                                        </td>
                                        <td>
                                            <div class="media align-items-center">
                                                <span class="avatar avatar-sm rounded-circle mr-2">
                                                    <?php if (!empty($mhs->foto)): ?>
                                                        <img alt="Foto" src="<?= base_url('uploads/mahasiswa/' . $mhs->foto) ?>">
                                                    <?php else: ?>
                                                        <img alt="Foto" src="<?= base_url('assets/img/theme/default-avatar.png') ?>">
                                                    <?php endif; ?>
                                                </span>
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold">
                                                        <?= htmlspecialchars($mhs->nama) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= htmlspecialchars($mhs->nama_prodi ?: 'Tidak Ada') ?></span>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= ucfirst($mhs->jenis_kelamin) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= htmlspecialchars($mhs->email) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($mhs->status == '1'): ?>
                                                <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($mhs->current_workflow): ?>
                                                <?php
                                                $workflow_badges = [
                                                    'bimbingan' => 'warning',
                                                    'seminar_proposal' => 'info',
                                                    'penelitian' => 'primary',
                                                    'seminar_skripsi' => 'success',
                                                    'publikasi' => 'dark'
                                                ];
                                                $workflow_labels = [
                                                    'bimbingan' => 'Bimbingan',
                                                    'seminar_proposal' => 'Seminar Proposal',
                                                    'penelitian' => 'Penelitian',
                                                    'seminar_skripsi' => 'Seminar Skripsi',
                                                    'publikasi' => 'Publikasi'
                                                ];
                                                $badge_class = isset($workflow_badges[$mhs->current_workflow]) ? $workflow_badges[$mhs->current_workflow] : 'secondary';
                                                $badge_label = isset($workflow_labels[$mhs->current_workflow]) ? $workflow_labels[$mhs->current_workflow] : 'Unknown';
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>"><?= $badge_label ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-light">Belum TA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light no-loading" href="#" role="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <a class="dropdown-item" href="<?= site_url('staf/mahasiswa/detail/' . $mhs->id) ?>">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    <?php if ($mhs->total_proposal > 0): ?>
                                                        <a class="dropdown-item" href="<?= site_url('staf/mahasiswa/progress/' . $mhs->id) ?>">
                                                            <i class="fas fa-chart-line"></i> Progress TA
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                                            <h5 class="text-muted">Tidak ada data mahasiswa</h5>
                                            <p class="text-muted mb-0">
                                                Data mahasiswa akan muncul di sini setelah terdaftar di sistem.
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
    
    // Auto-submit form on filter change
    $('#prodi_id, #status').change(function() {
        $('#filterForm').submit();
    });
    
    // Search on enter
    $('#search').keypress(function(e) {
        if (e.which == 13) {
            $('#filterForm').submit();
        }
    });
});
</script>
<?php 
$script = ob_get_clean();

// Load template staf dengan data yang sudah disiapkan
$this->load->view('template/staf', [
    'title' => isset($title) ? $title : 'Daftar Mahasiswa',
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>