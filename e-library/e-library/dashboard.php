<?php
// =========================================================
// DASHBOARD SISTEM INFORMASI PERPUSTAKAAN
// STK SANTO YAKOBUS MERAUKE
// =========================================================

// 1. CONFIG & SESSION
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek Login & Update Status Otomatis
requireLogin();
updateStatusPeminjaman($conn); // Auto-update status terlambat

$admin_username = $_SESSION['admin_username'] ?? 'Administrator';

// 2. LOGIKA STATISTIK & DATA
// ---------------------------------------------------------

// A. Statistik Utama (Card Atas)
$stats = [
    'total_buku' => $conn->query("SELECT SUM(stok) FROM buku")->fetch_row()[0] ?? 0,
    'total_judul' => $conn->query("SELECT COUNT(*) FROM buku")->fetch_row()[0] ?? 0,
    'total_anggota' => $conn->query("SELECT (SELECT COUNT(*) FROM mahasiswa) + (SELECT COUNT(*) FROM dosen)")->fetch_row()[0] ?? 0,
    'sedang_dipinjam' => $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status IN ('dipinjam', 'diperpanjang', 'terlambat')")->fetch_row()[0] ?? 0,
    'terlambat' => $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'terlambat'")->fetch_row()[0] ?? 0
];

// B. Hitung Potensi Denda Berjalan (Realtime)
// Menghitung denda dari buku yang belum kembali dan sudah lewat jatuh tempo
$today = date('Y-m-d');
$q_denda = "SELECT tanggal_jatuh_tempo FROM peminjaman 
            WHERE tanggal_jatuh_tempo < '$today' 
            AND tanggal_kembali IS NULL 
            AND status NOT IN ('dikembalikan', 'selesai')";
$res_denda = $conn->query($q_denda);
$total_estimasi_denda = 0;
while($r = $res_denda->fetch_assoc()){
    $jt = new DateTime($r['tanggal_jatuh_tempo']);
    $now = new DateTime($today);
    $hari = $jt->diff($now)->days;
    $total_estimasi_denda += ($hari * 1000); // Rp 1.000/hari
}

// C. Data Grafik (6 Bulan Terakhir)
$chart_labels = [];
$chart_pinjam = [];
$chart_kembali = [];

for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime("-$i months"));
    $y = date('Y', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    
    $chart_labels[] = $label;
    $chart_pinjam[] = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE MONTH(tanggal_pinjam) = '$m' AND YEAR(tanggal_pinjam) = '$y'")->fetch_row()[0];
    $chart_kembali[] = $conn->query("SELECT COUNT(*) FROM pengembalian WHERE MONTH(tanggal_kembali) = '$m' AND YEAR(tanggal_kembali) = '$y'")->fetch_row()[0];
}

// D. Peminjaman Terakhir (Tabel Bawah)
$q_recent = "SELECT p.*, b.judul, 
             CASE WHEN p.jenis_peminjam='mahasiswa' THEN m.nama ELSE d.nama END as peminjam
             FROM peminjaman p
             JOIN buku b ON p.buku_id = b.id
             LEFT JOIN mahasiswa m ON p.peminjam_id=m.id AND p.jenis_peminjam='mahasiswa'
             LEFT JOIN dosen d ON p.peminjam_id=d.id AND p.jenis_peminjam='dosen'
             ORDER BY p.id DESC LIMIT 5";
