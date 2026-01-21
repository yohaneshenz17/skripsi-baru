<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get book data
$query = "SELECT id, nomor_buku, judul, pengarang, penerbit, tahun_terbit, stok, stok_tersedia FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($buku_id, $nomor_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok, $stok_tersedia);

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
    'stok_tersedia' => $stok_tersedia
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_buku = sanitize($_POST['nomor_buku']);
    $judul = sanitize($_POST['judul']);
    $pengarang = sanitize($_POST['pengarang']);
    $penerbit = sanitize($_POST['penerbit']);
    $tahun_terbit = sanitize($_POST['tahun_terbit']);
    $stok = intval($_POST['stok']);
    
    // Hitung stok_tersedia
    $stok_dipinjam = $buku['stok'] - $buku['stok_tersedia'];
    $stok_tersedia = $stok - $stok_dipinjam;
    
    if ($stok_tersedia < 0) {
        setAlert('danger', 'Stok tidak boleh kurang dari jumlah buku yang sedang dipinjam (' . $stok_dipinjam . ')');
    } else {
        $query = "UPDATE buku SET nomor_buku = ?, judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, stok = ?, stok_tersedia = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssiii", $nomor_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $stok, $stok_tersedia, $id);
        
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
    <title>Data Buku - E-Library STK Yakobus</title>
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
            border-left: 4px solid #667eea;
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
            background: rgba(102, 126, 234, 0.05);
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
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
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
        
        /* Search Box */
        .search-box {
            max-width: 400px;
        }
        .search-box .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }
        .search-box .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .search-box .btn {
            border-radius: 8px;
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
    </style>
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
