<?php
// =================================================================
// 3. VIEW RIWAYAT - Riwayat Detail Review
// File: application/views/dosen/publikasi/riwayat.php
// =================================================================
?>

<style>
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    .timeline:before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 40px;
        width: 2px;
        margin-left: -1.5px;
        content: '';
        background-color: #e9ecef;
    }
    .timeline > li {
        position: relative;
        margin-bottom: 50px;
        min-height: 50px;
    }
    .timeline > li:before,
    .timeline > li:after {
        content: ' ';
        display: table;
    }
    .timeline > li:after {
        clear: both;
    }
    .timeline > li .timeline-panel {
        position: relative;
        float: right;
        width: calc(100% - 90px);
        padding: 20px;
        text-align: left;
        background: #fff;
        border: 1px solid #d4e3f0;
        border-radius: 2px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
    }
    .timeline > li .timeline-panel:before {
        position: absolute;
        top: 26px;
        right: -15px;
        display: inline-block;
        border-top: 15px solid transparent;
        border-left: 15px solid #ccc;
        border-right: 0 solid #ccc;
        border-bottom: 15px solid transparent;
        content: ' ';
    }
    .timeline > li .timeline-panel:after {
        position: absolute;
        top: 27px;
        right: -14px;
        display: inline-block;
        border-top: 14px solid transparent;
        border-left: 14px solid #fff;
        border-right: 0 solid #fff;
        border-bottom: 14px solid transparent;
        content: ' ';
    }
    .timeline > li .timeline-badge {
        position: absolute;
        top: 16px;
        left: 18px;
        z-index: 100;
        width: 50px;
        height: 50px;
        line-height: 50px;
        font-size: 1.4em;
        text-align: center;
        border-radius: 50% 50% 50% 50%;
        color: #fff;
    }
    .timeline-badge.success {
        background-color: #28a745;
    }
    .timeline-badge.danger {
        background-color: #dc3545;  
    }
    .timeline-badge.warning {
        background-color: #ffc107;
    }
    .timeline-badge.info {
        background-color: #17a2b8;
    }
</style>

<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dosen/publikasi') ?>">Publikasi</a>
                    </li>
                    <li class="breadcrumb-item active">Riwayat Review</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history mr-2"></i>
                Riwayat Review - <?= htmlspecialchars($mahasiswa->nama) ?>
            </h1>
        </div>
    </div>

    <div class="row">
        <!-- Info Mahasiswa -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Info Mahasiswa
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold">Nama:</td>
                            <td><?= htmlspecialchars($mahasiswa->nama) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">NIM:</td>
                            <td><?= $mahasiswa->nim ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td><?= $mahasiswa->email ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Ringkasan
                    </h6>
                </div>
                <div class="card-body">
                    <p><strong>Total Pengajuan:</strong> <?= count($riwayat_publikasi) ?></p>
                    <p><strong>Total Review:</strong> <?= count($detail_reviews) ?></p>
                    <?php
                    $approved_count = 0;
                    $rejected_count = 0;
                    foreach($detail_reviews as $review) {
                        if($review->status == 'approved') $approved_count++;
                        if($review->status == 'rejected') $rejected_count++;
                    }
                    ?>
                    <p><strong>Disetujui:</strong> <?= $approved_count ?></p>
                    <p><strong>Ditolak:</strong> <?= $rejected_count ?></p>
                </div>
            </div>
        </div>

        <!-- Timeline Riwayat -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-timeline mr-2"></i>
                        Timeline Review
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($detail_reviews)): ?>
                        <ul class="timeline">
                            <?php foreach ($detail_reviews as $review): ?>
                                <li>
                                    <?php
                                    $badge_class = 'info';
                                    $icon = 'fas fa-info-circle';
                                    if ($review->status == 'approved') {
                                        $badge_class = 'success';
                                        $icon = 'fas fa-check-circle';
                                    } elseif ($review->status == 'rejected') {
                                        $badge_class = 'danger';
                                        $icon = 'fas fa-times-circle';
                                    }
                                    ?>
                                    <div class="timeline-badge <?= $badge_class ?>">
                                        <i class="<?= $icon ?>"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h6 class="timeline-title">
                                                <?php
                                                switch($review->status) {
                                                    case 'approved':
                                                        echo '<span class="text-success">Publikasi Disetujui</span>';
                                                        break;
                                                    case 'rejected':
                                                        echo '<span class="text-danger">Publikasi Ditolak</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="text-info">Review</span>';
                                                }
                                                ?>
                                            </h6>
                                            <p class="text-muted">
                                                <small>
                                                    <i class="fas fa-calendar"></i> 
                                                    <?= date('d/m/Y H:i', strtotime($review->tanggal)) ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="timeline-body">
                                            <?php if (!empty($review->komentar)): ?>
                                                <p><?= nl2br(htmlspecialchars($review->komentar)) ?></p>
                                            <?php else: ?>
                                                <p class="text-muted"><em>Tidak ada komentar</em></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat review untuk mahasiswa ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daftar Publikasi -->
            <?php if (!empty($riwayat_publikasi)): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list mr-2"></i>
                            Daftar Pengajuan Publikasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Status</th>
                                        <th>Status Pembimbing</th>
                                        <th>Tanggal Review</th>
                                        <th>Status Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat_publikasi as $pub): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($pub->tanggal_pengajuan)) ?></td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                $status_text = ucfirst($pub->status);
                                                switch($pub->status) {
                                                    case 'submitted':
                                                        $status_class = 'warning';
                                                        $status_text = 'Submitted';
                                                        break;
                                                    case 'review_pembimbing':
                                                        $status_class = 'info';
                                                        $status_text = 'Review Dosen';
                                                        break;
                                                    case 'review_staf':
                                                        $status_class = 'primary';
                                                        $status_text = 'Review Staf';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'success';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        $status_text = 'Ditolak';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?>">
                                                    <?= $status_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $pb_class = 'secondary';
                                                $pb_text = 'Pending';
                                                switch($pub->status_pembimbing) {
                                                    case 'approved':
                                                        $pb_class = 'success';
                                                        $pb_text = 'Approved';
                                                        break;
                                                    case 'rejected':
                                                        $pb_class = 'danger';
                                                        $pb_text = 'Rejected';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?= $pb_class ?>">
                                                    <?= $pb_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $pub->tanggal_review_pembimbing ? 
                                                    date('d/m/Y H:i', strtotime($pub->tanggal_review_pembimbing)) : 
                                                    '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($pub->status == 'completed'): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i> Selesai
                                                    </span>
                                                <?php elseif ($pub->status == 'rejected'): ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-times-circle"></i> Ditolak
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-clock"></i> Proses
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Back Button -->
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('dosen/publikasi') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

</div>