<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Fungsi hitung denda berjalan (untuk Infografis Atas)
function hitungDendaBerjalan($conn) {
    $today = date('Y-m-d');
    
    $query = "SELECT p.id, p.tanggal_jatuh_tempo, p.kode_peminjaman
              FROM peminjaman p
              WHERE p.tanggal_jatuh_tempo < '$today'
              AND p.tanggal_kembali IS NULL
              AND p.status NOT IN ('dikembalikan', 'selesai')";
    
    $result = $conn->query($query);
    
    $total_denda = 0;
    $total_buku = 0;
    $total_hari_terlambat = 0;
    $tarif_denda = 1000;
    
    $detail = array();
    
    while ($row = $result->fetch_assoc()) {
        $tanggal_jatuh_tempo = new DateTime($row['tanggal_jatuh_tempo']);
        $today_date = new DateTime($today);
        $hari_terlambat = $tanggal_jatuh_tempo->diff($today_date)->days;
        
        if ($hari_terlambat > 0) {
            $denda_item = $hari_terlambat * $tarif_denda;
            $total_denda += $denda_item;
            $total_buku++;
            $total_hari_terlambat += $hari_terlambat;
            
            $detail[] = array(
                'kode' => $row['kode_peminjaman'],
                'hari' => $hari_terlambat,
                'denda' => $denda_item
            );
        }
    }
    
    return [
        'total_denda' => $total_denda,
        'total_buku' => $total_buku,
        'total_hari' => $total_hari_terlambat,
        'detail' => $detail
    ];
}

// Filter Input
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$metode_filter = isset($_GET['metode']) ? $_GET['metode'] : 'all';

// Query Condition
$where_query = "WHERE pg.denda > 0 
                AND MONTH(pg.tanggal_kembali) = $bulan 
                AND YEAR(pg.tanggal_kembali) = $tahun";

if ($metode_filter != 'all') {
    $where_query .= " AND pg.metode_pembayaran = '" . $conn->real_escape_string($metode_filter) . "'";
}

// --- 1. SETUP PAGINATION & FILTER ---
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Ambil parameter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$metode_filter = isset($_GET['metode']) ? $_GET['metode'] : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// --- 2. BANGUN QUERY (GABUNGAN FILTER & SEARCH) ---
// Base condition
$where_clause = "WHERE pg.denda > 0"; 

// A. Filter Bulan & Tahun (Wajib)
$where_clause .= " AND MONTH(pg.tanggal_kembali) = '$bulan' AND YEAR(pg.tanggal_kembali) = '$tahun'";

// B. Filter Metode
if ($metode_filter != 'all') {
    $where_clause .= " AND pg.metode_pembayaran = '" . $conn->real_escape_string($metode_filter) . "'";
}

// C. Filter Search (Opsional - Jika diisi)
if (!empty($search)) {
    $where_clause .= " AND (p.kode_peminjaman LIKE '%$search%' 
                OR m.nama LIKE '%$search%' 
                OR m.nim LIKE '%$search%'
                OR d.nama LIKE '%$search%'
                OR d.nuptk LIKE '%$search%'
                OR b.judul LIKE '%$search%'
                OR pg.nomor_bukti LIKE '%$search%')";
}

// --- 3. EKSEKUSI QUERY UTAMA ---
$query = "SELECT pg.*, pg.status_buku, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, 
          b.judul, b.nomor_buku
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
          LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
          $where_clause
          ORDER BY 
            CASE WHEN pg.sisa_denda > 0 THEN 0 ELSE 1 END ASC,
            FIELD(pg.metode_pembayaran, 'cash', 'transfer', 'tagihan_studi', 'waive'),
            pg.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = $conn->query($query);
$data_laporan = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data_laporan[] = $row;
    }
}

// --- 4. HITUNG TOTAL DATA (Untuk Pagination) ---
$query_count = "SELECT COUNT(*) as total 
                FROM pengembalian pg 
                JOIN peminjaman p ON pg.peminjaman_id = p.id
                JOIN buku b ON p.buku_id = b.id 
                LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
                LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
                $where_clause";
