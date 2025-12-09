<?php
require_once 'config.php';
requireLogin();

// Parameters - ENHANCED with more filters
$export = $_GET['export'] ?? '';
$export_format = $_GET['format'] ?? 'csv';
$jenjang_filter = $_GET['jenjang'] ?? '';
$dosen_filter = (int)($_GET['dosen'] ?? 0);
$status_filter = $_GET['status'] ?? 'semua'; // CHANGED: Default to show all
$per_dosen = $_GET['per_dosen'] ?? '';

// SORTING PARAMETERS - BARU
$sort_by = $_GET['sort_by'] ?? 'nama_lengkap';
$sort_order = $_GET['sort_order'] ?? 'asc';

// Validasi sorting parameters
$valid_sort_columns = ['nama_lengkap', 'nama_dosen', 'status_penilaian', 'rata_rata_berbobot', 'tanggal_penilaian'];
if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'nama_lengkap';
}
if (!in_array($sort_order, ['asc', 'desc'])) {
    $sort_order = 'asc';
}

// Build query conditions - ENHANCED to show ALL students
$where_conditions = [];
$params = [];

// Base query: Include ALL students (assessed or not)
$base_query = "
    FROM mahasiswa m
    LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
    LEFT JOIN users u ON p.dosen_penilai_id = u.id
";

// Apply filters
if ($jenjang_filter) {
    $where_conditions[] = "m.jenjang = ?";
    $params[] = $jenjang_filter;
}

if ($dosen_filter) {
    if ($per_dosen) {
        // For per-dosen export, use assigned_dosen_id
        $where_conditions[] = "m.assigned_dosen_id = ?";
    } else {
        // For regular filter, use actual evaluator
        $where_conditions[] = "p.dosen_penilai_id = ?";
    }
    $params[] = $dosen_filter;
}

