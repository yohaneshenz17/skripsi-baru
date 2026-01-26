<?php
// Hubungkan ke database
require_once 'config/database.php';

// Helper Tanggal Indo Sederhana
function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
		'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
	);
	$pecahkan = explode('-', $tanggal);
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// Inisialisasi Variabel
$active_tab = 'buku'; // Default tab
$hasil_buku = [];
$data_peminjam = null;
$riwayat_pinjam = [];
$keyword = '';
$nomor_induk = '';

// --- LOGIKA PENCARIAN BUKU ---
if (isset($_GET['q'])) {
    $active_tab = 'buku';
    $keyword = $conn->real_escape_string($_GET['q']);
    if (!empty($keyword)) {
        $sql = "SELECT * FROM buku 
                WHERE judul LIKE '%$keyword%' 
                OR pengarang LIKE '%$keyword%' 
                OR penerbit LIKE '%$keyword%'
                OR nomor_buku LIKE '%$keyword%'
                ORDER BY judul ASC LIMIT 50";
        $query = $conn->query($sql);
        while ($row = $query->fetch_assoc()) {
            $hasil_buku[] = $row;
        }
    }
}

// --- LOGIKA CEK STATUS PEMINJAMAN ---
if (isset($_GET['cek_id'])) {
    $active_tab = 'peminjam';
    $nomor_induk = $conn->real_escape_string($_GET['cek_id']);
    
    // 1. Cek di tabel Mahasiswa
    $sql_mhs = "SELECT id, nama, 'mahasiswa' as tipe FROM mahasiswa WHERE nim = '$nomor_induk'";
    $q_mhs = $conn->query($sql_mhs);
    
    if ($q_mhs->num_rows > 0) {
        $data_peminjam = $q_mhs->fetch_assoc();
    } else {
        // 2. Cek di tabel Dosen jika bukan mahasiswa
        $sql_dsn = "SELECT id, nama, 'dosen' as tipe FROM dosen WHERE nuptk = '$nomor_induk'";
        $q_dsn = $conn->query($sql_dsn);
        if ($q_dsn->num_rows > 0) {
            $data_peminjam = $q_dsn->fetch_assoc();
        }
    }

    // 3. Jika data peminjam ditemukan, cari pinjamannya
    if ($data_peminjam) {
        $peminjam_id = $data_peminjam['id'];
        $tipe = $data_peminjam['tipe'];
        
        $sql_pinjam = "SELECT p.*, b.judul, b.nomor_buku 
                       FROM peminjaman p 
                       JOIN buku b ON p.buku_id = b.id 
                       WHERE p.peminjam_id = '$peminjam_id' 
                       AND p.jenis_peminjam = '$tipe' 
                       AND p.status IN ('dipinjam', 'diperpanjang', 'terlambat')
                       ORDER BY p.tanggal_pinjam DESC";
                       
        $q_pinjam = $conn->query($sql_pinjam);
        while ($row = $q_pinjam->fetch_assoc()) {
            $riwayat_pinjam[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPAC - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 1rem;
            margin-bottom: 2rem;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            background: transparent;
        }
        .book-card {
            transition: transform 0.2s;
            border: 1px solid #eee;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }
    </style>
</head>
<body>

    <div class="hero-section text-center">
        <div class="container">
            <img src="assets/images/stk.png" alt="Logo" height="60" class="mb-3 bg-white rounded-circle p-1">
            <h1 class="fw-bold">E-Library STK St. Yakobus Merauke</h1>
            <p class="lead opacity-75">Katalog Publik & Cek Status Peminjaman Mandiri</p>
        </div>
    </div>

    <div class="container mb-5" style="margin-top: -60px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card search-card">
                    <div class="card-header bg-white border-0 pt-3 px-4">
                        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= ($active_tab == 'buku') ? 'active' : '' ?>" id="buku-tab" data-bs-toggle="tab" data-bs-target="#buku-pane" type="button"><i class="bi bi-book me-2"></i>Cari Buku</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= ($active_tab == 'peminjam') ? 'active' : '' ?>" id="peminjam-tab" data-bs-toggle="tab" data-bs-target="#peminjam-pane" type="button"><i class="bi bi-person-badge me-2"></i>Cek Peminjaman Saya</button>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="card-body p-4 min-vh-50">
                        <div class="tab-content" id="myTabContent">
                            
                            <div class="tab-pane fade <?= ($active_tab == 'buku') ? 'show active' : '' ?>" id="buku-pane">
                                <form action="cari.php" method="GET" class="mb-4">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Ketik Judul, Pengarang, atau Nomor Buku..." value="<?= htmlspecialchars($keyword) ?>" autofocus>
                                        <button class="btn btn-primary px-4" type="submit">Cari</button>
                                    </div>
                                </form>

                                <?php if (isset($_GET['q'])): ?>
                                    <h5 class="mb-3 text-muted">Hasil pencarian untuk: "<strong><?= htmlspecialchars($keyword) ?></strong>"</h5>
                                    
                                    <?php if (count($hasil_buku) > 0): ?>
                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                                            <?php foreach ($hasil_buku as $buku): ?>
                                            <div class="col">
                                                <div class="card book-card p-3">
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0">
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 80px;">
                                                                <i class="bi bi-book text-secondary fs-1"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="fw-bold mb-1 text-primary"><?= $buku['judul'] ?></h6>
                                                            <p class="text-muted small mb-1"><i class="bi bi-person me-1"></i><?= $buku['pengarang'] ?></p>
                                                            <p class="text-muted small mb-2"><i class="bi bi-upc-scan me-1"></i><?= $buku['nomor_buku'] ?></p>
                                                            
                                                            <?php if ($buku['stok_tersedia'] > 0): ?>
                                                                <span class="badge bg-success status-badge">
                                                                    <i class="bi bi-check-circle me-1"></i>Tersedia (<?= $buku['stok_tersedia'] ?>)
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger status-badge">
                                                                    <i class="bi bi-x-circle me-1"></i>Stok Habis
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-emoji-frown fs-1"></i>
                                            <p class="mt-2">Buku tidak ditemukan. Coba kata kunci lain.</p>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted opacity-50">
                                        <i class="bi bi-journal-bookmark fs-1"></i>
                                        <p>Silakan ketik judul buku yang ingin Anda cari.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="tab-pane fade <?= ($active_tab == 'peminjam') ? 'show active' : '' ?>" id="peminjam-pane">
                                <form action="cari.php" method="GET" class="mb-4">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-heading text-muted"></i></span>
                                        <input type="text" name="cek_id" class="form-control bg-light border-start-0" placeholder="Masukkan NIM (Mahasiswa) atau NUPTK (Dosen)..." value="<?= htmlspecialchars($nomor_induk) ?>">
                                        <button class="btn btn-success px-4" type="submit">Cek Status</button>
                                    </div>
                                    <div class="form-text text-muted ps-2"><i class="bi bi-info-circle me-1"></i> Masukkan NIM Anda untuk melihat buku yang sedang dipinjam.</div>
                                </form>

                                <?php if (isset($_GET['cek_id'])): ?>
                                    <?php if ($data_peminjam): ?>
                                        <div class="alert alert-info border-0 shadow-sm mb-4">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 display-6 me-3">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                                    <h5 class="alert-heading fw-bold mb-0">Halo, <?= $data_peminjam['nama'] ?>!</h5>
                                                    <p class="mb-0 text-muted">Status: <?= ucfirst($data_peminjam['tipe']) ?></p>
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="fw-bold border-bottom pb-2 mb-3">Buku yang Sedang Dipinjam:</h6>
                                        
                                        <?php if (count($riwayat_pinjam) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Judul Buku</th>
                                                            <th>Tgl Pinjam</th>
                                                            <th>Jatuh Tempo</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($riwayat_pinjam as $pinjam): 
                                                            $jatuh_tempo = new DateTime($pinjam['tanggal_jatuh_tempo']);
                                                            $hari_ini = new DateTime();
                                                            $is_late = $hari_ini > $jatuh_tempo;
                                                            $diff = $hari_ini->diff($jatuh_tempo);
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <span class="fw-bold d-block"><?= $pinjam['judul'] ?></span>
                                                                <small class="text-muted"><?= $pinjam['nomor_buku'] ?></small>
                                                            </td>
                                                            <td><?= tgl_indo($pinjam['tanggal_pinjam']) ?></td>
                                                            <td>
                                                                <?= tgl_indo($pinjam['tanggal_jatuh_tempo']) ?>
                                                                <?php if ($is_late): ?>
                                                                    <div class="text-danger small fw-bold">Telat <?= $diff->days ?> Hari</div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($pinjam['status'] == 'terlambat' || $is_late): ?>
                                                                    <span class="badge bg-danger">Terlambat</span>
                                                                <?php elseif ($pinjam['status'] == 'diperpanjang'): ?>
                                                                    <span class="badge bg-info text-dark">Diperpanjang</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-primary">Dipinjam</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4 border rounded bg-light">
                                                <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                                <p class="mt-2 mb-0">Tidak ada buku yang sedang dipinjam.</p>
                                            </div>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                            <div>
                                                Data tidak ditemukan. Pastikan NIM yang Anda masukkan benar.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted small">
                    &copy; <?= date('Y') ?> E-Library STK Santo Yakobus Merauke. Made with Love by SIPD. <a href="index.php" class="text-decoration-none">Login Admin</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>