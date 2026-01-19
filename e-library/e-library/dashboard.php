<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();
updateStatusPeminjaman($conn);

// Get statistics
$stats = array();

// Total buku
$query = "SELECT SUM(stok) as total FROM buku";
$result = $conn->query($query);
$stats['total_buku'] = $result->fetch_assoc()['total'] ?? 0;

// Total mahasiswa
$query = "SELECT COUNT(*) as total FROM mahasiswa";
$result = $conn->query($query);
$stats['total_mahasiswa'] = $result->fetch_assoc()['total'];

// Total dosen
$query = "SELECT COUNT(*) as total FROM dosen";
$result = $conn->query($query);
$stats['total_dosen'] = $result->fetch_assoc()['total'];

// Peminjaman aktif
$query = "SELECT COUNT(*) as total FROM peminjaman WHERE status IN ('dipinjam', 'diperpanjang', 'terlambat')";
$result = $conn->query($query);
$stats['peminjaman_aktif'] = $result->fetch_assoc()['total'];

// Keterlambatan
$query = "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat'";
$result = $conn->query($query);
$stats['keterlambatan'] = $result->fetch_assoc()['total'];

// Total denda belum dibayar
$query = "SELECT SUM(sisa_denda) as total FROM pengembalian WHERE sisa_denda > 0";
$result = $conn->query($query);
$stats['total_denda'] = $result->fetch_assoc()['total'] ?? 0;

// Buku habis
$query = "SELECT * FROM buku WHERE stok_tersedia <= 0 AND stok > 0";
$buku_habis = $conn->query($query);

// Peminjaman akan jatuh tempo (3 hari ke depan)
$today = date('Y-m-d');
$three_days = date('Y-m-d', strtotime('+3 days'));
$query = "SELECT p.*, b.judul, b.nomor_buku 
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.tanggal_jatuh_tempo BETWEEN ? AND ?
          AND p.status IN ('dipinjam', 'diperpanjang')
          ORDER BY p.tanggal_jatuh_tempo ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $today, $three_days);
$stmt->execute();
$akan_jatuh_tempo = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
                    <div class="text-muted">
                        <i class="bi bi-calendar3 me-2"></i><?= formatTanggalIndo(date('Y-m-d')) ?>
                    </div>
                </div>

                <?php showAlert(); ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Total Buku</h6>
                                        <h2 class="mb-0 mt-2"><?= number_format($stats['total_buku']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-book-fill fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Mahasiswa</h6>
                                        <h2 class="mb-0 mt-2"><?= number_format($stats['total_mahasiswa']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people-fill fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Dosen</h6>
                                        <h2 class="mb-0 mt-2"><?= number_format($stats['total_dosen']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-person-badge-fill fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Peminjaman Aktif</h6>
                                        <h2 class="mb-0 mt-2"><?= number_format($stats['peminjaman_aktif']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-arrow-left-right fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Keterlambatan</h6>
                                        <h2 class="mb-0 mt-2"><?= number_format($stats['keterlambatan']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card stat-card bg-dark text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Total Denda Belum Dibayar</h6>
                                        <h2 class="mb-0 mt-2"><?= formatRupiah($stats['total_denda']) ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-cash-stack fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <?php if ($buku_habis->num_rows > 0): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-circle-fill me-2"></i>Peringatan: Buku Stok Habis</h5>
                    <hr>
                    <ul class="mb-0">
                        <?php while ($buku = $buku_habis->fetch_assoc()): ?>
                        <li><strong><?= $buku['judul'] ?></strong> (<?= $buku['nomor_buku'] ?>) - Stok: <?= $buku['stok_tersedia'] ?>/<?= $buku['stok'] ?></li>
                        <?php endwhile; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Akan Jatuh Tempo -->
                <?php if ($akan_jatuh_tempo->num_rows > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Peminjaman Akan Jatuh Tempo (3 Hari ke Depan)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Peminjam</th>
                                        <th>Buku</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Sisa Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pjm = $akan_jatuh_tempo->fetch_assoc()): 
                                        $sisa_hari = (strtotime($pjm['tanggal_jatuh_tempo']) - strtotime($today)) / (60 * 60 * 24);
                                    ?>
                                    <tr>
                                        <td><?= $pjm['kode_peminjaman'] ?></td>
                                        <td>
                                            <?= getNamaPeminjam($conn, $pjm['jenis_peminjam'], $pjm['peminjam_id']) ?>
                                            <br><small class="text-muted"><?= ucfirst($pjm['jenis_peminjam']) ?></small>
                                        </td>
                                        <td><?= $pjm['judul'] ?></td>
                                        <td><?= formatTanggalIndo($pjm['tanggal_jatuh_tempo']) ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?= round($sisa_hari) ?> hari</span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="bi bi-lightning-fill me-2"></i>Aksi Cepat</h4>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/peminjaman/add.php" class="text-decoration-none">
                            <div class="card quick-action-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-plus-circle-fill fs-1 text-primary mb-2"></i>
                                    <h6>Tambah Peminjaman</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/pengembalian/index.php" class="text-decoration-none">
                            <div class="card quick-action-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-arrow-return-left fs-1 text-success mb-2"></i>
                                    <h6>Pengembalian Buku</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/surat_keterangan/add.php" class="text-decoration-none">
                            <div class="card quick-action-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-file-earmark-text-fill fs-1 text-info mb-2"></i>
                                    <h6>Buat Surat Keterangan</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="modules/laporan/index.php" class="text-decoration-none">
                            <div class="card quick-action-card text-center">
                                <div class="card-body">
                                    <i class="bi bi-file-bar-graph-fill fs-1 text-warning mb-2"></i>
                                    <h6>Laporan</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
