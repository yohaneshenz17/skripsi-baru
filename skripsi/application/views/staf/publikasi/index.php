<?php
/**
 * View Index Publikasi untuk Staf - ROBUST ERROR HANDLING VERSION
 * File: application/views/staf/publikasi/index.php
 * 
 * FITUR:
 * - Error handling yang komprehensif
 * - Debug info untuk development
 * - Fallback untuk data kosong
 * - Compatible dengan berbagai struktur database
 */

// Start output buffering untuk content
ob_start();
?>

<!-- Header dengan informasi dan waktu -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow mr-3">
                <i class="fas fa-upload"></i>
            </div>
            <div>
                <h2 class="mb-1 text-dark font-weight-bold">Validasi Publikasi Tugas Akhir</h2>
                <p class="text-secondary mb-0 font-weight-medium">Kelola dan validasi publikasi tugas akhir mahasiswa</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-right">
        <div class="card bg-gradient-info border-0">
            <div class="card-body p-3">
                <div class="text-white">
                    <h6 class="text-white mb-1">
                        <i class="fas fa-user-tie"></i> 
                        <?= $this->session->userdata('nama') ?: 'Staf Akademik' ?>
                    </h6>
                    <small>
                        <i class="fas fa-clock"></i> 
                        <?= date('d F Y, H:i') ?> WIB
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Debug Info (Only in development) -->
<?php if (ENVIRONMENT === 'development' && isset($debug_info) && $debug_info): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><i class="fas fa-bug"></i> Debug Mode</strong>
                    <small>- Database structure detection active</small>
                </div>
                <button class="btn btn-sm btn-outline-info debug-toggle">Toggle Debug Info</button>
            </div>
            <div class="debug-info mt-2" style="display: none;">
                <strong>Tabel Utama:</strong> <?= $debug_info['main_table'] ?? 'undefined' ?><br>
                <strong>Kolom Tersedia:</strong> <?= implode(', ', $debug_info['available_columns'] ?? []) ?><br>
                <strong>Table Info:</strong> <pre><?= json_encode($debug_info['table_info'] ?? [], JSON_PRETTY_PRINT) ?></pre>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<?php if ($this->session->flashdata('info')): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <i class="fas fa-info-circle"></i> 
        <strong>Info!</strong> <?= $this->session->flashdata('info') ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
<?php endif; ?>

<!-- Statistics Cards dengan Error Handling -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Validasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            $menunggu = 0;
                            if (isset($statistics['menunggu_validasi'])) {
                                $menunggu = (int)$statistics['menunggu_validasi'];
                            }
                            echo $menunggu;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Perlu ditindaklanjuti</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Divalidasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            $divalidasi = 0;
                            if (isset($statistics['sudah_divalidasi'])) {
                                $divalidasi = (int)$statistics['sudah_divalidasi'];
                            }
                            echo $divalidasi;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Selesai diproses</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Publikasi Selesai</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            $selesai = 0;
                            if (isset($statistics['publikasi_selesai'])) {
                                $selesai = (int)$statistics['publikasi_selesai'];
                            }
                            echo $selesai;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Completed</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Keseluruhan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            $total = 0;
                            if (isset($statistics['total_keseluruhan'])) {
                                $total = (int)$statistics['total_keseluruhan'];
                            }
                            echo $total;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2">
                        <i class="fas fa-calendar-alt"></i> 
                        <?= date('F Y') ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- System Status Info (jika ada masalah) -->
