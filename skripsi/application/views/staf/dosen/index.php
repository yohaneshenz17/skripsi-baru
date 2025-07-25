<?php
// File: application/views/staf/dosen/index.php
// PERBAIKAN: Menghilangkan foto dosen, hanya nama dosen saja
// Kolom lainnya tetap dipertahankan

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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
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
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
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
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
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
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tidak Membimbing</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total_tidak_membimbing']) ? $statistics['total_tidak_membimbing'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Section -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="h3 mb-0">
                    <i class="fas fa-search"></i> Pencarian Data Dosen
                </h6>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('staf/dosen') ?>" id="filterForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Pencarian</label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Cari nama dosen, NIP, atau email..." 
                                   value="<?= htmlspecialchars($this->input->get('search') ?: '') ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <a href="<?= base_url('staf/dosen') ?>" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportData()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Data Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="h3 mb-0">
                    <i class="fas fa-table"></i> Data Dosen Aktif
                </h6>
                <p class="text-sm mb-0 text-muted">
                    Total: <?= isset($dosen_list) ? count($dosen_list) : 0 ?> dosen aktif
                </p>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm" onclick="location.reload()">
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
                        <!-- PERBAIKAN: Kolom nama tanpa foto -->
                        <th width="25%">Nama Dosen</th>
                        <th width="25%">Email</th>
                        <th width="15%">No. Telepon</th>
                        <th width="10%">Status</th>
                        <th width="8%">Bimbingan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($dosen_list) && count($dosen_list) > 0): ?>
                        <?php foreach ($dosen_list as $index => $dsn): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= htmlspecialchars($dsn->nip) ?></span>
                                </td>
                                <td>
                                    <!-- PERBAIKAN: Hanya nama dosen, tanpa foto -->
                                    <div>
                                        <strong><?= htmlspecialchars($dsn->nama) ?></strong>
                                        <?php if ($dsn->is_kaprodi): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-crown text-warning"></i> 
                                                Kaprodi <?= htmlspecialchars($dsn->prodi_kelola) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm"><?= htmlspecialchars($dsn->email) ?></span>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= !empty($dsn->nomor_telepon) ? htmlspecialchars($dsn->nomor_telepon) : '-' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($dsn->is_kaprodi): ?>
                                        <span class="badge badge-warning">Kaprodi</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Dosen</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($dsn->total_bimbingan > 0): ?>
                                        <span class="badge badge-success"><?= $dsn->total_bimbingan ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Tidak ada data dosen yang ditemukan.</p>
                                    <?php if ($this->input->get('search')): ?>
                                        <small>Coba ubah kata kunci pencarian.</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (isset($dosen_list) && count($dosen_list) > 0): ?>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Daftar ini hanya menampilkan dosen aktif (bukan admin atau kaprodi yang tidak mengajar).
                        Status Kaprodi ditunjukkan dengan ikon crown pada nama dosen.
                    </small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript untuk functionality -->
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
    
    // Focus pada search input jika tidak ada parameter search
    <?php if (!$this->input->get('search')): ?>
        $('#search').focus();
    <?php endif; ?>
});

// Export function
function exportData() {
    // Get current search parameter
    const search = $('#search').val();
    let exportUrl = '<?= base_url("staf/dosen/export") ?>';
    
    // Add search to export URL
    if (search) {
        exportUrl += '?search=' + encodeURIComponent(search);
    }
    
    window.location.href = exportUrl;
}

// Refresh data function
function refreshData() {
    location.reload();
}
</script>

<?php 
$content = ob_get_clean();

// Load template staf dengan data yang sudah disiapkan
$this->load->view('template/staf', [
    'title' => isset($title) ? $title : 'Daftar Dosen',
    'content' => $content,
    'css' => '
        <style>
            .table th {
                border-top: none;
                padding: 1rem 0.75rem;
                font-weight: 600;
                color: #8898aa;
                background-color: #f6f9fc;
            }
            
            .table td {
                padding: 1rem 0.75rem;
                border-top: 1px solid #dee2e6;
            }
            
            .card-stats .icon {
                width: 48px;
                height: 48px;
            }
            
            .badge-warning {
                background-color: #ffc107;
                color: #212529;
            }
        </style>
    ',
    'script' => ''
]);
?>