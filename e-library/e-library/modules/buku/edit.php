<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get book data
$query = "SELECT id, nomor_buku, judul, pengarang, penerbit, tahun_terbit, stok, stok_tersedia, is_referensi FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($buku_id, $nomor_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok, $stok_tersedia, $is_referensi);

if (!$stmt->fetch()) {
    $stmt->close();
    setAlert('danger', 'Buku tidak ditemukan!');
    header('Location: index.php');
    exit;
}
$stmt->close();

$buku = [
    'id' => $buku_id,
    'nomor_buku' => $nomor_buku,
    'judul' => $judul_buku,
    'pengarang' => $pengarang,
    'penerbit' => $penerbit,
    'tahun_terbit' => $tahun_terbit,
    'stok' => $stok,
    'stok_tersedia' => $stok_tersedia,
    'is_referensi' => $is_referensi
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_buku = sanitize($_POST['nomor_buku']);
    $judul = sanitize($_POST['judul']);
    $pengarang = sanitize($_POST['pengarang']);
    $penerbit = sanitize($_POST['penerbit']);
    $tahun_terbit = sanitize($_POST['tahun_terbit']);
    $stok = intval($_POST['stok']);
    $is_referensi = isset($_POST['is_referensi']) ? 1 : 0;
    
    // Hitung stok_tersedia
    $stok_dipinjam = $buku['stok'] - $buku['stok_tersedia'];
    $stok_tersedia = $stok - $stok_dipinjam;
    
    if ($stok_tersedia < 0) {
        setAlert('danger', 'Stok tidak boleh kurang dari jumlah buku yang sedang dipinjam (' . $stok_dipinjam . ')');
    } else {
        $query = "UPDATE buku SET nomor_buku = ?, judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, stok = ?, stok_tersedia = ?, is_referensi = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssiiii", $nomor_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $stok, $stok_tersedia, $is_referensi, $id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Buku berhasil diupdate!');
            header('Location: index.php');
            exit;
        } else {
            setAlert('danger', 'Gagal mengupdate buku!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-pencil me-2"></i>Edit Buku</h1>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Buku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nomor_buku" value="<?= $buku['nomor_buku'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="judul" value="<?= $buku['judul'] ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pengarang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="pengarang" value="<?= $buku['pengarang'] ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Penerbit</label>
                                        <input type="text" class="form-control" name="penerbit" value="<?= $buku['penerbit'] ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tahun Terbit</label>
                                        <input type="number" class="form-control" name="tahun_terbit" value="<?= $buku['tahun_terbit'] ?>" min="1900" max="<?= date('Y') ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="stok" value="<?= $buku['stok'] ?>" min="<?= $buku['stok'] - $buku['stok_tersedia'] ?>" required>
                                        <small class="text-muted">Stok tersedia saat ini: <?= $buku['stok_tersedia'] ?>, Sedang dipinjam: <?= $buku['stok'] - $buku['stok_tersedia'] ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="card bg-light border-warning">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_referensi" id="is_referensi" value="1" <?= $buku['is_referensi'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_referensi">
                                                        <i class="bi bi-bookmark-check text-warning"></i> <strong>Buku Referensi</strong>
                                                    </label>
                                                    <div><small class="text-muted">Centang jika buku ini <strong>hanya boleh dibaca di ruang baca</strong> dan <strong>tidak dapat dipinjam</strong></small></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>