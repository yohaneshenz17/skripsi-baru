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

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Breadcrumb -->
<div class="row mb-3">
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
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Header Card -->
<div class="card bg-gradient-primary text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="text-white mb-0">
                    <i class="fa fa-user-graduate mr-2"></i>
                    Detail Publikasi Tugas Akhir
                </h3>
                <p class="text-white-75 mb-0">
                    Monitoring dan oversight publikasi mahasiswa
                </p>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('kaprodi/publikasi') ?>" class="btn btn-neutral">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <a href="<?= base_url('kaprodi/publikasi/tracking/' . $publikasi->id) ?>" class="btn btn-info">
                    <i class="fa fa-route"></i> Tracking
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-lg-8">
        <!-- Informasi Mahasiswa & Publikasi -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">Informasi Publikasi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Info Mahasiswa -->
                    <div class="col-md-6">
                        <h6 class="heading-small text-muted mb-4">Data Mahasiswa</h6>
                        <div class="pl-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Nama Mahasiswa</label>
                                <p class="form-control-plaintext font-weight-bold"><?= $publikasi->nama_mahasiswa ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">NIM</label>
                                <p class="form-control-plaintext"><?= $publikasi->nim ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Program Studi</label>
                                <p class="form-control-plaintext"><?= $publikasi->nama_prodi ?></p>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Dosen Pembimbing</label>
                                <p class="form-control-plaintext"><?= $publikasi->nama_pembimbing ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Progress -->
                    <div class="col-md-6">
                        <h6 class="heading-small text-muted mb-4">Status & Progress</h6>
                        <div class="pl-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Status Publikasi</label>
                                <p class="form-control-plaintext">
                                    <?php
                                    $status_badges = [
                                        'draft' => 'badge-secondary',
                                        'submitted' => 'badge-info',
                                        'review_pembimbing' => 'badge-warning',
                                        'approved_pembimbing' => 'badge-primary',
                                        'review_staf' => 'badge-primary',
                                        'completed' => 'badge-success',
                                        'rejected' => 'badge-danger'
                                    ];
                                    
                                    $status_text = [
                                        'draft' => 'Draft',
                                        'submitted' => 'Diajukan ke Dosen',
                                        'review_pembimbing' => 'Review Dosen',
                                        'approved_pembimbing' => 'Approved Dosen',
                                        'review_staf' => 'Review Staf',
                                        'completed' => 'Selesai',
                                        'rejected' => 'Ditolak'
                                    ];
                                    
                                    $badge_class = $status_badges[$publikasi->status] ?? 'badge-secondary';
                                    $status_display = $status_text[$publikasi->status] ?? 'Unknown';
                                    ?>
                                    <span class="badge badge-lg <?= $badge_class ?>"><?= $status_display ?></span>
                                </p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-control-label">Progress Workflow</label>
                                <div class="progress mb-2" style="height: 8px;">
                                    <?php
                                    $progress_map = [
                                        'draft' => 20,
                                        'submitted' => 40,
                                        'review_pembimbing' => 50,
                                        'approved_pembimbing' => 70,
                                        'review_staf' => 80,
                                        'completed' => 100,
                                        'rejected' => 30
                                    ];
                                    $progress = $progress_map[$publikasi->status] ?? 10;
                                    $progress_color = $publikasi->status == 'completed' ? 'success' : 
                                                    ($publikasi->status == 'rejected' ? 'danger' : 'info');
                                    ?>
                                    <div class="progress-bar bg-<?= $progress_color ?>" 
                                         role="progressbar" 
                                         style="width: <?= $progress ?>%"
                                         aria-valuenow="<?= $progress ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted"><?= $progress ?>% dari 9 langkah workflow</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Pengajuan</label>
                                <p class="form-control-plaintext">
                                    <?= !empty($publikasi->tanggal_pengajuan) ? 
                                        date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan)) : 
                                        date('d F Y H:i', strtotime($publikasi->created_at)) ?>
                                </p>
                            </div>
                            
                            <?php if (!empty($publikasi->tanggal_selesai)): ?>
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Selesai</label>
                                <p class="form-control-plaintext">
                                    <?= date('d F Y H:i', strtotime($publikasi->tanggal_selesai)) ?>
                                    <small class="text-muted d-block">
                                        Durasi: <?= $this->_calculate_duration($publikasi->tanggal_pengajuan, $publikasi->tanggal_selesai) ?> hari
                                    </small>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Judul Proposal -->
                <div class="form-group">
                    <label class="form-control-label">Judul Tugas Akhir</label>
                    <p class="form-control-plaintext"><?= $publikasi->judul_proposal ?></p>
                </div>

                <!-- Files yang diupload -->
                <?php if (!empty($publikasi->file_skripsi_final) || !empty($publikasi->file_surat_revisi) || !empty($publikasi->file_surat_perpustakaan)): ?>
                <div class="form-group">
                    <label class="form-control-label">File yang Diupload</label>
                    <div class="mt-2">
                        <?php if (!empty($publikasi->file_skripsi_final)): ?>
                            <span class="badge badge-info mr-2 mb-1">
                                <i class="fa fa-file-pdf"></i> Skripsi Final
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($publikasi->file_surat_revisi)): ?>
                            <span class="badge badge-primary mr-2 mb-1">
                                <i class="fa fa-file-alt"></i> Surat Revisi
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                            <span class="badge badge-success mr-2 mb-1">
                                <i class="fa fa-file-alt"></i> Surat Perpustakaan
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Link Repository -->
                <?php if (!empty($publikasi->link_repository)): ?>
                <div class="form-group">
                    <label class="form-control-label">Link Repository</label>
                    <p class="form-control-plaintext">
                        <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fa fa-external-link-alt"></i> Buka Repository
                        </a>
                        <small class="text-muted d-block mt-1"><?= $publikasi->link_repository ?></small>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Komentar -->
                <?php if (!empty($publikasi->komentar_pembimbing) || !empty($publikasi->komentar_staf)): ?>
                <hr class="my-4">
                <h6 class="heading-small text-muted mb-4">Komentar & Feedback</h6>
                
                <div class="row">
                    <?php if (!empty($publikasi->komentar_pembimbing)): ?>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fa fa-user-tie"></i> Komentar Dosen Pembimbing
                            </h6>
                            <p class="mb-0"><?= nl2br($publikasi->komentar_pembimbing) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($publikasi->komentar_staf)): ?>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fa fa-user-cog"></i> Komentar Staf
                            </h6>
                            <p class="mb-0"><?= nl2br($publikasi->komentar_staf) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Override Actions untuk Kaprodi -->
                <?php if (in_array($publikasi->status, ['review_pembimbing', 'review_staf', 'rejected'])): ?>
                <hr class="my-4">
                <div class="alert alert-warning">
                    <h5><i class="fa fa-exclamation-triangle"></i> Override Available</h5>
                    <p class="mb-2">
                        Sebagai Kaprodi, Anda dapat melakukan override untuk menangani situasi darurat atau kasus khusus.
                        Tindakan override akan dicatat dalam log sistem.
                    </p>
                    <a href="<?= base_url('kaprodi/publikasi/override/' . $publikasi->id) ?>" 
                       class="btn btn-warning"
                       onclick="return confirm('Yakin ingin melakukan override? Pastikan Anda memahami konsekuensinya.')">
                        <i class="fa fa-shield-alt"></i> Override Decision
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar - Timeline & Jurnal -->
    <div class="col-lg-4">
        <!-- Timeline Activities -->
        <div class="card shadow mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-history"></i> Timeline Aktivitas
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($timeline)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fa fa-clock fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada aktivitas</p>
                    </div>
                <?php else: ?>
                    <div class="timeline timeline-one-side">
                        <?php foreach ($timeline as $log): ?>
                            <div class="timeline-block">
                                <span class="timeline-step badge-primary">
                                    <i class="fa fa-circle"></i>
                                </span>
                                <div class="timeline-content">
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($log->created_at)) ?>
                                    </small>
                                    <h6 class="text-sm mt-1 mb-0"><?= $log->aktivitas ?></h6>
                                    <?php if (!empty($log->deskripsi)): ?>
                                        <p class="text-xs text-muted mt-1 mb-0"><?= $log->deskripsi ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($log->user_name)): ?>
                                        <small class="text-primary">oleh <?= $log->user_name ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Jurnal Bimbingan Summary -->
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-book"></i> Jurnal Bimbingan
                </h3>
                <small class="text-muted">10 bimbingan terakhir</small>
            </div>
            <div class="card-body">
                <?php if (empty($jurnal_bimbingan)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fa fa-book fa-2x mb-2"></i>
                        <p class="mb-0">Belum ada jurnal bimbingan</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($jurnal_bimbingan, 0, 10) as $jurnal): ?>
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-sm mb-1">Bimbingan ke-<?= $jurnal->pertemuan_ke ?></h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                                    </small>
                                </div>
                                <div>
                                    <?php if ($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-sm badge-success">Valid</span>
                                    <?php elseif ($jurnal->status_validasi == '2'): ?>
                                        <span class="badge badge-sm badge-warning">Revisi</span>
                                    <?php else: ?>
                                        <span class="badge badge-sm badge-secondary">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($jurnal->materi_bimbingan)): ?>
                                <p class="text-xs mt-1 mb-0">
                                    <?= substr($jurnal->materi_bimbingan, 0, 100) ?>
                                    <?= strlen($jurnal->materi_bimbingan) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($jurnal_bimbingan) > 10): ?>
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Dan <?= count($jurnal_bimbingan) - 10 ?> jurnal lainnya
                            </small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>