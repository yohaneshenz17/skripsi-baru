<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$metode_filter = isset($_GET['metode']) ? $_GET['metode'] : 'all';

// Query laporan denda
$query = "SELECT pg.*, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, 
          b.judul, b.nomor_buku
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          WHERE pg.denda > 0 
          AND MONTH(pg.tanggal_kembali) = ? 
          AND YEAR(pg.tanggal_kembali) = ?";

if ($metode_filter != 'all') {
    $query .= " AND pg.metode_pembayaran = '" . $conn->real_escape_string($metode_filter) . "'";
}

$query .= " ORDER BY pg.tanggal_kembali ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Statistik periode
$stats_query = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(denda) as total_denda,
                SUM(denda_dibayar) as total_dibayar,
                SUM(sisa_denda) as total_sisa,
                SUM(CASE WHEN metode_pembayaran = 'cash' THEN denda_dibayar ELSE 0 END) as cash,
                SUM(CASE WHEN metode_pembayaran = 'transfer' THEN denda_dibayar ELSE 0 END) as transfer,
                SUM(CASE WHEN metode_pembayaran = 'tagihan_studi' THEN denda_dibayar ELSE 0 END) as tagihan_studi,
                SUM(CASE WHEN metode_pembayaran = 'waive' THEN denda ELSE 0 END) as waive
                FROM pengembalian
                WHERE denda > 0 
                AND MONTH(tanggal_kembali) = ? 
                AND YEAR(tanggal_kembali) = ?";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ii", $bulan, $tahun);
$stmt->execute();
$stmt->bind_result($total_transaksi, $total_denda, $total_dibayar, $total_sisa, 
                   $cash, $transfer, $tagihan_studi, $waive);
$stmt->fetch();
$stmt->close();

