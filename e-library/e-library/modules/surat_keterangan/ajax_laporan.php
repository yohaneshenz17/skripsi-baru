<?php
/**
 * AJAX Handler untuk Laporan Surat Keterangan
 */

require_once 'config.php';
cekLoginAdmin();

$action = $_REQUEST['action'] ?? '';

if ($action == 'load_laporan') {
    loadLaporan();
} elseif ($action == 'export_excel') {
    exportExcel();
}

function loadLaporan() {
    global $conn;
    
    $tahun = $_POST['tahun'] ?? '';
    $bulan = $_POST['bulan'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    
    // Build query
    $where = ["sk.status = 'terbit'"];
    $params = [];
    $types = '';
    
    if ($tahun) {
        $where[] = "sk.tahun_periode = ?";
        $params[] = $tahun;
        $types .= 'i';
    }
    
    if ($bulan) {
        $where[] = "MONTH(sk.tanggal_terbit) = ?";
        $params[] = $bulan;
        $types .= 'i';
    }
    
    if ($jenis) {
        $where[] = "sk.jenis_surat = ?";
        $params[] = $jenis;
        $types .= 's';
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Get statistik
    $sql_stat = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN jenis_surat = 'UAS' THEN 1 ELSE 0 END) as uas,
                    SUM(CASE WHEN jenis_surat = 'PPA' THEN 1 ELSE 0 END) as ppa,
                    SUM(override_tunggakan) as override
                 FROM surat_keterangan sk
                 WHERE {$where_clause}";
    
    $stmt_stat = $conn->prepare($sql_stat);
    if ($types) {
        $stmt_stat->bind_param($types, ...$params);
    }
    $stmt_stat->execute();
    $statistik = $stmt_stat->get_result()->fetch_assoc();
    
    // Get data detail
    $sql = "SELECT sk.*, m.nama_mahasiswa, m.angkatan, ps.nama_prodi, a.nama as admin_nama
            FROM surat_keterangan sk
            JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN program_studi ps ON m.id_prodi = ps.id_prodi
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE {$where_clause}
            ORDER BY sk.tanggal_terbit DESC, sk.nomor_surat ASC";
    
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '';
    $no = 1;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $badge_jenis = $row['jenis_surat'] == 'UAS' ? 'badge-primary' : 'badge-info';
            $tanggal = formatTanggalIndonesia($row['tanggal_terbit']);
            $status = $row['override_tunggakan'] ? '<span class="badge badge-warning">Override</span>' : '<span class="badge badge-success">Normal</span>';
            
            $html .= "<tr>";
            $html .= "<td>{$no}</td>";
            $html .= "<td><small>{$row['nomor_surat']}</small></td>";
            $html .= "<td><small>{$tanggal}</small></td>";
            $html .= "<td>{$row['nim']}</td>";
            $html .= "<td>{$row['nama_mahasiswa']}</td>";
            $html .= "<td><small>{$row['nama_prodi']}</small></td>";
            $html .= "<td><span class='badge {$badge_jenis}'>{$row['jenis_surat']}</span></td>";
            $html .= "<td><small>{$row['admin_nama']}</small></td>";
            $html .= "<td>{$status}</td>";
            $html .= "</tr>";
            $no++;
        }
    } else {
        $html = "<tr><td colspan='9' class='text-center'>Tidak ada data</td></tr>";
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'statistik' => $statistik,
        'html' => $html
    ]);
}

function exportExcel() {
    global $conn, $BULAN_INDONESIA;
    
    $tahun = $_GET['tahun'] ?? '';
    $bulan = $_GET['bulan'] ?? '';
    $jenis = $_GET['jenis'] ?? '';
    
    // Build query
    $where = ["sk.status = 'terbit'"];
    $params = [];
    $types = '';
    
    if ($tahun) {
        $where[] = "sk.tahun_periode = ?";
        $params[] = $tahun;
        $types .= 'i';
    }
    
    if ($bulan) {
        $where[] = "MONTH(sk.tanggal_terbit) = ?";
        $params[] = $bulan;
        $types .= 'i';
    }
    
    if ($jenis) {
        $where[] = "sk.jenis_surat = ?";
        $params[] = $jenis;
        $types .= 's';
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Get data
    $sql = "SELECT sk.nomor_surat, sk.tanggal_terbit, sk.nim, m.nama_mahasiswa, m.angkatan, 
                   ps.nama_prodi, sk.jenis_surat, a.nama as admin_nama, sk.override_tunggakan,
                   sk.catatan
            FROM surat_keterangan sk
            JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN program_studi ps ON m.id_prodi = ps.id_prodi
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE {$where_clause}
            ORDER BY sk.tanggal_terbit DESC, sk.nomor_surat ASC";
    
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Nama file
    $periode = '';
    if ($bulan && $tahun) {
        $periode = $BULAN_INDONESIA[$bulan] . '_' . $tahun;
    } elseif ($tahun) {
        $periode = 'Tahun_' . $tahun;
    } else {
        $periode = 'Semua_Periode';
    }
    
    $filename = 'Laporan_Surat_Keterangan_' . $periode . '_' . date('YmdHis') . '.xls';
    
    // Set header untuk Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output Excel
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    echo '</head>';
    echo '<body>';
    
    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<th colspan="10" style="text-align: center; padding: 10px;">LAPORAN SURAT KETERANGAN BEBAS PERPUSTAKAAN</th>';
    echo '</tr>';
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<th colspan="10" style="text-align: center; padding: 5px;">Sekolah Tinggi Katolik Santo Yakobus Merauke</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th colspan="10" style="text-align: center; padding: 5px;">Periode: ' . $periode . '</th>';
    echo '</tr>';
    echo '<tr></tr>'; // Empty row
    echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
    echo '<th>No</th>';
    echo '<th>Nomor Surat</th>';
    echo '<th>Tanggal Terbit</th>';
    echo '<th>NIM</th>';
    echo '<th>Nama Mahasiswa</th>';
    echo '<th>Angkatan</th>';
    echo '<th>Program Studi</th>';
    echo '<th>Jenis Surat</th>';
    echo '<th>Admin</th>';
    echo '<th>Override/Catatan</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $tanggal = formatTanggalIndonesia($row['tanggal_terbit']);
        $override = $row['override_tunggakan'] ? 'YA - ' . $row['catatan'] : 'TIDAK';
        
        echo '<tr>';
        echo '<td>' . $no . '</td>';
        echo '<td>' . $row['nomor_surat'] . '</td>';
        echo '<td>' . $tanggal . '</td>';
        echo '<td>' . $row['nim'] . '</td>';
        echo '<td>' . $row['nama_mahasiswa'] . '</td>';
        echo '<td>' . $row['angkatan'] . '</td>';
        echo '<td>' . $row['nama_prodi'] . '</td>';
        echo '<td>' . $row['jenis_surat'] . '</td>';
        echo '<td>' . $row['admin_nama'] . '</td>';
        echo '<td>' . $override . '</td>';
        echo '</tr>';
        $no++;
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</body>';
    echo '</html>';
    
    exit;
}
?>
