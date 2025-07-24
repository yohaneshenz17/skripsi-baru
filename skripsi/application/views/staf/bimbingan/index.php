<?php 
// ===========================================
// 1. BIMBINGAN INDEX VIEW
// File: application/views/staf/bimbingan/index.php
// ===========================================
?>

<!-- BIMBINGAN INDEX -->
<?php 
ob_start();
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Bimbingan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_bimbingan'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">mahasiswa aktif</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Jurnal Valid</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['jurnal_valid'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">sudah divalidasi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Jurnal Pending</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['jurnal_pending'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
                    <span class="text-nowrap">menunggu validasi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['bimbingan_bulan_ini'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-calendar"></i></span>
                    <span class="text-nowrap">sesi bimbingan</span>
                </p>
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
                        <h3 class="mb-0">Daftar Mahasiswa Bimbingan</h3>
                    </div>
                    <div class="col text-right">
                        <button class="btn btn-sm btn-primary" onclick="exportSelected()">
                            <i class="fas fa-download"></i> Export Selected
                        </button>
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
                    <select name="dosen_id" class="form-control form-control-sm mr-2">
                        <option value="">Semua Dosen</option>
                        <?php foreach($dosen_list as $dosen): ?>
                            <option value="<?= $dosen->id ?>" <?= $filters['dosen_id'] == $dosen->id ? 'selected' : '' ?>>
                                <?= $dosen->nama ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?= $filters['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="selesai" <?= $filters['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari mahasiswa..." value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= base_url('staf/bimbingan') ?>" class="btn btn-sm btn-secondary ml-2">Reset</a>
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
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Total Bimbingan</th>
                            <th scope="col">Last Bimbingan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($bimbingan)): ?>
                            <?php foreach($bimbingan as $b): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" value="<?= $b->id ?>">
                                    </td>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="mb-0 text-sm font-weight-bold"><?= $b->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $b->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $b->nama_prodi ?></td>
                                    <td><?= $b->nama_pembimbing ?: '-' ?></td>
                                    <td class="text-wrap" style="max-width: 200px;">
                                        <?= character_limiter($b->judul, 50) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= $b->total_bimbingan ?></span>
                                    </td>
                                    <td>
                                        <?= $b->last_bimbingan ? date('d/m/Y', strtotime($b->last_bimbingan)) : '-' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        $status_text = 'Unknown';
                                        
                                        switch($b->workflow_status) {
                                            case 'bimbingan':
                                                $status_class = 'warning';
                                                $status_text = 'Bimbingan';
                                                break;
                                            case 'seminar_proposal':
                                                $status_class = 'info';
                                                $status_text = 'Seminar Proposal';
                                                break;
                                            case 'penelitian':
                                                $status_class = 'primary';
                                                $status_text = 'Penelitian';
                                                break;
                                            case 'seminar_skripsi':
                                                $status_class = 'success';
                                                $status_text = 'Seminar Skripsi';
                                                break;
                                            case 'publikasi':
                                            case 'selesai':
                                                $status_class = 'success';
                                                $status_text = 'Selesai';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                <a class="dropdown-item" href="<?= base_url('staf/bimbingan/detail_mahasiswa/' . $b->id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/bimbingan/export_jurnal/' . $b->id) ?>" target="_blank">
                                                    <i class="fas fa-download"></i> Export Jurnal
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data bimbingan</td>
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

// Export selected function
function exportSelected() {
    const selected = [];
    document.querySelectorAll('.select-item:checked').forEach(checkbox => {
        selected.push(checkbox.value);
    });
    
    if (selected.length === 0) {
        alert('Pilih minimal satu mahasiswa untuk di-export');
        return;
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("staf/bimbingan/export_all") ?>';
    
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'proposal_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Manajemen Bimbingan',
    'content' => $content,
    'css' => '',
    'script' => ''
]);