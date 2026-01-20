<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// --- 1. KONFIGURASI PAGINATION & SORTING ---
$limit = 20; // Sesuai permintaan: 20 list per tampilan
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Sorting Logic
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'tanggal_jatuh_tempo';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

// Whitelist kolom agar aman
$allowed_sort = ['kode_peminjaman', 'nama_peminjam', 'judul', 'tanggal_pinjam', 'tanggal_jatuh_tempo', 'status', 'denda'];
if (!in_array($sort_column, $allowed_sort)) $sort_column = 'tanggal_jatuh_tempo';

// Mapping sort 'denda' ke 'tanggal_jatuh_tempo' (Semakin lama jatuh tempo, semakin besar denda)
// Jika sort denda DESC (terbesar), berarti jatuh tempo ASC (terlama), dan sebaliknya.
if ($sort_column == 'denda') {
    $real_sort_column = 'tanggal_jatuh_tempo';
    $real_sort_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC'; // Invert logic
} else {
    $real_sort_column = $sort_column;
    $real_sort_order = $sort_order;
}

// --- 2. FILTER & SEARCH LOGIC ---
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where = "WHERE p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";

if ($status_filter != 'all') {
    $where .= " AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

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

// --- 3. QUERY UTAMA (JOIN untuk Search/Sort Nama) ---
$base_query = "FROM peminjaman p
               JOIN buku b ON p.buku_id = b.id
               LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
               LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
               $where";

// Hitung Total Data (Pagination)
$count_query = "SELECT COUNT(*) as total $base_query";
$total_result = $conn->query($count_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Ambil Data
$query = "SELECT p.*, b.judul, b.nomor_buku,
          COALESCE(m.nama, d.nama) as nama_peminjam,
          COALESCE(m.nim, d.nuptk) as identifier
          $base_query
          ORDER BY $real_sort_column $real_sort_order
          LIMIT $offset, $limit";

$result = $conn->query($query);

// --- 4. QUERY RIWAYAT (LIMIT 10) ---
$query_riwayat = "SELECT pg.*, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id,
                  b.judul, b.nomor_buku,
                  COALESCE(m.nama, d.nama) as nama_peminjam,
                  COALESCE(m.nim, d.nuptk) as identifier
                  FROM pengembalian pg
                  JOIN peminjaman p ON pg.peminjaman_id = p.id
                  JOIN buku b ON p.buku_id = b.id
                  LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
                  LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
                  ORDER BY pg.created_at DESC
                  LIMIT 10"; // Sesuai permintaan: 10 riwayat terakhir
$result_riwayat = $conn->query($query_riwayat);

// Helper URL Generator
function getUrl($params = []) {
    $current = $_GET;
    $merged = array_merge($current, $params);
    if (isset($params['sort']) || isset($params['search']) || isset($params['status'])) {
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
    <title>Pengembalian Buku - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Navbar */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.8rem 1.5rem; }
        .navbar-brand { color: #fff !important; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar */
        .sidebar { position: fixed; top: 56px; bottom: 0; left: 0; width: 250px; background: white; border-right: 1px solid #e2e8f0; overflow-y: auto; }
        .sidebar-heading { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; padding: 1rem 1.2rem 0.5rem; color: #64748b; margin-top: 0.5rem; }
        .sidebar .nav-link { color: #475569; padding: 0.8rem 1.5rem; font-weight: 500; }
        .sidebar .nav-link:hover { color: #667eea; background: rgba(102, 126, 234, 0.08); }
        .sidebar .nav-link.active { color: #667eea; background: rgba(102, 126, 234, 0.1); border-right: 3px solid #667eea; font-weight: 600; }
        .sidebar .nav-link i { margin-right: 0.7rem; font-size: 1.1rem; }
        
        .main-content { margin-left: 250px; margin-top: 56px; padding: 1.5rem; }
        .page-header { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #f093fb; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
        
        /* Table Sortable Headers */
        .table thead th { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: none; padding: 1rem 0.75rem; vertical-align: middle; }
        .table thead th a { text-decoration: none; color: #475569; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; display: flex; align-items: center; }
        .table thead th a:hover { color: #667eea; }
        
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .btn { border-radius: 6px; font-weight: 500; }
        .info-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        
        /* Pagination */
        .pagination .page-link { color: #667eea; }
        .pagination .page-item.active .page-link { background-color: #667eea; border-color: #667eea; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-fill me-2"></i>E-Library STK Yakobus
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle me-1"></i><?= $_SESSION['admin_username'] ?></span>
            </div>
        </div>
    </nav>

    <nav class="sidebar pt-3">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
            <li class="sidebar-heading">DATA MASTER</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php"><i class="bi bi-book"></i>Data Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php"><i class="bi bi-people"></i>Data Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php"><i class="bi bi-person-badge"></i>Data Dosen</a></li>
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/peminjaman/index.php"><i class="bi bi-arrow-left-right"></i>Peminjaman Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php"><i class="bi bi-arrow-clockwise"></i>Perpanjangan Buku</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>modules/pengembalian/index.php"><i class="bi bi-arrow-return-left"></i>Pengembalian Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php"><i class="bi bi-cash-stack"></i>Manajemen Denda</a></li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php"><i class="bi bi-file-earmark-text"></i>Surat Keterangan</a></li>
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php"><i class="bi bi-file-bar-graph"></i>Laporan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php"><i class="bi bi-cloud-download"></i>Backup Database</a></li>
        </ul>
    </nav>

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
            </ul>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <input type="hidden" name="sort" value="<?= $sort_column ?>">
                    <input type="hidden" name="order" value="<?= isset($_GET['order']) ? $_GET['order'] : '' ?>">
                    
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
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Kode, Judul, Nama Peminjam, atau NIM..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                            <?php if(!empty($search)): ?>
                                <a href="index.php" class="btn btn-secondary"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1rem 1.5rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Peminjaman Aktif</h5>
                    <span class="badge bg-light text-dark">Halaman <?= $page ?> dari <?= $total_pages > 0 ? $total_pages : 1 ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'kode_peminjaman']) ?>">KODE <?= getSortIcon('kode_peminjaman') ?></a>
                                </th>
                                <th width="18%">
                                    <a href="<?= getUrl(['sort' => 'nama_peminjam']) ?>">PEMINJAM <?= getSortIcon('nama_peminjam') ?></a>
                                </th>
                                <th width="20%">
                                    <a href="<?= getUrl(['sort' => 'judul']) ?>">BUKU <?= getSortIcon('judul') ?></a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'tanggal_pinjam']) ?>">TGL PINJAM <?= getSortIcon('tanggal_pinjam') ?></a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'tanggal_jatuh_tempo']) ?>">JATUH TEMPO <?= getSortIcon('tanggal_jatuh_tempo') ?></a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'status']) ?>">STATUS <?= getSortIcon('status') ?></a>
                                </th>
                                <th width="10%">
                                    <a href="<?= getUrl(['sort' => 'denda']) ?>">DENDA <?= getSortIcon('denda') ?></a>
                                </th>
                                <th width="7%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $offset + 1;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    // Hitung denda real-time
                                    $today = new DateTime();
                                    $jatuh_tempo = new DateTime($row['tanggal_jatuh_tempo']);
                                    $selisih = $jatuh_tempo->diff($today);
                                    
                                    $keterlambatan_hari = 0;
                                    $denda = 0;
                                    $status_class = '';
                                    
                                    // Logic hitung hari & denda
                                    if ($today > $jatuh_tempo) {
                                        $keterlambatan_hari = $selisih->days;
                                        $denda = $keterlambatan_hari * 1000;
                                        $status_text = 'Terlambat ' . $keterlambatan_hari . ' hari';
                                        $badge_color = 'danger';
                                    } else {
                                        $status_text = ucfirst($row['status']);
                                        $badge_color = ($row['status'] == 'diperpanjang') ? 'info' : 'success';
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
                                    <small class="text-muted">No: <?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= date('d/m/y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td><?= date('d/m/y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                                <td><span class="badge bg-<?= $badge_color ?>"><?= $status_text ?></span></td>
                                <td>
                                    <?php if ($denda > 0): ?>
                                        <span class="text-danger fw-bold"><?= formatRupiah($denda) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Proses Pengembalian">
                                        <i class="bi bi-check-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada peminjaman yang perlu dikembalikan
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

        <div class="card mb-5">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Pengembalian (10 Terakhir)</h5>
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
                                <th width="10%">KETERLAMBATAN</th>
                                <th width="10%">DENDA</th>
                                <th width="10%">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no_riwayat = 1;
                            if ($result_riwayat->num_rows > 0):
                                while ($row = $result_riwayat->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no_riwayat++ ?></td>
                                <td><code><?= $row['kode_peminjaman'] ?></code></td>
                                <td>
                                    <strong><?= $row['nama_peminjam'] ?></strong><br>
                                    <small class="text-muted"><?= $row['identifier'] ?></small>
                                </td>
                                <td>
                                    <strong><?= $row['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                </td>
                                <td><?= date('d/m/y', strtotime($row['tanggal_kembali'])) ?></td>
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
                                    <?php if ($row['sisa_denda'] > 0): ?>
                                        <span class="badge bg-warning text-dark">Ada Sisa Denda</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
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