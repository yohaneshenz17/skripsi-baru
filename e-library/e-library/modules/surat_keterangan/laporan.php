<?php
/**
 * Halaman Laporan Surat Keterangan
 * Style: Consistent with Peminjaman Index
 */

require_once 'config.php';
cekLoginAdmin();

// Definisi BASE_URL jika belum ada (untuk navigasi sidebar)
if (!defined('BASE_URL')) {
    define('BASE_URL', '../../');
}

$pageTitle = "Laporan Surat Keterangan";
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Ambil data untuk filter tahun
$tahun_list = [];
$sql_tahun = "SELECT DISTINCT tahun_periode FROM surat_keterangan ORDER BY tahun_periode DESC";
$result_tahun = $conn->query($sql_tahun);
while ($row = $result_tahun->fetch_assoc()) {
    $tahun_list[] = $row['tahun_periode'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - E-Library STK Yakobus</title>
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
            border-left: 4px solid #f093fb;
        }
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card-body { padding: 1.5rem; }
        
        /* Stats Card Styling */
        .stats-card {
            color: white;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .stats-card:hover { transform: translateY(-5px); }
        .stats-card .card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-card h2 { font-weight: 700; margin: 0; font-size: 2rem; }
        .stats-card p { margin: 0; opacity: 0.9; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .stats-card i { font-size: 3rem; opacity: 0.3; }
        
        /* Table */
        .table { font-size: 0.9rem; }
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
        .table tbody tr:hover {
            background: rgba(240, 147, 251, 0.05);
        }
        .table tbody td {
            vertical-align: middle;
            padding: 0.9rem 0.75rem;
        }
        
        /* Badge & Buttons */
        .badge { padding: 0.4rem 0.7rem; font-weight: 500; border-radius: 6px; }
        .btn { border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; }
        
        /* Filter Box */
        .filter-box {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
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
                            <?= $admin_username ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
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
                <a class="nav-link active" href="index.php">
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
            <h1><i class="bi bi-file-earmark-bar-graph me-2"></i><?= $pageTitle ?></h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="filter-box">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Tahun</label>
                    <select id="filter_tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        <?php foreach ($tahun_list as $tahun): ?>
                            <option value="<?php echo $tahun; ?>" <?php echo ($tahun == date('Y')) ? 'selected' : ''; ?>>
                                <?php echo $tahun; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Bulan</label>
                    <select id="filter_bulan" class="form-select">
                        <option value="">Semua Bulan</option>
                        <?php 
                        global $BULAN_INDONESIA;
                        foreach ($BULAN_INDONESIA as $key => $bulan): 
                        ?>
                            <option value="<?php echo $key; ?>"><?php echo $bulan; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Jenis Surat</label>
                    <select id="filter_jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="UAS">UAS</option>
                        <option value="PPA">PPA</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-primary w-100" onclick="loadLaporan()">
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-success w-100" onclick="exportExcel()">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stats-card bg-info">
                    <div class="card-body">
                        <div>
                            <p>Total Surat</p>
                            <h2 id="stat_total">0</h2>
                        </div>
                        <i class="bi bi-files"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-primary">
                    <div class="card-body">
                        <div>
                            <p>Surat UAS</p>
                            <h2 id="stat_uas">0</h2>
                        </div>
                        <i class="bi bi-pencil-square"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-success">
                    <div class="card-body">
                        <div>
                            <p>Surat PPA</p>
                            <h2 id="stat_ppa">0</h2>
                        </div>
                        <i class="bi bi-mortarboard"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div>
                            <p>Override Tunggakan</p>
                            <h2 id="stat_override">0</h2>
                        </div>
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title fw-bold text-secondary mb-0">Detail Laporan</h5>
                    <div class="d-flex gap-3 small text-muted">
                        <span class="d-flex align-items-center"><div style="width:12px;height:12px;background:#fff3cd;border:1px solid #ffecb5;margin-right:5px;border-radius:2px;"></div> Override (Perlu Review)</span>
                        <span class="d-flex align-items-center"><div style="width:12px;height:12px;background:#f8d7da;border:1px solid #f5c6cb;margin-right:5px;border-radius:2px;"></div> Dibatalkan</span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableLaporan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nomor Surat</th>
                                <th>Tanggal</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Program Studi</th>
                                <th>Jenis</th>
                                <th>Admin</th>
                                <th>Status</th>
                                <th>Catatan Audit</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-laporan">
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="spinner-border text-primary mb-2" role="status"></div>
                                    <p class="mb-0">Memuat data laporan...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        loadLaporan();
    });

    function loadLaporan() {
        const tahun = $('#filter_tahun').val();
        const bulan = $('#filter_bulan').val();
        const jenis = $('#filter_jenis').val();
        
        // Show loading state
        $('#tbody-laporan').html('<tr><td colspan="10" class="text-center py-5 text-muted"><div class="spinner-border text-primary mb-2" role="status"></div><p class="mb-0">Sedang memuat data...</p></td></tr>');
        
        $.ajax({
            url: 'ajax_laporan.php',
            type: 'POST',
            data: {
                action: 'load_laporan',
                tahun: tahun,
                bulan: bulan,
                jenis: jenis
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update statistik
                    $('#stat_total').text(response.statistik.total);
                    $('#stat_uas').text(response.statistik.uas);
                    $('#stat_ppa').text(response.statistik.ppa);
                    $('#stat_override').text(response.statistik.override);
                    
                    // Update tabel
                    $('#tbody-laporan').html(response.html);
                } else {
                    $('#tbody-laporan').html('<tr><td colspan="10" class="text-center text-danger py-4">' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                $('#tbody-laporan').html('<tr><td colspan="10" class="text-center text-danger py-4"><i class="bi bi-exclamation-circle me-2"></i>Gagal memuat laporan. Terjadi kesalahan server.</td></tr>');
            }
        });
    }

    function exportExcel() {
        const tahun = $('#filter_tahun').val();
        const bulan = $('#filter_bulan').val();
        const jenis = $('#filter_jenis').val();
        
        const params = new URLSearchParams({
            action: 'export_excel',
            tahun: tahun,
            bulan: bulan,
            jenis: jenis
        });
        
        window.open('ajax_laporan.php?' + params.toString(), '_blank');
    }
    </script>
</body>
</html>