// Status filter - ENHANCED with more options
switch ($status_filter) {
    case 'final':
        $where_conditions[] = "p.status_penilaian = 'final'";
        break;
    case 'draft':
        $where_conditions[] = "p.status_penilaian = 'draft'";
        break;
    case 'belum_dinilai':
        $where_conditions[] = "p.status_penilaian IS NULL";
        break;
    case 'sudah_dinilai':
        $where_conditions[] = "p.status_penilaian IS NOT NULL";
        break;
    // 'semua' shows all students - no condition added
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Build ORDER BY clause berdasarkan sorting parameter
$order_clause = '';
switch ($sort_by) {
    case 'nama_lengkap':
        $order_clause = "m.nama_lengkap {$sort_order}";
        break;
    case 'nama_dosen':
        $order_clause = "COALESCE(u.nama_lengkap, u2.nama_lengkap, 'Belum Di-assign') {$sort_order}, m.nama_lengkap ASC";
        break;
    case 'status_penilaian':
        $order_clause = "CASE 
            WHEN p.status_penilaian IS NULL THEN 1
            WHEN p.status_penilaian = 'draft' THEN 2
            WHEN p.status_penilaian = 'final' THEN 3
        END {$sort_order}, m.nama_lengkap ASC";
        break;
    case 'rata_rata_berbobot':
        $order_clause = "CASE WHEN p.rpl01_pedagogik IS NOT NULL THEN
            (p.rpl01_pedagogik * 6 + p.rpl02_perangkat * 6 + p.rpl03_profesional * 6 + 
             p.rpl04_administrasi * 6 + p.rpl05_inovasi * 3) / 27
            ELSE NULL
        END {$sort_order}, m.nama_lengkap ASC";
        break;
    case 'tanggal_penilaian':
        $order_clause = "p.updated_at {$sort_order}, m.nama_lengkap ASC";
        break;
    default:
        $order_clause = "CASE 
            WHEN p.status_penilaian IS NULL THEN 1
            WHEN p.status_penilaian = 'draft' THEN 2
            WHEN p.status_penilaian = 'final' THEN 3
        END, m.nama_lengkap";
}

// Get report data with document counts - ENHANCED query with sorting
try {
    $sql = "
        SELECT 
            m.nim, m.nama_lengkap, m.jenjang, m.tempat_tugas,
            m.no_telepon, m.provinsi, m.kabupaten, m.status_pegawai,
            COALESCE(u.nama_lengkap, u2.nama_lengkap, 'Belum Di-assign') as nama_dosen,
            p.rpl01_pedagogik, p.rpl01_huruf_mutu,
            p.rpl02_perangkat, p.rpl02_huruf_mutu,
            p.rpl03_profesional, p.rpl03_huruf_mutu,
            p.rpl04_administrasi, p.rpl04_huruf_mutu,
            p.rpl05_inovasi, p.rpl05_huruf_mutu,
            p.status_penilaian,
            p.catatan_dosen, p.updated_at as tanggal_penilaian,
            -- Status display
            CASE 
                WHEN p.status_penilaian = 'final' THEN 'Selesai'
                WHEN p.status_penilaian = 'draft' THEN 'Draft'
                ELSE 'Belum Dinilai'
            END as status_display,
            -- Weighted average calculation
            CASE WHEN p.rpl01_pedagogik IS NOT NULL THEN
                ROUND((p.rpl01_pedagogik * 6 + p.rpl02_perangkat * 6 + p.rpl03_profesional * 6 + 
                       p.rpl04_administrasi * 6 + p.rpl05_inovasi * 3) / 27, 2)
                ELSE NULL
            END as rata_rata_berbobot,
            -- Document count for RPL.02 (12 semesters)
            (CASE WHEN m.rpl02_perangkat_ganjil_2019 IS NOT NULL AND m.rpl02_perangkat_ganjil_2019 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2019 IS NOT NULL AND m.rpl02_perangkat_genap_2019 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_ganjil_2020 IS NOT NULL AND m.rpl02_perangkat_ganjil_2020 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2020 IS NOT NULL AND m.rpl02_perangkat_genap_2020 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_ganjil_2021 IS NOT NULL AND m.rpl02_perangkat_ganjil_2021 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2021 IS NOT NULL AND m.rpl02_perangkat_genap_2021 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_ganjil_2022 IS NOT NULL AND m.rpl02_perangkat_ganjil_2022 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2022 IS NOT NULL AND m.rpl02_perangkat_genap_2022 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_ganjil_2023 IS NOT NULL AND m.rpl02_perangkat_ganjil_2023 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2023 IS NOT NULL AND m.rpl02_perangkat_genap_2023 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_ganjil_2024 IS NOT NULL AND m.rpl02_perangkat_ganjil_2024 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl02_perangkat_genap_2024 IS NOT NULL AND m.rpl02_perangkat_genap_2024 != '' THEN 1 ELSE 0 END) as rpl02_doc_count,
            -- Document count for RPL.03 (12 semesters)
            (CASE WHEN m.rpl03_pengembangan_ganjil_2019 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2019 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2019 IS NOT NULL AND m.rpl03_pengembangan_genap_2019 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_ganjil_2020 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2020 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2020 IS NOT NULL AND m.rpl03_pengembangan_genap_2020 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_ganjil_2021 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2021 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2021 IS NOT NULL AND m.rpl03_pengembangan_genap_2021 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_ganjil_2022 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2022 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2022 IS NOT NULL AND m.rpl03_pengembangan_genap_2022 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_ganjil_2023 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2023 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2023 IS NOT NULL AND m.rpl03_pengembangan_genap_2023 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_ganjil_2024 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2024 != '' THEN 1 ELSE 0 END +
             CASE WHEN m.rpl03_pengembangan_genap_2024 IS NOT NULL AND m.rpl03_pengembangan_genap_2024 != '' THEN 1 ELSE 0 END) as rpl03_doc_count
        $base_query
        LEFT JOIN users u2 ON m.assigned_dosen_id = u2.id
        $where_clause
        ORDER BY $order_clause
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $laporan_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $laporan_data = [];
    $error = "Error: " . $e->getMessage();
}

// ENHANCED Export functionality with Excel support
if (($export === 'csv' || $export === 'excel') && !empty($laporan_data)) {
    $filename_suffix = $per_dosen ? '_per_dosen_' . $dosen_filter : '';
    $filename_suffix .= '_' . $status_filter;
    
    if ($export === 'excel') {
        // Excel export
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="laporan_rpl' . $filename_suffix . '_' . date('Y-m-d') . '.xlsx"');
        
        // Simple Excel generation using HTML table method
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        echo '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">';
        echo '<Title>Laporan Penilaian RPL</Title>';
        echo '<Subject>Sistem Penilaian RPL STKYAKOBUS</Subject>';
        echo '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>';
        echo '</DocumentProperties>';
        echo '<Worksheet ss:Name="Laporan RPL">';
        echo '<Table>';
        
        // Header row
        echo '<Row>';
        $headers = [
            'NIM', 'Nama Lengkap', 'Jenjang', 'Tempat Tugas', 'Email', 'Provinsi', 'Kabupaten',
            'Dosen Penilai', 'Status', 'Tanggal Penilaian',
            'RPL01 Skor', 'RPL01 Huruf', 'RPL02 Skor', 'RPL02 Huruf', 'RPL02 Docs',
            'RPL03 Skor', 'RPL03 Huruf', 'RPL03 Docs', 'RPL04 Skor', 'RPL04 Huruf', 
            'RPL05 Skor', 'RPL05 Huruf', 'Rata-rata Berbobot', 'Catatan'
        ];
        
        foreach ($headers as $header) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
        }
        echo '</Row>';
        
        // Data rows
        foreach ($laporan_data as $row) {
            echo '<Row>';
            $data = [
                $row['nim'], $row['nama_lengkap'], $row['jenjang'], $row['tempat_tugas'],
                $row['email'], $row['provinsi'], $row['kabupaten'], $row['nama_dosen'],
                $row['status_display'],
                $row['tanggal_penilaian'] ? formatTanggalIndo($row['tanggal_penilaian']) : '-',
                $row['rpl01_pedagogik'] ?? '-', $row['rpl01_huruf_mutu'] ?? '-',
                $row['rpl02_perangkat'] ?? '-', $row['rpl02_huruf_mutu'] ?? '-', 
                ($row['rpl02_doc_count'] ?? 0) . '/12',
                $row['rpl03_profesional'] ?? '-', $row['rpl03_huruf_mutu'] ?? '-',
                ($row['rpl03_doc_count'] ?? 0) . '/12',
                $row['rpl04_administrasi'] ?? '-', $row['rpl04_huruf_mutu'] ?? '-',
                $row['rpl05_inovasi'] ?? '-', $row['rpl05_huruf_mutu'] ?? '-',
                $row['rata_rata_berbobot'] ?? '-', $row['catatan_dosen'] ?? ''
            ];
            
            foreach ($data as $cell) {
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($cell) . '</Data></Cell>';
            }
            echo '</Row>';
        }
        
        echo '</Table>';
        echo '</Worksheet>';
        echo '</Workbook>';
        
    } else {
        // CSV export (existing functionality)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_rpl' . $filename_suffix . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        $header = [
            'NIM', 'Nama Lengkap', 'Jenjang', 'Tempat Tugas', 'Email', 'Provinsi', 'Kabupaten',
            'Dosen Penilai', 'Status', 'Tanggal Penilaian',
            'RPL01 Skor', 'RPL01 Huruf', 'RPL02 Skor', 'RPL02 Huruf', 'RPL02 Docs (/12)',
            'RPL03 Skor', 'RPL03 Huruf', 'RPL03 Docs (/12)', 'RPL04 Skor', 'RPL04 Huruf', 
            'RPL05 Skor', 'RPL05 Huruf', 'Rata-rata Berbobot', 'Catatan'
        ];
        
        fputcsv($output, $header);
        
        // CSV Data
        foreach ($laporan_data as $row) {
            $csv_row = [
                $row['nim'], $row['nama_lengkap'], $row['jenjang'], $row['tempat_tugas'],
                $row['email'], $row['provinsi'], $row['kabupaten'], $row['nama_dosen'],
                $row['status_display'],
                $row['tanggal_penilaian'] ? formatTanggalIndo($row['tanggal_penilaian']) : '-',
                $row['rpl01_pedagogik'] ?? '-', $row['rpl01_huruf_mutu'] ?? '-',
                $row['rpl02_perangkat'] ?? '-', $row['rpl02_huruf_mutu'] ?? '-', 
                ($row['rpl02_doc_count'] ?? 0) . '/12',
                $row['rpl03_profesional'] ?? '-', $row['rpl03_huruf_mutu'] ?? '-',
                ($row['rpl03_doc_count'] ?? 0) . '/12',
                $row['rpl04_administrasi'] ?? '-', $row['rpl04_huruf_mutu'] ?? '-',
                $row['rpl05_inovasi'] ?? '-', $row['rpl05_huruf_mutu'] ?? '-',
                $row['rata_rata_berbobot'] ?? '-', $row['catatan_dosen'] ?? ''
            ];
            fputcsv($output, $csv_row);
        }
        
        fclose($output);
    }
    exit();
}

