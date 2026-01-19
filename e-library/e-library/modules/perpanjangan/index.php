<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();
updateStatusPeminjaman($conn);

// Get peminjaman yang bisa diperpanjang (status: dipinjam/terlambat, belum pernah diperpanjang)
$query = "SELECT p.*, b.judul, b.nomor_buku,
          (SELECT COUNT(*) FROM perpanjangan WHERE peminjaman_id = p.id) as jumlah_perpanjangan
          FROM peminjaman p
          JOIN buku b ON p.buku_id = b.id
          WHERE p.status IN ('dipinjam', 'terlambat')
          ORDER BY p.tanggal_jatuh_tempo ASC";
$result = $conn->query($query);

// Get riwayat perpanjangan yang sudah dilakukan
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
                  ORDER BY pr.tanggal_perpanjangan DESC";
$result_riwayat = $conn->query($query_riwayat);
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
            background: rgba(56, 239, 125, 0.05);
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
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(56, 239, 125, 0.4);
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
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        .info-box {
            background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
            border-left: 4px solid #0284c7;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .expired-soon {
            background: rgba(251, 191, 36, 0.1);
            border-left: 3px solid #f59e0b;
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
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">NO</th>
                                <th width="12%">KODE</th>
                                <th width="18%">PEMINJAM</th>
                                <th width="20%">BUKU</th>
                                <th width="10%">TGL PINJAM</th>
                                <th width="10%">JATUH TEMPO</th>
                                <th width="10%">STATUS</th>
                                <th width="10%">PERPANJANGAN</th>
                                <th width="10%">AKSI</th>
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
                                        <br><small class="badge bg-warning">H-<?= $hari_tersisa ?></small>
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
                                        <a href="add.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success" title="Perpanjang">
                                            <i class="bi bi-arrow-clockwise"></i> Perpanjang
                                        </a>
                                    <?php elseif ($sudah_perpanjang): ?>
                                        <small class="text-muted">Sudah 1x</small>
                                    <?php else: ?>
                                        <small class="text-muted">Belum H-1</small>
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
                                    Tidak ada peminjaman yang perlu diperpanjang
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIWAYAT PERPANJANGAN -->
        <div class="card mt-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem;">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Perpanjangan</h5>
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
                            $no = 1;
                            if ($result_riwayat->num_rows > 0):
                                while ($row = $result_riwayat->fetch_assoc()):
                                    // Get nama peminjam
                                    $nama_peminjam = getNamaPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
                                    $identifier = getIdentifierPeminjam($conn, $row['jenis_peminjam'], $row['peminjam_id']);
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