<?php
// =================================================================
// File: application/views/mahasiswa/seminar_proposal/detail.php
// =================================================================
?>

<!-- Detail Seminar Proposal -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Detail Seminar Proposal</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>"><i class="ni ni-tv-2"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_proposal') ?>">Seminar Proposal</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" class="btn btn-sm btn-neutral">
                        <i class="ni ni-bold-left"></i> Kembali
                    </a>
                    <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                    <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan') ?>" class="btn btn-sm btn-warning">
                        <i class="ni ni-settings"></i> Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--7">
    <div class="row">
        <!-- Main Content -->
        <div class="col-xl-8 mb-5">
            <!-- Status Card -->
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Status Pengajuan</h3>
                        </div>
                        <div class="col text-right">
                            <?= format_status_badge($seminar->status) ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="heading-small text-muted mb-2">INFORMASI UMUM</h6>
                            <div class="pl-lg-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Status Saat Ini</label>
                                            <p class="form-control-alternative">
                                                <?= $seminar->status_description ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Tanggal Pengajuan</label>
                                            <p class="form-control-alternative">
                                                <i class="ni ni-calendar-grid-58 text-primary"></i>
                                                <?= date('d F Y, H:i', strtotime($seminar->created_at)) ?> WIT
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($seminar->updated_at != $seminar->created_at): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Terakhir Diperbarui</label>
                                            <p class="form-control-alternative">
                                                <i class="ni ni-time-alarm text-warning"></i>
                                                <?= date('d F Y, H:i', strtotime($seminar->updated_at)) ?> WIT
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="heading-small text-muted mb-2">FILE & DOKUMEN</h6>
                            <div class="pl-lg-4">
                                <?php if ($seminar->file_proposal): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label">File Proposal</label>
                                            <div class="d-flex align-items-center">
                                                <i class="ni ni-archive-2 text-success mr-2"></i>
                                                <a href="<?= base_url('uploads/seminar_proposal/' . $seminar->file_proposal) ?>" 
                                                   target="_blank" class="text-primary">
                                                    <i class="ni ni-cloud-download-95"></i> Download File
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($seminar->file_turnitin): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Hasil Turnitin</label>
                                            <div class="d-flex align-items-center">
                                                <i class="ni ni-archive-2 text-info mr-2"></i>
                                                <a href="<?= base_url('uploads/turnitin/' . $seminar->file_turnitin) ?>" 
                                                   target="_blank" class="text-primary">
                                                    <i class="ni ni-cloud-download-95"></i> Download Turnitin
                                                </a>
                                                <?php if ($seminar->plagiarism_percentage): ?>
                                                <span class="badge badge-<?= $seminar->plagiarism_percentage <= 30 ? 'success' : 'danger' ?> ml-2">
                                                    <?= $seminar->plagiarism_percentage ?>%
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($seminar->keterangan_mahasiswa): ?>
                    <hr class="my-4">
                    <h6 class="heading-small text-muted mb-2">KETERANGAN MAHASISWA</h6>
                    <div class="pl-lg-4">
                        <div class="form-group">
                            <div class="alert alert-info" role="alert">
                                <strong><i class="ni ni-chat-round"></i> Keterangan:</strong><br>
                                <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Workflow Progress -->
            <?php if ($workflow && $workflow['found']): ?>
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Progress Workflow</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                        <?php foreach ($workflow['progress'] as $step): ?>
                        <div class="timeline-block">
                            <span class="timeline-step 
                                <?= $step['completed'] ? 'badge-success' : ($step['active'] ? 'badge-warning' : 'badge-light') ?>">
                                <i class="ni <?= $step['icon'] ?>"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 <?= $step['completed'] ? 'text-success' : ($step['active'] ? 'text-warning' : 'text-muted') ?>">
                                    <?= $step['title'] ?>
                                    <?php if ($step['completed']): ?>
                                        <i class="ni ni-check-bold text-success ml-1"></i>
                                    <?php elseif ($step['active']): ?>
                                        <i class="ni ni-time-alarm text-warning ml-1"></i>
                                    <?php endif; ?>
                                </h6>
                                <p class="text-sm text-muted mb-0"><?= $step['description'] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($workflow['next_action']): ?>
                    <div class="alert alert-info" role="alert">
                        <strong><i class="ni ni-notification-70"></i> Tahap Selanjutnya:</strong>
                        <?= $workflow['next_action'] ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Jadwal Seminar (jika sudah dijadwalkan) -->
            <?php if ($seminar->tanggal_seminar): ?>
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">
                        <i class="ni ni-calendar-grid-58 text-primary"></i>
                        Jadwal Seminar Proposal
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tanggal</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-calendar-grid-58 text-primary"></i>
                                    <?= date('l, d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Waktu</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-time-alarm text-primary"></i>
                                    <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-control-label">Tempat</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-pin-3 text-primary"></i>
                                    <?= $seminar->tempat_seminar ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($seminar->nama_penguji1 || $seminar->nama_penguji2): ?>
                    <hr class="my-4">
                    <h6 class="heading-small text-muted mb-2">TIM PENGUJI</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Pembimbing</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-single-02 text-primary"></i>
                                    <?= $seminar->nama_pembimbing ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($seminar->nama_penguji1): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Penguji 1</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-single-02 text-success"></i>
                                    <?= $seminar->nama_penguji1 ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($seminar->nama_penguji2): ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Penguji 2</label>
                                <p class="form-control-alternative">
                                    <i class="ni ni-single-02 text-success"></i>
                                    <?= $seminar->nama_penguji2 ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Review Comments -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">
                        <i class="ni ni-chat-round text-info"></i>
                        Komentar & Review
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Komentar Pembimbing -->
                    <?php if ($seminar->status_pembimbing != 'pending'): ?>
                    <div class="media">
                        <div class="media-object">
                            <div class="icon icon-shape 
                                <?= $seminar->status_pembimbing == 'approved' ? 'bg-success' : 'bg-danger' ?> 
                                text-white rounded-circle">
                                <i class="ni ni-single-02"></i>
                            </div>
                        </div>
                        <div class="media-body ml-4">
                            <h6 class="mb-1">
                                Dosen Pembimbing 
                                <span class="badge badge-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                                    <?= $seminar->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak' ?>
                                </span>
                            </h6>
                            <?php if ($seminar->komentar_pembimbing): ?>
                            <p class="text-sm text-muted mb-2"><?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-muted mb-0">
                                <i class="ni ni-time-alarm"></i>
                                <?= date('d F Y, H:i', strtotime($seminar->tanggal_review_pembimbing)) ?> WIT
                            </p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <?php endif; ?>
                    
                    <!-- Komentar Kaprodi -->
                    <?php if ($seminar->status_kaprodi != 'pending'): ?>
                    <div class="media">
                        <div class="media-object">
                            <div class="icon icon-shape 
                                <?= $seminar->status_kaprodi == 'approved' ? 'bg-success' : 'bg-danger' ?> 
                                text-white rounded-circle">
                                <i class="ni ni-badge"></i>
                            </div>
                        </div>
                        <div class="media-body ml-4">
                            <h6 class="mb-1">
                                Ketua Program Studi 
                                <span class="badge badge-<?= $seminar->status_kaprodi == 'approved' ? 'success' : 'danger' ?>">
                                    <?= $seminar->status_kaprodi == 'approved' ? 'Disetujui' : 'Ditolak' ?>
                                </span>
                            </h6>
                            <?php if ($seminar->komentar_kaprodi): ?>
                            <p class="text-sm text-muted mb-2"><?= nl2br(htmlspecialchars($seminar->komentar_kaprodi)) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-muted mb-0">
                                <i class="ni ni-time-alarm"></i>
                                <?= date('d F Y, H:i', strtotime($seminar->tanggal_review_kaprodi)) ?> WIT
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($seminar->status_pembimbing == 'pending' && $seminar->status_kaprodi == 'pending'): ?>
                    <div class="text-center py-4">
                        <i class="ni ni-notification-70 fa-2x text-warning mb-2"></i>
                        <p class="text-muted">Belum ada komentar dari pembimbing atau kaprodi</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Info Proposal Card -->
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">Informasi Proposal</h3>
                </div>
                <div class="card-body">
                    <h6 class="heading-small text-muted mb-2">DETAIL PROPOSAL</h6>
                    <div class="pl-lg-4">
                        <div class="form-group">
                            <label class="form-control-label">Judul</label>
                            <p class="form-control-alternative"><?= $seminar->judul ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">NIM</label>
                            <p class="form-control-alternative">
                                <i class="ni ni-badge text-primary"></i>
                                <?= $seminar->nim ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Nama Mahasiswa</label>
                            <p class="form-control-alternative">
                                <i class="ni ni-circle-08 text-primary"></i>
                                <?= $seminar->nama_mahasiswa ?>
                            </p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Pembimbing</label>
                            <p class="form-control-alternative">
                                <i class="ni ni-single-02 text-primary"></i>
                                <?= $seminar->nama_pembimbing ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Jurnal Bimbingan Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Jurnal Bimbingan Tervalidasi</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($jurnal_validasi)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
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
                                    <td><?= $jurnal->pertemuan_ke ?></td>
                                    <td><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                                    <td><span class="badge badge-success">Valid</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <span class="badge badge-success badge-lg">
                            Total: <?= count($jurnal_validasi) ?> Jurnal
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="ni ni-notification-70 fa-2x text-warning mb-2"></i>
                        <p class="text-muted">Belum ada jurnal yang divalidasi</p>
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-outline-primary btn-sm btn-block mt-3">
                        <i class="ni ni-books"></i> Lihat Semua Jurnal
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">Aksi</h3>
                </div>
                <div class="card-body">
                    <div class="nav-wrapper">
                        <ul class="nav nav-pills nav-fill flex-column" role="tablist">
                            <?php if (in_array($seminar->status, ['draft', 'rejected'])): ?>
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/seminar_proposal/ajukan') ?>" 
                                   class="nav-link mb-sm-3 mb-md-0 text-warning">
                                    <i class="ni ni-settings mr-2"></i>Edit Pengajuan
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/seminar_proposal') ?>" 
                                   class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-bold-left mr-2"></i>Kembali ke Dashboard
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?= base_url('mahasiswa/bimbingan') ?>" 
                                   class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-books mr-2"></i>Jurnal Bimbingan
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="javascript:void(0)" onclick="refreshStatus()" 
                                   class="nav-link mb-sm-3 mb-md-0">
                                    <i class="ni ni-time-alarm mr-2"></i>Refresh Status
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function refreshStatus() {
    // Show loading indicator
    Swal.fire({
        title: 'Memuat...',
        text: 'Memeriksa update status terbaru',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Refresh the page after 2 seconds
    setTimeout(function() {
        location.reload();
    }, 2000);
}
</script>