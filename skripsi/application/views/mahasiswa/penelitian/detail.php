<?php
/**
 * Detail Permohonan Izin Penelitian - Tahap 4 Workflow
 * View untuk tracking status dan detail permohonan
 * 
 * File: application/views/mahasiswa/penelitian/detail.php
 */
?>

<style>
.progress-tracker {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.progress-step {
    position: relative;
    padding: 1rem 0;
}

.progress-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 1.5rem;
    top: 3rem;
    width: 2px;
    height: 2rem;
    background: #dee2e6;
}

.progress-step.completed::after {
    background: #28a745;
}

.progress-step.active::after {
    background: #007bff;
}

.step-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 1rem;
    flex-shrink: 0;
}

.step-icon.completed {
    background: #28a745;
    color: white;
}

.step-icon.active {
    background: #007bff;
    color: white;
    animation: pulse 2s infinite;
}

.step-icon.pending {
    background: #dee2e6;
    color: #6c757d;
}

.step-icon.rejected {
    background: #dc3545;
    color: white;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.info-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
}

.status-submitted { background: #fff3cd; color: #856404; }
.status-review_pembimbing { background: #cce5ff; color: #004085; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-completed { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

.download-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 12px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    color: white;
}

.back-btn {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
    color: white;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-left: 4rem;
}

.progress-bar-wrapper {
    background: #e9ecef;
    height: 10px;
    border-radius: 50px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 50px;
    transition: width 0.3s ease;
}
</style>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list mr-2 text-primary"></i>
            Detail Izin Penelitian
        </h1>
        <p class="text-muted mb-0">Tracking status permohonan izin penelitian</p>
    </div>
    <div>
        <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn back-btn">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
    </div>
</div>

<!-- Progress Tracker -->
<div class="progress-tracker">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 font-weight-bold">Progress Penelitian</h4>
            <p class="mb-0 opacity-75">Status: <?= ucfirst(str_replace('_', ' ', $permohonan->status)) ?></p>
        </div>
        <div class="text-right">
            <div class="progress-bar-wrapper" style="width: 200px;">
                <div class="progress-bar-fill" style="width: <?= $progress['progress_percentage'] ?>%;"></div>
            </div>
            <small class="mt-1 d-block"><?= $progress['progress_percentage'] ?>% Selesai</small>
        </div>
    </div>

    <!-- Timeline -->
    <div class="timeline">
        <?php foreach ($progress['steps'] as $index => $step): ?>
            <div class="progress-step <?= $step['status'] ?>">
                <div class="d-flex align-items-start">
                    <div class="step-icon <?= $step['status'] ?>">
                        <?php if ($step['status'] == 'completed'): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif ($step['status'] == 'active'): ?>
                            <i class="fas fa-spinner fa-spin"></i>
                        <?php elseif ($step['status'] == 'rejected'): ?>
                            <i class="fas fa-times"></i>
                        <?php else: ?>
                            <?= $index + 1 ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-grow-1">
                        <h6 class="mb-1 font-weight-bold"><?= $step['name'] ?></h6>
                        <p class="mb-1 text-light"><?= $step['description'] ?></p>
                        <?php if ($step['date']): ?>
                            <small class="opacity-75">
                                <i class="fas fa-clock mr-1"></i>
                                <?= date('d/m/Y H:i', strtotime($step['date'])) ?> WIT
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Detail Information -->
<div class="row">
    <!-- Informasi Permohonan -->
    <div class="col-lg-8">
        <div class="card info-card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Permohonan
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="font-weight-bold" width="40%">Nama Mahasiswa:</td>
                                <td><?= htmlspecialchars($permohonan->nama_mahasiswa) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">NIM:</td>
                                <td><?= htmlspecialchars($permohonan->nim) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Semester:</td>
                                <td><?= htmlspecialchars($permohonan->semester) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Program Studi:</td>
                                <td><?= htmlspecialchars($permohonan->program_studi) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="font-weight-bold" width="40%">Tanggal Pengajuan:</td>
                                <td><?= date('d/m/Y H:i', strtotime($permohonan->created_at)) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Dosen Pembimbing:</td>
                                <td><?= htmlspecialchars($permohonan->nama_pembimbing) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Status Pembimbing:</td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($permohonan->status_pembimbing) {
                                        case 'pending':
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Review';
                                            break;
                                        case 'approved':
                                            $status_class = 'success';
                                            $status_text = 'Disetujui';
                                            break;
                                        case 'rejected':
                                            $status_class = 'danger';
                                            $status_text = 'Ditolak';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Status Permohonan:</td>
                                <td>
                                    <span class="status-badge status-<?= $permohonan->status ?>">
                                        <?php
                                        switch ($permohonan->status) {
                                            case 'submitted': echo 'Diajukan'; break;
                                            case 'review_pembimbing': echo 'Review Pembimbing'; break;
                                            case 'approved': echo 'Menunggu Staf'; break;
                                            case 'completed': echo 'Selesai'; break;
                                            case 'rejected': echo 'Ditolak'; break;
                                            default: echo ucfirst($permohonan->status);
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Penelitian -->
        <div class="card info-card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-search mr-2"></i>
                    Detail Penelitian
                </h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold">Judul Skripsi:</label>
                    <p class="text-justify"><?= htmlspecialchars($permohonan->judul_skripsi_terbaru) ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Tempat Penelitian:</label>
                            <p>
                                <i class="fas fa-map-marker-alt text-muted mr-2"></i>
                                <?= htmlspecialchars($permohonan->tempat_penelitian) ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Periode Penelitian:</label>
                            <p>
                                <i class="fas fa-calendar-alt text-muted mr-2"></i>
                                <?= date('d/m/Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?> - 
                                <?= date('d/m/Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($permohonan->file_proposal_revisi)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">File Proposal Revisi:</label>
                        <p>
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                            <?= htmlspecialchars($permohonan->file_proposal_revisi) ?>
                            <small class="text-muted">(Diupload: <?= date('d/m/Y H:i', strtotime($permohonan->created_at)) ?>)</small>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Komentar/Feedback -->
        <?php if (!empty($permohonan->komentar_pembimbing)): ?>
            <div class="card info-card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-comment mr-2"></i>
                        Komentar Dosen Pembimbing
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-sm rounded-circle bg-warning text-white d-flex align-items-center justify-content-center">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ml-3">
                            <h6 class="mb-1"><?= htmlspecialchars($permohonan->nama_pembimbing) ?></h6>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($permohonan->komentar_pembimbing)) ?></p>
                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?> WIT
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Download Section -->
        <?php if ($permohonan->status == 'completed' && !empty($permohonan->file_surat_izin_staf)): ?>
            <div class="card info-card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-download mr-2"></i>
                        Download Surat
                    </h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-file-pdf fa-3x text-success mb-3"></i>
                    <h6 class="font-weight-bold">Surat Izin Penelitian</h6>
                    <p class="text-muted mb-3">
                        Surat izin penelitian sudah siap untuk didownload
                    </p>
                    <a href="<?= base_url('mahasiswa/penelitian/download_surat/' . $permohonan->id) ?>" 
                       class="btn download-btn btn-block">
                        <i class="fas fa-download mr-2"></i>
                        Download Surat Izin
                    </a>
                    <small class="text-muted mt-2 d-block">
                        Diupload: <?= date('d/m/Y H:i', strtotime($permohonan->tanggal_upload_surat_staf)) ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Information -->
        <div class="card info-card mb-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Status
                </h6>
            </div>
            <div class="card-body">
                <?php if ($permohonan->status == 'submitted' || $permohonan->status == 'review_pembimbing'): ?>
                    <div class="text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                        <h6 class="font-weight-bold">Menunggu Review</h6>
                        <p class="text-muted mb-0">
                            Permohonan Anda sedang direview oleh dosen pembimbing. 
                            Mohon tunggu konfirmasi selanjutnya.
                        </p>
                    </div>
                <?php elseif ($permohonan->status == 'approved'): ?>
                    <div class="text-center">
                        <i class="fas fa-cogs fa-2x text-info mb-3"></i>
                        <h6 class="font-weight-bold">Sedang Diproses</h6>
                        <p class="text-muted mb-0">
                            Permohonan telah disetujui pembimbing dan sedang diproses 
                            oleh staf untuk pembuatan surat izin.
                        </p>
                    </div>
                <?php elseif ($permohonan->status == 'rejected'): ?>
                    <div class="text-center">
                        <i class="fas fa-times-circle fa-2x text-danger mb-3"></i>
                        <h6 class="font-weight-bold">Ditolak</h6>
                        <p class="text-muted mb-3">
                            Permohonan ditolak oleh dosen pembimbing. 
                            Silakan periksa komentar dan perbaiki.
                        </p>
                        <a href="<?= base_url('mahasiswa/penelitian/ajukan/' . $permohonan->proposal_mahasiswa_id) ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-redo mr-1"></i>
                            Ajukan Ulang
                        </a>
                    </div>
                <?php elseif ($permohonan->status == 'completed'): ?>
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <h6 class="font-weight-bold">Selesai</h6>
                        <p class="text-muted mb-0">
                            Surat izin penelitian telah selesai dan siap digunakan 
                            untuk keperluan penelitian Anda.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card info-card">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-bolt mr-2"></i>
                    Aksi Cepat
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('mahasiswa/penelitian') ?>" 
                       class="btn btn-outline-primary btn-sm btn-block">
                        <i class="fas fa-list mr-2"></i>
                        Lihat Semua Permohonan
                    </a>
                    
                    <?php if ($permohonan->status != 'completed'): ?>
                        <button class="btn btn-outline-info btn-sm btn-block" 
                                onclick="location.reload()">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh Status
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('mahasiswa/bimbingan') ?>" 
                       class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-book mr-2"></i>
                        Jurnal Bimbingan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh untuk status yang masih pending (setiap 30 detik)
<?php if (in_array($permohonan->status, ['submitted', 'review_pembimbing', 'approved'])): ?>
    setInterval(function() {
        // Check for status update (bisa implementasi AJAX jika diperlukan)
        console.log('Checking status update...');
    }, 30000);
<?php endif; ?>

// Animasi progress bar saat load
$(document).ready(function() {
    $('.progress-bar-fill').css('width', '0%');
    setTimeout(function() {
        $('.progress-bar-fill').css('width', '<?= $progress['progress_percentage'] ?>%');
    }, 500);
});
</script>