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
$query = "SELECT * FROM buku WHERE stok_tersedia <= 0 AND stok > 0 LIMIT 5";
$buku_habis = $conn->query($query);

// Peminjaman akan jatuh tempo (3 hari ke depan)
$today = date('Y-m-d');
$three_days = date('Y-m-d', strtotime('+3 days'));
$query = "SELECT p.*, b.judul, b.nomor_buku 
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.tanggal_jatuh_tempo BETWEEN ? AND ?
          AND p.status IN ('dipinjam', 'diperpanjang')
          ORDER BY p.tanggal_jatuh_tempo ASC
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $today, $three_days);
$stmt->execute();
$stmt->bind_result($pjm_id, $pjm_kode, $pjm_jenis, $pjm_peminjam_id, $pjm_buku_id, $pjm_tgl_pinjam, $pjm_tgl_tempo, $pjm_tgl_kembali, $pjm_status, $pjm_denda, $pjm_created, $pjm_updated, $pjm_judul, $pjm_nomor);
$akan_jatuh_tempo = array();
while ($stmt->fetch()) {
    $akan_jatuh_tempo[] = array(
        'kode_peminjaman' => $pjm_kode,
        'jenis_peminjam' => $pjm_jenis,
        'peminjam_id' => $pjm_peminjam_id,
        'judul' => $pjm_judul,
        'tanggal_jatuh_tempo' => $pjm_tgl_tempo
    );
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            overflow-x: hidden;
        }

        /* Navbar - FIXED CONTRAST */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 1.5rem;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-brand {
            color: #fff !important; /* FIXED: White text */
            font-weight: 700;
            font-size: 1.3rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .navbar-brand i {
            color: #ffd700; /* Gold icon */
        }
        
        .navbar .nav-link {
            color: rgba(255,255,255,0.95) !important; /* FIXED: White text */
            font-weight: 500;
        }
        
        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Sidebar - IMPROVED DESIGN */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            width: 250px;
            padding: 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.08);
            overflow-y: auto;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .sidebar .nav {
            padding: 1rem 0;
        }
        
        /* Sidebar Heading - GRADIENT SEPARATOR */
        .sidebar-heading {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 1rem 1.2rem 0.5rem;
            color: #64748b;
            background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%);
            margin-top: 0.5rem;
            position: relative;
        }
        
        .sidebar-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1.2rem;
            right: 1.2rem;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%);
        }
        
        /* Sidebar Links - MODERN STYLE */
        .sidebar .nav-link {
            font-weight: 500;
            color: #475569;
            padding: 0.75rem 1.2rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            margin: 0.2rem 0;
        }
        
        .sidebar .nav-link i {
            width: 22px;
            font-size: 1.1rem;
            margin-right: 0.7rem;
            transition: transform 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            border-left-color: #667eea;
            transform: translateX(3px);
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.15);
        }
        
        .sidebar .nav-link.active {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%);
            border-left-color: #667eea;
            font-weight: 600;
        }

        /* Main Content - COMPACT */
        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 1.2rem 1.5rem;
            min-height: calc(100vh - 56px);
        }
        
        /* Welcome Banner - COMPACT */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-banner h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .welcome-banner .date-time {
            text-align: right;
        }
        
        .welcome-banner .time {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .welcome-banner .date {
            font-size: 0.9rem;
            opacity: 0.95;
        }

        /* Stats Cards - COMPACT & MODERN */
        .stat-card {
            border: none;
            border-radius: 12px;
            padding: 1.3rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, rgba(255,255,255,0.5), rgba(255,255,255,0.2));
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card .card-body {
            padding: 0;
        }
        
        .stat-card h6 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.5rem;
            opacity: 0.95;
        }
        
        .stat-card h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.3rem 0;
            line-height: 1;
        }
        
        .stat-card .stat-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.25;
        }

        /* Alert Cards - COMPACT */
        .alert-card {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .alert-card h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        
        .alert-card ul {
            margin: 0;
            padding-left: 1.2rem;
            font-size: 0.9rem;
        }
        
        .alert-card li {
            margin-bottom: 0.4rem;
        }

        /* Quick Actions - COMPACT */
        .quick-action-card {
            border: none;
            border-radius: 10px;
            padding: 1.2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            background: white;
            height: 100%;
        }
        
        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .quick-action-card i {
            font-size: 2.5rem;
            margin-bottom: 0.6rem;
            display: block;
        }
        
        .quick-action-card h6 {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            color: #334155;
        }

        /* Table - COMPACT */
        .table-compact {
            font-size: 0.85rem;
        }
        
        .table-compact th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 0.6rem 0.8rem;
        }
        
        .table-compact td {
            padding: 0.6rem 0.8rem;
        }

        /* Badge - MODERN */
        .badge {
            padding: 0.35rem 0.6rem;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* Responsive - Optimize for 15.5" laptop */
        @media (max-width: 1920px) {
            .stat-card h2 {
                font-size: 1.8rem;
            }
            .stat-card .stat-icon {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 1366px) {
            .main-content {
                padding: 1rem;
            }
            .stat-card {
                padding: 1rem;
            }
            .stat-card h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-book-fill me-2"></i>
                <strong>E-Library STK Yakobus</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= $_SESSION['admin_username'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="change_password.php"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="sidebar-heading">DATA MASTER</li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/buku/index.php">
                    <i class="bi bi-book"></i>
                    Data Buku
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/mahasiswa/index.php">
                    <i class="bi bi-people"></i>
                    Data Mahasiswa
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/dosen/index.php">
                    <i class="bi bi-person-badge"></i>
                    Data Dosen
                </a>
            </li>
            
            <li class="sidebar-heading">TRANSAKSI</li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/peminjaman/index.php">
                    <i class="bi bi-arrow-left-right"></i>
                    Peminjaman Buku
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/perpanjangan/index.php">
                    <i class="bi bi-arrow-clockwise"></i>
                    Perpanjangan Buku
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/pengembalian/index.php">
                    <i class="bi bi-arrow-return-left"></i>
                    Pengembalian Buku
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/denda/index.php">
                    <i class="bi bi-cash-stack"></i>
                    Manajemen Denda
                </a>
            </li>
            
            <li class="sidebar-heading">LAYANAN</li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/surat_keterangan/index.php">
                    <i class="bi bi-file-earmark-text"></i>
                    Surat Keterangan
                </a>
            </li>
            
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/laporan/index.php">
                    <i class="bi bi-file-bar-graph"></i>
                    Laporan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="modules/backup/index.php">
                    <i class="bi bi-cloud-download"></i>
                    Backup Database
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h4>Selamat Datang, <?= $_SESSION['admin_username'] ?>!</h4>
                <small><i class="bi bi-calendar3 me-2"></i><?= formatTanggalIndo(date('Y-m-d')) ?></small>
            </div>
            <div class="date-time">
                <div class="time" id="clock">14:16</div>
                <div class="date">WIT</div>
            </div>
        </div>

        <?php if (isset($_SESSION['alert'])): $alert = $_SESSION['alert']; ?>
        <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
            <?= $alert['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['alert']); endif; ?>

        <!-- Statistics Cards - 2 ROWS COMPACT -->
        <div class="row g-3 mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <h6>Total Buku</h6>
                        <h2><?= number_format($stats['total_buku']) ?></h2>
                        <small><i class="bi bi-box"></i> Koleksi Perpustakaan</small>
                        <i class="bi bi-book-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body">
                        <h6>Mahasiswa</h6>
                        <h2><?= number_format($stats['total_mahasiswa']) ?></h2>
                        <small><i class="bi bi-mortarboard"></i> Anggota Aktif</small>
                        <i class="bi bi-people-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);">
                    <div class="card-body">
                        <h6>Dosen</h6>
                        <h2><?= number_format($stats['total_dosen']) ?></h2>
                        <small><i class="bi bi-person-workspace"></i> Pengajar</small>
                        <i class="bi bi-person-badge-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body">
                        <h6>Peminjaman Aktif</h6>
                        <h2><?= number_format($stats['peminjaman_aktif']) ?></h2>
                        <small><i class="bi bi-clock-history"></i> Sedang Dipinjam</small>
                        <i class="bi bi-arrow-left-right stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                    <div class="card-body">
                        <h6>Keterlambatan</h6>
                        <h2><?= number_format($stats['keterlambatan']) ?></h2>
                        <small><i class="bi bi-exclamation-triangle"></i> Perlu Tindak Lanjut</small>
                        <i class="bi bi-exclamation-triangle-fill stat-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="stat-card text-white" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                    <div class="card-body">
                        <h6>Total Denda Belum Dibayar</h6>
                        <h2><?= formatRupiah($stats['total_denda']) ?></h2>
                        <small><i class="bi bi-wallet2"></i> Tunggakan</small>
                        <i class="bi bi-cash-stack stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts & Quick Actions - SIDE BY SIDE -->
        <div class="row g-3 mb-3">
            <!-- Left Column: Alerts -->
            <div class="col-lg-6">
                <?php if ($buku_habis->num_rows > 0): ?>
                <div class="alert-card" style="background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%); border-left: 4px solid #ffc107;">
                    <h5 class="text-warning"><i class="bi bi-exclamation-circle-fill me-2"></i>Stok Buku Habis</h5>
                    <ul class="mb-0">
                        <?php while ($buku = $buku_habis->fetch_assoc()): ?>
                        <li><strong><?= $buku['judul'] ?></strong> (<?= $buku['nomor_buku'] ?>)</li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($akan_jatuh_tempo)): ?>
                <div class="alert-card" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #ff9800;">
                    <h5 class="text-warning"><i class="bi bi-clock-history me-2"></i>Jatuh Tempo (3 Hari)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-compact mb-0">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Buku</th>
                                    <th>Jatuh Tempo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($akan_jatuh_tempo as $pjm): ?>
                                <tr>
                                    <td><code><?= $pjm['kode_peminjaman'] ?></code></td>
                                    <td><?= $pjm['judul'] ?></td>
                                    <td><span class="badge bg-warning"><?= formatTanggalIndo($pjm['tanggal_jatuh_tempo']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Quick Actions -->
            <div class="col-lg-6">
                <h5 class="mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Aksi Cepat</h5>
                <div class="row g-2">
                    <div class="col-3">
                        <a href="modules/peminjaman/add.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-plus-circle-fill text-primary"></i>
                                <h6>Tambah Peminjaman</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="modules/pengembalian/index.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-arrow-return-left text-success"></i>
                                <h6>Pengembalian Buku</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="modules/surat_keterangan/add.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-file-earmark-text-fill text-info"></i>
                                <h6>Buat Surat</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="modules/laporan/index.php" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-file-bar-graph-fill text-warning"></i>
                                <h6>Laporan</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clock').textContent = hours + ':' + minutes;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>