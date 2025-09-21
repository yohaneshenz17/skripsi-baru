<?php
/**
 * CSV BATCH IMPORT SYSTEM - NO EXTERNAL LIBRARY REQUIRED
 * 
 * File: csv_batch_import.php
 * 
 * Simple, reliable import system menggunakan CSV files
 * Tidak butuh PhpSpreadsheet, hanya PHP native functions
 * 
 * CARA PENGGUNAAN:
 * 1. Convert Excel ke CSV (batch_1.csv, batch_2.csv, dst)
 * 2. Upload CSV files
 * 3. Import per batch dengan aman
 */

require_once 'config.php';
requireAdmin();

$BATCH_SIZE_PER_EXECUTION = 50; // Records per database transaction
$MAX_EXECUTION_TIME = 240; // 4 menit per batch
$UPLOAD_DIR = 'csv_uploads/';

set_time_limit($MAX_EXECUTION_TIME);
ini_set('memory_limit', '256M');

// Create upload directory if not exists
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

$message = '';
$error = '';
$batch_results = [];
$available_batches = [];

// Scan for available CSV batch files
$csv_files = glob($UPLOAD_DIR . "batch_*.csv");
foreach ($csv_files as $file) {
    $batch_num = preg_replace('/.*batch_(\d+)\.csv/', '$1', $file);
    $available_batches[] = [
        'file' => basename($file),
        'path' => $file,
        'batch_num' => (int)$batch_num,
        'size' => filesize($file),
        'lines' => count(file($file)) - 1, // Minus header
        'exists' => file_exists($file)
    ];
}

// Sort by batch number
usort($available_batches, function($a, $b) {
    return $a['batch_num'] - $b['batch_num'];
});

// Handle CSV file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    
    try {
        $uploadedFile = $_FILES['csv_file'];
        
        if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
            $filename = basename($uploadedFile['name']);
            
            // Validate CSV file
            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
                throw new Exception('File harus berformat CSV');
            }
            
            // Move uploaded file
            $destination = $UPLOAD_DIR . $filename;
            if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
                
                // Count lines
                $line_count = count(file($destination)) - 1; // Minus header
                
                $message = "CSV berhasil diupload: $filename ($line_count records)";
                
                // Refresh available batches
                $csv_files = glob($UPLOAD_DIR . "batch_*.csv");
                $available_batches = [];
                foreach ($csv_files as $file) {
                    $batch_num = preg_replace('/.*batch_(\d+)\.csv/', '$1', $file);
                    $available_batches[] = [
                        'file' => basename($file),
                        'path' => $file,
                        'batch_num' => (int)$batch_num,
                        'size' => filesize($file),
                        'lines' => count(file($file)) - 1,
                        'exists' => file_exists($file)
                    ];
                }
                
                usort($available_batches, function($a, $b) {
                    return $a['batch_num'] - $b['batch_num'];
                });
                
            } else {
                throw new Exception('Gagal menyimpan file');
            }
            
        } else {
            $error = 'Error upload file: ' . $uploadedFile['error'];
        }
        
    } catch (Exception $e) {
        $error = 'Error processing file: ' . $e->getMessage();
    }
}

