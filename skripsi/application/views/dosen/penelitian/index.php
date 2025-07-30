<?php
/**
 * View: application/views/dosen/penelitian/index.php
 * Halaman dashboard penelitian untuk dosen pembimbing
 * Menampilkan permohonan izin penelitian yang perlu direview dan riwayat review
 */
?>

<!-- Page Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Penelitian</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active">Penelitian</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <div class="nav-wrapper">
                        <p class="text-white mb-0">
                            <i class="fa fa-info-circle mr-2"></i>
                            Kelola permohonan izin penelitian mahasiswa bimbingan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="container-fluid mt--6">
    
    <!-- Alert Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="fa fa-check-circle"></i></span>
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Total Permohonan</h5>
                            <span class="h2 font-weight-bold mb-0"><?= $stats['total'] ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                <i class="fa fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-nowrap">Semua permohonan</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                            <span class="h2 font-weight-bold mb-0 text-warning"><?= $stats['perlu_review'] ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                <i class="fa fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-nowrap">Menunggu persetujuan</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Disetujui</h5>
                            <span class="h2 font-weight-bold mb-0 text-success"><?= $stats['disetujui'] ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                <i class="fa fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-nowrap">Sudah disetujui</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Ditolak</h5>
                            <span class="h2 font-weight-bold mb-0 text-danger"><?= $stats['ditolak'] ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fa fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-nowrap">Perlu perbaikan</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        
        <!-- Permohonan Perlu Review -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="fa fa-clock text-warning mr-2"></i>
                                Permohonan Perlu Review
                            </h3>
                        </div>
                        <div class="col text-right">
                            <span class="badge badge-pill badge-warning">
                                <?= count($permohonan_review) ?> permohonan
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if(!empty($permohonan_review)): ?>
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>Penelitian</th>
                                        <th>Tanggal Ajuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($permohonan_review as $permohonan): ?>
                                    <tr>
                                        <td>
                                            <div class="media align-items-center">
                                                <div class="media-body">
                                                    <span class="name mb-0 text-sm font-weight-bold">
                                                        <?= $permohonan->nama_mahasiswa ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fa fa-id-card mr-1"></i>
                                                        <?= $permohonan->nim ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fa fa-graduation-cap mr-1"></i>
                                                        <?= $permohonan->program_studi ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm">
                                                <strong><?= character_limiter($permohonan->judul_skripsi_terbaru, 50) ?></strong>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker-alt mr-1"></i>
                                                <?= $permohonan->tempat_penelitian ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar mr-1"></i>
                                                <?= date('d/m/Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?> - 
                                                <?= date('d/m/Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-sm">
                                                <i class="fa fa-calendar-alt mr-1 text-primary"></i>
                                                <?= date('d/m/Y', strtotime($permohonan->created_at)) ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fa fa-clock mr-1"></i>
                                                <?= date('H:i', strtotime($permohonan->created_at)) ?> WIT
                                            </small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <a class="dropdown-item" href="<?= base_url('dosen/penelitian/detail/' . $permohonan->id) ?>">
                                                        <i class="fa fa-eye mr-2"></i> Detail & Review
                                                    </a>
                                                    <?php if($permohonan->file_proposal_revisi): ?>
                                                    <a class="dropdown-item" href="<?= base_url('dosen/penelitian/view_file/' . $permohonan->id) ?>" target="_blank">
                                                        <i class="fa fa-download mr-2"></i> Lihat File Proposal
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="icon icon-shape bg-gradient-secondary shadow border-radius-md mb-4 mx-auto" style="width: 60px; height: 60px;">
                                <i class="fa fa-inbox text-lg opacity-10"></i>
                            </div>
                            <h6 class="text-muted">Tidak ada permohonan yang perlu direview</h6>
                            <p class="text-sm text-muted">Semua permohonan izin penelitian sudah Anda review.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Riwayat Review -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="fa fa-history text-info mr-2"></i>
                                Riwayat Review
                            </h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if(!empty($riwayat_review)): ?>
                        <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                            <?php foreach($riwayat_review as $riwayat): ?>
                            <div class="timeline-block">
                                <span class="timeline-step 
                                    <?= $riwayat->status_pembimbing == 'approved' ? 'badge-success' : 'badge-danger' ?>">
                                    <i class="fa <?= $riwayat->status_pembimbing == 'approved' ? 'fa-check' : 'fa-times' ?>"></i>
                                </span>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-pill 
                                            <?= $riwayat->status_pembimbing == 'approved' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $riwayat->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak' ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($riwayat->tanggal_review_pembimbing)) ?>
                                        </small>
                                    </div>
                                    <h6 class="text-sm font-weight-bold mb-1 mt-2">
                                        <?= $riwayat->nama_mahasiswa ?>
                                    </h6>
                                    <p class="text-sm text-muted mb-0">
                                        <?= character_limiter($riwayat->judul_skripsi_terbaru, 40) ?>
                                    </p>
                                    
                                    <a href="<?= base_url('dosen/penelitian/detail/' . $riwayat->id) ?>" 
                                       class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fa fa-eye mr-1"></i> Detail
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="icon icon-shape bg-gradient-secondary shadow border-radius-md mb-4 mx-auto" style="width: 60px; height: 60px;">
                                <i class="fa fa-history text-lg opacity-10"></i>
                            </div>
                            <h6 class="text-muted">Belum ada riwayat review</h6>
                            <p class="text-sm text-muted">Riwayat review akan muncul setelah Anda memberikan persetujuan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>

    <!-- Info Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h4 class="text-white mb-3">
                                <i class="fa fa-info-circle mr-2"></i>
                                Informasi Workflow Penelitian
                            </h4>
                            <div class="row text-white">
                                <div class="col-md-4">
                                    <h6 class="text-white"><i class="fa fa-user mr-2"></i>Mahasiswa</h6>
                                    <p class="text-sm text-white opacity-8">Mengajukan permohonan izin penelitian dengan melampirkan proposal yang sudah direvisi</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-white"><i class="fa fa-user-tie mr-2"></i>Dosen Pembimbing</h6>
                                    <p class="text-sm text-white opacity-8">Memberikan persetujuan atau penolakan terhadap permohonan penelitian</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-white"><i class="fa fa-users mr-2"></i>Staf Akademik</h6>
                                    <p class="text-sm text-white opacity-8">Memproses dan mengupload surat izin penelitian setelah disetujui dosen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>