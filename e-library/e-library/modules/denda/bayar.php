<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get data pengembalian dengan sisa denda
$query = "SELECT pg.id, pg.peminjaman_id, pg.tanggal_kembali, pg.keterlambatan_hari, 
          pg.denda, pg.denda_dibayar, pg.uang_kembali, pg.sisa_denda, 
          pg.metode_pembayaran, pg.keterangan, pg.created_at, pg.nomor_bukti, pg.tanggal_lunas,
          p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, 
          b.judul, b.nomor_buku
          FROM pengembalian pg
          JOIN peminjaman p ON pg.peminjaman_id = p.id
          JOIN buku b ON p.buku_id = b.id
          WHERE pg.id = ? AND pg.sisa_denda > 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result(
    $pg_id, $pg_peminjaman_id, $pg_tanggal_kembali, $pg_keterlambatan_hari, 
    $pg_denda, $pg_denda_dibayar, $pg_uang_kembali, $pg_sisa_denda, 
    $pg_metode_pembayaran, $pg_keterangan, $pg_created_at, $pg_nomor_bukti, $pg_tanggal_lunas,
    $kode_peminjaman, $jenis_peminjam, $peminjam_id, $judul, $nomor_buku
);

if (!$stmt->fetch()) {
    $stmt->close();
    setAlert('danger', 'Data tidak ditemukan atau denda sudah lunas!');
    header('Location: index.php');
    exit;
}

$data = [
    'id' => $pg_id,
    'peminjaman_id' => $pg_peminjaman_id,
    'tanggal_kembali' => $pg_tanggal_kembali,
    'keterlambatan_hari' => $pg_keterlambatan_hari,
    'denda' => $pg_denda,
    'denda_dibayar' => $pg_denda_dibayar,
    'uang_kembali' => $pg_uang_kembali,
    'sisa_denda' => $pg_sisa_denda,
    'metode_pembayaran' => $pg_metode_pembayaran,
    'keterangan' => $pg_keterangan,
    'created_at' => $pg_created_at,
    'nomor_bukti' => $pg_nomor_bukti,
    'tanggal_lunas' => $pg_tanggal_lunas,
    'kode_peminjaman' => $kode_peminjaman,
    'jenis_peminjam' => $jenis_peminjam,
    'peminjam_id' => $peminjam_id,
    'judul' => $judul,
    'nomor_buku' => $nomor_buku
];
$stmt->close();

// Get nama peminjam
$nama_peminjam = getNamaPeminjam($conn, $data['jenis_peminjam'], $data['peminjam_id']);
$identifier = getIdentifierPeminjam($conn, $data['jenis_peminjam'], $data['peminjam_id']);

// Get riwayat pembayaran denda
$query_detail = "SELECT id, pengembalian_id, tanggal_bayar, nominal, metode_pembayaran, keterangan, created_at 
                 FROM pembayaran_denda_detail WHERE pengembalian_id = ? ORDER BY created_at DESC";
$stmt_detail = $conn->prepare($query_detail);
$stmt_detail->bind_param("i", $id);
$stmt_detail->execute();
$stmt_detail->bind_result($det_id, $det_pengembalian_id, $det_tanggal_bayar, $det_nominal, $det_metode, $det_keterangan, $det_created_at);

$detail_bayar = [];
while ($stmt_detail->fetch()) {
    $detail_bayar[] = [
        'id' => $det_id,
        'pengembalian_id' => $det_pengembalian_id,
        'tanggal_bayar' => $det_tanggal_bayar,
        'nominal' => $det_nominal,
        'metode_pembayaran' => $det_metode,
        'keterangan' => $det_keterangan,
        'created_at' => $det_created_at
    ];
}
$stmt_detail->close();