<?php if (isset($statistics) && array_sum($statistics) === 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="col">
                    <h5 class="mb-1">Status Sistem</h5>
                    <p class="mb-0">
                        Belum ada data publikasi atau sistem masih dalam proses konfigurasi. 
                        Pastikan mahasiswa sudah mengajukan publikasi dan dosen telah menyetujui.
                    </p>
                    <?php if (ENVIRONMENT === 'development'): ?>
                    <small class="text-muted">
                        <strong>Dev Note:</strong> Periksa struktur database dan pastikan data sample tersedia.
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Workflow Info Card -->
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            Workflow Publikasi Staf
                        </h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="workflow-step completed">
                            <div class="step-number bg-secondary">1-2</div>
                            <div class="step-content">
                                <h6 class="step-title">Mahasiswa & Dosen</h6>
                                <p class="step-desc">Mahasiswa ajukan → Dosen approve</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="workflow-step current">
                            <div class="step-number bg-warning">3</div>
                            <div class="step-content">
                                <h6 class="step-title">Input Repository</h6>
                                <p class="step-desc">Staf input link repository skripsi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="workflow-step">
                            <div class="step-number bg-primary">4</div>
                            <div class="step-content">
                                <h6 class="step-title">Validasi Final</h6>
                                <p class="step-desc">Staf validasi + notifikasi email</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="workflow-step">
                            <div class="step-number bg-success">5</div>
                            <div class="step-content">
                                <h6 class="step-title">Selesai</h6>
                                <p class="step-desc">Proses completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Pengajuan Publikasi dengan Error Handling -->
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-list text-primary mr-2"></i>
                            Daftar Pengajuan Publikasi
                        </h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/publikasi/export') ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel mr-2"></i>Export
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Dosen Pembimbing</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pengajuan_list) && is_array($pengajuan_list) && count($pengajuan_list) > 0): ?>
                            <?php foreach ($pengajuan_list as $key => $item): ?>
                            <?php 
                            // Safe property access dengan fallback
                            $nama_mahasiswa = isset($item->nama_mahasiswa) ? $item->nama_mahasiswa : 'N/A';
                            $nim = isset($item->nim) ? $item->nim : 'N/A';
                            $judul = isset($item->judul) ? $item->judul : 'Judul tidak tersedia';
                            $nama_dosen = isset($item->nama_dosen) ? $item->nama_dosen : 'N/A';
                            $link_repository = isset($item->link_repository) ? $item->link_repository : '';
                            $status_staf = isset($item->status_staf) ? $item->status_staf : 'pending';
                            $created_at = isset($item->created_at) ? $item->created_at : date('Y-m-d H:i:s');
                            $id = isset($item->id) ? $item->id : 0;
                            ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold">
                                                <?= htmlspecialchars($nama_mahasiswa) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($nim) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm" title="<?= htmlspecialchars($judul) ?>">
                                        <?= strlen($judul) > 50 ? htmlspecialchars(substr($judul, 0, 50)) . '...' : htmlspecialchars($judul) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= htmlspecialchars($nama_dosen) ?></span>
                                </td>
                                <td>
                                    <?php
                                    // Determine status dengan safe checking
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    if (empty($link_repository)) {
                                        $status_class = 'badge-warning';
                                        $status_text = 'Perlu Input Repository';
                                    } elseif ($status_staf === 'pending' || $status_staf === '0') {
                                        $status_class = 'badge-info';
                                        $status_text = 'Perlu Validasi';
                                    } elseif ($status_staf === 'approved' || $status_staf === '1') {
                                        $status_class = 'badge-success';
                                        $status_text = 'Disetujui';
                                    } elseif ($status_staf === 'rejected' || $status_staf === '2') {
                                        $status_class = 'badge-danger';
                                        $status_text = 'Ditolak';
                                    } else {
                                        $status_class = 'badge-secondary';
                                        $status_text = 'Status: ' . $status_staf;
                                    }
                                    ?>
                                    <span class="badge badge-dot mr-4">
                                        <i class="<?= $status_class ?>"></i>
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= date('d/m/Y', strtotime($created_at)) ?>
                                    </span>
                                </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($id > 0): ?>
                                                <a href="<?= base_url('staf/publikasi/detail/' . $id) ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php
                                                $dosen_approved = isset($item->status_pembimbing) ? $item->status_pembimbing === 'approved' : false;
                                                $is_final_validated = ($status_staf === '1' || $status_staf === 'approved');
                                                ?>
                                                
                                                <?php if ($dosen_approved && !$is_final_validated): ?>
                                                    <!-- SELALU TERSEDIA setelah dosen approve -->
                                                    <a href="<?= base_url('staf/publikasi/input_repository/' . $id) ?>" 
                                                       class="btn btn-sm btn-warning" title="<?= empty($link_repository) ? 'Input Repository' : 'Edit Repository' ?>">
                                                        <i class="fas fa-<?= empty($link_repository) ? 'plus' : 'edit' ?>"></i>
                                                        <?= empty($link_repository) ? 'Input' : 'Edit' ?>
                                                    </a>
                                                    
                                                    <?php if (!empty($link_repository)): ?>
                                                        <a href="<?= base_url('staf/publikasi/validasi/' . $id) ?>" 
                                                           class="btn btn-sm btn-success btn-validasi" title="Validasi Final">
                                                            <i class="fas fa-check"></i> Validasi
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>Belum Ada Data</h5>
                                        <p>Belum ada pengajuan publikasi yang perlu ditangani.</p>
                                        <small>
                                            Data akan muncul setelah:
                                            <br>1. Mahasiswa mengajukan publikasi
                                            <br>2. Dosen pembimbing menyetujui
                                        </small>
                                        
                                        <?php if (ENVIRONMENT === 'development'): ?>
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-outline-info debug-toggle">
                                                <i class="fas fa-bug"></i> Show Debug Info
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>

