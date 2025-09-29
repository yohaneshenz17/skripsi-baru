<?php
require_once 'config.php';
requireAdmin();

// Configure PHP for large file processing
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600); // 10 minutes
ini_set('auto_detect_line_endings', true);

$message = '';
$error = '';
$debug_info = [];
$preview_data = [];
$import_results = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'preview_csv' && isset($_FILES['csv_file'])) {
        try {
            $preview_data = previewCSV($_FILES['csv_file']['tmp_name']);
            $message = "Preview berhasil! Ditemukan {$preview_data['total_rows']} rows data.";
        } catch (Exception $e) {
            $error = 'Error preview: ' . $e->getMessage();
        }
    }
    
    if ($action === 'import_csv' && isset($_FILES['csv_file'])) {
        try {
            $import_results = importCSVRobust($_FILES['csv_file']['tmp_name']);
            $message = "Import selesai! Berhasil: {$import_results['success']}, Gagal: {$import_results['failed']}, Total: {$import_results['total_processed']}";
        } catch (Exception $e) {
            $error = 'Error import: ' . $e->getMessage();
        }
    }
}

/**
 * Preview CSV file untuk debugging dan validasi
 */
function previewCSV($file_path) {
    global $debug_info;
    
    $debug_info = [];
    $preview = [
        'file_info' => [],
        'headers' => [],
        'sample_rows' => [],
        'total_rows' => 0,
        'encoding' => '',
        'delimiter' => '',
        'issues' => []
    ];
    
    // File info
    $preview['file_info'] = [
        'size' => filesize($file_path),
        'size_mb' => round(filesize($file_path) / 1024 / 1024, 2)
    ];
    
    // Read and detect encoding
    $file_content = file_get_contents($file_path);
    $detected_encoding = mb_detect_encoding($file_content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'], true);
    $preview['encoding'] = $detected_encoding;
    
    if ($detected_encoding !== 'UTF-8') {
        $file_content = mb_convert_encoding($file_content, 'UTF-8', $detected_encoding);
    }
    
    // Detect delimiter
    $first_line = strtok($file_content, "\n");
    $semicolon_count = substr_count($first_line, ';');
    $comma_count = substr_count($first_line, ',');
    $tab_count = substr_count($first_line, "\t");
    
    if ($semicolon_count > $comma_count && $semicolon_count > $tab_count) {
        $delimiter = ';';
    } elseif ($comma_count > $tab_count) {
        $delimiter = ',';
    } else {
        $delimiter = "\t";
    }
    $preview['delimiter'] = $delimiter;
    
    // Parse CSV
    $lines = explode("\n", $file_content);
    $parsed_rows = [];
    
    foreach ($lines as $index => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $columns = str_getcsv($line, $delimiter, '"', '\\');
        
        // Remove BOM if exists
        if ($index === 0 && !empty($columns[0])) {
            $columns[0] = ltrim($columns[0], "\xEF\xBB\xBF");
        }
        
        $parsed_rows[] = $columns;
    }
    
    if (empty($parsed_rows)) {
        throw new Exception('File CSV kosong atau tidak dapat dibaca');
    }
    
    // Extract headers
    $preview['headers'] = $parsed_rows[0];
    $preview['total_rows'] = count($parsed_rows) - 1; // Minus header
    
    // Sample data (first 5 rows)
    for ($i = 1; $i <= min(5, count($parsed_rows) - 1); $i++) {
        $preview['sample_rows'][] = array_combine($preview['headers'], $parsed_rows[$i]);
    }
    
    // Validate structure
    $expected_columns = [
        'No', 'NIM', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Tugas',
        'No Telepon', 'Status Pegawai', 'Tanggal Lahir', 'Provinsi', 
        'Kabupaten', 'Jenjang', 'Surat Tugas Mengajar'
    ];
    
    foreach ($expected_columns as $col) {
        if (!in_array($col, $preview['headers'])) {
            $preview['issues'][] = "Kolom '$col' tidak ditemukan";
        }
    }
    
    if (count($preview['headers']) !== 38) {
        $preview['issues'][] = "Jumlah kolom: " . count($preview['headers']) . " (expected: 38)";
    }
    
    return $preview;
}

/**
 * Import CSV dengan error handling yang robust
 */
function importCSVRobust($file_path) {
    global $pdo, $debug_info;
    
    $results = [
        'total_processed' => 0,
        'success' => 0,
        'failed' => 0,
        'duplicates' => 0,
        'errors' => [],
        'execution_time' => 0
    ];
    
    $start_time = microtime(true);
    
    // Parse file
    $file_content = file_get_contents($file_path);
    $detected_encoding = mb_detect_encoding($file_content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    
    if ($detected_encoding !== 'UTF-8') {
        $file_content = mb_convert_encoding($file_content, 'UTF-8', $detected_encoding);
    }
    
    // Detect delimiter
    $first_line = strtok($file_content, "\n");
    $delimiter = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';
    
    // Parse all lines
    $lines = explode("\n", $file_content);
    $headers = [];
    $data_rows = [];
    
    foreach ($lines as $index => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $columns = str_getcsv($line, $delimiter, '"', '\\');
        
        // Remove BOM from first column if exists
        if ($index === 0 && !empty($columns[0])) {
            $columns[0] = ltrim($columns[0], "\xEF\xBB\xBF");
        }
        
        if (empty($headers)) {
            $headers = $columns;
            continue;
        }
        
        // Ensure row has same number of columns as header
        while (count($columns) < count($headers)) {
            $columns[] = '';
        }
        
        $data_rows[] = array_combine($headers, array_slice($columns, 0, count($headers)));
    }
    
    $results['total_processed'] = count($data_rows);
    
    // Column mapping - EXACT match dengan CSV headers
    $db_column_mapping = [
        'NIM' => 'nim',
        'Nama Lengkap' => 'nama_lengkap',
        'Jenis Kelamin' => 'jenis_kelamin',
        'Tempat Tugas' => 'tempat_tugas',
        'No Telepon' => 'no_telepon',
        'Status Pegawai' => 'status_pegawai',
        'Tanggal Lahir' => 'tanggal_lahir',
        'Provinsi' => 'provinsi',
        'Kabupaten' => 'kabupaten',
        'Jenjang' => 'jenjang',
        'Surat Tugas Mengajar' => 'link_sk_mengajar',
        'Surat Keterangan Administrasi Pembelajaran' => 'link_administrasi',
        'Surat Keterangan Inovasi Pembelajaran' => 'link_inovasi',
        // RPL.02 Perangkat Pembelajaran
        'Perangkat Semester Ganjil 2019/2020' => 'rpl02_perangkat_ganjil_2019',
        'Perangkat Semester Genap 2019/2020' => 'rpl02_perangkat_genap_2019',
        'Perangkat Semester Ganjil 2020/2021' => 'rpl02_perangkat_ganjil_2020',
        'Perangkat Semester Genap 2020/2021' => 'rpl02_perangkat_genap_2020',
        'Perangkat Semester Ganjil 2021/2022' => 'rpl02_perangkat_ganjil_2021',
        'Perangkat Semester Genap 2021/2022' => 'rpl02_perangkat_genap_2021',
        'Perangkat Semester Ganjil 2022/2023' => 'rpl02_perangkat_ganjil_2022',
        'Perangkat Semester Genap 2022/2023' => 'rpl02_perangkat_genap_2022',
        'Perangkat Semester Ganjil 2023/2024' => 'rpl02_perangkat_ganjil_2023',
        'Perangkat Semester Genap 2023/2024' => 'rpl02_perangkat_genap_2023',
        'Perangkat Semester Ganjil 2024/2025' => 'rpl02_perangkat_ganjil_2024',
        'Perangkat Semester Genap 2024/2025' => 'rpl02_perangkat_genap_2024',
        // RPL.03 Pengembangan Diri/Profesional
        'Pengembangan Diri Semester Ganjil 2019/2020' => 'rpl03_pengembangan_ganjil_2019',
        'Pengembangan Diri Semester Genap 2019/2020' => 'rpl03_pengembangan_genap_2019',
        'Pengembangan Diri Semester Ganjil 2020/2021' => 'rpl03_pengembangan_ganjil_2020',
        'Pengembangan Diri Semester Genap 2020/2021' => 'rpl03_pengembangan_genap_2020',
        'Pengembangan Diri Semester Ganjil 2021/2022' => 'rpl03_pengembangan_ganjil_2021',
        'Pengembangan Diri Semester Genap 2021/2022' => 'rpl03_pengembangan_genap_2021',
        'Pengembangan Diri Semester Ganjil 2022/2023' => 'rpl03_pengembangan_ganjil_2022',
        'Pengembangan Diri Semester Genap 2022/2023' => 'rpl03_pengembangan_genap_2022',
        'Pengembangan Diri Semester Ganjil 2023/2024' => 'rpl03_pengembangan_ganjil_2023',
        'Pengembangan Diri Semester Genap 2023/2024' => 'rpl03_pengembangan_genap_2023',
        'Pengembangan Diri Semester Ganjil 2024/2025' => 'rpl03_pengembangan_ganjil_2024',
        'Pengembangan Diri Semester Genap 2024/2025' => 'rpl03_pengembangan_genap_2024'
    ];
    
    // Process in batches of 25 rows
    $batch_size = 25;
    $total_batches = ceil(count($data_rows) / $batch_size);
    
    for ($batch = 0; $batch < $total_batches; $batch++) {
        $batch_start = $batch * $batch_size;
        $batch_rows = array_slice($data_rows, $batch_start, $batch_size);
        
        try {
            $pdo->beginTransaction();
            
            foreach ($batch_rows as $row_index => $row) {
                $actual_row_num = $batch_start + $row_index + 2; // +2 for header and 0-index
                
                try {
                    // Validate required fields
                    $nim = trim($row['NIM'] ?? '');
                    $nama = trim($row['Nama Lengkap'] ?? '');
                    $jenjang = trim($row['Jenjang'] ?? '');
                    
                    if (empty($nim)) {
                        $results['errors'][] = "Row $actual_row_num: NIM kosong";
                        $results['failed']++;
                        continue;
                    }
                    
                    if (empty($nama)) {
                        $results['errors'][] = "Row $actual_row_num: Nama kosong";
                        $results['failed']++;
                        continue;
                    }
                    
                    // Check for duplicate NIM
                    $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
                    $stmt->execute([$nim]);
                    if ($stmt->fetch()) {
                        $results['duplicates']++;
                        $results['errors'][] = "Row $actual_row_num: NIM $nim sudah ada";
                        continue;
                    }
                    
                    // Map data
                    $db_data = [];
                    foreach ($db_column_mapping as $csv_col => $db_col) {
                        $value = trim($row[$csv_col] ?? '');
                        
                        if ($db_col === 'tanggal_lahir' && !empty($value)) {
                            // Parse date
                            $date_obj = DateTime::createFromFormat('d/m/Y', $value);
                            if (!$date_obj) {
                                $date_obj = DateTime::createFromFormat('Y-m-d', $value);
                            }
                            if (!$date_obj) {
                                $date_obj = DateTime::createFromFormat('d-m-Y', $value);
                            }
                            
                            $db_data[$db_col] = $date_obj ? $date_obj->format('Y-m-d') : null;
                        } else if ($db_col === 'jenis_kelamin') {
                            $value = strtolower($value);
                            if (in_array($value, ['l', 'laki-laki', 'laki', 'male'])) {
                                $db_data[$db_col] = 'Laki-laki';
                            } else if (in_array($value, ['p', 'perempuan', 'wanita', 'female'])) {
                                $db_data[$db_col] = 'Perempuan';
                            } else {
                                $db_data[$db_col] = 'Laki-laki'; // Default
                            }
                        } else if ($db_col === 'jenjang') {
                            $value = strtoupper(trim($value));
                            $db_data[$db_col] = in_array($value, ['SD', 'SMP', 'SMA', 'SMK']) ? $value : 'SD';
                        } else {
                            $db_data[$db_col] = $value ?: null;
                        }
                    }
                    
                    // Add missing required fields with defaults (only existing columns)
                    $db_data['status_penilaian'] = 'belum_dinilai';
                    
                    // Prepare and execute INSERT
                    $columns = array_keys($db_data);
                    $placeholders = array_fill(0, count($columns), '?');
                    $values = array_values($db_data);
                    
                    $sql = "INSERT INTO mahasiswa (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($values);
                    
                    $results['success']++;
                    
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row $actual_row_num (NIM: $nim): " . $e->getMessage();
                }
            }
            
            $pdo->commit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $results['errors'][] = "Batch $batch error: " . $e->getMessage();
        }
    }
    
    $results['execution_time'] = round(microtime(true) - $start_time, 2);
    
    // Log activity
    logAktivitas($pdo, $_SESSION['user_id'], 'Import CSV Robust', 
        "Total: {$results['total_processed']}, Success: {$results['success']}, Failed: {$results['failed']}");
    
    return $results;
}

// Get current stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $current_total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as recent FROM mahasiswa WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $recent_imports = $stmt->fetch()['recent'];
} catch (PDOException $e) {
    $current_total = 0;
    $recent_imports = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Robust CSV Import - Debugging Mode</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            transition: all 0.3s;
            margin: 0.5rem;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #27ae60;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #e74c3c;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: #f39c12;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 1rem;
            border: 2px dashed #3498db;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        input[type="file"]:hover {
            border-color: #2980b9;
            background: #e8f4fd;
        }
        
        .debug-section {
            background: #2c3e50;
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .debug-section h4 {
            color: #3498db;
            margin-bottom: 1rem;
        }
        
        .debug-info {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .preview-table th,
        .preview-table td {
            padding: 0.5rem;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .preview-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .preview-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .error-list {
            max-height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .error-item {
            padding: 0.25rem 0;
            border-bottom: 1px solid #eee;
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            border-bottom-color: #3498db;
            color: #3498db;
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .instructions {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            margin: 1rem 0;
        }
        
        .step-number {
            background: #3498db;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 Robust CSV Import System</h1>
        <p>Advanced debugging and error analysis for batch import</p>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Current Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($current_total) ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($recent_imports) ?></div>
                <div class="stat-label">Import 1 Jam Terakhir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($preview_data) > 0 ? $preview_data['total_rows'] : 0 ?></div>
                <div class="stat-label">Rows di Preview</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= !empty($import_results) ? $import_results['success'] : 0 ?></div>
                <div class="stat-label">Last Import Success</div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="card">
            <h3>📋 Panduan Import CSV</h3>
            <div class="instructions">
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Format File:</strong> CSV dengan delimiter semicolon (;), encoding UTF-8
                        <br><small>File harus memiliki 38 kolom sesuai template Excel</small>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Preview First:</strong> Gunakan "Preview CSV" untuk mengecek struktur file
                        <br><small>Ini akan mendeteksi masalah sebelum import actual</small>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Import Batch:</strong> Jika preview OK, gunakan "Import CSV"
                        <br><small>Sistem akan memproses data per batch 25 rows untuk menghindari timeout</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- File Upload -->
        <div class="card">
            <h3>📤 Upload & Process CSV File</h3>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csv_file">Pilih File CSV (batch_1.csv, batch_2.csv, etc.)</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" name="action" value="preview_csv" class="btn btn-info">
                        🔍 Preview CSV (Debug Mode)
                    </button>
                    <button type="submit" name="action" value="import_csv" class="btn btn-success"
                            onclick="return confirm('Import CSV sekarang? Pastikan sudah preview dulu!')">
                        🚀 Import CSV to Database
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Preview Results -->
        <?php if (!empty($preview_data)): ?>
            <div class="card">
                <h3>🔍 Preview Results</h3>
                
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="showTab('file-info')">File Info</button>
                    <button class="tab-btn" onclick="showTab('headers')">Headers</button>
                    <button class="tab-btn" onclick="showTab('sample-data')">Sample Data</button>
                    <button class="tab-btn" onclick="showTab('issues')">Issues</button>
                </div>
                
                <div id="file-info" class="tab-content active">
                    <div class="debug-section">
                        <h4>📄 File Information</h4>
                        <div class="debug-info">
                            File Size: <?= $preview_data['file_info']['size_mb'] ?> MB<br>
                            Encoding: <?= $preview_data['encoding'] ?><br>
                            Delimiter: "<?= $preview_data['delimiter'] ?>"<br>
                            Total Rows: <?= $preview_data['total_rows'] ?><br>
                            Total Columns: <?= count($preview_data['headers']) ?>
                        </div>
                    </div>
                </div>
                
                <div id="headers" class="tab-content">
                    <h4>📊 Column Headers (<?= count($preview_data['headers']) ?> columns)</h4>
                    <div class="preview-table-container" style="max-height: 400px; overflow-y: auto;">
                        <table class="preview-table">
                            <thead>
                                <tr><th>#</th><th>Column Name</th><th>Mapped to DB</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preview_data['headers'] as $index => $header): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($header) ?></td>
                                        <td>
                                            <?php
                                            $db_mapping = [
                                                'NIM' => 'nim', 'Nama Lengkap' => 'nama_lengkap', 
                                                'Jenis Kelamin' => 'jenis_kelamin', 'Tempat Tugas' => 'tempat_tugas',
                                                'Surat Tugas Mengajar' => 'link_sk_mengajar'
                                            ];
                                            echo htmlspecialchars($db_mapping[$header] ?? 'Document field');
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="sample-data" class="tab-content">
                    <h4>📋 Sample Data (First 5 rows)</h4>
                    <?php if (!empty($preview_data['sample_rows'])): ?>
                        <div style="max-height: 400px; overflow: auto;">
                            <table class="preview-table">
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jenjang</th>
                                        <th>Tempat Tugas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preview_data['sample_rows'] as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['NIM'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['Nama Lengkap'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['Jenjang'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['Tempat Tugas'] ?? '') ?></td>
                                            <td>
                                                <?php if (empty($row['NIM'])): ?>
                                                    <span style="color: red;">⚠️ NIM Empty</span>
                                                <?php else: ?>
                                                    <span style="color: green;">✅ OK</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div id="issues" class="tab-content">
                    <h4>⚠️ Detected Issues</h4>
                    <?php if (empty($preview_data['issues'])): ?>
                        <div class="alert alert-success">✅ No issues detected! File structure looks good.</div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Found <?= count($preview_data['issues']) ?> potential issues:
                            <ul style="margin: 0.5rem 0 0 1.5rem;">
                                <?php foreach ($preview_data['issues'] as $issue): ?>
                                    <li><?= htmlspecialchars($issue) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Import Results -->
        <?php if (!empty($import_results)): ?>
            <div class="card">
                <h3>📊 Import Results</h3>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $import_results['total_processed'] ?></div>
                        <div class="stat-label">Total Processed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" style="color: #27ae60;"><?= $import_results['success'] ?></div>
                        <div class="stat-label">Success</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" style="color: #e74c3c;"><?= $import_results['failed'] ?></div>
                        <div class="stat-label">Failed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" style="color: #f39c12;"><?= $import_results['duplicates'] ?></div>
                        <div class="stat-label">Duplicates</div>
                    </div>
                </div>
                
                <div class="debug-section">
                    <h4>⏱️ Performance</h4>
                    <div class="debug-info">
                        Execution Time: <?= $import_results['execution_time'] ?> seconds<br>
                        Performance: <?= $import_results['total_processed'] > 0 ? round($import_results['total_processed'] / $import_results['execution_time'], 2) : 0 ?> rows/second
                    </div>
                </div>
                
                <?php if (!empty($import_results['errors'])): ?>
                    <h4>❌ Error Details (<?= count($import_results['errors']) ?> errors)</h4>
                    <div class="error-list">
                        <?php foreach (array_slice($import_results['errors'], 0, 50) as $error): ?>
                            <div class="error-item"><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                        <?php if (count($import_results['errors']) > 50): ?>
                            <div class="error-item" style="color: #666; font-style: italic;">
                                ... and <?= count($import_results['errors']) - 50 ?> more errors
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Navigation -->
        <div style="text-align: center; margin: 2rem 0;">
            <a href="dashboard_admin.php" class="btn btn-primary">← Dashboard Admin</a>
            <a href="manage_mahasiswa.php" class="btn btn-primary">👥 Kelola Mahasiswa</a>
            <a href="csv_batch_import.php" class="btn btn-warning">📄 CSV Batch Import (Original)</a>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>