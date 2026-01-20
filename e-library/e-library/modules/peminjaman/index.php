<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();
updateStatusPeminjaman($conn);

// --- 1. LOGIK TAMPILAN (VIEW: ACTIVE vs HISTORY) ---
$view = isset($_GET['view']) ? $_GET['view'] : 'active';
$valid_views = ['active', 'history'];
if (!in_array($view, $valid_views)) $view = 'active';

// --- 2. CONFIG PAGINATION & SORTING ---
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Default sort
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'tanggal_pinjam';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'asc' ? 'ASC' : 'DESC';

// Whitelist kolom sorting
$allowed_sort = ['kode_peminjaman', 'nama_peminjam', 'judul', 'tanggal_pinjam', 'tanggal_jatuh_tempo', 'status'];
if (!in_array($sort_column, $allowed_sort)) $sort_column = 'tanggal_pinjam';

// --- 3. FILTERING ---
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where = "WHERE 1=1";

// Filter berdasarkan View (Tab)
if ($view == 'active') {
    // Tampilkan: Dipinjam, Diperpanjang, Terlambat
    $where .= " AND p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";
} else {
    // Tampilkan: Dikembalikan (Riwayat)
    $where .= " AND p.status = 'dikembalikan'";
}

// Filter Dropdown Status (Spesifik)
if (!empty($status_filter)) {
    $where .= " AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Filter Pencarian (Global)
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where .= " AND (
        p.kode_peminjaman LIKE '%$s%' OR 
        b.judul LIKE '%$s%' OR 
        b.nomor_buku LIKE '%$s%' OR
        m.nama LIKE '%$s%' OR 
        m.nim LIKE '%$s%' OR
        d.nama LIKE '%$s%' OR 
        d.nuptk LIKE '%$s%'
    )";
}

// --- 4. QUERY UTAMA ---
$base_query = "FROM peminjaman p
               JOIN buku b ON p.buku_id = b.id
               LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
               LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
               $where";

// Hitung Total Data (Untuk Pagination)
$count_query = "SELECT COUNT(*) as total $base_query";
$total_data = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Ambil Data
$query = "SELECT p.*, b.judul, b.nomor_buku,
          COALESCE(m.nama, d.nama) as nama_peminjam,
          COALESCE(m.nim, d.nuptk) as identifier
          $base_query
          ORDER BY $sort_column $sort_order
          LIMIT $offset, $limit";

$result = $conn->query($query);

