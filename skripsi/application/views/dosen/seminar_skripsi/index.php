<!-- 
File: application/views/dosen/seminar_skripsi/index.php
IMPROVED UI - Dashboard Seminar Skripsi untuk Dosen
Konsisten dengan design pattern Seminar Proposal
-->

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Seminar Skripsi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Seminar Skripsi</li>
            </ol>
        </nav>
    </div>

    <!-- Alert Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Bimbingan Skripsi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Bimbingan Skripsi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perlu Review -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['perlu_review'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disetujui -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Disetujui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['disetujui'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ditolak -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ditolak
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['ditolak'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pengajuan Perlu Review -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-hourglass-half"></i> Pengajuan Perlu Review
                    </h6>
                    <?php if (count($pengajuan_review) > 0): ?>
                        <span class="badge badge-warning"><?= count($pengajuan_review) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($pengajuan_review)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada pengajuan yang perlu direview</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="pengajuan-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="25%">Mahasiswa</th>
                                        <th width="40%">Judul</th>
                                        <th width="20%">Tanggal Pengajuan</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pengajuan_review as $pengajuan): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($pengajuan->nama_mahasiswa) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($pengajuan->nim) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-wrap" title="<?= htmlspecialchars(!empty($pengajuan->judul_skripsi) ? $pengajuan->judul_skripsi : $pengajuan->judul) ?>">
                                                <?= character_limiter(htmlspecialchars(!empty($pengajuan->judul_skripsi) ? $pengajuan->judul_skripsi : $pengajuan->judul), 60) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y H:i', strtotime($pengajuan->created_at)) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $pengajuan->id) ?>" 
                                                   class="btn btn-outline-primary btn-sm" 
                                                   data-toggle="tooltip" 
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (!empty($pengajuan->file_skripsi)): ?>
                                                <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $pengajuan->id) ?>" 
                                                   class="btn btn-outline-info btn-sm" 
                                                   target="_blank"
                                                   data-toggle="tooltip" 
                                                   title="Lihat File Skripsi">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seminar Perlu Penilaian -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> Seminar Perlu Penilaian
                    </h6>
                    <?php if (count($perlu_penilaian) > 0): ?>
                        <span class="badge badge-info"><?= count($perlu_penilaian) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($perlu_penilaian)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada seminar yang perlu dinilai</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30%">Mahasiswa</th>
                                        <th width="35%">Judul</th>
                                        <th width="20%">Tanggal Pengajuan</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($perlu_penilaian as $seminar): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($seminar->nama_mahasiswa) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($seminar->nim) ?></small>
                                        </td>
                                        <td>
                                            <span class="text-wrap" title="<?= htmlspecialchars(!empty($seminar->judul_skripsi) ? $seminar->judul_skripsi : $seminar->judul) ?>">
                                                <?= character_limiter(htmlspecialchars(!empty($seminar->judul_skripsi) ? $seminar->judul_skripsi : $seminar->judul), 50) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y', strtotime($seminar->created_at)) ?><br>
                                                <span class="text-muted">
                                                    <?php if (!empty($seminar->tanggal_seminar)): ?>
                                                        Seminar: <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                                    <?php else: ?>
                                                        Diajukan
                                                    <?php endif; ?>
                                                </span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm w-100">
                                                <a href="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                                                   class="btn btn-primary btn-sm mb-1"
                                                   data-toggle="tooltip" 
                                                   title="Input/Edit Penilaian">
                                                    <i class="fas fa-edit"></i> 
                                                    <?= !empty($seminar->status_penilaian) ? 'Edit' : 'Input' ?> Penilaian
                                                </a>
                                                <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $seminar->id) ?>" 
                                                   class="btn btn-outline-info btn-sm"
                                                   data-toggle="tooltip" 
                                                   title="Lihat Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Rekomendasi -->
    <?php if (!empty($riwayat_rekomendasi)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Riwayat Rekomendasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="20%">Mahasiswa</th>
                                    <th width="35%">Judul</th>
                                    <th width="15%">Status</th>
                                    <th width="20%">Tanggal Review</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat_rekomendasi as $riwayat): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($riwayat->nama_mahasiswa) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($riwayat->nim) ?></small>
                                    </td>
                                    <td>
                                        <span class="text-wrap" title="<?= htmlspecialchars(!empty($riwayat->judul_skripsi) ? $riwayat->judul_skripsi : $riwayat->judul) ?>">
                                            <?= character_limiter(htmlspecialchars(!empty($riwayat->judul_skripsi) ? $riwayat->judul_skripsi : $riwayat->judul), 80) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($riwayat->status_pembimbing == 'approved'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Disetujui
                                            </span>
                                        <?php elseif ($riwayat->status_pembimbing == 'rejected'): ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Ditolak
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?= !empty($riwayat->tanggal_review_pembimbing) ? 
                                                date('d/m/Y H:i', strtotime($riwayat->tanggal_review_pembimbing)) : 
                                                '-' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $riwayat->id) ?>" 
                                           class="btn btn-outline-primary btn-sm"
                                           data-toggle="tooltip" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Additional CSS for improved styling -->
<style>
.card {
    border: none;
    border-radius: 0.35rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    background-color: #f8f9fc;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.badge {
    font-size: 0.75rem;
}

.text-wrap {
    word-wrap: break-word;
    white-space: normal;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .h5 {
        font-size: 1.1rem;
    }
    
    .btn-group .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
}
</style>