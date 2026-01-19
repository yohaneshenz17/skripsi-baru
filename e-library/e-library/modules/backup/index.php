<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

if (isset($_GET['backup'])) {
    $filename = 'backup_' . date('Y-m-d_His') . '.sql';
    $filepath = '../../uploads/backup/' . $filename;
    
    // Create backup directory if not exists
    if (!file_exists('../../uploads/backup/')) {
        mkdir('../../uploads/backup/', 0777, true);
    }
    
    // MySQL dump command
    $command = "mysqldump -h " . DB_HOST . " -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . $filepath;
    
    exec($command, $output, $return);
    
    if ($return === 0) {
        // Download file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        
        // Delete file after download
        unlink($filepath);
        exit;
    } else {
        setAlert('danger', 'Gagal membuat backup database!');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Database - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-cloud-download me-2"></i>Backup Database</h1>
                </div>

                <?php showAlert(); ?>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bi bi-database text-primary" style="font-size: 80px;"></i>
                                <h4 class="mt-3">Backup Database</h4>
                                <p class="text-muted">
                                    Backup database akan menghasilkan file SQL yang berisi semua data perpustakaan.
                                    Simpan file backup di tempat yang aman.
                                </p>
                                <hr>
                                <a href="?backup=1" class="btn btn-primary btn-lg">
                                    <i class="bi bi-download me-2"></i>Download Backup Sekarang
                                </a>
                                
                                <div class="alert alert-warning mt-4 text-start">
                                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Catatan Penting:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Lakukan backup secara berkala (minimal 1x seminggu)</li>
                                        <li>Simpan file backup di lokasi yang aman</li>
                                        <li>File backup dapat digunakan untuk restore database</li>
                                        <li>Backup tidak termasuk file foto/upload</li>
                                    </ul>
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
