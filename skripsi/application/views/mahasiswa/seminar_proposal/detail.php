<?php
/**
 * Detail Seminar Proposal View - Mahasiswa (UPDATED VERSION)
 * File: application/views/mahasiswa/seminar_proposal/detail.php
 * 
 * 🔧 PERBAIKAN:
 * - Fixed property names sesuai database schema
 * - Improved error handling untuk undefined properties
 * - Enhanced display logic
 * - ADDED: Informasi Dosen Penguji 1 dan Penguji 2 di Status Pengajuan
 */
?>

<style>
    /* Page Styles */
    .detail-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
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
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-bottom: none;
        padding: 1.5rem;
    }
    
    .card-header.secondary {
        background: linear-gradient(87deg, #6c757d 0, #8a909a 100%);
    }
    
    .card-header.success {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
    }
    
    .card-header.warning {
        background: linear-gradient(87deg, #fb6340 0, #fbb140 100%);
    }
    
    .card-header.danger {
        background: linear-gradient(87deg, #f5365c 0, #f56036 100%);
    }
    
    .card-header.submitted {
        background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
    }
    
    .card-header.approved {
        background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%);
    }
    
    .card-header.rejected {
        background: linear-gradient(87deg, #f5365c 0, #f56036 100%);
    }
    
    .card-header.draft {
        background: linear-gradient(87deg, #adb5bd 0, #8898aa 100%);
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
        margin-bottom: 1rem;
    }
    
    .status-badge.submitted {
        background: rgba(17, 205, 239, 0.15);
        color: #11cdef;
    }
    
    .status-badge.approved {
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
    
    .status-badge.review_pembimbing {
        background: rgba(251, 99, 64, 0.15);
        color: #fb6340;
    }
    
    .status-badge.review_kaprodi {
        background: rgba(94, 114, 228, 0.15);
        color: #5e72e4;
    }
    
    .status-badge.scheduled {
        background: rgba(17, 205, 239, 0.15);
        color: #11cdef;
    }
    
    .status-badge.completed {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
    }
    
    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        padding: 1rem;
        background: #f8f9fe;
        border-radius: 0.375rem;
        border-left: 4px solid #5e72e4;
    }
    
    .info-label {
        font-size: 0.75rem;
        color: #8898aa;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-bottom: 0.25rem;
        font-weight: 600;
    }
    
    .info-value {
        font-size: 0.875rem;
        color: #32325d;
        font-weight: 600;
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
        background: #5e72e4;
        border-radius: 50%;
        border: 3px solid white;
    }
    
    .timeline-item.completed::before {
        background: #2dce89;
    }
    
    .timeline-item.pending::before {
        background: #fb6340;
    }
    
    /* Comment Box */
    .comment-box {
        background: #f8f9fe;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 1rem;
    }
    
    .comment-box.pembimbing {
        border-left: 4px solid #5e72e4;
    }
    
    .comment-box.kaprodi {
        border-left: 4px solid #2dce89;
    }
    
    .comment-box.rejected {
        border-left: 4px solid #f5365c;
        background: #fff5f5;
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
    
    .btn-outline-light {
        background: transparent;
        color: white;
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
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
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    /* Alert */
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    
    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
    
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }
    
    /* File Link */
    .file-link {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: #f8f9fe;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        color: #5e72e4;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.15s ease;
    }
    
    .file-link:hover {
        background: #e8ecff;
        border-color: #5e72e4;
        text-decoration: none;
        color: #4c63d2;
    }
    
    /* Table */
    .table {
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }
    
    .table th,
    .table td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background: #f8f9fe;
        font-weight: 600;
        color: #32325d;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .detail-page {
            padding: 0 1rem;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .timeline {
            padding-left: 1.5rem;
        }
        
        .timeline-item::before {
            left: -1.25rem;
        }
        
        .main-grid {
            grid-template-columns: 1fr !important;
        }
    }
    
    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
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

<div class="detail-page">

    <!-- Header Card -->
    <div class="card">
        <div class="card-header <?php echo isset($seminar->status) ? strtolower($seminar->status) : 'secondary'; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div>
                    <h4 style="margin: 0; color: white;">
                        <i class="fas fa-file-alt" style="margin-right: 0.5rem;"></i>
                        Detail Seminar Proposal
                    </h4>
                    <p style="margin: 0.5rem 0 0; color: rgba(255,255,255,0.8);">
                        ID: <?php echo isset($seminar->id) ? '#SP-' . str_pad($seminar->id, 4, '0', STR_PAD_LEFT) : 'N/A'; ?>
                    </p>
                </div>
                <div>
                    <a href="<?php echo base_url('mahasiswa/seminar_proposal'); ?>" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-grid">
        
        <!-- Main Content -->
        <div>
            
            <!-- Status Card -->
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-info-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Status Pengajuan
                    </h6>
                    
                    <?php if (isset($seminar)): ?>
                        <div class="status-badge <?php echo $seminar->status; ?>">
                            <?php 
                            $status_text = [
                                'draft' => 'Draft',
                                'submitted' => 'Menunggu Review',
                                'review_pembimbing' => 'Sedang Direview Pembimbing',
                                'review_kaprodi' => 'Sedang Direview Kaprodi',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'scheduled' => 'Terjadwal',
                                'completed' => 'Selesai'
                            ];
                            echo $status_text[$seminar->status] ?? ucfirst($seminar->status);
                            ?>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Tanggal Pengajuan</div>
                                <div class="info-value">
                                    <?php echo $seminar->created_at ? date('d F Y H:i', strtotime($seminar->created_at)) : '-'; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Terakhir Update</div>
                                <div class="info-value">
                                    <?php echo $seminar->updated_at ? date('d F Y H:i', strtotime($seminar->updated_at)) : '-'; ?>
                                </div>
                            </div>
                            
                            <!-- ADDED: Informasi Dosen Penguji -->
                            <div class="info-item">
                                <div class="info-label">Dosen Penguji 1</div>
                                <div class="info-value">
                                    <?php echo isset($seminar->nama_penguji1) && !empty($seminar->nama_penguji1) ? 
                                        htmlspecialchars($seminar->nama_penguji1) : 
                                        '<span style="color: #8898aa; font-style: italic;">Belum Ditentukan</span>'; ?>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-label">Dosen Penguji 2</div>
                                <div class="info-value">
                                    <?php echo isset($seminar->nama_penguji2) && !empty($seminar->nama_penguji2) ? 
                                        htmlspecialchars($seminar->nama_penguji2) : 
                                        '<span style="color: #8898aa; font-style: italic;">Belum Ditentukan</span>'; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($seminar->tanggal_seminar)): ?>
                            <div class="info-item">
                                <div class="info-label">Tanggal Seminar</div>
                                <div class="info-value">
                                    <?php 
                                    $tanggal_display = date('d F Y', strtotime($seminar->tanggal_seminar));
                                    if (!empty($seminar->jam_seminar)) {
                                        $tanggal_display .= ' ' . date('H:i', strtotime($seminar->jam_seminar));
                                    }
                                    echo $tanggal_display;
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($seminar->tempat_seminar)): ?>
                            <div class="info-item">
                                <div class="info-label">Tempat Seminar</div>
                                <div class="info-value"><?php echo htmlspecialchars($seminar->tempat_seminar); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Data seminar proposal tidak ditemukan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Proposal Info -->
            <?php if (isset($proposal)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-file-text" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Informasi Proposal
                    </h6>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>Judul Proposal:</strong><br>
                        <p style="margin-top: 0.5rem; line-height: 1.6; color: #525f7f;">
                            <?php echo htmlspecialchars($proposal->judul); ?>
                        </p>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Mahasiswa</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($proposal->nama_mahasiswa); ?><br>
                                <small style="color: #8898aa;">NIM: <?php echo htmlspecialchars($proposal->nim); ?></small>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Dosen Pembimbing</div>
                            <div class="info-value"><?php echo htmlspecialchars($proposal->nama_pembimbing ?: 'Belum Ditentukan'); ?></div>
                        </div>
                        
                        <?php if (!empty($proposal->nama_prodi)): ?>
                        <div class="info-item">
                            <div class="info-label">Program Studi</div>
                            <div class="info-value"><?php echo htmlspecialchars($proposal->nama_prodi); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($proposal->jenis_penelitian)): ?>
                        <div class="info-item">
                            <div class="info-label">Jenis Penelitian</div>
                            <div class="info-value"><?php echo htmlspecialchars($proposal->jenis_penelitian); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- File dan Dokumen -->
            <?php if (isset($seminar)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-file-download" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        File dan Dokumen
                    </h6>
                    
                    <?php if (!empty($seminar->file_proposal)): ?>
                        <div style="margin-bottom: 1rem;">
                            <strong>File Proposal:</strong><br>
                            <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal); ?>" 
                               target="_blank" class="file-link" style="margin-top: 0.5rem;">
                                <i class="fas fa-file-pdf" style="margin-right: 0.5rem;"></i>
                                <?php echo htmlspecialchars($seminar->file_proposal); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($seminar->file_proposal)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Belum ada file yang diupload.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Keterangan Mahasiswa -->
            <?php if (isset($seminar) && !empty($seminar->keterangan_mahasiswa)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-comment" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Keterangan Mahasiswa
                    </h6>
                    
                    <div class="comment-box">
                        <?php echo nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feedback dari Pembimbing -->
            <?php if (isset($seminar) && !empty($seminar->komentar_pembimbing)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-user-graduate" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Feedback Dosen Pembimbing
                    </h6>
                    
                    <div class="comment-box pembimbing">
                        <?php if (isset($seminar->status_pembimbing)): ?>
                        <strong>Status Rekomendasi:</strong> 
                        <?php 
                        if ($seminar->status_pembimbing == 'approved') {
                            echo '<span style="color: #2dce89;">Direkomendasikan</span>';
                        } elseif ($seminar->status_pembimbing == 'rejected') {
                            echo '<span style="color: #f5365c;">Tidak Direkomendasikan</span>';
                        } else {
                            echo '<span style="color: #fb6340;">Menunggu Review</span>';
                        }
                        ?>
                        <br><br>
                        <?php endif; ?>
                        
                        <strong>Komentar:</strong><br>
                        <?php echo nl2br(htmlspecialchars($seminar->komentar_pembimbing)); ?>
                        
                        <?php if (!empty($seminar->tanggal_review_pembimbing)): ?>
                        <br><br>
                        <small style="color: #8898aa;">
                            <i class="fas fa-clock"></i>
                            Direview pada: <?php echo date('d F Y H:i', strtotime($seminar->tanggal_review_pembimbing)); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feedback dari Kaprodi -->
            <?php if (isset($seminar) && !empty($seminar->komentar_kaprodi)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-user-tie" style="margin-right: 0.5rem; color: #2dce89;"></i>
                        Feedback Ketua Program Studi
                    </h6>
                    
                    <div class="comment-box kaprodi">
                        <?php if (isset($seminar->status_kaprodi)): ?>
                        <strong>Status Validasi:</strong> 
                        <?php 
                        if ($seminar->status_kaprodi == 'approved') {
                            echo '<span style="color: #2dce89;">Disetujui</span>';
                        } elseif ($seminar->status_kaprodi == 'rejected') {
                            echo '<span style="color: #f5365c;">Ditolak</span>';
                        } else {
                            echo '<span style="color: #fb6340;">Menunggu Review</span>';
                        }
                        ?>
                        <br><br>
                        <?php endif; ?>
                        
                        <strong>Komentar:</strong><br>
                        <?php echo nl2br(htmlspecialchars($seminar->komentar_kaprodi)); ?>
                        
                        <?php if (!empty($seminar->tanggal_review_kaprodi)): ?>
                        <br><br>
                        <small style="color: #8898aa;">
                            <i class="fas fa-clock"></i>
                            Direview pada: <?php echo date('d F Y H:i', strtotime($seminar->tanggal_review_kaprodi)); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div>
            
            <!-- Container Tindakan - Update untuk menambahkan tombol Lihat Penilaian -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-cogs mr-2"></i>
                        Tindakan
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Tombol Download File (existing) -->
                    <?php if (!empty($seminar->file_proposal)): ?>
                    <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                       class="btn btn-outline-primary btn-block mb-2" 
                       target="_blank"
                       title="Download file proposal">
                        <i class="fas fa-download mr-2"></i>
                        Download File
                    </a>
                    <?php endif; ?>
                    
                    <!-- TOMBOL BARU: Lihat Penilaian -->
                    <?php 
                    // Cek apakah penilaian sudah dipublikasikan
                    $this->db->select('id, published_at');
                    $this->db->from('penilaian_seminar_proposal');
                    $this->db->where('seminar_proposal_id', $seminar->id);
                    $this->db->where('status_penilaian', 'published');
                    $this->db->where('published_at IS NOT NULL');
                    $penilaian_published = $this->db->get()->row();
                    ?>
                    
                    <?php if ($penilaian_published): ?>
                    <a href="<?= base_url('mahasiswa/seminar_proposal/lihat_penilaian/' . $seminar->id) ?>" 
                       class="btn btn-success btn-block mb-2"
                       title="Lihat hasil penilaian seminar proposal">
                        <i class="fas fa-star mr-2"></i>
                        Lihat Penilaian
                    </a>
                    <small class="text-muted d-block mb-3">
                        <i class="fas fa-check-circle text-success mr-1"></i>
                        Penilaian dipublikasikan: <?= date('d/m/Y H:i', strtotime($penilaian_published->published_at)) ?>
                    </small>
                    <?php else: ?>
                    <!-- Tombol disabled jika penilaian belum tersedia -->
                    <button class="btn btn-outline-secondary btn-block mb-2" 
                            disabled
                            title="Penilaian belum tersedia atau belum dipublikasikan">
                        <i class="fas fa-clock mr-2"></i>
                        Penilaian Belum Tersedia
                    </button>
                    <small class="text-muted d-block mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Penilaian akan muncul setelah seminar selesai dan dosen menginput nilai
                    </small>
                    <?php endif; ?>
                    
                    <!-- Tombol Lihat Semua (existing) -->
                    <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" 
                       class="btn btn-outline-secondary btn-block"
                       title="Kembali ke daftar seminar proposal">
                        <i class="fas fa-list mr-2"></i>
                        Lihat Semua
                    </a>
                </div>
            </div>

            <!-- Timeline Workflow -->
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-route" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Timeline Workflow
                    </h6>
                    
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <strong>Pengajuan Mahasiswa</strong><br>
                            <small style="color: #8898aa;">
                                <?php echo isset($seminar) && $seminar->created_at ? 
                                    date('d M Y H:i', strtotime($seminar->created_at)) : 'Belum diajukan'; ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo (isset($seminar) && !empty($seminar->tanggal_review_pembimbing)) ? 'completed' : 'pending'; ?>">
                            <strong>Review Pembimbing</strong><br>
                            <small style="color: #8898aa;">
                                <?php echo (isset($seminar) && !empty($seminar->tanggal_review_pembimbing)) ? 
                                    date('d M Y H:i', strtotime($seminar->tanggal_review_pembimbing)) : 'Menunggu review'; ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo (isset($seminar) && !empty($seminar->tanggal_review_kaprodi)) ? 'completed' : 'pending'; ?>">
                            <strong>Validasi Kaprodi</strong><br>
                            <small style="color: #8898aa;">
                                <?php echo (isset($seminar) && !empty($seminar->tanggal_review_kaprodi)) ? 
                                    date('d M Y H:i', strtotime($seminar->tanggal_review_kaprodi)) : 'Menunggu validasi'; ?>
                            </small>
                        </div>
                        
                        <div class="timeline-item <?php echo (isset($seminar) && !empty($seminar->tanggal_seminar)) ? 'completed' : 'pending'; ?>">
                            <strong>Pelaksanaan Seminar</strong><br>
                            <small style="color: #8898aa;">
                                <?php 
                                if (isset($seminar) && !empty($seminar->tanggal_seminar)) {
                                    $tanggal_display = date('d M Y', strtotime($seminar->tanggal_seminar));
                                    if (!empty($seminar->jam_seminar)) {
                                        $tanggal_display .= ' ' . date('H:i', strtotime($seminar->jam_seminar));
                                    }
                                    echo $tanggal_display;
                                } else {
                                    echo 'Belum dijadwalkan';
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jurnal Bimbingan -->
            <?php if (isset($jurnal_validasi) && !empty($jurnal_validasi)): ?>
            <div class="card">
                <div class="card-body">
                    <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">
                        <i class="fas fa-book" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Jurnal Bimbingan Tervalidasi
                    </h6>
                    
                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pertemuan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jurnal_validasi as $jurnal): ?>
                                <tr>
                                    <td><?php echo $jurnal->pertemuan_ke; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)); ?></td>
                                    <td>
                                        <span style="color: #2dce89; font-size: 0.75rem;">
                                            <i class="fas fa-check-circle"></i> Tervalidasi
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <a href="<?php echo base_url('mahasiswa/bimbingan'); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt"></i>
                            Lihat Semua Jurnal
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>