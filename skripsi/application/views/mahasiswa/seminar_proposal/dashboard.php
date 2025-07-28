<?php
/**
 * Dashboard Seminar Proposal - Mahasiswa
 * File: application/views/mahasiswa/seminar_proposal/dashboard.php
 * 
 * Dashboard utama untuk manajemen seminar proposal mahasiswa
 * Menggunakan template mahasiswa_simple.php dengan CSS inline
 */
?>

<style>
    /* Card Styles */
    .card {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        border: none;
        margin-bottom: 1.5rem;
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
        background: rgba(0,0,0,0.05);
        border-radius: 0.25rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        transition: width 0.6s ease;
        border-radius: 0.25rem;
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        color: white;
        text-decoration: none;
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
    
    .btn-success {
        background: #2dce89;
        color: white;
        border-color: #2dce89;
    }
    
    .btn-warning {
        background: #fb6340;
        color: white;
        border-color: #fb6340;
    }
    
    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.375rem;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        background: #adb5bd;
        border: 2px solid white;
    }
    
    .timeline-item.completed:before {
        background: #2dce89;
    }
    
    .timeline-item.active:before {
        background: #5e72e4;
        box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.15);
    }
    
    /* Alert */
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        margin-bottom: 1rem;
    }
    
    .alert-info {
        background: rgba(17, 205, 239, 0.1);
        color: #0c5460;
        border-color: rgba(17, 205, 239, 0.2);
    }
    
    .alert-warning {
        background: rgba(251, 99, 64, 0.1);
        color: #8a2b06;
        border-color: rgba(251, 99, 64, 0.2);
    }
    
    .alert-success {
        background: rgba(45, 206, 137, 0.1);
        color: #155724;
        border-color: rgba(45, 206, 137, 0.2);
    }
    
    /* Icon */
    .icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        font-size: 0.875rem;
        margin-right: 0.75rem;
    }
    
    .icon-primary {
        background: rgba(94, 114, 228, 0.15);
        color: #5e72e4;
    }
    
    .icon-success {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    .icon-warning {
        background: rgba(251, 99, 64, 0.15);
        color: #fb6340;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .timeline {
            padding-left: 1.5rem;
        }
        
        .timeline-item:before {
            left: -1.25rem;
        }
    }
</style>

