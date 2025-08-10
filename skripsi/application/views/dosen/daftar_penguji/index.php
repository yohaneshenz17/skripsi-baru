<?php
/**
 * View: Daftar Penguji - Index
 * 
 * Halaman utama untuk menampilkan daftar tugas penguji dosen
 * Menampilkan Seminar Proposal dan Seminar Skripsi
 * 
 * File: application/views/dosen/daftar_penguji/index.php
 */
?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-support-16"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Seminar Proposal</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_proposal'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                            <i class="ni ni-books"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-gavel"></i></span>
                    <span class="text-nowrap">Tugas Penguji</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Seminar Skripsi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_skripsi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-primary mr-2"><i class="fa fa-gavel"></i></span>
                    <span class="text-nowrap">Tugas Penguji</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Penilaian</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['menunggu_penilaian'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="ni ni-time-alarm"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
                    <span class="text-nowrap">Perlu Dinilai</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Selesai Dinilai</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['selesai_dinilai'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="ni ni-check-bold"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Sudah Selesai</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Daftar Tugas Penguji</h3>
                        <p class="text-sm mb-0">Seminar Proposal dan Seminar Skripsi yang ditugaskan kepada Anda</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <a href="<?= base_url('dosen/daftar_penguji/export_pdf?type=all') ?>" class="btn btn-sm btn-success" target="_blank">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-fill" id="pengujTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="proposal-tab" data-toggle="tab" href="#proposal-content" role="tab" aria-controls="proposal-content" aria-selected="true">
                            <i class="ni ni-books text-danger"></i>
                            Seminar Proposal (<?= count($seminar_proposal) ?>)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="skripsi-tab" data-toggle="tab" href="#skripsi-content" role="tab" aria-controls="skripsi-content" aria-selected="false">
                            <i class="fa fa-graduation-cap text-primary"></i>
                            Seminar Skripsi (<?= count($seminar_skripsi) ?>)
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="pengujTabsContent">
                    <!-- Seminar Proposal Tab -->
                    <div class="tab-pane fade show active" id="proposal-content" role="tabpanel" aria-labelledby="proposal-tab">
                        <div class="table-responsive mt-4">
                            <?php if (empty($seminar_proposal)): ?>
                            <div class="text-center py-4">
                                <div class="icon icon-shape icon-lg bg-gradient-danger text-white rounded-circle mx-auto mb-3">
                                    <i class="ni ni-books"></i>
                                </div>
                                <h5 class="text-muted">Tidak Ada Tugas Penguji</h5>
                                <p class="text-sm text-muted">Anda belum ditugaskan sebagai penguji seminar proposal</p>
                            </div>
                            <?php else: ?>
                            <table class="table table-flush table-hover" id="table-proposal">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Mahasiswa</th>
                                        <th>Judul Proposal</th>
                                        <th>Jadwal Seminar</th>
                                        <th>Posisi Penguji</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($seminar_proposal as $proposal): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold"><?= $proposal->nama_mahasiswa ?></span>
                                                    <br>
                                                    <small class="text-muted"><?= $proposal->nim ?></small>
                                                    <br>
                                                    <small class="text-muted"><?= $proposal->nama_prodi ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm font-weight-bold" title="<?= $proposal->judul ?>">
                                                <?= strlen($proposal->judul) > 50 ? substr($proposal->judul, 0, 50) . '...' : $proposal->judul ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">Pembimbing: <?= $proposal->nama_pembimbing ?></small>
                                        </td>
                                        <td>
                                            <?php if ($proposal->tanggal_seminar): ?>
                                                <span class="text-sm">
                                                    <i class="fa fa-calendar text-primary"></i>
                                                    <?= date('d M Y', strtotime($proposal->tanggal_seminar)) ?>
                                                </span>
                                                <br>
                                                <span class="text-sm">
                                                    <i class="fa fa-clock text-info"></i>
                                                    <?= date('H:i', strtotime($proposal->jam_seminar)) ?> WIT
                                                </span>
                                                <br>
                                                <span class="text-sm">
                                                    <i class="fa fa-map-marker text-warning"></i>
                                                    <?= $proposal->tempat_seminar ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum Dijadwalkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $proposal->posisi_penguji == 'Penguji 1' ? 'primary' : 'info' ?>">
                                                <?= $proposal->posisi_penguji ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch($proposal->status) {
                                                case 'scheduled':
                                                    $status_class = 'success';
                                                    $status_text = 'Terjadwal';
                                                    break;
                                                case 'completed':
                                                    $status_class = 'primary';
                                                    $status_text = 'Selesai';
                                                    break;
                                                case 'approved':
                                                    $status_class = 'info';
                                                    $status_text = 'Disetujui';
                                                    break;
                                                default:
                                                    $status_class = 'warning';
                                                    $status_text = 'Proses';
                                            }
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('dosen/daftar_penguji/detail_proposal/' . $proposal->id) ?>" 
                                                   class="btn btn-primary btn-sm" title="Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Seminar Skripsi Tab -->
                    <div class="tab-pane fade" id="skripsi-content" role="tabpanel" aria-labelledby="skripsi-tab">
                        <div class="table-responsive mt-4">
                            <?php if (empty($seminar_skripsi)): ?>
                            <div class="text-center py-4">
                                <div class="icon icon-shape icon-lg bg-gradient-primary text-white rounded-circle mx-auto mb-3">
                                    <i class="fa fa-graduation-cap"></i>
                                </div>
                                <h5 class="text-muted">Tidak Ada Tugas Penguji</h5>
                                <p class="text-sm text-muted">Anda belum ditugaskan sebagai penguji seminar skripsi</p>
                            </div>
                            <?php else: ?>
                            <table class="table table-flush table-hover" id="table-skripsi">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Mahasiswa</th>
                                        <th>Judul Skripsi</th>
                                        <th>Jadwal Seminar</th>
                                        <th>Posisi Penguji</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($seminar_skripsi as $skripsi): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold"><?= $skripsi->nama_mahasiswa ?></span>
                                                    <br>
                                                    <small class="text-muted"><?= $skripsi->nim ?></small>
                                                    <br>
                                                    <small class="text-muted"><?= $skripsi->nama_prodi ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm font-weight-bold" title="<?= $skripsi->judul ?>">
                                                <?= strlen($skripsi->judul) > 50 ? substr($skripsi->judul, 0, 50) . '...' : $skripsi->judul ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">Pembimbing: <?= $skripsi->nama_pembimbing ?></small>
                                        </td>
                                        <td>
                                            <?php if ($skripsi->tanggal_seminar): ?>
                                                <span class="text-sm">
                                                    <i class="fa fa-calendar text-primary"></i>
                                                    <?= date('d M Y', strtotime($skripsi->tanggal_seminar)) ?>
                                                </span>
                                                <br>
                                                <span class="text-sm">
                                                    <i class="fa fa-clock text-info"></i>
                                                    <?= date('H:i', strtotime($skripsi->jam_seminar)) ?> WIT
                                                </span>
                                                <br>
                                                <span class="text-sm">
                                                    <i class="fa fa-map-marker text-warning"></i>
                                                    <?= $skripsi->tempat_seminar ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Belum Dijadwalkan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $skripsi->posisi_penguji == 'Penguji 1' ? 'primary' : 'info' ?>">
                                                <?= $skripsi->posisi_penguji ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch($skripsi->status) {
                                                case 'scheduled':
                                                    $status_class = 'success';
                                                    $status_text = 'Terjadwal';
                                                    break;
                                                case 'completed':
                                                    $status_class = 'primary';
                                                    $status_text = 'Selesai';
                                                    break;
                                                case 'approved':
                                                    $status_class = 'info';
                                                    $status_text = 'Disetujui';
                                                    break;
                                                default:
                                                    $status_class = 'warning';
                                                    $status_text = 'Proses';
                                            }
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('dosen/daftar_penguji/detail_skripsi/' . $skripsi->id) ?>" 
                                                   class="btn btn-primary btn-sm" title="Detail">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>