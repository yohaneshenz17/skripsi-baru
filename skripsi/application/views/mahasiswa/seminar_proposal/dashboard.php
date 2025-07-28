<!-- ================================================================================
FILE: application/views/mahasiswa/seminar_proposal/dashboard.php
ENHANCED DASHBOARD DENGAN STATUS INFORMATIF
================================================================================ -->

<style>
/* Enhanced CSS untuk Status Display */
.submission-success-banner {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: none;
    border-left: 5px solid #28a745;
    border-radius: 8px;
    animation: slideInDown 0.6s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.status-icon {
    font-size: 2.5rem;
    animation: checkPulse 2s ease-in-out infinite;
}

@keyframes checkPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.progress-timeline {
    position: relative;
    margin: 25px 0;
}

.timeline-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.timeline-container::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 30px;
    right: 30px;
    height: 3px;
    background: linear-gradient(to right, #28a745 var(--progress, 0%), #e9ecef var(--progress, 0%));
    border-radius: 2px;
    z-index: 1;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
    flex: 1;
    max-width: 120px;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 3px solid #e9ecef;
    background: #fff;
    color: #6c757d;
}

.timeline-step.completed .step-circle {
    background: #28a745;
    border-color: #28a745;
    color: white;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.timeline-step.active .step-circle {
    background: #007bff;
    border-color: #007bff;
    color: white;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

.step-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: #2d3748;
}

.step-desc {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.2;
}

.status-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e1e8ed;
    transition: all 0.3s ease;
}

.status-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.status-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px 12px 0 0;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-submitted { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
.status-approved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.status-rejected { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.status-scheduled { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

.action-button {
    transition: all 0.3s ease;
    border-radius: 6px;
    font-weight: 600;
}

.action-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.info-tooltip {
    position: relative;
    cursor: help;
}

.info-tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    z-index: 1000;
}
</style>

<!-- ✅ ENHANCED SUCCESS NOTIFICATION -->
<?php if($this->session->flashdata('submission_success')): ?>
    <?php $success_data = $this->session->flashdata('submission_success'); ?>
    <div class="alert submission-success-banner alert-dismissible fade show" role="alert">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="status-icon text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="col">
                <h4 class="alert-heading mb-2">
                    ✅ <?= $success_data['action'] === 'updated' ? 'Pengajuan Berhasil Diperbarui!' : 'Pengajuan Berhasil Dikirim!' ?>
                </h4>
                <p class="mb-2 font-weight-medium"><?= $success_data['message'] ?></p>
                
                <div class="submission-meta mb-3">
                    <small class="text-muted">
                        <i class="fas fa-id-card mr-1"></i>
                        <strong>ID Pengajuan:</strong> #SP-<?= str_pad($success_data['seminar_id'], 4, '0', STR_PAD_LEFT) ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-clock mr-1"></i>
                        <strong>Waktu:</strong> <?= date('d F Y, H:i', strtotime($success_data['timestamp'])) ?> WIT
                    </small>
                </div>
                
                <div class="next-steps-preview p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-1">
                                <i class="fas fa-route text-primary mr-2"></i>
                                Langkah Selanjutnya:
                            </h6>
                            <p class="mb-0 text-muted small"><?= $success_data['next_step_description'] ?></p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-info">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                Estimasi: <?= $success_data['estimated_time'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- ✅ ENHANCED STATUS DISPLAY CARD -->
<?php if (isset($seminar_data) && $seminar_data): ?>
<div class="status-card mb-4">
    <div class="status-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Status Pengajuan Seminar Proposal
                </h5>
                <p class="mb-0 opacity-90">
                    ID: #SP-<?php echo str_pad($seminar_data->id, 4, '0', STR_PAD_LEFT); ?> | 
                    Diajukan: <?php echo date('d M Y, H:i', strtotime($seminar_data->created_at)); ?> WIT
                </p>
            </div>
            <div class="col-md-4 text-right">
                <?php
                $status_config = [
                    'draft' => ['class' => 'secondary', 'icon' => 'edit', 'text' => 'Draft'],
                    'submitted' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Menunggu Review'],
                    'review_pembimbing' => ['class' => 'info', 'icon' => 'search', 'text' => 'Sedang Direview'],
                    'review_kaprodi' => ['class' => 'primary', 'icon' => 'user-tie', 'text' => 'Review Kaprodi'],
                    'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Disetujui'],
                    'rejected' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Ditolak'],
                    'scheduled' => ['class' => 'info', 'icon' => 'calendar', 'text' => 'Terjadwal'],
                    'completed' => ['class' => 'success', 'icon' => 'flag-checkered', 'text' => 'Selesai']
                ];
                $current_status = $status_config[$seminar_data->status] ?? $status_config['draft'];
                ?>
                <span class="status-badge status-<?= $seminar_data->status ?>">
                    <i class="fas fa-<?= $current_status['icon'] ?> mr-1"></i>
                    <?= $current_status['text'] ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Progress Timeline -->
    <div class="card-body">
        <div class="progress-timeline" style="--progress: <?= $workflow_progress['percentage'] ?>%">
            <div class="timeline-container">
                <?php
                $timeline_steps = [
                    ['key' => 'preparation', 'title' => 'Persiapan', 'desc' => 'Menyiapkan berkas', 'icon' => '1'],
                    ['key' => 'submission', 'title' => 'Pengajuan', 'desc' => 'Berkas terkirim', 'icon' => '2'],
                    ['key' => 'pembimbing_review', 'title' => 'Review Dosen', 'desc' => 'Evaluasi pembimbing', 'icon' => '3'],
                    ['key' => 'kaprodi_review', 'title' => 'Review Kaprodi', 'desc' => 'Persetujuan akhir', 'icon' => '4'],
                    ['key' => 'scheduling', 'title' => 'Penjadwalan', 'desc' => 'Penentuan jadwal', 'icon' => '5'],
                    ['key' => 'completion', 'title' => 'Selesai', 'desc' => 'Seminar terlaksana', 'icon' => '✓']
                ];
                
                $current_step_key = '';
                switch($seminar_data->status) {
                    case 'draft': $current_step_key = 'preparation'; break;
                    case 'submitted': $current_step_key = 'submission'; break;
                    case 'review_pembimbing': $current_step_key = 'pembimbing_review'; break;
                    case 'review_kaprodi': $current_step_key = 'kaprodi_review'; break;
                    case 'approved': $current_step_key = 'scheduling'; break;
                    case 'scheduled': $current_step_key = 'scheduling'; break;
                    case 'completed': $current_step_key = 'completion'; break;
                    case 'rejected': $current_step_key = 'pembimbing_review'; break;
                }
                
                foreach($timeline_steps as $index => $step):
                    $is_completed = in_array($step['key'], $workflow_progress['completed_steps'] ?? []);
                    $is_active = $step['key'] === $current_step_key;
                ?>
                    <div class="timeline-step <?= $is_completed ? 'completed' : '' ?> <?= $is_active ? 'active' : '' ?>">
                        <div class="step-circle">
                            <?= $is_completed ? '✓' : $step['icon'] ?>
                        </div>
                        <div class="step-title"><?= $step['title'] ?></div>
                        <div class="step-desc"><?= $step['desc'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Current Status Details -->
        <div class="status-details mt-4">
            <?php if($seminar_data->status === 'submitted' || $seminar_data->status === 'review_pembimbing'): ?>
                <div class="alert alert-info border-left-info">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-info"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Menunggu Review Dosen Pembimbing
                            </h6>
                            <p class="mb-2">
                                Pengajuan Anda sedang direview oleh <strong><?= $proposal->nama_pembimbing ?></strong>. 
                                Harap bersabar menunggu hasil review.
                            </p>
                            <div class="d-flex align-items-center">
                                <div class="info-tooltip mr-3" data-tooltip="Estimasi berdasarkan rata-rata waktu review sebelumnya">
                                    <small class="text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        <strong>Estimasi:</strong> 3-5 hari kerja
                                    </small>
                                </div>
                                <div class="info-tooltip" data-tooltip="Anda akan mendapat email otomatis setelah review selesai">
                                    <small class="text-muted">
                                        <i class="fas fa-bell mr-1"></i>
                                        <strong>Notifikasi:</strong> Via email
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif($seminar_data->status === 'review_kaprodi'): ?>
                <div class="alert alert-primary border-left-primary">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-primary"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-crown mr-2"></i>
                                Sedang Direview oleh Kaprodi
                            </h6>
                            <p class="mb-2">
                                Dosen pembimbing telah menyetujui pengajuan Anda. Saat ini sedang menunggu persetujuan final dari Kaprodi.
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Proses ini biasanya memakan waktu 2-3 hari kerja.
                            </small>
                        </div>
                    </div>
                </div>
                
            <?php elseif($seminar_data->status === 'approved'): ?>
                <div class="alert alert-success border-left-success">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-thumbs-up mr-2"></i>
                                Pengajuan Disetujui!
                            </h6>
                            <p class="mb-2">
                                Selamat! Pengajuan seminar proposal Anda telah disetujui. 
                                Tim akademik akan segera menentukan jadwal seminar Anda.
                            </p>
                            <small class="text-success font-weight-bold">
                                <i class="fas fa-calendar-plus mr-1"></i>
                                Menunggu penjadwalan seminar
                            </small>
                        </div>
                    </div>
                </div>
                
            <?php elseif($seminar_data->status === 'rejected'): ?>
                <div class="alert alert-danger border-left-danger">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-times-circle mr-2"></i>
                                Pengajuan Perlu Diperbaiki
                            </h6>
                            <?php if(!empty($seminar_data->komentar_pembimbing)): ?>
                                <div class="feedback-box">
                                    <strong>Catatan dari Dosen Pembimbing:</strong>
                                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($seminar_data->komentar_pembimbing)) ?></p>
                                </div>
                            <?php endif; ?>
                            <p class="mb-0">
                                Silakan lakukan perbaikan sesuai catatan dan ajukan ulang pengajuan Anda.
                            </p>
                        </div>
                    </div>
                </div>
                
            <?php elseif($seminar_data->status === 'scheduled'): ?>
                <div class="alert alert-info border-left-info">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-info"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-calendar-day mr-2"></i>
                                Seminar Telah Dijadwalkan
                            </h6>
                            <?php if($seminar_data->tanggal_seminar && $seminar_data->jam_seminar): ?>
                                <div class="schedule-info bg-light p-3 rounded mb-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>📅 Tanggal:</strong><br>
                                            <?= date('d F Y', strtotime($seminar_data->tanggal_seminar)) ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>🕐 Waktu:</strong><br>
                                            <?= date('H:i', strtotime($seminar_data->jam_seminar)) ?> WIT
                                        </div>
                                        <div class="col-md-4">
                                            <strong>📍 Tempat:</strong><br>
                                            <?= $seminar_data->tempat_seminar ?: 'Akan diinformasikan' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <p class="mb-0">
                                Bersiaplah untuk presentasi seminar proposal Anda. Pastikan semua berkas sudah lengkap.
                            </p>
                        </div>
                    </div>
                </div>
                
            <?php elseif($seminar_data->status === 'completed'): ?>
                <div class="alert alert-success border-left-success">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-success"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                <i class="fas fa-flag-checkered mr-2"></i>
                                Seminar Selesai - Lanjut ke Penelitian
                            </h6>
                            <p class="mb-2">
                                Selamat! Anda telah berhasil menyelesaikan seminar proposal. 
                                Kini Anda dapat melanjutkan ke fase penelitian.
                            </p>
                            <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-microscope mr-1"></i>
                                Lanjut ke Fase Penelitian
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="text-right mt-4">
            <a href="<?= base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id) ?>" 
               class="btn btn-outline-primary action-button mr-2">
                <i class="fas fa-eye mr-1"></i>
                Lihat Detail Lengkap
            </a>
            
            <?php if(in_array($seminar_data->status, ['draft', 'rejected'])): ?>
                <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id) ?>" 
                   class="btn btn-primary action-button">
                    <i class="fas fa-edit mr-1"></i>
                    <?= $seminar_data->status === 'rejected' ? 'Ajukan Ulang' : 'Edit Pengajuan' ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ✅ ENHANCED NO SEMINAR STATE -->
<div class="row">
    <div class="col-md-8">
        <!-- Syarat dan Informasi -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 font-weight-600 text-primary">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Pengajuan Seminar Proposal
                </h6>
            </div>
            <div class="card-body">
                <?php if(isset($syarat_jurnal) && $syarat_jurnal['eligible']): ?>
                    <div class="alert alert-success">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="col">
                                <h6 class="mb-1">✅ Syarat Terpenuhi - Siap Mengajukan!</h6>
                                <p class="mb-2">
                                    Anda telah menyelesaikan <strong><?= $syarat_jurnal['total_validated'] ?></strong> 
                                    dari <strong><?= $syarat_jurnal['minimum_required'] ?></strong> jurnal bimbingan yang diperlukan.
                                </p>
                                <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id) ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Ajukan Seminar Proposal Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div class="col">
                                <h6 class="mb-1">⚠️ Syarat Belum Terpenuhi</h6>
                                <p class="mb-2">
                                    Anda baru menyelesaikan <strong><?= $syarat_jurnal['total_validated'] ?></strong> 
                                    dari <strong><?= $syarat_jurnal['minimum_required'] ?></strong> jurnal bimbingan yang diperlukan.
                                </p>
                                <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-plus mr-1"></i>
                                    Tambah Jurnal Bimbingan
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 font-weight-600 text-primary">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Progress Keseluruhan
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="progress-circle mb-3">
                    <div class="progress-value"><?= $workflow_progress['percentage'] ?>%</div>
                </div>
                <p class="text-muted small mb-0">
                    <?= $workflow_progress['current_step'] ?>
                </p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0 font-weight-600 text-primary">
                    <i class="fas fa-bolt mr-2"></i>
                    Akses Cepat
                </h6>
            </div>
            <div class="card-body">
                <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-outline-success btn-sm btn-block mb-2">
                    <i class="fas fa-book mr-1"></i>
                    Jurnal Bimbingan
                </a>
                <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-outline-info btn-sm btn-block mb-2">
                    <i class="fas fa-file-alt mr-1"></i>
                    Lihat Proposal
                </a>
                <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-outline-secondary btn-sm btn-block">
                    <i class="fas fa-tachometer-alt mr-1"></i>
                    Dashboard Utama
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Auto-dismiss success notification after 10 seconds
    setTimeout(function() {
        $('.submission-success-banner').fadeOut('slow');
    }, 10000);
    
    // Smooth scroll to status card if redirected from submission
    if (window.location.search.includes('submission=success')) {
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $('.status-card').offset().top - 100
            }, 1000);
        }, 500);
    }
});
</script>

<!-- Additional CSS for progress circle -->
<style>
.progress-circle {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.progress-circle::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: conic-gradient(#007bff 0deg <?= $workflow_progress['percentage'] * 3.6 ?>deg, #e9ecef <?= $workflow_progress['percentage'] * 3.6 ?>deg 360deg);
}

.progress-circle::after {
    content: '';
    position: absolute;
    top: 10px;
    left: 10px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
}

.progress-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.2rem;
    font-weight: bold;
    color: #007bff;
    z-index: 1;
}
</style>