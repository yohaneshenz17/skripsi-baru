<?php
$id_user = $this->session->userdata('id');
$verifikasi = '';
$dataUser = $this->db->get_where('mahasiswa', array('id' => $id_user))->result();
foreach ($dataUser as $du) {
    $verifikasi = $du->status;
}
?>
<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', "Dashboard") ?>

<?php $this->app->section() ?>
<?php if ($verifikasi == 1) { ?>
    
    <!-- Welcome Message -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="text-white mb-0">👋 Selamat Datang, <?= $this->session->userdata('nama') ?>!</h3>
                            <p class="text-white mt-2 mb-0 opacity-8">
                                Kelola progress tugas akhir Anda dengan mudah melalui dashboard ini.
                            </p>
                            <p class="text-white-50 mt-1 mb-0 small">
                                <strong>Status saat ini:</strong> <span id="current-stage">Usulan Proposal</span>
                                <span class="ml-3"><strong>Progress:</strong> <span id="progress-percentage">0</span>%</span>
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                                <i class="ni ni-hat-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-xl-8">
            
            <!-- Pengumuman Tahapan Skripsi (TETAP) -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-uppercase text-muted mb-0">
                        <i class="ni ni-bell-55 text-primary"></i> Pengumuman Tahapan Skripsi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 10%">No</th>
                                    <th style="width: 30%">Tahapan</th>
                                    <th style="width: 25%">Tanggal Deadline</th>
                                    <th style="width: 35%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Ambil data pengumuman tahapan
                                $pengumuman = $this->db->get_where('pengumuman_tahapan', ['aktif' => '1'])->result();
                                
                                if (!empty($pengumuman)) {
                                    foreach($pengumuman as $p): 
                                        // Hitung status deadline
                                        $deadline_date = new DateTime($p->tanggal_deadline);
                                        $today = new DateTime();
                                        $interval = $today->diff($deadline_date);
                                        
                                        if ($today > $deadline_date) {
                                            $status_class = 'text-danger';
                                            $status_text = 'Telah lewat';
                                        } elseif ($interval->days <= 7) {
                                            $status_class = 'text-warning';
                                            $status_text = $interval->days . ' hari lagi';
                                        } else {
                                            $status_class = 'text-success';
                                            $status_text = $interval->days . ' hari lagi';
                                        }
                                ?>
                                <tr>
                                    <td><span class="badge badge-soft-primary"><?= $p->no ?></span></td>
                                    <td><strong><?= $p->tahapan ?></strong></td>
                                    <td>
                                        <span class="<?= $status_class ?>">
                                            <strong><?= date('d M Y', strtotime($p->tanggal_deadline)) ?></strong>
                                        </span>
                                        <br><small class="<?= $status_class ?>"><?= $status_text ?></small>
                                    </td>
                                    <td><?= $p->keterangan ? $p->keterangan : '-' ?></td>
                                </tr>
                                <?php 
                                    endforeach; 
                                } else {
                                    echo '<tr><td colspan="4" class="text-center text-muted">Belum ada pengumuman tahapan</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Progress Workflow -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">
                                <i class="ni ni-chart-pie-35 text-success"></i> Progress Tahapan Tugas Akhir
                            </h5>
                            <p class="text-sm mb-0 text-muted">Tracking progres berdasarkan workflow sistem</p>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-primary badge-lg" id="progress-badge">
                                0% Selesai
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="progress-wrapper mb-4">
                        <div class="progress-info">
                            <div class="progress-label">
                                <span>Progress Keseluruhan</span>
                            </div>
                            <div class="progress-percentage">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-gradient-success" role="progressbar" 
                                 style="width: 0%" id="progress-bar"></div>
                        </div>
                    </div>

                    <!-- Workflow Steps -->
                    <div class="row" id="workflow-steps">
                        <!-- Steps akan di-load via AJAX -->
                        <div class="col-12 text-center">
                            <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="text-muted mt-2">Memuat progress workflow...</p>
                        </div>
                    </div>

                    <!-- Current Stage Info -->
                    <div class="alert alert-info mt-3" id="current-stage-info" style="display: none;">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ni ni-compass-04 fa-2x"></i>
                            </div>
                            <div class="col">
                                <h6 class="alert-heading mb-1">Tahap Saat Ini: <span id="stage-title">-</span></h6>
                                <p class="mb-0 text-sm" id="stage-description">
                                    Loading...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-uppercase text-muted mb-0">
                        <i class="ni ni-time-alarm text-warning"></i> Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body" id="recent-activities">
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Memuat aktivitas...</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-xl-4">

            <!-- Notifikasi Terbaru -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-uppercase text-muted mb-0">
                        <i class="ni ni-notification-70 text-danger"></i> Notifikasi Terbaru
                    </h5>
                </div>
                <div class="card-body" id="notifikasi-container">
                    <div class="text-center py-3">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Memuat notifikasi...</p>
                    </div>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="row">
                <div class="col-md-6 col-xl-12">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Bimbingan</h5>
                                    <span class="h2 font-weight-bold mb-0" id="total-bimbingan">0</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                                        <i class="ni ni-books"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <span class="text-info mr-2">
                                    <i class="ni ni-calendar-grid-58"></i> <span id="bimbingan-bulan-ini">0</span>
                                </span>
                                <span class="text-nowrap">bulan ini</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-12">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Proposal</h5>
                                    <span class="h2 font-weight-bold mb-0 total-proposal">0</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                                        <i class="ni ni-single-copy-04"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-sm">
                                <a href="<?= base_url() ?>mahasiswa/proposal" class="text-success mr-2">
                                    <i class="fa fa-arrow-right"></i> Kelola Proposal
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-uppercase text-muted mb-0">
                        <i class="ni ni-button-power text-primary"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body" id="quick-actions">
                    <a href="<?= base_url() ?>mahasiswa/proposal" class="btn btn-primary btn-block mb-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ni ni-single-copy-04"></i>
                            </div>
                            <div class="col text-left">
                                <span class="font-weight-bold">Usulan Proposal</span><br>
                                <small class="opacity-8">Kelola proposal skripsi</small>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url() ?>mahasiswa/bimbingan" class="btn btn-info btn-block mb-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ni ni-books"></i>
                            </div>
                            <div class="col text-left">
                                <span class="font-weight-bold">Bimbingan</span><br>
                                <small class="opacity-8">Jurnal bimbingan</small>
                            </div>
                        </div>
                    </a>
                    <a href="<?= base_url() ?>mahasiswa/seminar" class="btn btn-success btn-block mb-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ni ni-calendar-grid-58"></i>
                            </div>
                            <div class="col text-left">
                                <span class="font-weight-bold">Seminar</span><br>
                                <small class="opacity-8">Seminar proposal</small>
                            </div>
                        </div>
                    </a>
                    <!-- Kontak Form Button -->
                    <a href="<?= base_url() ?>mahasiswa/kontak" class="btn btn-outline-primary btn-block">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ni ni-email-83"></i>
                            </div>
                            <div class="col text-left">
                                <span class="font-weight-bold">Kontak Form</span><br>
                                <small class="opacity-8">Kirim pesan</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
    
<?php } else { ?>
    <!-- Jika mahasiswa belum terverifikasi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ni ni-lock-circle-open fa-4x text-warning mb-4"></i>
                    <h3 class="text-warning">Akun Belum Terverifikasi</h3>
                    <p class="text-muted mb-4">
                        Akun Anda sedang dalam proses verifikasi oleh admin. 
                        Silakan hubungi admin untuk informasi lebih lanjut.
                    </p>
                    <a href="<?= base_url('mahasiswa/profil') ?>" class="btn btn-primary">
                        Kelola Profil
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<style>
/* ============================================
   WORKFLOW STEP COLOR FIXES - KONTRAS YANG BAIK
============================================ */

.workflow-step {
    transition: all 0.3s ease;
}

.workflow-step:hover {
    transform: translateY(-2px);
}

.badge-soft-primary {
    color: #5e72e4;
    background-color: rgba(94, 114, 228, 0.1);
}

.progress-wrapper {
    position: relative;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-one-side .timeline-block:not(:last-child) {
    padding-bottom: 1rem;
}

.timeline-one-side .timeline-step {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-right: 1rem;
}

/* Status Completed - Hijau */
.workflow-icon-completed {
    background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
    color: white !important;
}

.workflow-text-completed {
    color: #2dce89 !important;
    font-weight: 600 !important;
}

/* Status Active - Biru */
.workflow-icon-active {
    background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    color: white !important;
    box-shadow: 0 4px 6px rgba(94, 114, 228, 0.4) !important;
    animation: pulse-primary 2s infinite;
}

.workflow-text-active {
    color: #5e72e4 !important;
    font-weight: 700 !important;
}

/* Status Pending - Abu-abu dengan kontras yang baik */
.workflow-icon-pending {
    background: linear-gradient(87deg, #8898aa 0, #6c757d 100%) !important;
    color: white !important;
    border: 2px solid #dee2e6;
}

.workflow-text-pending {
    color: #6c757d !important; /* Warna abu-abu gelap yang kontras */
    font-weight: 500 !important;
}

/* Hover effects */
.workflow-step:hover .workflow-icon-pending {
    background: linear-gradient(87deg, #6c757d 0, #5a6169 100%) !important;
    transform: scale(1.05);
}

.workflow-step:hover .workflow-text-pending {
    color: #495057 !important;
}

.workflow-step:hover .workflow-icon-active {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(94, 114, 228, 0.6) !important;
}

.workflow-step:hover .workflow-icon-completed {
    transform: scale(1.05);
}

/* Animation untuk status active */
@keyframes pulse-primary {
    0% {
        box-shadow: 0 4px 6px rgba(94, 114, 228, 0.4);
    }
    50% {
        box-shadow: 0 6px 12px rgba(94, 114, 228, 0.6);
    }
    100% {
        box-shadow: 0 4px 6px rgba(94, 114, 228, 0.4);
    }
}

/* Badge color overrides untuk konsistensi */
.workflow-step .badge-secondary {
    background-color: #6c757d !important;
    color: white !important;
    border: 1px solid #5a6169;
}

.workflow-step .badge-primary {
    background-color: #5e72e4 !important;
    color: white !important;
    box-shadow: 0 2px 4px rgba(94, 114, 228, 0.3);
}

.workflow-step .badge-success {
    background-color: #2dce89 !important;
    color: white !important;
}

/* Override untuk memastikan tidak ada warna putih pada text pending */
.text-secondary.workflow-text-pending {
    color: #6c757d !important;
}

.text-muted.workflow-text-pending {
    color: #6c757d !important;
}

/* Pastikan icon selalu putih di dalam circle */
.workflow-icon-pending i,
.workflow-icon-active i,
.workflow-icon-completed i {
    color: white !important;
}

/* Mobile responsive */
@media (max-width: 767.98px) {
    .workflow-text-pending,
    .workflow-text-active,
    .workflow-text-completed {
        font-size: 0.875rem !important;
    }
    
    .workflow-icon-pending,
    .workflow-icon-active,
    .workflow-icon-completed {
        width: 40px !important;
        height: 40px !important;
    }
}
</style>

<script>
// FIXED: Definisi base_url dan fungsi call
var base_url = '<?= base_url() ?>';

// FIXED: Fungsi call() yang benar
function call(url, data = null) {
    return $.ajax({
        url: base_url + url,
        type: data ? 'POST' : 'GET',
        data: data,
        dataType: 'json'
    });
}

$(document).ready(function() {
    console.log('Dashboard script loaded'); // Debug
    
    // Load data mahasiswa (existing functionality)
    call('api/mahasiswa/detail/<?= $this->session->userdata('id') ?>').done(function(req) {
        if (req.data) {
            $('.total-proposal').html(req.data.proposal.length);
        }
    }).fail(function() {
        console.log('Error loading mahasiswa data');
        $('.total-proposal').html('0');
    });

    // Load workflow progress
    loadWorkflowProgress();
    
    // Load notifikasi
    loadNotifikasi();
    
    // Load statistik bimbingan
    loadStatistikBimbingan();
    
    // Load recent activities
    loadRecentActivities();
});

// FIXED: loadWorkflowProgress dengan warna yang kontras
function loadWorkflowProgress() {
    console.log('Loading workflow progress...'); // Debug
    
    call('mahasiswa/dashboard/get_workflow_progress').done(function(response) {
        console.log('Workflow response:', response); // Debug
        
        if (response.status === 'success') {
            const data = response.data;
            
            // Update progress bar
            $('#progress-percentage').text(Math.round(data.progress_percentage));
            $('#progress-text').text(Math.round(data.progress_percentage) + '%');
            $('#progress-badge').text(Math.round(data.progress_percentage) + '% Selesai');
            $('#progress-bar').css('width', data.progress_percentage + '%');
            $('#current-stage').text(data.current_stage_name);
            
            // FIXED: Update workflow steps dengan warna yang kontras
            let stepsHtml = '';
            Object.keys(data.stages).forEach(function(key) {
                const stage = data.stages[key];
                let statusBadge = '';
                let iconColor = '';
                let textColor = '';
                let bgColor = '';
                
                // PERBAIKAN: Set warna berdasarkan status dengan kontras yang baik
                if (stage.status === 'completed') {
                    statusBadge = '<span class="badge badge-success badge-sm">Selesai</span>';
                    iconColor = 'success';
                    textColor = 'success';
                    bgColor = 'success';
                } else if (stage.status === 'active') {
                    statusBadge = '<span class="badge badge-primary badge-sm">Aktif</span>';
                    iconColor = 'primary';
                    textColor = 'primary';
                    bgColor = 'primary';
                } else {
                    // PENDING - WARNA YANG KONTRAS DAN MUDAH DIBACA
                    statusBadge = '<span class="badge badge-secondary badge-sm">Pending</span>';
                    iconColor = 'secondary';
                    textColor = 'muted'; // Gunakan text-muted untuk kontras yang baik
                    bgColor = 'secondary';
                }
                
                stepsHtml += `
                    <div class="col-md-2 text-center mb-3">
                        <div class="workflow-step">
                            <div class="icon icon-shape bg-gradient-${bgColor} text-white rounded-circle shadow mb-2 mx-auto workflow-icon-${stage.status}">
                                <i class="ni ni-${getStageIcon(key)}"></i>
                            </div>
                            <h6 class="text-${textColor} text-sm font-weight-bold workflow-text-${stage.status}">
                                ${stage.name}
                            </h6>
                            ${statusBadge}
                        </div>
                    </div>
                `;
            });
            
            $('#workflow-steps').html(stepsHtml);
            
            // Update current stage info
            $('#stage-title').text(data.current_stage_name);
            $('#stage-description').text(getStageDescription(data.current_stage));
            $('#current-stage-info').show();
        }
    }).fail(function(xhr, status, error) {
        console.log('Error loading workflow:', error); // Debug
        $('#workflow-steps').html(`
            <div class="col-12 text-center">
                <i class="ni ni-notification-70 fa-2x text-danger mb-2"></i>
                <p class="text-muted">Gagal memuat progress workflow</p>
            </div>
        `);
    });
}

function loadNotifikasi() {
    console.log('Loading notifikasi...'); // Debug
    
    call('mahasiswa/dashboard/get_notifikasi').done(function(response) {
        console.log('Notifikasi response:', response); // Debug
        
        if (response.status === 'success') {
            const notifikasi = response.data;
            let html = '';
            
            if (notifikasi.length > 0) {
                notifikasi.forEach(function(notif) {
                    html += `
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <span class="alert-icon"><i class="ni ni-bell-55"></i></span>
                            <span class="alert-text">
                                <strong>${notif.judul}</strong><br>
                                <small>${notif.pesan}</small><br>
                                <small class="text-muted">
                                    dari: ${notif.nama_pengirim || 'Sistem'} • 
                                    ${formatDate(notif.created_at)}
                                </small>
                            </span>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                });
            } else {
                html = `
                    <div class="text-center py-3">
                        <i class="ni ni-notification-70 fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0 text-sm">Tidak ada notifikasi baru</p>
                    </div>
                `;
            }
            
            $('#notifikasi-container').html(html);
        }
    }).fail(function(xhr, status, error) {
        console.log('Error loading notifikasi:', error); // Debug
        $('#notifikasi-container').html(`
            <div class="text-center py-3">
                <i class="ni ni-notification-70 fa-2x text-danger mb-2"></i>
                <p class="text-muted mb-0 text-sm">Gagal memuat notifikasi</p>
            </div>
        `);
    });
}

function loadStatistikBimbingan() {
    console.log('Loading statistik bimbingan...'); // Debug
    
    call('mahasiswa/dashboard/get_statistik_bimbingan').done(function(response) {
        console.log('Statistik response:', response); // Debug
        
        if (response.status === 'success') {
            const stats = response.data;
            $('#total-bimbingan').text(stats.total_bimbingan);
            $('#bimbingan-bulan-ini').text(stats.bimbingan_bulan_ini);
        }
    }).fail(function(xhr, status, error) {
        console.log('Error loading statistik:', error); // Debug
        $('#total-bimbingan').text('0');
        $('#bimbingan-bulan-ini').text('0');
    });
}

function loadRecentActivities() {
    console.log('Loading recent activities...'); // Debug
    
    call('mahasiswa/dashboard/get_recent_activities').done(function(response) {
        console.log('Activities response:', response); // Debug
        
        if (response.status === 'success') {
            const activities = response.data;
            let html = '';
            
            if (activities.length > 0) {
                html = '<div class="timeline timeline-one-side">';
                activities.forEach(function(activity) {
                    const statusIcon = activity.status_validasi == '1' ? 'check-bold' : 'bullet-list-67';
                    const statusColor = activity.status_validasi == '1' ? 'success' : 'warning';
                    const statusText = activity.status_validasi == '1' ? 'Divalidasi' : 
                                     (activity.status_validasi == '2' ? 'Perlu Revisi' : 'Menunggu Validasi');
                    const statusBadge = activity.status_validasi == '1' ? 'success' : 
                                      (activity.status_validasi == '2' ? 'warning' : 'secondary');
                    
                    html += `
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="ni ni-${statusIcon} text-${statusColor}"></i>
                            </span>
                            <div class="timeline-content">
                                <small class="text-muted">
                                    ${formatDate(activity.tanggal_bimbingan)}
                                </small>
                                <h6 class="text-sm mt-0 mb-1">
                                    Bimbingan ke-${activity.pertemuan_ke}
                                </h6>
                                <p class="text-muted text-xs mt-1 mb-0">
                                    ${activity.materi_bimbingan.substring(0, 80)}${activity.materi_bimbingan.length > 80 ? '...' : ''}
                                </p>
                                <span class="badge badge-${statusBadge} badge-sm mt-1">${statusText}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                html += `
                    <div class="text-center mt-3">
                        <a href="${base_url}mahasiswa/bimbingan" class="btn btn-sm btn-outline-primary">
                            Lihat Semua Aktivitas
                        </a>
                    </div>
                `;
            } else {
                html = `
                    <div class="text-center py-4">
                        <i class="ni ni-time-alarm fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Belum ada aktivitas bimbingan</p>
                        <a href="${base_url}mahasiswa/bimbingan" class="btn btn-sm btn-primary mt-2">
                            Mulai Bimbingan
                        </a>
                    </div>
                `;
            }
            
            $('#recent-activities').html(html);
        }
    }).fail(function(xhr, status, error) {
        console.log('Error loading activities:', error); // Debug
        $('#recent-activities').html(`
            <div class="text-center py-4">
                <i class="ni ni-time-alarm fa-3x text-danger mb-3"></i>
                <p class="text-muted mb-0">Gagal memuat aktivitas</p>
            </div>
        `);
    });
}

function getStageIcon(stage) {
    const icons = {
        'usulan_proposal': 'single-copy-04',
        'bimbingan': 'books',
        'seminar_proposal': 'calendar-grid-58',
        'penelitian': 'atom',
        'seminar_skripsi': 'hat-3',
        'publikasi': 'trophy'
    };
    return icons[stage] || 'circle-08';
}

function getStageDescription(stage) {
    const descriptions = {
        'usulan_proposal': 'Silakan ajukan proposal skripsi Anda untuk memulai proses bimbingan.',
        'bimbingan': 'Lakukan bimbingan rutin dengan dosen pembimbing dan catat dalam jurnal bimbingan.',
        'seminar_proposal': 'Siapkan dan ajukan seminar proposal setelah mendapat persetujuan pembimbing.',
        'penelitian': 'Laksanakan penelitian dan ajukan surat izin penelitian jika diperlukan.',
        'seminar_skripsi': 'Siapkan dokumen skripsi lengkap untuk seminar akhir/ujian skripsi.',
        'publikasi': 'Upload dokumen final dan lengkapi persyaratan publikasi tugas akhir.',
        'selesai': 'Selamat! Anda telah menyelesaikan semua tahapan tugas akhir.'
    };
    return descriptions[stage] || 'Loading...';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>