// Fungsi Helper URL
function getUrl($params = []) {
    $current = $_GET;
    $merged = array_merge($current, $params);
    if (isset($params['sort']) || isset($params['status']) || isset($params['view'])) {
        $merged['page'] = 1; 
    }
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
    <title>Peminjaman Buku - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Navbar */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 0.8rem 1.5rem; }
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.3rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar */
        .sidebar { position: fixed; top: 56px; bottom: 0; left: 0; width: 250px; background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%); box-shadow: 2px 0 15px rgba(0,0,0,0.08); overflow-y: auto; z-index: 1000;}
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
        .sidebar-heading { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; padding: 1rem 1.2rem 0.5rem; color: #64748b; background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%); margin-top: 0.5rem; position: relative; }
        .sidebar-heading::after { content: ''; position: absolute; bottom: 0; left: 1.2rem; right: 1.2rem; height: 2px; background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%); }
        .sidebar .nav-link { font-weight: 500; color: #475569; padding: 0.75rem 1.2rem; border-left: 3px solid transparent; transition: all 0.3s ease; margin: 0.2rem 0; }
        .sidebar .nav-link i { width: 22px; font-size: 1.1rem; margin-right: 0.7rem; }
        .sidebar .nav-link:hover { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%); border-left-color: #667eea; transform: translateX(3px); }
        .sidebar .nav-link.active { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%); border-left-color: #667eea; font-weight: 600; }
        
        /* Content & Components */
        .main-content { margin-left: 250px; margin-top: 56px; padding: 1.5rem; }
        .page-header { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #f093fb; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        
        /* Table Headers Sortable */
        .table thead th { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 1rem 0.75rem; vertical-align: middle; border: none; }
        .table thead th a { text-decoration: none; color: #475569; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; }
        .table thead th a:hover { color: #667eea; }
        .table tbody td { vertical-align: middle; padding: 0.9rem 0.75rem; font-size: 0.9rem; }

        /* Nav Pills for View Mode */
        .nav-pills .nav-link { color: #64748b; font-weight: 500; border-radius: 8px; padding: 0.5rem 1.2rem; border: 1px solid transparent; }
        .nav-pills .nav-link.active { background-color: #667eea; color: white; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25); }
        .nav-pills .nav-link:hover:not(.active) { background-color: #e2e8f0; }

        /* Helpers */
        .btn { border-radius: 8px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .search-box .form-control { border-radius: 8px; border: 2px solid #e2e8f0; }
        .search-box .form-control:focus { border-color: #f093fb; box-shadow: 0 0 0 0.2rem rgba(240, 147, 251, 0.15); }
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

    <main class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-arrow-left-right me-2"></i>Peminjaman Buku</h1>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Peminjaman
            </a>
        </div>

        <?php showAlert(); ?>

        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $view == 'active' ? 'active' : '' ?>" href="<?= getUrl(['view' => 'active']) ?>">
                    <i class="bi bi-hourglass-split me-2"></i>Sedang Dipinjam 
                    <?php if($view == 'active'): ?><span class="badge bg-white text-primary ms-1 rounded-pill"><?= $total_data ?></span><?php endif; ?>
                </a>
            </li>
            <li class="nav-item ms-2">
                <a class="nav-link <?= $view == 'history' ? 'active' : '' ?>" href="<?= getUrl(['view' => 'history']) ?>">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Selesai
                    <?php if($view == 'history'): ?><span class="badge bg-white text-primary ms-1 rounded-pill"><?= $total_data ?></span><?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="card">
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <form method="GET" action="">
                            <input type="hidden" name="view" value="<?= $view ?>">
                            <input type="hidden" name="status" value="<?= $status_filter ?>">
                            <input type="hidden" name="sort" value="<?= $sort_column ?>">
                            <div class="input-group search-box">
                                <input type="text" class="form-control" name="search" placeholder="Cari Kode, Nama, NIM/NUPTK, atau Judul..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                <?php if(!empty($search)): ?>
                                <a href="?view=<?= $view ?>" class="btn btn-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-5">
                        <?php if($view == 'active'): ?>
                        <div class="d-flex justify-content-end">
                             <form method="GET" action="" id="filterForm">
                                <input type="hidden" name="view" value="<?= $view ?>">
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                <input type="hidden" name="sort" value="<?= $sort_column ?>">
                                <select class="form-select" name="status" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">-- Filter Status --</option>
                                    <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                    <option value="diperpanjang" <?= $status_filter == 'diperpanjang' ? 'selected' : '' ?>>Diperpanjang</option>
                                    <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                </select>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

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
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'tanggal_jatuh_tempo']) ?>">
                                        JATUH TEMPO <?= getSortIcon('tanggal_jatuh_tempo') ?>
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'status']) ?>">
                                        STATUS <?= getSortIcon('status') ?>
                                    </a>
                                </th>
                                <th width="8%">DENDA</th>
                                <th width="12%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    // Hitung denda real-time
                                    $denda = 0;
                                    if ($row['status'] == 'terlambat') {
                                        $hari_terlambat = hitungKeterlambatan($row['tanggal_jatuh_tempo']);
                                        $denda = hitungDenda($hari_terlambat);
                                    }
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $row['nama_peminjam'] ?></strong><br>
                                    <small class="text-muted"><i class="bi bi-person-badge"></i> <?= $row['identifier'] ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_pinjam']) ?></td>
                                <td>
                                    <?= formatTanggalIndo($row['tanggal_jatuh_tempo']) ?>
                                    <?php if($row['status'] == 'terlambat'): ?>
                                        <div class="small text-danger fw-bold mt-1">Lewat <?= $hari_terlambat ?> hari</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'dipinjam'): ?>
                                        <span class="badge bg-info">Dipinjam</span>
                                    <?php elseif ($row['status'] == 'diperpanjang'): ?>
                                        <span class="badge bg-warning">Diperpanjang</span>
                                    <?php elseif ($row['status'] == 'terlambat'): ?>
                                        <span class="badge bg-danger">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Dikembalikan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($denda > 0): ?>
                                        <span class="text-danger"><strong><?= formatRupiah($denda) ?></strong></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['status'] != 'dikembalikan'): ?>
                                        <?php if ($row['status'] == 'dipinjam'): ?>
                                        <a href="../perpanjangan/add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="Perpanjang">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="../pengembalian/add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Kembalikan">
                                            <i class="bi bi-arrow-return-left"></i>
                                        </a>
                                        <?php if ($row['status'] == 'dipinjam'): ?>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus peminjaman ini? Stok buku akan dikembalikan.')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Selesai</span>
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
                                    Tidak ada data peminjaman
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>