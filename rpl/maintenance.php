<?php
/*
 * MAINTENANCE.PHP - System Maintenance Tools
 * Hanya untuk Admin - Tools untuk backup, maintenance, dan debugging
 * 
 * PERINGATAN: File ini berisi tools yang powerful untuk maintenance sistem.
 * Pastikan hanya admin yang memiliki akses dan backup database sebelum menjalankan operasi apapun.
 */

require_once 'config.php';
requireAdmin();

$message = '';
$error = '';
$action_performed = '';

// Handle maintenance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'backup_database':
                $action_performed = backupDatabase();
                break;
                
            case 'cleanup_logs':
                $days = (int)($_POST['days'] ?? 30);
                $action_performed = cleanupLogs($days);
                break;
                
            case 'reset_all_assignments':
                $action_performed = resetAllAssignments();
                break;
                
            case 'optimize_database':
                $action_performed = optimizeDatabase();
                break;
                
            case 'generate_test_data':
                $count = (int)($_POST['test_count'] ?? 5);
                $action_performed = generateTestData($count);
                break;
                
            case 'export_statistics':
                $action_performed = exportStatistics();
                break;
                
            default:
                $error = 'Aksi tidak dikenali!';
        }
        
        if ($action_performed && !$error) {
            $message = $action_performed;
            logAktivitas($pdo, $_SESSION['user_id'], 'Maintenance Action', $action);
        }
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Maintenance functions
function backupDatabase() {
    global $pdo;
    
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = __DIR__ . '/backups/';
    
    // Create backups directory if not exists
    if (!is_dir($backup_path)) {
        mkdir($backup_path, 0755, true);
    }
    
    // Simple backup using mysqldump (requires shell access)
    $command = sprintf(
        'mysqldump -h%s -u%s -p%s %s > %s',
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        $backup_path . $backup_file
    );
    
    // Alternative: PHP-based backup
    $tables = ['users', 'mahasiswa', 'penilaian_rpl', 'dokumen_perangkat', 'log_aktivitas'];
    $backup_content = "-- Database Backup RPL System\n";
    $backup_content .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $create_table = $stmt->fetch()['Create Table'];
        $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $backup_content .= $create_table . ";\n\n";
        
        $stmt = $pdo->query("SELECT * FROM `$table`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(function($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, array_values($row));
            
            $backup_content .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
        $backup_content .= "\n";
    }
    
    file_put_contents($backup_path . $backup_file, $backup_content);
    
    return "Backup database berhasil disimpan: $backup_file";
}

function cleanupLogs($days) {
    global $pdo;
    
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
    $stmt = $pdo->prepare("DELETE FROM log_aktivitas WHERE created_at < ?");
    $stmt->execute([$cutoff_date]);
    
    $deleted = $stmt->rowCount();
    return "Berhasil menghapus $deleted log aktivitas yang lebih lama dari $days hari";
}

function resetAllAssignments() {
    global $pdo;
    
    $pdo->beginTransaction();
    
    // Reset assignment mahasiswa
    $stmt = $pdo->query("UPDATE mahasiswa SET assigned_dosen_id = NULL, status_penilaian = 'belum_dinilai'");
    $reset_mhs = $stmt->rowCount();
    
    // Hapus semua penilaian
    $stmt = $pdo->query("DELETE FROM penilaian_rpl");
    $deleted_penilaian = $stmt->rowCount();
    
    $pdo->commit();
    
    return "Reset assignment: $reset_mhs mahasiswa, hapus $deleted_penilaian penilaian";
}

function optimizeDatabase() {
    global $pdo;
    
    $tables = ['users', 'mahasiswa', 'penilaian_rpl', 'dokumen_perangkat', 'log_aktivitas'];
    $optimized = 0;
    
    foreach ($tables as $table) {
        $pdo->query("OPTIMIZE TABLE `$table`");
        $optimized++;
    }
    
    return "Berhasil mengoptimasi $optimized tabel database";
}

