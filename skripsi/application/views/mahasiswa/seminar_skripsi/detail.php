<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/detail.php
 * COMPLETE FIXED VERSION - Ready to replace existing file
 * 
 * SEMUA PERBAIKAN:
 * 1. Fixed undefined variable errors
 * 2. Fixed tanggal_pengajuan -> created_at
 * 3. Fixed judul_skripsi display
 * 4. Fixed progress bar compatibility
 * 5. Defensive programming untuk semua variables
 * 6. Struktur sesuai workflow existing
 */

// ===============================================================
// DEFENSIVE PROGRAMMING: Pastikan semua variables ada
// ===============================================================

$seminar = isset($seminar) ? $seminar : new stdClass();
$progress_data = isset($progress_data) ? $progress_data : ['percentage' => 0, 'steps' => []];
$progress = isset($progress) ? $progress : $progress_data; // Backward compatibility
$can_edit = isset($can_edit) ? $can_edit : false;
$can_resubmit = isset($can_resubmit) ? $can_resubmit : false;

// ===============================================================
// FIELD VALIDATION: Pastikan semua field seminar object ada
// ===============================================================

if (!isset($seminar->id)) $seminar->id = 0;
if (!isset($seminar->judul_skripsi)) $seminar->judul_skripsi = 'Judul tidak tersedia';
if (!isset($seminar->status)) $seminar->status = 'unknown';
if (!isset($seminar->proposal_id)) $seminar->proposal_id = 0;
if (!isset($seminar->file_skripsi)) $seminar->file_skripsi = '';
if (!isset($seminar->created_at)) $seminar->created_at = '';
if (!isset($seminar->updated_at)) $seminar->updated_at = '';
if (!isset($seminar->status_pembimbing)) $seminar->status_pembimbing = 'pending';
if (!isset($seminar->status_kaprodi)) $seminar->status_kaprodi = 'pending';
if (!isset($seminar->keterangan_mahasiswa)) $seminar->keterangan_mahasiswa = '';
if (!isset($seminar->nama_mahasiswa)) $seminar->nama_mahasiswa = '';
if (!isset($seminar->nim)) $seminar->nim = '';
if (!isset($seminar->nama_pembimbing)) $seminar->nama_pembimbing = '';

// ===============================================================
// STATUS LOGIC: Tentukan status text dan class untuk display
// ===============================================================

$status_text = 'Status tidak diketahui';
$status_class = 'secondary';
$status_icon = 'fas fa-question';

// Primary status logic
switch ($seminar->status) {
    case 'draft':
        $status_text = 'Draft - Belum Dikirim';
        $status_class = 'secondary';
        $status_icon = 'fas fa-edit';
        break;
    case 'submitted':
        $status_text = 'Menunggu Review Pembimbing';
        $status_class = 'warning';
        $status_icon = 'fas fa-clock';
        break;
    case 'approved':
        $status_text = 'Disetujui - Menunggu Penjadwalan';
        $status_class = 'success';
        $status_icon = 'fas fa-check';
        break;
    case 'rejected':
        $status_text = 'Ditolak - Perlu Perbaikan';
        $status_class = 'danger';
        $status_icon = 'fas fa-times';
        break;
    case 'scheduled':
        $status_text = 'Seminar Dijadwalkan';
        $status_class = 'info';
        $status_icon = 'fas fa-calendar';
        break;
    case 'completed':
        $status_text = 'Seminar Selesai';
        $status_class = 'success';
        $status_icon = 'fas fa-trophy';
        break;
    default:
        $status_text = 'Status: ' . ucfirst($seminar->status);
        $status_class = 'secondary';
        $status_icon = 'fas fa-question';
}

// Override dengan status pembimbing/kaprodi jika ditolak
if ($seminar->status_pembimbing == 'rejected') {
    $status_text = 'Ditolak oleh Pembimbing';
    $status_class = 'danger';
    $status_icon = 'fas fa-times-circle';
} elseif ($seminar->status_kaprodi == 'rejected') {
    $status_text = 'Ditolak oleh Kaprodi';
    $status_class = 'danger';
    $status_icon = 'fas fa-times-circle';
}

// ===============================================================
// PROGRESS CALCULATION: Hitung percentage jika tidak ada
// ===============================================================