$total_rows = $conn->query($query_count)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// --- 5. STATISTIK RINGKASAN (Sesuai Filter) ---
$stats_query = "SELECT 
                COUNT(*) as total_transaksi,
                SUM(denda) as total_denda,
                SUM(CASE WHEN metode_pembayaran = 'tagihan_studi' THEN denda ELSE denda_dibayar END) as total_dibayar,
                SUM(sisa_denda) as total_sisa,
                SUM(CASE WHEN metode_pembayaran = 'cash' THEN denda_dibayar ELSE 0 END) as cash,
                SUM(CASE WHEN metode_pembayaran = 'transfer' THEN denda_dibayar ELSE 0 END) as transfer,
                SUM(CASE WHEN metode_pembayaran = 'tagihan_studi' THEN denda ELSE 0 END) as tagihan_studi,
                SUM(CASE WHEN metode_pembayaran = 'waive' THEN denda ELSE 0 END) as waive
                FROM pengembalian pg
                JOIN peminjaman p ON pg.peminjaman_id = p.id
                JOIN buku b ON p.buku_id = b.id 
                LEFT JOIN mahasiswa m ON (p.peminjam_id = m.id AND p.jenis_peminjam = 'mahasiswa')
                LEFT JOIN dosen d ON (p.peminjam_id = d.id AND p.jenis_peminjam = 'dosen')
                $where_clause";
$stats_result = $conn->query($stats_query);

if ($stats_result) {
    $stats_data = $stats_result->fetch_assoc();
    $total_transaksi = $stats_data['total_transaksi'] ?? 0;
    $total_denda = $stats_data['total_denda'] ?? 0;
    $total_dibayar = $stats_data['total_dibayar'] ?? 0;
    $total_sisa = $stats_data['total_sisa'] ?? 0;
    $cash = $stats_data['cash'] ?? 0;
    $transfer = $stats_data['transfer'] ?? 0;
    $tagihan_studi = $stats_data['tagihan_studi'] ?? 0;
    $waive = $stats_data['waive'] ?? 0;
} else {
    $total_transaksi = $total_denda = $total_dibayar = $total_sisa = 0;
    $cash = $transfer = $tagihan_studi = $waive = 0;
}

// Hitung denda berjalan (Infografis)
$denda_berjalan = hitungDendaBerjalan($conn);
$tanggal_generate = date('Y-m-d H:i:s');

