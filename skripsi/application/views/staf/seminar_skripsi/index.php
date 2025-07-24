// ===========================================
// 4. SEMINAR SKRIPSI INDEX VIEW
// File: application/views/staf/seminar_skripsi/index.php
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
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_seminar_skripsi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-graduation-cap"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Lulus</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['sudah_lulus'] ?></span>
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
                <h3 class="mb-0">Daftar Seminar Skripsi</h3>
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
                        <option value="lulus" <?= $filters['status'] == 'lulus' ? 'selected' : '' ?>>Lulus</option>
                    </select>
                    <input type="month" name="periode" class="form-control form-control-sm mr-2" value="<?= $filters['periode'] ?>">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" 
                           placeholder="Cari mahasiswa..." value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="<?= base_url('staf/seminar_skripsi') ?>" class="btn btn-sm btn-secondary ml-2">Reset</a>
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
                        <?php if(!empty($seminar_skripsi)): ?>
                            <?php foreach($seminar_skripsi as $ss): ?>
                                <tr>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="mb-0 text-sm font-weight-bold"><?= $ss->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $ss->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $ss->nama_prodi ?></td>
                                    <td class="text-wrap" style="max-width: 200px;">
                                        <?= character_limiter($ss->judul, 50) ?>
                                    </td>
                                    <td><?= $ss->nama_pembimbing ?: '-' ?></td>
                                    <td><?= $ss->nama_penguji ?: '-' ?></td>
                                    <td>
                                        <?php if($ss->tanggal_seminar_skripsi): ?>
                                            <?= date('d/m/Y H:i', strtotime($ss->tanggal_seminar_skripsi)) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum dijadwalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $ss->nama_ruangan ?: '-' ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        $status_text = 'Unknown';
                                        
                                        if(in_array($ss->workflow_status, ['publikasi', 'selesai'])) {
                                            $status_class = 'success';
                                            $status_text = 'Lulus';
                                        } elseif($ss->status_seminar_skripsi == '1' && $ss->tanggal_seminar_skripsi) {
                                            $status_class = 'info';
                                            $status_text = 'Dijadwalkan';
                                        } elseif($ss->status_seminar_skripsi == '1' && !$ss->tanggal_seminar_skripsi) {
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Jadwal';
                                        } elseif($ss->status_seminar_skripsi == '2') {
                                            $status_class = 'primary';
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
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_skripsi/detail/' . $ss->id) ?>">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_skripsi/export_undangan/' . $ss->id) ?>" target="_blank">
                                                    <i class="fas fa-envelope"></i> Export Undangan
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_skripsi/export_berita_acara/' . $ss->id) ?>" target="_blank">
                                                    <i class="fas fa-file-alt"></i> Export Berita Acara
                                                </a>
                                                <a class="dropdown-item" href="<?= base_url('staf/seminar_skripsi/export_form_penilaian/' . $ss->id) ?>" target="_blank">
                                                    <i class="fas fa-clipboard-list"></i> Export Form Penilaian
                                                </a>
                                                <?php if(in_array($ss->workflow_status, ['publikasi', 'selesai'])): ?>
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_skripsi/export_sertifikat/' . $ss->id) ?>" target="_blank">
                                                        <i class="fas fa-certificate"></i> Export Sertifikat
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data seminar skripsi</td>
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
    'title' => 'Manajemen Seminar Skripsi',
    'content' => $content,
    'css' => '',
    'script' => ''
]);