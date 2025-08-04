<?php
/**
 * File: application/views/mahasiswa/seminar_skripsi/detail.php
 * ENHANCED VERSION - Support Full Workflow
 * 
 * WORKFLOW SUPPORT:
 * ✅ Submit > Dosen Approve > Kaprodi Approve > Jadwal > Penilaian
 * ✅ Submit > Reject > Resubmit > Approve > Jadwal > Penilaian
 * ✅ Tampilan jadwal seminar setelah disetujui
 * ✅ Link ke hasil penilaian jika sudah ada
 * ✅ Enhanced progress tracking
 * ✅ Multiple file download support
 */

// ===============================================================
// DEFENSIVE PROGRAMMING: Pastikan semua variables ada
// ===============================================================

$seminar = isset($seminar) ? $seminar : new stdClass();
$progress_data = isset($progress_data) ? $progress_data : ['percentage' => 0, 'steps' => []];
$progress = isset($progress) ? $progress : $progress_data; // Backward compatibility
$can_edit = isset($can_edit) ? $can_edit : false;
$can_resubmit = isset($can_resubmit) ? $can_resubmit : false;

// NEW: Workflow support variables
$jadwal_seminar = isset($jadwal_seminar) ? $jadwal_seminar : null;
$has_penilaian = isset($has_penilaian) ? $has_penilaian : false;
$penilaian_data = isset($penilaian_data) ? $penilaian_data : null;

// ===============================================================
// FIELD VALIDATION: Pastikan semua field seminar object ada
// ===============================================================

if (!isset($seminar->id)) $seminar->id = 0;
if (!isset($seminar->judul_skripsi)) $seminar->judul_skripsi = '';
if (!isset($seminar->status)) $seminar->status = 'unknown';
if (!isset($seminar->proposal_id)) $seminar->proposal_id = 0;
if (!isset($seminar->file_skripsi)) $seminar->file_skripsi = '';
if (!isset($seminar->surat_keterangan_penelitian)) $seminar->surat_keterangan_penelitian = ''; // NEW
if (!isset($seminar->created_at)) $seminar->created_at = '';
if (!isset($seminar->updated_at)) $seminar->updated_at = '';
if (!isset($seminar->status_pembimbing)) $seminar->status_pembimbing = 'pending';
if (!isset($seminar->status_kaprodi)) $seminar->status_kaprodi = 'pending';
if (!isset($seminar->keterangan_mahasiswa)) $seminar->keterangan_mahasiswa = '';
if (!isset($seminar->nama_mahasiswa)) $seminar->nama_mahasiswa = '';
if (!isset($seminar->nim)) $seminar->nim = '';
if (!isset($seminar->nama_pembimbing)) $seminar->nama_pembimbing = '';
if (!isset($seminar->proposal_judul)) $seminar->proposal_judul = '';

// ===============================================================
// ENHANCED STATUS LOGIC: Mendukung workflow lengkap
// ===============================================================

$status_text = 'Status tidak diketahui';
$status_class = 'secondary';
$status_icon = 'fas fa-question';

// Enhanced status logic dengan workflow support
switch ($seminar->status) {
    case 'draft':
        $status_text = 'Draft - Belum Dikirim';
        $status_class = 'secondary';
        $status_icon = 'fas fa-edit';
        break;
    case 'submitted':
        // Check specific review status
        if ($seminar->status_pembimbing == 'pending') {
            $status_text = 'Menunggu Review Dosen Pembimbing';
            $status_class = 'warning';
            $status_icon = 'fas fa-clock';
        } elseif ($seminar->status_pembimbing == 'approved' && $seminar->status_kaprodi == 'pending') {
            $status_text = 'Menunggu Validasi Kaprodi';
            $status_class = 'info';
            $status_icon = 'fas fa-user-check';
        }
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
        $status_class = 'primary';
        $status_icon = 'fas fa-calendar-check';
        break;
    case 'completed':
        if ($has_penilaian) {
            $status_text = 'Seminar Selesai - Nilai Tersedia';
            $status_class = 'success';
            $status_icon = 'fas fa-trophy';
        } else {
            $status_text = 'Seminar Selesai - Menunggu Nilai';
            $status_class = 'info';
            $status_icon = 'fas fa-clock';
        }
        break;
    default:
        $status_text = 'Status: ' . ucfirst($seminar->status);
        $status_class = 'secondary';
        $status_icon = 'fas fa-question';
}

