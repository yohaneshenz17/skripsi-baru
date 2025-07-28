<?php
/**
 * Detail Seminar Proposal - Mahasiswa
 * File: application/views/mahasiswa/seminar_proposal/detail.php
 * 
 * Halaman detail untuk melihat informasi lengkap seminar proposal
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
    
    .status-badge.submitted {
        background: rgba(17, 205, 239, 0.15);
        color: #11cdef;
    }
    
    .status-badge.review_pembimbing {
        background: rgba(251, 99, 64, 0.15);
        color: #fb6340;
    }
    
    .status-badge.review_kaprodi {
        background: rgba(255, 193, 7, 0.15);
        color: #fd7e14;
    }
    
    .status-badge.approved {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    .status-badge.scheduled {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    .status-badge.completed {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    .status-badge.rejected {
        background: rgba(245, 54, 92, 0.15);
        color: #f5365c;
    }
    
    .status-badge.draft {
        background: rgba(173, 181, 189, 0.15);
        color: #adb5bd;
    }
    
    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        padding: 1rem;
        background: #f8f9fe;
        border-radius: 0.375rem;
        border-left: 4px solid #5e72e4;
    }
    
    .info-label {
        font-weight: 600;
        color: #32325d;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        color: #8898aa;
        font-size: 0.875rem;
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
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    /* Comment Box */
    .comment-box {
        background: #f1f3f4;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-top: 0.5rem;
        border-left: 4px solid #5e72e4;
    }
    
    .comment-box.pembimbing {
        border-left-color: #5e72e4;
    }
    
    .comment-box.kaprodi {
        border-left-color: #2dce89;
    }
    
    .comment-box.rejected {
        border-left-color: #f5365c;
        background: rgba(245, 54, 92, 0.05);
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
    
    .timeline-item.success:before {
        background: #2dce89;
    }
    
    .timeline-item.warning:before {
        background: #fb6340;
    }
    
    .timeline-item.info:before {
        background: #5e72e4;
    }
    
    /* File Display */
    .file-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: rgba(94, 114, 228, 0.05);
        border-radius: 0.375rem;
        border: 1px solid rgba(94, 114, 228, 0.15);
        margin-bottom: 1rem;
    }
    
    .file-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: #5e72e4;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 0.75rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
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
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 1.5rem;">
        
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 1.5rem;">
            <a href="<?php echo base_url('mahasiswa/seminar_proposal'); ?>" 
               style="color: #5e72e4; text-decoration: none; font-size: 0.875rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Seminar
            </a>
        </nav>
        
        <!-- Header -->
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h4 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #32325d;">
                            Detail Seminar Proposal
                        </h4>
                        <p style="margin: 0.5rem 0 0 0; color: #8898aa; font-size: 0.875rem;">
                            <?php echo $proposal->judul; ?>
                        </p>
                    </div>
                    
                    <div class="status-badge <?php echo $seminar->status; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $seminar->status)); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            
            <!-- Main Content -->
            <div>
                
                <!-- Informasi Umum -->
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-info-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Informasi Pengajuan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Tanggal Pengajuan</div>
                                <div class="info-value">
                                    <?php echo date('d F Y, H:i', strtotime($seminar->created_at)); ?> WIB
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Terakhir Update</div>
                                <div class="info-value">
                                    <?php echo date('d F Y, H:i', strtotime($seminar->updated_at)); ?> WIB
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Status Pembimbing</div>
                                <div class="info-value">
                                    <?php 
                                    switch($seminar->status_pembimbing) {
                                        case 'pending':
                                            echo '<span style="color: #fb6340;">⏳ Menunggu Review</span>';
                                            break;
                                        case 'approved':
                                            echo '<span style="color: #2dce89;">✓ Direkomendasikan</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span style="color: #f5365c;">✗ Ditolak</span>';
                                            break;
                                        default:
                                            echo '<span style="color: #8898aa;">-</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Status Kaprodi</div>
                                <div class="info-value">
                                    <?php 
                                    switch($seminar->status_kaprodi) {
                                        case 'pending':
                                            echo '<span style="color: #fb6340;">⏳ Menunggu Review</span>';
                                            break;
                                        case 'approved':
                                            echo '<span style="color: #2dce89;">✓ Disetujui</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span style="color: #f5365c;">✗ Ditolak</span>';
                                            break;
                                        default:
                                            echo '<span style="color: #8898aa;">Belum direview</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($seminar->tanggal_seminar): ?>
                            <div style="background: rgba(45, 206, 137, 0.1); padding: 1rem; border-radius: 0.375rem; border-left: 4px solid #2dce89;">
                                <h6 style="margin: 0 0 0.5rem 0; font-weight: 600; color: #2dce89;">
                                    <i class="fas fa-calendar-check"></i> Jadwal Seminar
                                </h6>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                                    <div>
                                        <strong>Tanggal:</strong><br>
                                        <?php echo date('d F Y', strtotime($seminar->tanggal_seminar)); ?>
                                    </div>
                                    <?php if ($seminar->jam_seminar): ?>
                                    <div>
                                        <strong>Waktu:</strong><br>
                                        <?php echo date('H:i', strtotime($seminar->jam_seminar)); ?> WIB
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($seminar->tempat_seminar): ?>
                                    <div>
                                        <strong>Tempat:</strong><br>
                                        <?php echo $seminar->tempat_seminar; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- File Proposal -->
                <?php if ($seminar->file_proposal): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-file-alt" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            File Proposal
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="file-item">
                            <div class="file-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong><?php echo $seminar->file_proposal; ?></strong><br>
                                <small style="color: #8898aa;">
                                    Upload: <?php echo date('d/m/Y H:i', strtotime($seminar->created_at)); ?>
                                </small>
                            </div>
                            <div>
                                <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal); ?>" 
                                   target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-download"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Keterangan Mahasiswa -->
                <?php if ($seminar->keterangan_mahasiswa): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-comment" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Keterangan dari Anda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="background: #f8f9fe; padding: 1rem; border-radius: 0.375rem;">
                            <?php echo nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Komentar Pembimbing -->
                <?php if ($seminar->komentar_pembimbing): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-user-tie" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Komentar Dosen Pembimbing
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="comment-box pembimbing">
                            <?php echo nl2br(htmlspecialchars($seminar->komentar_pembimbing)); ?>
                        </div>
                        <?php if ($seminar->tanggal_review_pembimbing): ?>
                            <small style="color: #8898aa; margin-top: 0.5rem; display: block;">
                                <i class="fas fa-clock"></i>
                                Review pada: <?php echo date('d F Y, H:i', strtotime($seminar->tanggal_review_pembimbing)); ?> WIB
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Komentar Kaprodi -->
                <?php if ($seminar->komentar_kaprodi): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-user-graduate" style="margin-right: 0.5rem; color: #2dce89;"></i>
                            Komentar Kaprodi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="comment-box kaprodi">
                            <?php echo nl2br(htmlspecialchars($seminar->komentar_kaprodi)); ?>
                        </div>
                        <?php if ($seminar->tanggal_review_kaprodi): ?>
                            <small style="color: #8898aa; margin-top: 0.5rem; display: block;">
                                <i class="fas fa-clock"></i>
                                Review pada: <?php echo date('d F Y, H:i', strtotime($seminar->tanggal_review_kaprodi)); ?> WIB
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                                <a href="<?php echo base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id); ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                    Edit Pengajuan
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($seminar->status === 'completed'): ?>
                                <a href="<?php echo base_url('mahasiswa/penelitian'); ?>" class="btn btn-success">
                                    <i class="fas fa-search"></i>
                                    Lanjut ke Penelitian
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo base_url('mahasiswa/kontak'); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-comments"></i>
                                Hubungi Pembimbing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div>
                
                <!-- Riwayat Aktivitas -->
                <?php if (!empty($riwayat)): ?>
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-history" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Riwayat Aktivitas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach (array_slice($riwayat, 0, 10) as $log): ?>
                                <div class="timeline-item <?php echo $log->action === 'approved' ? 'success' : ($log->action === 'rejected' ? 'warning' : 'info'); ?>">
                                    <strong><?php echo ucfirst($log->action); ?></strong><br>
                                    <small style="color: #8898aa;">
                                        <?php echo $log->description; ?><br>
                                        <?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?>
                                        <?php if ($log->user_nama): ?>
                                            - <?php echo $log->user_nama; ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Info Kontak -->
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-address-card" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Informasi Kontak
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($proposal->nama_pembimbing): ?>
                            <div style="margin-bottom: 1rem;">
                                <strong>Dosen Pembimbing:</strong><br>
                                <span style="color: #8898aa;"><?php echo $proposal->nama_pembimbing; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <a href="<?php echo base_url('mahasiswa/kontak'); ?>" style="color: #5e72e4; text-decoration: none; font-size: 0.875rem;">
                                <i class="fas fa-envelope"></i> Kirim Pesan
                            </a>
                            <a href="<?php echo base_url('mahasiswa/jurnal_bimbingan'); ?>" style="color: #5e72e4; text-decoration: none; font-size: 0.875rem;">
                                <i class="fas fa-book"></i> Jurnal Bimbingan
                            </a>
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
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="<?php echo base_url('mahasiswa/seminar_proposal'); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Dashboard
                            </a>
                            
                            <?php if ($seminar->file_proposal): ?>
                                <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal); ?>" 
                                   target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-download"></i>
                                    Download File
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo base_url('mahasiswa/bantuan'); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-question-circle"></i>
                                Bantuan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto refresh untuk update status real-time (optional)
    // Uncomment jika diperlukan auto refresh
    /*
    setInterval(function() {
        // Check for status updates
        // location.reload();
    }, 60000); // 1 minute
    */
    
    // Print functionality (if needed)
    function printDetail() {
        window.print();
    }
    
    // Copy link functionality
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Link detail telah disalin ke clipboard');
        });
    }
});
</script>