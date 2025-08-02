<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Seminar Skripsi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('dosen') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Seminar Skripsi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total Bimbingan Skripsi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['perlu_review'] ?></h3>
                        <p>Perlu Review</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['disetujui'] ?></h3>
                        <p>Disetujui</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $stats['ditolak'] ?></h3>
                        <p>Ditolak</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pengajuan Perlu Review -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-hourglass-half mr-2"></i>
                            Pengajuan Perlu Review
                        </h3>
                        <?php if (count($pengajuan_review) > 0): ?>
                            <span class="badge badge-warning float-right"><?= count($pengajuan_review) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pengajuan_review)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Tidak ada pengajuan yang perlu direview</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Judul</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pengajuan_review as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $item->nama_mahasiswa ?></strong><br>
                                                    <small class="text-muted"><?= $item->nim ?></small>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                          title="<?= $item->judul ?>">
                                                        <?= character_limiter($item->judul, 30) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y H:i', strtotime($item->created_at)) ?></small>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $item->id) ?>" 
                                                       class="btn btn-sm btn-primary" title="Review">
                                                        <i class="fas fa-eye"></i>
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

            <!-- Seminar Perlu Penilaian -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-edit mr-2"></i>
                            Seminar Perlu Penilaian
                        </h3>
                        <?php if (count($perlu_penilaian) > 0): ?>
                            <span class="badge badge-info float-right"><?= count($perlu_penilaian) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($perlu_penilaian)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                                <p>Tidak ada seminar yang perlu dinilai</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Jadwal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($perlu_penilaian as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $item->nama_mahasiswa ?></strong><br>
                                                    <small class="text-muted"><?= $item->nim ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($item->tanggal_seminar): ?>
                                                        <strong><?= date('d/m/Y', strtotime($item->tanggal_seminar)) ?></strong><br>
                                                        <small class="text-muted">
                                                            <?= date('H:i', strtotime($item->jam_seminar)) ?> - <?= $item->tempat_seminar ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum dijadwalkan</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item->status == 'scheduled'): ?>
                                                        <span class="badge badge-warning">Terjadwal</span>
                                                    <?php elseif ($item->status == 'completed'): ?>
                                                        <span class="badge badge-success">Selesai</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (empty($item->status_penilaian) || $item->status_penilaian == 'draft'): ?>
                                                        <br><small class="text-danger">Belum dinilai</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('dosen/seminar_skripsi/penilaian/' . $item->id) ?>" 
                                                       class="btn btn-sm btn-success" title="Input Penilaian">
                                                        <i class="fas fa-edit"></i>
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

        <!-- Riwayat Rekomendasi -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history mr-2"></i>
                            Riwayat Rekomendasi Terbaru
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($riwayat_rekomendasi)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>Belum ada riwayat rekomendasi</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="table-riwayat">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Judul Skripsi</th>
                                            <th>Rekomendasi</th>
                                            <th>Komentar</th>
                                            <th>Tanggal Review</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($riwayat_rekomendasi as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $item->nama_mahasiswa ?></strong><br>
                                                    <small class="text-muted"><?= $item->nim ?></small>
                                                </td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" 
                                                          title="<?= $item->judul ?>">
                                                        <?= character_limiter($item->judul, 40) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($item->status_pembimbing == 'approved'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>Disetujui
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times mr-1"></i>Ditolak
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item->komentar_pembimbing): ?>
                                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                              title="<?= $item->komentar_pembimbing ?>">
                                                            <?= character_limiter($item->komentar_pembimbing, 30) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y H:i', strtotime($item->tanggal_review_pembimbing)) ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($item->status == 'review_kaprodi'): ?>
                                                        <span class="badge badge-warning">Review Kaprodi</span>
                                                    <?php elseif ($item->status == 'approved'): ?>
                                                        <span class="badge badge-info">Disetujui Kaprodi</span>
                                                    <?php elseif ($item->status == 'scheduled'): ?>
                                                        <span class="badge badge-primary">Terjadwal</span>
                                                    <?php elseif ($item->status == 'completed'): ?>
                                                        <span class="badge badge-success">Selesai</span>
                                                    <?php elseif ($item->status == 'rejected'): ?>
                                                        <span class="badge badge-danger">Ditolak</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $item->id) ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
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

    </div>
</section>

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="fas fa-info-circle mr-2"></i>
                    Panduan Seminar Skripsi untuk Dosen Pembimbing
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-primary">Review & Rekomendasi</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success mr-2"></i>Review file skripsi mahasiswa</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Berikan rekomendasi: approved/rejected</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Isi komentar/feedback (wajib jika tolak)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-primary">Input Penilaian</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-edit text-info mr-2"></i>Nilai Final (sistem 3 dosen)</li>
                            <li><i class="fas fa-edit text-info mr-2"></i>Catatan Revisi (6 komponen)</li>
                            <li><i class="fas fa-edit text-info mr-2"></i>Rekomendasi Seminar Skripsi</li>
                        </ul>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="font-weight-bold text-primary">Workflow Status</h6>
                <div class="row">
                    <div class="col-md-12">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 20%"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 20%"></div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 20%"></div>
                            <div class="progress-bar bg-success" role="progressbar" style="width: 20%"></div>
                            <div class="progress-bar bg-dark" role="progressbar" style="width: 20%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small>Submitted</small>
                            <small>Review Pembimbing</small>
                            <small>Review Kaprodi</small>
                            <small>Scheduled</small>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) -->
<div class="fab-container">
    <button type="button" class="btn btn-info rounded-circle fab-button" data-toggle="modal" data-target="#infoModal" 
            title="Panduan">
        <i class="fas fa-question"></i>
    </button>
</div>

<style>
.fab-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
}

.fab-button {
    width: 56px;
    height: 56px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.fab-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(0,0,0,0.4);
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}

.small-box {
    border-radius: 10px;
    position: relative;
    overflow: hidden;
}

.small-box .icon {
    transition: all 0.3s ease;
}

.small-box:hover .icon {
    transform: scale(1.1);
}
</style>