<?php
/**
 * View Dashboard Penelitian - Mahasiswa
 * File: application/views/mahasiswa/penelitian/index.php
 * 
 * Menampilkan dashboard penelitian dengan progress tracking dan status permohonan
 * Mengikuti design pattern existing dengan Bootstrap 4 dan card-based layout
 */
?>

<!-- Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-left-primary shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tahap 4 - Penelitian
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            Permohonan Izin Penelitian
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            <?= $proposal->judul ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-search fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Tracking -->
<?php if ($permohonan): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line mr-2"></i>Progress Permohonan
                </h6>
            </div>
            <div class="card-body">
                <div class="progress-steps">
                    <?php foreach ($progress_steps as $index => $step): ?>
                    <div class="step-item <?= $step['status'] ?>">
                        <div class="step-icon">
                            <i class="fas fa-<?= $step['icon'] ?>"></i>
                        </div>
                        <div class="step-content">
                            <h6><?= $step['title'] ?></h6>
                            <small class="text-muted">
                                <?php
                                switch ($step['status']) {
                                    case 'completed':
                                        echo '<i class="fas fa-check text-success"></i> Selesai';
                                        break;
                                    case 'active':
                                        echo '<i class="fas fa-clock text-warning"></i> Sedang Proses';
                                        break;
                                    case 'error':
                                        echo '<i class="fas fa-times text-danger"></i> Ditolak';
                                        break;
                                    default:
                                        echo '<i class="fas fa-circle text-muted"></i> Menunggu';
                                }
                                ?>
                            </small>
                        </div>
                        <?php if ($index < count($progress_steps) - 1): ?>
                        <div class="step-connector"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Status Permohonan atau Syarat -->