<div style="margin-top: -3rem; position: relative; z-index: 10;">
    <!-- Main Content Area -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;">
        
        <!-- Header Status Card -->
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center;">
                        <div class="icon icon-primary">
                            <i class="fas fa-presentation" style="font-size: 1rem;"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #32325d;">
                                <?php echo $proposal ? $proposal->judul : 'Belum Ada Proposal'; ?>
                            </h4>
                            <p style="margin: 0; color: #8898aa; font-size: 0.875rem;">
                                <?php if ($proposal): ?>
                                    Pembimbing: <?php echo $proposal->nama_pembimbing ?: 'Belum Ditentukan'; ?>
                                <?php else: ?>
                                    Silakan ajukan proposal terlebih dahulu
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div style="text-align: right;">
                        <div class="status-badge <?php echo $workflow_status['status_class']; ?>">
                            <?php echo $workflow_status['status_text']; ?>
                        </div>
                        <div style="margin-top: 0.5rem;">
                            <small style="color: #8898aa;">
                                Progress: <?php echo $progress_percentage; ?>%
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress" style="margin-top: 1rem;">
                    <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
            </div>
        </div>

        <?php if (!$proposal): ?>
            <!-- No Proposal Alert -->
            <div class="alert alert-info">
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-info-circle" style="margin-right: 0.75rem; font-size: 1.125rem;"></i>
                    <div>
                        <strong>Belum Ada Proposal Aktif</strong><br>
                        Anda belum memiliki proposal yang aktif. Silakan ajukan proposal terlebih dahulu melalui menu Proposal.
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; padding: 2rem;">
                <a href="<?php echo base_url('mahasiswa/proposal'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Ajukan Proposal Baru
                </a>
            </div>
            
        <?php else: ?>
            
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem;">
                
                <!-- Main Content -->
                <div>
                    
                    <!-- Syarat Jurnal Bimbingan -->
                    <div class="card">
                        <div class="card-header">
                            <h5 style="margin: 0; font-weight: 600; color: #32325d;">
                                <i class="fas fa-list-check" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                                Syarat Jurnal Bimbingan
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($syarat_jurnal['eligible']): ?>
                                <div class="alert alert-success">
                                    <strong>✓ Syarat Terpenuhi!</strong><br>
                                    Anda telah memiliki <?php echo $syarat_jurnal['jurnal_validated_count']; ?> jurnal bimbingan yang tervalidasi.
                                    Dapat mengajukan seminar proposal.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <strong>⚠ Syarat Belum Terpenuhi</strong><br>
                                    Jurnal tervalidasi: <strong><?php echo $syarat_jurnal['jurnal_validated_count']; ?></strong> dari minimal <strong><?php echo $syarat_jurnal['minimum_required']; ?></strong><br>
                                    Masih perlu <strong><?php echo $syarat_jurnal['missing']; ?></strong> jurnal lagi.
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1rem;">
                                <a href="<?php echo base_url('mahasiswa/jurnal_bimbingan'); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-book"></i>
                                    Lihat Jurnal Bimbingan
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Pengajuan -->
                    <?php if ($seminar_data): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 style="margin: 0; font-weight: 600; color: #32325d;">
                                    <i class="fas fa-file-alt" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                                    Detail Pengajuan Seminar
                                </h5>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                                    <div>
                                        <strong>Tanggal Pengajuan:</strong><br>
                                        <span style="color: #8898aa;">
                                            <?php echo date('d/m/Y H:i', strtotime($seminar_data->created_at)); ?>
                                        </span>
                                    </div>
                                    
                                    <div>
                                        <strong>Step Saat Ini:</strong><br>
                                        <span style="color: #8898aa;">
                                            <?php echo ucfirst(str_replace('_', ' ', $seminar_data->current_step)); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($seminar_data->tanggal_seminar): ?>
                                    <div>
                                        <strong>Jadwal Seminar:</strong><br>
                                        <span style="color: #8898aa;">
                                            <?php echo date('d/m/Y', strtotime($seminar_data->tanggal_seminar)); ?>
                                            <?php if ($seminar_data->jam_seminar): ?>
                                                <?php echo date('H:i', strtotime($seminar_data->jam_seminar)); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($seminar_data->tempat_seminar): ?>
                                    <div>
                                        <strong>Tempat:</strong><br>
                                        <span style="color: #8898aa;">
                                            <?php echo $seminar_data->tempat_seminar; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($seminar_data->keterangan_mahasiswa): ?>
                                <div style="margin-bottom: 1.5rem;">
                                    <strong>Keterangan Anda:</strong><br>
                                    <div style="background: #f8f9fe; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem;">
                                        <?php echo nl2br(htmlspecialchars($seminar_data->keterangan_mahasiswa)); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($seminar_data->komentar_pembimbing): ?>
                                <div style="margin-bottom: 1.5rem;">
                                    <strong>Komentar Pembimbing:</strong><br>
                                    <div style="background: #f1f3f4; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem; border-left: 4px solid #5e72e4;">
                                        <?php echo nl2br(htmlspecialchars($seminar_data->komentar_pembimbing)); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($seminar_data->komentar_kaprodi): ?>
                                <div style="margin-bottom: 1.5rem;">
                                    <strong>Komentar Kaprodi:</strong><br>
                                    <div style="background: #f1f3f4; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem; border-left: 4px solid #2dce89;">
                                        <?php echo nl2br(htmlspecialchars($seminar_data->komentar_kaprodi)); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
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
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">Tindakan Selanjutnya</h6>
                            
                            <?php if ($workflow_status['current_step'] === 'belum_mengajukan' && $syarat_jurnal['eligible']): ?>
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
                                <div class="timeline-item <?php echo $progress_percentage >= 20 ? 'completed' : ($workflow_status['current_step'] === 'belum_eligible' ? 'active' : ''); ?>">
                                    <strong>Syarat Jurnal</strong><br>
                                    <small style="color: #8898aa;">Min. <?php echo $syarat_jurnal['minimum_required']; ?> jurnal tervalidasi</small>
                                </div>
                                
                                <div class="timeline-item <?php echo $progress_percentage >= 30 ? 'completed' : (in_array($workflow_status['current_step'], ['belum_mengajukan', 'draft', 'submitted']) ? 'active' : ''); ?>">
                                    <strong>Pengajuan</strong><br>
                                    <small style="color: #8898aa;">Upload file & isi keterangan</small>
                                </div>
                                
                                <div class="timeline-item <?php echo $progress_percentage >= 50 ? 'completed' : ($workflow_status['current_step'] === 'review_pembimbing' ? 'active' : ''); ?>">
                                    <strong>Review Pembimbing</strong><br>
                                    <small style="color: #8898aa;">Rekomendasi dari dosen</small>
                                </div>
                                
                                <div class="timeline-item <?php echo $progress_percentage >= 70 ? 'completed' : ($workflow_status['current_step'] === 'review_kaprodi' ? 'active' : ''); ?>">
                                    <strong>Persetujuan Kaprodi</strong><br>
                                    <small style="color: #8898aa;">Validasi final</small>
                                </div>
                                
                                <div class="timeline-item <?php echo $progress_percentage >= 90 ? 'completed' : ($workflow_status['current_step'] === 'scheduled' ? 'active' : ''); ?>">
                                    <strong>Penjadwalan</strong><br>
                                    <small style="color: #8898aa;">Tanggal, waktu & tempat</small>
                                </div>
                                
                                <div class="timeline-item <?php echo $progress_percentage >= 100 ? 'completed' : ''; ?>">
                                    <strong>Selesai</strong><br>
                                    <small style="color: #8898aa;">Lanjut penelitian</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-header">
                            <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                                <i class="fas fa-link" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                                Tautan Cepat
                            </h6>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <a href="<?php echo base_url('mahasiswa/proposal'); ?>" style="color: #5e72e4; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-file-alt"></i>
                                    Kelola Proposal
                                </a>
                                <a href="<?php echo base_url('mahasiswa/jurnal_bimbingan'); ?>" style="color: #5e72e4; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-book"></i>
                                    Jurnal Bimbingan
                                </a>
                                <a href="<?php echo base_url('mahasiswa/kontak'); ?>" style="color: #5e72e4; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-comments"></i>
                                    Hubungi Pembimbing
                                </a>
                                <a href="<?php echo base_url('mahasiswa/bantuan'); ?>" style="color: #5e72e4; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-question-circle"></i>
                                    Bantuan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto refresh progress setiap 30 detik
    setInterval(function() {
        // Optional: auto refresh untuk update status real-time
        // location.reload();
    }, 30000);
    
    // Smooth scroll untuk timeline
    const timelineItems = document.querySelectorAll('.timeline-item.active');
    if (timelineItems.length > 0) {
        timelineItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>