// Get statistics - ENHANCED with all status
try {
    $stats_sql = "
        SELECT 
            COUNT(m.id) as total_mahasiswa,
            COUNT(CASE WHEN p.status_penilaian = 'final' THEN 1 END) as total_selesai,
            COUNT(CASE WHEN p.status_penilaian = 'draft' THEN 1 END) as total_draft,
            COUNT(CASE WHEN p.status_penilaian IS NULL THEN 1 END) as total_belum,
            AVG(CASE WHEN p.status_penilaian IS NOT NULL THEN
                (p.rpl01_pedagogik * 6 + p.rpl02_perangkat * 6 + p.rpl03_profesional * 6 + 
                 p.rpl04_administrasi * 6 + p.rpl05_inovasi * 3) / 27 
                END) as rata_rata_keseluruhan,
            -- Average document availability
            AVG((CASE WHEN m.rpl02_perangkat_ganjil_2019 IS NOT NULL AND m.rpl02_perangkat_ganjil_2019 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2019 IS NOT NULL AND m.rpl02_perangkat_genap_2019 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_ganjil_2020 IS NOT NULL AND m.rpl02_perangkat_ganjil_2020 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2020 IS NOT NULL AND m.rpl02_perangkat_genap_2020 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_ganjil_2021 IS NOT NULL AND m.rpl02_perangkat_ganjil_2021 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2021 IS NOT NULL AND m.rpl02_perangkat_genap_2021 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_ganjil_2022 IS NOT NULL AND m.rpl02_perangkat_ganjil_2022 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2022 IS NOT NULL AND m.rpl02_perangkat_genap_2022 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_ganjil_2023 IS NOT NULL AND m.rpl02_perangkat_ganjil_2023 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2023 IS NOT NULL AND m.rpl02_perangkat_genap_2023 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_ganjil_2024 IS NOT NULL AND m.rpl02_perangkat_ganjil_2024 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl02_perangkat_genap_2024 IS NOT NULL AND m.rpl02_perangkat_genap_2024 != '' THEN 1 ELSE 0 END)) as avg_rpl02_docs,
            AVG((CASE WHEN m.rpl03_pengembangan_ganjil_2019 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2019 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2019 IS NOT NULL AND m.rpl03_pengembangan_genap_2019 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_ganjil_2020 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2020 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2020 IS NOT NULL AND m.rpl03_pengembangan_genap_2020 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_ganjil_2021 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2021 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2021 IS NOT NULL AND m.rpl03_pengembangan_genap_2021 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_ganjil_2022 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2022 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2022 IS NOT NULL AND m.rpl03_pengembangan_genap_2022 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_ganjil_2023 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2023 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2023 IS NOT NULL AND m.rpl03_pengembangan_genap_2023 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_ganjil_2024 IS NOT NULL AND m.rpl03_pengembangan_ganjil_2024 != '' THEN 1 ELSE 0 END +
                 CASE WHEN m.rpl03_pengembangan_genap_2024 IS NOT NULL AND m.rpl03_pengembangan_genap_2024 != '' THEN 1 ELSE 0 END)) as avg_rpl03_docs
        $base_query
        LEFT JOIN users u2 ON m.assigned_dosen_id = u2.id
        $where_clause
    ";
    
    $stmt = $pdo->prepare($stats_sql);
    $stmt->execute($params);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    $stats = [
        'total_mahasiswa' => 0, 'total_selesai' => 0, 'total_draft' => 0, 'total_belum' => 0,
        'rata_rata_keseluruhan' => 0, 'avg_rpl02_docs' => 0, 'avg_rpl03_docs' => 0
    ];
}

