<?php
/**
 * COMPLETE SET VIEWS UNTUK DOSEN SEMINAR SKRIPSI
 * 
 * File: application/views/dosen/seminar_skripsi/
 * 
 * 1. index.php - Dashboard dengan seminar perlu penilaian
 * 2. detail.php - Detail pengajuan dengan judul baru vs lama + 2 file download
 * 3. penilaian.php - Form penilaian lengkap
 */

// =========================================================================
// FILE 1: application/views/dosen/seminar_skripsi/index.php
// =========================================================================
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap mr-2"></i>
            Seminar Skripsi
        </h1>
        <div class="btn-group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-chart-bar mr-1"></i> Statistik
            </button>
            <div class="dropdown-menu">
                <div class="dropdown-item-text">
                    <small class="text-muted">Total Bimbingan: <?= $stats['total'] ?></small>
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item-text">
                    <small>Perlu Review: <span class="badge badge-warning"><?= $stats['perlu_review'] ?></span></small>
                </div>
                <div class="dropdown-item-text">
                    <small>Disetujui: <span class="badge badge-success"><?= $stats['disetujui'] ?></span></small>
                </div>
                <div class="dropdown-item-text">
                    <small>Ditolak: <span class="badge badge-danger"><?= $stats['ditolak'] ?></span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['perlu_review'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Perlu Penilaian
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($perlu_penilaian) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Disetujui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['disetujui'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Bimbingan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengajuan Perlu Review -->
    <?php if(!empty($pengajuan_review)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clock mr-2"></i>
                Pengajuan Perlu Review (<?= count($pengajuan_review) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="pengajuan-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul Skripsi</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pengajuan_review as $pengajuan): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3">
                                            <?= strtoupper(substr($pengajuan->nama_mahasiswa, 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark"><?= $pengajuan->nama_mahasiswa ?></div>
                                            <small class="text-muted"><?= $pengajuan->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="<?= $pengajuan->judul ?>">
                                        <?= $pengajuan->judul ?>
                                    </div>
                                    <?php if (!empty($pengajuan->judul_skripsi) && $pengajuan->judul_skripsi !== $pengajuan->judul): ?>
                                        <small class="text-info">
                                            <i class="fas fa-edit"></i> Judul diubah
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($pengajuan->created_at)) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php
                                    $status_badge = '';
                                    switch($pengajuan->status) {
                                        case 'submitted':
                                            $status_badge = '<span class="badge badge-warning">Menunggu Review</span>';
                                            break;
                                        case 'resubmitted':
                                            $status_badge = '<span class="badge badge-info">Pengajuan Ulang</span>';
                                            break;
                                        default:
                                            $status_badge = '<span class="badge badge-secondary">' . ucfirst($pengajuan->status) . '</span>';
                                    }
                                    echo $status_badge;
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $pengajuan->id) ?>" 
                                           class="btn btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-success" 
                                                onclick="quickApprove(<?= $pengajuan->id ?>, '<?= addslashes($pengajuan->nama_mahasiswa) ?>')" 
                                                title="Setujui Cepat">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger" 
                                                onclick="quickReject(<?= $pengajuan->id ?>, '<?= addslashes($pengajuan->nama_mahasiswa) ?>')" 
                                                title="Tolak">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ✅ ENHANCED: Seminar Perlu Penilaian - FITUR UTAMA -->
    <?php if(!empty($perlu_penilaian)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-edit mr-2"></i>
                🎯 Seminar Perlu Penilaian (<?= count($perlu_penilaian) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Penting:</strong> Seminar yang sudah dilaksanakan dan perlu dinilai. Setelah publikasi penilaian, mahasiswa dapat lanjut ke tahap Publikasi.
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul Skripsi</th>
                            <th>Jadwal Seminar</th>
                            <th>Status Penilaian</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($perlu_penilaian as $seminar): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3">
                                            <?= strtoupper(substr($seminar->nama_mahasiswa, 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark"><?= $seminar->nama_mahasiswa ?></div>
                                            <small class="text-muted"><?= $seminar->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="<?= $seminar->judul ?>">
                                        <?= $seminar->judul ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($seminar->tanggal_seminar)): ?>
                                        <div>
                                            <i class="fas fa-calendar text-primary"></i>
                                            <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                        </div>
                                        <?php if(!empty($seminar->jam_seminar)): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> 
                                                <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIB
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Belum dijadwalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_penilaian = $seminar->status_penilaian ?? 'belum_dinilai';
                                    switch($status_penilaian) {
                                        case 'draft':
                                            echo '<span class="badge badge-warning">Draft</span>';
                                            break;
                                        case 'published':
                                            echo '<span class="badge badge-success">Sudah Dinilai</span>';
                                            break;
                                        default:
                                            echo '<span class="badge badge-danger">Perlu Penilaian</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> 
                                        <?= ($status_penilaian == 'draft') ? 'Edit Penilaian' : 'Input Penilaian' ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Riwayat Rekomendasi -->
    <?php if(!empty($riwayat_rekomendasi)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history mr-2"></i>
                Riwayat Rekomendasi Terbaru
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Judul</th>
                            <th>Tanggal Review</th>
                            <th>Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($riwayat_rekomendasi as $riwayat): ?>
                            <tr>
                                <td>
                                    <div><?= $riwayat->nama_mahasiswa ?></div>
                                    <small class="text-muted"><?= $riwayat->nim ?></small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= $riwayat->judul ?>">
                                        <?= $riwayat->judul ?>
                                    </div>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($riwayat->tanggal_review_pembimbing)) ?></small>
                                </td>
                                <td>
                                    <?php if($riwayat->status_pembimbing == 'approved'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Ditolak</span>
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

<!-- CSS Custom -->
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.btn-action {
    margin-right: 5px;
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.775rem;
    }
}
</style>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize DataTable jika ada data
    if ($('#pengajuan-table').length && $('#pengajuan-table tbody tr').length > 0) {
        $('#pengajuan-table').DataTable({
            'language': {
                'url': '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            },
            'pageLength': 10,
            'order': [[2, 'desc']] // Sort by tanggal pengajuan
        });
    }
});

// Quick approve function
function quickApprove(seminarId, mahasiswaName) {
    if (confirm('Yakin menyetujui pengajuan seminar skripsi ' + mahasiswaName + '?')) {
        $.post('<?= base_url('dosen/seminar_skripsi/rekomendasi') ?>', {
            seminar_id: seminarId,
            rekomendasi: 'approved',
            komentar_pembimbing: 'Disetujui'
        }, function(response) {
            location.reload();
        });
    }
}

// Quick reject function  
function quickReject(seminarId, mahasiswaName) {
    const komentar = prompt('Masukkan alasan penolakan untuk ' + mahasiswaName + ':');
    if (komentar && komentar.trim() !== '') {
        $.post('<?= base_url('dosen/seminar_skripsi/rekomendasi') ?>', {
            seminar_id: seminarId,
            rekomendasi: 'rejected',
            komentar_pembimbing: komentar
        }, function(response) {
            location.reload();
        });
    }
}
</script>
