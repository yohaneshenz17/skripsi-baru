<?php
/**
 * SIMPLE FALLBACK - Kaprodi Seminar Skripsi Index View
 * 
 * View sederhana sebagai fallback jika view utama mengalami error
 * Tanpa dependencies eksternal, hanya menggunakan basic HTML dan Bootstrap
 * 
 * File: application/views/kaprodi/seminar_skripsi/index_simple.php
 */

// Pastikan data tersedia
$seminar_skripsi = isset($seminar_skripsi) ? $seminar_skripsi : [];
$stats = isset($stats) ? $stats : [];

// Simple word limiter
function simple_limit_words($str, $limit = 8) {
    if (empty($str)) return '-';
    $words = explode(' ', trim($str));
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $str;
}
?>

<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-graduation-cap mr-2"></i>Kelola Seminar Skripsi
                </h1>
                <a href="<?= base_url('kaprodi/dashboard') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['pending_review']) ? $stats['pending_review'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Disetujui
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['approved_month']) ? $stats['approved_month'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ditolak
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['rejected_month']) ? $stats['rejected_month'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Terjadwal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['scheduled']) ? $stats['scheduled'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list mr-2"></i>Daftar Seminar Skripsi
                    </h6>
                </div>
                <div class="col-auto">
                    <span class="text-muted">
                        Total: <?= is_array($seminar_skripsi) ? count($seminar_skripsi) : 0 ?> pengajuan
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php if(empty($seminar_skripsi)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-graduation-cap fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">Tidak ada data seminar skripsi</h5>
                    <p class="text-gray-400">Belum ada mahasiswa yang mengajukan seminar skripsi</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Judul Skripsi</th>
                                <th>Pembimbing</th>
                                <th>Status</th>
                                <th>Plagiarisme</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($seminar_skripsi as $key => $item): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item->nama_mahasiswa ?? 'N/A') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($item->nim ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span title="<?= htmlspecialchars($item->judul_skripsi ?? $item->judul_proposal ?? 'N/A') ?>">
                                            <?= simple_limit_words($item->judul_skripsi ?? $item->judul_proposal ?? 'N/A', 8) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item->nama_pembimbing ?? 'Belum ditentukan') ?></td>
                                    <td>
                                        <?php 
                                        $status = $item->status_kaprodi ?? 'pending';
                                        switch($status) {
                                            case 'pending':
                                                echo '<span class="badge badge-warning">Menunggu Review</span>';
                                                break;
                                            case 'approved':
                                                echo '<span class="badge badge-success">Disetujui</span>';
                                                break;
                                            case 'rejected':
                                                echo '<span class="badge badge-danger">Ditolak</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Draft</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($item->plagiarism_percentage)): ?>
                                            <?php 
                                            $plagiarism = floatval($item->plagiarism_percentage);
                                            $badge_class = $plagiarism <= 30 ? 'success' : 'danger';
                                            ?>
                                            <span class="badge badge-<?= $badge_class ?>">
                                                <?= number_format($plagiarism, 1) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $item->created_at ? date('d/m/Y H:i', strtotime($item->created_at)) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= base_url('kaprodi/seminar_skripsi/detail/' . $item->id) ?>" 
                                               class="btn btn-primary btn-sm" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if($item->status_kaprodi == 'approved' && empty($item->tanggal_seminar)): ?>
                                                <a href="<?= base_url('kaprodi/seminar_skripsi/penjadwalan/' . $item->id) ?>" 
                                                   class="btn btn-info btn-sm" 
                                                   title="Jadwalkan">
                                                    <i class="fas fa-calendar-plus"></i>
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

<script>
// Simple refresh function
function refreshPage() {
    location.reload();
}

// Auto refresh setiap 30 detik jika ada pending review
<?php if(isset($stats['pending_review']) && $stats['pending_review'] > 0): ?>
setTimeout(function() {
    location.reload();
}, 30000);
<?php endif; ?>
</script>