<div class="row">
    <div class="col-md-8">
        <?php if ($permohonan): ?>
            <!-- Card Status Permohonan Existing -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt mr-2"></i>Status Permohonan
                    </h6>
                    <?php
                    $status_class = '';
                    $status_text = '';
                    switch ($permohonan->status) {
                        case 'submitted':
                        case 'review_pembimbing':
                            $status_class = 'warning';
                            $status_text = 'Menunggu Review';
                            break;
                        case 'approved':
                            $status_class = 'info';
                            $status_text = 'Disetujui - Proses Staf';
                            break;
                        case 'rejected':
                            $status_class = 'danger';
                            $status_text = 'Ditolak';
                            break;
                        case 'surat_ready':
                            $status_class = 'success';
                            $status_text = 'Surat Siap';
                            break;
                        case 'completed':
                            $status_class = 'success';
                            $status_text = 'Selesai';
                            break;
                        default:
                            $status_class = 'secondary';
                            $status_text = 'Draft';
                    }
                    ?>
                    <span class="badge badge-<?= $status_class ?> badge-pill px-3 py-2">
                        <?= $status_text ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Tanggal Pengajuan</strong></td>
                                    <td>: <?= date('d F Y', strtotime($permohonan->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat Penelitian</strong></td>
                                    <td>: <?= $permohonan->tempat_penelitian ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Periode Penelitian</strong></td>
                                    <td>: <?= date('d M', strtotime($permohonan->tanggal_mulai_penelitian)) ?> - <?= date('d M Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Pembimbing</strong></td>
                                    <td>: <?= $permohonan->nama_pembimbing ?></td>
                                </tr>
                                <?php if ($permohonan->tanggal_review_pembimbing): ?>
                                <tr>
                                    <td><strong>Review Pembimbing</strong></td>
                                    <td>: <?= date('d F Y', strtotime($permohonan->tanggal_review_pembimbing)) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($permohonan->tanggal_upload_surat_staf): ?>
                                <tr>
                                    <td><strong>Surat Diterbitkan</strong></td>
                                    <td>: <?= date('d F Y', strtotime($permohonan->tanggal_upload_surat_staf)) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Komentar Pembimbing -->
                    <?php if ($permohonan->komentar_pembimbing): ?>
                    <div class="alert alert-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?> mt-3">
                        <h6><i class="fas fa-comment mr-2"></i>Komentar Pembimbing:</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($permohonan->komentar_pembimbing)) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <a href="<?= base_url('mahasiswa/penelitian/detail/' . $permohonan->id) ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-eye mr-1"></i>Detail Permohonan
                        </a>
                        
                        <?php if (in_array($permohonan->status, ['surat_ready', 'completed']) && $permohonan->file_surat_izin_staf): ?>
                        <a href="<?= base_url('mahasiswa/penelitian/download_surat/' . $permohonan->id) ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-download mr-1"></i>Download Surat
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($permohonan->status == 'rejected'): ?>
                        <a href="<?= base_url('mahasiswa/penelitian/ajukan') ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-redo mr-1"></i>Ajukan Ulang
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Card Cek Syarat -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-check mr-2"></i>Syarat Pengajuan Izin Penelitian
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($eligibility['error']): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= $eligibility['message'] ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($eligibility['requirements'] as $req_key => $requirement): ?>
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            <div class="mr-3">
                                <?php if ($requirement['status'] == 'OK'): ?>
                                    <i class="fas fa-check-circle text-success fa-lg"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger fa-lg"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= ucwords(str_replace('_', ' ', $req_key)) ?></h6>
                                <small class="text-muted"><?= $requirement['detail'] ?></small>
                            </div>
                            <div>
                                <span class="badge badge-<?= $requirement['status'] == 'OK' ? 'success' : 'danger' ?>">
                                    <?= $requirement['count'] ?>/<?= $requirement['required'] ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <?php if ($eligibility['eligible']): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check mr-2"></i>
                                    <strong>Selamat!</strong> Anda memenuhi syarat untuk mengajukan izin penelitian.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Belum Memenuhi Syarat</strong><br>
                                    Pastikan semua syarat terpenuhi sebelum mengajukan permohonan.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar Info -->
    <div class="col-md-4">
        <!-- Info Proposal -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle mr-2"></i>Info Proposal
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Mahasiswa</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= $proposal->nama ?> (<?= $proposal->nim ?>)</td>
                    </tr>
                    <tr>
                        <td><strong>Program Studi</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= $proposal->nama_prodi ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pembimbing</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= $proposal->nama_pembimbing ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Action Card -->
        <?php if ($can_submit): ?>
        <div class="card shadow mb-4 border-left-success">
            <div class="card-body text-center">
                <i class="fas fa-paper-plane fa-3x text-success mb-3"></i>
                <h5 class="text-success">Siap Mengajukan?</h5>
                <p class="text-muted mb-3">Semua syarat sudah terpenuhi. Anda dapat mengajukan permohonan izin penelitian sekarang.</p>
                <a href="<?= base_url('mahasiswa/penelitian/ajukan') ?>" 
                   class="btn btn-success btn-block">
                    <i class="fas fa-plus mr-2"></i>Ajukan Permohonan
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Help Card -->
        <div class="card shadow border-left-info">
            <div class="card-body">
                <h6 class="text-info"><i class="fas fa-question-circle mr-2"></i>Butuh Bantuan?</h6>
                <small class="text-muted">
                    Jika ada kendala dalam proses pengajuan, silakan hubungi:
                    <br><br>
                    <strong>Unit SIPD</strong><br>
                    Email: sipd@stkstjak.ac.id<br>
                    Telp: (021) 123-4567
                </small>
            </div>
        </div>
    </div>
</div>

<!-- CSS untuk Progress Steps -->
<style>
.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 20px 0;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    text-align: center;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-size: 18px;
    z-index: 2;
    position: relative;
}

.step-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}

.step-content small {
    font-size: 12px;
}

.step-connector {
    position: absolute;
    top: 25px;
    left: 50%;
    width: 100%;
    height: 2px;
    z-index: 1;
}

/* Status Colors */
.step-item.pending .step-icon {
    background-color: #f8f9fa;
    border: 2px solid #dee2e6;
    color: #6c757d;
}

.step-item.active .step-icon {
    background-color: #ffc107;
    border: 2px solid #ffc107;
    color: white;
}

.step-item.completed .step-icon {
    background-color: #28a745;
    border: 2px solid #28a745;
    color: white;
}

.step-item.error .step-icon {
    background-color: #dc3545;
    border: 2px solid #dc3545;
    color: white;
}

.step-item.pending .step-connector {
    background-color: #dee2e6;
}

.step-item.completed .step-connector {
    background-color: #28a745;
}

.step-item.active .step-connector {
    background-color: #ffc107;
}

.step-item.error .step-connector {
    background-color: #dc3545;
}

/* Responsive */
@media (max-width: 768px) {
    .progress-steps {
        flex-direction: column;
        align-items: stretch;
    }
    
    .step-item {
        flex-direction: row;
        text-align: left;
        margin-bottom: 20px;
    }
    
    .step-icon {
        margin-right: 15px;
        margin-bottom: 0;
    }
    
    .step-connector {
        display: none;
    }
}
</style>