if (!isset($progress['percentage']) || $progress['percentage'] == 0) {
    // Fallback calculation
    $percentage = 20; // Default: pengajuan dikirim
    
    if ($seminar->status_pembimbing == 'approved') {
        $percentage = 40;
    }
    if ($seminar->status_kaprodi == 'approved') {
        $percentage = 60;
    }
    if ($seminar->status == 'scheduled') {
        $percentage = 80;
    }
    if ($seminar->status == 'completed') {
        $percentage = 100;
    }
    
    $progress['percentage'] = $percentage;
}

// ===============================================================
// HELPER TEXT: Text bantuan berdasarkan status
// ===============================================================

$help_text = '';
switch ($seminar->status) {
    case 'submitted':
        $help_text = 'Pengajuan Anda sedang dalam proses review oleh dosen pembimbing. Mohon tunggu notifikasi selanjutnya.';
        break;
    case 'approved':
        $help_text = 'Pengajuan telah disetujui dan sedang menunggu penjadwalan seminar. Staf akan menghubungi Anda untuk koordinasi jadwal.';
        break;
    case 'rejected':
        $help_text = 'Pengajuan ditolak. Silakan perbaiki sesuai catatan dan ajukan ulang melalui tombol "Edit Pengajuan" atau "Ajukan Ulang".';
        break;
    case 'scheduled':
        $help_text = 'Seminar telah dijadwalkan. Bersiaplah untuk presentasi dan pastikan semua berkas sudah lengkap.';
        break;
    case 'completed':
        $help_text = 'Selamat! Seminar skripsi Anda telah selesai dilaksanakan. Silakan lanjut ke tahap selanjutnya.';
        break;
    default:
        $help_text = 'Status pengajuan sedang diproses oleh sistem. Hubungi admin jika ada pertanyaan.';
}

if ($seminar->status_pembimbing == 'rejected') {
    $help_text = 'Pengajuan ditolak oleh dosen pembimbing. Silakan perbaiki berdasarkan masukan yang diberikan.';
} elseif ($seminar->status_kaprodi == 'rejected') {
    $help_text = 'Pengajuan ditolak oleh Kaprodi. Silakan perbaiki berdasarkan masukan yang diberikan.';
}
?>

<style>
.progress-step-horizontal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.step-item {
    text-align: center;
    flex: 1;
    position: relative;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-weight: bold;
    color: white;
}

.step-title {
    font-size: 12px;
    font-weight: 500;
}

.step-completed {
    background-color: #28a745;
}

.step-active {
    background-color: #007bff;
}

.step-pending {
    background-color: #6c757d;
}

.step-rejected {
    background-color: #dc3545;
}

.step-line {
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background-color: #e9ecef;
    z-index: -1;
}

.step-line.completed {
    background-color: #28a745;
}

.info-table {
    margin-bottom: 0;
    table-layout: fixed;
    width: 100%;
}

.info-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #f8f9fa;
    vertical-align: top;
    word-wrap: break-word;
    word-break: break-word;
}

.info-table td:first-child {
    font-weight: 600;
    color: #495057;
    width: 30%;
    vertical-align: top;
}

.info-table td:last-child {
    width: 70%;
}

.judul-wrap {
    word-wrap: break-word;
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    line-height: 1.4;
    max-height: 120px;
    overflow-y: auto;
    padding-right: 5px;
    position: relative;
}

.judul-wrap:hover {
    cursor: help;
}

.judul-wrap.scrollable {
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 8px;
    background-color: #fafafa;
}

.judul-wrap.scrollable::after {
    content: '↕';
    position: absolute;
    right: 2px;
    top: 2px;
    font-size: 10px;
    color: #6c757d;
    opacity: 0.7;
}

/* Custom scrollbar untuk judul wrap */
.judul-wrap::-webkit-scrollbar {
    width: 4px;
}

.judul-wrap::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.judul-wrap::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.judul-wrap::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.card-header-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-block-spacing {
    margin-bottom: 0.5rem;
}