// Process pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_bayar = $_POST['tanggal_bayar'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';
    
    $sisa_denda_sekarang = $data['sisa_denda'];
    $nominal_bayar = 0;
    $uang_kembali = 0;
    $sisa_denda_baru = 0;
    
    // Hitung pembayaran
    if ($metode_pembayaran == 'cash' || $metode_pembayaran == 'transfer') {
        $nominal_bayar = intval($_POST['nominal_bayar']);
        
        if ($nominal_bayar >= $sisa_denda_sekarang) {
            $uang_kembali = $nominal_bayar - $sisa_denda_sekarang;
            $sisa_denda_baru = 0;
        } else {
            $sisa_denda_baru = $sisa_denda_sekarang - $nominal_bayar;
        }
    } elseif ($metode_pembayaran == 'tagihan_studi') {
        // Tagihan studi: sisa denda dianggap lunas
        $nominal_bayar = $sisa_denda_sekarang;
        $sisa_denda_baru = 0;
    } elseif ($metode_pembayaran == 'waive') {
        // Waive: denda dibatalkan
        if (empty($keterangan)) {
            setAlert('warning', 'Keterangan wajib diisi untuk pembebasan denda!');
            goto form_display;
        }
        $nominal_bayar = 0;
        $sisa_denda_baru = 0;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 1. Insert ke pembayaran_denda_detail
        $query = "INSERT INTO pembayaran_denda_detail 
                  (pengembalian_id, tanggal_bayar, nominal, metode_pembayaran, keterangan) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isiss", $id, $tanggal_bayar, $nominal_bayar, $metode_pembayaran, $keterangan);
        $stmt->execute();
        $stmt->close();
        
        // 2. Generate nomor bukti jika LUNAS
        $nomor_bukti = null;
        $tanggal_lunas_val = null;
        
        if ($sisa_denda_baru == 0) {
            // Generate nomor bukti format: BP-DENDA/XXX/MM/YYYY
            $tahun = date('Y');
            $bulan = date('n');
            $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            
            // Get last number
            $query_last = "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(nomor_bukti, '/', 1), '-', -1) AS UNSIGNED)) as last_number 
                          FROM pengembalian 
                          WHERE nomor_bukti LIKE 'BP-DENDA/%' AND YEAR(tanggal_lunas) = ?";
            $stmt_last = $conn->prepare($query_last);
            $stmt_last->bind_param("i", $tahun);
            $stmt_last->execute();
            $stmt_last->bind_result($last_number);
            $stmt_last->fetch();
            $stmt_last->close();
            
            $next_number = str_pad(($last_number ?? 0) + 1, 3, '0', STR_PAD_LEFT);
            $nomor_bukti = "BP-DENDA-{$next_number}/{$bulan_romawi[$bulan]}/{$tahun}";
            $tanggal_lunas_val = date('Y-m-d H:i:s');
        }
        
        // 3. Update tabel pengembalian
        $denda_dibayar_total = $data['denda_dibayar'] + $nominal_bayar;
        $uang_kembali_total = $data['uang_kembali'] + $uang_kembali;
        
        $query = "UPDATE pengembalian 
                  SET denda_dibayar = ?, uang_kembali = ?, sisa_denda = ?, nomor_bukti = ?, tanggal_lunas = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiissi", $denda_dibayar_total, $uang_kembali_total, $sisa_denda_baru, $nomor_bukti, $tanggal_lunas_val, $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Success message
        $msg = 'Pembayaran denda berhasil dicatat!';
        if ($sisa_denda_baru == 0) {
            $msg .= ' Denda sudah LUNAS. <a href="cetak_bukti.php?id=' . $id . '" target="_blank" class="alert-link">Download Bukti Pembayaran</a>';
        } else {
            $msg .= ' Sisa denda: ' . formatRupiah($sisa_denda_baru);
        }
        
        setAlert('success', $msg);
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        setAlert('danger', 'Gagal memproses pembayaran: ' . $e->getMessage());
    }
}

