<?php
// Mulai output buffering
ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card bg-gradient-info text-white">
            <div class="card-body text-center">
                <h4 class="text-white mb-0">
                    <i class="fas fa-route mr-2"></i>
                    <strong>Tracking Publikasi:</strong> 9 Langkah Workflow Tugas Akhir
                </h4>
                <p class="mb-0 text-white-75">
                    <?php if ($publikasi->status == 'completed'): ?>
                        🎉 <strong>Publikasi Selesai!</strong> Semua tahap telah diselesaikan oleh mahasiswa.
                    <?php else: ?>
                        Monitoring progress publikasi mahasiswa: <strong><?= $publikasi->nama_mahasiswa ?></strong> 
                        (Step <?= $step_current ?> dari 9 langkah)
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="row mb-4">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi') ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/publikasi') ?>">Publikasi</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/publikasi/detail/' . $publikasi->id) ?>">Detail</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Tracking</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- Main Tracking -->
    <div class="col-lg-8">
        <!-- Workflow Visual -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-tasks"></i> Progress Workflow - 9 Langkah
                </h3>
            </div>
            <div class="card-body">
                
                <!-- Progress Bar Overview -->
                <div class="progress mb-4" style="height: 25px;">
                    <?php 
                    $progress_percentage = 11; // Default step 1
                    
                    // Calculate progress berdasarkan step_current
                    $progress_percentage = ($step_current / 9) * 100;
                    $progress_color = $publikasi->status == 'completed' ? 'success' : 
                                    ($publikasi->status == 'rejected' ? 'danger' : 'info');
                    ?>
                    <div class="progress-bar bg-<?= $progress_color ?> progress-bar-striped" 
                         role="progressbar" 
                         style="width: <?= $progress_percentage ?>%"
                         aria-valuenow="<?= $progress_percentage ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        <strong><?= round($progress_percentage) ?>%</strong>
                    </div>
                </div>

                <!-- 9 Langkah Detail -->
                <div class="workflow-steps">
                    
                    <!-- Step 1: Memenuhi Syarat -->
                    <div class="step-item <?= $step_current >= 1 ? 'completed' : '' ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 1 ? 'bg-success' : 'bg-secondary' ?>">
                                    <i class="fas <?= $step_current >= 1 ? 'fa-check' : 'fa-user-graduate' ?>"></i>
                                    <span class="step-number">1</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Mahasiswa Memenuhi Syarat</h6>
                                <p class="step-desc text-muted">16+ jurnal bimbingan tervalidasi dan proposal disetujui</p>
                                <?php if ($step_current >= 1): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Syarat terpenuhi</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 1): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Belum</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Isi Form -->
                    <div class="step-item <?= $step_current >= 2 ? 'completed' : ($step_current == 1 ? 'current' : '') ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 2 ? 'bg-success' : ($step_current == 1 ? 'bg-primary' : 'bg-secondary') ?>">
                                    <i class="fas <?= $step_current >= 2 ? 'fa-check' : 'fa-edit' ?>"></i>
                                    <span class="step-number">2</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Isi Form Pengajuan</h6>
                                <p class="step-desc text-muted">Upload file skripsi final dan dokumen persyaratan</p>
                                <?php if ($step_current >= 2): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Form telah diisi</small>
                                <?php elseif ($step_current == 1): ?>
                                    <small class="text-primary"><i class="fa fa-clock"></i> Sedang mengisi form</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 2): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif ($step_current == 1): ?>
                                    <span class="badge badge-primary">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Kirim Ajuan -->
                    <div class="step-item <?= $step_current >= 3 ? 'completed' : ($step_current == 2 ? 'current' : '') ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 3 ? 'bg-success' : ($step_current == 2 ? 'bg-primary' : 'bg-secondary') ?>">
                                    <i class="fas <?= $step_current >= 3 ? 'fa-check' : 'fa-paper-plane' ?>"></i>
                                    <span class="step-number">3</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Kirim Ajuan ke Dosen</h6>
                                <p class="step-desc text-muted">Submit pengajuan publikasi ke dosen pembimbing</p>
                                <?php if ($step_current >= 3): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Ajuan telah dikirim</small>
                                    <?php if (!empty($publikasi->tanggal_pengajuan)): ?>
                                        <small class="text-muted d-block">
                                            Tanggal: <?= date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 3): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif ($step_current == 2): ?>
                                    <span class="badge badge-primary">Siap Kirim</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4-6: Proses Dosen -->
                    <div class="step-item <?= $step_current >= 6 ? 'completed' : (in_array($step_current, [4, 5]) ? 'current' : '') ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 6 ? 'bg-success' : (in_array($step_current, [4, 5]) ? 'bg-warning' : 'bg-secondary') ?>">
                                    <i class="fas <?= $step_current >= 6 ? 'fa-check' : 'fa-user-tie' ?>"></i>
                                    <span class="step-number">4-6</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Review Dosen Pembimbing</h6>
                                <p class="step-desc text-muted">Dosen melakukan review dan memberikan keputusan</p>
                                <?php if ($step_current >= 6): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Dosen telah menyetujui</small>
                                <?php elseif (in_array($step_current, [4, 5])): ?>
                                    <small class="text-warning"><i class="fa fa-clock"></i> Sedang direview dosen</small>
                                <?php endif; ?>
                                
                                <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Komentar dosen:</small>
                                        <div class="alert alert-info alert-sm mt-1">
                                            <?= nl2br(substr($publikasi->komentar_pembimbing, 0, 150)) ?>
                                            <?= strlen($publikasi->komentar_pembimbing) > 150 ? '...' : '' ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 6): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif (in_array($step_current, [4, 5])): ?>
                                    <span class="badge badge-warning">Review</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 7: Validasi Staf -->
                    <div class="step-item <?= $step_current >= 8 ? 'completed' : ($step_current == 7 ? 'current' : '') ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 8 ? 'bg-success' : ($step_current == 7 ? 'bg-info' : 'bg-secondary') ?>">
                                    <i class="fas <?= $step_current >= 8 ? 'fa-check' : 'fa-user-cog' ?>"></i>
                                    <span class="step-number">7</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Validasi Staf</h6>
                                <p class="step-desc text-muted">Staf memvalidasi dan input link repository</p>
                                <?php if ($step_current >= 8): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Staf telah memvalidasi</small>
                                    <?php if (!empty($publikasi->link_repository)): ?>
                                        <small class="text-muted d-block">
                                            Repository: <a href="<?= $publikasi->link_repository ?>" target="_blank">Lihat</a>
                                        </small>
                                    <?php endif; ?>
                                <?php elseif ($step_current == 7): ?>
                                    <small class="text-info"><i class="fa fa-clock"></i> Sedang divalidasi staf</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 8): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif ($step_current == 7): ?>
                                    <span class="badge badge-info">Proses</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 8-9: Selesai -->
                    <div class="step-item <?= $step_current >= 9 ? 'completed' : ($step_current == 8 ? 'current' : '') ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="step-icon <?= $step_current >= 9 ? 'bg-success' : ($step_current == 8 ? 'bg-primary' : 'bg-secondary') ?>">
                                    <i class="fas <?= $step_current >= 9 ? 'fa-check' : 'fa-trophy' ?>"></i>
                                    <span class="step-number">8-9</span>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="step-title">Publikasi Selesai</h6>
                                <p class="step-desc text-muted">Download surat keterangan publikasi</p>
                                <?php if ($step_current >= 9): ?>
                                    <small class="text-success"><i class="fa fa-check"></i> Publikasi selesai!</small>
                                    <?php if (!empty($publikasi->tanggal_selesai)): ?>
                                        <small class="text-muted d-block">
                                            Selesai: <?= date('d/m/Y H:i', strtotime($publikasi->tanggal_selesai)) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($step_current >= 9): ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Menunggu</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons untuk Kaprodi -->
                <?php if (in_array($publikasi->status, ['review_pembimbing', 'review_staf', 'rejected'])): ?>
                <hr class="my-4">
                <div class="alert alert-warning">
                    <h6><i class="fa fa-exclamation-triangle"></i> Override Available</h6>
                    <p class="mb-2">Sebagai Kaprodi, Anda dapat melakukan override jika diperlukan.</p>
                    <a href="<?= base_url('kaprodi/publikasi/override/' . $publikasi->id) ?>" 
                       class="btn btn-warning btn-sm">
                        <i class="fa fa-shield-alt"></i> Override Decision
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Info Mahasiswa -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-user-graduate"></i> Info Mahasiswa
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h5 class="mb-1"><?= $publikasi->nama_mahasiswa ?></h5>
                    <p class="text-muted mb-0"><?= $publikasi->nim ?></p>
                    <small class="text-muted"><?= $publikasi->nama_prodi ?></small>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-primary mb-0"><?= $step_current ?>/9</h6>
                        <small class="text-muted">Langkah</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-success mb-0"><?= round(($step_current / 9) * 100) ?>%</h6>
                        <small class="text-muted">Progress</small>
                    </div>
                </div>
                
                <hr>
                
                <p class="text-sm">
                    <strong>Dosen Pembimbing:</strong><br>
                    <?= $publikasi->nama_pembimbing ?>
                </p>
                
                <p class="text-sm">
                    <strong>Judul:</strong><br>
                    <?= word_limiter($publikasi->judul_proposal, 15) ?>
                </p>
            </div>
        </div>

        <!-- Timeline Singkat -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-history"></i> Aktivitas Terbaru
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($timeline)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fa fa-clock fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada aktivitas</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($timeline, -5) as $log): ?>
                        <div class="border-bottom py-2">
                            <h6 class="text-sm mb-1"><?= $log->aktivitas ?></h6>
                            <small class="text-muted">
                                <?= date('d/m H:i', strtotime($log->created_at)) ?>
                                <?php if (!empty($log->user_name)): ?>
                                    - <?= $log->user_name ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="<?= base_url('kaprodi/publikasi/detail/' . $publikasi->id) ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Lihat Detail Lengkap
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>

<style>
.workflow-steps .step-item {
    padding: 1rem 0;
    border-left: 3px solid #dee2e6;
    margin-left: 1rem;
    position: relative;
}

.workflow-steps .step-item.completed {
    border-left-color: #28a745;
}

.workflow-steps .step-item.current {
    border-left-color: #007bff;
    background-color: #f8f9fa;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    position: relative;
    margin-left: -26px;
}

.step-number {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: white;
    color: #333;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid;
}

.step-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.step-desc {
    font-size: 0.875rem;
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}
</style>