$res_recent = $conn->query($q_recent);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Eksekutif - E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* --- 1. GLOBAL STYLES --- */
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f3f4f6; overflow-x: hidden; }
        
        /* --- 2. NAVBAR & SIDEBAR (FIXED) --- */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.8rem 1.5rem; box-shadow: 0 2px 15px rgba(0,0,0,0.1); z-index: 1040; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; font-size: 1.4rem; }
        
        .sidebar { position: fixed; top: 60px; bottom: 0; left: 0; width: 260px; background: #ffffff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); overflow-y: auto; z-index: 1000; padding-top: 1rem; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #ddd; border-radius: 5px; }
        
        .nav-link { color: #555; padding: 0.8rem 1.5rem; font-weight: 500; transition: all 0.3s; display: flex; align-items: center; }
        .nav-link:hover { color: #667eea; background: #f8f9fa; padding-left: 1.8rem; }
        .nav-link.active { color: #667eea; background: linear-gradient(90deg, rgba(102,126,234,0.1) 0%, rgba(255,255,255,0) 100%); border-left: 4px solid #667eea; font-weight: 600; }
        .nav-link i { width: 25px; font-size: 1.2rem; margin-right: 10px; }
        .sidebar-heading { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; padding: 1.5rem 1.5rem 0.5rem; font-weight: 700; }

        /* --- 3. MAIN CONTENT --- */
        .main-content { margin-left: 260px; margin-top: 60px; padding: 2rem; min-height: 100vh; }
        
        /* --- 4. DASHBOARD CARDS (Infografis) --- */
        .stat-card { background: #fff; border-radius: 15px; padding: 1.5rem; box-shadow: 0 5px 20px rgba(0,0,0,0.05); position: relative; overflow: hidden; transition: transform 0.3s; border: none; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        
        .stat-card.primary { border-bottom: 4px solid #667eea; }
        .stat-card.success { border-bottom: 4px solid #11998e; }
        .stat-card.warning { border-bottom: 4px solid #f6d365; }
        .stat-card.danger { border-bottom: 4px solid #ff6b6b; }
        
        .stat-icon-bg { position: absolute; top: -10px; right: -10px; font-size: 5rem; opacity: 0.1; transform: rotate(15deg); }
        .stat-value { font-size: 2.2rem; font-weight: 800; color: #333; margin-bottom: 0; line-height: 1.2; }
        .stat-label { color: #888; font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* --- 5. WELCOME BANNER --- */
        .welcome-banner { background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%); border-radius: 15px; padding: 2rem; color: #1a5c48; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(132, 250, 176, 0.4); position: relative; overflow: hidden; }
        .welcome-deco { position: absolute; right: 20px; bottom: -20px; font-size: 8rem; opacity: 0.2; color: #fff; }
        
        /* --- 6. QUICK ACTIONS --- */
        .quick-btn { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 1.2rem; text-align: center; transition: all 0.3s; height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: #555; }
        .quick-btn:hover { background: #fff; border-color: #667eea; box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2); transform: translateY(-3px); color: #667eea; }
        .quick-btn i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
        
        /* --- 7. TABLE STYLE --- */
        .custom-table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #6c757d; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
        .badge-status { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar { left: -260px; transition: 0.3s; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-journal-bookmark-fill me-2"></i>E-Library STK St. Yakobus Merauke
            </a>
            <button class="navbar-toggler" type="button" onclick="document.querySelector('.sidebar').classList.toggle('show')">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="ms-auto d-flex align-items-center text-white">
                <div class="me-4 d-none d-md-block text-end">
                    <div id="jam" class="fw-bold fs-5"></div>
                    <small class="opacity-75"><?= date('l, d F Y') ?></small>
                </div>
                <div class="dropdown">
                    <a class="nav-link text-white dropdown-toggle p-0" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_username) ?>&background=random" class="rounded-circle border border-2 border-white" width="40">
                        <span class="ms-2 d-none d-md-inline"><?= $admin_username ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <nav class="sidebar">
        <div class="px-3 mb-3">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Main Menu</small>
        </div>
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
        </ul>

        <div class="sidebar-heading mt-0">Master Data</div>
        <ul class="nav flex-column mb-4">
            <li class="nav-item"><a class="nav-link" href="modules/buku/index.php"><i class="bi bi-book"></i> Data Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/mahasiswa/index.php"><i class="bi bi-people"></i> Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/dosen/index.php"><i class="bi bi-person-badge"></i> Dosen</a></li>
        </ul>

        <div class="sidebar-heading mt-0">Sirkulasi</div>
        <ul class="nav flex-column mb-4">
            <li class="nav-item"><a class="nav-link" href="modules/peminjaman/index.php"><i class="bi bi-box-arrow-up-right"></i> Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/pengembalian/index.php"><i class="bi bi-box-arrow-in-down-left"></i> Pengembalian</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/perpanjangan/index.php"><i class="bi bi-arrow-repeat"></i> Perpanjangan</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/denda/index.php"><i class="bi bi-wallet2"></i> Manajemen Denda</a></li>
        </ul>

        <div class="sidebar-heading mt-0">Laporan & Tools</div>
        <ul class="nav flex-column mb-5">
            <li class="nav-item"><a class="nav-link" href="modules/surat_keterangan/index.php"><i class="bi bi-file-earmark-check"></i> Surat Keterangan</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/laporan/index.php"><i class="bi bi-printer"></i> Laporan</a></li>
            <li class="nav-item"><a class="nav-link" href="modules/backup/index.php"><i class="bi bi-database-gear"></i> Backup & Restore</a></li>
        </ul>
    </nav>

    <main class="main-content">
        
        <div class="welcome-banner">
            <h2 class="fw-bold mb-1">Selamat Datang, Administrator!</h2>
            <p class="mb-0 fs-5 opacity-75">Sistem Informasi Perpustakaan siap melayani.</p>
            <i class="bi bi-book welcome-deco"></i>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card primary">
                    <div class="stat-label">Koleksi Buku</div>
                    <div class="stat-value"><?= number_format($stats['total_judul']) ?> <small class="fs-6 text-muted fw-normal">Judul</small></div>
                    <div class="text-muted small mt-1">Total <?= number_format($stats['total_buku']) ?> Eksemplar</div>
                    <i class="bi bi-journal-bookmark-fill stat-icon-bg text-primary"></i>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="stat-card success">
                    <div class="stat-label">Total Anggota</div>
                    <div class="stat-value"><?= number_format($stats['total_anggota']) ?> <small class="fs-6 text-muted fw-normal">Orang</small></div>
                    <div class="text-muted small mt-1">Mahasiswa & Dosen Aktif</div>
                    <i class="bi bi-people-fill stat-icon-bg text-success"></i>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card warning">
                    <div class="stat-label">Sirkulasi Aktif</div>
                    <div class="stat-value text-warning"><?= number_format($stats['sedang_dipinjam']) ?> <small class="fs-6 text-muted fw-normal">Buku</small></div>
                    <div class="text-muted small mt-1">Sedang dipinjam saat ini</div>
                    <i class="bi bi-arrow-left-right stat-icon-bg text-warning"></i>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card danger">
                    <div class="stat-label">Keterlambatan</div>
                    <div class="stat-value text-danger"><?= number_format($stats['terlambat']) ?> <small class="fs-6 text-muted fw-normal">Buku</small></div>
                    <div class="text-muted small mt-1">Belum dikembalikan</div>
                    <i class="bi bi-exclamation-circle-fill stat-icon-bg text-danger"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Tren Peminjaman (6 Bulan Terakhir)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="transaksiChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-danger text-white position-relative overflow-hidden">
                    <div class="card-body p-4 text-center">
                        <h6 class="text-uppercase opacity-75 fw-bold mb-2">Potensi Denda Berjalan</h6>
                        <h2 class="display-5 fw-bold mb-0">Rp <?= number_format($total_estimasi_denda, 0, ',', '.') ?></h2>
                        <p class="small opacity-75 mt-2 mb-3">Estimasi dari buku yang terlambat tapi belum kembali.</p>
                        <a href="modules/denda/index.php" class="btn btn-light rounded-pill px-4 fw-bold text-danger stretched-link">Lihat Detail</a>
                        <i class="bi bi-cash-coin position-absolute" style="font-size: 8rem; opacity: 0.1; right: -20px; bottom: -20px;"></i>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">Aksi Cepat</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="modules/peminjaman/add.php" class="quick-btn">
                                    <i class="bi bi-plus-circle-fill text-primary"></i>
                                    <span class="fw-bold small">Pinjam Baru</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="modules/pengembalian/index.php" class="quick-btn">
                                    <i class="bi bi-box-arrow-in-down text-success"></i>
                                    <span class="fw-bold small">Kembali Buku</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="modules/surat_keterangan/index.php" class="quick-btn">
                                    <i class="bi bi-file-earmark-text text-info"></i>
                                    <span class="fw-bold small">Surat Bebas</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="modules/laporan/index.php" class="quick-btn">
                                    <i class="bi bi-file-pdf text-danger"></i>
                                    <span class="fw-bold small">Laporan</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-secondary"></i>Aktivitas Peminjaman Terbaru</h5>
                <a href="modules/peminjaman/index.php" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res_recent->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td class="fw-bold text-dark"><?= $row['peminjam'] ?> <br> <span class="badge bg-light text-secondary fw-normal"><?= ucfirst($row['jenis_peminjam']) ?></span></td>
                            <td><?= substr($row['judul'], 0, 40) ?>...</td>
                            <td class="text-muted small"><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                            <td class="text-center">
                                <?php 
                                    $st = $row['status'];
                                    $badge = 'secondary';
                                    if($st == 'dipinjam') $badge = 'primary';
                                    if($st == 'terlambat') $badge = 'danger';
                                    if($st == 'dikembalikan') $badge = 'success';
                                ?>
                                <span class="badge bg-<?= $badge ?> badge-status"><?= strtoupper($st) ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Clock Script
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace('.', ':');
            document.getElementById('jam').textContent = timeString + " WIT";
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. Chart Script
        const ctx = document.getElementById('transaksiChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Peminjaman',
                    data: <?= json_encode($chart_pinjam) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4
                }, {
                    label: 'Pengembalian',
                    data: <?= json_encode($chart_kembali) ?>,
                    borderColor: '#11998e',
                    backgroundColor: 'rgba(17, 153, 142, 0.05)',
                    tension: 0.4,
                    fill: true,
                    borderDash: [5, 5],
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>