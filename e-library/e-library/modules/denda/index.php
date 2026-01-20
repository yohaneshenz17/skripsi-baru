<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Fungsi hitung denda berjalan
function hitungDendaBerjalan($conn) {
    $today = date('Y-m-d');
    
    // Query untuk mendapatkan peminjaman yang terlambat
    $query = "SELECT p.id, p.tanggal_jatuh_tempo, p.tanggal_pinjam
              FROM peminjaman p
              WHERE p.tanggal_jatuh_tempo < '$today'
              AND p.tanggal_kembali IS NULL
              AND p.status NOT IN ('dikembalikan', 'selesai')";
    
    $result = $conn->query($query);
    
    $total_denda = 0;
    $total_buku = 0;
    $total_hari_terlambat = 0;
    
    // TARIF TETAP - ganti sesuai kebijakan perpustakaan
    $tarif_denda = 1000; // Rp 1.000 per hari keterlambatan
    
    while ($row = $result->fetch_assoc()) {
        $tanggal_jatuh_tempo = new DateTime($row['tanggal_jatuh_tempo']);
        $today_date = new DateTime($today);
        $hari_terlambat = $tanggal_jatuh_tempo->diff($today_date)->days;
        
        if ($hari_terlambat > 0) {
            $total_denda += ($hari_terlambat * $tarif_denda);
            $total_buku++;
            $total_hari_terlambat += $hari_terlambat;
        }
    }
    
    return [
        'total_denda' => $total_denda,
        'total_buku' => $total_buku,
        'total_hari' => $total_hari_terlambat
    ];
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'belum_lunas';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query denda
$query = "SELECT pg.*, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, p.tanggal_pinjam, 
          p.tanggal_jatuh_tempo, b.judul, b.nomor_buku
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          WHERE pg.denda > 0";

if ($status_filter == 'belum_lunas') {
    $query .= " AND pg.sisa_denda > 0";
} elseif ($status_filter == 'lunas') {
    $query .= " AND pg.sisa_denda = 0";
}

if (!empty($search)) {
    $query .= " AND (p.kode_peminjaman LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR b.judul LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$query .= " ORDER BY pg.created_at DESC";
$result = $conn->query($query);

// Statistik denda
$stats_query = "SELECT 
                COUNT(*) as total_denda,
                SUM(denda) as total_nominal_denda,
                SUM(denda_dibayar) as total_dibayar,
                SUM(sisa_denda) as total_sisa,
                SUM(CASE WHEN sisa_denda > 0 THEN 1 ELSE 0 END) as belum_lunas,
                SUM(CASE WHEN sisa_denda = 0 THEN 1 ELSE 0 END) as sudah_lunas
                FROM pengembalian
                WHERE denda > 0";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Hitung denda berjalan
$denda_berjalan = hitungDendaBerjalan($conn);

// Total exposure = denda berjalan + sisa denda yang belum dibayar
$total_exposure = $denda_berjalan['total_denda'] + $stats['total_sisa'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Denda - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 1.5rem;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.3rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            width: 250px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.08);
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
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
        .sidebar .nav-link {
            font-weight: 500;
            color: #475569;
            padding: 0.75rem 1.2rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link i {
            width: 22px;
            font-size: 1.1rem;
            margin-right: 0.7rem;
        }
        .sidebar .nav-link:hover {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            border-left-color: #667eea;
            transform: translateX(3px);
        }
        .sidebar .nav-link.active {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%);
            border-left-color: #667eea;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 1.5rem;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #f093fb;
        }
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        /* Card */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        /* Table */
        .table {
            font-size: 0.9rem;
        }
        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #475569;
            border: none;
            padding: 0.9rem 0.75rem;
        }
        .table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s;
        }
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Stat Card Mini */
        .stat-card-mini {
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card-mini:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .stat-card-mini h6 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .stat-card-mini h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }
        .stat-card-mini small {
            font-size: 0.75rem;
        }
        
        /* Stat Card Clickable */
        .stat-card-clickable {
            cursor: pointer;
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card-clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .stat-card-clickable h6 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .stat-card-clickable h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }
        .stat-card-clickable small {
            font-size: 0.75rem;
        }
        .stat-card-clickable .detail-info {
            font-size: 0.7rem;
            margin-top: 0.3rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-half"></i> E-Library STK Yakobus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= $_SESSION['admin_username'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/profile/index.php">
                                <i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="sidebar-heading">MENU UTAMA</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li class="sidebar-heading">MASTER DATA</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php">
                    <i class="bi bi-book"></i>Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php">
                    <i class="bi bi-mortarboard"></i>Mahasiswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php">
                    <i class="bi bi-person-workspace"></i>Dosen
                </a>
            </li>
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/peminjaman/index.php">
                    <i class="bi bi-arrow-left-right"></i>Peminjaman
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php">
                    <i class="bi bi-box-arrow-in-down"></i>Pengembalian
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="<?= BASE_URL ?>modules/denda/index.php">
                    <i class="bi bi-cash-stack"></i>Denda
                </a>
            </li>
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php">
                    <i class="bi bi-file-bar-graph"></i>Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php">
                    <i class="bi bi-cloud-download"></i>Backup Database
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-cash-stack me-2"></i>Manajemen Denda</h1>
            <a href="laporan.php" class="btn btn-primary">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Denda
            </a>
        </div>

        <?php showAlert(); ?>

        <!-- Statistik Denda -->
        <div class="row g-3 mb-4">
            <!-- Denda Berjalan (NEW) -->
            <div class="col-md-4">
                <div class="stat-card-clickable" style="border-left-color: #ffa500;" 
                     onclick="window.location.href='<?= BASE_URL ?>modules/peminjaman/index.php?filter=terlambat'">
                    <h6 style="color: #ff8800;">Denda Berjalan Hari Ini</h6>
                    <h3 class="text-warning">
                        <i class="bi bi-hourglass-split me-2"></i>
                        <?= formatRupiah($denda_berjalan['total_denda']) ?>
                    </h3>
                    <small class="text-muted">
                        <?= $denda_berjalan['total_buku'] ?> buku • Total <?= $denda_berjalan['total_hari'] ?> hari keterlambatan
                    </small>
                    <div class="detail-info text-warning">
                        <i class="bi bi-cursor-fill"></i> Klik untuk lihat detail peminjaman terlambat
                    </div>
                </div>
            </div>

            <!-- Total Exposure (NEW) -->
            <div class="col-md-4">
                <div class="stat-card-mini" style="border-left-color: #dc3545;">
                    <h6 style="color: #dc3545;">Total Exposure Denda</h6>
                    <h3 class="text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= formatRupiah($total_exposure) ?>
                    </h3>
                    <small class="text-muted">Denda Berjalan + Belum Dibayar</small>
                </div>
            </div>

            <!-- Total Nominal Denda (EXISTING) -->
            <div class="col-md-4">
                <div class="stat-card-mini" style="border-left-color: #ff6b6b;">
                    <h6>Total Nominal Denda</h6>
                    <h3 class="text-danger"><?= formatRupiah($stats['total_nominal_denda']) ?></h3>
                    <small class="text-muted"><?= number_format($stats['total_denda']) ?> transaksi</small>
                </div>
            </div>

            <!-- Sudah Dibayar (EXISTING) -->
            <div class="col-md-4">
                <div class="stat-card-mini" style="border-left-color: #51cf66;">
                    <h6>Sudah Dibayar</h6>
                    <h3 class="text-success"><?= formatRupiah($stats['total_dibayar']) ?></h3>
                    <small class="text-muted"><?= $stats['sudah_lunas'] ?> transaksi lunas</small>
                </div>
            </div>

            <!-- Belum Dibayar (EXISTING) -->
            <div class="col-md-4">
                <div class="stat-card-mini" style="border-left-color: #ffa94d;">
                    <h6>Belum Dibayar</h6>
                    <h3 class="text-warning"><?= formatRupiah($stats['total_sisa']) ?></h3>
                    <small class="text-muted"><?= $stats['belum_lunas'] ?> transaksi tertunda</small>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="belum_lunas" <?= $status_filter == 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                            <option value="lunas" <?= $status_filter == 'lunas' ? 'selected' : '' ?>>Sudah Lunas</option>
                            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cari</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Kode peminjaman atau judul buku..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Denda -->
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    <?= $status_filter == 'belum_lunas' ? 'Denda Belum Lunas' : ($status_filter == 'lunas' ? 'Denda Sudah Lunas' : 'Semua Denda') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="4%">NO</th>
                                <th width="10%">KODE</th>
                                <th width="16%">PEMINJAM</th>
                                <th width="16%">BUKU</th>
                                <th width="10%">TGL KEMBALI</th>
                                <th width="7%">TERLAMBAT</th>
                                <th width="10%">DENDA</th>
                                <th width="10%">DIBAYAR</th>
                                <th width="10%">SISA</th>
                                <th width="7%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $nama_peminjam = getNamaPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    $identifier = getIdentifierPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><?= $identifier ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_kembali']) ?></td>
                                <td>
                                    <span class="badge bg-danger"><?= $row['keterlambatan_hari'] ?> hari</span>
                                </td>
                                <td>
                                    <strong class="text-danger"><?= formatRupiah($row['denda']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($row['denda_dibayar'] > 0): ?>
                                        <span class="text-success"><?= formatRupiah($row['denda_dibayar']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['sisa_denda'] > 0): ?>
                                        <strong class="text-warning"><?= formatRupiah($row['sisa_denda']) ?></strong>
                                    <?php else: ?>
                                        <span class="badge bg-success">LUNAS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['sisa_denda'] > 0): ?>
                                        <a href="bayar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Bayar Denda">
                                            <i class="bi bi-cash"></i> Bayar
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-check-circle"></i> Lunas
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data denda
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>