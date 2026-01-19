<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_buku = sanitize($_POST['nomor_buku']);
    $judul = sanitize($_POST['judul']);
    $pengarang = sanitize($_POST['pengarang']);
    $penerbit = sanitize($_POST['penerbit']);
    $tahun_terbit = sanitize($_POST['tahun_terbit']);
    $stok = intval($_POST['stok']);
    
    // Check if nomor_buku already exists - FIXED VERSION
    $check = "SELECT id FROM buku WHERE nomor_buku = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $nomor_buku);
    $stmt->execute();
    $stmt->bind_result($found_id);
    
    if ($stmt->fetch()) {
        // Nomor buku sudah ada
        $stmt->close();
        setAlert('danger', 'Nomor buku sudah digunakan!');
    } else {
        $stmt->close();
        
        // Insert new book
        $query = "INSERT INTO buku (nomor_buku, judul, pengarang, penerbit, tahun_terbit, stok, stok_tersedia) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssii", $nomor_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $stok, $stok);
        
        if ($stmt->execute()) {
            $stmt->close();
            setAlert('success', 'Buku berhasil ditambahkan!');
            header('Location: index.php');
            exit;
        } else {
            $stmt->close();
            setAlert('danger', 'Gagal menambahkan buku!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-plus-circle me-2"></i>Tambah Buku</h1>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Buku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nomor_buku" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Judul Buku <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="judul" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pengarang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="pengarang" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Penerbit</label>
                                        <input type="text" class="form-control" name="penerbit">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tahun Terbit</label>
                                        <input type="number" class="form-control" name="tahun_terbit" min="1900" max="<?= date('Y') ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="stok" min="0" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan
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