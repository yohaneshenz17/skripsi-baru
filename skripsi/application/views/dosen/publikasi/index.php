<?php
// =================================================================
// 1. VIEW INDEX - Dashboard Publikasi Dosen
// File: application/views/dosen/publikasi/index.php
// =================================================================
?>

<style>
    .card-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .card-stats-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .card-stats-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .card-stats-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s;
    }
    .btn-action {
        margin: 0 2px;
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
</style>

<!-- Content untuk template existing -->
<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-upload mr-2"></i>
            Review Publikasi Tugas Akhir
        </h1>
        <div class="d-none d-lg-inline-block">
            <span class="text-muted">Dashboard untuk review publikasi mahasiswa bimbingan</span>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Total Pengajuan
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= $stats['total_pengajuan'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-upload fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats-2 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Perlu Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= $stats['perlu_review'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats-3 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Disetujui
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= $stats['approved'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">
                                Selesai
                            </div>
                            <div class="h5 mb-0 font-weight-bold">
                                <?= $stats['completed'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengajuan Perlu Review -->
    <div class="card shadow mb-4">
        <div class="card-header card-header-custom py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clock mr-2"></i>
                Pengajuan Perlu Review (<?= count($pengajuan_review) ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($pengajuan_review)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Judul Skripsi</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengajuan_review as $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($item->nama_mahasiswa) ?></div>
                                        <small class="text-muted"><?= $item->nim ?> - <?= $item->nama_prodi ?></small>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px;">
                                            <?= character_limiter(htmlspecialchars($item->judul_skripsi), 80, '...') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($item->tanggal_pengajuan)) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = 'warning';
                                        $status_text = 'Menunggu Review';
                                        if ($item->status == 'review_pembimbing') {
                                            $status_class = 'info';
                                            $status_text = 'Dalam Review';
                                        }
                                        ?>
                                        <span class="badge badge-<?= $status_class ?> status-badge">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('dosen/publikasi/review/' . $item->id) ?>" 
                                           class="btn btn-primary btn-action">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                        <button class="btn btn-success btn-action btn-quick-approve" 
                                                data-id="<?= $item->id ?>" 
                                                data-nama="<?= htmlspecialchars($item->nama_mahasiswa) ?>">
                                            <i class="fas fa-check"></i> Quick Approve
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada pengajuan yang perlu direview saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Review Terbaru -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background-color: #f8f9fa;">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history mr-2"></i>
                Riwayat Review Terbaru
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($riwayat_review)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Judul Skripsi</th>
                                <th>Tanggal Review</th>
                                <th>Keputusan</th>
                                <th>Komentar</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_review as $item): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($item->nama_mahasiswa) ?></div>
                                        <small class="text-muted"><?= $item->nim ?></small>
                                    </td>
                                    <td>
                                        <div style="max-width: 250px;">
                                            <?= character_limiter(htmlspecialchars($item->judul_skripsi), 60, '...') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($item->tanggal_review_pembimbing)) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = $item->status_pembimbing == 'approved' ? 'success' : 'danger';
                                        $status_text = $item->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak';
                                        ?>
                                        <span class="badge badge-<?= $status_class ?> status-badge">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            <?= character_limiter(htmlspecialchars($item->komentar_pembimbing), 50, '...') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('dosen/publikasi/riwayat/' . $item->mahasiswa_id) ?>" 
                                           class="btn btn-info btn-action">
                                            <i class="fas fa-list"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada riwayat review.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
