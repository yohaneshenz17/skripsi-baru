<?php
/**
 * Dashboard Seminar Proposal - Mahasiswa (FIXED VERSION)
 * File: application/views/mahasiswa/seminar_proposal/dashboard.php
 * 
 * Dashboard utama untuk manajemen seminar proposal mahasiswa
 * PERBAIKAN: Menampilkan workflow yang benar sesuai status proposal
 */
?>

<style>
    /* Card Styles */
    .card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        border: none;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .status-badge.success {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    .status-badge.warning {
        background: rgba(251, 99, 64, 0.15);
        color: #fb6340;
    }
    
    .status-badge.info {
        background: rgba(17, 205, 239, 0.15);
        color: #11cdef;
    }
    
    .status-badge.danger {
        background: rgba(245, 54, 92, 0.15);
        color: #f5365c;
    }
    
    .status-badge.secondary {
        background: rgba(173, 181, 189, 0.15);
        color: #adb5bd;
    }
    
    /* Progress Bar */
    .progress {
        height: 0.5rem;
        border-radius: 0.375rem;
        background: #e3e6f0;
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, #11cdef 0%, #5e72e4 100%);
        transition: width 0.6s ease;
        height: 100%;
        border-radius: 0.375rem;
    }
    
    /* Dashboard Layout */
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    
    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: white;
        border-radius: 0.375rem;
        border: 1px solid #e3e6f0;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 1rem;
        width: 1rem;
        height: 1rem;
        background: #adb5bd;
        border-radius: 50%;
        border: 3px solid white;
    }
    
    .timeline-item.completed::before {
        background: #2dce89;
    }
    
    .timeline-item.active::before {
        background: #5e72e4;
    }
    
    .timeline-item.pending::before {
        background: #fb6340;
    }
    
    /* Buttons */
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: all 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        text-decoration: none;
        color: white;
    }
    
    .btn-warning {
        background: linear-gradient(87deg, #fb6340 0, #fbb140 100%);
        color: white;
        border-color: #fb6340;
    }
    
    .btn-success {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
        color: white;
        border-color: #2dce89;
    }
    
    .btn-outline-primary {
        background: transparent;
        color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .btn-outline-primary:hover {
        background: #5e72e4;
        color: white;
        text-decoration: none;
    }
    
    /* Alert */
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }
    
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    
    /* Stats Card */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .stats-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 1rem;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .timeline {
            padding-left: 1.5rem;
        }
        
        .timeline-item::before {
            left: -1.25rem;
        }
    }
