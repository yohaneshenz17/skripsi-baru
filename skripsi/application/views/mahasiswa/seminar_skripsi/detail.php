<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/detail.php
 * FIXED VERSION - Pattern sesuai dengan pengajuan.php dan workflow terbaru
 * 
 * PERBAIKAN:
 * 1. Fixed undefined variable $can_edit, $progress, $can_resubmit
 * 2. Pattern defensive programming dengan isset() checks
 * 3. Sesuaikan dengan struktur data dari controller
 * 4. Fixed array access untuk progress data
 */

// ✅ FIXED: Ensure all variables are defined with default values
$seminar = isset($seminar) ? $seminar : new stdClass();
$progress_data = isset($progress_data) ? $progress_data : ['percentage' => 0, 'steps' => []];

// ✅ FIXED: Calculate can_edit berdasarkan status seminar
$can_edit = false;
if (isset($seminar->status)) {
    $can_edit = in_array($seminar->status, ['draft', 'rejected', 'submitted']) || 
                (isset($seminar->status_pembimbing) && $seminar->status_pembimbing == 'rejected');
}

// ✅ FIXED: Calculate can_resubmit
$can_resubmit = false;
if (isset($seminar->status)) {
    $can_resubmit = in_array($seminar->status, ['rejected']) || 
                    (isset($seminar->status_pembimbing) && $seminar->status_pembimbing == 'rejected');
}

// ✅ FIXED: Safe progress calculation
$progress = isset($progress_data) ? $progress_data : [
    'percentage' => 0,
    'steps' => [
        ['title' => 'Pengajuan', 'status' => 'completed', 'completed' => true, 'active' => false],
        ['title' => 'Review Pembimbing', 'status' => 'pending', 'completed' => false, 'active' => true],
        ['title' => 'Validasi Kaprodi', 'status' => 'pending', 'completed' => false, 'active' => false],
        ['title' => 'Penjadwalan', 'status' => 'pending', 'completed' => false, 'active' => false],
        ['title' => 'Pelaksanaan', 'status' => 'pending', 'completed' => false, 'active' => false]
    ]
];

// ✅ FIXED: Ensure seminar properties exist
if (!isset($seminar->id)) $seminar->id = 0;
if (!isset($seminar->judul_skripsi)) $seminar->judul_skripsi = 'Judul tidak tersedia';
if (!isset($seminar->status)) $seminar->status = 'unknown';
if (!isset($seminar->proposal_id)) $seminar->proposal_id = 0;
if (!isset($seminar->file_skripsi)) $seminar->file_skripsi = '';
if (!isset($seminar->created_at)) $seminar->created_at = '';
if (!isset($seminar->updated_at)) $seminar->updated_at = '';

// ✅ FIXED: Safe status display
$status_text = 'Status tidak diketahui';
$status_class = 'secondary';
if (isset($seminar->status)) {
    switch ($seminar->status) {
        case 'submitted':
            $status_text = 'Menunggu Review Pembimbing';
            $status_class = 'warning';
            break;
        case 'approved':
            $status_text = 'Disetujui';
            $status_class = 'success';
            break;
        case 'rejected':
            $status_text = 'Ditolak - Perlu Perbaikan';
            $status_class = 'danger';
            break;
        case 'scheduled':
            $status_text = 'Seminar Dijadwalkan';
            $status_class = 'info';
            break;
        case 'completed':
            $status_text = 'Seminar Selesai';
            $status_class = 'success';
            break;
        default:
            $status_text = 'Status: ' . ucfirst($seminar->status);
            $status_class = 'secondary';
    }
}
?>

<style>
.progress-container {
    position: relative;
    margin-bottom: 2rem;
}

.progress {
    height: 8px;
    border-radius: 10px;
    background-color: #e9ecef;
    margin-bottom: 1rem;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e9ecef;
    color: #6c757d;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 8px;
    z-index: 2;
    position: relative;
}

.step-circle.completed {
    background-color: #28a745;
    color: white;
}

.step-circle.active {
    background-color: #007bff;
    color: white;
}

.step-title {
    font-size: 12px;
    text-align: center;
    color: #6c757d;
    font-weight: 500;
}

.step-title.completed {
    color: #28a745;
    font-weight: 600;
}

.step-title.active {
    color: #007bff;
    font-weight: 600;
}

.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.status-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

.btn-action {
    border-radius: 0.375rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    margin-bottom: 0.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #495057;
    flex: 0 0 40%;
}

.info-value {
    color: #6c757d;
    flex: 1;
    text-align: right;
}
</style>

