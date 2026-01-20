<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();
updateStatusPeminjaman($conn);

// ==========================================
// 1. LOGIKA SORTING & PENCARIAN & PAGINATION
// ==========================================

// A. Pagination Setup
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// B. Sorting Setup
// Default sort: Tanggal Jatuh Tempo (ASC) agar yang mendesak muncul duluan
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'tanggal_jatuh_tempo';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// Whitelist kolom agar aman
$allowed_sort = ['kode_peminjaman', 'nama_peminjam', 'judul', 'tanggal_pinjam', 'tanggal_jatuh_tempo'];
if (!in_array($sort_column, $allowed_sort)) {
    $sort_column = 'tanggal_jatuh_tempo';
}

// C. Search Setup
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where_clause = "WHERE p.status IN ('dipinjam', 'terlambat')";

if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where_clause .= " AND (
        p.kode_peminjaman LIKE '%$s%' OR 
        b.judul LIKE '%$s%' OR 
        b.nomor_buku LIKE '%$s%' OR
        m.nama LIKE '%$s%' OR 
        m.nim LIKE '%$s%' OR
        d.nama LIKE '%$s%' OR 
        d.nuptk LIKE '%$s%'
    )";
}

// ==========================================
// 2. EKSEKUSI QUERY UTAMA
// ==========================================

// Kita perlu JOIN ke tabel mahasiswa & dosen untuk fitur SORT BY NAMA dan SEARCH BY NAMA
$base_query = "FROM peminjaman p
               JOIN buku b ON p.buku_id = b.id
               LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
               LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
               $where_clause";

