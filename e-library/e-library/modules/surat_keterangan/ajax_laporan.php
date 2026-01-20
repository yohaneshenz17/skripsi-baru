<?php
session_start();

// Cek sesi admin
if (!isset($_SESSION['admin_id'])) {
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesi habis, silakan login kembali.']);
        exit;
    }
}

require_once 'config.php';

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'load_laporan':
        loadLaporan();
        break;
    case 'export_excel':
        exportExcel();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Membangun Query WHERE berdasarkan filter
 */
function buildWhereClause($tahun, $bulan, $jenis) {
    global $conn;
    $where = ["1=1"];
    
    if (!empty($tahun)) {
        $where[] = "sk.tahun_periode = " . intval($tahun);
    }
    
    if (!empty($bulan)) {
        $where[] = "MONTH(sk.tanggal_terbit) = " . intval($bulan);
    }
    
    if (!empty($jenis)) {
        $where[] = "sk.jenis_surat = '" . $conn->real_escape_string($jenis) . "'";
    }
    
    return implode(' AND ', $where);
}

function loadLaporan() {
    global $conn;
    
    $tahun = $_POST['tahun'] ?? '';
    $bulan = $_POST['bulan'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    
    $whereClause = buildWhereClause($tahun, $bulan, $jenis);
    
    // Query Data Utama dengan Join ke Admin dan Mahasiswa
    $sql = "SELECT sk.*, m.nama as nama_mhs, m.program_studi, a.username as nama_admin
            FROM surat_keterangan sk
            JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE {$whereClause}
            ORDER BY sk.tanggal_terbit DESC, sk.id DESC";
            
    $result = $conn->query($sql);
    
    // Inisialisasi Statistik
    $stats = [
        'total' => 0,
        'uas' => 0,
        'ppa' => 0,
        'override' => 0
    ];
    
    $html = '';
    $no = 1;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Hitung Statistik
            $stats['total']++;
            if ($row['jenis_surat'] == 'UAS') $stats['uas']++;
            if ($row['jenis_surat'] == 'PPA') $stats['ppa']++;
            if ($row['override_tunggakan'] == 1) $stats['override']++;
            
            // Format Tampilan
            $tgl = date('d/m/Y', strtotime($row['tanggal_terbit']));
            $badge_jenis = $row['jenis_surat'] == 'UAS' ? 'bg-primary' : 'bg-info';
            
            // Status Logic
            $status_label = '';
            $row_class = '';
            
            if ($row['status'] == 'dibatalkan') {
                $status_label = "<span class='badge bg-danger'>Dibatalkan</span>";
                $row_class = 'table-danger'; // Merahkan baris
            } else {
                $status_label = "<span class='badge bg-success'>Terbit</span>";
            }
            
            // Override Logic (Audit Trail Highlighting)
            $override_icon = '';
            if ($row['override_tunggakan'] == 1) {
                $override_icon = "<i class='fas fa-exclamation-triangle text-warning' title='Override Tunggakan: " . htmlspecialchars($row['catatan']) . "'></i>";
                if ($row_class == '') $row_class = 'table-warning'; // Kuningkan baris jika override (tapi tidak batal)
            }
            
            $html .= "<tr class='{$row_class}'>";
            $html .= "<td>{$no}</td>";
            $html .= "<td><strong>{$row['nomor_surat']}</strong></td>";
            $html .= "<td>{$tgl}</td>";
            $html .= "<td>{$row['nim']}</td>";
            $html .= "<td>{$row['nama_mhs']}</td>";
            $html .= "<td>{$row['program_studi']}</td>";
            $html .= "<td><span class='badge {$badge_jenis}'>{$row['jenis_surat']}</span></td>";
            $html .= "<td>" . ($row['nama_admin'] ?? 'System') . "</td>";
            $html .= "<td>{$status_label} {$override_icon}</td>";
            
            // Kolom Catatan (Penting untuk Audit)
            $catatan = !empty($row['catatan']) ? $row['catatan'] : '-';
            $html .= "<td class='small text-muted'>{$catatan}</td>";
            
            $html .= "</tr>";
            $no++;
        }
    } else {
        $html = "<tr><td colspan='10' class='text-center'>Tidak ada data ditemukan</td></tr>";
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'statistik' => $stats
    ]);
}

function exportExcel() {
    global $conn;
    
    $tahun = $_GET['tahun'] ?? '';
    $bulan = $_GET['bulan'] ?? '';
    $jenis = $_GET['jenis'] ?? '';
    
    $filename = "Laporan_Surat_Keterangan_" . date('Ymd_His') . ".xls";
    
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $whereClause = buildWhereClause($tahun, $bulan, $jenis);
    
    $sql = "SELECT sk.*, m.nama as nama_mhs, m.program_studi, a.username as nama_admin
            FROM surat_keterangan sk
            JOIN mahasiswa m ON sk.nim = m.nim
            LEFT JOIN admin a ON sk.admin_id = a.id
            WHERE {$whereClause}
            ORDER BY sk.tanggal_terbit DESC";
            
    $result = $conn->query($sql);
    
    echo "
    <table border='1'>
        <thead>
            <tr style='background-color: #f2f2f2;'>
                <th>No</th>
                <th>Nomor Surat</th>
                <th>Tanggal Terbit</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Program Studi</th>
                <th>Jenis Surat</th>
                <th>Admin Penerbit</th>
                <th>Status</th>
                <th>Override Tunggakan</th>
                <th>Catatan Audit</th>
            </tr>
        </thead>
        <tbody>";
        
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $override = $row['override_tunggakan'] == 1 ? 'YA' : 'TIDAK';
        $tgl = date('d-m-Y', strtotime($row['tanggal_terbit']));
        
        echo "<tr>
            <td>{$no}</td>
            <td>{$row['nomor_surat']}</td>
            <td>{$tgl}</td>
            <td>'{$row['nim']}</td>
            <td>{$row['nama_mhs']}</td>
            <td>{$row['program_studi']}</td>
            <td>{$row['jenis_surat']}</td>
            <td>{$row['nama_admin']}</td>
            <td>{$row['status']}</td>
            <td>{$override}</td>
            <td>{$row['catatan']}</td>
        </tr>";
        $no++;
    }
    
    echo "</tbody></table>";
    exit;
}
?>