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
          WHERE p.tanggal_jatuh_tempo BETWEEN '$today' AND '$three_days'
          AND p.status IN ('dipinjam', 'diperpanjang')
          ORDER BY p.tanggal_jatuh_tempo ASC";
$akan_jatuh_tempo = $conn->query($query);
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
    <style>
        /* Enhanced Dashboard Styles */
        .dashboard-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            font-size: 2rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 15px 0 5px 0;
        }
        
        .stat-label {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .quick-action-card {
            border: none;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            cursor: pointer;
        }
        
        .quick-action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .quick-action-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 2rem;
        }
        
        .alert-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .info-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Welcome Section -->
                <div class="dashboard-welcome">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="bi bi-hand-wave-fill me-2"></i>
                                Selamat Datang, <?= $_SESSION['admin_username'] ?>!
                            </h2>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-calendar3 me-2"></i>
                                <?= formatTanggalIndo(date('Y-m-d')) ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-inline-block text-center">
                                <div class="fs-1 fw-bold"><?= date('H:i') ?></div>
                                <small>WIT</small>
                            </div>
                        </div>
                    </div>
                </div>

                <?php showAlert(); ?>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card bg-gradient-primary text-brown">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Total Buku</div>
                                    <div class="stat-number"><?= number_format($stats['total_buku']) ?></div>
                                    <small><i class="bi bi-graph-up me-1"></i>Koleksi Perpustakaan</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-book-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card bg-success text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Mahasiswa</div>
                                    <div class="stat-number"><?= number_format($stats['total_mahasiswa']) ?></div>
                                    <small><i class="bi bi-people me-1"></i>Anggota Aktif</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card bg-info text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Dosen</div>
                                    <div class="stat-number"><?= number_format($stats['total_dosen']) ?></div>
                                    <small><i class="bi bi-mortarboard me-1"></i>Pengajar</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card bg-warning text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Peminjaman Aktif</div>
                                    <div class="stat-number"><?= number_format($stats['peminjaman_aktif']) ?></div>
                                    <small><i class="bi bi-clock-history me-1"></i>Sedang Dipinjam</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="stat-card bg-danger text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Keterlambatan</div>
                                    <div class="stat-number"><?= number_format($stats['keterlambatan']) ?></div>
                                    <small><i class="bi bi-exclamation-triangle me-1"></i>Perlu Tindak Lanjut</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="stat-card bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-label">Total Denda Belum Dibayar</div>
                                    <div class="stat-number"><?= formatRupiah($stats['total_denda']) ?></div>
                                    <small><i class="bi bi-currency-dollar me-1"></i>Tunggakan</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($buku_habis->num_rows > 0): ?>
                <div class="alert-card alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex">
                        <div class="fs-2 me-3">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-2">
                                <i class="bi bi-inbox me-2"></i>Peringatan: Buku Stok Habis
                            </h5>
                            <ul class="mb-0">
                                <?php while ($buku = $buku_habis->fetch_assoc()): ?>
                                <li>
                                    <strong><?= $buku['judul'] ?></strong> 
                                    (<?= $buku['nomor_buku'] ?>) - 
                                    Stok: <?= $buku['stok_tersedia'] ?>/<?= $buku['stok'] ?>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Akan Jatuh Tempo -->
                <?php if ($akan_jatuh_tempo->num_rows > 0): ?>
                <div class="card alert-card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Peminjaman Akan Jatuh Tempo (3 Hari ke Depan)
                        </h5>
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
                                        <td><span class="badge bg-primary"><?= $pjm['kode_peminjaman'] ?></span></td>
                                        <td>
                                            <?= getNamaPeminjam($conn, $pjm['jenis_peminjam'], $pjm['peminjam_id']) ?>
                                            <br><small class="text-muted"><?= ucfirst($pjm['jenis_peminjam']) ?></small>
                                        </td>
                                        <td><?= $pjm['judul'] ?></td>
                                        <td><?= formatTanggalIndo($pjm['tanggal_jatuh_tempo']) ?></td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock me-1"></i><?= round($sisa_hari) ?> hari
                                            </span>
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
                <h4 class="mb-4">
                    <i class="bi bi-lightning-fill me-2 text-warning"></i>
                    Aksi Cepat
                </h4>
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <a href="modules/peminjaman/add.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <div class="quick-action-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-plus-circle-fill"></i>
                                </div>
                                <h6 class="mb-1">Tambah Peminjaman</h6>
                                <small class="text-muted">Pinjam buku baru</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="modules/pengembalian/index.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <div class="quick-action-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-arrow-return-left"></i>
                                </div>
                                <h6 class="mb-1">Pengembalian Buku</h6>
                                <small class="text-muted">Kembalikan buku</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="modules/surat_keterangan/add.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <div class="quick-action-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <h6 class="mb-1">Buat Surat</h6>
                                <small class="text-muted">Surat Keterangan</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <a href="modules/laporan/index.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <div class="quick-action-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-file-bar-graph-fill"></i>
                                </div>
                                <h6 class="mb-1">Laporan</h6>
                                <small class="text-muted">Cetak laporan</small>
                            </div>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto update waktu
        setInterval(function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.querySelector('.fs-1.fw-bold').textContent = hours + ':' + minutes;
        }, 60000);
    </script>
</body>
</html>