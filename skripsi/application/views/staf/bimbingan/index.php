<?php
// File: application/views/staf/bimbingan/index.php
// FIXED - Menggunakan template staf untuk konsistensi sidebar

// Capture content untuk template
ob_start();
?>

<!-- Header dengan informasi dan waktu -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow mr-3">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <h2 class="mb-1"><?= isset($title) ? $title : 'Monitoring Bimbingan' ?></h2>
                <p class="text-muted mb-0">Kelola dan pantau jurnal bimbingan mahasiswa tugas akhir</p>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['total_mahasiswa']) ? $statistics['total_mahasiswa'] : 0 ?>
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
                        <i class="fas fa-arrow-up"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Tahap Bimbingan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['bimbingan']) ? $statistics['bimbingan'] : 0 ?>
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
                        <i class="fas fa-clock"></i>
                    </span>
                    <span class="text-nowrap">Dalam proses</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Seminar Proposal</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['seminar_proposal']) ? $statistics['seminar_proposal'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-presentation"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2">
                        <i class="fas fa-chart-up"></i>
                    </span>
                    <span class="text-nowrap">Siap seminar</span>
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
                            <?= isset($statistics['penelitian']) ? $statistics['penelitian'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="text-nowrap">Meneliti</span>
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
                <form method="GET" action="<?= site_url('staf/bimbingan') ?>" class="row" id="filterForm">
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="prodi_id" class="form-control-label">Program Studi</label>
                            <select name="prodi_id" id="prodi_id" class="form-control">
                                <option value="">-- Semua Prodi --</option>
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
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="dosen_id" class="form-control-label">Dosen Pembimbing</label>
                            <select name="dosen_id" id="dosen_id" class="form-control">
                                <option value="">-- Semua Dosen --</option>
                                <?php if (isset($dosen_list) && is_array($dosen_list)): ?>
                                    <?php foreach ($dosen_list as $dosen): ?>
                                        <option value="<?= $dosen->id ?>" <?= ($this->input->get('dosen_id') == $dosen->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dosen->nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="status" class="form-control-label">Status Workflow</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <?php if (isset($status_list) && is_array($status_list)): ?>
                                    <?php foreach ($status_list as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= ($this->input->get('status') == $key) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($value) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="search" class="form-control-label">Pencarian</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="Nama/NIM/Judul..." 
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
                    <a href="<?= site_url('staf/bimbingan') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i> Reset Filter
                    </a>
                    <button type="button" class="btn btn-outline-success" onclick="exportAllData()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
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
                            Daftar Mahasiswa Bimbingan
                        </h3>
                        <p class="text-sm mb-0 text-muted">
                            Total: <?= isset($mahasiswa_bimbingan) ? count($mahasiswa_bimbingan) : 0 ?> mahasiswa
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
                                <th width="10%">NIM</th>
                                <th width="15%">Mahasiswa</th>
                                <th width="12%">Prodi</th>
                                <th width="25%">Judul Proposal</th>
                                <th width="15%">Pembimbing</th>
                                <th width="8%">Status</th>
                                <th width="10%">Progress</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($mahasiswa_bimbingan) && count($mahasiswa_bimbingan) > 0): ?>
                                <?php foreach ($mahasiswa_bimbingan as $index => $mhs): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?= htmlspecialchars($mhs->nim) ?></span>
                                        </td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold">
                                                        <?= htmlspecialchars($mhs->nama_mahasiswa) ?>
                                                    </span><br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-envelope"></i> 
                                                        <?= htmlspecialchars($mhs->email_mahasiswa) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm"><?= htmlspecialchars($mhs->nama_prodi) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-sm" title="<?= htmlspecialchars($mhs->judul) ?>" data-toggle="tooltip">
                                                <?= strlen($mhs->judul) > 50 ? substr(htmlspecialchars($mhs->judul), 0, 50) . '...' : htmlspecialchars($mhs->judul) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($mhs->nama_pembimbing)): ?>
                                                <span class="text-sm font-weight-bold text-success">
                                                    <?= htmlspecialchars($mhs->nama_pembimbing) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-sm text-muted">Belum ditetapkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'bimbingan' => 'warning',
                                                'seminar_proposal' => 'info',
                                                'penelitian' => 'primary',
                                                'seminar_skripsi' => 'success',
                                                'publikasi' => 'dark'
                                            ];
                                            $status_labels = [
                                                'bimbingan' => 'Bimbingan',
                                                'seminar_proposal' => 'Seminar Proposal',
                                                'penelitian' => 'Penelitian',
                                                'seminar_skripsi' => 'Seminar Skripsi',
                                                'publikasi' => 'Publikasi'
                                            ];
                                            $status_class = isset($status_classes[$mhs->workflow_status]) ? $status_classes[$mhs->workflow_status] : 'secondary';
                                            $status_label = isset($status_labels[$mhs->workflow_status]) ? $status_labels[$mhs->workflow_status] : 'Unknown';
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $status_label ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <small class="text-success">
                                                        <i class="fas fa-check"></i> <?= $mhs->jurnal_tervalidasi ?>
                                                    </small>
                                                    <small class="text-warning ml-1">
                                                        <i class="fas fa-clock"></i> <?= $mhs->jurnal_pending ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="progress progress-xs mt-1">
                                                <?php 
                                                $total = max($mhs->total_jurnal, 1);
                                                $pct_valid = ($mhs->jurnal_tervalidasi / $total) * 100;
                                                $pct_pending = ($mhs->jurnal_pending / $total) * 100;
                                                ?>
                                                <div class="progress-bar bg-success" style="width: <?= $pct_valid ?>%"></div>
                                                <div class="progress-bar bg-warning" style="width: <?= $pct_pending ?>%"></div>
                                            </div>
                                            <small class="text-muted">Total: <?= $mhs->total_jurnal ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= site_url('staf/bimbingan/detail_mahasiswa/' . $mhs->proposal_id) ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Detail Progress" 
                                                   data-toggle="tooltip">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= site_url('staf/bimbingan/export_jurnal/' . $mhs->proposal_id) ?>" 
                                                   class="btn btn-outline-success" 
                                                   title="Export Jurnal PDF" 
                                                   data-toggle="tooltip"
                                                   target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                                            <h5 class="text-muted">Belum ada mahasiswa dalam tahap bimbingan</h5>
                                            <p class="text-muted mb-0">
                                                Mahasiswa akan muncul di sini setelah pembimbing menyetujui penunjukan dan masuk tahap bimbingan.
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
    $('#prodi_id, #dosen_id, #status').change(function() {
        $('#filterForm').submit();
    });
    
    // Loading state untuk tombol
    $('.btn').click(function() {
        var btn = $(this);
        if (!btn.hasClass('no-loading')) {
            btn.prop('disabled', true);
            setTimeout(function() {
                btn.prop('disabled', false);
            }, 2000);
        }
    });
    
    // Search on enter
    $('#search').keypress(function(e) {
        if (e.which == 13) {
            $('#filterForm').submit();
        }
    });
});

function exportAllData() {
    if (confirm('Apakah Anda yakin ingin mengexport semua data bimbingan?')) {
        window.open('<?= site_url("staf/bimbingan/export_all") ?>', '_blank');
    }
}

function refreshData() {
    location.reload();
}

// Show loading when navigating
$(document).on('click', 'a[href]:not([href="#"]):not([target="_blank"])', function() {
    $('body').append('<div id="loading" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:9999;display:flex;align-items:center;justify-content:center;"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
});
</script>
<?php 
$script = ob_get_clean();

// Load template staf dengan data yang sudah disiapkan
$this->load->view('template/staf', [
    'title' => isset($title) ? $title : 'Monitoring Bimbingan',
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>