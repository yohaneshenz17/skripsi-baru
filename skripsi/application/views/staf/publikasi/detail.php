<?php
/**
 * ====================================================================
 * FILE 2: application/views/staf/publikasi/detail.php - SCRIPT LENGKAP
 * ====================================================================
 * 
 * PERBAIKAN: Action buttons di header yang lebih tepat
 */
?>

<?php
/**
 * View Detail Publikasi untuk Staf
 * File: application/views/staf/publikasi/detail.php
 * 
 * FEATURES:
 * - Konsisten dengan template staf yang sudah ada
 * - Error handling yang komprehensif
 * - Action buttons berdasarkan status
 * - Timeline progress workflow
 */

// Start output buffering
ob_start();
?>

<!-- Breadcrumb Navigation -->
<div class="row mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('staf/publikasi') ?>" class="text-primary">
                        <i class="fas fa-upload mr-1"></i>Publikasi
                    </a>
                </li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Header dengan Action Buttons -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow mr-3">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <h2 class="mb-1 text-dark font-weight-bold">Detail Publikasi</h2>
                <p class="text-secondary mb-0 font-weight-medium">
                    <?php 
                    $judul_display = isset($publikasi->judul) ? $publikasi->judul : 'Judul tidak tersedia';
                    echo strlen($judul_display) > 60 ? substr($judul_display, 0, 60) . '...' : $judul_display;
                    ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- ===== BAGIAN YANG DIPERBAIKI: ACTION BUTTONS DI HEADER ===== -->
    <div class="col-lg-4 text-right">
        <div class="btn-group" role="group">
            <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            
            <?php if (isset($publikasi) && $publikasi): ?>
                <?php 
                $link_repository = isset($publikasi->link_repository) ? $publikasi->link_repository : '';
                $status_pembimbing = isset($publikasi->status_pembimbing) ? $publikasi->status_pembimbing : '';
                $status_staf = isset($publikasi->status_staf) ? $publikasi->status_staf : 
                              (isset($publikasi->validasi_staf_publikasi) ? $publikasi->validasi_staf_publikasi : '0');
                
                $dosen_approved = ($status_pembimbing === 'approved' || $status_pembimbing === '1');
                $is_final_validated = ($status_staf === '1' || $status_staf === 'approved');
                ?>
                
                <?php if ($dosen_approved && !$is_final_validated): ?>
                    <!-- Tombol Input/Edit Repository - SELALU TERSEDIA -->
                    <a href="<?= base_url('staf/publikasi/input_repository/' . $publikasi->id) ?>" 
                       class="btn btn-warning">
                        <i class="fas fa-<?= empty($link_repository) ? 'plus' : 'edit' ?> mr-1"></i> 
                        <?= empty($link_repository) ? 'Input Repository' : 'Edit Repository' ?>
                    </a>
                    
                    <!-- Tombol Validasi - hanya jika repository sudah ada -->
                    <?php if (!empty($link_repository)): ?>
                        <a href="<?= base_url('staf/publikasi/validasi/' . $publikasi->id) ?>" 
                           class="btn btn-success">
                            <i class="fas fa-check mr-1"></i> Validasi Final
                        </a>
                    <?php endif; ?>
                    
                <?php elseif ($is_final_validated): ?>
                    <!-- Jika sudah divalidasi final -->
                    <span class="btn btn-outline-success disabled">
                        <i class="fas fa-check-circle mr-1"></i> Publikasi Selesai
                    </span>
                    
                <?php elseif (!$dosen_approved): ?>
                    <!-- Jika belum disetujui dosen -->
                    <span class="btn btn-outline-secondary disabled">
                        <i class="fas fa-hourglass-half mr-1"></i> Menunggu Persetujuan Dosen
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- ===== END BAGIAN YANG DIPERBAIKI ===== -->
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> 
        <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<?php if (isset($publikasi) && $publikasi): ?>