// Handle CSV batch import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv_batch'])) {
    
    $batch_file = $_POST['batch_file'] ?? '';
    $batch_path = $UPLOAD_DIR . $batch_file;
    
    if (!file_exists($batch_path)) {
        $error = "CSV file tidak ditemukan: $batch_file";
    } else {
        
        try {
            $start_time = microtime(true);
            $pdo->beginTransaction();
            
            // Open CSV file
            $handle = fopen($batch_path, 'r');
            if (!$handle) {
                throw new Exception("Tidak bisa membuka file CSV");
            }
            
            // Read header (first line)
            $header = fgetcsv($handle);
            
            // Prepare SQL
            $sql = "
                INSERT INTO mahasiswa (
                    nim, nama_lengkap, jenis_kelamin, tempat_tugas, no_telepon,
                    status_pegawai, tanggal_lahir, provinsi, kabupaten, jenjang, 
                    link_sk_mengajar, link_administrasi, link_inovasi,
                    rpl02_perangkat_ganjil_2019, rpl02_perangkat_genap_2019,
                    rpl02_perangkat_ganjil_2020, rpl02_perangkat_genap_2020,
                    rpl02_perangkat_ganjil_2021, rpl02_perangkat_genap_2021,
                    rpl02_perangkat_ganjil_2022, rpl02_perangkat_genap_2022,
                    rpl02_perangkat_ganjil_2023, rpl02_perangkat_genap_2023,
                    rpl02_perangkat_ganjil_2024, rpl02_perangkat_genap_2024,
                    rpl03_pengembangan_ganjil_2019, rpl03_pengembangan_genap_2019,
                    rpl03_pengembangan_ganjil_2020, rpl03_pengembangan_genap_2020,
                    rpl03_pengembangan_ganjil_2021, rpl03_pengembangan_genap_2021,
                    rpl03_pengembangan_ganjil_2022, rpl03_pengembangan_genap_2022,
                    rpl03_pengembangan_ganjil_2023, rpl03_pengembangan_genap_2023,
                    rpl03_pengembangan_ganjil_2024, rpl03_pengembangan_genap_2024
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    nama_lengkap = VALUES(nama_lengkap),
                    tempat_tugas = VALUES(tempat_tugas),
                    updated_at = CURRENT_TIMESTAMP
            ";
            
            $stmt = $pdo->prepare($sql);
            
            $imported = 0;
            $updated = 0;
            $errors = 0;
            $processed = 0;
            $line_number = 1; // Start from 1 (after header)
            
            // Process each line
            while (($row = fgetcsv($handle)) !== FALSE) {
                $line_number++;
                
                try {
                    // Skip empty rows
                    if (empty($row[1])) continue;
                    
                    // Map data from CSV (skip No column at index 0)
                    $nim = sanitizeInput($row[1] ?? '');
                    $nama_lengkap = sanitizeInput($row[2] ?? '');
                    $jenis_kelamin = sanitizeInput($row[3] ?? '');
                    $tempat_tugas = sanitizeInput($row[4] ?? '');
                    $no_telepon = sanitizeInput($row[5] ?? '');
                    $status_pegawai = sanitizeInput($row[6] ?? '');
                    
                    // Handle tanggal lahir
                    $tanggal_lahir = null;
                    if (!empty($row[7])) {
                        // Try different date formats
                        $date_str = $row[7];
                        if (is_numeric($date_str)) {
                            // Excel date serial number
                            $unix_date = ($date_str - 25569) * 86400;
                            $tanggal_lahir = date('Y-m-d', $unix_date);
                        } else {
                            // String date
                            $tanggal_lahir = date('Y-m-d', strtotime($date_str));
                        }
                    }
                    
                    $provinsi = sanitizeInput($row[8] ?? '');
                    $kabupaten = sanitizeInput($row[9] ?? '');
                    $jenjang = sanitizeInput($row[10] ?? '');
                    
                    // Validate required fields
                    if (empty($nim) || empty($nama_lengkap) || empty($jenjang)) {
                        $errors++;
                        continue;
                    }
                    
                    // Prepare parameters (37 total)
                    $params = [
                        $nim, $nama_lengkap, $jenis_kelamin, $tempat_tugas, 
                        $no_telepon, $status_pegawai, $tanggal_lahir, $provinsi, 
                        $kabupaten, $jenjang
                    ];
                    
                    // Add document links (27 fields: 1 + 12 + 12 + 2)
                    for ($i = 11; $i <= 37; $i++) {
                        $link = sanitizeInput($row[$i] ?? '');
                        $params[] = !empty($link) ? $link : null;
                    }
                    
                    // Execute insert
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        $imported++;
                    } else {
                        $updated++;
                    }
                    
                    $processed++;
                    
                    // Mini batch commit setiap 50 records untuk performance
                    if ($processed % $BATCH_SIZE_PER_EXECUTION === 0) {
                        $pdo->commit();
                        $pdo->beginTransaction();
                    }
                    
                } catch (Exception $e) {
                    $errors++;
                    error_log("CSV Import Error Line $line_number: " . $e->getMessage());
                    continue;
                }
            }
            
            fclose($handle);
            $pdo->commit();
            
            $execution_time = round(microtime(true) - $start_time, 2);
            
            $batch_results = [
                'batch_file' => $batch_file,
                'total_processed' => $processed,
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'execution_time' => $execution_time,
                'records_per_second' => $processed > 0 ? round($processed / $execution_time, 2) : 0
            ];
            
            logAktivitas($pdo, $_SESSION['user_id'], 'Import CSV Batch', 
                "File: $batch_file, Imported: $imported, Updated: $updated, Errors: $errors");
            
            $message = "CSV import selesai! File: $batch_file | Imported: $imported | Updated: $updated | Errors: $errors | Time: {$execution_time}s";
            
            // Mark batch as completed (rename file)
            $completed_path = $UPLOAD_DIR . 'completed_' . $batch_file;
            rename($batch_path, $completed_path);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error import CSV: ' . $e->getMessage();
        }
    }
}

