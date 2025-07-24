// ===========================================
// 5. PUBLIKASI INDEX VIEW
// File: application/views/staf/publikasi/index.php
// ===========================================

ob_start();
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_publikasi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Pending Staf</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['pending_staf'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Validated Staf</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['validated_staf'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Selesai</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['selesai'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Daftar Publikasi Tugas Akhir</h3>
                    </div>
                    <div class="col text-right">
                        <button class="btn btn-sm btn-success" onclick="bulkValidasi('1')">
                            <i class="fas fa-check"></i> Bulk Setuju
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="bulkValidasi('2')">
                            <i class="fas fa-times"></i> Bulk Tolak
                        </button>
                        <a href="<?= base_url('staf/publikasi/export_laporan') ?>" class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-download"></i> Export Laporan
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="card-header border-0 pb-0">
                <form method="GET" class="form-inline">
                    <select name="prodi_id" class="form-control form-control-sm mr-2">
                        <option value="">Semua Prodi</option>
                        <?php foreach($prodi_list as $prodi): ?>
                            <option value="<?= $prodi->id ?>" <?= $filters['prodi_id'] == $prodi->id ? 'selected' : '' ?>>
                                <?= $prodi->nama ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="diajukan" <?= $filters['status'] == 'diajukan' ? 'selected' : '' ?>>Diajukan</option>
                        <option value="validated" <?= $filters['status'] == 'validated' ? 'selected' : '' ?>>Validated Staf</option>
                        <option value="approved" <?= $filters['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                    </select>
                    <input type="month" name="periode" class="form-control form-control-sm mr-2" value="<?= $filters['periode'] ?>">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari mahasiswa/repository..." value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-sm btn-secondary ml-2">Reset</a>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Prodi</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Link Repository</th>
                            <th scope="col">Tanggal Publikasi</th>
                            <th scope="col">Status Staf</th>
                            <th scope="col">Status Kaprodi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($publikasi)): ?>
                            <?php foreach($publikasi as $pub): ?>
                                <tr>
                                    <td>
                                        <?php if($pub->status_publikasi == '1' && (!$pub->validasi_staf_publikasi || $pub->validasi_staf_publikasi == '0')): ?>
                                            <input type="checkbox" class="select-item" value="<?= $pub->id ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="mb-0 text-sm font-weight-bold"><?= $pub->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $pub->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $pub->nama_prodi ?></td>
                                    <td class="text-wrap" style="max-width: 200px;">
                                        <?= character_limiter($pub->judul, 50) ?>
                                    </td>
                                    <td>
                                        <?php if($pub->link_repository): ?>
                                            <a href="<?= $pub->link_repository ?>" target="_blank" class="text-primary">
                                                <i class="fas fa-external-link-alt"></i> Lihat Repository
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada link</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $pub->tanggal_publikasi ? date('d/m/Y', strtotime($pub->tanggal_publikasi)) : '-' ?>
                                    </td>
                                    <td>
                                        <?php
                                        switch($pub->validasi_staf_publikasi) {
                                            case '1':
                                                echo '<span class="badge badge-success">Disetujui</span>';
                                                break;
                                            case '2':
                                                echo '<span class="badge badge-danger">Ditolak</span>';
                                                break;
                                            default:
                                                if($pub->status_publikasi == '1') {
                                                    echo '<span class="badge badge-warning">Pending</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">Belum Diajukan</span>';
                                                }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        switch($pub->validasi_kaprodi_publikasi) {
                                            case '1':
                                                echo '<span class="badge badge-success">Approved</span>';
                                                break;
                                            case '2':
                                                echo '<span class="badge badge-danger">Rejected</span>';
                                                break;
                                            default:
                                                if($pub->validasi_staf_publikasi == '1') {
                                                    echo '<span class="badge badge-warning">Pending</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">-</span>';
                                                }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                <a class="dropdown-item" href="<?= base_url('staf/publikasi/detail/' . $pub->id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if(!$pub->link_repository): ?>
                                                    <a class="dropdown-item" href="<?= base_url('staf/publikasi/input_repository?proposal_id=' . $pub->id) ?>">
                                                        <i class="fas fa-plus"></i> Input Repository
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($pub->status_publikasi == '1' && (!$pub->validasi_staf_publikasi || $pub->validasi_staf_publikasi == '0')): ?>
                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="validasiSingle(<?= $pub->id ?>, '1')">
                                                        <i class="fas fa-check text-success"></i> Setujui
                                                    </a>
                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="validasiSingle(<?= $pub->id ?>, '2')">
                                                        <i class="fas fa-times text-danger"></i> Tolak
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data publikasi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Select all checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Bulk validasi function
function bulkValidasi(status) {
    const selected = [];
    document.querySelectorAll('.select-item:checked').forEach(checkbox => {
        selected.push(checkbox.value);
    });
    
    if (selected.length === 0) {
        alert('Pilih minimal satu publikasi untuk divalidasi');
        return;
    }
    
    const statusText = status === '1' ? 'menyetujui' : 'menolak';
    const keterangan = prompt(`Masukkan keterangan untuk ${statusText} publikasi:`);
    
    if (keterangan === null) return; // User cancelled
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("staf/publikasi/bulk_validasi") ?>';
    
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'proposal_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status_validasi';
    statusInput.value = status;
    form.appendChild(statusInput);
    
    const keteranganInput = document.createElement('input');
    keteranganInput.type = 'hidden';
    keteranganInput.name = 'keterangan';
    keteranganInput.value = keterangan;
    form.appendChild(keteranganInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Single validasi function
function validasiSingle(proposalId, status) {
    const statusText = status === '1' ? 'menyetujui' : 'menolak';
    const keterangan = prompt(`Masukkan keterangan untuk ${statusText} publikasi:`);
    
    if (keterangan === null) return; // User cancelled
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("staf/publikasi/validasi") ?>';
    
    const proposalInput = document.createElement('input');
    proposalInput.type = 'hidden';
    proposalInput.name = 'proposal_id';
    proposalInput.value = proposalId;
    form.appendChild(proposalInput);
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status_validasi';
    statusInput.value = status;
    form.appendChild(statusInput);
    
    const keteranganInput = document.createElement('input');
    keteranganInput.type = 'hidden';
    keteranganInput.name = 'keterangan';
    keteranganInput.value = keterangan;
    form.appendChild(keteranganInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Manajemen Publikasi',
    'content' => $content,
    'css' => '',
    'script' => ''
]);
?>