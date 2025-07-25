<?php
// File: application/views/staf/mahasiswa/index.php
// PERBAIKAN: Simplified table - No photo, No action column
// Kolom: NIM + Nama Mahasiswa + Program Studi + Jenis Kelamin + Email + Status Workflow

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
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Mahasiswa Aktif</h5>
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
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tidak Aktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($statistics['tidak_aktif']) ? $statistics['tidak_aktif'] : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-user-times"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Program Studi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($prodi_list) ? count($prodi_list) : 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-graduation-cap"></i>
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
                    <i class="fas fa-filter"></i> Filter & Pencarian Data
                </h6>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('staf/mahasiswa') ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="prodi_id" class="form-control">
                            <option value="">-- Semua Program Studi --</option>
                            <?php if (isset($prodi_list)): ?>
                                <?php foreach ($prodi_list as $prodi): ?>
                                    <option value="<?= $prodi->id ?>" <?= $this->input->get('prodi_id') == $prodi->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prodi->nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status Mahasiswa</label>
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" <?= $this->input->get('status') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="tidak_aktif" <?= $this->input->get('status') == 'tidak_aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                            <option value="lulus" <?= $this->input->get('status') == 'lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="drop_out" <?= $this->input->get('status') == 'drop_out' ? 'selected' : '' ?>>Drop Out</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Pencarian</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, NIM, atau email..." value="<?= htmlspecialchars($this->input->get('search') ?: '') ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="<?= base_url('staf/mahasiswa') ?>" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset Filter
                    </a>
                    <button type="button" class="btn btn-success" onclick="exportData()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="h3 mb-0">
                    <i class="fas fa-table"></i> Data Mahasiswa
                </h6>
                <p class="text-sm mb-0">
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
            <!-- PERBAIKAN: Simplified table dengan kolom yang diminta user -->
            <table class="table align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th width="8%">No</th>
                        <th width="15%">NIM</th>
                        <th width="25%">Nama Mahasiswa</th>
                        <th width="20%">Program Studi</th>
                        <th width="12%">Jenis Kelamin</th>
                        <th width="20%">Email</th>
                        <th width="15%">Status Workflow</th>
                        <!-- REMOVED: Kolom Aksi dihapus sesuai permintaan -->
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
                                    <!-- PERBAIKAN: Hanya nama, tanpa foto -->
                                    <div>
                                        <strong><?= htmlspecialchars($mhs->nama) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= htmlspecialchars($mhs->nama_prodi ?: 'Belum ditentukan') ?>
                                    </span>
                                    <?php if (!empty($mhs->kode_prodi)): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($mhs->kode_prodi) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mhs->jenis_kelamin == 'L'): ?>
                                        <span class="badge badge-info">Laki-laki</span>
                                    <?php elseif ($mhs->jenis_kelamin == 'P'): ?>
                                        <span class="badge badge-pink">Perempuan</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-sm"><?= htmlspecialchars($mhs->email) ?></span>
                                </td>
                                <td>
                                    <!-- PERBAIKAN: Status workflow yang lebih informatif -->
                                    <?php if (!empty($mhs->current_workflow)): ?>
                                        <?php
                                        $workflow_class = '';
                                        $workflow_text = '';
                                        switch ($mhs->current_workflow) {
                                            case 'proposal_submitted':
                                                $workflow_class = 'badge-warning';
                                                $workflow_text = 'Proposal Diajukan';
                                                break;
                                            case 'bimbingan':
                                                $workflow_class = 'badge-info';
                                                $workflow_text = 'Bimbingan';
                                                break;
                                            case 'seminar_proposal':
                                                $workflow_class = 'badge-primary';
                                                $workflow_text = 'Seminar Proposal';
                                                break;
                                            case 'penelitian':
                                                $workflow_class = 'badge-secondary';
                                                $workflow_text = 'Penelitian';
                                                break;
                                            case 'seminar_skripsi':
                                                $workflow_class = 'badge-success';
                                                $workflow_text = 'Seminar Skripsi';
                                                break;
                                            case 'publikasi':
                                                $workflow_class = 'badge-dark';
                                                $workflow_text = 'Publikasi';
                                                break;
                                            default:
                                                $workflow_class = 'badge-light';
                                                $workflow_text = ucfirst(str_replace('_', ' ', $mhs->current_workflow));
                                        }
                                        ?>
                                        <span class="badge <?= $workflow_class ?>"><?= $workflow_text ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-light">Belum Ada Proposal</span>
                                    <?php endif; ?>
                                </td>
                                <!-- REMOVED: Kolom Aksi tidak ditampilkan -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>Tidak ada data mahasiswa yang ditemukan.</p>
                                    <?php if ($this->input->get('search') || $this->input->get('prodi_id') || $this->input->get('status')): ?>
                                        <small>Coba ubah kriteria pencarian atau filter.</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JavaScript untuk additional functionality -->
<script>
$(document).ready(function() {
    // Initialize DataTable jika diperlukan
    // $('.table').DataTable({
    //     "pageLength": 25,
    //     "responsive": true,
    //     "language": {
    //         "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
    //     }
    // });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Export function
function exportData() {
    // Get current filter parameters
    const urlParams = new URLSearchParams(window.location.search);
    let exportUrl = '<?= base_url("staf/mahasiswa/export") ?>';
    
    // Add filters to export URL
    if (urlParams.toString()) {
        exportUrl += '?' + urlParams.toString();
    }
    
    window.location.href = exportUrl;
}

// Refresh dengan maintain filter
function refreshData() {
    location.reload();
}
</script>

<?php 
$content = ob_get_clean();

// Load template staf dengan content yang sudah di-capture
$this->load->view('template/staf', [
    'title' => 'Daftar Mahasiswa',
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
            
            .badge-pink {
                color: #fff;
                background-color: #f093fb;
            }
            
            .card-stats .icon {
                width: 48px;
                height: 48px;
            }
        </style>
    ',
    'script' => ''
]);
?>