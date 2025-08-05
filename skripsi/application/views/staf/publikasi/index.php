<?php
/**
 * View Index Publikasi untuk Staf
 * File: application/views/staf/publikasi/index.php
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Validasi Publikasi Tugas Akhir</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Publikasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['menunggu_validasi'] ?></h3>
                        <p>Menunggu Validasi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="#pengajuan-section" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $stats['sudah_divalidasi'] ?></h3>
                        <p>Sudah Divalidasi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="#riwayat-section" class="small-box-footer">
                        Lihat Riwayat <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['publikasi_selesai'] ?></h3>
                        <p>Publikasi Selesai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="small-box-footer">
                        Total Keseluruhan
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['total_bulan_ini'] ?></h3>
                        <p>Validasi Bulan Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="small-box-footer">
                        <?= date('F Y') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow Info Card -->
        <div class="row">
            <div class="col-12">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Workflow Publikasi - Tugas Staf
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="workflow-steps">
                            <div class="step">
                                <div class="step-number bg-secondary">1-2</div>
                                <div class="step-content">
                                    <strong>Mahasiswa & Dosen</strong>
                                    <small>Mahasiswa ajukan → Dosen approve</small>
                                </div>
                            </div>
                            <div class="step current">
                                <div class="step-number bg-warning">3</div>
                                <div class="step-content">
                                    <strong>Input Repository</strong>
                                    <small>Staf input link repository skripsi</small>
                                </div>
                            </div>
                            <div class="step current">
                                <div class="step-number bg-warning">4</div>
                                <div class="step-content">
                                    <strong>Validasi Final</strong>
                                    <small>Staf validasi & kirim notifikasi</small>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number bg-success">5-6</div>
                                <div class="step-content">
                                    <strong>Selesai</strong>
                                    <small>Mahasiswa download surat</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengajuan Perlu Validasi -->
        <div class="row" id="pengajuan-section">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            Pengajuan Perlu Validasi (<?= count($pengajuan_validasi) ?>)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pengajuan_validasi)): ?>
                            <div class="table-responsive">
                                <table id="pengajuanTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Mahasiswa</th>
                                            <th>Judul Skripsi</th>
                                            <th>Dosen Pembimbing</th>
                                            <th>Tgl Approve Dosen</th>
                                            <th>Status Repository</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pengajuan_validasi as $index => $pub): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <strong><?= esc($pub->nama_mahasiswa) ?></strong><br>
                                                <small class="text-muted"><?= esc($pub->nim) ?></small><br>
                                                <small class="text-info"><?= esc($pub->program_studi) ?></small>
                                            </td>
                                            <td>
                                                <span class="text-wrap" style="max-width: 300px;">
                                                    <?= esc(word_limiter($pub->judul_skripsi_final, 8)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= esc($pub->nama_dosen_pembimbing) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($pub->tanggal_review_pembimbing): ?>
                                                    <small><?= date('d/m/Y H:i', strtotime($pub->tanggal_review_pembimbing)) ?></small>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($pub->link_repository)): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-link"></i> Sudah Input
                                                    </span>
                                                    <br><small class="text-muted">Siap validasi</small>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> Belum Input
                                                    </span>
                                                    <br><small class="text-muted">Perlu input link</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('staf/publikasi/detail/' . $pub->id) ?>" 
                                                       class="btn btn-sm btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if (empty($pub->link_repository)): ?>
                                                        <a href="<?= base_url('staf/publikasi/input_repository/' . $pub->id) ?>" 
                                                           class="btn btn-sm btn-warning" title="Input Repository">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('staf/publikasi/validasi/' . $pub->id) ?>" 
                                                           class="btn btn-sm btn-success" title="Validasi Final">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Tidak ada pengajuan yang perlu divalidasi saat ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Validasi -->
        <div class="row" id="riwayat-section">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Riwayat Validasi Terakhir (10 Data)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($riwayat_validasi)): ?>
                            <div class="table-responsive">
                                <table id="riwayatTable" class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Judul Skripsi</th>
                                            <th>Tgl Validasi</th>
                                            <th>Status</th>
                                            <th>Repository Link</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($riwayat_validasi as $riwayat): ?>
                                        <tr>
                                            <td>
                                                <strong><?= esc($riwayat->nama_mahasiswa) ?></strong><br>
                                                <small class="text-muted"><?= esc($riwayat->nim) ?></small>
                                            </td>
                                            <td>
                                                <span class="text-wrap" style="max-width: 250px;">
                                                    <?= esc(word_limiter($riwayat->judul_skripsi_final, 6)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($riwayat->tanggal_validasi_staf)) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($riwayat->status_staf === 'approved'): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Disetujui
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> Ditolak
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($riwayat->link_repository)): ?>
                                                    <a href="<?= esc($riwayat->link_repository) ?>" 
                                                       target="_blank" class="btn btn-xs btn-outline-primary">
                                                        <i class="fas fa-external-link-alt"></i> Lihat
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('staf/publikasi/detail/' . $riwayat->id) ?>" 
                                                   class="btn btn-xs btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Belum ada riwayat validasi.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
/* Workflow Steps */
.workflow-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin: 20px 0;
}

.step {
    display: flex;
    align-items: center;
    flex: 1;
    margin: 10px;
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
}

.step.current {
    background: #fff3cd;
    border-color: #ffc107;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin-right: 15px;
    flex-shrink: 0;
}

.step-content strong {
    display: block;
    font-size: 14px;
    margin-bottom: 5px;
}

.step-content small {
    color: #6c757d;
    font-size: 12px;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .workflow-steps {
        flex-direction: column;
    }
    
    .step {
        width: 100%;
        margin: 5px 0;
    }
}

/* Table responsiveness */
.table-responsive {
    border-radius: 8px;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

/* Badge improvements */
.badge {
    font-size: 11px;
    padding: 4px 8px;
}

/* Button group improvements */
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>