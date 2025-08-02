<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detail Seminar Skripsi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('dosen') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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

        <div class="row">
            <!-- Informasi Mahasiswa & Skripsi -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Informasi Mahasiswa & Skripsi
                        </h3>
                        <div class="card-tools">
                            <!-- Status Badge -->
                            <?php if ($seminar->status == 'submitted'): ?>
                                <span class="badge badge-info badge-lg">Submitted</span>
                            <?php elseif ($seminar->status == 'review_pembimbing'): ?>
                                <span class="badge badge-warning badge-lg">Review Pembimbing</span>
                            <?php elseif ($seminar->status == 'review_kaprodi'): ?>
                                <span class="badge badge-primary badge-lg">Review Kaprodi</span>
                            <?php elseif ($seminar->status == 'approved'): ?>
                                <span class="badge badge-success badge-lg">Disetujui</span>
                            <?php elseif ($seminar->status == 'rejected'): ?>
                                <span class="badge badge-danger badge-lg">Ditolak</span>
                            <?php elseif ($seminar->status == 'scheduled'): ?>
                                <span class="badge badge-info badge-lg">Terjadwal</span>
                            <?php elseif ($seminar->status == 'completed'): ?>
                                <span class="badge badge-success badge-lg">Selesai</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="font-weight-bold">NIM</td>
                                        <td>:</td>
                                        <td><?= $seminar->nim ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Nama</td>
                                        <td>:</td>
                                        <td><?= $seminar->nama_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Email</td>
                                        <td>:</td>
                                        <td><?= $seminar->email_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Pembimbing</td>
                                        <td>:</td>
                                        <td><?= $seminar->nama_pembimbing ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="font-weight-bold">Status Proposal</td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge badge-<?= $seminar->workflow_status == 'seminar_skripsi' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $seminar->workflow_status)) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tanggal Pengajuan</td>
                                        <td>:</td>
                                        <td><?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Current Step</td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge badge-info"><?= ucfirst($seminar->current_step) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Plagiarism Check</td>
                                        <td>:</td>
                                        <td>
                                            <?php if ($seminar->plagiarism_percentage !== null): ?>
                                                <span class="badge badge-<?= $seminar->plagiarism_percentage <= 20 ? 'success' : 'warning' ?>">
                                                    <?= $seminar->plagiarism_percentage ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Belum dicek</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Judul Skripsi -->
                        <div class="mt-3">
                            <strong>Judul Skripsi:</strong>
                            <p class="mt-2 p-3 bg-light border-left border-primary">
                                <?= $seminar->judul ?>
                            </p>
                        </div>
                        
                        <!-- Keterangan Mahasiswa -->
                        <?php if ($seminar->keterangan_mahasiswa): ?>
                            <div class="mt-3">
                                <strong>Keterangan dari Mahasiswa:</strong>
                                <p class="mt-2 p-3 bg-light border-left border-info">
                                    <?= nl2br($seminar->keterangan_mahasiswa) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- File Skripsi -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-pdf mr-2"></i>
                            File Skripsi
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if ($seminar->file_skripsi): ?>
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= $seminar->file_skripsi ?></h6>
                                    <small class="text-muted">
                                        Diunggah: <?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?>
                                    </small>
                                </div>
                                <div>
                                    <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                       class="btn btn-primary" target="_blank">
                                        <i class="fas fa-eye mr-2"></i>Lihat File
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-file-times fa-3x mb-3"></i>
                                <p>File skripsi belum diunggah</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Eligibility Check -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-circle mr-2"></i>
                            Kelayakan Seminar Skripsi
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="mr-3">
                                        <?php if ($eligibility['seminar_proposal_ok']): ?>
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Seminar Proposal</h6>
                                        <small class="text-muted">
                                            <?= $eligibility['seminar_proposal_ok'] ? 'Sudah selesai' : 'Belum selesai' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="mr-3">
                                        <?php if ($eligibility['penelitian_ok']): ?>
                                            <i class="fas fa-check-circle fa-2x text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Izin Penelitian</h6>
                                        <small class="text-muted">
                                            <?= $eligibility['penelitian_ok'] ? 'Sudah disetujui' : 'Belum disetujui' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <?php if ($eligibility['eligible']): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check mr-2"></i>
                                    Mahasiswa memenuhi syarat untuk seminar skripsi
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Mahasiswa belum memenuhi syarat untuk seminar skripsi
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="col-md-4">
                <!-- Aksi Rekomendasi -->
                <?php if ($seminar->status == 'review_pembimbing' && $seminar->current_step == 'pembimbing'): ?>
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title text-white">
                                <i class="fas fa-tasks mr-2"></i>
                                Aksi Diperlukan
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-center mb-4">
                                <strong>Berikan rekomendasi untuk pengajuan seminar skripsi ini</strong>
                            </p>
                            
                            <?php if ($eligibility['eligible']): ?>
                                <!-- Form Rekomendasi -->
                                <form action="<?= base_url('dosen/seminar_skripsi/rekomendasi') ?>" method="POST">
                                    <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                                    
                                    <div class="form-group">
                                        <label for="komentar_pembimbing">Komentar/Feedback:</label>
                                        <textarea name="komentar_pembimbing" id="komentar_pembimbing" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Berikan komentar atau feedback (opsional untuk persetujuan, wajib untuk penolakan)"></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="submit" name="rekomendasi" value="approved" 
                                                    class="btn btn-success btn-block"
                                                    onclick="return confirmRekomendasi('approved')">
                                                <i class="fas fa-check mr-2"></i>Setujui
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" name="rekomendasi" value="rejected" 
                                                    class="btn btn-danger btn-block"
                                                    onclick="return confirmRekomendasi('rejected')">
                                                <i class="fas fa-times mr-2"></i>Tolak
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Tidak dapat diproses!</strong><br>
                                    Mahasiswa belum memenuhi syarat untuk seminar skripsi.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status Rekomendasi -->
                <?php if ($seminar->status_pembimbing != 'pending'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Status Rekomendasi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <?php if ($seminar->status_pembimbing == 'approved'): ?>
                                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                    <h5 class="text-success">Disetujui</h5>
                                <?php else: ?>
                                    <i class="fas fa-times-circle fa-3x text-danger mb-2"></i>
                                    <h5 class="text-danger">Ditolak</h5>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($seminar->komentar_pembimbing): ?>
                                <div class="mt-3">
                                    <strong>Komentar:</strong>
                                    <p class="mt-2 p-3 bg-light border-left border-primary">
                                        <?= nl2br($seminar->komentar_pembimbing) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($seminar->tanggal_review_pembimbing): ?>
                                <small class="text-muted">
                                    Direview: <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Informasi Seminar -->
                <?php if ($seminar->tanggal_seminar): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Jadwal Seminar
                            </h3>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-calendar fa-3x text-primary mb-3"></i>
                            <h5><?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?></h5>
                            <p class="mb-1">
                                <i class="fas fa-clock mr-2"></i>
                                <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIB
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?= $seminar->tempat_seminar ?>
                            </p>
                            
                            <?php if (in_array($seminar->status, ['scheduled', 'completed'])): ?>
                                <div class="mt-3">
                                    <a href="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                                       class="btn btn-primary btn-block">
                                        <i class="fas fa-edit mr-2"></i>Input Penilaian
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Jurnal Bimbingan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-book mr-2"></i>
                            Jurnal Bimbingan Terbaru
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($jurnal_bimbingan)): ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>Belum ada jurnal bimbingan</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($jurnal_bimbingan, 0, 3) as $jurnal): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <small class="font-weight-bold">
                                            <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                                        </small>
                                        <?php if ($jurnal->status_validasi == '1'): ?>
                                            <span class="badge badge-success badge-sm">Valid</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning badge-sm">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0 text-sm">
                                        <?= character_limiter($jurnal->topik_bimbingan, 60) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($jurnal_bimbingan) > 3): ?>
                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        Dan <?= count($jurnal_bimbingan) - 3 ?> jurnal lainnya...
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('dosen/seminar_skripsi') ?>" 
                               class="btn btn-outline-secondary btn-block mb-2">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                            </a>
                            
                            <?php if ($seminar->file_skripsi): ?>
                                <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                   class="btn btn-outline-primary btn-block mb-2" target="_blank">
                                    <i class="fas fa-download mr-2"></i>Download Skripsi
                                </a>
                            <?php endif; ?>
                            
                            <a href="mailto:<?= $seminar->email_mahasiswa ?>?subject=Seminar Skripsi - <?= $seminar->nama_mahasiswa ?>" 
                               class="btn btn-outline-info btn-block">
                                <i class="fas fa-envelope mr-2"></i>Email Mahasiswa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.border-left {
    border-left: 4px solid !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
    margin-bottom: 1.5rem;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.text-sm {
    font-size: 0.875rem;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

.btn-block {
    display: block;
    width: 100%;
}

.fa-3x {
    font-size: 3em;
}

.fa-2x {
    font-size: 2em;
}
</style>