// STATISTIK PER RPL (MAX/MIN/AVG) - BARU - HANYA STATUS FINAL
$rpl_stats = [];
try {
    $rpl_stats_sql = "
        SELECT 
            MAX(p.rpl01_pedagogik) as rpl01_max,
            MIN(p.rpl01_pedagogik) as rpl01_min,
            AVG(p.rpl01_pedagogik) as rpl01_avg,
            
            MAX(p.rpl02_perangkat) as rpl02_max,
            MIN(p.rpl02_perangkat) as rpl02_min,
            AVG(p.rpl02_perangkat) as rpl02_avg,
            
            MAX(p.rpl03_profesional) as rpl03_max,
            MIN(p.rpl03_profesional) as rpl03_min,
            AVG(p.rpl03_profesional) as rpl03_avg,
            
            MAX(p.rpl04_administrasi) as rpl04_max,
            MIN(p.rpl04_administrasi) as rpl04_min,
            AVG(p.rpl04_administrasi) as rpl04_avg,
            
            MAX(p.rpl05_inovasi) as rpl05_max,
            MIN(p.rpl05_inovasi) as rpl05_min,
            AVG(p.rpl05_inovasi) as rpl05_avg
        FROM mahasiswa m
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
        LEFT JOIN users u2 ON m.assigned_dosen_id = u2.id
        WHERE p.status_penilaian = 'final'
    ";
    
    // Add existing filters to RPL stats query
    if ($jenjang_filter) {
        $rpl_stats_sql .= " AND m.jenjang = ?";
    }
    if ($dosen_filter && !$per_dosen) {
        $rpl_stats_sql .= " AND p.dosen_penilai_id = ?";
    }
    
    $stmt = $pdo->prepare($rpl_stats_sql);
    
    // Execute with appropriate parameters
    $rpl_params = [];
    if ($jenjang_filter) $rpl_params[] = $jenjang_filter;
    if ($dosen_filter && !$per_dosen) $rpl_params[] = $dosen_filter;
    
    $stmt->execute($rpl_params);
    $rpl_stats = $stmt->fetch();
    
} catch (PDOException $e) {
    $rpl_stats = [];
}

// Get dosen list for filter
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'dosen' ORDER BY nama_lengkap");
    $dosen_options = $stmt->fetchAll();
} catch (PDOException $e) {
    $dosen_options = [];
}

