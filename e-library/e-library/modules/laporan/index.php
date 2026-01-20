<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perpustakaan - E-Library STK Yakobus</title>
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
        
        /* Page Header */
        .page-header { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #f093fb; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
        
        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
        .card-header { padding: 1rem 1.5rem; font-weight: 600; color: white; border-radius: 12px 12px 0 0 !important; }
        
        /* List Group Custom */
        .list-group-item { border-left: none; border-right: none; padding: 1rem 1.25rem; color: #475569; font-weight: 500; }
        .list-group-item:first-child { border-top: none; }
        .list-group-item i { color: #667eea; font-size: 1.1rem; }
        .list-group-item:hover { background-color: #f8f9fa; color: #667eea; }
        
        /* Info Box */
        .info-box { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1rem; border-radius: 8px; margin-top: 1rem; }
        
        /* Custom Gradients */
        .bg-gradient-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
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
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php"><i class="bi bi-arrow-return-left"></i>Pengembalian Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php"><i class="bi bi-cash-stack"></i>Manajemen Denda</a></li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php"><i class="bi bi-file-earmark-text"></i>Surat Keterangan</a></li>
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>modules/laporan/index.php"><i class="bi bi-file-bar-graph"></i>Laporan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php"><i class="bi bi-cloud-download"></i>Backup Database</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="bi bi-file-bar-graph me-2"></i>Pusat Laporan</h1>
        </div>

        <?php showAlert(); ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-gradient-green">
                        <h5 class="mb-0"><i class="bi bi-list-stars me-2"></i>Laporan Daftar & Arsip</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="daftar_peminjaman.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-arrow-left-right me-2"></i>Daftar Peminjaman Berjalan</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_keterlambatan.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-exclamation-triangle me-2"></i>Daftar Keterlambatan</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_perpanjangan.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-arrow-clockwise me-2"></i>Daftar Perpanjangan Buku</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_pengembalian.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-arrow-return-left me-2"></i>Daftar Pengembalian</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_buku_habis.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-inbox me-2"></i>Daftar Buku Stok Habis</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_mahasiswa.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-people me-2"></i>Daftar Mahasiswa</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                        <a href="daftar_dosen.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-badge me-2"></i>Daftar Dosen</span>
                            <i class="bi bi-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-gradient-purple">
                        <h5 class="mb-0"><i class="bi bi-printer me-2"></i>Cetak Laporan Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="cetak_laporan.php" target="_blank">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Periode Laporan</label>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <select class="form-select" name="bulan" required>
                                            <option value="01" <?= date('m') == '01' ? 'selected' : '' ?>>Januari</option>
                                            <option value="02" <?= date('m') == '02' ? 'selected' : '' ?>>Februari</option>
                                            <option value="03" <?= date('m') == '03' ? 'selected' : '' ?>>Maret</option>
                                            <option value="04" <?= date('m') == '04' ? 'selected' : '' ?>>April</option>
                                            <option value="05" <?= date('m') == '05' ? 'selected' : '' ?>>Mei</option>
                                            <option value="06" <?= date('m') == '06' ? 'selected' : '' ?>>Juni</option>
                                            <option value="07" <?= date('m') == '07' ? 'selected' : '' ?>>Juli</option>
                                            <option value="08" <?= date('m') == '08' ? 'selected' : '' ?>>Agustus</option>
                                            <option value="09" <?= date('m') == '09' ? 'selected' : '' ?>>September</option>
                                            <option value="10" <?= date('m') == '10' ? 'selected' : '' ?>>Oktober</option>
                                            <option value="11" <?= date('m') == '11' ? 'selected' : '' ?>>November</option>
                                            <option value="12" <?= date('m') == '12' ? 'selected' : '' ?>>Desember</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="tahun" required>
                                            <?php 
                                            $tahun_sekarang = date('Y');
                                            for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 5; $i--) {
                                                echo "<option value='$i'>$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Format File</label>
                                <div class="p-3 border rounded bg-light">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="format" value="PDF" id="pdf" checked>
                                        <label class="form-check-label" for="pdf">
                                            <i class="bi bi-file-pdf text-danger fs-5 me-1 align-middle"></i> PDF Document
                                        </label>
                                    </div>
                                    </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-cloud-download me-2"></i>GENERATE & DOWNLOAD LAPORAN
                            </button>
                        </form>
                        
                        <div class="info-box">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Cakupan Laporan Bulanan:</h6>
                                    <ul class="mb-0 ps-3 small" style="opacity: 0.9;">
                                        <li>Statistik Aset & Anggota</li>
                                        <li>Rekap Peminjaman & Pengembalian</li>
                                        <li>Daftar Keterlambatan & Denda</li>
                                        <li>Analisis Buku Terpopuler & User Aktif</li>
                                        <li>Rekap Surat Keterangan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>