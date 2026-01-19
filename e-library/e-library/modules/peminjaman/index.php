<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();
updateStatusPeminjaman($conn);

// Get filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$where = "WHERE p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";

if (!empty($search)) {
    $where .= " AND (p.kode_peminjaman LIKE '%$search%' OR b.judul LIKE '%$search%' OR b.nomor_buku LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $where .= " AND p.status = '$status_filter'";
}

$query = "SELECT p.*, b.judul, b.nomor_buku, p.jenis_peminjam, p.peminjam_id
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          $where
          ORDER BY p.created_at DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Buku - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        /* Navbar - CONSISTENT */
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
        
        /* Sidebar - CONSISTENT */
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
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 0.75rem;
        }
        .table tbody tr {
            transition: all 0.2s ease;
        }
        .table tbody tr:hover {
            background: rgba(240, 147, 251, 0.05);
            transform: scale(1.01);
        }
        .table tbody td {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        
        /* Button */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
        }
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4);
            color: white;
        }
        .btn-sm {
            padding: 0.35rem 0.7rem;
            font-size: 0.85rem;
        }
        
        /* Badge */
        .badge {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
            border-radius: 6px;
        }
        
        /* Search Box */
        .search-box {
            max-width: 400px;
        }
        .search-box .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }
        .search-box .form-control:focus {
            border-color: #f093fb;
            box-shadow: 0 0 0 0.2rem rgba(240, 147, 251, 0.15);
        }
        .search-box .btn {
            border-radius: 8px;
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        /* Filter */
        .filter-box {
            max-width: 200px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-fill me-2"></i>
                <strong>E-Library STK Yakobus</strong>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= $_SESSION['admin_username'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>change_password.php"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
                <a class="nav-link active" href="<?= BASE_URL ?>modules/peminjaman/index.php">
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
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-arrow-left-right me-2"></i>Peminjaman Buku</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Peminjaman
            </a>
        </div>

        <?php showAlert(); ?>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" action="">
                            <div class="input-group search-box">
                                <input type="text" class="form-control" name="search" placeholder="Cari kode, judul buku, atau nomor buku..." value="<?= $search ?>">
                                <button class="btn btn-warning" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3 ms-auto">
                        <form method="GET" action="">
                            <select class="form-select filter-box" name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                <option value="diperpanjang" <?= $status_filter == 'diperpanjang' ? 'selected' : '' ?>>Diperpanjang</option>
                                <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Kode</th>
                                <th width="15%">Peminjam</th>
                                <th width="20%">Buku</th>
                                <th width="10%">Tgl Pinjam</th>
                                <th width="10%">Jatuh Tempo</th>
                                <th width="8%">Status</th>
                                <th width="10%">Denda</th>
                                <th width="15%">Aksi</th>
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
                                    
                                    // Hitung keterlambatan
                                    $keterlambatan = hitungKeterlambatan($row['tanggal_jatuh_tempo']);
                                    $denda = $keterlambatan > 0 ? hitungDenda($keterlambatan) : 0;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-<?= $row['jenis_peminjam'] == 'mahasiswa' ? 'person' : 'person-badge' ?>"></i>
                                        <?= $identifier ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_pinjam']) ?></td>
                                <td>
                                    <?= formatTanggalIndo($row['tanggal_jatuh_tempo']) ?>
                                    <?php if ($keterlambatan > 0): ?>
                                        <br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> <?= $keterlambatan ?> hari</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    if ($row['status'] == 'dipinjam') $badge_class = 'primary';
                                    elseif ($row['status'] == 'diperpanjang') $badge_class = 'info';
                                    elseif ($row['status'] == 'terlambat') $badge_class = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge_class ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td>
                                    <?php if ($denda > 0): ?>
                                        <span class="text-danger"><strong><?= formatRupiah($denda) ?></strong></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] != 'dikembalikan'): ?>
                                        <a href="../perpanjangan/index.php?peminjaman_id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Perpanjang">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </a>
                                        <a href="../pengembalian/process.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Kembalikan">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data peminjaman aktif
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($result->num_rows > 0): ?>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Informasi:</strong> Menampilkan <?= $result->num_rows ?> peminjaman aktif. 
                    Durasi peminjaman: <strong>7 hari</strong>. 
                    Denda keterlambatan: <strong>Rp 1.000/hari</strong>.
                    Maksimal peminjaman: <strong>3 buku per orang</strong>.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>