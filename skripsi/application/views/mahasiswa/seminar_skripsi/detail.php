<?php
/**
 * Detail Seminar Skripsi Mahasiswa (Phase 5)
 * File: application/views/mahasiswa/seminar_skripsi/detail.php
 * 
 * View untuk menampilkan detail pengajuan seminar skripsi dan progress tracking
 * Menggunakan struktur yang konsisten dengan detail seminar proposal
 */
?>

<style>
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
    .status-badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 50px;
    }
    .progress-container {
        position: relative;
        margin-bottom: 2rem;
    }
    .progress {
        height: 8px;
        border-radius: 10px;
        background-color: #e9ecef;
    }
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }
    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
    }
    .progress-step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 auto 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 3px solid #e9ecef;
        background-color: white;
        color: #6c757d;
        position: relative;
        z-index: 2;
    }
    .step-circle.completed {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    .step-circle.active {
        background-color: #667eea;
        border-color: #667eea;
        color: white;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
        100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
    }
    .step-title {
        font-size: 0.8rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }
    .step-description {
        font-size: 0.7rem;
        color: #6c757d;
    }
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 1rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0.5rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #667eea;
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 1.25rem;
        width: 2px;
        height: calc(100% - 0.5rem);
        background-color: #e9ecef;
    }
    .timeline-item:last-child::after {
        display: none;
    }
    .document-preview {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        background-color: #f8f9fa;
        text-align: center;
    }
    .btn-action {
        margin: 0.25rem;
    }
</style>

