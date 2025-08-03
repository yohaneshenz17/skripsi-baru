<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/index.php
 * ENHANCED VERSION - Dashboard Seminar Skripsi dengan Tombol Lihat Penilaian
 * 
 * PERBAIKAN:
 * ✅ Fix error "Call to undefined method CI_Loader::_build_simple_progress()"
 * ✅ Tambah tombol "Lihat Penilaian" ketika penilaian sudah published
 * ✅ Tambah tombol "Ajukan Ulang" untuk resubmission
 * ✅ Tambah tombol "Lanjut ke Publikasi" ketika memenuhi syarat
 * ✅ Keep existing structure stable
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
        
        <?php if ($error): ?>
            <!-- ERROR STATE -->
            <div class="col-12">
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
            </div>
                
        <?php elseif ($has_existing_seminar && $show_progress): ?>
            <!-- EXISTING SEMINAR - SHOW PROGRESS -->
            <div class="col-lg-8">
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
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     style="width: <?= $progress_percentage ?>%">
                                    <?= $progress_percentage ?>%
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($current_seminar)): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID Seminar:</strong> #<?= $current_seminar->id ?></p>
                                    <p><strong>Judul:</strong> <?= character_limiter($current_seminar->judul, 50) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <?php if ($current_seminar->status == 'completed'): ?>
                                            <span class="badge badge-success">Selesai</span>
                                        <?php elseif ($current_seminar->status == 'rejected'): ?>
                                            <span class="badge badge-danger">Ditolak</span>
                                        <?php elseif ($current_seminar->status == 'submitted'): ?>
                                            <span class="badge badge-warning">Menunggu Review</span>
                                        <?php else: ?>
                                            <span class="badge badge-info"><?= ucfirst($current_seminar->status) ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Tanggal Pengajuan:</strong> <?= date('d/m/Y', strtotime($current_seminar->created_at)) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ✅ ENHANCED: Progress Steps (FIXED - tidak panggil method dari view) -->
                        <?php if (isset($current_seminar)): ?>
                        <div class="mt-4">
                            <h6 class="text-muted border-bottom pb-2">Progress Timeline</h6>
                            <div class="row">
                                <div class="col-md-2 text-center mb-2">
                                    <div class="step-icon bg-success text-white mb-1">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <small class="text-muted">Pengajuan</small>
                                </div>
                                <div class="col-md-2 text-center mb-2">
                                    <div class="step-icon <?= in_array($current_seminar->status_pembimbing, ['approved']) ? 'bg-success' : ($current_seminar->status_pembimbing == 'rejected' ? 'bg-danger' : 'bg-warning') ?> text-white mb-1">
                                        <i class="fas <?= in_array($current_seminar->status_pembimbing, ['approved']) ? 'fa-check' : ($current_seminar->status_pembimbing == 'rejected' ? 'fa-times' : 'fa-clock') ?>"></i>
                                    </div>
                                    <small class="text-muted">Review Pembimbing</small>
                                </div>
                                <div class="col-md-2 text-center mb-2">
                                    <div class="step-icon <?= $current_seminar->status_kaprodi == 'approved' ? 'bg-success' : ($current_seminar->status_kaprodi == 'rejected' ? 'bg-danger' : 'bg-secondary') ?> text-white mb-1">
                                        <i class="fas <?= $current_seminar->status_kaprodi == 'approved' ? 'fa-check' : ($current_seminar->status_kaprodi == 'rejected' ? 'fa-times' : 'fa-circle') ?>"></i>
                                    </div>
                                    <small class="text-muted">Validasi Kaprodi</small>
                                </div>
                                <div class="col-md-2 text-center mb-2">
                                    <div class="step-icon <?= $current_seminar->status == 'scheduled' ? 'bg-success' : 'bg-secondary' ?> text-white mb-1">
                                        <i class="fas <?= $current_seminar->status == 'scheduled' ? 'fa-calendar' : 'fa-circle' ?>"></i>
                                    </div>
                                    <small class="text-muted">Penjadwalan</small>
                                </div>
                                <div class="col-md-2 text-center mb-2">
                                    <div class="step-icon <?= $current_seminar->status == 'completed' ? 'bg-success' : 'bg-secondary' ?> text-white mb-1">
                                        <i class="fas <?= $current_seminar->status == 'completed' ? 'fa-graduation-cap' : 'fa-circle' ?>"></i>
                                    </div>
                                    <small class="text-muted">Pelaksanaan</small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Komentar/Feedback jika ada -->
                        <?php if (isset($current_seminar) && (!empty($current_seminar->komentar_pembimbing) || !empty($current_seminar->komentar_kaprodi))): ?>
                        <div class="mt-4">
                            <h6 class="text-muted border-bottom pb-2">Catatan & Feedback</h6>
                            <?php if (!empty($current_seminar->komentar_pembimbing)): ?>
                            <div class="alert alert-info">
                                <strong><i class="fas fa-user mr-2"></i>Dosen Pembimbing:</strong><br>
                                <?= nl2br(htmlspecialchars($current_seminar->komentar_pembimbing)) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($current_seminar->komentar_kaprodi)): ?>
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-user-tie mr-2"></i>Kaprodi:</strong><br>
                                <?= nl2br(htmlspecialchars($current_seminar->komentar_kaprodi)) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ✅ NEW: Action Sidebar -->
            <div class="col-lg-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-tools mr-2"></i>
                            Aksi Tersedia
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- ✅ NEW: Tombol Lihat Penilaian -->
                        <?php if (isset($can_view_penilaian) && $can_view_penilaian && !empty($published_penilaian)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/view_penilaian/' . $current_seminar->id) ?>" 
                           class="btn btn-success btn-block mb-3"
                           title="Lihat hasil penilaian seminar skripsi">
                            <i class="fas fa-star mr-2"></i>
                            Lihat Penilaian
                        </a>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Penilaian dipublikasikan: <?= date('d/m/Y H:i', strtotime($published_penilaian->published_at)) ?>
                        </small>
                        <?php elseif (isset($current_seminar) && $current_seminar->status == 'completed'): ?>
                        <!-- Tombol disabled jika penilaian belum tersedia -->
                        <button class="btn btn-outline-secondary btn-block mb-3" 
                                disabled
                                title="Penilaian belum tersedia atau belum dipublikasikan">
                            <i class="fas fa-clock mr-2"></i>
                            Penilaian Belum Tersedia
                        </button>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Penilaian akan muncul setelah dosen menginput dan mempublikasikan nilai
                        </small>
                        <?php endif; ?>

                        <!-- ✅ NEW: Tombol Lanjut ke Publikasi -->
                        <?php if (isset($can_proceed_publikasi) && $can_proceed_publikasi): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/proceed_to_publikasi') ?>" 
                           class="btn btn-warning btn-block mb-3"
                           onclick="return confirm('Yakin ingin melanjutkan ke tahap publikasi tugas akhir?')"
                           title="Lanjut ke tahap publikasi tugas akhir">
                            <i class="fas fa-arrow-right mr-2"></i>
                            Lanjut ke Publikasi
                        </a>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-graduation-cap text-warning mr-1"></i>
                            Anda telah lulus seminar skripsi!
                        </small>
                        <?php endif; ?>

                        <!-- ✅ NEW: Tombol Ajukan Ulang -->
                        <?php if (isset($can_resubmit) && $can_resubmit): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/resubmit/' . $current_seminar->id) ?>" 
                           class="btn btn-info btn-block mb-3"
                           title="Ajukan ulang setelah perbaikan">
                            <i class="fas fa-redo mr-2"></i>
                            Ajukan Ulang
                        </a>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-exclamation-triangle text-info mr-1"></i>
                            Lakukan perbaikan sesuai komentar dosen/kaprodi
                        </small>
                        <?php endif; ?>

                        <!-- Detail Seminar (EXISTING - STABLE) -->
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/detail/' . (isset($current_seminar) ? $current_seminar->id : '')) ?>" 
                           class="btn btn-info btn-block mb-3">
                            <i class="fas fa-eye mr-2"></i>Lihat Detail
                        </a>

                        <!-- Download File -->
                        <?php if (isset($current_seminar) && !empty($current_seminar->file_skripsi)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $current_seminar->id) ?>" 
                           class="btn btn-outline-secondary btn-block mb-3"
                           title="Download file skripsi">
                            <i class="fas fa-download mr-2"></i>
                            Download File
                        </a>
                        <?php endif; ?>

                        <!-- Kembali (EXISTING - STABLE) -->
                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>

                <!-- ✅ NEW: Info Progress Card -->
                <div class="card border-left-info shadow mt-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tahap Seminar Skripsi
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    Phase 5 dari 6
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            Setelah lulus seminar skripsi, Anda dapat melanjutkan ke tahap publikasi tugas akhir.
                        </small>
                    </div>
                </div>
            </div>
                
        <?php elseif ($show_form): ?>
            <!-- ELIGIBLE - SHOW FORM -->
            <div class="col-12">
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
            </div>
                
        <?php else: ?>
            <!-- NOT ELIGIBLE - SHOW REQUIREMENTS -->
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4 class="text-warning">Belum Memenuhi Syarat</h4>
                        <p class="text-muted mb-4">
                            Silakan lengkapi persyaratan di bawah terlebih dahulu.
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
                        
                        <!-- Quick Actions -->
                        <div class="mt-4">
                            <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-primary mr-2">
                                <i class="fas fa-comments mr-2"></i>
                                Jurnal Bimbingan
                            </a>
                            <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-info mr-2">
                                <i class="fas fa-search mr-2"></i>
                                Izin Penelitian
                            </a>
                            <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
                
        <?php endif; ?>
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

.progress-bar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
}

.fa-3x {
    font-size: 3rem;
}

/* ✅ NEW: Step progress styles */
.step-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 14px;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

/* Button hover effects */
.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-md-2 {
        flex: 0 0 20%;
        max-width: 20%;
    }
    
    .step-icon {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}
</style>