<!-- Progress Timeline -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">
                    <i class="fas fa-timeline text-primary mr-2"></i>
                    Progress Workflow
                </h3>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="workflow-step completed">
                            <div class="step-number bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <h6 class="step-title">Pengajuan Mahasiswa</h6>
                            <p class="step-desc">Mahasiswa mengajukan publikasi</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <?php 
                        $dosen_approved = isset($publikasi->status_pembimbing) ? 
                                         $publikasi->status_pembimbing === 'approved' : false;
                        ?>
                        <div class="workflow-step <?= $dosen_approved ? 'completed' : 'pending' ?>">
                            <div class="step-number <?= $dosen_approved ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $dosen_approved ? '<i class="fas fa-check"></i>' : '2' ?>
                            </div>
                            <h6 class="step-title">Persetujuan Dosen</h6>
                            <p class="step-desc">Dosen menyetujui publikasi</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <?php 
                        $repo_inputted = !empty($link_repository);
                        $is_current_repo = $dosen_approved && !$repo_inputted;
                        ?>
                        <div class="workflow-step <?= $repo_inputted ? 'completed' : ($is_current_repo ? 'current' : 'pending') ?>">
                            <div class="step-number <?= $repo_inputted ? 'bg-success' : ($is_current_repo ? 'bg-warning' : 'bg-secondary') ?>">
                                <?= $repo_inputted ? '<i class="fas fa-check"></i>' : '3' ?>
                            </div>
                            <h6 class="step-title">Input Repository</h6>
                            <p class="step-desc">Staf input link repository</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <?php 
                        $is_validated = ($status_staf === '1' || $status_staf === 'approved');
                        $is_current_valid = $repo_inputted && !$is_validated;
                        ?>
                        <div class="workflow-step <?= $is_validated ? 'completed' : ($is_current_valid ? 'current' : 'pending') ?>">
                            <div class="step-number <?= $is_validated ? 'bg-success' : ($is_current_valid ? 'bg-warning' : 'bg-secondary') ?>">
                                <?= $is_validated ? '<i class="fas fa-check"></i>' : '4' ?>
                            </div>
                            <h6 class="step-title">Validasi Final</h6>
                            <p class="step-desc">Staf validasi & notifikasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Information -->
