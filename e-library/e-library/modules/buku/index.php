<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Get all books with search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE nomor_buku LIKE '%$search%' OR judul LIKE '%$search%' OR pengarang LIKE '%$search%'";
}

$query = "SELECT * FROM buku $where ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-book me-2"></i>Data Buku</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="import.php" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-excel me-2"></i>Import Excel
                        </a>
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Buku
                        </a>
                    </div>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="">
                                    <div class="input-group search-box">
                                        <input type="text" class="form-control" name="search" placeholder="Cari buku..." value="<?= $search ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Nomor Buku</th>
                                        <th width="25%">Judul</th>
                                        <th width="15%">Pengarang</th>
                                        <th width="15%">Penerbit</th>
                                        <th width="8%">Tahun</th>
                                        <th width="8%">Stok</th>
                                        <th width="8%">Tersedia</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $row['nomor_buku'] ?></td>
                                        <td><?= $row['judul'] ?></td>
                                        <td><?= $row['pengarang'] ?></td>
                                        <td><?= $row['penerbit'] ?></td>
                                        <td><?= $row['tahun_terbit'] ?></td>
                                        <td><span class="badge bg-primary"><?= $row['stok'] ?></span></td>
                                        <td>
                                            <span class="badge bg-<?= $row['stok_tersedia'] > 0 ? 'success' : 'danger' ?>">
                                                <?= $row['stok_tersedia'] ?>
                                            </span>
                                        </td>
                                        <td class="action-buttons">
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data buku</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