// Override dengan status pembimbing/kaprodi jika ditolak
if ($seminar->status_pembimbing == 'rejected') {
    $status_text = 'Ditolak oleh Dosen Pembimbing';
    $status_class = 'danger';
    $status_icon = 'fas fa-times-circle';
} elseif ($seminar->status_kaprodi == 'rejected') {
    $status_text = 'Ditolak oleh Kaprodi';
    $status_class = 'danger';
    $status_icon = 'fas fa-times-circle';
}

// ===============================================================
// ENHANCED PROGRESS CALCULATION
// ===============================================================

if (!isset($progress['percentage']) || $progress['percentage'] == 0) {
    // Enhanced calculation
    $percentage = 10; // Default: pengajuan dibuat
    
    if ($seminar->status == 'submitted') $percentage = 25;
    if ($seminar->status_pembimbing == 'approved') $percentage = 50;
    if ($seminar->status_kaprodi == 'approved') $percentage = 70;
    if ($seminar->status == 'scheduled') $percentage = 85;
    if ($seminar->status == 'completed' && !$has_penilaian) $percentage = 95;
    if ($seminar->status == 'completed' && $has_penilaian) $percentage = 100;
    
    $progress['percentage'] = $percentage;
}

// Enhanced steps dengan workflow support
if (!isset($progress['steps']) || empty($progress['steps'])) {
    $progress['steps'] = [
        [
            'title' => 'Pengajuan',
            'completed' => in_array($seminar->status, ['submitted', 'approved', 'scheduled', 'completed']),
            'active' => $seminar->status == 'draft',
            'icon' => 'fas fa-paper-plane'
        ],
        [
            'title' => 'Review Pembimbing',
            'completed' => $seminar->status_pembimbing == 'approved',
            'active' => $seminar->status == 'submitted' && $seminar->status_pembimbing == 'pending',
            'rejected' => $seminar->status_pembimbing == 'rejected',
            'icon' => 'fas fa-user-graduate'
        ],
        [
            'title' => 'Validasi Kaprodi',
            'completed' => $seminar->status_kaprodi == 'approved',
            'active' => $seminar->status_pembimbing == 'approved' && $seminar->status_kaprodi == 'pending',
            'rejected' => $seminar->status_kaprodi == 'rejected',
            'icon' => 'fas fa-user-tie'
        ],
        [
            'title' => 'Penjadwalan',
            'completed' => in_array($seminar->status, ['scheduled', 'completed']),
            'active' => $seminar->status == 'approved',
            'icon' => 'fas fa-calendar-alt'
        ],
        [
            'title' => 'Pelaksanaan',
            'completed' => $seminar->status == 'completed',
            'active' => $seminar->status == 'scheduled',
            'icon' => 'fas fa-presentation'
        ],
        [
            'title' => 'Penilaian',
            'completed' => $has_penilaian,
            'active' => $seminar->status == 'completed' && !$has_penilaian,
            'icon' => 'fas fa-star'
        ]
    ];
}

// ===============================================================
// HELPER TEXT: Text bantuan berdasarkan status
// ===============================================================