// Distribution by grade - Only for finalized assessments
$grade_distribution = [];
if (!empty($laporan_data)) {
    foreach (['A', 'B', 'C', 'D', 'E'] as $grade) {
        $grade_distribution[$grade] = [
            'rpl01' => 0, 'rpl02' => 0, 'rpl03' => 0, 'rpl04' => 0, 'rpl05' => 0
        ];
    }
    
    foreach ($laporan_data as $row) {
        if ($row['status_penilaian'] === 'final') {
            if ($row['rpl01_huruf_mutu']) $grade_distribution[$row['rpl01_huruf_mutu']]['rpl01']++;
            if ($row['rpl02_huruf_mutu']) $grade_distribution[$row['rpl02_huruf_mutu']]['rpl02']++;
            if ($row['rpl03_huruf_mutu']) $grade_distribution[$row['rpl03_huruf_mutu']]['rpl03']++;
            if ($row['rpl04_huruf_mutu']) $grade_distribution[$row['rpl04_huruf_mutu']]['rpl04']++;
            if ($row['rpl05_huruf_mutu']) $grade_distribution[$row['rpl05_huruf_mutu']]['rpl05']++;
        }
    }
}

// Helper functions untuk sorting - BARU
function getSortUrl($column, $current_sort, $current_order, $current_params) {
    $new_order = 'asc';
    if ($column === $current_sort && $current_order === 'asc') {
        $new_order = 'desc';
    }
    
    $params = array_merge($current_params, [
        'sort_by' => $column,
        'sort_order' => $new_order
    ]);
    
    return '?' . http_build_query($params);
}

