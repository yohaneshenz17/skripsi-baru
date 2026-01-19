<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Get all mahasiswa with search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE nim LIKE '%$search%' OR nama LIKE '%$search%' OR program_studi LIKE '%$search%'";
}

$query = "SELECT * FROM mahasiswa $where ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-people me-2"></i>Data Mahasiswa</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Mahasiswa
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
                                        <input type="text" class="form-control" name="search" placeholder="Cari mahasiswa..." value="<?= $search ?>">
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
                                        <th width="10%">NIM</th>
                                        <th width="25%">Nama</th>
                                        <th width="20%">Program Studi</th>
                                        <th width="10%">Angkatan</th>
                                        <th width="15%">No. HP</th>
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
                                        <td><span class="badge bg-primary"><?= $row['nim'] ?></span></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td><?= $row['program_studi'] ?></td>
                                        <td><?= $row['angkatan'] ?></td>
                                        <td><?= $row['no_hp'] ?? '-' ?></td>
                                        <td class="action-buttons">
                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus mahasiswa ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data mahasiswa</td>
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