<?php
// =================================================================
// File: application/views/mahasiswa/seminar_proposal/dashboard.php
// =================================================================
?>

<!-- Dashboard Seminar Proposal Mahasiswa -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Header stats -->
            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Status Proposal</h5>
                                    <span class="h2 font-weight-bold mb-0">
                                        <?= ucfirst($proposal->workflow_status) ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                        <i class="ni ni-book-bookmark"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Jurnal Validasi</h5>
                                    <span class="h2 font-weight-bold mb-0">
                                        <?= $jurnal_check['jurnal_validated_count'] ?>/8
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-<?= $jurnal_check['eligible'] ? 'success' : 'warning' ?> text-white rounded-circle shadow">
                                        <i class="ni ni-check-bold"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Pembimbing</h5>
                                    <span class="h2 font-weight-bold mb-0 text-truncate" style="font-size: 1rem;">
                                        <?= $proposal->nama_pembimbing ?? 'Belum ditentukan' ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                                        <i class="ni ni-single-02"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Status Seminar</h5>
                                    <span class="h2 font-weight-bold mb-0 text-truncate" style="font-size: 1rem;">
                                        <?php if ($seminar): ?>
                                            <?= format_status_badge($seminar->status, false) ?>
                                        <?php else: ?>
                                            <span class="badge badge-light">Belum Diajukan</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                        <i class="ni ni-hat-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--7">
    <div class="row">
        <!-- Main Content -->
        <div class="col-xl-8 mb-5">
            
            <!-- Workflow Progress Card -->
            <?php if ($seminar && $workflow): ?>
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Progress Seminar Proposal</h3>
                        </div>
                        <div class="col text-right">
                            <span class="badge badge-lg <?= $this->seminar_model->get_status_badge_class($seminar->status) ?>">
                                <?= $seminar->status_description ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                        <?php foreach ($workflow['progress'] as $index => $step): ?>
                        <div class="timeline-block">
                            <span class="timeline-step 
                                <?= $step['completed'] ? 'badge-success' : ($step['active'] ? 'badge-warning' : 'badge-light') ?>">
                                <i class="ni <?= $step['icon'] ?>"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 <?= $step['completed'] ? 'text-success' : ($step['active'] ? 'text-warning' : 'text-muted') ?>">
                                    <?= $step['title'] ?>
                                    <?php if ($step['completed']): ?>
                                        <i class="ni ni-check-bold text-success ml-1"></i>
                                    <?php elseif ($step['active']): ?>
                                        <i class="ni ni-time-alarm text-warning ml-1"></i>
                                    <?php endif; ?>
                                </h6>
                                <p class="text-sm text-muted mb-0"><?= $step['description'] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($workflow['next_action']): ?>
                    <div class="alert alert-info mt-3" role="alert">
                        <strong><i class="ni ni-notification-70"></i> Tindakan Selanjutnya:</strong>
                        <?= $workflow['next_action'] ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">
                        <i class="ni ni-send text-primary"></i>
                        Pengajuan Seminar Proposal
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (!$seminar && $can_submit): ?>
                        <!-- Belum mengajukan & memenuhi syarat -->
                        <div class="text-center">
                            <div class="icon icon-shape icon-shape-success rounded-circle mb-3">
                                <i class="ni ni-check-bold"></i>
                            </div>
                            <h4>Siap Mengajukan Seminar Proposal!</h4>
                            <p class="text-muted">
                                Anda telah memenuhi semua syarat untuk mengajukan seminar proposal.
                                Pastikan proposal Anda sudah final sebelum mengajukan.
                            </p>
                            <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan') ?>" class="btn btn-primary btn-lg">
                                <i class="ni ni-send"></i> Ajukan Seminar Proposal
                            </a>
                        </div>
                    <?php elseif (!$seminar && !$can_submit): ?>
                        <!-- Belum mengajukan & belum memenuhi syarat -->
                        <div class="text-center">
                            <div class="icon icon-shape icon-shape-warning rounded-circle mb-3">
                                <i class="ni ni-notification-70"></i>
                            </div>
                            <h4>Belum Memenuhi Syarat</h4>
                            <p class="text-muted"><?= $jurnal_check['message'] ?></p>
                            
                            <div class="progress-wrapper">
                                <div class="progress-info">
                                    <div class="progress-label">
                                        <span>Progress Jurnal Bimbingan</span>
                                    </div>
                                    <div class="progress-percentage">
                                        <span><?= $jurnal_check['jurnal_validated_count'] ?>/8</span>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?= ($jurnal_check['jurnal_validated_count'] / 8) * 100 ?>%">
                                    </div>
                                </div>
                            </div>
                            
                            <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-warning btn-lg mt-3">
                                <i class="ni ni-books"></i> Lanjutkan Bimbingan
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Sudah mengajukan -->
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Status: <?= format_status_badge($seminar->status) ?></h5>
                                <p class="text-muted"><?= $seminar->status_description ?></p>
                                
                                <?php if ($seminar->file_proposal): ?>
                                <p><strong>File Proposal:</strong> 
                                    <a href="<?= base_url('uploads/seminar_proposal/' . $seminar->file_proposal) ?>" 
                                       target="_blank" class="text-primary">
                                        <i class="ni ni-cloud-download-95"></i> Download
                                    </a>
                                </p>
                                <?php endif; ?>
                                
                                <?php if ($seminar->tanggal_seminar): ?>
                                <div class="alert alert-info">
                                    <strong><i class="ni ni-calendar-grid-58"></i> Jadwal Seminar:</strong><br>
                                    📅 <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?><br>
                                    🕒 <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT<br>
                                    📍 <?= $seminar->tempat_seminar ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('mahasiswa/seminar_proposal/detail/' . $seminar->id) ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="ni ni-bullet-list-67"></i> Detail
                                </a>
                                
                                <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                                <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan') ?>" 
                                   class="btn btn-warning">
                                    <i class="ni ni-settings"></i> Edit
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Info Proposal Card -->
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Informasi Proposal</h3>
                </div>
                <div class="card-body">
                    <h6 class="heading-small text-muted mb-2">DETAIL PROPOSAL</h6>
                    <div class="pl-lg-4">
                        <div class="form-group">
                            <label class="form-control-label">Judul</label>
                            <p class="form-control-alternative"><?= $proposal->judul ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Pembimbing</label>
                            <p class="form-control-alternative">
                                <i class="ni ni-single-02 text-primary"></i>
                                <?= $proposal->nama_pembimbing ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Status Pembimbing</label>
                            <p class="form-control-alternative">
                                <?php if ($proposal->status_pembimbing == '1'): ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Syarat Jurnal Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Syarat Jurnal Bimbingan</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Jurnal Tervalidasi</span>
                        <span class="badge badge-<?= $jurnal_check['eligible'] ? 'success' : 'warning' ?> badge-lg">
                            <?= $jurnal_check['jurnal_validated_count'] ?>/8
                        </span>
                    </div>
                    
                    <div class="progress mb-3">
                        <div class="progress-bar bg-<?= $jurnal_check['eligible'] ? 'success' : 'warning' ?>" 
                             role="progressbar" 
                             style="width: <?= ($jurnal_check['jurnal_validated_count'] / 8) * 100 ?>%">
                        </div>
                    </div>
                    
                    <?php if ($jurnal_check['eligible']): ?>
                        <div class="alert alert-success" role="alert">
                            <strong><i class="ni ni-check-bold"></i> Memenuhi Syarat!</strong><br>
                            Anda dapat mengajukan seminar proposal.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <strong><i class="ni ni-notification-70"></i> Belum Memenuhi Syarat</strong><br>
                            Perlu <?= $jurnal_check['missing'] ?> jurnal lagi yang divalidasi dosen.
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-outline-primary btn-sm btn-block">
                        <i class="ni ni-books"></i> Lihat Jurnal Bimbingan
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="nav-wrapper">
                        <ul class="nav nav-pills nav-fill flex-column" role="tablist">
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/proposal') ?>" class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-book-bookmark mr-2"></i>Proposal Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-books mr-2"></i>Jurnal Bimbingan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/dashboard') ?>" class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-tv-2 mr-2"></i>Dashboard
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>