<div class="row">
    <!-- Left Column - Main Info -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Informasi Mahasiswa
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Nama Mahasiswa</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_mahasiswa) ? htmlspecialchars($publikasi->nama_mahasiswa) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">NIM</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nim) ? htmlspecialchars($publikasi->nim) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Email</label>
                            <p class="form-control-plaintext">
                                <?php if (isset($publikasi->email_mahasiswa) && !empty($publikasi->email_mahasiswa)): ?>
                                    <a href="mailto:<?= $publikasi->email_mahasiswa ?>" class="text-primary">
                                        <?= htmlspecialchars($publikasi->email_mahasiswa) ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Program Studi</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_prodi) ? htmlspecialchars($publikasi->nama_prodi) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Dosen Pembimbing</label>
                            <p class="form-control-plaintext">
                                <?= isset($publikasi->nama_dosen) ? htmlspecialchars($publikasi->nama_dosen) : 'N/A' ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label text-sm font-weight-bold">Tanggal Pengajuan</label>
                            <p class="form-control-plaintext">
                                <?php 
                                $created_at = isset($publikasi->created_at) ? $publikasi->created_at : null;
                                echo $created_at ? date('d F Y, H:i', strtotime($created_at)) . ' WIB' : 'N/A';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tugas Akhir Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-book text-info mr-2"></i>
                    Informasi Tugas Akhir
                </h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label text-sm font-weight-bold">Judul Tugas Akhir</label>
                    <p class="form-control-plaintext">
                        <?= isset($publikasi->judul) ? htmlspecialchars($publikasi->judul) : 'N/A' ?>
                    </p>
                </div>
                
                <?php if (!empty($link_repository)): ?>
                <div class="form-group">
                    <label class="form-control-label text-sm font-weight-bold">Link Repository</label>
                    <p class="form-control-plaintext">
                        <a href="<?= htmlspecialchars($link_repository) ?>" 
                           target="_blank" 
                           class="text-primary">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            <?= htmlspecialchars($link_repository) ?>
                        </a>
                    </p>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Repository belum diinput.</strong> 
                    Klik tombol "Input Repository" untuk menambahkan link repository perpustakaan.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Validation History (if any) -->
        <?php if (isset($publikasi->catatan_staf) && !empty($publikasi->catatan_staf)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-comments text-warning mr-2"></i>
                    Riwayat Validasi
                </h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label text-sm font-weight-bold">Catatan Staf</label>
                    <p class="form-control-plaintext">
                        <?= htmlspecialchars($publikasi->catatan_staf) ?>
                    </p>
                </div>
                
                <?php if (isset($publikasi->tanggal_validasi_staf) && !empty($publikasi->tanggal_validasi_staf)): ?>
                <div class="form-group">
                    <label class="form-control-label text-sm font-weight-bold">Tanggal Validasi</label>
                    <p class="form-control-plaintext">
                        <?= date('d F Y, H:i', strtotime($publikasi->tanggal_validasi_staf)) ?> WIB
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Status & Actions -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-info-circle text-success mr-2"></i>
                    Status Publikasi
                </h4>
            </div>
            <div class="card-body">
                <?php
                // Determine current status
                $current_status = 'Menunggu';
                $status_class = 'secondary';
                $status_icon = 'hourglass-half';
                
                if (empty($link_repository)) {
                    $current_status = 'Perlu Input Repository';
                    $status_class = 'warning';
                    $status_icon = 'plus-circle';
                } elseif ($status_staf === '0' || $status_staf === 'pending') {
                    $current_status = 'Menunggu Validasi';
                    $status_class = 'info';
                    $status_icon = 'clock';
                } elseif ($status_staf === '1' || $status_staf === 'approved') {
                    $current_status = 'Disetujui';
                    $status_class = 'success';
                    $status_icon = 'check-circle';
                } elseif ($status_staf === '2' || $status_staf === 'rejected') {
                    $current_status = 'Ditolak';
                    $status_class = 'danger';
                    $status_icon = 'times-circle';
                }
                ?>
                
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="fas fa-<?= $status_icon ?> fa-3x text-<?= $status_class ?>"></i>
                    </div>
                    <h5 class="text-<?= $status_class ?> mb-2"><?= $current_status ?></h5>
                    
                    <?php if ($current_status === 'Perlu Input Repository'): ?>
                        <p class="text-muted">Silakan input link repository perpustakaan untuk melanjutkan proses.</p>
                    <?php elseif ($current_status === 'Menunggu Validasi'): ?>
                        <p class="text-muted">Repository sudah diinput. Silakan lakukan validasi final.</p>
                    <?php elseif ($current_status === 'Disetujui'): ?>
                        <p class="text-muted">Publikasi telah divalidasi dan disetujui.</p>
                    <?php elseif ($current_status === 'Ditolak'): ?>
                        <p class="text-muted">Publikasi ditolak. Lihat catatan untuk perbaikan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-tools text-primary mr-2"></i>
                    Aksi Cepat
                </h4>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (empty($link_repository)): ?>
                        <a href="<?= base_url('staf/publikasi/input_repository/' . $publikasi->id) ?>" 
                           class="btn btn-warning btn-block">
                            <i class="fas fa-plus mr-2"></i>
                            Input Repository
                        </a>
                    <?php elseif ($status_staf === '0' || $status_staf === 'pending'): ?>
                        <a href="<?= base_url('staf/publikasi/validasi/' . $publikasi->id) ?>" 
                           class="btn btn-success btn-block">
                            <i class="fas fa-check mr-2"></i>
                            Validasi Publikasi
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($link_repository)): ?>
                        <a href="<?= htmlspecialchars($link_repository) ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-block">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Lihat Repository
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($publikasi->email_mahasiswa) && !empty($publikasi->email_mahasiswa)): ?>
                        <a href="mailto:<?= $publikasi->email_mahasiswa ?>?subject=Publikasi Tugas Akhir - <?= urlencode($publikasi->judul ?? '') ?>" 
                           class="btn btn-outline-info btn-block">
                            <i class="fas fa-envelope mr-2"></i>
                            Email Mahasiswa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Error State -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                <h3>Data Tidak Ditemukan</h3>
                <p class="text-muted">Data publikasi yang Anda cari tidak ditemukan atau telah dihapus.</p>
                <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
echo $content;
?>

<!-- CSS untuk Timeline dan Detail -->
<style>
.workflow-step {
    text-align: center;
    position: relative;
    margin-bottom: 20px;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.step-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #32325d;
    font-size: 0.9rem;
}

.step-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}

.workflow-step.completed .step-number {
    background-color: #28a745 !important;
}

.workflow-step.current .step-number {
    background-color: #ffc107 !important;
    animation: pulse 2s infinite;
}

.workflow-step.pending .step-number {
    background-color: #6c757d !important;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.form-control-plaintext {
    padding-left: 0;
    padding-right: 0;
    border: none;
    background: none;
    font-weight: 500;
    color: #525f7f;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .workflow-step {
        margin-bottom: 30px;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<!-- JavaScript untuk interaktivitas -->
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
    
    // Smooth scroll untuk long content
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 20
            }, 1000);
        }
    });
    
    console.log('Publikasi Detail Page Loaded');
});
</script>