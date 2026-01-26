<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get data peminjaman
$query = "SELECT p.*, b.judul, b.nomor_buku, b.stok_tersedia
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.id = ? AND p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($pjm_id, $pjm_kode, $pjm_jenis, $pjm_peminjam_id, $pjm_buku_id,
                   $pjm_tgl_pinjam, $pjm_tgl_tempo, $pjm_tgl_kembali, $pjm_status, $pjm_denda,
                   $pjm_created, $pjm_updated, $pjm_judul, $pjm_nomor, $pjm_stok_tersedia);

if (!$stmt->fetch()) {
    $stmt->close();
    setAlert('danger', 'Data peminjaman tidak ditemukan atau sudah dikembalikan!');
    header('Location: index.php');
    exit;
}

// Simpan data ke array
$pinjam = array(
    'id' => $pjm_id,
    'kode_peminjaman' => $pjm_kode,
    'jenis_peminjam' => $pjm_jenis,
    'peminjam_id' => $pjm_peminjam_id,
    'buku_id' => $pjm_buku_id,
    'tanggal_pinjam' => $pjm_tgl_pinjam,
    'tanggal_jatuh_tempo' => $pjm_tgl_tempo,
    'status' => $pjm_status,
    'judul' => $pjm_judul,
    'nomor_buku' => $pjm_nomor,
    'stok_tersedia' => $pjm_stok_tersedia
);
$stmt->close();

// Get nama peminjam
$nama_peminjam = getNamaPeminjam($conn, $pinjam['jenis_peminjam'], $pinjam['peminjam_id']);
$identifier = getIdentifierPeminjam($conn, $pinjam['jenis_peminjam'], $pinjam['peminjam_id']);