function generateTestData($count) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO mahasiswa (nim, nama_lengkap, jenjang, tempat_tugas, email, status_pegawai, provinsi) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $jenjang_options = ['SD', 'SMP', 'SMA', 'SMK'];
    $status_options = ['PNS', 'PPPK', 'NON ASN'];
    $provinsi_options = ['NUSA TENGGARA TIMUR', 'PAPUA', 'KALIMANTAN UTARA'];
    
    for ($i = 1; $i <= $count; $i++) {
        $nim = 'TEST' . str_pad($i, 6, '0', STR_PAD_LEFT);
        $nama = 'MAHASISWA TEST ' . $i;
        $jenjang = $jenjang_options[array_rand($jenjang_options)];
        $tempat_tugas = 'SEKOLAH TEST ' . $i;
        $email = 'test' . $i . '@test.com';
        $status = $status_options[array_rand($status_options)];
        $provinsi = $provinsi_options[array_rand($provinsi_options)];
        
        $stmt->execute([$nim, $nama, $jenjang, $tempat_tugas, $email, $status, $provinsi]);
    }
    
    return "Berhasil generate $count data mahasiswa test";
}

function exportStatistics() {
    global $pdo;
    
    $stats = [];
    
    // Total statistics
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $stats['total_mahasiswa'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'dosen'");
    $stats['total_dosen'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM penilaian_rpl WHERE status_penilaian = 'final'");
    $stats['total_penilaian_selesai'] = $stmt->fetch()['total'];
    
    // Distribution by jenjang
    $stmt = $pdo->query("SELECT jenjang, COUNT(*) as count FROM mahasiswa GROUP BY jenjang");
    $stats['distribusi_jenjang'] = $stmt->fetchAll();
    
    // Distribution by provinsi
    $stmt = $pdo->query("SELECT provinsi, COUNT(*) as count FROM mahasiswa GROUP BY provinsi ORDER BY count DESC LIMIT 10");
    $stats['distribusi_provinsi'] = $stmt->fetchAll();
    
    // Progress by dosen
    $stmt = $pdo->query("
        SELECT u.nama_lengkap, 
               COUNT(m.id) as total_assigned,
               COUNT(p.id) as total_dinilai
        FROM users u
        LEFT JOIN mahasiswa m ON u.id = m.assigned_dosen_id
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.status_penilaian = 'final'
        WHERE u.role = 'dosen'
        GROUP BY u.id, u.nama_lengkap
    ");
    $stats['progress_dosen'] = $stmt->fetchAll();
    
    $export_file = 'statistics_export_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents(__DIR__ . '/exports/' . $export_file, json_encode($stats, JSON_PRETTY_PRINT));
    
    return "Export statistik berhasil: $export_file";
}

// Get system information
function getSystemInfo() {
    global $pdo;
    
    $info = [];
    
    // Database info
    $stmt = $pdo->query("SELECT VERSION() as version");
    $info['mysql_version'] = $stmt->fetch()['version'];
    
    // Table sizes
    $stmt = $pdo->query("
        SELECT table_name, 
               ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = '" . DB_NAME . "'
        ORDER BY size_mb DESC
    ");
    $info['table_sizes'] = $stmt->fetchAll();
    
    // PHP info
    $info['php_version'] = PHP_VERSION;
    $info['memory_limit'] = ini_get('memory_limit');
    $info['upload_max_filesize'] = ini_get('upload_max_filesize');
    $info['max_execution_time'] = ini_get('max_execution_time');
    
    // Disk space
    $info['disk_free_space'] = round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB';
    $info['disk_total_space'] = round(disk_total_space('.') / 1024 / 1024 / 1024, 2) . ' GB';
    
    return $info;
}

$system_info = getSystemInfo();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - <?= APP_NAME ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: #e74c3c;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: background 0.3s;
            margin: 0.5rem;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        
        .btn:hover { opacity: 0.9; }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table th, .info-table td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .info-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .danger-zone {
            border: 2px solid #e74c3c;
            border-radius: 8px;
            padding: 1.5rem;
            background: #fdf2f2;
        }
        
        .danger-zone h3 {
            color: #e74c3c;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 System Maintenance Tools</h1>
        <a href="dashboard_admin.php" class="btn btn-primary">← Kembali ke Dashboard</a>
    </div>
    
    <div class="container">
        <div class="warning">
            <strong>⚠️ PERINGATAN MAINTENANCE</strong><br>
            Tools ini dapat mengubah atau menghapus data sistem. Pastikan Anda telah melakukan backup database sebelum menjalankan operasi apapun.
            Gunakan dengan sangat hati-hati!
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
        <?php endif; ?>
        
        <div class="grid">
            <!-- Backup & Export -->
            <div class="card">
                <h3>💾 Backup & Export</h3>
                
                <form method="POST" style="margin-bottom: 1rem;">
                    <input type="hidden" name="action" value="backup_database">
                    <button type="submit" class="btn btn-success" 
                            onclick="return confirm('Backup database sekarang?')">
                        📁 Backup Database
                    </button>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="action" value="export_statistics">
                    <button type="submit" class="btn btn-primary">
                        📊 Export Statistik
                    </button>
                </form>
            </div>
            
            <!-- Database Maintenance -->
            <div class="card">
                <h3>🗄️ Database Maintenance</h3>
                
                <form method="POST" style="margin-bottom: 1rem;">
                    <input type="hidden" name="action" value="optimize_database">
                    <button type="submit" class="btn btn-warning">
                        ⚡ Optimize Database
                    </button>
                </form>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="days">Hapus log lebih dari (hari):</label>
                        <input type="number" id="days" name="days" value="30" min="1" max="365">
                    </div>
                    <input type="hidden" name="action" value="cleanup_logs">
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Hapus log aktivitas lama?')">
                        🗑️ Cleanup Logs
                    </button>
                </form>
            </div>
            
            <!-- Testing Tools -->
            <div class="card">
                <h3>🧪 Testing Tools</h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="test_count">Jumlah data test:</label>
                        <input type="number" id="test_count" name="test_count" value="5" min="1" max="100">
                    </div>
                    <input type="hidden" name="action" value="generate_test_data">
                    <button type="submit" class="btn btn-primary">
                        🎲 Generate Test Data
                    </button>
                </form>
            </div>
            
            <!-- System Information -->
            <div class="card">
                <h3>ℹ️ System Information</h3>
                
                <table class="info-table">
                    <tr><th>PHP Version</th><td><?= $system_info['php_version'] ?></td></tr>
                    <tr><th>MySQL Version</th><td><?= $system_info['mysql_version'] ?></td></tr>
                    <tr><th>Memory Limit</th><td><?= $system_info['memory_limit'] ?></td></tr>
                    <tr><th>Upload Max Size</th><td><?= $system_info['upload_max_filesize'] ?></td></tr>
                    <tr><th>Max Execution Time</th><td><?= $system_info['max_execution_time'] ?>s</td></tr>
                    <tr><th>Free Disk Space</th><td><?= $system_info['disk_free_space'] ?></td></tr>
                    <tr><th>Total Disk Space</th><td><?= $system_info['disk_total_space'] ?></td></tr>
                </table>
            </div>
            
            <!-- Database Tables -->
            <div class="card">
                <h3>📊 Database Tables</h3>
                
                <table class="info-table">
                    <thead>
                        <tr><th>Table</th><th>Size (MB)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($system_info['table_sizes'] as $table): ?>
                            <tr>
                                <td><?= sanitizeInput($table['table_name']) ?></td>
                                <td><?= $table['size_mb'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <div class="danger-zone">
            <h3>⚠️ DANGER ZONE</h3>
            <p style="margin-bottom: 1rem;">
                Operasi di bawah ini akan menghapus data secara permanen. 
                <strong>BACKUP DATABASE TERLEBIH DAHULU!</strong>
            </p>
            
            <form method="POST">
                <input type="hidden" name="action" value="reset_all_assignments">
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('PERINGATAN: Ini akan menghapus SEMUA assignment mahasiswa dan penilaian! Apakah Anda yakin?')">
                    💥 Reset ALL Assignments
                </button>
            </form>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem; color: #666;">
            <strong>Catatan Maintenance:</strong><br>
            • Backup database secara rutin (mingguan/bulanan)<br>
            • Cleanup logs untuk menghemat space disk<br>
            • Monitor ukuran database dan optimasi jika diperlukan<br>
            • Test data hanya untuk development, hapus di production<br>
            • Selalu backup sebelum melakukan reset assignment
        </div>
    </div>
</body>
</html>