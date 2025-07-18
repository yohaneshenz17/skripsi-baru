<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Usulan Proposal - Penunjukan Pembimbing') ?>

<?php $this->app->section() ?>

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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Persetujuan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($proposals) ? count($proposals) : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Proposals pending</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Dosen ID</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $this->session->userdata('id') ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-user"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Session active</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Status</h5>
                        <span class="h2 font-weight-bold mb-0">ONLINE</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> Ready</span>
                    <span class="text-nowrap">to review</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Riwayat</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($riwayat_proposals) ? count($riwayat_proposals) : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-default text-white rounded-circle shadow">
                            <i class="fa fa-history"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Sudah direspon</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Workflow Info Panel -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">📋 Phase 1 Workflow: Kaprodi telah menunjuk Anda sebagai pembimbing. Silakan berikan persetujuan atau penolakan.</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Dosen:</strong> <?= $this->session->userdata('nama') ?> | 
                            <strong>Status:</strong> Menunggu persetujuan Anda untuk proposal di bawah ini
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Penunjukan Pembimbing Menunggu Persetujuan -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Penunjukan Pembimbing Menunggu Persetujuan</h3>
                        <p class="text-sm mb-0">Kaprodi telah menunjuk Anda sebagai pembimbing untuk proposal berikut</p>
                    </div>
                </div>
            </div>
            
            <?php if (isset($proposals) && count($proposals) > 0): ?>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class="sort" data-sort="name">Mahasiswa</th>
                                <th scope="col" class="sort" data-sort="budget">Proposal</th>
                                <th scope="col" class="sort" data-sort="status">Tanggal Penetapan</th>
                                <th scope="col" class="sort" data-sort="completion">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            <?php foreach ($proposals as $proposal): ?>
                                <tr>
                                    <td class="budget">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle mr-3">
                                                <?php
                                                $foto_mahasiswa = (!empty($proposal->foto_mahasiswa)) ? $proposal->foto_mahasiswa : 'default.png';
                                                $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mahasiswa);
                                                ?>
                                                <img alt="Foto <?= $proposal->nama_mahasiswa ?>" src="<?= $foto_path ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="media-body">
                                                <span class="name mb-0 text-sm font-weight-bold"><?= $proposal->nama_mahasiswa ?></span>
                                                <small class="d-block text-muted">NIM: <?= $proposal->nim ?></small>
                                                <small class="d-block text-muted"><?= $proposal->nama_prodi ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold mb-1">
                                                <?= substr($proposal->judul, 0, 60) ?><?= strlen($proposal->judul) > 60 ? '...' : '' ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($proposal->created_at)) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm">
                                            <?= $proposal->tanggal_penetapan ? date('d/m/Y', strtotime($proposal->tanggal_penetapan)) : 'N/A' ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= $proposal->tanggal_penetapan ? date('H:i', strtotime($proposal->tanggal_penetapan)) . ' WIT' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-dot mr-4">
                                            <i class="bg-warning"></i>
                                            <span class="status">Menunggu Persetujuan</span>
                                        </span>
                                        <br>
                                        <small class="text-muted">Pending Review</small>
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                <a class="dropdown-item" href="<?= base_url('dosen/usulan_proposal/detail/' . $proposal->id) ?>">
                                                    <i class="fa fa-eye"></i> Detail & Respon
                                                </a>
                                                <?php if ($proposal->file_draft_proposal): ?>
                                                    <a class="dropdown-item" href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" target="_blank">
                                                        <i class="fa fa-download"></i> Download File
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card-body text-center py-5">
                    <i class="fa fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Tidak Ada Penunjukan Pembimbing</h4>
                    <p class="text-muted mb-0">
                        Saat ini tidak ada proposal yang memerlukan persetujuan Anda sebagai pembimbing.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Riwayat Proposal yang Sudah Direspon -->
<?php if (isset($riwayat_proposals) && count($riwayat_proposals) > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Riwayat Respon Proposal</h3>
                            <p class="text-sm mb-0">Proposal yang sudah Anda respon sebelumnya</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Mahasiswa</th>
                                <th scope="col">Proposal</th>
                                <th scope="col">Tanggal Respon</th>
                                <th scope="col">Status</th>
                                <th scope="col">Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_proposals as $riwayat): ?>
                                <tr>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="avatar rounded-circle mr-3">
                                                <?php
                                                $foto_mahasiswa = (!empty($riwayat->foto_mahasiswa)) ? $riwayat->foto_mahasiswa : 'default.png';
                                                $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mahasiswa);
                                                ?>
                                                <img alt="Foto <?= $riwayat->nama_mahasiswa ?>" src="<?= $foto_path ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="media-body">
                                                <span class="name mb-0 text-sm font-weight-bold"><?= $riwayat->nama_mahasiswa ?></span>
                                                <small class="d-block text-muted">NIM: <?= $riwayat->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm">
                                            <?= substr($riwayat->judul, 0, 50) ?><?= strlen($riwayat->judul) > 50 ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm">
                                            <?= date('d M Y', strtotime($riwayat->tanggal_respon_pembimbing)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($riwayat->status_pembimbing == '1'): ?>
                                            <span class="badge badge-success">Disetujui</span>
                                        <?php elseif ($riwayat->status_pembimbing == '2'): ?>
                                            <span class="badge badge-danger">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Unknown</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-sm text-muted">
                                            <?= $riwayat->komentar_pembimbing ? substr($riwayat->komentar_pembimbing, 0, 40) . '...' : 'Tidak ada komentar' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-refresh page every 5 minutes for real-time updates
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>