// =======================================================================
// PROSES PENGEMBALIAN BUKU (STEP 1: LOGIC & VALIDASI)
// =======================================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Sanitasi Input (Wajib agar aman)
    $tanggal_kembali   = sanitize($_POST['tanggal_kembali']);
    $metode_pembayaran = sanitize($_POST['metode_pembayaran']);
    $keterangan        = isset($_POST['keterangan']) ? sanitize($_POST['keterangan']) : '';
    
    // 2. Cek Status Buku Hilang/Rusak
    // Default status = 'normal' jika tidak ada input
    $status_buku           = isset($_POST['status_buku']) && !empty($_POST['status_buku']) ? sanitize($_POST['status_buku']) : 'normal';
    $nominal_denda_buku    = isset($_POST['nominal_denda_buku']) ? intval($_POST['nominal_denda_buku']) : 0;
    $keterangan_kehilangan = isset($_POST['keterangan_kehilangan']) ? sanitize($_POST['keterangan_kehilangan']) : '';
    
    // Flagging boolean
    $is_buku_hilang = ($status_buku != 'normal');
    
    // 3. Hitung Keterlambatan & Denda Telat
    $jatuh_tempo = new DateTime($pinjam['tanggal_jatuh_tempo']);
    $tgl_kembali_obj = new DateTime($tanggal_kembali);
    
    $keterlambatan_hari = 0;
    $denda_keterlambatan = 0;

    if ($tgl_kembali_obj > $jatuh_tempo) {
        $selisih = $jatuh_tempo->diff($tgl_kembali_obj);
        $keterlambatan_hari = $selisih->days;
        $denda_keterlambatan = $keterlambatan_hari * 1000; // Tarif Rp 1.000/hari
    }
    
    // 4. Tentukan TOTAL TAGIHAN (Logic MAX)
    // Jika buku hilang, denda telat biasanya diabaikan (diganti harga buku), 
    // atau diambil mana yang paling merugikan (Max).
    if ($is_buku_hilang) {
        $total_tagihan = max($denda_keterlambatan, $nominal_denda_buku);
    } else {
        $total_tagihan = $denda_keterlambatan;
    }
    
    // 5. Hitung Pembayaran
    $nominal_bayar = 0; // Uang yang diterima fisik
    $sisa_denda = 0;    // Sisa kewajiban di sistem perpus
    
    if ($total_tagihan > 0) {
        if ($metode_pembayaran == 'cash' || $metode_pembayaran == 'transfer') {
            // Jika bayar tunai/transfer, hitung uang masuk
            $nominal_bayar = isset($_POST['nominal_bayar']) ? intval($_POST['nominal_bayar']) : 0;
            
            if ($nominal_bayar >= $total_tagihan) {
                $sisa_denda = 0; // Lunas
            } else {
                $sisa_denda = $total_tagihan - $nominal_bayar; // Belum Lunas
            }
            
        } elseif ($metode_pembayaran == 'tagihan_studi') {
            // Jika tagihan studi: 
            // - Uang fisik (nominal_bayar) = 0
            // - Sisa denda di perpus = 0 (karena dianggap lunas/dialihkan)
            $nominal_bayar = 0;
            $sisa_denda = 0;
            
        } elseif ($metode_pembayaran == 'waive') {
            // Jika waive (pembebasan):
            $nominal_bayar = 0;
            $sisa_denda = 0;
            
            // Validasi wajib isi alasan
            if (empty($keterangan)) {
                setAlert('danger', 'Keterangan wajib diisi untuk pembebasan denda (Waive)!');
                // Trik agar form tidak reset total, redirect kembali
                header("Location: add.php?id=" . $id); 
                exit;
            }
        }
    }
    
    // Generate nomor bukti jika LUNAS
    $nomor_bukti = null;
    $tanggal_lunas_val = null;
    
    if ($sisa_denda == 0 && $total_denda > 0) {
        // Generate nomor bukti format: BP-DENDA/XXX/MM/YYYY
        $tahun = date('Y');
        $bulan = date('n');
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        
        // Get last number - COMPATIBLE dengan bind_result()
        $query_last = "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(nomor_bukti, '/', 1), '-', -1) AS UNSIGNED)) as last_number 
                      FROM pengembalian 
                      WHERE nomor_bukti LIKE 'BP-DENDA-%' AND YEAR(tanggal_lunas) = ?";
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // 2. Insert ke tabel pengembalian (UPDATE: TAMBAH KOLOM STATUS BUKU)
        $query = "INSERT INTO pengembalian 
                  (peminjaman_id, tanggal_kembali, keterlambatan_hari, denda, denda_dibayar, sisa_denda, metode_pembayaran, keterangan, nomor_bukti, tanggal_lunas, status_buku, nominal_denda_buku, keterangan_kehilangan) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        // Perhatikan string tipe data: "isi iiiisssssss" -> "isi iiiisssssis" (tambah i dan s di akhir)
        $stmt->bind_param("isiiiisssssis", 
            $id, 
            $tanggal_kembali, 
            $keterlambatan, 
            $total_tagihan, // PENTING: Gunakan $total_tagihan (bukan $denda lama)
            $nominal_bayar, 
            $sisa_denda, 
            $metode_pembayaran, 
            $keterangan,
            $nomor_bukti,
            $tanggal_lunas,
            $status_buku,         // Baru
            $nominal_denda_buku,  // Baru
            $keterangan_kehilangan // Baru
        );
        $stmt->execute();
        $pengembalian_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Insert ke tabel pembayaran_denda_detail (jika ada pembayaran)
        if ($denda_dibayar > 0 && ($metode_pembayaran == 'cash' || $metode_pembayaran == 'transfer' || $metode_pembayaran == 'tagihan_studi')) {
            $query = "INSERT INTO pembayaran_denda_detail 
                      (pengembalian_id, tanggal_bayar, nominal, metode_pembayaran, keterangan) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isiss", $pengembalian_id, $tanggal_kembali, $denda_dibayar, $metode_pembayaran, $keterangan);
            $stmt->execute();
            $stmt->close();
        }
        
        // 3. Update status peminjaman
        $query = "UPDATE peminjaman 
                  SET status = 'dikembalikan', tanggal_kembali = ?, denda = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $tanggal_kembali, $total_denda, $id);
        $stmt->execute();
        $stmt->close();
        
        // 4. Update stok buku (LOGIKA: HILANG vs NORMAL)
        if ($status_buku == 'hilang' || $status_buku == 'rusak_parah') {
            // SKENARIO A: BUKU HILANG/RUSAK
            // Kurangi STOK MASTER (Aset), Stok Tersedia JANGAN ditambah (karena fisik tidak ada)
            $query = "UPDATE buku SET stok = stok - 1 WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $pinjam['buku_id']);
            $stmt->execute();
            $stmt->close();
        } else {
            // SKENARIO B: NORMAL
            // Kembalikan ke rak (Tambah Stok Tersedia +1)
            $query = "UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $pinjam['buku_id']);
            $stmt->execute();
            $stmt->close();
        }
        // Jika hilang/rusak, stok TIDAK ditambah
        
        // Commit transaction
        $conn->commit();
        
        // Success message
        $msg = 'Pengembalian berhasil dicatat!';
        if ($is_buku_hilang) {
            $msg .= ' Status buku: ' . ucwords(str_replace('_', ' ', $status_buku)) . '.';
        }
        if ($total_denda > 0) {
            $msg .= ' Denda: ' . formatRupiah($total_denda);
            if ($sisa_denda > 0) {
                $msg .= ', Sisa denda: ' . formatRupiah($sisa_denda);
            }
            if ($uang_kembali > 0) {
                $msg .= ', Uang kembali: ' . formatRupiah($uang_kembali);
            }
        }
        
        setAlert('success', $msg);
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        setAlert('danger', 'Gagal memproses pengembalian: ' . $e->getMessage());
    }
}

