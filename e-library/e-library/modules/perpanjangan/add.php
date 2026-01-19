<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get data peminjaman - FIXED: Ganti get_result() dengan bind_result()
$query = "SELECT p.id, p.kode_peminjaman, p.jenis_peminjam, p.peminjam_id, p.buku_id, 
          p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.tanggal_kembali, p.status, p.denda,
          b.judul, b.nomor_buku,
          (SELECT COUNT(*) FROM perpanjangan WHERE peminjaman_id = p.id) as jumlah_perpanjangan
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.id = ? AND p.status IN ('dipinjam', 'terlambat')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($pjm_id, $pjm_kode, $pjm_jenis, $pjm_peminjam_id, $pjm_buku_id,
                   $pjm_tgl_pinjam, $pjm_tgl_tempo, $pjm_tgl_kembali, $pjm_status, $pjm_denda,
                   $pjm_judul, $pjm_nomor, $pjm_jumlah_perpanjangan);

if (!$stmt->fetch()) {
    $stmt->close();
    setAlert('danger', 'Data peminjaman tidak ditemukan!');
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
    'tanggal_kembali' => $pjm_tgl_kembali,
    'status' => $pjm_status,
    'denda' => $pjm_denda,
    'judul' => $pjm_judul,
    'nomor_buku' => $pjm_nomor,
    'jumlah_perpanjangan' => $pjm_jumlah_perpanjangan
);
$stmt->close();

// Validasi sudah pernah perpanjang
if ($pinjam['jumlah_perpanjangan'] > 0) {
    setAlert('warning', 'Peminjaman ini sudah pernah diperpanjang! Maksimal 1 kali perpanjangan.');
    header('Location: index.php');
    exit;
}

// Cek apakah sudah H-1 atau lewat
$today = new DateTime();
$jatuh_tempo = new DateTime($pinjam['tanggal_jatuh_tempo']);
$diff = $today->diff($jatuh_tempo);
$hari_tersisa = $jatuh_tempo >= $today ? $diff->days : -$diff->days;

// Validasi waktu perpanjangan (H-1 atau sudah lewat)
if ($hari_tersisa > 1) {
    setAlert('warning', 'Perpanjangan hanya bisa dilakukan mulai H-1 sebelum jatuh tempo!');
    header('Location: index.php');
    exit;
}

// Get nama peminjam
$nama_peminjam = getNamaPeminjam($conn, $pinjam['jenis_peminjam'], $pinjam['peminjam_id']);
$identifier = getIdentifierPeminjam($conn, $pinjam['jenis_peminjam'], $pinjam['peminjam_id']);

// Hitung jatuh tempo baru (jatuh tempo lama + 7 hari)
$tgl_tempo_lama = $pinjam['tanggal_jatuh_tempo'];
$tgl_tempo_baru = date('Y-m-d', strtotime($tgl_tempo_lama . ' +7 days'));

// Process perpanjangan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $today_date = date('Y-m-d');
    
    // Insert ke tabel perpanjangan
    $query = "INSERT INTO perpanjangan (peminjaman_id, tanggal_perpanjangan, jatuh_tempo_lama, jatuh_tempo_baru) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $id, $today_date, $tgl_tempo_lama, $tgl_tempo_baru);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Update status peminjaman menjadi 'diperpanjang' dan update tanggal jatuh tempo
        $update = "UPDATE peminjaman SET status = 'diperpanjang', tanggal_jatuh_tempo = ? WHERE id = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("si", $tgl_tempo_baru, $id);
        $stmt2->execute();
        $stmt2->close();
        
        setAlert('success', 'Perpanjangan berhasil! Jatuh tempo baru: ' . formatTanggalIndo($tgl_tempo_baru));
        header('Location: index.php');
        exit;
    } else {
        $stmt->close();
        setAlert('danger', 'Gagal melakukan perpanjangan!');
    }
}

// Hitung denda jika terlambat
$denda = 0;
$keterlambatan = 0;
if ($hari_tersisa < 0) {
    $keterlambatan = abs($hari_tersisa);
    $denda = hitungDenda($keterlambatan);
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
            border-left: 4px solid #38ef7d;
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
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .card-header h5 {
            margin: 0;
        }
        
        /* Table */
        .table-borderless th {
            font-weight: 600;
            color: #334155;
            padding: 0.75rem 0;
        }
        
        .table-borderless td {
            padding: 0.75rem 0;
        }
        
        /* Badge */
        .badge {
            padding: 0.4rem 0.7rem;
            font-weight: 500;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        /* Button */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
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
        
        .btn-secondary {
            background: #64748b;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #475569;
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left-color: #dc2626;
            color: #991b1b;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left-color: #3b82f6;
            color: #1e40af;
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="bi bi-arrow-clockwise me-2"></i>Perpanjangan Buku</h1>
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
                                <th>Jatuh Tempo Lama</th>
                                <td>
                                    <span class="badge bg-<?= $hari_tersisa < 0 ? 'danger' : 'warning' ?>">
                                        <?= formatTanggalIndo($tgl_tempo_lama) ?>
                                    </span>
                                    <?php if ($hari_tersisa < 0): ?>
                                        <span class="text-danger ms-2">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Terlambat <?= $keterlambatan ?> hari
                                        </span>
                                    <?php elseif ($hari_tersisa == 0): ?>
                                        <span class="text-warning ms-2">
                                            <i class="bi bi-clock"></i>
                                            Jatuh tempo hari ini
                                        </span>
                                    <?php else: ?>
                                        <span class="text-info ms-2">
                                            <i class="bi bi-clock"></i>
                                            Tersisa <?= $hari_tersisa ?> hari
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Jatuh Tempo Baru</th>
                                <td>
                                    <span class="badge bg-success">
                                        <?= formatTanggalIndo($tgl_tempo_baru) ?>
                                    </span>
                                    <small class="text-muted ms-2">(+7 hari dari jatuh tempo lama)</small>
                                </td>
                            </tr>
                            <?php if ($denda > 0): ?>
                            <tr>
                                <th>Denda Keterlambatan</th>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= formatRupiah($denda) ?>
                                    </span>
                                    <small class="text-muted ms-2">(<?= $keterlambatan ?> hari × Rp 1.000)</small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-<?= $pinjam['status'] == 'terlambat' ? 'danger' : 'info' ?>">
                                        <?= ucfirst($pinjam['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <?php if ($denda > 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Perhatian!</strong> Peminjaman ini terlambat <?= $keterlambatan ?> hari. 
                            Denda sebesar <strong><?= formatRupiah($denda) ?></strong> akan dicatat dan harus dibayar saat pengembalian.
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="" onsubmit="return confirm('Yakin ingin melakukan perpanjangan?')">
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Konfirmasi Perpanjangan
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
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle me-2"></i>Informasi Perpanjangan</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li class="mb-2"><strong>Durasi Perpanjangan:</strong> 7 hari tambahan</li>
                            <li class="mb-2"><strong>Maksimal Perpanjangan:</strong> 1 kali</li>
                            <li class="mb-2"><strong>Waktu Perpanjangan:</strong> H-1 sebelum jatuh tempo atau setelahnya</li>
                            <li><strong>Denda Keterlambatan:</strong> Rp 1.000 per hari</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Catatan Penting:</strong><br>
                    <small>Jatuh tempo baru dihitung dari <strong>jatuh tempo lama</strong>, bukan dari hari ini. Jika peminjaman sudah terlambat, denda tetap dihitung saat pengembalian.</small>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>