<?php
require_once 'config.php';
requireAdmin();

/**
 * CSV ANALYZER - Debug Tool for batch_1.csv
 * 
 * Tool khusus untuk menganalisis masalah import file batch_1.csv
 * dan memberikan rekomendasi perbaikan
 */

$analysis_results = [];
$file_path = 'csv_uploads/batch_1.csv';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file_path = $_FILES['csv_file']['tmp_name'];
}

if (file_exists($file_path)) {
    $analysis_results = analyzeCSVFile($file_path);
}

function analyzeCSVFile($file_path) {
    $results = [
        'file_info' => [],
        'encoding_analysis' => [],
        'structure_analysis' => [],
        'data_quality' => [],
        'database_compatibility' => [],
        'recommendations' => [],
        'sample_data' => []
    ];
    
    // 1. FILE INFO ANALYSIS
    $results['file_info'] = [
        'size_bytes' => filesize($file_path),
        'size_mb' => round(filesize($file_path) / 1024 / 1024, 2),
        'modified' => date('Y-m-d H:i:s', filemtime($file_path))
    ];
    
    // 2. ENCODING ANALYSIS
    $file_content = file_get_contents($file_path);
    $detected_encodings = [];
    $test_encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'CP1252'];
    
    foreach ($test_encodings as $encoding) {
        $converted = @mb_convert_encoding($file_content, 'UTF-8', $encoding);
        if ($converted !== false) {
            $detected_encodings[] = $encoding;
        }
    }
    
    $primary_encoding = mb_detect_encoding($file_content, $test_encodings, true);
    
    $results['encoding_analysis'] = [
        'detected_primary' => $primary_encoding,
        'possible_encodings' => $detected_encodings,
        'has_bom' => (substr($file_content, 0, 3) === "\xEF\xBB\xBF"),
        'file_size_check' => strlen($file_content) === filesize($file_path)
    ];
    
    // Convert to UTF-8 for analysis
    if ($primary_encoding && $primary_encoding !== 'UTF-8') {
        $file_content = mb_convert_encoding($file_content, 'UTF-8', $primary_encoding);
    }
    
    // 3. STRUCTURE ANALYSIS
    $lines = explode("\n", $file_content);
    $non_empty_lines = array_filter($lines, function($line) { return trim($line) !== ''; });
    
    // Detect delimiter
    $first_line = trim($lines[0]);
    $delimiter_analysis = [
        'semicolon' => substr_count($first_line, ';'),
        'comma' => substr_count($first_line, ','),
        'tab' => substr_count($first_line, "\t"),
        'pipe' => substr_count($first_line, '|')
    ];
    
    $detected_delimiter = ';'; // Default
    $max_count = max($delimiter_analysis);
    foreach ($delimiter_analysis as $delim => $count) {
        if ($count === $max_count) {
            $detected_delimiter = ($delim === 'semicolon') ? ';' : 
                                 (($delim === 'comma') ? ',' : 
                                 (($delim === 'tab') ? "\t" : '|'));
            break;
        }
    }
    
    // Parse with detected delimiter
    $parsed_lines = [];
    foreach ($non_empty_lines as $index => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $columns = str_getcsv($line, $detected_delimiter, '"', '\\');
        
        // Clean BOM from first column if exists
        if ($index === 0 && !empty($columns[0])) {
            $columns[0] = ltrim($columns[0], "\xEF\xBB\xBF");
        }
        
        $parsed_lines[] = $columns;
    }
    
    $results['structure_analysis'] = [
        'total_lines' => count($lines),
        'non_empty_lines' => count($non_empty_lines),
        'detected_delimiter' => $detected_delimiter,
        'delimiter_counts' => $delimiter_analysis,
        'header_columns' => count($parsed_lines[0] ?? []),
        'consistent_columns' => true,
        'column_variations' => []
    ];
    
    // Check column consistency
    if (!empty($parsed_lines)) {
        $header_count = count($parsed_lines[0]);
        $column_variations = [];
        
        foreach ($parsed_lines as $index => $line) {
            $col_count = count($line);
            if ($col_count !== $header_count) {
                $results['structure_analysis']['consistent_columns'] = false;
                $column_variations[] = "Line " . ($index + 1) . ": $col_count columns";
            }
        }
        
        $results['structure_analysis']['column_variations'] = $column_variations;
    }
    
    // 4. DATA QUALITY ANALYSIS
    if (!empty($parsed_lines)) {
        $headers = $parsed_lines[0];
        $data_rows = array_slice($parsed_lines, 1);
        
        $quality_issues = [];
        $empty_nim_count = 0;
        $empty_nama_count = 0;
        $invalid_jenjang_count = 0;
        $invalid_gender_count = 0;
        $date_issues = 0;
        
        foreach ($data_rows as $index => $row) {
            $row_num = $index + 2; // +1 for header, +1 for 0-index
            
            // Check required fields
            $nim = trim($row[1] ?? ''); // Column B
            $nama = trim($row[2] ?? ''); // Column C
            $jenjang = trim($row[10] ?? ''); // Column K
            $gender = trim($row[3] ?? ''); // Column D
            $tanggal_lahir = trim($row[7] ?? ''); // Column H
            
            if (empty($nim)) {
                $empty_nim_count++;
                $quality_issues[] = "Row $row_num: NIM kosong";
            }
            
            if (empty($nama)) {
                $empty_nama_count++;
                $quality_issues[] = "Row $row_num: Nama kosong";
            }
            
            if (!in_array(strtoupper($jenjang), ['SD', 'SMP', 'SMA', 'SMK'])) {
                $invalid_jenjang_count++;
                $quality_issues[] = "Row $row_num: Jenjang '$jenjang' tidak valid";
            }
            
            $gender_lower = strtolower($gender);
            if (!in_array($gender_lower, ['l', 'p', 'laki-laki', 'perempuan', 'laki', 'wanita'])) {
                $invalid_gender_count++;
                $quality_issues[] = "Row $row_num: Jenis kelamin '$gender' tidak dikenali";
            }
            
            if (!empty($tanggal_lahir)) {
                $date_formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
                $valid_date = false;
                
                foreach ($date_formats as $format) {
                    if (DateTime::createFromFormat($format, $tanggal_lahir) !== false) {
                        $valid_date = true;
                        break;
                    }
                }
                
                if (!$valid_date) {
                    $date_issues++;
                    $quality_issues[] = "Row $row_num: Format tanggal '$tanggal_lahir' tidak dikenali";
                }
            }
        }
        
        $results['data_quality'] = [
            'total_data_rows' => count($data_rows),
            'empty_nim_count' => $empty_nim_count,
            'empty_nama_count' => $empty_nama_count,
            'invalid_jenjang_count' => $invalid_jenjang_count,
            'invalid_gender_count' => $invalid_gender_count,
            'date_issues' => $date_issues,
            'quality_issues' => array_slice($quality_issues, 0, 20), // Limit to first 20
            'total_quality_issues' => count($quality_issues)
        ];
        
        // Sample data for preview
        $results['sample_data'] = [
            'headers' => $headers,
            'first_5_rows' => array_slice($data_rows, 0, 5)
        ];
    }
    
    // 5. DATABASE COMPATIBILITY CHECK
    try {
        global $pdo;
        
        // Check if table exists and get structure
        $stmt = $pdo->query("DESCRIBE mahasiswa");
        $db_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $db_column_names = array_column($db_columns, 'Field');
        
        // Expected mapping (only columns that exist in database)
        $expected_mapping = [
            'NIM' => 'nim',
            'Nama Lengkap' => 'nama_lengkap',
            'Jenis Kelamin' => 'jenis_kelamin',
            'Tempat Tugas' => 'tempat_tugas',
            'No Telepon' => 'no_telepon',
            'Status Pegawai' => 'status_pegawai',
            'Tanggal Lahir' => 'tanggal_lahir',
            'Provinsi' => 'provinsi',
            'Kabupaten' => 'kabupaten',
            'Jenjang' => 'jenjang'
        ];
        
        $missing_db_columns = [];
        foreach ($expected_mapping as $csv_col => $db_col) {
            if (!in_array($db_col, $db_column_names)) {
                $missing_db_columns[] = $db_col;
            }
        }
        
        // Check for duplicate NIMs in database
        $existing_nims = [];
        if (!empty($parsed_lines) && count($parsed_lines) > 1) {
            $sample_nims = array_slice(array_column(array_slice($parsed_lines, 1), 1), 0, 10);
            $nims_placeholder = str_repeat('?,', count($sample_nims) - 1) . '?';
            $stmt = $pdo->prepare("SELECT nim FROM mahasiswa WHERE nim IN ($nims_placeholder)");
            $stmt->execute($sample_nims);
            $existing_nims = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        $results['database_compatibility'] = [
            'table_exists' => true,
            'total_db_columns' => count($db_column_names),
            'missing_db_columns' => $missing_db_columns,
            'sample_existing_nims' => $existing_nims,
            'db_connection_ok' => true
        ];
        
    } catch (Exception $e) {
        $results['database_compatibility'] = [
            'table_exists' => false,
            'error' => $e->getMessage(),
            'db_connection_ok' => false
        ];
    }
    
    // 6. GENERATE RECOMMENDATIONS
    $recommendations = [];
    
    if ($results['encoding_analysis']['detected_primary'] !== 'UTF-8') {
        $recommendations[] = [
            'type' => 'encoding',
            'severity' => 'medium',
            'message' => 'File encoding bukan UTF-8. Convert ke UTF-8 sebelum upload.',
            'solution' => 'Gunakan Excel: Save As → CSV (UTF-8)'
        ];
    }
    
    if ($results['structure_analysis']['detected_delimiter'] !== ';') {
        $recommendations[] = [
            'type' => 'delimiter',
            'severity' => 'high',
            'message' => 'Delimiter bukan semicolon (;). Sistem expect semicolon.',
            'solution' => 'Convert Excel dengan delimiter semicolon atau update kode import'
        ];
    }
    
    if (!$results['structure_analysis']['consistent_columns']) {
        $recommendations[] = [
            'type' => 'structure',
            'severity' => 'critical',
            'message' => 'Jumlah kolom tidak konsisten antar baris.',
            'solution' => 'Periksa dan perbaiki struktur CSV, pastikan semua baris punya 38 kolom'
        ];
    }
    
    if ($results['data_quality']['empty_nim_count'] > 0) {
        $recommendations[] = [
            'type' => 'data',
            'severity' => 'critical',
            'message' => $results['data_quality']['empty_nim_count'] . ' rows dengan NIM kosong.',
            'solution' => 'NIM adalah field wajib. Hapus baris dengan NIM kosong atau isi NIM yang valid'
        ];
    }
    
    if ($results['data_quality']['invalid_jenjang_count'] > 0) {
        $recommendations[] = [
            'type' => 'data',
            'severity' => 'medium',
            'message' => $results['data_quality']['invalid_jenjang_count'] . ' rows dengan jenjang tidak valid.',
            'solution' => 'Jenjang harus: SD, SMP, SMA, atau SMK'
        ];
    }
    
    if (isset($results['database_compatibility']['sample_existing_nims']) && 
        !empty($results['database_compatibility']['sample_existing_nims'])) {
        $recommendations[] = [
            'type' => 'duplicate',
            'severity' => 'medium',
            'message' => 'Ditemukan NIM yang sudah ada di database.',
            'solution' => 'Sistem akan skip duplicate NIM, atau hapus dari CSV jika memang tidak ingin update'
        ];
    }
    
    $results['recommendations'] = $recommendations;
    
    return $results;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Analyzer - batch_1.csv Diagnostic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .analysis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            border-bottom: 3px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .metric {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .metric:last-child { border-bottom: none; }
        
        .metric-label {
            font-weight: 500;
            color: #555;
        }
        
        .metric-value {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .status-good { color: #27ae60; }
        .status-warning { color: #f39c12; }
        .status-error { color: #e74c3c; }
        
        .recommendation {
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 0 8px 8px 0;
        }
        
        .recommendation.critical { border-left-color: #e74c3c; }
        .recommendation.high { border-left-color: #f39c12; }
        .recommendation.medium { border-left-color: #3498db; }
        
        .recommendation-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .recommendation-solution {
            font-size: 0.9rem;
            color: #666;
            background: #e8f4fd;
            padding: 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.5rem;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .data-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .issue-list {
            max-height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .issue-item {
            padding: 0.5rem;
            margin: 0.25rem 0;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #e74c3c;
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .upload-section {
            background: rgba(255,255,255,0.95);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        
        .upload-area {
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: rgba(52, 152, 219, 0.05);
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #e67e22; }
        
        .severity-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .severity-critical {
            background: #fee;
            color: #c33;
        }
        
        .severity-high {
            background: #fff3cd;
            color: #856404;
        }
        
        .severity-medium {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            stroke-dasharray: 283; /* 2 * PI * 45 */
            stroke-dashoffset: 283;
            transition: stroke-dashoffset 0.35s;
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔬 CSV Analyzer</h1>
        <p>Diagnostic tool untuk menganalisis masalah import file batch_1.csv</p>
    </div>
    
    <div class="container">
        <!-- Upload Section -->
        <div class="upload-section">
            <h3>📁 Upload File untuk Analisis</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="upload-area">
                    <h4>Upload batch_1.csv atau file CSV lainnya</h4>
                    <input type="file" name="csv_file" accept=".csv" style="margin: 1rem 0;">
                    <br>
                    <button type="submit" class="btn">🔍 Analyze File</button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($analysis_results)): ?>
            <div class="analysis-grid">
                <!-- File Info -->
                <div class="card">
                    <h3>📄 File Information</h3>
                    <div class="metric">
                        <span class="metric-label">File Size</span>
                        <span class="metric-value"><?= $analysis_results['file_info']['size_mb'] ?> MB</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Modified</span>
                        <span class="metric-value"><?= $analysis_results['file_info']['modified'] ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Encoding</span>
                        <span class="metric-value <?= $analysis_results['encoding_analysis']['detected_primary'] === 'UTF-8' ? 'status-good' : 'status-warning' ?>">
                            <?= $analysis_results['encoding_analysis']['detected_primary'] ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Has BOM</span>
                        <span class="metric-value <?= $analysis_results['encoding_analysis']['has_bom'] ? 'status-warning' : 'status-good' ?>">
                            <?= $analysis_results['encoding_analysis']['has_bom'] ? 'Yes' : 'No' ?>
                        </span>
                    </div>
                </div>
                
                <!-- Structure Analysis -->
                <div class="card">
                    <h3>🏗️ Structure Analysis</h3>
                    <div class="metric">
                        <span class="metric-label">Total Lines</span>
                        <span class="metric-value"><?= $analysis_results['structure_analysis']['total_lines'] ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Data Rows</span>
                        <span class="metric-value"><?= $analysis_results['structure_analysis']['non_empty_lines'] - 1 ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Delimiter</span>
                        <span class="metric-value <?= $analysis_results['structure_analysis']['detected_delimiter'] === ';' ? 'status-good' : 'status-error' ?>">
                            "<?= $analysis_results['structure_analysis']['detected_delimiter'] ?>"
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Columns</span>
                        <span class="metric-value <?= $analysis_results['structure_analysis']['header_columns'] === 38 ? 'status-good' : 'status-warning' ?>">
                            <?= $analysis_results['structure_analysis']['header_columns'] ?>/38
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Column Consistency</span>
                        <span class="metric-value <?= $analysis_results['structure_analysis']['consistent_columns'] ? 'status-good' : 'status-error' ?>">
                            <?= $analysis_results['structure_analysis']['consistent_columns'] ? 'OK' : 'Issues Found' ?>
                        </span>
                    </div>
                </div>
                
                <!-- Data Quality -->
                <div class="card">
                    <h3>✅ Data Quality</h3>
                    <div class="metric">
                        <span class="metric-label">Total Rows</span>
                        <span class="metric-value"><?= $analysis_results['data_quality']['total_data_rows'] ?></span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Empty NIM</span>
                        <span class="metric-value <?= $analysis_results['data_quality']['empty_nim_count'] === 0 ? 'status-good' : 'status-error' ?>">
                            <?= $analysis_results['data_quality']['empty_nim_count'] ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Empty Nama</span>
                        <span class="metric-value <?= $analysis_results['data_quality']['empty_nama_count'] === 0 ? 'status-good' : 'status-error' ?>">
                            <?= $analysis_results['data_quality']['empty_nama_count'] ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Invalid Jenjang</span>
                        <span class="metric-value <?= $analysis_results['data_quality']['invalid_jenjang_count'] === 0 ? 'status-good' : 'status-warning' ?>">
                            <?= $analysis_results['data_quality']['invalid_jenjang_count'] ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Date Issues</span>
                        <span class="metric-value <?= $analysis_results['data_quality']['date_issues'] === 0 ? 'status-good' : 'status-warning' ?>">
                            <?= $analysis_results['data_quality']['date_issues'] ?>
                        </span>
                    </div>
                </div>
                
                <!-- Database Compatibility -->
                <div class="card">
                    <h3>🗄️ Database Compatibility</h3>
                    <div class="metric">
                        <span class="metric-label">DB Connection</span>
                        <span class="metric-value <?= $analysis_results['database_compatibility']['db_connection_ok'] ? 'status-good' : 'status-error' ?>">
                            <?= $analysis_results['database_compatibility']['db_connection_ok'] ? 'OK' : 'Failed' ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Table Exists</span>
                        <span class="metric-value <?= $analysis_results['database_compatibility']['table_exists'] ? 'status-good' : 'status-error' ?>">
                            <?= $analysis_results['database_compatibility']['table_exists'] ? 'Yes' : 'No' ?>
                        </span>
                    </div>
                    <?php if (isset($analysis_results['database_compatibility']['total_db_columns'])): ?>
                        <div class="metric">
                            <span class="metric-label">DB Columns</span>
                            <span class="metric-value"><?= $analysis_results['database_compatibility']['total_db_columns'] ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="metric">
                        <span class="metric-label">Existing NIMs</span>
                        <span class="metric-value <?= empty($analysis_results['database_compatibility']['sample_existing_nims']) ? 'status-good' : 'status-warning' ?>">
                            <?= count($analysis_results['database_compatibility']['sample_existing_nims'] ?? []) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Recommendations -->
            <?php if (!empty($analysis_results['recommendations'])): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3>💡 Recommendations</h3>
                    <?php foreach ($analysis_results['recommendations'] as $rec): ?>
                        <div class="recommendation <?= $rec['severity'] ?>">
                            <div class="recommendation-title">
                                <span class="severity-badge severity-<?= $rec['severity'] ?>"><?= $rec['severity'] ?></span>
                                <?= htmlspecialchars($rec['message']) ?>
                            </div>
                            <div class="recommendation-solution">
                                <strong>Solution:</strong> <?= htmlspecialchars($rec['solution']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Data Quality Issues -->
            <?php if (!empty($analysis_results['data_quality']['quality_issues'])): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3>⚠️ Data Quality Issues (<?= $analysis_results['data_quality']['total_quality_issues'] ?> total)</h3>
                    <div class="issue-list">
                        <?php foreach ($analysis_results['data_quality']['quality_issues'] as $issue): ?>
                            <div class="issue-item"><?= htmlspecialchars($issue) ?></div>
                        <?php endforeach; ?>
                        <?php if ($analysis_results['data_quality']['total_quality_issues'] > 20): ?>
                            <div class="issue-item" style="color: #666; font-style: italic;">
                                ... and <?= $analysis_results['data_quality']['total_quality_issues'] - 20 ?> more issues
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Sample Data Preview -->
            <?php if (!empty($analysis_results['sample_data'])): ?>
                <div class="card" style="margin-top: 2rem;">
                    <h3>👁️ Sample Data Preview</h3>
                    <div style="overflow-x: auto;">
                        <table class="data-table">
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
                                <?php foreach ($analysis_results['sample_data']['first_5_rows'] as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row[1] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row[2] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row[10] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row[4] ?? '') ?></td>
                                        <td>
                                            <?php if (empty($row[1])): ?>
                                                <span class="status-error">❌ NIM Empty</span>
                                            <?php elseif (empty($row[2])): ?>
                                                <span class="status-error">❌ Nama Empty</span>
                                            <?php else: ?>
                                                <span class="status-good">✅ OK</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div style="text-align: center; margin: 2rem 0;">
            <a href="robust_csv_import.php" class="btn btn-success">🚀 Go to Robust Import</a>
            <a href="csv_batch_import.php" class="btn btn-warning">📄 Original CSV Import</a>
            <a href="dashboard_admin.php" class="btn">← Dashboard Admin</a>
        </div>
    </div>
</body>
</html>