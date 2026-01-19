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
    <title>Laporan - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-file-bar-graph me-2"></i>Laporan</h1>
                </div>

                <?php showAlert(); ?>

                <!-- Laporan Daftar -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Laporan Daftar</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="daftar_peminjaman.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-arrow-left-right me-2"></i>Daftar Peminjaman Berjalan
                                </a>
                                <a href="daftar_keterlambatan.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-exclamation-triangle me-2"></i>Daftar Keterlambatan
                                </a>
                                <a href="daftar_perpanjangan.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Daftar Perpanjangan Buku
                                </a>
                                <a href="daftar_pengembalian.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-arrow-return-left me-2"></i>Daftar Pengembalian
                                </a>
                                <a href="daftar_buku_habis.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-inbox me-2"></i>Daftar Buku Stok Habis
                                </a>
                                <a href="daftar_mahasiswa.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-people me-2"></i>Daftar Mahasiswa
                                </a>
                                <a href="daftar_dosen.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-person-badge me-2"></i>Daftar Dosen
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Laporan Bulanan</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="bulanan.php" class="mb-3">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Bulan</label>
                                        <select class="form-select" name="bulan" required>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= $i ?>" <?= date('n') == $i ? 'selected' : '' ?>>
                                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Tahun</label>
                                        <select class="form-select" name="tahun" required>
                                            <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Format Laporan</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="format" value="pdf" id="pdf" checked>
                                            <label class="form-check-label" for="pdf">
                                                <i class="bi bi-file-pdf text-danger"></i> PDF
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="format" value="excel" id="excel">
                                            <label class="form-check-label" for="excel">
                                                <i class="bi bi-file-excel text-success"></i> Excel
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-download me-2"></i>Generate Laporan
                                    </button>
                                </form>
                                
                                <div class="alert alert-info mb-0">
                                    <small>
                                        <strong>Isi Laporan Bulanan:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Total peminjaman & pengembalian</li>
                                            <li>Total keterlambatan & denda</li>
                                            <li>Mahasiswa peminjam aktif</li>
                                            <li>Mahasiswa dengan keterlambatan</li>
                                            <li>Mahasiswa paling sering pinjam</li>
                                            <li>Buku terpopuler dipinjam</li>
                                        </ul>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
