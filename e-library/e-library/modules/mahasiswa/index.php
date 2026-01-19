<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Pagination settings
$records_per_page = 30;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $records_per_page;

// Get search query
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build WHERE clause
$where = '';
$count_where = '';
if (!empty($search)) {
    $where = "WHERE nim LIKE '%$search%' OR nama LIKE '%$search%' OR program_studi LIKE '%$search%'";
    $count_where = $where;
}

// Get total records
$count_query = "SELECT COUNT(*) as total FROM mahasiswa $count_where";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get data with pagination
$query = "SELECT * FROM mahasiswa $where ORDER BY id DESC LIMIT $records_per_page OFFSET $offset";
$result = $conn->query($query);

// Calculate showing range
$showing_from = $total_records > 0 ? $offset + 1 : 0;
$showing_to = min($offset + $records_per_page, $total_records);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - E-Library STK Yakobus</title>
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
            border-left: 4px solid #11998e;
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
            background: rgba(17, 153, 142, 0.05);
            transform: scale(1.01);
        }
        .table tbody td {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        
        /* Photo */
        .photo-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
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
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
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
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.15);
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
        
        /* Pagination */
        .pagination-info {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            color: #11998e;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin: 0 3px;
            padding: 0.5rem 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-color: #11998e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(17, 153, 142, 0.3);
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-color: #11998e;
            color: white;
            font-weight: 600;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #cbd5e0;
            background-color: #f8f9fa;
            border-color: #e2e8f0;
        }
        
        .pagination .page-link i {
            font-size: 0.85rem;
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
                <a class="nav-link active" href="<?= BASE_URL ?>modules/mahasiswa/index.php">
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
            <h1><i class="bi bi-people me-2"></i>Data Mahasiswa</h1>
            <div>
                <a href="import.php" class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Excel
                </a>
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Mahasiswa
                </a>
            </div>
        </div>

        <?php showAlert(); ?>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <form method="GET" action="">
                            <div class="input-group search-box">
                                <input type="text" class="form-control" name="search" placeholder="Cari NIM, nama, atau program studi..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-success" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                <?php if (!empty($search)): ?>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="pagination-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Menampilkan <strong><?= number_format($showing_from) ?></strong> - <strong><?= number_format($showing_to) ?></strong> dari <strong><?= number_format($total_records) ?></strong> mahasiswa
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="8%">Foto</th>
                                <th width="12%">NIM</th>
                                <th width="20%">Nama</th>
                                <th width="20%">Program Studi</th>
                                <th width="10%">Angkatan</th>
                                <th width="15%">No. HP</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (!empty($row['foto']) && file_exists(UPLOAD_MAHASISWA . $row['foto'])): ?>
                                        <img src="<?= BASE_URL ?>uploads/mahasiswa/<?= $row['foto'] ?>" class="photo-thumb" alt="Foto">
                                    <?php else: ?>
                                        <div class="photo-thumb bg-secondary d-flex align-items-center justify-content-center text-white">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= htmlspecialchars($row['nim']) ?></code></td>
                                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($row['program_studi']) ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($row['angkatan']) ?></span></td>
                                <td><?= htmlspecialchars($row['no_hp']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus mahasiswa ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <?= !empty($search) ? 'Tidak ada hasil pencarian' : 'Tidak ada data mahasiswa' ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="pagination-info">
                        Halaman <strong><?= $current_page ?></strong> dari <strong><?= $total_pages ?></strong>
                    </div>
                    
                    <nav>
                        <ul class="pagination mb-0">
                            <!-- Previous Button -->
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            </li>

                            <?php
                            // Pagination logic
                            $range = 2; // Show 2 pages before and after current page
                            $start_page = max(1, $current_page - $range);
                            $end_page = min($total_pages, $current_page + $range);

                            // First page
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                }
                            }

                            // Page numbers
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php
                            // Last page
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                            }
                            ?>

                            <!-- Next Button -->
                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>