// Helper Bulan
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
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        
        /* Navbar */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 0.8rem 1.5rem; }
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.3rem; }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar */
        .sidebar { position: fixed; top: 56px; bottom: 0; left: 0; width: 250px; background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%); box-shadow: 2px 0 15px rgba(0,0,0,0.08); overflow-y: auto; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
        
        .sidebar-heading { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; padding: 1rem 1.2rem 0.5rem; color: #64748b; background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%); margin-top: 0.5rem; position: relative; }
        .sidebar-heading::after { content: ''; position: absolute; bottom: 0; left: 1.2rem; right: 1.2rem; height: 2px; background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%); }
        
        .sidebar .nav-link { font-weight: 500; color: #475569; padding: 0.75rem 1.2rem; border-left: 3px solid transparent; transition: all 0.3s ease; margin: 0.2rem 0; }
        .sidebar .nav-link i { width: 22px; font-size: 1.1rem; margin-right: 0.7rem; }
        .sidebar .nav-link:hover { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%); border-left-color: #667eea; transform: translateX(3px); }
        .sidebar .nav-link.active { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%); border-left-color: #667eea; font-weight: 600; }
        
        .main-content { margin-left: 250px; margin-top: 56px; padding: 1.5rem; }
        
        /* Stats & Infografis */
        .stat-summary { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
        .stat-item { text-align: center; padding: 1rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; }
        .stat-item h4 { margin: 0; font-size: 1.3rem; font-weight: 700; color: #1e293b; }
        
        .denda-berjalan-box { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-left: 4px solid #ff9800; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }

        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none; }
            .navbar { display: none; }
            .main-content { margin-left: 0; margin-top: 0; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-book-half"></i> E-Library STK Yakobus</a>
            <div class="ms-auto">
                <button onclick="window.print()" class="btn btn-light btn-sm"><i class="bi bi-printer-fill me-1"></i> Cetak Laporan</button>
                <a href="index.php" class="btn btn-outline-light btn-sm ms-2"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
            </div>
        </div>
    </nav>

    <nav class="sidebar no-print">
        <ul class="nav flex-column">
            <li class="sidebar-heading">MENU UTAMA</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
            
            <li class="sidebar-heading">MASTER DATA</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php"><i class="bi bi-book"></i>Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php"><i class="bi bi-mortarboard"></i>Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php"><i class="bi bi-person-workspace"></i>Dosen</a></li>
            
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/peminjaman/index.php"><i class="bi bi-arrow-left-right"></i>Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php"><i class="bi bi-arrow-clockwise"></i>Perpanjangan Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php"><i class="bi bi-box-arrow-in-down"></i>Pengembalian</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>modules/denda/index.php"><i class="bi bi-cash-stack"></i>Manajemen Denda</a></li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php"><i class="bi bi-file-earmark-text"></i>Surat Keterangan</a></li>            
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php"><i class="bi bi-file-bar-graph"></i>Laporan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php"><i class="bi bi-cloud-download"></i>Backup Database</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header no-print bg-white p-3 rounded mb-3 shadow-sm border-start border-4 border-info">
            <h1 class="h3 fw-bold m-0 text-dark"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan & Manajemen Denda</h1>
        </div>
        
<div class="card no-print mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Bulan</label>
                <select name="bulan" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>>
                            <?= $nama_bulan[str_pad($m, 2, '0', STR_PAD_LEFT)] ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tahun</label>
                <select name="tahun" class="form-select">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Metode</label>
                <select name="metode" class="form-select">
                    <option value="all" <?= $metode_filter == 'all' ? 'selected' : '' ?>>Semua Metode</option>
                    <option value="cash" <?= $metode_filter == 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="transfer" <?= $metode_filter == 'transfer' ? 'selected' : '' ?>>Transfer</option>
                    <option value="tagihan_studi" <?= $metode_filter == 'tagihan_studi' ? 'selected' : '' ?>>Tagihan Studi</option>
                    <option value="waive" <?= $metode_filter == 'waive' ? 'selected' : '' ?>>Waive</option>
                </select>
            </div>

            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari Nama Peminjam, NIM, NUPTK, atau Judul Buku..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

        <div class="text-center mb-4 d-none d-print-block">
            <h3>LAPORAN DENDA PERPUSTAKAAN</h3>
            <p class="mb-0">Periode: <strong><?= $periode_text ?></strong></p>
            <?php if ($metode_filter != 'all'): ?><p class="mb-0 small">Metode: <?= ucfirst($metode_filter) ?></p><?php endif; ?>
        </div>

        <?php if ($denda_berjalan['total_denda'] > 0): ?>
        <div class="denda-berjalan-box no-print">
            <h5 class="text-warning mb-3"><i class="bi bi-hourglass-split me-2"></i>DENDA BERJALAN (BELUM DIKEMBALIKAN)</h5>
            <div class="row">
                <div class="col-md-4 text-center">
                    <h6 class="text-muted small">Potensi Denda</h6>
                    <h3 class="text-warning fw-bold"><?= formatRupiah($denda_berjalan['total_denda']) ?></h3>
                </div>
                <div class="col-md-4 text-center">
                    <h6 class="text-muted small">Buku Terlambat</h6>
                    <h3 class="text-warning fw-bold"><?= $denda_berjalan['total_buku'] ?></h3>
                </div>
                <div class="col-md-4 text-center">
                    <h6 class="text-muted small">Akumulasi Hari</h6>
                    <h3 class="text-warning fw-bold"><?= $denda_berjalan['total_hari'] ?></h3>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="stat-summary">
            <h5 class="mb-3"><i class="bi bi-bar-chart-fill me-2"></i>Ringkasan Transaksi (Periode Ini)</h5>
            <div class="stat-grid">
                <div class="stat-item"><label>Total Transaksi</label><h4><?= number_format($total_transaksi) ?></h4></div>
                <div class="stat-item"><label>Total Denda</label><h4><?= formatRupiah($total_denda) ?></h4></div>
                <div class="stat-item"><label>Terbayar/Lunas</label><h4 class="text-success"><?= formatRupiah($total_dibayar) ?></h4></div>
                <div class="stat-item"><label>Sisa Belum Bayar</label><h4 class="text-danger"><?= formatRupiah($total_sisa) ?></h4></div>
            </div>
            <hr class="my-3 opacity-25">
            <div class="stat-grid">
                <div class="stat-item"><small>Cash</small><h5><?= formatRupiah($cash) ?></h5></div>
                <div class="stat-item"><small>Transfer</small><h5><?= formatRupiah($transfer) ?></h5></div>
                <div class="stat-item"><small>Tagihan Studi</small><h5><?= formatRupiah($tagihan_studi) ?></h5></div>
                <div class="stat-item"><small>Waive</small><h5><?= formatRupiah($waive) ?></h5></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Detail Transaksi</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0 align-middle text-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">NO</th>
                                <th>KODE & PEMINJAM</th>
                                <th>BUKU & STATUS</th>
                                <th>TGL KEMBALI</th>
                                <th class="text-end">DENDA</th>
                                <th class="text-end">DIBAYAR</th>
                                <th class="text-end">SISA</th>
                                <th class="text-center">METODE</th>
                                <th class="text-center" width="12%">AKSI</th> </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (count($data_laporan) > 0):
                                foreach ($data_laporan as $row):
                                    $nama_peminjam = getNamaPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    $identifier = getIdentifierPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    
                                    // Fix Tampilan Dibayar (Tagihan Studi)
                                    $tampil_dibayar = ($row['metode_pembayaran'] == 'tagihan_studi') ? $row['denda'] : $row['denda_dibayar'];
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $row['kode_peminjaman'] ?></span><br>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><?= $identifier ?></small>
                                </td>
                                <td>
                                    <?= $row['judul'] ?><br>
                                    <small class="text-muted"><?= $row['nomor_buku'] ?></small>
                                    <?php if (isset($row['status_buku']) && $row['status_buku'] != 'normal'): ?>
                                        <div class="mt-1">
                                            <?php if ($row['status_buku'] == 'hilang'): ?>
                                                <span class="badge bg-danger">HILANG</span>
                                            <?php elseif ($row['status_buku'] == 'rusak_parah'): ?>
                                                <span class="badge bg-danger">RUSAK PARAH</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatTanggalIndo($row['tanggal_kembali']) ?></td>
                                <td class="text-end fw-bold"><?= formatRupiah($row['denda']) ?></td>
                                <td class="text-end text-success"><?= formatRupiah($tampil_dibayar) ?></td>
                                <td class="text-end">
                                    <?php if ($row['sisa_denda'] > 0): ?>
                                        <span class="text-danger fw-bold"><?= formatRupiah($row['sisa_denda']) ?></span>
                                    <?php else: ?>
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $metode = $row['metode_pembayaran'];
                                    $cls = ($metode=='cash')?'success':(($metode=='transfer')?'info':(($metode=='tagihan_studi')?'primary':'secondary'));
                                    ?>
                                    <span class="badge bg-<?= $cls ?>"><?= strtoupper(str_replace('_',' ',$metode)) ?></span>
                                </td>
                                
                                <td class="text-center no-print">
                                    <div class="d-flex justify-content-center gap-1">
                                        <?php if ($row['sisa_denda'] > 0): ?>
                                            <a href="bayar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Bayar">
                                                <i class="bi bi-wallet2"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php 
                                        $bisa_cetak = ($row['sisa_denda'] <= 0 || 
                                                       $row['metode_pembayaran'] == 'tagihan_studi' || 
                                                       $row['metode_pembayaran'] == 'waive' || 
                                                       $row['denda_dibayar'] > 0);
                                        if ($bisa_cetak): 
                                        ?>
                                            <a href="cetak_bukti.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-success" title="Cetak Bukti">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data denda untuk periode ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (count($data_laporan) > 0): ?>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">TOTAL:</td>
                                <td class="text-end"><?= formatRupiah($total_denda) ?></td>
                                <td class="text-end"><?= formatRupiah($total_dibayar) ?></td>
                                <td class="text-end"><?= formatRupiah($total_sisa) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white py-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= $search ?>">Previous</a>
                                </li>
                                
                                <?php 
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                if($start > 1) { 
                                    echo '<li class="page-item"><a class="page-link" href="?page=1&search='.$search.'">1</a></li>';
                                    if($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }

                                for($i = $start; $i <= $end; $i++): 
                                ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; 

                                if($end < $total_pages) {
                                    if($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'&search='.$search.'">'.$total_pages.'</a></li>';
                                }
                                ?>

                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= $search ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center mt-2 text-muted small">
                            Menampilkan <?= $result->num_rows ?> dari total <?= $total_rows ?> data
                        </div>
                    </div>
                    <?php endif; ?>
        </div>

        <?php if ($tagihan_studi > 0 && ($metode_filter == 'all' || $metode_filter == 'tagihan_studi')): ?>
        <div class="alert alert-info mt-4 no-print shadow-sm">
            <h5><i class="bi bi-info-circle-fill me-2"></i>Catatan Tagihan Studi</h5>
            <p class="mb-0">
                Total denda yang dialihkan ke tagihan studi: <strong><?= formatRupiah($tagihan_studi) ?></strong><br>
                Mohon serahkan laporan ini ke bagian Keuangan/Akademik untuk pemrosesan lebih lanjut.
            </p>
        </div>
        <?php endif; ?>

        <div class="row mt-5">
            <div class="col-6"><small class="text-muted">Dicetak otomatis oleh sistem: <?= date('d F Y, H:i') ?></small></div>
            <div class="col-6 text-end">
                <p class="mb-5">Kepala Perpustakaan,</p>
                <p class="fw-bold text-decoration-underline">Yuliana Mangera, S.S.I.</p>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>