// Get current database stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $current_total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa WHERE nim LIKE '25869050%'");
    $current_rpl = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $recent_imports = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $current_total = 0;
    $current_rpl = 0;
    $recent_imports = 0;
}

// Get completed batches
$completed_files = glob($UPLOAD_DIR . "completed_*.csv");
$completed_count = count($completed_files);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Batch Import - 2260 Mahasiswa STKYAKOBUS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.5rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.9rem; }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-info { background: #e8f4fd; color: #0c5460; }
        .alert-warning { background: #fff3cd; color: #856404; }
        
        .upload-area {
            border: 2px dashed #27ae60;
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            background: #f8fff8;
        }
        
        .batch-list {
            display: grid;
            gap: 1rem;
        }
        
        .batch-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .batch-item.completed {
            border-left-color: #27ae60;
            background: #e8f5e8;
        }
        
        .batch-info {
            flex: 1;
        }
        
        .batch-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .progress-bar {
            background: #ecf0f1;
            height: 20px;
            border-radius: 10px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            background: #27ae60;
            height: 100%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .results {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            font-family: monospace;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .instructions {
            background: #e8f4fd;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .step {
            margin: 0.5rem 0;
            padding: 0.5rem 0;
        }
        
        .step-number {
            display: inline-block;
            background: #3498db;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📄 CSV Batch Import System</h1>
        <h2>Simple & Reliable - No Library Required</h2>
        <p>Import 2260 mahasiswa dengan sistem CSV yang mudah dan aman</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Instructions -->
    <div class="instructions">
        <h3>📋 Cara Menggunakan CSV Import</h3>
        <div class="step">
            <span class="step-number">1</span>
            <strong>Convert Excel ke CSV:</strong> Buka Excel → File → Save As → CSV (Comma delimited)
        </div>
        <div class="step">
            <span class="step-number">2</span>
            <strong>Split Manual:</strong> Bagi jadi 5 file: batch_1.csv (500 rows), batch_2.csv (500 rows), dst
        </div>
        <div class="step">
            <span class="step-number">3</span>
            <strong>Upload CSV:</strong> Upload satu per satu file CSV menggunakan form di bawah
        </div>
        <div class="step">
            <span class="step-number">4</span>
            <strong>Import Batch:</strong> Klik "Import Batch" untuk setiap file secara berurutan
        </div>
        <div class="step">
            <span class="step-number">5</span>
            <strong>Monitor:</strong> Pantau progress hingga semua 2260 data terimport
        </div>
    </div>
    
    <!-- Statistics Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= number_format($current_total) ?></div>
            <div class="stat-label">Total Mahasiswa</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($current_rpl) ?></div>
            <div class="stat-label">Mahasiswa RPL</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count($available_batches) ?></div>
            <div class="stat-label">CSV Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $completed_count ?></div>
            <div class="stat-label">CSV Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= number_format($recent_imports) ?></div>
            <div class="stat-label">Import 1 Jam Terakhir</div>
        </div>
    </div>
    
    <!-- Progress Overview -->
    <?php if ($current_rpl > 0): ?>
        <?php $progress_percent = min(100, ($current_rpl / 2260) * 100); ?>
        <div class="card">
            <h3>📊 Overall Progress - Target 2260 Mahasiswa</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress_percent ?>%">
                    <?= round($progress_percent, 1) ?>% (<?= $current_rpl ?>/2260)
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Upload CSV Section -->
    <div class="card">
        <h3>📁 Upload CSV Files</h3>
        <div class="alert alert-warning">
            <strong>📝 Format File CSV:</strong><br>
            • Nama file: <code>batch_1.csv</code>, <code>batch_2.csv</code>, <code>batch_3.csv</code>, dst<br>
            • Encoding: UTF-8 (untuk karakter Indonesia)<br>
            • Delimiter: Comma (,)<br>
            • Header: Baris pertama harus berisi nama kolom<br>
            • Max per file: ~500 records untuk performa optimal
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="upload-area">
                <h4>📤 Upload CSV Batch File</h4>
                <input type="file" name="csv_file" accept=".csv" required>
                <br><br>
                <button type="submit" class="btn btn-success">📄 Upload CSV</button>
            </div>
        </form>
    </div>
    
    <!-- CSV Batch Management -->
    <div class="card">
        <h3>📦 CSV Batch Files</h3>
        
        <?php if (empty($available_batches)): ?>
            <div class="alert alert-info">
                <strong>ℹ️ Belum ada CSV files</strong><br>
                Upload file CSV terlebih dahulu. Pastikan nama file format: batch_1.csv, batch_2.csv, dst.
            </div>
        <?php else: ?>
            <div class="batch-list">
                <?php foreach ($available_batches as $batch): ?>
                    <div class="batch-item">
                        <div class="batch-info">
                            <strong>📄 <?= htmlspecialchars($batch['file']) ?></strong><br>
                            <small>
                                Batch #<?= $batch['batch_num'] ?> | 
                                Size: <?= round($batch['size'] / 1024, 1) ?> KB |
                                Records: <?= number_format($batch['lines']) ?>
                            </small>
                        </div>
                        <div class="batch-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="batch_file" value="<?= htmlspecialchars($batch['file']) ?>">
                                <button type="submit" name="import_csv_batch" class="btn btn-success btn-sm"
                                        onclick="return confirm('Import CSV batch <?= $batch['batch_num'] ?>?\n\nEstimasi waktu: 2-4 menit\nRecords: <?= $batch['lines'] ?>')">
                                    🚀 Import Batch
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-info" style="margin-top: 1rem;">
                <strong>📋 Petunjuk Import CSV:</strong><br>
                • Import batch secara berurutan (1, 2, 3, dst)<br>
                • Tunggu hingga selesai sebelum import batch berikutnya<br>
                • Jika error, file tetap ada untuk di-retry<br>
                • Setelah sukses, file akan dipindah ke folder completed<br>
                • CSV format lebih stabil daripada Excel untuk import besar
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Import Results -->
    <?php if (!empty($batch_results) && isset($batch_results['batch_file'])): ?>
        <div class="card">
            <h3>📊 Hasil Import Terakhir</h3>
            <table>
                <tr><th>Metric</th><th>Value</th></tr>
                <tr><td>CSV File</td><td><?= htmlspecialchars($batch_results['batch_file']) ?></td></tr>
                <tr><td>Total Processed</td><td><?= number_format($batch_results['total_processed']) ?></td></tr>
                <tr><td>Imported (New)</td><td style="color: #27ae60; font-weight: bold;"><?= number_format($batch_results['imported']) ?></td></tr>
                <tr><td>Updated (Existing)</td><td style="color: #f39c12; font-weight: bold;"><?= number_format($batch_results['updated']) ?></td></tr>
                <tr><td>Errors</td><td style="color: #e74c3c; font-weight: bold;"><?= number_format($batch_results['errors']) ?></td></tr>
                <tr><td>Execution Time</td><td><?= $batch_results['execution_time'] ?> seconds</td></tr>
                <tr><td>Performance</td><td><?= $batch_results['records_per_second'] ?> records/second</td></tr>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Navigation -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="dashboard_admin.php" class="btn btn-primary">← Dashboard Admin</a>
        <a href="manage_mahasiswa.php" class="btn btn-primary">👥 Kelola Mahasiswa</a>
        <a href="laporan.php" class="btn btn-primary">📊 Lihat Laporan</a>
    </div>
    
    <!-- Help & Troubleshooting -->
    <div class="card">
        <h3>❓ FAQ CSV Import</h3>
        
        <details style="margin: 0.5rem 0;">
            <summary><strong>Q: Bagaimana convert Excel ke CSV dengan benar?</strong></summary>
            <p>A: Buka Excel → File → Save As → pilih "CSV (Comma delimited)" → Save. Pastikan encoding UTF-8 untuk karakter Indonesia.</p>
        </details>
        
        <details style="margin: 0.5rem 0;">
            <summary><strong>Q: Error "Invalid date format" saat import?</strong></summary>
            <p>A: Pastikan format tanggal di Excel adalah YYYY-MM-DD atau DD/MM/YYYY. Hindari format custom Excel.</p>
        </details>
        
        <details style="margin: 0.5rem 0;">
            <summary><strong>Q: Kenapa beberapa records error/skip?</strong></summary>
            <p>A: Biasanya karena NIM kosong, nama kosong, atau jenjang kosong. Field wajib harus diisi.</p>
        </details>
        
        <details style="margin: 0.5rem 0;">
            <summary><strong>Q: Aman import CSV yang sama berulang kali?</strong></summary>
            <p>A: Ya, aman. System menggunakan ON DUPLICATE KEY UPDATE, jadi record duplicate akan diupdate, bukan error.</p>
        </details>
        
        <details style="margin: 0.5rem 0;">
            <summary><strong>Q: Berapa lama waktu import 500 records?</strong></summary>
            <p>A: Sekitar 1-3 menit tergantung server. CSV lebih cepat daripada Excel karena tidak perlu library external.</p>
        </details>
    </div>
</body>
</html>