form_display:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayar Denda - E-Library STK Yakobus</title>
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
        .card-header h5 {
            margin: 0;
            font-size: 1rem;
        }
        
        /* Table */
        .table {
            font-size: 0.9rem;
        }
        .table-borderless th {
            font-weight: 600;
            color: #475569;
            padding: 0.6rem 0.75rem;
        }
        .table-borderless td {
            padding: 0.6rem 0.75rem;
        }
        
        /* Alert */
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        /* Form */
        .form-label {
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.4rem;
        }
        .form-control, .form-select, textarea.form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
        }
        .form-control:focus, .form-select:focus, textarea.form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
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
        
        /* List Group */
        .list-group-item {
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border: 1px solid #e2e8f0;
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
        <div class="page-header">
            <h1><i class="bi bi-cash me-2"></i>Bayar Denda</h1>
        </div>

        <?php showAlert(); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Informasi Peminjaman</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Kode Peminjaman</th>
                                <td><code><?= $data['kode_peminjaman'] ?></code></td>
                            </tr>
                            <tr>
                                <th>Peminjam</th>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><?= $identifier ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Buku</th>
                                <td>
                                    <strong><?= $data['judul'] ?></strong><br>
                                    <small class="text-muted"><?= $data['nomor_buku'] ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Kembali</th>
                                <td><?= formatTanggalIndo($data['tanggal_kembali']) ?></td>
                            </tr>
                            <tr>
                                <th>Keterlambatan</th>
                                <td><span class="badge bg-danger"><?= $data['keterlambatan_hari'] ?> hari</span></td>
                            </tr>
                        </table>

                        <div class="alert alert-warning">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Total Denda:</strong><br>
                                    <h4 class="text-danger mb-0"><?= formatRupiah($data['denda']) ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <strong>Sudah Dibayar:</strong><br>
                                    <h4 class="text-success mb-0"><?= formatRupiah($data['denda_dibayar']) ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <strong>Sisa Denda:</strong><br>
                                    <h4 class="text-warning mb-0"><?= formatRupiah($data['sisa_denda']) ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <strong>Metode Awal:</strong><br>
                                    <span class="badge bg-secondary"><?= ucfirst($data['metode_pembayaran']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Pembayaran -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-credit-card me-2"></i>Form Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formBayar">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Tanggal Pembayaran <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_bayar" 
                                           value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-cash me-1"></i>
                                        Metode Pembayaran <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="metode_pembayaran" id="metodePembayaran" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="tagihan_studi">Include Tagihan Studi (Lunas)</option>
                                        <option value="waive">Waive (Pembebasan)</option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3" id="inputNominal" style="display:none;">
                                    <label class="form-label">
                                        <i class="bi bi-wallet2 me-1"></i>
                                        Nominal Pembayaran <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" name="nominal_bayar" id="nominalBayar" 
                                           min="0" placeholder="Masukkan nominal pembayaran">
                                    <div class="form-text">
                                        Sisa denda: <strong><?= formatRupiah($data['sisa_denda']) ?></strong>
                                    </div>
                                    <div id="uangKembaliInfo" class="alert alert-success mt-2" style="display:none;">
                                        <i class="bi bi-cash-stack me-2"></i>
                                        Uang Kembali: <strong id="uangKembaliText">Rp 0</strong>
                                    </div>
                                    <div id="sisaDendaInfo" class="alert alert-danger mt-2" style="display:none;">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        Sisa Denda Baru: <strong id="sisaDendaText">Rp 0</strong>
                                    </div>
                                </div>

                                <div class="col-12 mb-3" id="infoTagihan" style="display:none;">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        <strong>Informasi:</strong> Sisa denda sebesar <strong><?= formatRupiah($data['sisa_denda']) ?></strong> 
                                        akan dialihkan ke tagihan studi dan dianggap <strong>LUNAS</strong> di sistem e-library.
                                    </div>
                                </div>

                                <div class="col-12 mb-3" id="inputKeterangan" style="display:none;">
                                    <label class="form-label">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        Keterangan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" name="keterangan" rows="3" 
                                              placeholder="Alasan pembebasan atau catatan pembayaran..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Proses Pembayaran
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Riwayat Pembayaran -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock-history me-2"></i>Riwayat Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_detail->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($detail = $result_detail->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= formatRupiah($detail['nominal']) ?></h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i> <?= formatTanggalIndo($detail['tanggal_bayar']) ?><br>
                                                <i class="bi bi-credit-card"></i> <?= ucfirst($detail['metode_pembayaran']) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php if (!empty($detail['keterangan'])): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($detail['keterangan']) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Belum ada pembayaran</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sisaDenda = <?= $data['sisa_denda'] ?>;
        const metodePembayaran = document.getElementById('metodePembayaran');
        const inputNominal = document.getElementById('inputNominal');
        const nominalBayar = document.getElementById('nominalBayar');
        const uangKembaliInfo = document.getElementById('uangKembaliInfo');
        const sisaDendaInfo = document.getElementById('sisaDendaInfo');
        const infoTagihan = document.getElementById('infoTagihan');
        const inputKeterangan = document.getElementById('inputKeterangan');

        metodePembayaran.addEventListener('change', function() {
            const metode = this.value;
            
            // Reset
            inputNominal.style.display = 'none';
            infoTagihan.style.display = 'none';
            inputKeterangan.style.display = 'none';
            nominalBayar.required = false;
            document.querySelector('[name="keterangan"]').required = false;
            
            if (metode === 'cash' || metode === 'transfer') {
                inputNominal.style.display = 'block';
                nominalBayar.required = true;
            } else if (metode === 'tagihan_studi') {
                infoTagihan.style.display = 'block';
            } else if (metode === 'waive') {
                inputKeterangan.style.display = 'block';
                document.querySelector('[name="keterangan"]').required = true;
            }
        });

        nominalBayar.addEventListener('input', function() {
            const nominal = parseInt(this.value) || 0;
            
            if (nominal >= sisaDenda) {
                const kembali = nominal - sisaDenda;
                uangKembaliInfo.style.display = 'block';
                sisaDendaInfo.style.display = 'none';
                document.getElementById('uangKembaliText').textContent = 'Rp ' + kembali.toLocaleString('id-ID');
            } else if (nominal > 0) {
                const sisa = sisaDenda - nominal;
                sisaDendaInfo.style.display = 'block';
                uangKembaliInfo.style.display = 'none';
                document.getElementById('sisaDendaText').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
            } else {
                uangKembaliInfo.style.display = 'none';
                sisaDendaInfo.style.display = 'none';
            }
        });

        document.getElementById('formBayar').addEventListener('submit', function(e) {
            if (!confirm('Yakin ingin memproses pembayaran ini?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>