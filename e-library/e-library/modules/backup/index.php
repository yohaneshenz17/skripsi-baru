<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';
require_once '../../config/functions.php';

$pageTitle = "Backup & Restore Database";
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// --- 1. LOGIKA BACKUP DATABASE (DOWNLOAD) ---
if (isset($_POST['backup_now'])) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while($row = $result->fetch_row()) { $tables[] = $row[0]; }
    
    $return = "SET FOREIGN_KEY_CHECKS=0;\n";
    $return .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $return .= "SET time_zone = \"+00:00\";\n\n";
    
    foreach($tables as $table) {
        $return .= "DROP TABLE IF EXISTS `$table`;\n";
        $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $return .= "\n\n" . $row2[1] . ";\n\n";
        
        $result = $conn->query("SELECT * FROM $table");
        $num_fields = $result->field_count;
        
        for ($i = 0; $i < $num_fields; $i++) {
            while($row = $result->fetch_row()) {
                $return .= "INSERT INTO `$table` VALUES(";
                for($j=0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return .= '"' . $row[$j] . '"' ; } else { $return .= '""'; }
                    if ($j < ($num_fields-1)) { $return .= ','; }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }
    
    $return .= "SET FOREIGN_KEY_CHECKS=1;";
    $filename = 'db_library_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"".$filename."\"");
    echo $return;
    exit;
}

// --- 2. LOGIKA RESTORE DATABASE (UPLOAD) ---
if (isset($_POST['restore_now'])) {
    $file = $_FILES['backup_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if ($ext != 'sql') {
        setAlert('danger', 'Format file harus .sql!');
    } else {
        $templine = '';
        $lines = file($file['tmp_name']);
        $error = '';
        
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') continue;
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';') {
                if (!$conn->query($templine)) {
                    $error .= "Error: " . $conn->error . "<br>";
                }
                $templine = '';
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        
        if (empty($error)) {
            setAlert('success', 'Database berhasil direstore! Silakan login ulang.');
        } else {
            setAlert('danger', 'Terjadi kesalahan saat restore:<br>' . $error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        
        /* Navbar Konsisten */
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 0.8rem 1.5rem; }
        .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.3rem; }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar Konsisten */
        .sidebar { position: fixed; top: 56px; bottom: 0; left: 0; width: 250px; background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%); box-shadow: 2px 0 15px rgba(0,0,0,0.08); overflow-y: auto; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
        
        .sidebar-heading { font-size: 0.65rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px; padding: 1rem 1.2rem 0.5rem; color: #64748b; background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%); margin-top: 0.5rem; position: relative; }
        .sidebar-heading::after { content: ''; position: absolute; bottom: 0; left: 1.2rem; right: 1.2rem; height: 2px; background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%); }
        
        .sidebar .nav-link { font-weight: 500; color: #475569; padding: 0.75rem 1.2rem; border-left: 3px solid transparent; transition: all 0.3s ease; margin: 0.2rem 0; }
        .sidebar .nav-link i { width: 22px; font-size: 1.1rem; margin-right: 0.7rem; }
        .sidebar .nav-link:hover { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%); border-left-color: #667eea; transform: translateX(3px); }
        .sidebar .nav-link.active { color: #667eea; background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%); border-left-color: #667eea; font-weight: 600; }
        
        .main-content { margin-left: 250px; margin-top: 56px; padding: 1.5rem; }
        
        /* Card Styles */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-fill me-2"></i>E-Library STK Yakobus
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle me-1"></i><?= $admin_username ?></span>
                <a href="../../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>

    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="sidebar-heading">MENU UTAMA</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
            
            <li class="sidebar-heading">MASTER DATA</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php"><i class="bi bi-book"></i>Buku</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php"><i class="bi bi-people"></i>Mahasiswa</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php"><i class="bi bi-person-badge"></i>Dosen</a></li>
            
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/peminjaman/index.php"><i class="bi bi-arrow-left-right"></i>Peminjaman</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php"><i class="bi bi-arrow-clockwise"></i>Perpanjangan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php"><i class="bi bi-box-arrow-in-down"></i>Pengembalian</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php"><i class="bi bi-cash-stack"></i>Manajemen Denda</a></li>
            
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php"><i class="bi bi-file-earmark-text"></i>Surat Keterangan</a></li>
            
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php"><i class="bi bi-file-bar-graph"></i>Laporan</a></li>
            <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>modules/backup/index.php"><i class="bi bi-cloud-download"></i>Backup Database</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="bg-white p-3 rounded mb-4 shadow-sm border-start border-4 border-info d-flex justify-content-between align-items-center">
            <h1 class="h3 fw-bold m-0 text-dark"><i class="bi bi-database-gear me-2"></i><?= $pageTitle ?></h1>
        </div>

        <?php showAlert(); ?>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-start border-4 border-success">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-success">
                            <span class="d-inline-block p-3 rounded-circle bg-success bg-opacity-10">
                                <i class="bi bi-cloud-download" style="font-size: 3rem;"></i>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold">Backup Database</h4>
                        <p class="card-text text-muted mb-4">
                            Download seluruh data perpustakaan (Buku, Anggota, Transaksi) ke dalam format file <b>.sql</b>.
                        </p>
                        <form method="POST" action="">
                            <button type="submit" name="backup_now" class="btn btn-success px-4 py-2 rounded-pill fw-bold w-100 shadow-sm">
                                <i class="bi bi-download me-2"></i>DOWNLOAD BACKUP
                            </button>
                        </form>
                        <div class="mt-3 small text-muted">
                            <i class="bi bi-info-circle me-1"></i> Disarankan backup minimal 1x seminggu.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100 border-start border-4 border-danger">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 text-danger">
                            <span class="d-inline-block p-3 rounded-circle bg-danger bg-opacity-10">
                                <i class="bi bi-cloud-upload" style="font-size: 3rem;"></i>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold text-danger">Restore Database</h4>
                        <p class="card-text text-muted mb-4">
                            Kembalikan data dari file <b>.sql</b>. <br>
                            <span class="text-danger fw-bold bg-danger bg-opacity-10 px-2 py-1 rounded small">PERHATIAN: Data saat ini akan ditimpa!</span>
                        </p>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="input-group mb-3 shadow-sm">
                                <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                            </div>
                            <button type="button" class="btn btn-outline-danger px-4 py-2 rounded-pill fw-bold w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmRestore">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>RESTORE DATA
                            </button>

                            <div class="modal fade" id="confirmRestore" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Restore</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start p-4">
                                            <p class="fw-bold mb-2">Anda yakin ingin melakukan restore database?</p>
                                            <div class="alert alert-warning border-warning">
                                                <ul class="mb-0 small">
                                                    <li>Semua data yang ada sekarang akan <strong>DIHAPUS & DITIMPA</strong>.</li>
                                                    <li>Proses ini tidak dapat dibatalkan.</li>
                                                    <li>Pastikan file yang diupload adalah hasil backup dari sistem ini.</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="restore_now" class="btn btn-danger shadow-sm">Ya, Timpa Data!</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>