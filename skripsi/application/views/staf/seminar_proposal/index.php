// ===========================================
// 2. SEMINAR PROPOSAL INDEX VIEW
// File: application/views/staf/seminar_proposal/index.php
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Seminar</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_seminar_proposal'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-presentation"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Dijadwalkan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['sudah_dijadwalkan'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-calendar-check"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Jadwal</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['menunggu_jadwal'] ?></span>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['seminar_bulan_ini'] ?></span>
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
                <h3 class="mb-0">Daftar Seminar Proposal</h3>
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
                        <option value="dijadwalkan" <?= $filters['status'] == 'dijadwalkan' ? 'selected' : '' ?>>Dijadwalkan</option>
                        <option value="menunggu_jadwal" <?= $filters['status'] == 'menunggu_jadwal' ? 'selected' : '' ?>>Menunggu Jadwal</option>
                        <option value="selesai" <?= $filters['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <input type="month" name="periode" class="form-control form-control-sm mr-2" value="<?= $filters['periode'] ?>">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari mahasiswa..." value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= base_url('staf/seminar_proposal') ?>" class="btn btn-sm btn-secondary ml-2">Reset</a>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Prodi</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Penguji</th>
                            <th scope="col">Jadwal</th>
                            <th scope="col">Ruangan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($seminar_proposal)): ?>
                            <?php foreach($seminar_proposal as $sp): ?>
                                <tr>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="mb-0 text-sm font-weight-bold"><?= $sp->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $sp->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $sp->nama_prodi ?></td>
                                    <td class="text-wrap" style="max-width: 200px;">
                                        <?= character_limiter($sp->judul, 50) ?>
                                    </td>
                                    <td><?= $sp->nama_pembimbing ?: '-' ?></td>
                                    <td><?= $sp->nama_penguji ?: '-' ?></td>
                                    <td>
                                        <?php if($sp->tanggal_seminar_proposal): ?>
                                            <?= date('d/m/Y H:i', strtotime($sp->tanggal_seminar_proposal)) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum dijadwalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $sp->nama_ruangan ?: '-' ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        $status_text = 'Unknown';
                                        
                                        if($sp->status_seminar_proposal == '1' && $sp->tanggal_seminar_proposal) {
                                            $status_class = 'success';
                                            $status_text = 'Dijadwalkan';
                                        } elseif($sp->status_seminar_proposal == '1' && !$sp->tanggal_seminar_proposal) {
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Jadwal';
                                        } elseif($sp->status_seminar_proposal == '2') {
                                            $status_class = 'success';
                                            $status_text = 'Selesai';
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
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/detail/' . $sp->id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/export_undangan/' . $sp->id) ?>" target="_blank">
                                                    <i class="fas fa-envelope"></i> Export Undangan
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/export_berita_acara/' . $sp->id) ?>" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Export Berita Acara
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/export_form_penilaian/' . $sp->id) ?>" target="_blank">
                                                    <i class="fas fa-clipboard-list"></i> Export Form Penilaian
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data seminar proposal</td>
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
    'title' => 'Manajemen Seminar Proposal',
    'content' => $content,
    'css' => '',
    'script' => ''
]);
// Continuation will be in next artifact...
?>