// Nama bulan
$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$periode_text = $nama_bulan[str_pad($bulan, 2, '0', STR_PAD_LEFT)] . ' ' . $tahun;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Denda - E-Library STK Yakobus</title>
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
            border: 1px solid #dee2e6;
            padding: 0.7rem 0.5rem;
        }
        .table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody td {
            padding: 0.7rem 0.5rem;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        
        /* Stat Summary */
        .stat-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .stat-summary h3 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 8px;
        }
        .stat-item label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 0.25rem;
        }
        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        /* Buttons */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Form */
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        /* Print Styles */
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            .navbar { display: none !important; }
            .main-content { margin-left: 0 !important; margin-top: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
            .page-header { border: none !important; }
            body { background: white !important; }
            .stat-summary { 
                background: white !important; 
                color: black !important; 
                border: 2px solid #667eea !important; 
            }
            .stat-item { 
                background: white !important; 
                border: 1px solid #dee2e6 !important; 
            }
            .table { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-fill me-2"></i>E-Library STK Yakobus
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle me-1"></i><?= $_SESSION['admin_username'] ?>
                </span>
                <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar no-print">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li class="sidebar-heading">DATA MASTER</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php">
                    <i class="bi bi-book"></i>Data Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php">
                    <i class="bi bi-people"></i>Data Mahasiswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php">
                    <i class="bi bi-person-badge"></i>Data Dosen
                </a>
            </li>
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/peminjaman/index.php">
                    <i class="bi bi-arrow-left-right"></i>Peminjaman Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php">
                    <i class="bi bi-arrow-clockwise"></i>Perpanjangan Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php">
                    <i class="bi bi-arrow-return-left"></i>Pengembalian Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="<?= BASE_URL ?>modules/denda/index.php">
                    <i class="bi bi-cash-stack"></i>Manajemen Denda
                </a>
            </li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php">
                    <i class="bi bi-file-earmark-text"></i>Surat Keterangan
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
        <div class="page-header d-flex justify-content-between align-items-center no-print">
            <h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Denda</h1>
            <div>
                <button onclick="window.print()" class="btn btn-secondary me-2">
                    <i class="bi bi-printer me-2"></i>Cetak PDF
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3 no-print">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" 
                                        <?= $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                    <?= $nama_bulan[str_pad($m, 2, '0', STR_PAD_LEFT)] ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $metode_filter == 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="cash" <?= $metode_filter == 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="transfer" <?= $metode_filter == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                            <option value="tagihan_studi" <?= $metode_filter == 'tagihan_studi' ? 'selected' : '' ?>>Tagihan Studi</option>
                            <option value="waive" <?= $metode_filter == 'waive' ? 'selected' : '' ?>>Waive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Header Laporan -->
        <div class="text-center mb-4">
            <h2>LAPORAN DENDA KETERLAMBATAN</h2>
            <h4>PERPUSTAKAAN STK YAKOBUS MERAUKE</h4>
            <p class="mb-0">Periode: <strong><?= $periode_text ?></strong></p>
            <?php if ($metode_filter != 'all'): ?>
                <p class="mb-0">Metode: <strong><?= ucfirst($metode_filter) ?></strong></p>
            <?php endif; ?>
            <hr>
        </div>

        <!-- Statistik Ringkasan -->
        <div class="stat-summary">
            <h3><i class="bi bi-bar-chart-fill me-2"></i>Ringkasan Statistik</h3>
            <div class="stat-grid">
                <div class="stat-item">
                    <label>Total Transaksi</label>
                    <h4><?= number_format($total_transaksi) ?></h4>
                </div>
                <div class="stat-item">
                    <label>Total Denda</label>
                    <h4><?= formatRupiah($total_denda) ?></h4>
                </div>
                <div class="stat-item">
                    <label>Sudah Dibayar</label>
                    <h4><?= formatRupiah($total_dibayar) ?></h4>
                </div>
                <div class="stat-item">
                    <label>Sisa Denda</label>
                    <h4><?= formatRupiah($total_sisa) ?></h4>
                </div>
            </div>
            <hr class="my-3" style="opacity: 0.3;">
            <div class="stat-grid">
                <div class="stat-item">
                    <label><i class="bi bi-cash"></i> Cash</label>
                    <h4><?= formatRupiah($cash) ?></h4>
                </div>
                <div class="stat-item">
                    <label><i class="bi bi-credit-card"></i> Transfer</label>
                    <h4><?= formatRupiah($transfer) ?></h4>
                </div>
                <div class="stat-item">
                    <label><i class="bi bi-receipt"></i> Tagihan Studi</label>
                    <h4><?= formatRupiah($tagihan_studi) ?></h4>
                </div>
                <div class="stat-item">
                    <label><i class="bi bi-x-circle"></i> Waive</label>
                    <h4><?= formatRupiah($waive) ?></h4>
                </div>
            </div>
        </div>

        <!-- Tabel Detail -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="3%" class="text-center">NO</th>
                                <th width="10%">KODE</th>
                                <th width="15%">PEMINJAM</th>
                                <th width="18%">BUKU</th>
                                <th width="10%">TGL KEMBALI</th>
                                <th width="7%" class="text-center">TERLAMBAT</th>
                                <th width="10%" class="text-end">DENDA</th>
                                <th width="10%" class="text-end">DIBAYAR</th>
                                <th width="10%" class="text-end">SISA</th>
                                <th width="7%" class="text-center">METODE</th>
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
                                <td class="text-center"><?= $no++ ?></td>
                                <td><small><?= $row['kode_peminjaman'] ?></small></td>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><?= $identifier ?></small>
                                </td>
                                <td>
                                    <?= $row['judul'] ?><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_kembali']) ?></td>
                                <td class="text-center"><?= $row['keterlambatan_hari'] ?> hari</td>
                                <td class="text-end"><?= formatRupiah($row['denda']) ?></td>
                                <td class="text-end"><?= formatRupiah($row['denda_dibayar']) ?></td>
                                <td class="text-end">
                                    <?php if ($row['sisa_denda'] > 0): ?>
                                        <strong class="text-warning"><?= formatRupiah($row['sisa_denda']) ?></strong>
                                    <?php else: ?>
                                        <span class="text-success">LUNAS</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <small>
                                    <?php
                                    $metode_icons = [
                                        'cash' => 'Cash',
                                        'transfer' => 'Transfer',
                                        'tagihan_studi' => 'Tagihan',
                                        'waive' => 'Waive'
                                    ];
                                    echo $metode_icons[$row['metode_pembayaran']] ?? '-';
                                    ?>
                                    </small>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Tidak ada data denda untuk periode ini
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if ($result->num_rows > 0): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">TOTAL:</td>
                                <td class="text-end"><?= formatRupiah($total_denda) ?></td>
                                <td class="text-end"><?= formatRupiah($total_dibayar) ?></td>
                                <td class="text-end"><?= formatRupiah($total_sisa) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer Laporan -->
        <div class="row mt-4">
            <div class="col-6">
                <p class="mb-0"><small>Dicetak: <?= date('d F Y, H:i') ?> WIT</small></p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1">Merauke, <?= date('d F Y') ?></p>
                <p class="mb-1">Petugas Perpustakaan</p>
                <br><br>
                <p class="mb-0">____________________</p>
            </div>
        </div>

        <!-- Catatan Khusus Tagihan Studi -->
        <?php if ($tagihan_studi > 0 && ($metode_filter == 'all' || $metode_filter == 'tagihan_studi')): ?>
        <div class="alert alert-info mt-4 no-print">
            <h5><i class="bi bi-info-circle-fill me-2"></i>Catatan Penting</h5>
            <p class="mb-0">
                Total denda yang dialihkan ke tagihan studi: <strong><?= formatRupiah($tagihan_studi) ?></strong><br>
                Laporan ini dapat diserahkan ke Bendahara Kampus untuk ditindaklanjuti melalui sistem akademik.
            </p>
        </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>