<!-- Internal CSS untuk halaman ini -->
<style>
.card-stats {
    transition: transform 0.3s ease;
}
.card-stats:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.workflow-step {
    text-align: center;
    position: relative;
    margin-bottom: 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-bottom: 10px;
}

.step-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #32325d;
}

.step-desc {
    font-size: 0.875rem;
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

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.badge-dot i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.btn-group .btn {
    margin-right: 2px;
}

/* Badge colors */
.badge-warning { background-color: #ffc107; }
.badge-info { background-color: #17a2b8; }
.badge-success { background-color: #28a745; }
.badge-danger { background-color: #dc3545; }
.badge-secondary { background-color: #6c757d; }

/* Debug styles */
.debug-info { 
    background: #f8f9fa; 
    padding: 10px; 
    border-radius: 5px; 
    font-size: 0.8rem; 
    font-family: monospace;
}
</style>

<!-- JavaScript untuk interaktivitas dan error handling -->
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm before validation action
    $('.btn-validasi').click(function(e) {
        if (!confirm('Yakin ingin memvalidasi publikasi ini?')) {
            e.preventDefault();
        }
    });
    
    // Tooltip for elements with title attribute
    $('[title]').tooltip();
    
    // Debug info toggle
    $('.debug-toggle').click(function() {
        $('.debug-info').toggle('fast');
    });
    
    // Error handling untuk AJAX requests (jika ada)
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('AJAX Error:', error);
        if (xhr.status === 500) {
            alert('Terjadi kesalahan server. Silakan refresh halaman.');
        }
    });
    
    // Log statistics untuk monitoring (hanya di development)
    <?php if (ENVIRONMENT === 'development'): ?>
    console.log('=== PUBLIKASI STAF DEBUG INFO ===');
    console.log('Statistics:', <?= json_encode(isset($statistics) ? $statistics : []) ?>);
    console.log('Pengajuan List Count:', <?= count(isset($pengajuan_list) ? $pengajuan_list : []) ?>);
    <?php if (isset($debug_info)): ?>
    console.log('Debug Info:', <?= json_encode($debug_info) ?>);
    <?php endif; ?>
    console.log('=====================================');
    <?php endif; ?>
});

// Global error handler
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('JavaScript Error:', {
        message: msg,
        source: url,
        line: lineNo,
        column: columnNo,
        error: error
    });
    return false;
};
</script>

<?php
// =============================================================================
// ERROR PAGE VIEW (Optional - Create separate file)
// File: application/views/staf/publikasi/error.php
// =============================================================================
?>

<!-- ERROR PAGE CONTENT (untuk file terpisah) -->
<!--
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                <h3>Terjadi Kesalahan</h3>
                <p class="text-muted"><?= isset($error_message) ? $error_message : 'Kesalahan tidak diketahui' ?></p>
                
                <?php if (ENVIRONMENT === 'development' && isset($debug_info)): ?>
                <div class="mt-4">
                    <button class="btn btn-outline-info debug-toggle">
                        <i class="fas fa-bug"></i> Show Debug Info
                    </button>
                    <div class="debug-info mt-3" style="display: none;">
                        <pre><?= json_encode($debug_info, JSON_PRETTY_PRINT) ?></pre>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="<?= base_url('staf/publikasi') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                    </a>
                    <a href="<?= base_url('staf/dashboard') ?>" class="btn btn-secondary">
                        <i class="fas fa-home mr-2"></i>Dashboard Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
-->