$help_text = '';
switch ($seminar->status) {
    case 'submitted':
        if ($seminar->status_pembimbing == 'pending') {
            $help_text = 'Pengajuan Anda sedang dalam proses review oleh dosen pembimbing. Mohon tunggu notifikasi selanjutnya.';
        } elseif ($seminar->status_pembimbing == 'approved' && $seminar->status_kaprodi == 'pending') {
            $help_text = 'Pengajuan telah disetujui pembimbing dan sedang menunggu validasi dari Kaprodi.';
        }
        break;
    case 'approved':
        $help_text = 'Pengajuan telah disetujui lengkap dan sedang menunggu penjadwalan. Staf akan menghubungi Anda untuk koordinasi jadwal.';
        break;
    case 'rejected':
        $help_text = 'Pengajuan ditolak. Silakan perbaiki sesuai catatan dan ajukan ulang melalui tombol "Ajukan Ulang".';
        break;
    case 'scheduled':
        $help_text = 'Seminar telah dijadwalkan. Bersiaplah untuk presentasi dan pastikan semua berkas sudah lengkap.';
        break;
    case 'completed':
        if ($has_penilaian) {
            $help_text = 'Selamat! Seminar skripsi telah selesai dan hasil penilaian sudah tersedia. Anda dapat melihat nilai dan catatan dari dosen penguji.';
        } else {
            $help_text = 'Seminar telah selesai dilaksanakan. Hasil penilaian akan segera dipublikasikan oleh tim penguji.';
        }
        break;
    default:
        $help_text = 'Status pengajuan sedang diproses oleh sistem. Hubungi admin jika ada pertanyaan.';
}

if ($seminar->status_pembimbing == 'rejected') {
    $help_text = 'Pengajuan ditolak oleh dosen pembimbing. Silakan perbaiki berdasarkan masukan yang diberikan dan ajukan ulang.';
} elseif ($seminar->status_kaprodi == 'rejected') {
    $help_text = 'Pengajuan ditolak oleh Kaprodi. Silakan perbaiki berdasarkan masukan yang diberikan dan ajukan ulang.';
}
?>

<style>
.progress-step-horizontal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    position: relative;
}

.step-item {
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 2;
}

.step-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-weight: bold;
    color: white;
    font-size: 1.1rem;
    position: relative;
    transition: all 0.3s ease;
}

.step-title {
    font-size: 11px;
    font-weight: 600;
    margin-top: 0.5rem;
}

