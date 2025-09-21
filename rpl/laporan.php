<?php
require_once 'config.php';
requireLogin();

// Parameters
$export = $_GET['export'] ?? '';
$jenjang_filter = $_GET['jenjang'] ?? '';
$dosen_filter = (int)($_GET['dosen'] ?? 0);
$status_filter = $_GET['status'] ?? 'final'; // Default hanya yang sudah final

// Build query conditions
$where_conditions = ["p.status_penilaian = 'final'"]; // Default filter final
$params = [];

if ($jenjang_filter) {
    $where_conditions[] = "m.jenjang = ?";
    $params[] = $jenjang_filter;
}

if ($dosen_filter) {
    $where_conditions[] = "p.dosen_penilai_id = ?";
    $params[] = $dosen_filter;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get report data
try {
    $sql = "
        SELECT 
            m.nim, m.nama_lengkap, m.jenjang, m.tempat_tugas,
            m.email, m.provinsi, m.kabupaten,
            u.nama_lengkap as nama_dosen,
            p.rpl01_pedagogik, p.rpl01_huruf_mutu,
            p.rpl02_perangkat, p.rpl02_huruf_mutu,
            p.rpl03_profesional, p.rpl03_huruf_mutu,
            p.rpl04_administrasi, p.rpl04_huruf_mutu,
            p.rpl05_inovasi, p.rpl05_huruf_mutu,
            p.catatan_dosen, p.updated_at as tanggal_penilaian,
            -- Hitung total skor dan rata-rata berbobot
            ROUND((p.rpl01_pedagogik * 6 + p.rpl02_perangkat * 6 + p.rpl03_profesional * 6 + 
                   p.rpl04_administrasi * 6 + p.rpl05_inovasi * 3) / 27, 2) as rata_rata_berbobot
        FROM mahasiswa m
        INNER JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
        INNER JOIN users u ON p.dosen_penilai_id = u.id
        $where_clause
        ORDER BY m.nama_lengkap
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $laporan_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $laporan_data = [];
}

// Export to CSV
if ($export === 'csv' && !empty($laporan_data)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_rpl_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // CSV Header
    $header = [
        'NIM', 'Nama Lengkap', 'Jenjang', 'Tempat Tugas', 'Email', 'Provinsi', 'Kabupaten',
        'Dosen Penilai', 'Tanggal Penilaian',
        'RPL01 Skor', 'RPL01 Huruf', 'RPL02 Skor', 'RPL02 Huruf', 
        'RPL03 Skor', 'RPL03 Huruf', 'RPL04 Skor', 'RPL04 Huruf', 
        'RPL05 Skor', 'RPL05 Huruf', 'Rata-rata Berbobot', 'Catatan'
    ];
    
    fputcsv($output, $header);
    
    // CSV Data
    foreach ($laporan_data as $row) {
        $csv_row = [
            $row['nim'], $row['nama_lengkap'], $row['jenjang'], $row['tempat_tugas'],
            $row['email'], $row['provinsi'], $row['kabupaten'], $row['nama_dosen'],
            formatTanggalIndo($row['tanggal_penilaian']),
            $row['rpl01_pedagogik'], $row['rpl01_huruf_mutu'],
            $row['rpl02_perangkat'], $row['rpl02_huruf_mutu'],
            $row['rpl03_profesional'], $row['rpl03_huruf_mutu'],
            $row['rpl04_administrasi'], $row['rpl04_huruf_mutu'],
            $row['rpl05_inovasi'], $row['rpl05_huruf_mutu'],
            $row['rata_rata_berbobot'], $row['catatan_dosen']
        ];
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    exit();
}

// Get statistics
try {
    $stats_sql = "
        SELECT 
            COUNT(*) as total_dinilai,
            AVG((p.rpl01_pedagogik * 6 + p.rpl02_perangkat * 6 + p.rpl03_profesional * 6 + 
                 p.rpl04_administrasi * 6 + p.rpl05_inovasi * 3) / 27) as rata_rata_keseluruhan,
            COUNT(CASE WHEN p.rpl01_huruf_mutu = 'A' THEN 1 END) as rpl01_a,
            COUNT(CASE WHEN p.rpl02_huruf_mutu = 'A' THEN 1 END) as rpl02_a,
            COUNT(CASE WHEN p.rpl03_huruf_mutu = 'A' THEN 1 END) as rpl03_a,
            COUNT(CASE WHEN p.rpl04_huruf_mutu = 'A' THEN 1 END) as rpl04_a,
            COUNT(CASE WHEN p.rpl05_huruf_mutu = 'A' THEN 1 END) as rpl05_a
        FROM penilaian_rpl p
        INNER JOIN mahasiswa m ON p.mahasiswa_id = m.id
        $where_clause
    ";
    
    $stmt = $pdo->prepare($stats_sql);
    $stmt->execute($params);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    $stats = ['total_dinilai' => 0, 'rata_rata_keseluruhan' => 0];
}

// Get dosen list for filter
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'dosen' ORDER BY nama_lengkap");
    $dosen_options = $stmt->fetchAll();
} catch (PDOException $e) {
    $dosen_options = [];
}

// Distribution by grade
$grade_distribution = [];
if (!empty($laporan_data)) {
    foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
        $grade_distribution[$grade] = [
            'rpl01' => 0, 'rpl02' => 0, 'rpl03' => 0, 'rpl04' => 0, 'rpl05' => 0
        ];
    }
    
    foreach ($laporan_data as $row) {
        $grade_distribution[$row['rpl01_huruf_mutu']]['rpl01']++;
        $grade_distribution[$row['rpl02_huruf_mutu']]['rpl02']++;
        $grade_distribution[$row['rpl03_huruf_mutu']]['rpl03']++;
        $grade_distribution[$row['rpl04_huruf_mutu']]['rpl04']++;
        $grade_distribution[$row['rpl05_huruf_mutu']]['rpl05']++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penilaian RPL - <?= APP_NAME ?></title>
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
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
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
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
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
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: background 0.3s;
            margin: 0.25rem;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        
        .btn:hover { opacity: 0.9; }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .grade-a { background: #d4edda; color: #155724; font-weight: bold; }
        .grade-b { background: #d1ecf1; color: #0c5460; font-weight: bold; }
        .grade-c { background: #fff3cd; color: #856404; font-weight: bold; }
        .grade-d { background: #f8d7da; color: #721c24; font-weight: bold; }
        .grade-e { background: #f8d7da; color: #721c24; font-weight: bold; }
        
        .distribution-chart {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .chart-item {
            text-align: center;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .chart-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        
        .grade-bar {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .grade-item {
            padding: 0.25rem;
            border-radius: 3px;
            font-size: 0.8rem;
            text-align: center;
        }
        
        @media print {
            .header, .filters, .btn { display: none; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .filter-row { grid-template-columns: 1fr; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
            .distribution-chart { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Laporan Penilaian RPL</h1>
        <div>
            <?php if (!empty($laporan_data)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" 
                   class="btn btn-success">📁 Export CSV</a>
                <button onclick="window.print()" class="btn btn-warning">🖨️ Print</button>
            <?php endif; ?>
            <a href="<?= isDosen() ? 'dashboard_dosen.php' : 'dashboard_admin.php' ?>" 
               class="btn btn-primary">← Kembali</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_dinilai'] ?? 0) ?></div>
                <div class="stat-label">Total Mahasiswa Dinilai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['rata_rata_keseluruhan'] ?? 0, 1) ?></div>
                <div class="stat-label">Rata-rata Keseluruhan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['rpl01_a'] ?? 0) ?></div>
                <div class="stat-label">Grade A - RPL01</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= date('d/m/Y') ?></div>
                <div class="stat-label">Tanggal Laporan</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <h3 style="margin-bottom: 1rem;">🔍 Filter Laporan</h3>
            <form method="GET">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="jenjang">Jenjang</label>
                        <select id="jenjang" name="jenjang">
                            <option value="">Semua Jenjang</option>
                            <option value="SD" <?= $jenjang_filter === 'SD' ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= $jenjang_filter === 'SMP' ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= $jenjang_filter === 'SMA' ? 'selected' : '' ?>>SMA</option>
                            <option value="SMK" <?= $jenjang_filter === 'SMK' ? 'selected' : '' ?>>SMK</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosen">Dosen Penilai</label>
                        <select id="dosen" name="dosen">
                            <option value="">Semua Dosen</option>
                            <?php foreach ($dosen_options as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>" <?= $dosen_filter === $dosen['id'] ? 'selected' : '' ?>>
                                    <?= sanitizeInput($dosen['nama_lengkap']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <a href="laporan.php" class="btn btn-warning">🔄 Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Distribution Chart -->
        <?php if (!empty($laporan_data)): ?>
            <div class="card">
                <h3>📈 Distribusi Nilai per Bidang RPL</h3>
                <div class="distribution-chart">
                    <?php 
                    $rpl_names = [
                        'rpl01' => 'RPL01 Pedagogik',
                        'rpl02' => 'RPL02 Perangkat', 
                        'rpl03' => 'RPL03 Profesional',
                        'rpl04' => 'RPL04 Administrasi',
                        'rpl05' => 'RPL05 Inovasi'
                    ];
                    ?>
                    
                    <?php foreach ($rpl_names as $rpl_key => $rpl_name): ?>
                        <div class="chart-item">
                            <div class="chart-title"><?= $rpl_name ?></div>
                            <div class="grade-bar">
                                <?php foreach (['A', 'B', 'C', 'D', 'E'] as $grade): ?>
                                    <div class="grade-item grade-<?= strtolower($grade) ?>">
                                        <?= $grade ?>: <?= $grade_distribution[$grade][$rpl_key] ?? 0 ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Data Table -->
        <div class="card">
            <h3>📋 Detail Hasil Penilaian</h3>
            
            <?php if (empty($laporan_data)): ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <p>Belum ada data penilaian yang selesai untuk ditampilkan.</p>
                    <p>Pastikan dosen sudah menyelesaikan penilaian mahasiswa.</p>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 1rem; color: #666;">
                    Menampilkan <?= count($laporan_data) ?> hasil penilaian
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Jenjang</th>
                            <th>Tempat Tugas</th>
                            <th>Dosen Penilai</th>
                            <th>RPL01</th>
                            <th>RPL02</th>
                            <th>RPL03</th>
                            <th>RPL04</th>
                            <th>RPL05</th>
                            <th>Rata-rata</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_data as $row): ?>
                            <tr>
                                <td><?= sanitizeInput($row['nim']) ?></td>
                                <td><?= sanitizeInput($row['nama_lengkap']) ?></td>
                                <td><?= sanitizeInput($row['jenjang']) ?></td>
                                <td><?= sanitizeInput($row['tempat_tugas']) ?></td>
                                <td><?= sanitizeInput($row['nama_dosen']) ?></td>
                                
                                <td>
                                    <span class="grade-<?= strtolower($row['rpl01_huruf_mutu']) ?>">
                                        <?= $row['rpl01_pedagogik'] ?> (<?= $row['rpl01_huruf_mutu'] ?>)
                                    </span>
                                </td>
                                <td>
                                    <span class="grade-<?= strtolower($row['rpl02_huruf_mutu']) ?>">
                                        <?= $row['rpl02_perangkat'] ?> (<?= $row['rpl02_huruf_mutu'] ?>)
                                    </span>
                                </td>
                                <td>
                                    <span class="grade-<?= strtolower($row['rpl03_huruf_mutu']) ?>">
                                        <?= $row['rpl03_profesional'] ?> (<?= $row['rpl03_huruf_mutu'] ?>)
                                    </span>
                                </td>
                                <td>
                                    <span class="grade-<?= strtolower($row['rpl04_huruf_mutu']) ?>">
                                        <?= $row['rpl04_administrasi'] ?> (<?= $row['rpl04_huruf_mutu'] ?>)
                                    </span>
                                </td>
                                <td>
                                    <span class="grade-<?= strtolower($row['rpl05_huruf_mutu']) ?>">
                                        <?= $row['rpl05_inovasi'] ?> (<?= $row['rpl05_huruf_mutu'] ?>)
                                    </span>
                                </td>
                                
                                <td><strong><?= $row['rata_rata_berbobot'] ?></strong></td>
                                <td><?= formatTanggalIndo($row['tanggal_penilaian']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Summary -->
        <?php if (!empty($laporan_data)): ?>
            <div class="card">
                <h3>📝 Ringkasan Laporan</h3>
                <div style="line-height: 1.6;">
                    <p><strong>Total Mahasiswa yang Telah Dinilai:</strong> <?= count($laporan_data) ?> orang</p>
                    <p><strong>Rata-rata Skor Keseluruhan:</strong> <?= number_format($stats['rata_rata_keseluruhan'], 2) ?></p>
                    <p><strong>Periode Laporan:</strong> <?= formatTanggalIndo(date('Y-m-d')) ?></p>
                    
                    <?php if ($jenjang_filter): ?>
                        <p><strong>Filter Jenjang:</strong> <?= sanitizeInput($jenjang_filter) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($dosen_filter): ?>
                        <?php
                        $selected_dosen = array_filter($dosen_options, function($d) use ($dosen_filter) {
                            return $d['id'] == $dosen_filter;
                        });
                        $selected_dosen = reset($selected_dosen);
                        ?>
                        <p><strong>Filter Dosen:</strong> <?= sanitizeInput($selected_dosen['nama_lengkap'] ?? '') ?></p>
                    <?php endif; ?>
                    
                    <p style="margin-top: 1rem; font-style: italic; color: #666;">
                        Laporan ini menampilkan hasil penilaian RPL yang telah difinalisasi oleh dosen penilai.
                        Rata-rata dihitung berdasarkan bobot SKS masing-masing bidang RPL.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>