function getSortIcon($column, $current_sort, $current_order) {
    if ($column !== $current_sort) {
        return '<span class="sort-icon">⇅</span>';
    }
    return $current_order === 'asc' ? 
        '<span class="sort-icon active">↑</span>' : 
        '<span class="sort-icon active">↓</span>';
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
            max-width: 1600px;
            margin: 0 auto;
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
        .btn-info { background: #17a2b8; color: white; }
        
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
            font-size: 0.85rem;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            user-select: none;
        }
        
        /* SORTING STYLES - BARU */
        .sortable-header {
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
            padding-right: 1.5rem;
        }
        
        .sortable-header:hover {
            background: #e9ecef;
        }
        
        .sort-icon {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 0.9rem;
        }
        
        .sort-icon.active {
            color: #3498db;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .grade-a { background: #d4edda; color: #155724; font-weight: bold; }
        .grade-b { background: #d1ecf1; color: #0c5460; font-weight: bold; }
        .grade-c { background: #fff3cd; color: #856404; font-weight: bold; }
        .grade-d { background: #f8d7da; color: #721c24; font-weight: bold; }
        .grade-e { background: #f8d7da; color: #721c24; font-weight: bold; }
        
        .status-selesai { background: #d4edda; color: #155724; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem; }
        .status-draft { background: #fff3cd; color: #856404; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem; }
        .status-belum { background: #f8d7da; color: #721c24; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem; }
        
        .doc-indicator {
            display: inline-block;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.7rem;
            margin-left: 0.5rem;
        }
        
        .doc-full { background: #d4edda; color: #155724; }
        .doc-partial { background: #fff3cd; color: #856404; }
        .doc-none { background: #f8d7da; color: #721c24; }
        
        .export-options {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .export-options select {
            width: auto;
            min-width: 150px;
        }
        
        /* TABEL STATISTIK PER RPL - BARU */
        .rpl-stats-table {
            width: 100%;
            margin-top: 1rem;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .rpl-stats-table th,
        .rpl-stats-table td {
            padding: 0.6rem;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .rpl-stats-table th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
        }
        
        .rpl-stats-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .rpl-stats-table tbody tr:hover {
            background: #e9ecef;
        }
        
        .rpl-stats-table .rpl-name {
            text-align: left;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .stat-max { color: #27ae60; font-weight: bold; }
        .stat-min { color: #e74c3c; font-weight: bold; }
        .stat-avg { color: #3498db; font-weight: bold; }
        
        @media print {
            .header, .filters, .btn, .export-options { display: none; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .filter-row { grid-template-columns: 1fr; }
            table { font-size: 0.75rem; }
            th, td { padding: 0.5rem; }
            .export-options { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Laporan Penilaian RPL - Enhanced</h1>
        <div>
            <a href="<?= isDosen() ? 'dashboard_dosen.php' : 'dashboard_admin.php' ?>" 
               class="btn btn-primary">← Kembali</a>
        </div>
    </div>
    
    <div class="container">
        <!-- ENHANCED Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_mahasiswa'] ?? 0) ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_selesai'] ?? 0) ?></div>
                <div class="stat-label">Selesai Dinilai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_draft'] ?? 0) ?></div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_belum'] ?? 0) ?></div>
                <div class="stat-label">Belum Dinilai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['rata_rata_keseluruhan'] ?? 0, 1) ?></div>
                <div class="stat-label">Rata-rata Nilai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['avg_rpl02_docs'] ?? 0, 1) ?>/12</div>
                <div class="stat-label">Avg RPL.02 Docs</div>
            </div>
        </div>
        
        <!-- STATISTIK PER RPL - BARU -->
        <?php if (!empty($rpl_stats) && $stats['total_selesai'] > 0): ?>
        <div class="card">
            <h3>📈 Statistik Nilai per Bidang RPL (Hanya Penilaian Final)</h3>
            <table class="rpl-stats-table">
                <thead>
                    <tr>
                        <th class="rpl-name">Bidang RPL</th>
                        <th>Nilai Maksimal</th>
                        <th>Nilai Minimal</th>
                        <th>Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="rpl-name">RPL01 - Pedagogik</td>
                        <td class="stat-max"><?= $rpl_stats['rpl01_max'] ? number_format($rpl_stats['rpl01_max'], 2) : '-' ?></td>
                        <td class="stat-min"><?= $rpl_stats['rpl01_min'] ? number_format($rpl_stats['rpl01_min'], 2) : '-' ?></td>
                        <td class="stat-avg"><?= $rpl_stats['rpl01_avg'] ? number_format($rpl_stats['rpl01_avg'], 2) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="rpl-name">RPL02 - Perangkat</td>
                        <td class="stat-max"><?= $rpl_stats['rpl02_max'] ? number_format($rpl_stats['rpl02_max'], 2) : '-' ?></td>
                        <td class="stat-min"><?= $rpl_stats['rpl02_min'] ? number_format($rpl_stats['rpl02_min'], 2) : '-' ?></td>
                        <td class="stat-avg"><?= $rpl_stats['rpl02_avg'] ? number_format($rpl_stats['rpl02_avg'], 2) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="rpl-name">RPL03 - Profesional</td>
                        <td class="stat-max"><?= $rpl_stats['rpl03_max'] ? number_format($rpl_stats['rpl03_max'], 2) : '-' ?></td>
                        <td class="stat-min"><?= $rpl_stats['rpl03_min'] ? number_format($rpl_stats['rpl03_min'], 2) : '-' ?></td>
                        <td class="stat-avg"><?= $rpl_stats['rpl03_avg'] ? number_format($rpl_stats['rpl03_avg'], 2) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="rpl-name">RPL04 - Administrasi</td>
                        <td class="stat-max"><?= $rpl_stats['rpl04_max'] ? number_format($rpl_stats['rpl04_max'], 2) : '-' ?></td>
                        <td class="stat-min"><?= $rpl_stats['rpl04_min'] ? number_format($rpl_stats['rpl04_min'], 2) : '-' ?></td>
                        <td class="stat-avg"><?= $rpl_stats['rpl04_avg'] ? number_format($rpl_stats['rpl04_avg'], 2) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="rpl-name">RPL05 - Inovasi</td>
                        <td class="stat-max"><?= $rpl_stats['rpl05_max'] ? number_format($rpl_stats['rpl05_max'], 2) : '-' ?></td>
                        <td class="stat-min"><?= $rpl_stats['rpl05_min'] ? number_format($rpl_stats['rpl05_min'], 2) : '-' ?></td>
                        <td class="stat-avg"><?= $rpl_stats['rpl05_avg'] ? number_format($rpl_stats['rpl05_avg'], 2) : '-' ?></td>
                    </tr>
                </tbody>
            </table>
            <p style="margin-top: 1rem; font-size: 0.85rem; color: #666;">
                <strong>Keterangan:</strong> 
                <span class="stat-max">Hijau</span> = Nilai Tertinggi | 
                <span class="stat-min">Merah</span> = Nilai Terendah | 
                <span class="stat-avg">Biru</span> = Rata-rata
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Grade Distribution Chart - Only show for final assessments -->
        <?php if (!empty($laporan_data) && $stats['total_selesai'] > 0): ?>
            <div class="card">
                <h3>📊 Distribusi Nilai per Bidang RPL (Hanya Penilaian Final)</h3>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-top: 1rem;">
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
                        <div style="text-align: center; padding: 1rem; border: 1px solid #ddd; border-radius: 5px;">
                            <div style="font-weight: bold; margin-bottom: 0.5rem; color: #2c3e50;"><?= $rpl_name ?></div>
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <?php foreach (['A', 'B', 'C', 'D', 'E'] as $grade): ?>
                                    <div class="grade-item grade-<?= strtolower($grade) ?>" style="padding: 0.25rem; border-radius: 3px; font-size: 0.8rem;">
                                        <?= $grade ?>: <?= $grade_distribution[$grade][$rpl_key] ?? 0 ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- ENHANCED Filters - POSISI DIPINDAH KE BAWAH DISTRIBUSI -->
        <div class="filters">
            <h3 style="margin-bottom: 1rem;">🔍 Filter & Export Laporan</h3>
            <form method="GET">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="status">Status Penilaian</label>
                        <select id="status" name="status">
                            <option value="semua" <?= $status_filter === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="final" <?= $status_filter === 'final' ? 'selected' : '' ?>>Selesai</option>
                            <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="sudah_dinilai" <?= $status_filter === 'sudah_dinilai' ? 'selected' : '' ?>>Sudah Dinilai</option>
                            <option value="belum_dinilai" <?= $status_filter === 'belum_dinilai' ? 'selected' : '' ?>>Belum Dinilai</option>
                        </select>
                    </div>
                    
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
            
            <!-- ENHANCED Export Options -->
            <?php if (!empty($laporan_data)): ?>
            <div class="export-options">
                <strong>Export Options:</strong>
                
                <!-- Regular Export -->
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" 
                   class="btn btn-success">📄 CSV</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                   class="btn btn-info">📊 Excel</a>
                
                <!-- Per-Dosen Export -->
                <?php if ($dosen_filter): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv', 'per_dosen' => '1'])) ?>" 
                   class="btn btn-success">📄 CSV Per Dosen</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel', 'per_dosen' => '1'])) ?>" 
                   class="btn btn-info">📊 Excel Per Dosen</a>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn btn-warning">🖨️ Print</button>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ENHANCED Data Table dengan Sorting -->
        <div class="card">
            <h3>📋 Detail Hasil Penilaian - Semua Status</h3>
            
            <?php if (isset($error)): ?>
                <div style="color: red; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (empty($laporan_data)): ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <p>Tidak ada data yang sesuai dengan filter yang dipilih.</p>
                    <p>Coba ubah filter atau reset untuk melihat semua data.</p>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 1rem; color: #666;">
                    Menampilkan <?= count($laporan_data) ?> dari <?= $stats['total_mahasiswa'] ?> mahasiswa
                    <br><small>Status: 🟢 Selesai | 🟡 Draft | 🔴 Belum Dinilai | Dokumen: 🟢 Lengkap (12/12) | 🟡 Sebagian | 🔴 Tidak ada</small>
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th class="sortable-header" onclick="window.location.href='<?= getSortUrl('nama_lengkap', $sort_by, $sort_order, $_GET) ?>'">
                                    Nama Lengkap
                                    <?= getSortIcon('nama_lengkap', $sort_by, $sort_order) ?>
                                </th>
                                <th>Jenjang</th>
                                <th class="sortable-header" onclick="window.location.href='<?= getSortUrl('nama_dosen', $sort_by, $sort_order, $_GET) ?>'">
                                    Dosen Penilai
                                    <?= getSortIcon('nama_dosen', $sort_by, $sort_order) ?>
                                </th>
                                <th class="sortable-header" onclick="window.location.href='<?= getSortUrl('status_penilaian', $sort_by, $sort_order, $_GET) ?>'">
                                    Status
                                    <?= getSortIcon('status_penilaian', $sort_by, $sort_order) ?>
                                </th>
                                <th>RPL01<br><small>Pedagogik</small></th>
                                <th>RPL02<br><small>Perangkat</small></th>
                                <th>RPL03<br><small>Profesional</small></th>
                                <th>RPL04<br><small>Administrasi</small></th>
                                <th>RPL05<br><small>Inovasi</small></th>
                                <th class="sortable-header" onclick="window.location.href='<?= getSortUrl('rata_rata_berbobot', $sort_by, $sort_order, $_GET) ?>'">
                                    Rata-rata
                                    <?= getSortIcon('rata_rata_berbobot', $sort_by, $sort_order) ?>
                                </th>
                                <th class="sortable-header" onclick="window.location.href='<?= getSortUrl('tanggal_penilaian', $sort_by, $sort_order, $_GET) ?>'">
                                    Tanggal
                                    <?= getSortIcon('tanggal_penilaian', $sort_by, $sort_order) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_data as $row): ?>
                                <tr>
                                    <td><?= sanitizeInput($row['nim']) ?></td>
                                    <td><?= sanitizeInput($row['nama_lengkap']) ?></td>
                                    <td><?= sanitizeInput($row['jenjang']) ?></td>
                                    <td><?= sanitizeInput($row['nama_dosen']) ?></td>
                                    
                                    <td>
                                        <span class="status-<?= strtolower($row['status_display'] ?? 'belum') ?>">
                                            <?= $row['status_display'] ?? 'Belum Dinilai' ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rpl01_pedagogik']): ?>
                                            <span class="grade-<?= strtolower($row['rpl01_huruf_mutu'] ?? '') ?>">
                                                <?= $row['rpl01_pedagogik'] ?> (<?= $row['rpl01_huruf_mutu'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rpl02_perangkat']): ?>
                                            <span class="grade-<?= strtolower($row['rpl02_huruf_mutu'] ?? '') ?>">
                                                <?= $row['rpl02_perangkat'] ?> (<?= $row['rpl02_huruf_mutu'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                        <?php 
                                        $doc_count = $row['rpl02_doc_count'] ?? 0;
                                        $doc_class = 'doc-none';
                                        if ($doc_count == 12) $doc_class = 'doc-full';
                                        elseif ($doc_count > 0) $doc_class = 'doc-partial';
                                        ?>
                                        <span class="doc-indicator <?= $doc_class ?>">
                                            <?= $doc_count ?>/12
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rpl03_profesional']): ?>
                                            <span class="grade-<?= strtolower($row['rpl03_huruf_mutu'] ?? '') ?>">
                                                <?= $row['rpl03_profesional'] ?> (<?= $row['rpl03_huruf_mutu'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                        <?php 
                                        $doc_count = $row['rpl03_doc_count'] ?? 0;
                                        $doc_class = 'doc-none';
                                        if ($doc_count == 12) $doc_class = 'doc-full';
                                        elseif ($doc_count > 0) $doc_class = 'doc-partial';
                                        ?>
                                        <span class="doc-indicator <?= $doc_class ?>">
                                            <?= $doc_count ?>/12
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rpl04_administrasi']): ?>
                                            <span class="grade-<?= strtolower($row['rpl04_huruf_mutu'] ?? '') ?>">
                                                <?= $row['rpl04_administrasi'] ?> (<?= $row['rpl04_huruf_mutu'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rpl05_inovasi']): ?>
                                            <span class="grade-<?= strtolower($row['rpl05_huruf_mutu'] ?? '') ?>">
                                                <?= $row['rpl05_inovasi'] ?> (<?= $row['rpl05_huruf_mutu'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($row['rata_rata_berbobot']): ?>
                                            <strong><?= $row['rata_rata_berbobot'] ?></strong>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?= $row['tanggal_penilaian'] ? formatTanggalIndo($row['tanggal_penilaian']) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ENHANCED Summary -->
        <?php if (!empty($laporan_data)): ?>
            <div class="card">
                <h3>📝 Ringkasan Laporan</h3>
                <div style="line-height: 1.8; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div>
                        <h4>📊 Statistik Penilaian</h4>
                        <p><strong>Total Mahasiswa:</strong> <?= number_format($stats['total_mahasiswa']) ?> orang</p>
                        <p><strong>Sudah Selesai:</strong> <?= number_format($stats['total_selesai']) ?> orang</p>
                        <p><strong>Masih Draft:</strong> <?= number_format($stats['total_draft']) ?> orang</p>
                        <p><strong>Belum Dinilai:</strong> <?= number_format($stats['total_belum']) ?> orang</p>
                        <p><strong>Progress:</strong> <?= $stats['total_mahasiswa'] > 0 ? number_format(($stats['total_selesai'] / $stats['total_mahasiswa']) * 100, 1) : 0 ?>%</p>
                    </div>
                    
                    <div>
                        <h4>📈 Rata-rata Dokumen</h4>
                        <p><strong>RPL.02 (Perangkat):</strong> <?= number_format($stats['avg_rpl02_docs'], 1) ?>/12 semester</p>
                        <p><strong>RPL.03 (Profesional):</strong> <?= number_format($stats['avg_rpl03_docs'], 1) ?>/12 semester</p>
                        <p><strong>Rata-rata Nilai:</strong> <?= number_format($stats['rata_rata_keseluruhan'] ?? 0, 2) ?></p>
                        <p><strong>Tanggal Laporan:</strong> <?= formatTanggalIndo(date('Y-m-d')) ?></p>
                    </div>
                    
                    <div>
                        <h4>🔍 Filter Aktif</h4>
                        <?php if ($jenjang_filter): ?>
                            <p><strong>Jenjang:</strong> <?= sanitizeInput($jenjang_filter) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($dosen_filter): ?>
                            <?php
                            $selected_dosen = array_filter($dosen_options, function($d) use ($dosen_filter) {
                                return $d['id'] == $dosen_filter;
                            });
                            $selected_dosen = reset($selected_dosen);
                            ?>
                            <p><strong>Dosen:</strong> <?= sanitizeInput($selected_dosen['nama_lengkap'] ?? '') ?></p>
                        <?php endif; ?>
                        
                        <p><strong>Status:</strong> <?= ucfirst(str_replace('_', ' ', $status_filter)) ?></p>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; font-style: italic; color: #666;">
                    <strong>Catatan:</strong> Laporan ini menampilkan semua mahasiswa dengan status yang berbeda-beda. 
                    Sistem sekarang mendukung multiple documents untuk RPL.02 dan RPL.03 (masing-masing 12 semester). 
                    Distribusi nilai dan statistik per RPL hanya dihitung untuk penilaian yang sudah final. Export tersedia dalam format CSV dan Excel.
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>