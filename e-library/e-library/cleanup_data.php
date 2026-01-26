<?php
/**
 * SCRIPT PEMBERSIHAN DATA E-LIBRARY
 * STK Santo Yakobus Merauke
 * 
 * FITUR:
 * - Backup database otomatis
 * - Konfirmasi password admin
 * - Transaction handling (rollback jika error)
 * - Urutan hapus yang aman (bottom-up)
 * - Hapus file PDF surat keterangan
 * - Reset AUTO_INCREMENT ke 1
 * - Logging detail aktivitas
 * - Report hasil pembersihan
 * 
 * PENGGUNAAN:
 * Upload file ini ke root directory e-library, lalu akses via browser
 * URL: https://stkyakobus.ac.id/e-library/cleanup_data.php
 */

// Include konfigurasi database yang sudah ada (untuk konstanta saja)
require_once __DIR__ . '/config/database.php';

// Path untuk backup dan PDF
define('BACKUP_PATH', __DIR__ . '/backups/');
define('PDF_PATH', __DIR__ . '/modules/surat_keterangan/pdf_generated/');

// Inisialisasi variabel
$step = isset($_GET['step']) ? $_GET['step'] : 'confirm';
$errors = [];
$success_messages = [];
$cleanup_report = [];

// =====================================================
// FUNGSI HELPER
// =====================================================

function connectDB() {
    // Buat koneksi BARU khusus untuk cleanup (jangan pakai global $conn)
    // Karena global $conn bisa sudah closed atau ada konflik
    $db_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($db_conn->connect_error) {
        die("Koneksi database gagal: " . $db_conn->connect_error);
    }
    
    $db_conn->set_charset("utf8mb4");
    return $db_conn;
}

function createBackup() {
    global $errors, $success_messages;
    
    // Buat folder backup jika belum ada
    if (!file_exists(BACKUP_PATH)) {
        mkdir(BACKUP_PATH, 0755, true);
    }
    
    $filename = 'backup_before_cleanup_' . date('Y-m-d_His') . '.sql';
    $filepath = BACKUP_PATH . $filename;
    
    // =====================================================
    // PHP NATIVE BACKUP (TANPA exec/mysqldump)
    // Aman untuk shared hosting yang disable exec()
    // =====================================================
    
    try {
        $conn = connectDB();
        
        // Buka file untuk ditulis
        $handle = fopen($filepath, 'w');
        if (!$handle) {
            throw new Exception("Gagal membuat file backup");
        }
        
        // Header SQL
        $sql_header = "-- E-Library Database Backup\n";
        $sql_header .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql_header .= "-- Database: " . DB_NAME . "\n\n";
        $sql_header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql_header .= "SET time_zone = \"+00:00\";\n\n";
        $sql_header .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $sql_header .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $sql_header .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $sql_header .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
        
        fwrite($handle, $sql_header);
        
        // Daftar tabel yang akan di-backup (semua tabel)
        $tables = [
            'admin',
            'buku',
            'dosen',
            'mahasiswa',
            'nomor_surat_counter',
            'pembayaran_denda_detail',
            'peminjaman',
            'pengembalian',
            'perpanjangan',
            'surat_keterangan'
        ];
        
        // Loop setiap tabel
        foreach ($tables as $table) {
            
            // Header tabel
            fwrite($handle, "\n--\n-- Table structure for table `{$table}`\n--\n\n");
            
            // DROP TABLE IF EXISTS
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            
            // CREATE TABLE
            $create_table = $conn->query("SHOW CREATE TABLE `{$table}`");
            if ($create_table) {
                $row = $create_table->fetch_row();
                fwrite($handle, $row[1] . ";\n\n");
            }
            
            // Data rows
            fwrite($handle, "--\n-- Dumping data for table `{$table}`\n--\n\n");
            
            $result = $conn->query("SELECT * FROM `{$table}`");
            
            if ($result && $result->num_rows > 0) {
                
                while ($row = $result->fetch_assoc()) {
                    
                    // Build INSERT statement
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
                    // Escape values
                    $escaped_values = [];
                    foreach ($values as $value) {
                        if (is_null($value)) {
                            $escaped_values[] = 'NULL';
                        } else {
                            $escaped_values[] = "'" . $conn->real_escape_string($value) . "'";
                        }
                    }
                    
                    $insert_sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escaped_values) . ");\n";
                    fwrite($handle, $insert_sql);
                }
                
                fwrite($handle, "\n");
            }
        }
        
        // Footer SQL
        $sql_footer = "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $sql_footer .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $sql_footer .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        
        fwrite($handle, $sql_footer);
        fclose($handle);
        
        // Verifikasi file
        if (file_exists($filepath) && filesize($filepath) > 0) {
            $size = round(filesize($filepath) / 1024, 2);
            $success_messages[] = "✅ Backup berhasil dibuat (PHP Native): {$filename} ({$size} KB)";
            $conn->close(); // Close koneksi sebelum return
            return $filepath;
        } else {
            $conn->close(); // Close koneksi sebelum throw
            throw new Exception("File backup kosong atau tidak terbuat");
        }
        
    } catch (Exception $e) {
        $errors[] = "❌ Gagal membuat backup: " . $e->getMessage();
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close(); // Pastikan koneksi di-close di catch block
        }
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        return false;
    }
}

