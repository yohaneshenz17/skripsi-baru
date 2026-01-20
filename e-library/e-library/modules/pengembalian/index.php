<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query peminjaman yang bisa dikembalikan (status: dipinjam, diperpanjang, terlambat)
$query = "SELECT p.*, b.judul, b.nomor_buku 
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";

if ($status_filter != 'all') {
    $query .= " AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($search)) {
    $query .= " AND (p.kode_peminjaman LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR b.judul LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$query .= " ORDER BY p.tanggal_jatuh_tempo ASC";
$result = $conn->query($query);

// Query riwayat pengembalian
$query_riwayat = "SELECT p.*, b.judul, b.nomor_buku, pg.tanggal_kembali, pg.keterlambatan_hari, 
                  pg.denda, pg.denda_dibayar, pg.sisa_denda, pg.metode_pembayaran
                  FROM pengembalian pg
                  JOIN peminjaman p ON pg.peminjaman_id = p.id
                  JOIN buku b ON p.buku_id = b.id
                  ORDER BY pg.created_at DESC
                  LIMIT 20";
$result_riwayat = $conn->query($query_riwayat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Buku - E-Library STK Yakobus</title>
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
        .table tbody td {
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .info-box ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
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
        
        /* Badges */
        .badge {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
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
    <nav class="sidebar">
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
                <a class="nav-link active" href="<?= BASE_URL ?>modules/pengembalian/index.php">
                    <i class="bi bi-arrow-return-left"></i>Pengembalian Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php">
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
        <div class="page-header">
            <h1><i class="bi bi-arrow-return-left me-2"></i>Pengembalian Buku</h1>
        </div>

        <?php showAlert(); ?>
        
        <div class="info-box">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Informasi Pengembalian:</strong>
            <ul class="mb-0 mt-2">
                <li>Buku dapat dikembalikan <strong>kapan saja</strong> dari H peminjaman hingga setelah jatuh tempo</li>
                <li>Denda keterlambatan: <strong>Rp 1.000 per hari</strong> terhitung sejak H+1 jatuh tempo</li>
                <li>Mahasiswa dapat mengembalikan buku <strong>sekaligus atau satu per satu</strong></li>
                <li>Stok buku akan otomatis <strong>bertambah</strong> setelah pengembalian</li>
            </ul>
        </div>

        <!-- Filter & Search -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua</option>
                            <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="diperpanjang" <?= $status_filter == 'diperpanjang' ? 'selected' : '' ?>>Diperpanjang</option>
                            <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cari Peminjaman</label>
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

        <!-- Tabel Peminjaman Aktif -->
        <div class="card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Peminjaman Aktif</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="10%">KODE</th>
                                <th width="18%">PEMINJAM</th>
                                <th width="20%">BUKU</th>
                                <th width="10%">TGL PINJAM</th>
                                <th width="10%">JATUH TEMPO</th>
                                <th width="10%">STATUS</th>
                                <th width="10%">DENDA</th>
                                <th width="7%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    // Get nama peminjam
                                    $nama_peminjam = getNamaPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    $identifier = getIdentifierPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    
                                    // Hitung keterlambatan dan denda
                                    $today = new DateTime();
                                    $jatuh_tempo = new DateTime($row['tanggal_jatuh_tempo']);
                                    $selisih = $jatuh_tempo->diff($today);
                                    
                                    if ($today > $jatuh_tempo) {
                                        $keterlambatan_hari = $selisih->days;
                                        $denda = $keterlambatan_hari * 1000;
                                        $status_badge = 'danger';
                                        $status_text = 'Terlambat ' . $keterlambatan_hari . ' hari';
                                    } else {
                                        $keterlambatan_hari = 0;
                                        $denda = 0;
                                        if ($row['status'] == 'diperpanjang') {
                                            $status_badge = 'info';
                                            $status_text = 'Diperpanjang';
                                        } else {
                                            $status_badge = 'success';
                                            $status_text = 'Dipinjam';
                                        }
                                    }
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-person-badge"></i> <?= $identifier ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted">No: <?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_pinjam']) ?></td>
                                <td><?= formatTanggalIndo($row['tanggal_jatuh_tempo']) ?></td>
                                <td><span class="badge bg-<?= $status_badge ?>"><?= $status_text ?></span></td>
                                <td>
                                    <?php if ($denda > 0): ?>
                                        <span class="text-danger fw-bold"><?= formatRupiah($denda) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Proses Pengembalian">
                                        <i class="bi bi-check-circle"></i> Kembalikan
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada peminjaman yang perlu dikembalikan
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIWAYAT PENGEMBALIAN -->
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Pengembalian (20 Terakhir)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="10%">KODE</th>
                                <th width="18%">PEMINJAM</th>
                                <th width="18%">BUKU</th>
                                <th width="10%">TGL KEMBALI</th>
                                <th width="8%">KETERLAMBATAN</th>
                                <th width="10%">DENDA</th>
                                <th width="10%">DIBAYAR</th>
                                <th width="11%">METODE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result_riwayat->num_rows > 0):
                                while ($row = $result_riwayat->fetch_assoc()):
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
                                    <?php if ($row['keterlambatan_hari'] > 0): ?>
                                        <span class="badge bg-danger"><?= $row['keterlambatan_hari'] ?> hari</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Tepat Waktu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['denda'] > 0): ?>
                                        <span class="text-danger"><?= formatRupiah($row['denda']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['denda_dibayar'] > 0): ?>
                                        <span class="text-success"><?= formatRupiah($row['denda_dibayar']) ?></span>
                                        <?php if ($row['sisa_denda'] > 0): ?>
                                            <br><small class="text-danger">Sisa: <?= formatRupiah($row['sisa_denda']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $metode_icons = [
                                        'cash' => '<i class="bi bi-cash"></i> Cash',
                                        'transfer' => '<i class="bi bi-credit-card"></i> Transfer',
                                        'tagihan_studi' => '<i class="bi bi-receipt"></i> Tagihan',
                                        'waive' => '<i class="bi bi-x-circle"></i> Waive'
                                    ];
                                    echo $metode_icons[$row['metode_pembayaran']] ?? '-';
                                    ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada riwayat pengembalian
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