</style>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="dashboard-container">

    <!-- Progress Header -->
    <div class="stats-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <h4 style="margin: 0; color: white;">
                    <i class="fas fa-graduation-cap" style="margin-right: 0.5rem;"></i>
                    Seminar Proposal
                </h4>
                <p style="margin: 0.5rem 0 0; color: rgba(255,255,255,0.8);">
                    <?php echo isset($proposal) ? htmlspecialchars($proposal->judul) : 'Belum Ada Proposal Aktif'; ?>
                </p>
            </div>
            <div style="text-align: right;">
                <div class="stats-value">
                    <?php echo isset($progress_percentage) ? $progress_percentage : 0; ?>%
                </div>
                <div class="stats-label">Progress Keseluruhan</div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress" style="margin-top: 1rem;">
            <div class="progress-bar" style="width: <?php echo isset($progress_percentage) ? $progress_percentage : 0; ?>%;"></div>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <!-- Main Content -->
        <div>
            
            <!-- Current Status Card -->
            <div class="card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <h6 style="margin-bottom: 0.5rem; font-weight: 600; color: #32325d;">
                                <i class="fas fa-file-alt" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                                <?php echo isset($proposal) ? htmlspecialchars($proposal->judul) : 'Belum Ada Proposal'; ?>
                            </h6>
                            <p style="margin: 0; color: #8898aa; font-size: 0.875rem;">
                                <?php if (isset($proposal)): ?>
                                    Pembimbing: <?php echo $proposal->nama_pembimbing ?: 'Belum Ditentukan'; ?>
                                <?php else: ?>
                                    Silakan ajukan proposal terlebih dahulu
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div style="text-align: right;">
                            <?php if (isset($workflow_status)): ?>
                                <div class="status-badge <?php echo $workflow_status['status_class']; ?>">
                                    <?php echo $workflow_status['status_text']; ?>
                                </div>
                            <?php endif; ?>
                            <div style="margin-top: 0.5rem;">
                                <small style="color: #8898aa;">
                                    Progress: <?php echo isset($progress_percentage) ? $progress_percentage : 0; ?>%
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar Detail -->
                    <div class="progress" style="margin-top: 1rem;">
                        <div class="progress-bar" style="width: <?php echo isset($progress_percentage) ? $progress_percentage : 0; ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Syarat Jurnal Bimbingan -->
            <?php if (isset($syarat_jurnal)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Syarat Jurnal Bimbingan
                    </h6>
                    
                    <?php if ($syarat_jurnal['eligible']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Syarat Terpenuhi!</strong><br>
                            Anda telah menyelesaikan <strong><?php echo $syarat_jurnal['total_validated']; ?></strong> 
                            dari <strong><?php echo $syarat_jurnal['minimum_required']; ?></strong> jurnal bimbingan yang diperlukan.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Syarat Belum Terpenuhi</strong><br>
                            Anda baru menyelesaikan <strong><?php echo $syarat_jurnal['total_validated']; ?></strong> 
                            dari <strong><?php echo $syarat_jurnal['minimum_required']; ?></strong> jurnal bimbingan yang diperlukan.
                            
                            <div style="margin-top: 1rem;">
                                <a href="<?php echo base_url('mahasiswa/bimbingan'); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-plus"></i>
                                    Tambah Jurnal Bimbingan
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status Pengajuan -->
            <?php if (isset($seminar_data) && $seminar_data): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-file-check" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Status Pengajuan Seminar
                    </h6>
                    
                    <div style="background: #f8f9fe; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <strong>ID Pengajuan: #SP-<?php echo str_pad($seminar_data->id, 4, '0', STR_PAD_LEFT); ?></strong>
                            <span class="status-badge <?php echo strtolower($seminar_data->status); ?>">
                                <?php 
                                $status_labels = [
                                    'draft' => 'Draft',
                                    'submitted' => 'Menunggu Review',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'scheduled' => 'Terjadwal'
                                ];
                                echo $status_labels[$seminar_data->status] ?? ucfirst($seminar_data->status);
                                ?>
                            </span>
                        </div>
                        
                        <p style="margin: 0; color: #8898aa; font-size: 0.875rem;">
                            Diajukan pada: <?php echo date('d F Y H:i', strtotime($seminar_data->created_at)); ?>
                        </p>
                        
                        <?php if ($seminar_data->tanggal_seminar): ?>
                        <p style="margin: 0.5rem 0 0; color: #8898aa; font-size: 0.875rem;">
                            <i class="fas fa-calendar"></i>
                            Jadwal Seminar: <?php echo date('d F Y H:i', strtotime($seminar_data->tanggal_seminar)); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Keterangan Mahasiswa -->
                    <?php if ($seminar_data->keterangan_mahasiswa): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Keterangan Anda:</strong><br>
                        <div style="background: #f8f9fe; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem;">
                            <?php echo nl2br(htmlspecialchars($seminar_data->keterangan_mahasiswa)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Feedback Pembimbing -->
                    <?php if ($seminar_data->komentar_pembimbing): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Feedback Dosen Pembimbing:</strong><br>
                        <div style="background: #f1f3f4; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem; border-left: 4px solid #5e72e4;">
                            <?php echo nl2br(htmlspecialchars($seminar_data->komentar_pembimbing)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Feedback Kaprodi -->
                    <?php if ($seminar_data->komentar_kaprodi): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Feedback Kaprodi:</strong><br>
                        <div style="background: #f1f3f4; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem; border-left: 4px solid #2dce89;">
                            <?php echo nl2br(htmlspecialchars($seminar_data->komentar_kaprodi)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons untuk Seminar -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="<?php echo base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i>
                            Lihat Detail
                        </a>
                        
                        <?php if (in_array($seminar_data->status, ['draft', 'rejected'])): ?>
                            <a href="<?php echo base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id); ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                                Edit Pengajuan
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($seminar_data->file_proposal): ?>
                            <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $seminar_data->file_proposal); ?>" 
                               target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-download"></i>
                                Download File
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons Main -->
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">Tindakan Selanjutnya</h6>
                    
                    <?php if (isset($workflow_status)): ?>
                        
                        <?php if ($workflow_status['current_step'] === 'belum_mengajukan' && isset($syarat_jurnal) && $syarat_jurnal['eligible']): ?>
                            <a href="<?php echo base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id); ?>" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                <?php echo $workflow_status['next_action']; ?>
                            </a>
                            
                        <?php elseif ($workflow_status['current_step'] === 'rejected'): ?>
                            <a href="<?php echo base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id); ?>" class="btn btn-warning">
                                <i class="fas fa-redo"></i>
                                <?php echo $workflow_status['next_action']; ?>
                            </a>
                            
                        <?php elseif ($workflow_status['current_step'] === 'completed'): ?>
                            <a href="<?php echo base_url('mahasiswa/penelitian'); ?>" class="btn btn-success">
                                <i class="fas fa-search"></i>
                                <?php echo $workflow_status['next_action']; ?>
                            </a>
                            
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                                <?php echo $workflow_status['next_action']; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div>
            
            <!-- Workflow Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                        <i class="fas fa-route" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Alur Seminar Proposal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 20 ? 'completed' : (isset($workflow_status) && $workflow_status['current_step'] === 'belum_eligible' ? 'active' : 'pending'); ?>">
                            <strong>Syarat Jurnal</strong><br>
                            <small style="color: #8898aa;">
                                Min. <?php echo isset($syarat_jurnal) ? $syarat_jurnal['minimum_required'] : 8; ?> jurnal tervalidasi
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 30 ? 'completed' : (isset($workflow_status) && in_array($workflow_status['current_step'], ['belum_mengajukan', 'draft', 'submitted']) ? 'active' : 'pending'); ?>">
                            <strong>Pengajuan</strong><br>
                            <small style="color: #8898aa;">Upload file & isi keterangan</small>
                        </div>
                        
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 50 ? 'completed' : (isset($workflow_status) && $workflow_status['current_step'] === 'review_pembimbing' ? 'active' : 'pending'); ?>">
                            <strong>Review Pembimbing</strong><br>
                            <small style="color: #8898aa;">Rekomendasi dari dosen</small>
                        </div>
                        
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 70 ? 'completed' : (isset($workflow_status) && $workflow_status['current_step'] === 'review_kaprodi' ? 'active' : 'pending'); ?>">
                            <strong>Persetujuan Kaprodi</strong><br>
                            <small style="color: #8898aa;">Validasi final</small>
                        </div>
                        
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 90 ? 'completed' : (isset($workflow_status) && $workflow_status['current_step'] === 'scheduled' ? 'active' : 'pending'); ?>">
                            <strong>Penjadwalan</strong><br>
                            <small style="color: #8898aa;">Tanggal, waktu & tempat</small>
                        </div>
                        
                        <div class="timeline-item <?php echo isset($progress_percentage) && $progress_percentage >= 100 ? 'completed' : 'pending'; ?>">
                            <strong>Pelaksanaan</strong><br>
                            <small style="color: #8898aa;">Seminar proposal</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                        <i class="fas fa-bolt" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <a href="<?php echo base_url('mahasiswa/bimbingan'); ?>" class="btn btn-outline-primary" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-book"></i>
                        Jurnal Bimbingan
                    </a>
                    
                    <a href="<?php echo base_url('mahasiswa/proposal'); ?>" class="btn btn-outline-primary" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-file-alt"></i>
                        Lihat Proposal
                    </a>
                    
                    <a href="<?php echo base_url('mahasiswa/profil'); ?>" class="btn btn-outline-primary" style="width: 100%;">
                        <i class="fas fa-user"></i>
                        Edit Profil
                    </a>
                </div>
            </div>

            <!-- Help & Tips -->
            <div class="card">
                <div class="card-header">
                    <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                        <i class="fas fa-question-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Tips & Bantuan
                    </h6>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #32325d;">📋 Persiapan Seminar</strong>
                        <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #8898aa; line-height: 1.5;">
                            Pastikan proposal sudah lengkap (Bab 1-3) dan telah dikonsultasikan dengan pembimbing minimal 8 kali.
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #32325d;">⏰ Timeline</strong>
                        <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #8898aa; line-height: 1.5;">
                            Proses review biasanya memakan waktu 5-7 hari kerja setelah pengajuan disubmit.
                        </p>
                    </div>
                    
                    <div>
                        <strong style="color: #32325d;">📞 Kontak</strong>
                        <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: #8898aa; line-height: 1.5;">
                            Hubungi admin prodi jika ada kendala teknis atau pertanyaan.
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>