form_display:
// Hitung keterlambatan dan denda untuk preview
$today = new DateTime();
$jatuh_tempo = new DateTime($pinjam['tanggal_jatuh_tempo']);

if ($today > $jatuh_tempo) {
    $selisih = $jatuh_tempo->diff($today);
    $keterlambatan = $selisih->days;
    $denda = $keterlambatan * 1000;
} else {
    $keterlambatan = 0;
    $denda = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Pengembalian - E-Library STK Yakobus</title>
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
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
        }
        .form-control:focus, .form-select:focus {
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
            <h1><i class="bi bi-check-circle me-2"></i>Proses Pengembalian Buku</h1>
        </div>

        <?php showAlert(); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Detail Peminjaman</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Kode Peminjaman</th>
                                <td><code><?= $pinjam['kode_peminjaman'] ?></code></td>
                            </tr>
                            <tr>
                                <th>Peminjam</th>
                                <td>
                                    <strong><?= $nama_peminjam ?></strong><br>
                                    <small class="text-muted"><?= ucfirst($pinjam['jenis_peminjam']) ?> - <?= $identifier ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Buku</th>
                                <td>
                                    <strong><?= $pinjam['judul'] ?></strong><br>
                                    <small class="text-muted">No. Buku: <?= $pinjam['nomor_buku'] ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Pinjam</th>
                                <td><?= formatTanggalIndo($pinjam['tanggal_pinjam']) ?></td>
                            </tr>
                            <tr>
                                <th>Jatuh Tempo</th>
                                <td>
                                    <?= formatTanggalIndo($pinjam['tanggal_jatuh_tempo']) ?>
                                    <span class="badge bg-<?= $keterlambatan > 0 ? 'danger' : 'success' ?> ms-2">
                                        <?= $keterlambatan > 0 ? 'Terlambat ' . $keterlambatan . ' hari' : 'Tepat Waktu' ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-<?= $pinjam['status'] == 'terlambat' ? 'danger' : ($pinjam['status'] == 'diperpanjang' ? 'info' : 'success') ?>">
                                        <?= ucfirst($pinjam['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <form method="POST" action="" id="formPengembalian">
                            
                            <?php if ($denda > 0): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Denda Keterlambatan:</strong> <?= formatRupiah($denda) ?> 
                                (<?= $keterlambatan ?> hari × Rp 1.000)
                            </div>
                            <?php endif; ?>
                        
                            <div class="card border-danger mb-3">
                                <div class="card-header bg-danger text-white">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="bukuHilang" onchange="toggleBukuHilang()">
                                        <label class="form-check-label fw-bold" for="bukuHilang">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Lapor Buku Hilang / Rusak Parah
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body" id="sectionBukuHilang" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Kategori Kehilangan <span class="text-danger">*</span></label>
                                            <select class="form-select" id="statusBuku" name="status_buku">
                                                <option value="">- Pilih Kategori -</option>
                                                <option value="hilang">Hilang</option>
                                                <option value="rusak_parah">Rusak Parah</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nominal Denda Buku <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="nominalDendaBuku" 
                                                       name="nominal_denda_buku" placeholder="0" min="0" onkeyup="hitungTotalDenda()">
                                            </div>
                                            <small class="text-muted">Harga penggantian buku</small>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Keterangan</label>
                                            <textarea class="form-control" name="keterangan_kehilangan" rows="2" placeholder="Kronologi singkat..."></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mb-0">
                                        <div class="row">
                                            <div class="col-md-6"><small>Denda Telat: <strong id="displayDendaTelat"><?= formatRupiah($denda) ?></strong></small></div>
                                            <div class="col-md-6"><small>Ganti Buku: <strong id="displayNominalBuku">Rp 0</strong></small></div>
                                        </div>
                                        <hr class="my-2">
                                        <strong class="text-primary">Total Bayar: <span id="displayTotalDenda"><?= formatRupiah($denda) ?></span></strong>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Tanggal Pengembalian <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_kembali" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3" id="paymentSection" style="display: <?= ($denda > 0) ? 'block' : 'none' ?>;">
                                    <label class="form-label"><i class="bi bi-cash me-1"></i>Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select" name="metode_pembayaran" id="metodePembayaran" <?= ($denda > 0) ? 'required' : '' ?>>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="tagihan_studi">Include Tagihan Studi</option>
                                        <option value="waive">Waive (Pembebasan)</option>
                                    </select>
                                </div>
                        
                                <input type="hidden" name="metode_pembayaran_default" value="cash" id="defaultPaymentMethod">
                        
                                <div class="col-12 mb-3" id="inputNominal" style="display:none;">
                                    <label class="form-label">Nominal Pembayaran <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="nominal_bayar" id="nominalBayar" min="0">
                                    <div id="uangKembaliInfo" class="alert alert-success mt-2 p-2" style="display:none;">Kembali: <strong id="uangKembaliText">Rp 0</strong></div>
                                    <div id="sisaDendaInfo" class="alert alert-danger mt-2 p-2" style="display:none;">Kurang: <strong id="sisaDendaText">Rp 0</strong></div>
                                </div>
                        
                                <div class="col-12 mb-3" id="infoTagihan" style="display:none;">
                                    <div class="alert alert-info">Denda akan dialihkan ke tagihan studi akademik.</div>
                                </div>
                        
                                <div class="col-12 mb-3" id="inputKeterangan" style="display:none;">
                                    <label class="form-label">Alasan Pembebasan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="keterangan" rows="2"></textarea>
                                </div>
                            </div>
                        
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-2"></i>Proses Pengembalian</button>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Informasi Pengembalian</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li class="mb-2"><strong>Denda:</strong> Rp 1.000 per hari keterlambatan</li>
                            <li class="mb-2"><strong>Pembayaran:</strong> Cash, Transfer, atau Tagihan Studi</li>
                            <li class="mb-2"><strong>Cicilan:</strong> Bisa bayar sebagian, sisa tercatat</li>
                            <li><strong>Stok:</strong> Otomatis bertambah setelah pengembalian</li>
                        </ul>
                    </div>
                </div>

                <?php if ($denda > 0): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong><br>
                    <small>Pastikan metode pembayaran sudah benar sebelum proses pengembalian. 
                    Untuk waive (pembebasan), wajib mengisi alasan.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
    // === 1. INISIALISASI VARIABEL ===
    const dendaKeterlambatan = <?= $denda ?>; // Dari PHP
    let totalDendaGlobal = dendaKeterlambatan;

    // Elements
    const elPaymentSection = document.getElementById('paymentSection');
    const elMetode = document.getElementById('metodePembayaran');
    const elDefaultMethod = document.getElementById('defaultPaymentMethod');
    
    // === 2. LOGIKA BUKU HILANG ===
    function toggleBukuHilang() {
        const checkbox = document.getElementById('bukuHilang');
        const section = document.getElementById('sectionBukuHilang');
        const statusBuku = document.getElementById('statusBuku');
        const nominalDenda = document.getElementById('nominalDendaBuku');
        
        if (checkbox.checked) {
            section.style.display = 'block';
            statusBuku.setAttribute('required', 'required');
            nominalDenda.setAttribute('required', 'required');
        } else {
            section.style.display = 'none';
            statusBuku.removeAttribute('required');
            nominalDenda.removeAttribute('required');
            statusBuku.value = '';
            nominalDenda.value = ''; // Reset nilai
            hitungTotalDenda(); // Recalculate jadi 0 (atau denda telat)
        }
    }

    // === 3. HITUNG TOTAL & KONTROL UI PEMBAYARAN ===
    function hitungTotalDenda() {
        const inputBuku = document.getElementById('nominalDendaBuku');
        const nominalBuku = inputBuku ? (parseInt(inputBuku.value) || 0) : 0;
        
        // Logika Max: Ambil mana yang lebih besar (Telat vs Ganti Buku)
        const totalDenda = Math.max(dendaKeterlambatan, nominalBuku);
        totalDendaGlobal = totalDenda;
        
        // Update Teks Rupiah
        const formatRp = (n) => 'Rp ' + n.toLocaleString('id-ID');
        document.getElementById('displayNominalBuku').textContent = formatRp(nominalBuku);
        document.getElementById('displayTotalDenda').textContent = formatRp(totalDenda);

        // LOGIKA PENTING: Show/Hide Payment Section
        if (totalDenda > 0) {
            // Ada tagihan (Entah telat atau buku hilang) -> Munculkan Opsi Bayar
            elPaymentSection.style.display = 'block';
            elMetode.setAttribute('required', 'required');
            elDefaultMethod.disabled = true; // Matikan default value
        } else {
            // Tidak ada tagihan sama sekali (Tepat waktu & Buku ada)
            elPaymentSection.style.display = 'none';
            elMetode.removeAttribute('required');
            elMetode.value = '';
            elDefaultMethod.disabled = false; // Aktifkan default value 'cash'
            toggleMetodePembayaran(); // Reset inputan bawahnya
        }

        // Update kalkulasi uang pas/kembali jika user sudah ketik
        if (document.getElementById('nominalBayar').value) {
            hitungNominalBayar();
        }
    }

    // === 4. LOGIKA METODE PEMBAYARAN ===
    function toggleMetodePembayaran() {
        const metode = elMetode.value;
        const elNominalInput = document.getElementById('inputNominal');
        const elTagihanInfo = document.getElementById('infoTagihan');
        const elKetInput = document.getElementById('inputKeterangan');
        const inpNominal = document.getElementById('nominalBayar');
        const txtKet = document.querySelector('textarea[name="keterangan"]');

        // Hide All First
        elNominalInput.style.display = 'none';
        elTagihanInfo.style.display = 'none';
        elKetInput.style.display = 'none';
        inpNominal.removeAttribute('required');
        if(txtKet) txtKet.removeAttribute('required');

        if (totalDendaGlobal > 0) {
            if (metode === 'cash' || metode === 'transfer') {
                elNominalInput.style.display = 'block';
                inpNominal.setAttribute('required', 'required');
            } else if (metode === 'tagihan_studi') {
                elTagihanInfo.style.display = 'block';
            } else if (metode === 'waive') {
                elKetInput.style.display = 'block';
                if(txtKet) txtKet.setAttribute('required', 'required');
            }
        }
    }

    // === 5. HITUNG KEMBALIAN ===
    function hitungNominalBayar() {
        const bayar = parseInt(document.getElementById('nominalBayar').value) || 0;
        const elKembali = document.getElementById('uangKembaliInfo');
        const elSisa = document.getElementById('sisaDendaInfo');
        
        if (bayar >= totalDendaGlobal) {
            const kembali = bayar - totalDendaGlobal;
            document.getElementById('uangKembaliText').textContent = 'Rp ' + kembali.toLocaleString('id-ID');
            elKembali.style.display = 'block';
            elSisa.style.display = 'none';
        } else {
            const kurang = totalDendaGlobal - bayar;
            document.getElementById('sisaDendaText').textContent = 'Rp ' + kurang.toLocaleString('id-ID');
            elSisa.style.display = 'block';
            elKembali.style.display = 'none';
        }
    }

    // Event Listeners
    if(elMetode) elMetode.addEventListener('change', toggleMetodePembayaran);
    document.getElementById('nominalBayar')?.addEventListener('input', hitungNominalBayar);

    // Initial Check (Penting jika refresh halaman saat telat)
    hitungTotalDenda();
</script>

</body>
</html>