.alert-help {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #495057;
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
                <div class="card-header card-header-gradient text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line mr-2"></i>
                        Progress Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Progress Keseluruhan</span>
                            <span class="text-primary font-weight-bold"><?= isset($progress['percentage']) ? $progress['percentage'] : 0 ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success progress-bar-striped" 
                                 role="progressbar" 
                                 style="width: <?= isset($progress['percentage']) ? $progress['percentage'] : 0 ?>%;" 
                                 aria-valuenow="<?= isset($progress['percentage']) ? $progress['percentage'] : 0 ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Steps -->
                    <div class="progress-step-horizontal">
                        <?php 
                        // Default steps jika tidak ada dari controller
                        if (!isset($progress['steps']) || empty($progress['steps'])) {
                            $progress['steps'] = [
                                ['title' => 'Pengajuan', 'completed' => true, 'active' => false],
                                ['title' => 'Review Pembimbing', 'completed' => $seminar->status_pembimbing == 'approved', 'active' => $seminar->status_pembimbing == 'pending'],
                                ['title' => 'Validasi Kaprodi', 'completed' => $seminar->status_kaprodi == 'approved', 'active' => $seminar->status_kaprodi == 'pending' && $seminar->status_pembimbing == 'approved'],
                                ['title' => 'Penjadwalan', 'completed' => in_array($seminar->status, ['scheduled', 'completed']), 'active' => $seminar->status == 'approved'],
                                ['title' => 'Pelaksanaan', 'completed' => $seminar->status == 'completed', 'active' => $seminar->status == 'scheduled']
                            ];
                        }
                        
                        foreach ($progress['steps'] as $index => $step): 
                            $step_class = 'step-pending';
                            $step_icon = 'fas fa-circle';
                            
                            if (isset($step['completed']) && $step['completed']) {
                                $step_class = 'step-completed';
                                $step_icon = 'fas fa-check';
                            } elseif (isset($step['active']) && $step['active']) {
                                $step_class = 'step-active';
                                $step_icon = 'fas fa-clock';
                            }
                            
                            // Check for rejection
                            if (($index == 1 && $seminar->status_pembimbing == 'rejected') || 
                                ($index == 2 && $seminar->status_kaprodi == 'rejected')) {
                                $step_class = 'step-rejected';
                                $step_icon = 'fas fa-times';
                            }
                        ?>
                            <div class="step-item">
                                <?php if ($index > 0): ?>
                                    <div class="step-line <?= isset($progress['steps'][$index-1]['completed']) && $progress['steps'][$index-1]['completed'] ? 'completed' : '' ?>"></div>
                                <?php endif; ?>
                                <div class="step-circle <?= $step_class ?>">
                                    <i class="<?= $step_icon ?>"></i>
                                </div>
                                <div class="step-title text-muted">
                                    <?= isset($step['title']) ? htmlspecialchars($step['title']) : 'Step ' . ($index + 1) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
                    <table class="info-table table table-borderless">
                        <tr>
                            <td><strong>ID Seminar:</strong></td>
                            <td>#<?= htmlspecialchars($seminar->id) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Judul Skripsi:</strong></td>
                            <td>
                                <div class="judul-wrap">
                                    <?= htmlspecialchars($seminar->judul_skripsi) ?>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($seminar->nama_mahasiswa)): ?>
                        <tr>
                            <td><strong>Mahasiswa:</strong></td>
                            <td><?= htmlspecialchars($seminar->nama_mahasiswa) ?> <?= !empty($seminar->nim) ? '(' . htmlspecialchars($seminar->nim) . ')' : '' ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($seminar->nama_pembimbing)): ?>
                        <tr>
                            <td><strong>Dosen Pembimbing:</strong></td>
                            <td><?= htmlspecialchars($seminar->nama_pembimbing) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-<?= $status_class ?> badge-lg">
                                    <i class="<?= $status_icon ?> mr-1"></i>
                                    <?= htmlspecialchars($status_text) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Pengajuan:</strong></td>
                            <td>
                                <?= $seminar->created_at ? date('d F Y H:i', strtotime($seminar->created_at)) : 'Tidak tersedia' ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Diupdate:</strong></td>
                            <td>
                                <?= $seminar->updated_at ? date('d F Y H:i', strtotime($seminar->updated_at)) : 'Tidak tersedia' ?>
                            </td>
                        </tr>
                        
                        <!-- Status Review Details -->
                        <tr>
                            <td><strong>Status Pembimbing:</strong></td>
                            <td>
                                <span class="badge badge-<?= $seminar->status_pembimbing == 'approved' ? 'success' : ($seminar->status_pembimbing == 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($seminar->status_pembimbing) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status Kaprodi:</strong></td>
                            <td>
                                <span class="badge badge-<?= $seminar->status_kaprodi == 'approved' ? 'success' : ($seminar->status_kaprodi == 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($seminar->status_kaprodi) ?>
                                </span>
                            </td>
                        </tr>
                        
                        <?php if (!empty($seminar->file_skripsi)): ?>
                        <tr>
                            <td><strong>File Skripsi:</strong></td>
                            <td>
                                <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                   class="btn btn-sm btn-success" target="_blank">
                                    <i class="fas fa-download mr-1"></i>Download File
                                </a>
                                <small class="text-muted d-block mt-1">
                                    File: <?= htmlspecialchars($seminar->file_skripsi) ?>
                                </small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (!empty($seminar->keterangan_mahasiswa)): ?>
                        <tr>
                            <td><strong>Keterangan:</strong></td>
                            <td>
                                <div class="judul-wrap">
                                    <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Help Text -->
            <div class="alert alert-help" role="alert">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Informasi:</strong> <?= $help_text ?>
            </div>

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
                    <?php if ($can_edit): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                           class="btn btn-warning btn-block btn-block-spacing">
                            <i class="fas fa-edit mr-2"></i>Edit Pengajuan
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->file_skripsi)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                           class="btn btn-success btn-block btn-block-spacing" target="_blank">
                            <i class="fas fa-download mr-2"></i>Download File
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($can_resubmit): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                           class="btn btn-danger btn-block btn-block-spacing">
                            <i class="fas fa-redo mr-2"></i>Ajukan Ulang
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                       class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                    </a>
                </div>
            </div>

            <!-- Status Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-<?= $status_class ?> text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="<?= $status_icon ?> mr-2"></i>
                        Status Saat Ini
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="<?= $status_icon ?> fa-3x text-<?= $status_class ?> mb-3"></i>
                        <h5 class="text-<?= $status_class ?>"><?= htmlspecialchars($status_text) ?></h5>
                    </div>
                    
                    <?php if (isset($progress['percentage'])): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Progress Keseluruhan</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-<?= $status_class ?>" style="width: <?= $progress['percentage'] ?>%">
                                <?= $progress['percentage'] ?>%
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                        <?= $help_text ?>
                    </small>
                </div>
            </div>

            <!-- Timeline Info (if needed in future) -->
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-clock mr-2"></i>
                        Timeline Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Pengajuan Dikirim</small><br>
                                <small><?= $seminar->created_at ? date('d M Y H:i', strtotime($seminar->created_at)) : '-' ?></small>
                            </div>
                        </div>
                        
                        <?php if ($seminar->status_pembimbing == 'approved'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Disetujui Pembimbing</small><br>
                                <small><?= $seminar->updated_at ? date('d M Y H:i', strtotime($seminar->updated_at)) : '-' ?></small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($seminar->status_kaprodi == 'approved'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Disetujui Kaprodi</small><br>
                                <small><?= $seminar->updated_at ? date('d M Y H:i', strtotime($seminar->updated_at)) : '-' ?></small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1rem;
}

.timeline-marker {
    position: absolute;
    left: -1.75rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -1.375rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

/* Responsive table untuk mobile */
@media (max-width: 768px) {
    .info-table td:first-child {
        width: 35%;
        font-size: 0.9rem;
    }
    
    .info-table td:last-child {
        width: 65%;
        font-size: 0.9rem;
    }
    
    .judul-wrap {
        font-size: 0.85rem;
        line-height: 1.3;
    }
}

@media (max-width: 576px) {
    .info-table {
        font-size: 0.8rem;
    }
    
    .info-table td {
        padding: 0.5rem 0.25rem;
        display: block;
        width: 100% !important;
        border-bottom: none;
    }
    
    .info-table tr {
        border-bottom: 1px solid #f8f9fa;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .info-table td:first-child {
        background-color: #f8f9fa;
        padding-bottom: 0.25rem;
        margin-bottom: 0.25rem;
        font-weight: 700;
    }
    
    .judul-wrap {
        font-size: 0.8rem;
        line-height: 1.2;
    }
}
</style>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle judul panjang
    $('.judul-wrap').each(function() {
        var $this = $(this);
        var text = $this.text().trim();
        
        // Check jika konten melebihi max-height dan perlu scroll
        if (this.scrollHeight > this.clientHeight) {
            $this.addClass('scrollable');
        }
        
        // Jika judul sangat panjang (lebih dari 100 karakter), tampilkan tooltip
        if (text.length > 100) {
            $this.tooltip({
                title: text,
                placement: 'top',
                trigger: 'hover focus',
                container: 'body',
                delay: { show: 500, hide: 100 }
            });
        }
    });
    
    // Smooth scroll for any anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 1000);
        }
    });
    
    // Auto refresh notification (optional)
    <?php if (in_array($seminar->status, ['submitted', 'review_pembimbing', 'review_kaprodi'])): ?>
    // Auto refresh untuk status yang sedang pending
    setTimeout(function() {
        console.log('Auto refresh check - Status masih pending');
        // Bisa ditambahkan AJAX refresh jika dibutuhkan
    }, 30000);
    <?php endif; ?>
});
</script>