.step-completed {
    background: linear-gradient(135deg, #28a745, #20c997);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.step-active {
    background: linear-gradient(135deg, #007bff, #0056b3);
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    animation: pulse 2s infinite;
}

.step-pending {
    background-color: #6c757d;
}

.step-rejected {
    background: linear-gradient(135deg, #dc3545, #c82333);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.progress-line {
    position: absolute;
    top: 22px;
    left: 0;
    right: 0;
    height: 3px;
    background: #e9ecef;
    z-index: 1;
}

.progress-line-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.5s ease;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
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

.file-download-group .btn {
    margin-bottom: 0.25rem;
}

.jadwal-highlight {
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.countdown-mini {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: bold;
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
                   class="btn btn-warning btn-sm mr-2">
                    <i class="fas fa-edit mr-1"></i> Edit Pengajuan
                </a>
            <?php endif; ?>
            <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" class="btn btn-secondary btn-sm">
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
            
            <!-- ENHANCED: Jadwal Seminar (jika sudah dijadwalkan) -->
            <?php if ($jadwal_seminar && $seminar->status == 'scheduled'): ?>
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header jadwal-highlight">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Jadwal Seminar Sudah Ditetapkan
                            </h6>
                            <small class="text-muted">Seminar akan dilaksanakan sesuai jadwal berikut</small>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/jadwal/' . $seminar->id) ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <i class="fas fa-calendar text-primary mr-2"></i>
                                <strong>Tanggal:</strong> 
                                <?= date('l, d F Y', strtotime($jadwal_seminar->tanggal_seminar)) ?>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-clock text-info mr-2"></i>
                                <strong>Waktu:</strong> 
                                <?= date('H:i', strtotime($jadwal_seminar->jam_seminar)) ?> WIB
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                <strong>Tempat:</strong> 
                                <?= htmlspecialchars($jadwal_seminar->tempat_seminar) ?>
                            </div>
                            <?php if (strtotime($jadwal_seminar->tanggal_seminar) > time()): ?>
                                <div class="countdown-mini text-warning">
                                    <i class="fas fa-hourglass-half mr-1"></i>
                                    <span id="countdown-text">Menghitung...</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ENHANCED: Hasil Penilaian (jika sudah ada) -->
            <?php if ($has_penilaian && $penilaian_data): ?>
            <div class="card shadow mb-4 border-left-success">
                <div class="card-header bg-success text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-star mr-2"></i>
                                Hasil Penilaian Tersedia
                            </h6>
                            <small>Seminar telah selesai dan dinilai oleh tim penguji</small>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="<?= base_url('mahasiswa/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                               class="btn btn-light btn-sm">
                                <i class="fas fa-eye mr-1"></i> Lihat Penilaian
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="nilai-preview bg-primary text-white p-3 rounded">
                                <h3 class="mb-1"><?= number_format($penilaian_data->nilai_akhir, 1) ?></h3>
                                <small>Nilai Akhir</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nilai-preview bg-success text-white p-3 rounded">
                                <h3 class="mb-1"><?= $penilaian_data->nilai_huruf ?></h3>
                                <small>Grade</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nilai-preview bg-<?= $penilaian_data->rekomendasi == 'lulus_tanpa_revisi' ? 'success' : 'warning' ?> text-white p-3 rounded">
                                <small>
                                    <?php
                                    switch($penilaian_data->rekomendasi) {
                                        case 'lulus_tanpa_revisi': echo 'LULUS'; break;
                                        case 'lulus_dengan_revisi_minor': echo 'LULUS + REVISI MINOR'; break;
                                        case 'lulus_dengan_revisi_mayor': echo 'LULUS + REVISI MAYOR'; break;
                                        default: echo 'TIDAK LULUS';
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
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
                            <span class="text-primary font-weight-bold"><?= $progress['percentage'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success progress-bar-striped" 
                                 role="progressbar" 
                                 style="width: <?= $progress['percentage'] ?>%;" 
                                 aria-valuenow="<?= $progress['percentage'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Progress Steps -->
                    <div class="progress-step-horizontal">
                        <div class="progress-line">
                            <div class="progress-line-fill" style="width: <?= max(0, ($progress['percentage'] - 10) * 100 / 90) ?>%;"></div>
                        </div>
                        
                        <?php foreach ($progress['steps'] as $index => $step): 
                            $step_class = 'step-pending';
                            $step_icon = $step['icon'] ?? 'fas fa-circle';
                            
                            if (isset($step['completed']) && $step['completed']) {
                                $step_class = 'step-completed';
                                $step_icon = 'fas fa-check';
                            } elseif (isset($step['active']) && $step['active']) {
                                $step_class = 'step-active';
                            } elseif (isset($step['rejected']) && $step['rejected']) {
                                $step_class = 'step-rejected';
                                $step_icon = 'fas fa-times';
                            }
                        ?>
                            <div class="step-item">
                                <div class="step-circle <?= $step_class ?>" title="<?= htmlspecialchars($step['title']) ?>">
                                    <i class="<?= $step_icon ?>"></i>
                                </div>
                                <div class="step-title text-muted">
                                    <?= htmlspecialchars($step['title']) ?>
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
                                    <?= htmlspecialchars($seminar->judul_skripsi ?: $seminar->proposal_judul) ?>
                                </div>
                                <?php if (!empty($seminar->judul_skripsi) && $seminar->judul_skripsi !== $seminar->proposal_judul): ?>
                                    <small class="text-info">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Judul diperbarui dari proposal
                                    </small>
                                <?php endif; ?>
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
                        
                        <!-- ENHANCED: Multiple File Downloads -->
                        <?php if (!empty($seminar->file_skripsi) || !empty($seminar->surat_keterangan_penelitian)): ?>
                        <tr>
                            <td><strong>File Tersedia:</strong></td>
                            <td>
                                <div class="file-download-group">
                                    <?php if (!empty($seminar->file_skripsi)): ?>
                                        <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" 
                                           class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-download mr-1"></i>File Skripsi
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                                        <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/surat') ?>" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-certificate mr-1"></i>Surat Penelitian
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2">
                                    <?php if (!empty($seminar->file_skripsi)): ?>
                                        <small class="text-muted d-block">Skripsi: <?= htmlspecialchars($seminar->file_skripsi) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                                        <small class="text-muted d-block">Surat: <?= htmlspecialchars($seminar->surat_keterangan_penelitian) ?></small>
                                    <?php endif; ?>
                                </div>
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
                    <!-- ENHANCED: Workflow-aware actions -->
                    
                    <?php if ($jadwal_seminar && $seminar->status == 'scheduled'): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/jadwal/' . $seminar->id) ?>" 
                           class="btn btn-primary btn-block btn-block-spacing">
                            <i class="fas fa-calendar-check mr-2"></i>Lihat Jadwal Seminar
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($has_penilaian): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                           class="btn btn-success btn-block btn-block-spacing">
                            <i class="fas fa-star mr-2"></i>Lihat Hasil Penilaian
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($can_edit): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/pengajuan/' . $seminar->proposal_id) ?>" 
                           class="btn btn-warning btn-block btn-block-spacing">
                            <i class="fas fa-edit mr-2"></i>Edit Pengajuan
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->file_skripsi)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" 
                           class="btn btn-success btn-block btn-block-spacing" target="_blank">
                            <i class="fas fa-download mr-2"></i>Download Skripsi
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/surat') ?>" 
                           class="btn btn-info btn-block btn-block-spacing" target="_blank">
                            <i class="fas fa-certificate mr-2"></i>Download Surat Penelitian
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($can_resubmit): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/resubmit/' . $seminar->id) ?>" 
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
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Progress Keseluruhan</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-<?= $status_class ?>" style="width: <?= $progress['percentage'] ?>%">
                                <?= $progress['percentage'] ?>%
                            </div>
                        </div>
                    </div>
                    
                    <small class="text-muted">
                        <?= $help_text ?>
                    </small>
                </div>
            </div>

            <!-- Enhanced Timeline Info -->
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

                        <?php if ($jadwal_seminar): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Dijadwalkan</small><br>
                                <small><?= date('d M Y H:i', strtotime($jadwal_seminar->tanggal_seminar . ' ' . $jadwal_seminar->jam_seminar)) ?></small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($has_penilaian): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Nilai Dipublikasi</small><br>
                                <small><?= $penilaian_data ? date('d M Y H:i', strtotime($penilaian_data->published_at)) : '-' ?></small>
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

.nilai-preview {
    transition: transform 0.2s;
}

.nilai-preview:hover {
    transform: scale(1.05);
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

    .progress-step-horizontal {
        flex-wrap: wrap;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .step-title {
        font-size: 10px;
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
    
    // ENHANCED: Countdown untuk jadwal seminar
    <?php if ($jadwal_seminar && strtotime($jadwal_seminar->tanggal_seminar) > time()): ?>
    function updateCountdown() {
        var seminarDate = new Date('<?= date('Y-m-d H:i:s', strtotime($jadwal_seminar->tanggal_seminar . ' ' . $jadwal_seminar->jam_seminar)) ?>').getTime();
        var now = new Date().getTime();
        var distance = seminarDate - now;
        
        if (distance > 0) {
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            
            if (days > 0) {
                $('#countdown-text').html(days + ' hari ' + hours + ' jam lagi');
            } else if (hours > 0) {
                $('#countdown-text').html(hours + ' jam lagi');
            } else {
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                $('#countdown-text').html(minutes + ' menit lagi');
            }
        } else {
            $('#countdown-text').html('Sedang berlangsung!').addClass('text-success');
        }
    }
    
    updateCountdown();
    setInterval(updateCountdown, 60000); // Update setiap menit
    <?php endif; ?>
    
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
    
    // Auto refresh notification untuk status yang sedang pending
    <?php if (in_array($seminar->status, ['submitted']) && ($seminar->status_pembimbing == 'pending' || $seminar->status_kaprodi == 'pending')): ?>
    setTimeout(function() {
        console.log('Auto refresh check - Status masih pending');
        // Bisa ditambahkan AJAX refresh jika dibutuhkan
    }, 30000);
    <?php endif; ?>
});
</script>