<?php 
// ===========================================
// 3. PENELITIAN INDEX VIEW
// File: application/views/staf/penelitian/index.php
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Penelitian</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_penelitian'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-search"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Butuh Surat Izin</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['butuh_surat_izin'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-file-contract"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Ada Surat</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['sudah_ada_surat'] ?></span>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['penelitian_bulan_ini'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar"></i>
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
                <h3 class="mb-0">Daftar Penelitian Mahasiswa</h3>
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
                    <select name="status_izin" class="form-control form-control-sm mr-2">
                        <option value="">Semua Status</option>
                        <option value="0" <?= $filters['status_izin'] === '0' ? 'selected' : '' ?>>Butuh Surat Izin</option>
                        <option value="1" <?= $filters['status_izin'] === '1' ? 'selected' : '' ?>>Sudah Ada Surat</option>
                        <option value="2" <?= $filters['status_izin'] === '2' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari mahasiswa/lokasi..." value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-sm btn-secondary ml-2">Reset</a>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Prodi</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Lokasi Penelitian</th>
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Status Izin</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($penelitian)): ?>
                            <?php foreach($penelitian as $p): ?>
                                <tr>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="mb-0 text-sm font-weight-bold"><?= $p->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $p->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $p->nama_prodi ?></td>
                                    <td class="text-wrap" style="max-width: 200px;">
                                        <?= character_limiter($p->judul, 50) ?>
                                    </td>
                                    <td><?= $p->lokasi_penelitian ?: '-' ?></td>
                                    <td><?= $p->nama_pembimbing ?: '-' ?></td>
                                    <td>
                                        <?php
                                        switch($p->status_izin_penelitian) {
                                            case '0':
                                                echo '<span class="badge badge-warning">Butuh Surat Izin</span>';
                                                break;
                                            case '1':
                                                echo '<span class="badge badge-success">Sudah Ada Surat</span>';
                                                break;
                                            case '2':
                                                echo '<span class="badge badge-danger">Ditolak</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Unknown</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= $p->created_at ? date('d/m/Y', strtotime($p->created_at)) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                <a class="dropdown-item" href="<?= base_url('staf/penelitian/detail/' . $p->id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if($p->status_izin_penelitian == '0'): ?>
                                                    <a class="dropdown-item" href="<?= base_url('staf/penelitian/cetak_surat/' . $p->id) ?>" target="_blank">
                                                        <i class="fas fa-print"></i> Cetak Surat Izin
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($p->surat_izin_penelitian): ?>
                                                    <a class="dropdown-item" href="<?= base_url('staf/penelitian/download_surat/' . $p->id) ?>" target="_blank">
                                                        <i class="fas fa-download"></i> Download Surat
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data penelitian</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Manajemen Penelitian',
    'content' => $content,
    'css' => '',
    'script' => ''
]);