function verifyAdminPassword($password) {
    $conn = connectDB();
    $query = "SELECT password FROM admin WHERE username = 'admin' LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $is_valid = password_verify($password, $row['password']);
        $conn->close(); // Close koneksi
        return $is_valid;
    }
    
    $conn->close(); // Close koneksi jika query gagal
    return false;
}

function deleteFiles($directory, $pattern = '*.pdf') {
    global $cleanup_report;
    
    if (!is_dir($directory)) {
        $cleanup_report['pdf_deleted'] = 0;
        return;
    }
    
    $files = glob($directory . $pattern);
    $deleted_count = 0;
    $deleted_files = [];
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $deleted_count++;
                $deleted_files[] = basename($file);
            }
        }
    }
    
    $cleanup_report['pdf_deleted'] = $deleted_count;
    $cleanup_report['pdf_files'] = $deleted_files;
}

function getTableCount($conn, $table) {
    $query = "SELECT COUNT(*) as count FROM `{$table}`";
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

function executeCleanup() {
    global $errors, $success_messages, $cleanup_report;
    
    $conn = connectDB();
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // =====================================================
        // STEP 1: AMBIL DATA STATISTIK SEBELUM HAPUS
        // =====================================================
        $cleanup_report['before'] = [
            'pembayaran_denda_detail' => getTableCount($conn, 'pembayaran_denda_detail'),
            'pengembalian' => getTableCount($conn, 'pengembalian'),
            'perpanjangan' => getTableCount($conn, 'perpanjangan'),
            'peminjaman' => getTableCount($conn, 'peminjaman'),
            'buku' => getTableCount($conn, 'buku'),
            'surat_keterangan' => getTableCount($conn, 'surat_keterangan'),
            'nomor_surat_counter' => getTableCount($conn, 'nomor_surat_counter')
        ];
        
        $success_messages[] = "📊 Data sebelum pembersihan berhasil dicatat";
        
        // =====================================================
        // STEP 2: HAPUS DATA (URUTAN BOTTOM-UP)
        // =====================================================
        
        // 2.1 Hapus pembayaran_denda_detail (child terdalam)
        if (!$conn->query("DELETE FROM pembayaran_denda_detail")) {
            throw new Exception("Gagal hapus pembayaran_denda_detail: " . $conn->error);
        }
        $cleanup_report['deleted']['pembayaran_denda_detail'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus pembayaran_denda_detail: {$conn->affected_rows} records";
        
        // 2.2 Hapus pengembalian
        if (!$conn->query("DELETE FROM pengembalian")) {
            throw new Exception("Gagal hapus pengembalian: " . $conn->error);
        }
        $cleanup_report['deleted']['pengembalian'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus pengembalian: {$conn->affected_rows} records";
        
        // 2.3 Hapus perpanjangan
        if (!$conn->query("DELETE FROM perpanjangan")) {
            throw new Exception("Gagal hapus perpanjangan: " . $conn->error);
        }
        $cleanup_report['deleted']['perpanjangan'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus perpanjangan: {$conn->affected_rows} records";
        
        // 2.4 Hapus peminjaman
        if (!$conn->query("DELETE FROM peminjaman")) {
            throw new Exception("Gagal hapus peminjaman: " . $conn->error);
        }
        $cleanup_report['deleted']['peminjaman'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus peminjaman: {$conn->affected_rows} records";
        
        // 2.5 Hapus buku (parent)
        if (!$conn->query("DELETE FROM buku")) {
            throw new Exception("Gagal hapus buku: " . $conn->error);
        }
        $cleanup_report['deleted']['buku'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus buku: {$conn->affected_rows} records";
        
        // 2.6 Hapus surat_keterangan (independent)
        if (!$conn->query("DELETE FROM surat_keterangan")) {
            throw new Exception("Gagal hapus surat_keterangan: " . $conn->error);
        }
        $cleanup_report['deleted']['surat_keterangan'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus surat_keterangan: {$conn->affected_rows} records";
        
        // 2.7 Hapus nomor_surat_counter (independent)
        if (!$conn->query("DELETE FROM nomor_surat_counter")) {
            throw new Exception("Gagal hapus nomor_surat_counter: " . $conn->error);
        }
        $cleanup_report['deleted']['nomor_surat_counter'] = $conn->affected_rows;
        $success_messages[] = "✅ Hapus nomor_surat_counter: {$conn->affected_rows} records";
        
        // =====================================================
        // STEP 3: RESET AUTO_INCREMENT KE 1
        // =====================================================
        $tables_to_reset = [
            'buku',
            'peminjaman',
            'perpanjangan',
            'pengembalian',
            'pembayaran_denda_detail',
            'surat_keterangan',
            'nomor_surat_counter'
        ];
        
        foreach ($tables_to_reset as $table) {
            if (!$conn->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1")) {
                throw new Exception("Gagal reset AUTO_INCREMENT untuk {$table}: " . $conn->error);
            }
        }
        
        $success_messages[] = "🔄 Reset AUTO_INCREMENT berhasil untuk 7 tabel";
        
        // =====================================================
        // STEP 4: HAPUS FILE PDF
        // =====================================================
        deleteFiles(PDF_PATH, '*.pdf');
        if (isset($cleanup_report['pdf_deleted'])) {
            $success_messages[] = "🗑️ Hapus file PDF: {$cleanup_report['pdf_deleted']} files";
        }
        
        // Commit transaction
        $conn->commit();
        $success_messages[] = "✅ COMMIT: Semua perubahan berhasil disimpan!";
        
        // Verify data setelah hapus
        $cleanup_report['after'] = [
            'pembayaran_denda_detail' => getTableCount($conn, 'pembayaran_denda_detail'),
            'pengembalian' => getTableCount($conn, 'pengembalian'),
            'perpanjangan' => getTableCount($conn, 'perpanjangan'),
            'peminjaman' => getTableCount($conn, 'peminjaman'),
            'buku' => getTableCount($conn, 'buku'),
            'surat_keterangan' => getTableCount($conn, 'surat_keterangan'),
            'nomor_surat_counter' => getTableCount($conn, 'nomor_surat_counter')
        ];
        
        $cleanup_report['status'] = 'success';
        
    } catch (Exception $e) {
        // Rollback jika error
        $conn->rollback();
        $errors[] = "❌ ERROR: " . $e->getMessage();
        $errors[] = "🔙 ROLLBACK: Semua perubahan dibatalkan!";
        $cleanup_report['status'] = 'failed';
        $conn->close(); // Pastikan koneksi di-close meskipun error
        return false;
    }
    
    $conn->close();
    
    return $cleanup_report['status'] === 'success';
}

// =====================================================
// PROSES HALAMAN
// =====================================================

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($step === 'execute') {
        $password = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
        
        // Verifikasi password admin
        if (empty($password)) {
            $errors[] = "Password admin wajib diisi!";
        } elseif (!verifyAdminPassword($password)) {
            $errors[] = "Password admin salah! Pembersihan dibatalkan.";
        } else {
            // Password benar, lakukan backup dan cleanup
            
            // 1. Backup database
            $backup_file = createBackup();
            
            if ($backup_file) {
                // 2. Eksekusi cleanup
                $cleanup_success = executeCleanup();
                
                if ($cleanup_success) {
                    $step = 'success';
                } else {
                    $step = 'error';
                }
            } else {
                $step = 'error';
                $errors[] = "Pembersihan dibatalkan karena backup gagal!";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembersihan Data E-Library - STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .cleanup-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .danger-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success-box {
            background: #d1e7dd;
            border-left: 4px solid #198754;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box {
            background: #cfe2ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .data-table {
            font-size: 14px;
        }
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn-danger-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
        }
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245, 87, 108, 0.4);
        }
        .report-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .report-item:last-child {
            border-bottom: none;
        }
        .badge-custom {
            font-size: 12px;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="cleanup-container">
        
        <?php if ($step === 'confirm'): ?>
        <!-- =====================================================
             HALAMAN KONFIRMASI
             ===================================================== -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Pembersihan Data E-Library</h3>
            </div>
            <div class="card-body">
                
                <div class="danger-box">
                    <h5><i class="bi bi-shield-exclamation me-2"></i>PERINGATAN PENTING!</h5>
                    <p class="mb-2">Script ini akan menghapus <strong>SEMUA DATA TRANSAKSI</strong> berikut:</p>
                    <ul class="mb-0">
                        <li>✅ Data Buku (45 records)</li>
                        <li>✅ Peminjaman Buku (51 records)</li>
                        <li>✅ Perpanjangan Buku (4 records)</li>
                        <li>✅ Pengembalian Buku (42 records)</li>
                        <li>✅ Pembayaran Denda (34 records)</li>
                        <li>✅ Surat Keterangan (8 records)</li>
                        <li>✅ File PDF Surat (2 files)</li>
                    </ul>
                </div>

                <div class="success-box">
                    <h5><i class="bi bi-check-circle me-2"></i>Data Yang TIDAK Akan Dihapus:</h5>
                    <ul class="mb-0">
                        <li>❌ Data Mahasiswa (307 records) - <strong>AMAN</strong></li>
                        <li>❌ Data Dosen (19 records) - <strong>AMAN</strong></li>
                        <li>❌ Data Admin (1 record) - <strong>AMAN</strong></li>
                    </ul>
                </div>

                <div class="info-box">
                    <h5><i class="bi bi-info-circle me-2"></i>Fitur Keamanan:</h5>
                    <ul class="mb-0">
                        <li>✅ Backup database otomatis (PHP Native - Aman di shared hosting)</li>
                        <li>✅ Transaction handling (rollback jika error)</li>
                        <li>✅ Urutan hapus yang aman (bottom-up)</li>
                        <li>✅ Reset AUTO_INCREMENT ke 1 (Fresh Start)</li>
                        <li>✅ Logging detail aktivitas</li>
                    </ul>
                </div>

                <div class="warning-box">
                    <h5><i class="bi bi-clock-history me-2"></i>Proses Akan Berjalan:</h5>
                    <ol class="mb-0">
                        <li>Backup database ke folder <code>/backups/</code></li>
                        <li>Hapus data transaksi (urutan: detail → master)</li>
                        <li>Hapus file PDF surat keterangan</li>
                        <li>Reset AUTO_INCREMENT ke 1</li>
                        <li>Generate laporan hasil</li>
                    </ol>
                </div>

                <form method="POST" action="?step=execute" onsubmit="return confirm('Apakah Anda YAKIN ingin melanjutkan? Proses ini TIDAK BISA dibatalkan!');">
                    <div class="mb-3 mt-4">
                        <label for="admin_password" class="form-label fw-bold">
                            <i class="bi bi-shield-lock me-2"></i>Password Admin (Untuk Konfirmasi):
                        </label>
                        <input type="password" class="form-control form-control-lg" id="admin_password" name="admin_password" required placeholder="Masukkan password admin">
                        <small class="text-muted">Password yang sama dengan login admin</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg btn-danger-custom">
                            <i class="bi bi-trash3 me-2"></i>MULAI PEMBERSIHAN DATA
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>Batal & Kembali
                        </a>
                    </div>
                </form>

            </div>
        </div>

        <?php elseif ($step === 'execute'): ?>
        <!-- =====================================================
             HALAMAN PROSES (dengan error)
             ===================================================== -->
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="mb-0"><i class="bi bi-x-circle-fill me-2"></i>Proses Gagal</h3>
            </div>
            <div class="card-body">
                
                <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
                <?php endforeach; ?>

                <a href="?step=confirm" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>

            </div>
        </div>

        <?php elseif ($step === 'success'): ?>
        <!-- =====================================================
             HALAMAN SUKSES
             ===================================================== -->
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Pembersihan Data Berhasil!</h3>
            </div>
            <div class="card-body">
                
                <div class="success-box">
                    <h5><i class="bi bi-check2-all me-2"></i>Proses Selesai</h5>
                    <p class="mb-0">Semua data transaksi berhasil dihapus dan database siap untuk digunakan!</p>
                </div>

                <h5 class="mt-4 mb-3"><i class="bi bi-clipboard-data me-2"></i>Log Aktivitas:</h5>
                <div class="border rounded p-3 bg-light">
                    <?php foreach ($success_messages as $msg): ?>
                    <div class="report-item">
                        <?= $msg ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h5 class="mt-4 mb-3"><i class="bi bi-graph-up me-2"></i>Statistik Pembersihan:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>Tabel</th>
                                <th class="text-center">Sebelum</th>
                                <th class="text-center">Dihapus</th>
                                <th class="text-center">Sesudah</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $tables = [
                                'pembayaran_denda_detail' => 'Pembayaran Denda Detail',
                                'pengembalian' => 'Pengembalian',
                                'perpanjangan' => 'Perpanjangan',
                                'peminjaman' => 'Peminjaman',
                                'buku' => 'Buku',
                                'surat_keterangan' => 'Surat Keterangan',
                                'nomor_surat_counter' => 'Nomor Surat Counter'
                            ];
                            
                            foreach ($tables as $key => $label): 
                                $before = $cleanup_report['before'][$key];
                                $deleted = $cleanup_report['deleted'][$key] ?? 0;
                                $after = $cleanup_report['after'][$key];
                                $status = ($after == 0) ? 'success' : 'warning';
                            ?>
                            <tr>
                                <td><?= $label ?></td>
                                <td class="text-center"><span class="badge bg-secondary badge-custom"><?= $before ?></span></td>
                                <td class="text-center"><span class="badge bg-danger badge-custom"><?= $deleted ?></span></td>
                                <td class="text-center"><span class="badge bg-success badge-custom"><?= $after ?></span></td>
                                <td class="text-center">
                                    <?php if ($status === 'success'): ?>
                                    <span class="badge bg-success badge-custom">✓ OK</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning badge-custom">! Warning</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($cleanup_report['pdf_deleted'])): ?>
                <div class="alert alert-info mt-3" role="alert">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    <strong>File PDF Dihapus:</strong> <?= $cleanup_report['pdf_deleted'] ?> file(s)
                    <?php if (!empty($cleanup_report['pdf_files'])): ?>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($cleanup_report['pdf_files'] as $file): ?>
                        <li><code><?= $file ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="alert alert-success mt-3" role="alert">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    <strong>AUTO_INCREMENT:</strong> Reset ke 1 untuk semua tabel transaksi
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-house-door me-2"></i>Kembali ke Dashboard
                    </a>
                    <a href="modules/buku/index.php" class="btn btn-success btn-lg">
                        <i class="bi bi-book me-2"></i>Mulai Input Data Buku
                    </a>
                </div>

                <div class="alert alert-warning mt-4" role="alert">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>PENTING:</strong> Jangan lupa hapus file <code>cleanup_data.php</code> setelah selesai untuk keamanan!
                </div>

            </div>
        </div>

        <?php elseif ($step === 'error'): ?>
        <!-- =====================================================
             HALAMAN ERROR
             ===================================================== -->
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>Terjadi Kesalahan!</h3>
            </div>
            <div class="card-body">
                
                <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endforeach; ?>

                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Jika ada transaction rollback, maka TIDAK ADA DATA yang terhapus. Database masih dalam kondisi aman.
                </div>

                <a href="?step=confirm" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Coba Lagi
                </a>

            </div>
        </div>

        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center mt-4 text-white">
            <small>
                <i class="bi bi-shield-check me-1"></i>
                E-Library Management System - STK Santo Yakobus Merauke
            </small>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>