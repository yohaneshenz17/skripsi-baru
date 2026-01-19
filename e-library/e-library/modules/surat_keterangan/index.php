<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Get all surat keterangan
$query = "SELECT * FROM surat_keterangan ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-file-earmark-text me-2"></i>Surat Keterangan Bebas Perpustakaan</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Buat Surat Baru
                        </a>
                    </div>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Nomor Surat</th>
                                        <th width="10%">NIM</th>
                                        <th width="25%">Nama</th>
                                        <th width="20%">Program Studi</th>
                                        <th width="15%">Keperluan</th>
                                        <th width="10%">Tanggal</th>
                                        <th width="10%">Aksi</th>
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
                                        <td><?= $row['nomor_surat'] ?></td>
                                        <td><?= $row['nim'] ?></td>
                                        <td><?= $row['nama'] ?></td>
                                        <td><?= $row['program_studi'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['keperluan'] == 'ujian_akhir' ? 'info' : 'success' ?>">
                                                <?= str_replace('_', ' ', ucwords($row['keperluan'])) ?>
                                            </span>
                                        </td>
                                        <td><?= formatTanggalIndo($row['tanggal_terbit']) ?></td>
                                        <td class="action-buttons">
                                            <a href="view_pdf.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada surat keterangan</td>
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