// Hitung Total Data (Untuk Pagination)
$count_query = "SELECT COUNT(*) as total $base_query";
$total_result = $conn->query($count_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil Data Utama
// COALESCE digunakan untuk menggabungkan kolom nama dari mhs/dosen menjadi satu kolom alias 'nama_peminjam' untuk disortir
$query = "SELECT p.*, b.judul, b.nomor_buku,
          COALESCE(m.nama, d.nama) as nama_peminjam,
          COALESCE(m.nim, d.nuptk) as identifier,
          (SELECT COUNT(*) FROM perpanjangan WHERE peminjaman_id = p.id) as jumlah_perpanjangan
          $base_query
          ORDER BY $sort_column $sort_order
          LIMIT $offset, $limit";

$result = $conn->query($query);

// ==========================================
// 3. EKSEKUSI QUERY RIWAYAT (LIMIT 10)
// ==========================================
$query_riwayat = "SELECT 
                    pr.id as perpanjangan_id,
                    pr.tanggal_perpanjangan,
                    pr.jatuh_tempo_lama,
                    pr.jatuh_tempo_baru,
                    p.id as peminjaman_id,
                    p.kode_peminjaman,
                    p.jenis_peminjam,
                    p.peminjam_id,
                    p.status,
                    b.judul,
                    b.nomor_buku
                  FROM perpanjangan pr
                  JOIN peminjaman p ON pr.peminjaman_id = p.id
                  JOIN buku b ON p.buku_id = b.id
                  ORDER BY pr.tanggal_perpanjangan DESC
                  LIMIT 10"; // Sesuai permintaan: hanya 10 terakhir
$result_riwayat = $conn->query($query_riwayat);

// Fungsi Helper URL untuk Sorting & Pagination
function getUrl($params = []) {
    $current = $_GET;
    $merged = array_merge($current, $params);
    
    // Reset page ke 1 jika melakukan search atau sort baru
    if (isset($params['search']) || isset($params['sort'])) {
        $merged['page'] = 1;
    }

    // Logic toggle order (ASC/DESC) jika kolom yang diklik sama
    if (isset($params['sort']) && isset($current['sort']) && $params['sort'] == $current['sort']) {
        $merged['order'] = (isset($current['order']) && $current['order'] == 'ASC') ? 'DESC' : 'ASC';
    }
    
    return '?' . http_build_query($merged);
}

function getSortIcon($col) {
    global $sort_column, $sort_order;
    if ($sort_column != $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:0.7em"></i>';
    return $sort_order == 'ASC' ? '<i class="bi bi-sort-up-alt ms-1"></i>' : '<i class="bi bi-sort-down ms-1"></i>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpanjangan Buku - E-Library STK Yakobus</title>
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
        
        /* Sidebar - FULL VERSION (Tidak dipotong) */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            width: 250px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.08);
            overflow-y: auto;
            z-index: 1000;
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
            border-left: 4px solid #38ef7d;
        }
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        /* Card & Table Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .table { font-size: 0.9rem; }
        
        /* Table Header Sortable Style */
        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            vertical-align: middle;
            border: none;
            padding: 1rem 0.75rem;
        }
        .table thead th a {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        .table thead th a:hover { color: #667eea; }

        .table tbody tr { transition: all 0.2s ease; }
        .table tbody tr:hover { background: rgba(56, 239, 125, 0.05); transform: scale(1.005); }
        .table tbody td { vertical-align: middle; padding: 0.9rem 0.75rem; }
        
        /* Components */
        .btn { border-radius: 8px; font-weight: 500; transition: all 0.3s ease; }
        .btn-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none; }
        .btn-sm { padding: 0.35rem 0.7rem; font-size: 0.85rem; }
        .badge { padding: 0.4rem 0.7rem; font-weight: 500; border-radius: 6px; }
        .alert { border: none; border-radius: 10px; border-left: 4px solid; }
        .info-box { background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%); border-left: 4px solid #0284c7; border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.5rem; }
        .expired-soon { background: rgba(251, 191, 36, 0.1); border-left: 3px solid #f59e0b; }
        
        /* Search Box Style */
        .search-box .form-control { border-radius: 8px 0 0 8px; border: 1px solid #e2e8f0; }
        .search-box .btn { border-radius: 0 8px 8px 0; }
    </style>
</head>
<body>
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
                <a class="nav-link active" href="<?= BASE_URL ?>modules/perpanjangan/index.php">
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

    <main class="main-content">
        <div class="page-header">
            <h1><i class="bi bi-arrow-clockwise me-2"></i>Perpanjangan Buku</h1>
        </div>

        <?php showAlert(); ?>
        
        <div class="info-box">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Ketentuan Perpanjangan:</strong>
            <ul class="mb-0 mt-2">
                <li>Perpanjangan hanya bisa dilakukan <strong>1 kali</strong> untuk <strong>7 hari tambahan</strong></li>
                <li>Perpanjangan dapat dilakukan mulai <strong>H-1 sebelum jatuh tempo</strong></li>
                <li>Jika perpanjang <strong>setelah jatuh tempo</strong>, akan dikenakan <strong>denda Rp 1.000/hari</strong></li>
                <li>Jatuh tempo baru dihitung dari <strong>tanggal jatuh tempo lama + 7 hari</strong></li>
            </ul>
        </div>

        <div class="card">
             <div class="card-header bg-white py-3">
                 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-primary me-3"><i class="bi bi-list-task me-2"></i>Daftar Peminjaman Aktif</h5>
                        <span class="badge bg-light text-muted border">
                            Halaman <?= $page ?> dari <?= $total_pages > 0 ? $total_pages : 1 ?>
                        </span>
                    </div>
                    
                    <form method="GET" action="" class="d-flex" style="max-width: 400px;">
                        <input type="hidden" name="sort" value="<?= $sort_column ?>">
                        <input type="hidden" name="order" value="<?= isset($_GET['order']) ? $_GET['order'] : '' ?>">
                        <div class="input-group search-box">
                            <input type="text" class="form-control form-control-sm" name="search" 
                                   placeholder="Cari Nama/NIM/Judul/Kode..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
                            <?php if(!empty($search)): ?>
                                <a href="index.php" class="btn btn-secondary btn-sm" title="Reset"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="12%">
                                    <a href="<?= getUrl(['sort' => 'kode_peminjaman']) ?>">
                                        KODE <?= getSortIcon('kode_peminjaman') ?>
                                    </a>
                                </th>
                                <th width="18%">
                                    <a href="<?= getUrl(['sort' => 'nama_peminjam']) ?>">
                                        PEMINJAM <?= getSortIcon('nama_peminjam') ?>
                                    </a>
                                </th>
                                <th width="20%">
                                    <a href="<?= getUrl(['sort' => 'judul']) ?>">
                                        BUKU <?= getSortIcon('judul') ?>
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'tanggal_pinjam']) ?>">
                                        TGL PINJAM <?= getSortIcon('tanggal_pinjam') ?>
                                    </a>
                                </th>
                                <th width="12%">
                                    <a href="<?= getUrl(['sort' => 'tanggal_jatuh_tempo']) ?>">
                                        JATUH TEMPO <?= getSortIcon('tanggal_jatuh_tempo') ?>
                                    </a>
                                </th>
                                <th width="10%">STATUS</th>
                                <th width="10%">PERPANJANGAN</th>
                                <th width="10%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    // Menggunakan data JOIN yang sudah diambil di query (lebih efisien)
                                    // Tapi jika ingin tetap konsisten dengan variabel $nama_peminjam di script lama,
                                    // kita bisa gunakan kolom yang di-alias tadi.
                                    $nama_peminjam = $row['nama_peminjam']; 
                                    $identifier = $row['identifier'];
                                    
                                    // Cek apakah sudah pernah diperpanjang
                                    $sudah_perpanjang = $row['jumlah_perpanjangan'] > 0;
                                    
                                    // Cek apakah sudah H-1
                                    $today = new DateTime();
                                    $jatuh_tempo = new DateTime($row['tanggal_jatuh_tempo']);
                                    $diff = $today->diff($jatuh_tempo);
                                    $hari_tersisa = $jatuh_tempo >= $today ? $diff->days : -$diff->days;
                                    
                                    // Cek apakah bisa perpanjang (H-1 atau sudah lewat)
                                    $bisa_perpanjang = ($hari_tersisa <= 1) && !$sudah_perpanjang;
                                    
                                    // Highlight jika hampir jatuh tempo
                                    $row_class = ($hari_tersisa <= 1 && $hari_tersisa >= 0) ? 'expired-soon' : '';
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= $no++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-person-badge"></i> <?= $identifier ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_pinjam']) ?></td>
                                <td>
                                    <?= formatTanggalIndo($row['tanggal_jatuh_tempo']) ?>
                                    <?php if ($hari_tersisa >= 0): ?>
                                        <br><small class="badge bg-warning text-dark">H-<?= $hari_tersisa ?></small>
                                    <?php else: ?>
                                        <br><small class="badge bg-danger">Lewat <?= abs($hari_tersisa) ?> hari</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'dipinjam'): ?>
                                        <span class="badge bg-info">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Terlambat</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sudah_perpanjang): ?>
                                        <span class="badge bg-secondary">Sudah Perpanjang</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($bisa_perpanjang): ?>
                                        <a href="add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success w-100" title="Perpanjang">
                                            <i class="bi bi-arrow-clockwise"></i> Perpanjang
                                        </a>
                                    <?php elseif ($sudah_perpanjang): ?>
                                        <small class="text-muted d-block text-center fst-italic">Maks. 1x</small>
                                    <?php else: ?>
                                        <small class="text-muted d-block text-center">Tunggu H-1</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada peminjaman yang sesuai pencarian
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= getUrl(['page' => $page-1]) ?>">Previous</a>
                        </li>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for($i = $start; $i <= $end; $i++): 
                        ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= getUrl(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= getUrl(['page' => $page+1]) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Perpanjangan (10 Terakhir)</h5>
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
                                <th width="10%">TGL PERPANJANGAN</th>
                                <th width="10%">TEMPO LAMA</th>
                                <th width="10%">TEMPO BARU</th>
                                <th width="8%">STATUS</th>
                                <th width="9%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no_riwayat = 1;
                            if ($result_riwayat->num_rows > 0):
                                while ($row = $result_riwayat->fetch_assoc()):
                                    // Untuk riwayat, kita gunakan helper lama jika perlu, 
                                    // atau JOIN query seperti di atas. Di sini saya pakai helper agar struktur mirip
                                    // dengan kode original Anda, tapi jika mau optimize bisa pakai JOIN di query_riwayat
                                    $nama_peminjam_rw = getNamaPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    $identifier_rw = getIdentifierPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                            ?>
                            <tr>
                                <td><?= $no_riwayat++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $nama_peminjam_rw ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-person-badge"></i> <?= $identifier_rw ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_perpanjangan']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= formatTanggalIndo($row['jatuh_tempo_lama']) ?></span></td>
                                <td><span class="badge bg-success"><?= formatTanggalIndo($row['jatuh_tempo_baru']) ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'diperpanjang'): ?>
                                        <span class="badge bg-info">Diperpanjang</span>
                                    <?php elseif ($row['status'] == 'dikembalikan'): ?>
                                        <span class="badge bg-secondary">Dikembalikan</span>
                                    <?php elseif ($row['status'] == 'terlambat'): ?>
                                        <span class="badge bg-danger">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'diperpanjang'): ?>
                                        <a href="delete.php?id=<?= $row['perpanjangan_id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin membatalkan perpanjangan ini?\n\nStatus peminjaman akan dikembalikan ke \'Dipinjam\' dan tanggal jatuh tempo akan kembali ke kondisi sebelum diperpanjang.')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">Selesai</small>
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
                                    Belum ada riwayat perpanjangan
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Informasi Hapus Perpanjangan:</strong> Tombol hapus hanya tersedia untuk perpanjangan dengan status "Diperpanjang". 
            Menghapus perpanjangan akan mengembalikan status peminjaman ke "Dipinjam" dan tanggal jatuh tempo ke kondisi sebelum diperpanjang.
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>