<!-- Content untuk template mahasiswa -->
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Detail Seminar Skripsi
        </h1>
        <div>
            <?php if ($can_edit): ?>
                <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                   class="btn btn-warning mr-2">
                    <i class="fas fa-edit mr-1"></i> Edit Pengajuan
                </a>
            <?php endif; ?>
            <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Main Content -->
        <div class="col-lg-8">
            
            <!-- Progress Tracking -->
            <div class="card shadow mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line mr-2"></i>
                        Progress Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: 0%;" 
                                 aria-valuenow="<?= $progress['percentage'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="progress-steps">
                            <?php foreach ($progress['steps'] as $step): ?>
                                <div class="progress-step">
                                    <div class="step-circle <?= $step['completed'] ? 'completed' : ($step['active'] ? 'active' : '') ?>">
                                        <i class="fas <?= $step['icon'] ?>"></i>
                                    </div>
                                    <div class="step-title"><?= $step['title'] ?></div>
                                    <div class="step-description"><?= $step['description'] ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <h5 class="mb-2">
                            <span class="badge badge-primary status-badge">
                                Progress: <?= $progress['percentage'] ?>%
                            </span>
                        </h5>
                        <p class="text-muted mb-0">
                            Status saat ini: <strong><?= ucfirst(str_replace('_', ' ', $seminar->status)) ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informasi Seminar -->
            <div class="card shadow mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>ID Seminar:</strong></td>
                                    <td><?= $seminar->id ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pengajuan:</strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        switch ($seminar->status) {
                                            case 'submitted':
                                            case 'review_pembimbing':
                                                $status_class = 'warning';
                                                break;
                                            case 'review_kaprodi':
                                                $status_class = 'info';
                                                break;
                                            case 'approved':
                                                $status_class = 'primary';
                                                break;
                                            case 'scheduled':
                                            case 'completed':
                                                $status_class = 'success';
                                                break;
                                            case 'rejected':
                                                $status_class = 'danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-<?= $status_class ?>">
                                            <?= $seminar->status_description ?? ucfirst(str_replace('_', ' ', $seminar->status)) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Current Step:</strong></td>
                                    <td><?= ucfirst($seminar->current_step) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Mahasiswa:</strong></td>
                                    <td><?= $seminar->nama_mahasiswa ?> (<?= $seminar->nim ?>)</td>
                                </tr>
                                <tr>
                                    <td><strong>Pembimbing:</strong></td>
                                    <td><?= $seminar->nama_pembimbing ?></td>
                                </tr>
                                <?php if (!empty($seminar->tanggal_seminar)): ?>
                                <tr>
                                    <td><strong>Tanggal Seminar:</strong></td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                        <?php if (!empty($seminar->jam_seminar)): ?>
                                            <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($seminar->tempat_seminar)): ?>
                                <tr>
                                    <td><strong>Tempat:</strong></td>
                                    <td><?= $seminar->tempat_seminar ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Judul dan Keterangan -->
            <div class="card shadow mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-text mr-2"></i>
                        Detail Proposal & Keterangan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Judul Skripsi:</h6>
                        <p class="text-justify"><?= $seminar->judul ?></p>
                    </div>
                    
                    <?php if (!empty($seminar->keterangan_mahasiswa)): ?>
                        <div class="mb-3">
                            <h6 class="font-weight-bold">Keterangan Mahasiswa:</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0 text-justify"><?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- File Skripsi -->
            <?php if (!empty($seminar->file_skripsi)): ?>
            <div class="card shadow mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-pdf mr-2"></i>
                        File Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="document-preview">
                        <i class="fas fa-file-alt fa-4x text-primary mb-3"></i>
                        <h6><?= $seminar->file_skripsi ?></h6>
                        <p class="text-muted mb-3">File skripsi yang telah diupload</p>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-download mr-2"></i>Download File
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feedback/Comments -->
            <?php if (!empty($seminar->komentar_pembimbing) || !empty($seminar->komentar_kaprodi)): ?>
            <div class="card shadow mb-4">
                <div class="card-header card-header-custom">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-comments mr-2"></i>
                        Komentar & Feedback
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($seminar->komentar_pembimbing)): ?>
                        <div class="mb-3">
                            <h6 class="font-weight-bold text-primary">
                                <i class="fas fa-user-tie mr-2"></i>
                                Komentar Dosen Pembimbing:
                            </h6>
                            <div class="alert alert-info">
                                <?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?>
                            </div>
                            <?php if (!empty($seminar->tanggal_review_pembimbing)): ?>
                                <small class="text-muted">
                                    Direview pada: <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->komentar_kaprodi)): ?>
                        <div class="mb-3">
                            <h6 class="font-weight-bold text-success">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Komentar Kaprodi:
                            </h6>
                            <div class="alert alert-success">
                                <?= nl2br(htmlspecialchars($seminar->komentar_kaprodi)) ?>
                            </div>
                            <?php if (!empty($seminar->tanggal_review_kaprodi)): ?>
                                <small class="text-muted">
                                    Direview pada: <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Turnitin Information -->
            <?php if (!empty($seminar->plagiarism_percentage)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-search mr-2"></i>
                        Hasil Turnitin Check
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5>Persentase Plagiarisme: 
                                <span class="badge badge-<?= $seminar->plagiarism_percentage <= 30 ? 'success' : 'danger' ?> status-badge">
                                    <?= $seminar->plagiarism_percentage ?>%
                                </span>
                            </h5>
                            <p class="mb-0">
                                <?php if ($seminar->plagiarism_percentage <= 30): ?>
                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                    Memenuhi syarat (≤30%)
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                    Melebihi batas maksimal (>30%)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="progress-circle" style="--progress: <?= $seminar->plagiarism_percentage ?>%;">
                                <div class="progress-text"><?= $seminar->plagiarism_percentage ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt mr-2"></i>
                        Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($can_edit): ?>
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                               class="btn btn-warning btn-action">
                                <i class="fas fa-edit mr-2"></i>Edit Pengajuan
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($seminar->file_skripsi)): ?>
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                               class="btn btn-success btn-action" target="_blank">
                                <i class="fas fa-download mr-2"></i>Download File
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($can_resubmit): ?>
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                               class="btn btn-primary btn-action">
                                <i class="fas fa-redo mr-2"></i>Ajukan Ulang
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                           class="btn btn-secondary btn-action">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dosen Penguji -->
            <?php if (!empty($seminar->nama_penguji1) || !empty($seminar->nama_penguji2)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-users mr-2"></i>
                        Dosen Penguji
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($seminar->nama_penguji1)): ?>
                        <div class="mb-2">
                            <strong>Penguji 1:</strong><br>
                            <?= $seminar->nama_penguji1 ?>
                            <?php if (!empty($seminar->nip_penguji1)): ?>
                                <br><small class="text-muted">NIP: <?= $seminar->nip_penguji1 ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->nama_penguji2)): ?>
                        <div class="mb-2">
                            <strong>Penguji 2:</strong><br>
                            <?= $seminar->nama_penguji2 ?>
                            <?php if (!empty($seminar->nip_penguji2)): ?>
                                <br><small class="text-muted">NIP: <?= $seminar->nip_penguji2 ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline Status -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-history mr-2"></i>
                        Timeline Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline-item">
                        <strong>Pengajuan Dibuat</strong>
                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></small>
                    </div>
                    
                    <?php if (!empty($seminar->tanggal_review_pembimbing)): ?>
                    <div class="timeline-item">
                        <strong>Review Pembimbing</strong>
                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?></small>
                        <br><span class="badge badge-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?> badge-sm">
                            <?= ucfirst($seminar->status_pembimbing) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->tanggal_review_kaprodi)): ?>
                    <div class="timeline-item">
                        <strong>Review Kaprodi</strong>
                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) ?></small>
                        <br><span class="badge badge-<?= $seminar->status_kaprodi == 'approved' ? 'success' : 'danger' ?> badge-sm">
                            <?= ucfirst($seminar->status_kaprodi) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->tanggal_seminar)): ?>
                    <div class="timeline-item">
                        <strong>Seminar Dijadwalkan</strong>
                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($seminar->tanggal_seminar)) ?></small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($seminar->status == 'completed'): ?>
                    <div class="timeline-item">
                        <strong>Seminar Selesai</strong>
                        <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($seminar->updated_at)) ?></small>
                        <br><span class="badge badge-success badge-sm">Completed</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Help & Contact -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-question-circle mr-2"></i>
                        Butuh Bantuan?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Jika ada pertanyaan atau kendala, silakan hubungi:</p>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-user-tie text-primary mr-2"></i>Dosen Pembimbing</li>
                        <li><i class="fas fa-envelope text-info mr-2"></i>Admin Prodi</li>
                        <li><i class="fas fa-phone text-success mr-2"></i>Unit SIPD</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->