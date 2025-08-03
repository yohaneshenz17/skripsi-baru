<?php
/**
 * Views untuk Kaprodi Seminar Skripsi Phase 5
 * 
 * File struktur:
 * - application/views/kaprodi/seminar_skripsi/index.php (Dashboard)
 * - application/views/kaprodi/seminar_skripsi/detail.php (Review Turnitin)
 * - application/views/kaprodi/seminar_skripsi/penjadwalan.php (Set Jadwal & Penguji)
 */
?>

<!-- ====================================== -->
<!-- FILE: application/views/kaprodi/seminar_skripsi/index.php -->
<!-- Dashboard Seminar Skripsi untuk Kaprodi -->
<!-- ====================================== -->

<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-graduation-cap"></i> Kelola Seminar Skripsi</h1>
                    <p class="text-muted">Phase 5: Review Turnitin & Penjadwalan Seminar</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Seminar Skripsi</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Cards -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Total Pengajuan -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total Pengajuan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>

                <!-- Perlu Review -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $stats['perlu_review'] ?></h3>
                            <p>Perlu Review Turnitin</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <a href="#review-section" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Perlu Dijadwalkan -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['perlu_dijadwalkan'] ?></h3>
                            <p>Perlu Dijadwalkan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <a href="#jadwal-section" class="small-box-footer">
                            Jadwalkan <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Terjadwal -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?= $stats['terjadwal'] ?></h3>
                            <p>Sudah Terjadwal</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <a href="#terjadwal-section" class="small-box-footer">
                            Lihat Jadwal <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pengajuan Perlu Review -->
            <div class="row" id="review-section">
                <div class="col-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search mr-2"></i>
                                Pengajuan Perlu Review Turnitin
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning"><?= count($pengajuan_review) ?> pengajuan</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pengajuan_review)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Tidak ada pengajuan yang perlu direview saat ini.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Mahasiswa</th>
                                                <th width="35%">Judul Skripsi</th>
                                                <th width="15%">Pembimbing</th>
                                                <th width="15%">Tgl Disetujui Pembimbing</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pengajuan_review as $i => $pengajuan): ?>
                                                <tr>
                                                    <td><?= $i + 1 ?></td>
                                                    <td>
                                                        <strong><?= $pengajuan->nama_mahasiswa ?></strong><br>
                                                        <small class="text-muted"><?= $pengajuan->nim ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-truncate d-block" style="max-width: 300px;" 
                                                              title="<?= $pengajuan->judul ?>">
                                                            <?= $pengajuan->judul ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $pengajuan->nama_pembimbing ?: 'Belum ditetapkan' ?></td>
                                                    <td>
                                                        <?php if ($pengajuan->tanggal_review_pembimbing): ?>
                                                            <small class="text-success">
                                                                <i class="fas fa-check mr-1"></i>
                                                                <?= date('d/m/Y H:i', strtotime($pengajuan->tanggal_review_pembimbing)) ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Belum direview</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('kaprodi/seminar_skripsi/detail/' . $pengajuan->id) ?>" 
                                                           class="btn btn-sm btn-warning">
                                                            <i class="fas fa-search mr-1"></i> Review
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seminar Perlu Dijadwalkan -->
            <div class="row" id="jadwal-section">
                <div class="col-12">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Seminar Perlu Dijadwalkan
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-success"><?= count($perlu_dijadwalkan) ?> seminar</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($perlu_dijadwalkan)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Semua seminar yang disetujui sudah dijadwalkan.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Mahasiswa</th>
                                                <th width="35%">Judul Skripsi</th>
                                                <th width="15%">Pembimbing</th>
                                                <th width="15%">Tgl Disetujui</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($perlu_dijadwalkan as $i => $seminar): ?>
                                                <tr>
                                                    <td><?= $i + 1 ?></td>
                                                    <td>
                                                        <strong><?= $seminar->nama_mahasiswa ?></strong><br>
                                                        <small class="text-muted"><?= $seminar->nim ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-truncate d-block" style="max-width: 300px;" 
                                                              title="<?= $seminar->judul ?>">
                                                            <?= $seminar->judul ?>
                                                        </span>
                                                        <?php if ($seminar->plagiarism_percentage): ?>
                                                            <br><small class="text-info">
                                                                <i class="fas fa-chart-line mr-1"></i>
                                                                Plagiarisme: <?= $seminar->plagiarism_percentage ?>%
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $seminar->nama_pembimbing ?: 'Belum ditetapkan' ?></td>
                                                    <td>
                                                        <small class="text-success">
                                                            <i class="fas fa-check mr-1"></i>
                                                            <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('kaprodi/seminar_skripsi/penjadwalan/' . $seminar->id) ?>" 
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-calendar-plus mr-1"></i> Jadwalkan
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal Mendatang -->
            <div class="row" id="terjadwal-section">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Jadwal Seminar Mendatang
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-primary"><?= count($jadwal_mendatang) ?> jadwal</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($jadwal_mendatang)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Tidak ada jadwal seminar dalam waktu dekat.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Mahasiswa</th>
                                                <th width="25%">Judul</th>
                                                <th width="15%">Jadwal</th>
                                                <th width="15%">Tempat</th>
                                                <th width="25%">Tim Penguji</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($jadwal_mendatang as $i => $jadwal): ?>
                                                <tr>
                                                    <td><?= $i + 1 ?></td>
                                                    <td>
                                                        <strong><?= $jadwal->nama_mahasiswa ?></strong><br>
                                                        <small class="text-muted"><?= $jadwal->nim ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="text-truncate d-block" style="max-width: 200px;" 
                                                              title="<?= $jadwal->judul ?>">
                                                            <?= $jadwal->judul ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?= date('d M Y', strtotime($jadwal->tanggal_seminar)) ?></strong><br>
                                                        <small class="text-info">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            <?= date('H:i', strtotime($jadwal->jam_seminar)) ?> WIB
                                                        </small>
                                                    </td>
                                                    <td><?= $jadwal->tempat_seminar ?></td>
                                                    <td>
                                                        <small>
                                                            <strong>Pembimbing:</strong> <?= $jadwal->nama_pembimbing ?><br>
                                                            <strong>Penguji 1:</strong> <?= $jadwal->nama_penguji1 ?><br>
                                                            <strong>Penguji 2:</strong> <?= $jadwal->nama_penguji2 ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
