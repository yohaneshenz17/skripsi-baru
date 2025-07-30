<?php
/**
 * View Detail Permohonan Izin Penelitian - Mahasiswa
 * File: application/views/mahasiswa/penelitian/detail.php
 * 
 * Menampilkan detail lengkap permohonan dengan tracking progress dan timeline
 * Mengikuti design pattern existing dengan Bootstrap 4
 */
?>

<!-- Header dengan Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('mahasiswa/penelitian') ?>">Penelitian</a>
                </li>
                <li class="breadcrumb-item active">Detail Permohonan #<?= $permohonan->id ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Status Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-left-primary shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Permohonan Izin Penelitian
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php
                            switch ($permohonan->status) {
                                case 'draft':
                                    echo 'Draft Permohonan';
                                    break;
                                case 'submitted':
                                case 'review_pembimbing':
                                    echo 'Menunggu Review Pembimbing';
                                    break;
                                case 'approved':
                                    echo 'Disetujui - Menunggu Staf';
                                    break;
                                case 'rejected':
                                    echo 'Ditolak Pembimbing';
                                    break;
                                case 'surat_ready':
                                    echo 'Surat Siap Download';
                                    break;
                                case 'completed':
                                    echo 'Selesai';
                                    break;
                                default:
                                    echo 'Status Tidak Dikenal';
                            }
                            ?>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            Diajukan: <?= date('d F Y, H:i', strtotime($permohonan->created_at)) ?> WIB
                        </div>
                    </div>
                    <div class="col-auto">
                        <?php
                        $status_class = '';
                        switch ($permohonan->status) {
                            case 'submitted':
                            case 'review_pembimbing':
                                $status_class = 'warning';
                                break;
                            case 'approved':
                                $status_class = 'info';
                                break;
                            case 'rejected':
                                $status_class = 'danger';
                                break;
                            case 'surat_ready':
                            case 'completed':
                                $status_class = 'success';
                                break;
                            default:
                                $status_class = 'secondary';
                        }
                        ?>
                        <span class="badge badge-<?= $status_class ?> badge-pill px-3 py-2" style="font-size: 12px;">
                            <?php
                            switch ($permohonan->status) {
                                case 'submitted':
                                case 'review_pembimbing':
                                    echo 'REVIEW';
                                    break;
                                case 'approved':
                                    echo 'APPROVED';
                                    break;
                                case 'rejected':
                                    echo 'REJECTED';
                                    break;
                                case 'surat_ready':
                                    echo 'READY';
                                    break;
                                case 'completed':
                                    echo 'COMPLETED';
                                    break;
                                default:
                                    echo 'DRAFT';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Tracking -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line mr-2"></i>Progress Permohonan
                </h6>
            </div>
            <div class="card-body">
                <div class="progress-steps-detail">
                    <?php foreach ($progress_steps as $index => $step): ?>
                    <div class="step-item-detail <?= $step['status'] ?>">
                        <div class="step-number">
                            <?php if ($step['status'] == 'completed'): ?>
                                <i class="fas fa-check"></i>
                            <?php elseif ($step['status'] == 'error'): ?>
                                <i class="fas fa-times"></i>
                            <?php elseif ($step['status'] == 'active'): ?>
                                <i class="fas fa-clock"></i>
                            <?php else: ?>
                                <?= $index + 1 ?>
                            <?php endif; ?>
                        </div>
                        <div class="step-content-detail">
                            <h6><?= $step['title'] ?></h6>
                            <small class="text-muted">
                                <?php
                                switch ($step['status']) {
                                    case 'completed':
                                        echo 'Selesai';
                                        break;
                                    case 'active':
                                        echo 'Sedang Proses';
                                        break;
                                    case 'error':
                                        echo 'Ditolak';
                                        break;
                                    default:
                                        echo 'Menunggu';
                                }
                                ?>
                            </small>
                        </div>
                        <?php if ($index < count($progress_steps) - 1): ?>
                        <div class="step-line"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Data Permohonan -->
    <div class="col-lg-8">
        <!-- Info Permohonan -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-alt mr-2"></i>Detail Permohonan
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama Mahasiswa</strong></td>
                                <td>: <?= $permohonan->nama_mahasiswa ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM</strong></td>
                                <td>: <?= $permohonan->nim ?></td>
                            </tr>
                            <tr>
                                <td><strong>Semester</strong></td>
                                <td>: <?= $permohonan->semester ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?= $permohonan->program_studi ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Tempat Penelitian</strong></td>
                                <td>: <?= $permohonan->tempat_penelitian ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Mulai</strong></td>
                                <td>: <?= date('d F Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Selesai</strong></td>
                                <td>: <?= date('d F Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Dosen Pembimbing</strong></td>
                                <td>: <?= $permohonan->nama_pembimbing ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Judul Skripsi -->
                <div class="mt-3">
                    <h6><strong>Judul Skripsi:</strong></h6>
                    <p class="text-muted"><?= $permohonan->judul_skripsi_terbaru ?></p>
                </div>

                <!-- File Proposal Revisi -->
                <?php if ($permohonan->file_proposal_revisi): ?>
                <div class="mt-3">
                    <h6><strong>Proposal Revisi:</strong></h6>
                    <a href="<?= base_url('uploads/proposal_revisi/' . $permohonan->file_proposal_revisi) ?>" 
                       target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-pdf mr-1"></i>Lihat File
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Review Pembimbing -->
        <?php if ($permohonan->tanggal_review_pembimbing): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-check mr-2"></i>Review Dosen Pembimbing
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <span class="badge badge-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                                <?= $permohonan->status_pembimbing == 'approved' ? 'DISETUJUI' : 'DITOLAK' ?>
                            </span>
                        </p>
                        <p><strong>Tanggal Review:</strong> <?= date('d F Y, H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?> WIB</p>
                        <p><strong>Reviewer:</strong> <?= $permohonan->nama_pembimbing ?></p>
                    </div>
                </div>
                
                <?php if ($permohonan->komentar_pembimbing): ?>
                <div class="mt-3">
                    <h6><strong>Komentar/Catatan:</strong></h6>
                    <div class="alert alert-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?> border-left-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                        <?= nl2br(htmlspecialchars($permohonan->komentar_pembimbing)) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Surat Izin -->
        <?php if ($permohonan->file_surat_izin_staf): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-contract mr-2"></i>Surat Izin Penelitian
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <span class="badge badge-success">TERSEDIA</span>
                        </p>
                        <p><strong>Tanggal Diterbitkan:</strong> <?= date('d F Y, H:i', strtotime($permohonan->tanggal_upload_surat_staf)) ?> WIB</p>
                    </div>
                </div>
                
                <?php if ($permohonan->keterangan_staf): ?>
                <div class="mt-3">
                    <h6><strong>Keterangan Staf:</strong></h6>
                    <div class="alert alert-info border-left-info">
                        <?= nl2br(htmlspecialchars($permohonan->keterangan_staf)) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($can_download): ?>
                <div class="mt-3">
                    <a href="<?= base_url('mahasiswa/penelitian/download_surat/' . $permohonan->id) ?>" 
                       class="btn btn-success">
                        <i class="fas fa-download mr-2"></i>Download Surat Izin
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt mr-2"></i>Aksi Cepat
                </h6>
            </div>
            <div class="card-body">
                <a href="<?= base_url('mahasiswa/penelitian') ?>" 
                   class="btn btn-outline-primary btn-block btn-sm mb-2">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Dashboard
                </a>
                
                <?php if ($can_download): ?>
                <a href="<?= base_url('mahasiswa/penelitian/download_surat/' . $permohonan->id) ?>" 
                   class="btn btn-success btn-block btn-sm mb-2">
                    <i class="fas fa-download mr-1"></i>Download Surat
                </a>
                <?php endif; ?>
                
                <?php if ($permohonan->status == 'rejected'): ?>
                <a href="<?= base_url('mahasiswa/penelitian/ajukan') ?>" 
                   class="btn btn-warning btn-block btn-sm mb-2">
                    <i class="fas fa-redo mr-1"></i>Ajukan Ulang
                </a>
                <?php endif; ?>

                <button class="btn btn-outline-secondary btn-block btn-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i>Cetak Detail
                </button>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history mr-2"></i>Timeline Aktivitas
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- Pengajuan -->
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Permohonan Diajukan</h6>
                            <p class="timeline-date"><?= date('d M Y, H:i', strtotime($permohonan->created_at)) ?></p>
                        </div>
                    </div>

                    <!-- Review Pembimbing -->
                    <?php if ($permohonan->tanggal_review_pembimbing): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?>"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">
                                <?= $permohonan->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak' ?> Pembimbing
                            </h6>
                            <p class="timeline-date"><?= date('d M Y, H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Surat Diterbitkan -->
                    <?php if ($permohonan->tanggal_upload_surat_staf): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Surat Diterbitkan</h6>
                            <p class="timeline-date"><?= date('d M Y, H:i', strtotime($permohonan->tanggal_upload_surat_staf)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Completed -->
                    <?php if ($permohonan->status == 'completed'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Selesai</h6>
                            <p class="timeline-date">Surat telah didownload</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info Contact -->
        <div class="card shadow border-left-info">
            <div class="card-body">
                <h6 class="text-info">
                    <i class="fas fa-phone mr-2"></i>Butuh Bantuan?
                </h6>
                <small class="text-muted">
                    Jika ada pertanyaan tentang status permohonan, silakan hubungi:
                    <br><br>
                    <strong>Unit SIPD</strong><br>
                    Email: sipd@stkstjak.ac.id<br>
                    Telp: (021) 123-4567<br>
                    <br>
                    <strong>Dosen Pembimbing</strong><br>
                    <?= $permohonan->nama_pembimbing ?><br>
                    <?= $permohonan->email_pembimbing ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- CSS untuk Progress Steps dan Timeline -->
<style>
/* Progress Steps Detail */
.progress-steps-detail {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step-item-detail {
    display: flex;
    align-items: flex-start;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 20px;
    flex-shrink: 0;
}

.step-content-detail {
    flex-grow: 1;
}

.step-content-detail h6 {
    margin-bottom: 5px;
    color: #5a5c69;
}

.step-line {
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 20px;
    background-color: #dee2e6;
}

/* Status Colors untuk Steps */
.step-item-detail.pending .step-number {
    background-color: #f8f9fa;
    border: 2px solid #dee2e6;
    color: #6c757d;
}

.step-item-detail.active .step-number {
    background-color: #ffc107;
    border: 2px solid #ffc107;
    color: white;
}

.step-item-detail.completed .step-number {
    background-color: #28a745;
    border: 2px solid #28a745;
    color: white;
}

.step-item-detail.error .step-number {
    background-color: #dc3545;
    border: 2px solid #dc3545;
    color: white;
}

.step-item-detail.completed .step-line {
    background-color: #28a745;
}

.step-item-detail.active .step-line {
    background-color: #ffc107;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #5a5c69;
}

.timeline-date {
    font-size: 12px;
    color: #858796;
    margin-bottom: 0;
}

/* Print Styles */
@media print {
    .card-header {
        background-color: #f8f9fc !important;
        color: #5a5c69 !important;
    }
    
    .btn, .timeline, .card:last-child {
        display: none !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .progress-steps-detail {
        gap: 15px;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        margin-right: 15px;
    }
    
    .step-line {
        left: 16px;
        height: 15px;
    }
}
</style>