<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Detail Seminar Skripsi
        </h1>
        <div class="btn-group">
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
                                 style="width: <?= isset($progress['percentage']) ? $progress['percentage'] : 0 ?>%;" 
                                 aria-valuenow="<?= isset($progress['percentage']) ? $progress['percentage'] : 0 ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        
                        <div class="progress-steps">
                            <?php if (isset($progress['steps']) && is_array($progress['steps'])): ?>
                                <?php foreach ($progress['steps'] as $step): ?>
                                    <div class="progress-step">
                                        <div class="step-circle <?= isset($step['completed']) && $step['completed'] ? 'completed' : (isset($step['active']) && $step['active'] ? 'active' : '') ?>">
                                            <?= isset($step['completed']) && $step['completed'] ? '<i class="fas fa-check"></i>' : (isset($step['active']) && $step['active'] ? '<i class="fas fa-clock"></i>' : '<i class="fas fa-circle"></i>') ?>
                                        </div>
                                        <div class="step-title <?= isset($step['completed']) && $step['completed'] ? 'completed' : (isset($step['active']) && $step['active'] ? 'active' : '') ?>">
                                            <?= isset($step['title']) ? htmlspecialchars($step['title']) : 'Step' ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default progress steps if no data -->
                                <div class="progress-step">
                                    <div class="step-circle completed">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="step-title completed">Pengajuan</div>
                                </div>
                                <div class="progress-step">
                                    <div class="step-circle active">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="step-title active">Review</div>
                                </div>
                                <div class="progress-step">
                                    <div class="step-circle">
                                        <i class="fas fa-circle"></i>
                                    </div>
                                    <div class="step-title">Validasi</div>
                                </div>
                                <div class="progress-step">
                                    <div class="step-circle">
                                        <i class="fas fa-circle"></i>
                                    </div>
                                    <div class="step-title">Jadwal</div>
                                </div>
                                <div class="progress-step">
                                    <div class="step-circle">
                                        <i class="fas fa-circle"></i>
                                    </div>
                                    <div class="step-title">Selesai</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Informasi -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">ID Seminar:</div>
                        <div class="info-value">#<?= htmlspecialchars($seminar->id) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Judul Skripsi:</div>
                        <div class="info-value"><?= htmlspecialchars($seminar->judul_skripsi) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Status:</div>
                        <div class="info-value">
                            <span class="badge badge-<?= $status_class ?> status-badge">
                                <?= htmlspecialchars($status_text) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Tanggal Pengajuan:</div>
                        <div class="info-value">
                            <?= $seminar->created_at ? date('d F Y H:i', strtotime($seminar->created_at)) : 'Tidak tersedia' ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Terakhir Diupdate:</div>
                        <div class="info-value">
                            <?= $seminar->updated_at ? date('d F Y H:i', strtotime($seminar->updated_at)) : 'Tidak tersedia' ?>
                        </div>
                    </div>

                    <?php if (!empty($seminar->file_skripsi)): ?>
                    <div class="info-item">
                        <div class="info-label">File Skripsi:</div>
                        <div class="info-value">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Information Cards can be added here -->
            <?php if (isset($seminar->plagiarism_percentage) && !empty($seminar->plagiarism_percentage)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-search mr-2"></i>
                        Hasil Cek Plagiarisme
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                Tingkat Kemiripan: 
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
                               class="btn btn-danger btn-action">
                                <i class="fas fa-redo mr-2"></i>Ajukan Ulang
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                           class="btn btn-secondary btn-action">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info mr-2"></i>
                        Informasi Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <span class="badge badge-<?= $status_class ?> badge-lg p-3">
                                <?= htmlspecialchars($status_text) ?>
                            </span>
                        </div>
                        
                        <?php if (isset($progress['percentage'])): ?>
                        <div class="mb-2">
                            <small class="text-muted">Progress Keseluruhan</small>
                            <div class="progress mt-1">
                                <div class="progress-bar" style="width: <?= $progress['percentage'] ?>%">
                                    <?= $progress['percentage'] ?>%
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <small class="text-muted">
                            <?php if ($seminar->status == 'submitted'): ?>
                                Pengajuan Anda sedang dalam proses review oleh dosen pembimbing.
                            <?php elseif ($seminar->status == 'approved'): ?>
                                Pengajuan telah disetujui dan sedang menunggu penjadwalan seminar.
                            <?php elseif ($seminar->status == 'rejected'): ?>
                                Pengajuan ditolak. Silakan perbaiki sesuai catatan dan ajukan ulang.
                            <?php elseif ($seminar->status == 'scheduled'): ?>
                                Seminar telah dijadwalkan. Bersiaplah untuk presentasi.
                            <?php elseif ($seminar->status == 'completed'): ?>
                                Selamat! Seminar skripsi Anda telah selesai dilaksanakan.
                            <?php else: ?>
                                Status pengajuan sedang diproses oleh sistem.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize any tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-refresh progress every 30 seconds
    setInterval(function() {
        // Optional: Add AJAX refresh for real-time updates
        console.log('Progress check - Auto refresh feature can be added here');
    }, 30000);
});
</script>