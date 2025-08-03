<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/index.php
 * SIMPLE VERSION - Dashboard Seminar Skripsi
 */

// Ensure variables are set
$has_existing_seminar = isset($has_existing_seminar) ? $has_existing_seminar : false;
$show_form = isset($show_form) ? $show_form : false;
$show_progress = isset($show_progress) ? $show_progress : false;
$show_eligibility_check = isset($show_eligibility_check) ? $show_eligibility_check : false;
$error = isset($error) ? $error : false;
?>

<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Seminar Skripsi</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            
            <?php if ($error): ?>
                <!-- ERROR STATE -->
                <div class="card border-danger">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h4 class="text-danger">Terjadi Kesalahan</h4>
                        <p class="text-muted"><?= isset($error_message) ? $error_message : 'Terjadi kesalahan sistem' ?></p>
                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
                
            <?php elseif ($has_existing_seminar && $show_progress): ?>
                <!-- EXISTING SEMINAR - SHOW PROGRESS -->
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks mr-2"></i>
                            Progress Seminar Skripsi
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Status: <?= isset($status_text) ? $status_text : 'Dalam Proses' ?></h6>
                        
                        <?php if (isset($progress_percentage)): ?>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?= $progress_percentage ?>%">
                                    <?= $progress_percentage ?>%
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($current_seminar)): ?>
                            <p><strong>ID Seminar:</strong> #<?= $current_seminar->id ?></p>
                            <p><strong>Judul:</strong> <?= $current_seminar->judul ?></p>
                            <p><strong>Status:</strong> <?= ucfirst($current_seminar->status) ?></p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/detail/' . (isset($current_seminar) ? $current_seminar->id : '')) ?>" 
                               class="btn btn-info">
                                <i class="fas fa-eye mr-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($show_form): ?>
                <!-- ELIGIBLE - SHOW FORM -->
                <div class="card border-success">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h4 class="text-success">Memenuhi Syarat Seminar Skripsi</h4>
                        <p class="text-muted mb-4">
                            Selamat! Anda telah memenuhi syarat untuk mengajukan seminar skripsi.
                        </p>
                        
                        <?php if (isset($requirements) && !empty($requirements)): ?>
                            <div class="row mb-4">
                                <div class="col-md-8 offset-md-2">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>✅ Syarat Terpenuhi:</h6>
                                            <?php foreach ($requirements as $req): ?>
                                                <p class="mb-1">
                                                    <i class="fas fa-check text-success mr-2"></i>
                                                    <?= $req['name'] ?>: <?= $req['current'] ?>/<?= $req['required'] ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($action_url) && $action_url): ?>
                            <a href="<?= $action_url ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-plus mr-2"></i>Ajukan Seminar Skripsi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- NOT ELIGIBLE - SHOW REQUIREMENTS -->
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4 class="text-warning">Belum Memenuhi Syarat</h4>
                        <p class="text-muted mb-4">
                            Silakan lengkapi persyaratan di samping terlebih dahulu.
                        </p>
                        
                        <?php if (isset($requirements) && !empty($requirements)): ?>
                            <div class="row mb-4">
                                <div class="col-md-8 offset-md-2">
                                    <div class="card bg-light">
                                        <div class="card-body text-left">
                                            <h6>📋 Status Persyaratan:</h6>
                                            <?php foreach ($requirements as $req): ?>
                                                <p class="mb-2">
                                                    <?php if ($req['met']): ?>
                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle text-danger mr-2"></i>
                                                    <?php endif; ?>
                                                    <?= $req['name'] ?>: <?= $req['current'] ?>/<?= $req['required'] ?>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-warning">
                                <strong>Yang perlu dilengkapi:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
                
            <?php endif; ?>
            
        </div>
    </div>
    
</div>

<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.progress {
    height: 1.5rem;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
}

.fa-3x {
    font-size: 3rem;
}
</style>