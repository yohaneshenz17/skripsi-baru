<?php
/**
 * TESTING SCRIPT - Multiple Documents System
 * untuk verifikasi deployment sukses
 * 
 * Akses: https://stkyakobus.ac.id/rpl/test_multiple_docs.php
 * 
 * PERINGATAN: Hapus file ini setelah testing selesai!
 */

require_once 'config.php';
requireAdmin(); // Hanya admin yang boleh run test

$test_results = [];

// Function untuk menambah hasil test
function addTestResult($test_name, $success, $message, $data = null) {
    global $test_results;
    $test_results[] = [
        'name' => $test_name,
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Test 1: Database Structure
try {
    $stmt = $pdo->query("DESCRIBE mahasiswa");
    $columns = $stmt->fetchAll();
    
    $expected_columns = [
        'rpl02_perangkat_ganjil_2019', 'rpl02_perangkat_genap_2019',
        'rpl02_perangkat_ganjil_2020', 'rpl02_perangkat_genap_2020',
        'rpl02_perangkat_ganjil_2021', 'rpl02_perangkat_genap_2021',
        'rpl02_perangkat_ganjil_2022', 'rpl02_perangkat_genap_2022',
        'rpl02_perangkat_ganjil_2023', 'rpl02_perangkat_genap_2023',
        'rpl02_perangkat_ganjil_2024', 'rpl02_perangkat_genap_2024',
        'rpl03_pengembangan_ganjil_2019', 'rpl03_pengembangan_genap_2019',
        'rpl03_pengembangan_ganjil_2020', 'rpl03_pengembangan_genap_2020',
        'rpl03_pengembangan_ganjil_2021', 'rpl03_pengembangan_genap_2021',
        'rpl03_pengembangan_ganjil_2022', 'rpl03_pengembangan_genap_2022',
        'rpl03_pengembangan_ganjil_2023', 'rpl03_pengembangan_genap_2023',
        'rpl03_pengembangan_ganjil_2024', 'rpl03_pengembangan_genap_2024'
    ];
    
    $existing_columns = array_column($columns, 'Field');
    $missing_columns = array_diff($expected_columns, $existing_columns);
    
    if (empty($missing_columns)) {
        addTestResult('Database Structure', true, 'Semua 24 kolom baru berhasil ditambahkan', [
            'total_columns' => count($existing_columns),
            'new_columns_added' => count($expected_columns)
        ]);
    } else {
        addTestResult('Database Structure', false, 'Kolom belum lengkap: ' . implode(', ', $missing_columns), [
            'missing_count' => count($missing_columns),
            'missing_columns' => $missing_columns
        ]);
    }
} catch (Exception $e) {
    addTestResult('Database Structure', false, 'Error: ' . $e->getMessage());
}

// Test 2: Config Functions
try {
    // Test konversi nilai baru
    $test_scores = [85 => 'A', 79 => 'B', 69 => 'C', 59 => 'D', 49 => 'E'];
    $conversion_correct = true;
    
    foreach ($test_scores as $score => $expected_grade) {
        if (skorKeHurufMutu($score) !== $expected_grade) {
            $conversion_correct = false;
            break;
        }
    }
    
    if ($conversion_correct) {
        addTestResult('Grade Conversion', true, 'Konversi nilai baru berfungsi dengan benar', $test_scores);
    } else {
        addTestResult('Grade Conversion', false, 'Konversi nilai tidak sesuai ekspektasi');
    }
    
    // Test semester list function
    $semester_list = getSemesterList();
    if (count($semester_list) === 12) {
        addTestResult('Semester Functions', true, 'Fungsi semester list OK (12 semester)', [
            'semester_count' => count($semester_list),
            'first_semester' => array_keys($semester_list)[0],
            'last_semester' => array_keys($semester_list)[11]
        ]);
    } else {
        addTestResult('Semester Functions', false, 'Jumlah semester tidak sesuai: ' . count($semester_list));
    }
    
} catch (Exception $e) {
    addTestResult('Config Functions', false, 'Error: ' . $e->getMessage());
}

// Test 3: Sample Data Insert
try {
    // Cek apakah sudah ada sample data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mahasiswa WHERE nim LIKE '2586905000%'");
    $sample_count = $stmt->fetch()['count'];
    
    if ($sample_count > 0) {
        // Test document retrieval functions
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = '25869050001' LIMIT 1");
        $stmt->execute();
        $sample_mahasiswa = $stmt->fetch();
        
        if ($sample_mahasiswa) {
            $rpl02_docs = getRPL02Documents($sample_mahasiswa);
            $rpl03_docs = getRPL03Documents($sample_mahasiswa);
            
            $rpl02_available = countAvailableDocuments($rpl02_docs);
            $rpl03_available = countAvailableDocuments($rpl03_docs);
            
            addTestResult('Document Functions', true, 'Fungsi dokumen berfungsi dengan baik', [
                'rpl02_documents_available' => $rpl02_available,
                'rpl03_documents_available' => $rpl03_available,
                'total_semester_slots' => 12
            ]);
        }
    } else {
        addTestResult('Sample Data', false, 'Sample data belum diimport. Jalankan import_mahasiswa.php terlebih dahulu');
    }
    
} catch (Exception $e) {
    addTestResult('Document Functions', false, 'Error: ' . $e->getMessage());
}

// Test 4: Database Performance
try {
    $start_time = microtime(true);
    
    // Test query yang kompleks
    $stmt = $pdo->query("
        SELECT m.nim, m.nama_lengkap,
               COUNT(CASE WHEN m.rpl02_perangkat_ganjil_2019 IS NOT NULL AND m.rpl02_perangkat_ganjil_2019 != '' THEN 1 END) as rpl02_count,
               COUNT(CASE WHEN m.rpl03_pengembangan_ganjil_2019 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2019 != '' THEN 1 END) as rpl03_count
        FROM mahasiswa m 
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
        GROUP BY m.id
        LIMIT 10
    ");
    $results = $stmt->fetchAll();
    
    $execution_time = microtime(true) - $start_time;
    
    if ($execution_time < 1.0) { // Kurang dari 1 detik
        addTestResult('Database Performance', true, 'Query performance bagus', [
            'execution_time_seconds' => round($execution_time, 4),
            'rows_processed' => count($results)
        ]);
    } else {
        addTestResult('Database Performance', false, 'Query lambat: ' . round($execution_time, 2) . ' detik');
    }
    
} catch (Exception $e) {
    addTestResult('Database Performance', false, 'Error: ' . $e->getMessage());
}

// Test 5: File Permissions
try {
    $critical_files = [
        'config.php', 'penilaian.php', 'laporan.php', 
        'import_mahasiswa.php', 'dashboard_admin.php', 'dashboard_dosen.php'
    ];
    
    $permission_issues = [];
    foreach ($critical_files as $file) {
        if (!is_readable($file)) {
            $permission_issues[] = $file . ' not readable';
        }
        if (!file_exists($file)) {
            $permission_issues[] = $file . ' not found';
        }
    }
    
    if (empty($permission_issues)) {
        addTestResult('File Permissions', true, 'Semua file critical dapat diakses', [
            'files_checked' => count($critical_files)
        ]);
    } else {
        addTestResult('File Permissions', false, 'Issues: ' . implode(', ', $permission_issues));
    }
    
} catch (Exception $e) {
    addTestResult('File Permissions', false, 'Error: ' . $e->getMessage());
}

// Test 6: Memory Usage
$memory_usage = memory_get_usage(true);
$memory_limit = ini_get('memory_limit');

addTestResult('Memory Usage', true, 'Memory usage OK', [
    'current_usage_mb' => round($memory_usage / 1024 / 1024, 2),
    'memory_limit' => $memory_limit
]);

// Test 7: Google Drive Link Validation
try {
    $test_links = [
        'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl',
        'https://drive.google.com/file/d/1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr/view',
        'invalid-link'
    ];
    
    $valid_count = 0;
    foreach ($test_links as $link) {
        if (isValidGoogleDriveLink($link)) {
            $valid_count++;
        }
    }
    
    addTestResult('Google Drive Validation', true, 'Link validation berfungsi', [
        'valid_links' => $valid_count,
        'total_tested' => count($test_links)
    ]);
    
} catch (Exception $e) {
    addTestResult('Google Drive Validation', false, 'Error: ' . $e->getMessage());
}

// Generate Summary
$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results, function($test) { return $test['success']; }));
$failed_tests = $total_tests - $passed_tests;

$overall_success = $failed_tests === 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Results - Multiple Documents System</title>
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
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background: <?= $overall_success ? '#27ae60' : '#e74c3c' ?>;
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .passed { color: #27ae60; }
        .failed { color: #e74c3c; }
        .total { color: #3498db; }
        
        .test-result {
            background: white;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .test-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .test-success {
            background: #d4edda;
            border-left: 4px solid #27ae60;
        }
        
        .test-failure {
            background: #f8d7da;
            border-left: 4px solid #e74c3c;
        }
        
        .test-name {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .test-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pass {
            background: #27ae60;
            color: white;
        }
        
        .status-fail {
            background: #e74c3c;
            color: white;
        }
        
        .test-details {
            padding: 1rem;
            border-top: 1px solid #e0e0e0;
            background: #f8f9fa;
        }
        
        .test-message {
            margin-bottom: 1rem;
        }
        
        .test-data {
            background: #fff;
            padding: 1rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
            margin-bottom: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.5rem;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $overall_success ? '✅' : '❌' ?> Testing Results - Multiple Documents System</h1>
            <p>Deployment Verification - <?= date('Y-m-d H:i:s') ?></p>
            <p><strong>Status: <?= $overall_success ? 'ALL TESTS PASSED' : 'SOME TESTS FAILED' ?></strong></p>
        </div>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> Hapus file ini (test_multiple_docs.php) setelah testing selesai!
            File ini hanya untuk verifikasi deployment dan tidak boleh accessible di production.
            <br><br>
            <a href="?delete_self=1" class="btn btn-danger" onclick="return confirm('Hapus file testing ini?')">
                🗑️ Hapus File Testing
            </a>
        </div>
        
        <?php if (isset($_GET['delete_self'])): ?>
            <?php
            if (unlink(__FILE__)) {
                echo '<div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                        ✅ File testing berhasil dihapus. Refresh halaman ini akan error 404.
                      </div>';
            } else {
                echo '<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                        ❌ Gagal menghapus file. Hapus secara manual via File Manager.
                      </div>';
            }
            ?>
        <?php endif; ?>
        
        <div class="summary">
            <div class="summary-card">
                <div class="summary-number total"><?= $total_tests ?></div>
                <div>Total Tests</div>
            </div>
            <div class="summary-card">
                <div class="summary-number passed"><?= $passed_tests ?></div>
                <div>Tests Passed</div>
            </div>
            <div class="summary-card">
                <div class="summary-number failed"><?= $failed_tests ?></div>
                <div>Tests Failed</div>
            </div>
            <div class="summary-card">
                <div class="summary-number <?= $overall_success ? 'passed' : 'failed' ?>">
                    <?= round(($passed_tests / $total_tests) * 100) ?>%
                </div>
                <div>Success Rate</div>
            </div>
        </div>
        
        <h2>Detailed Test Results</h2>
        
        <?php foreach ($test_results as $test): ?>
            <div class="test-result">
                <div class="test-header <?= $test['success'] ? 'test-success' : 'test-failure' ?>">
                    <div class="test-name"><?= htmlspecialchars($test['name']) ?></div>
                    <div class="test-status <?= $test['success'] ? 'status-pass' : 'status-fail' ?>">
                        <?= $test['success'] ? 'PASS' : 'FAIL' ?>
                    </div>
                </div>
                <div class="test-details">
                    <div class="test-message">
                        <strong>Result:</strong> <?= htmlspecialchars($test['message']) ?>
                    </div>
                    <?php if ($test['data']): ?>
                        <div class="test-data">
                            <?= htmlspecialchars(json_encode($test['data'], JSON_PRETTY_PRINT)) ?>
                        </div>
                    <?php endif; ?>
                    <div style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
                        Tested at: <?= $test['timestamp'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="margin-top: 3rem; padding: 2rem; background: white; border-radius: 8px;">
            <h3>Next Steps</h3>
            
            <?php if ($overall_success): ?>
                <div style="color: #27ae60; margin-bottom: 1rem;">
                    ✅ <strong>Semua tests berhasil!</strong> Sistem siap untuk production.
                </div>
                <ol>
                    <li>Hapus file testing ini</li>
                    <li>Import data production 2260 mahasiswa</li>
                    <li>Assign mahasiswa ke dosen</li>
                    <li>Training dosen interface baru</li>
                    <li>Go-live announcement</li>
                </ol>
            <?php else: ?>
                <div style="color: #e74c3c; margin-bottom: 1rem;">
                    ❌ <strong>Ada tests yang gagal!</strong> Perbaiki issues sebelum production.
                </div>
                <ol>
                    <li>Review failed tests di atas</li>
                    <li>Perbaiki issues yang ditemukan</li>
                    <li>Run testing ulang</li>
                    <li>Pastikan semua tests pass</li>
                    <li>Baru proceed ke production</li>
                </ol>
            <?php endif; ?>
            
            <div style="margin-top: 2rem;">
                <a href="dashboard_admin.php" class="btn">← Kembali ke Dashboard</a>
                <a href="penilaian.php?id=1" class="btn">Test Form Penilaian</a>
                <a href="laporan.php" class="btn">Test Laporan</a>
            </div>
        </div>
    </div>
</body>
</html>