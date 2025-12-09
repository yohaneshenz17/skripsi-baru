<?php
require_once 'config.php';
requireAdmin();

$message = '';
$error = '';

// Filter parameters - MODIFIED: Added per_page selection
$search = sanitizeInput($_GET['search'] ?? '');
$jenjang_filter = $_GET['jenjang'] ?? '';
$status_filter = $_GET['status'] ?? '';
$dosen_filter = (int)($_GET['dosen'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));

// NEW: Per page options with validation
$per_page_options = [50, 100, 150, 200];
$per_page = (int)($_GET['per_page'] ?? 150);
if (!in_array($per_page, $per_page_options)) {
    $per_page = 150; // Default to 150 as requested
}

$offset = ($page - 1) * $per_page;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // 🆕 NEW: Export Data Mahasiswa - FIXED dengan struktur database yang benar
    if ($action === 'export_data') {
        // Build query with same filters as display
        $where_conditions = [];
        $params = [];

        if ($search) {
            $where_conditions[] = "(m.nim LIKE ? OR m.nama_lengkap LIKE ? OR m.tempat_tugas LIKE ?)";
            $search_param = "%$search%";
            $params = array_merge($params, [$search_param, $search_param, $search_param]);
        }

        if ($jenjang_filter) {
            $where_conditions[] = "m.jenjang = ?";
            $params[] = $jenjang_filter;
        }

        if ($status_filter) {
            $where_conditions[] = "m.status_penilaian = ?";
            $params[] = $status_filter;
        }

        if ($dosen_filter) {
            $where_conditions[] = "m.assigned_dosen_id = ?";
            $params[] = $dosen_filter;
        }

        $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);
        
        // ✅ FIXED: Export query dengan nama kolom yang BENAR sesuai struktur database
        $export_query = "
            SELECT 
                m.nim as 'NIM',
                m.nama_lengkap as 'Nama Lengkap',
                m.jenis_kelamin as 'Jenis Kelamin',
                m.jenjang as 'Jenjang',
                m.tempat_tugas as 'Tempat Tugas',
                m.kabupaten as 'Kabupaten',
                m.provinsi as 'Provinsi',
                m.status_pegawai as 'Status Pegawai',
                m.no_telepon as 'No Telepon',
                COALESCE(u.nama_lengkap, 'Belum Di-assign') as 'Dosen Penilai',
                CASE 
                    WHEN p.status_penilaian = 'final' THEN 'Selesai Dinilai'
                    WHEN p.status_penilaian = 'draft' THEN 'Sedang Dinilai'
                    ELSE 'Belum Dinilai'
                END as 'Status Penilaian',
                CASE 
                    WHEN p.status_penilaian IS NOT NULL THEN 
                        CONCAT(
                            'RPL01: ', COALESCE(p.rpl01_huruf_mutu, '-'), ' (', COALESCE(p.rpl01_pedagogik, '-'), ') | ',
                            'RPL02: ', COALESCE(p.rpl02_huruf_mutu, '-'), ' (', COALESCE(p.rpl02_perangkat, '-'), ') | ',
                            'RPL03: ', COALESCE(p.rpl03_huruf_mutu, '-'), ' (', COALESCE(p.rpl03_profesional, '-'), ') | ',
                            'RPL04: ', COALESCE(p.rpl04_huruf_mutu, '-'), ' (', COALESCE(p.rpl04_administrasi, '-'), ') | ',
                            'RPL05: ', COALESCE(p.rpl05_huruf_mutu, '-'), ' (', COALESCE(p.rpl05_inovasi, '-'), ')'
                        )
                    ELSE '-'
                END as 'Nilai RPL',
                m.link_sk_mengajar as 'Link SK Mengajar',
                m.link_administrasi as 'Link Administrasi', 
                m.link_inovasi as 'Link Inovasi'
            FROM mahasiswa m
            LEFT JOIN users u ON m.assigned_dosen_id = u.id
            LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
            $where_clause
            ORDER BY m.nama_lengkap ASC
        ";
        
        $stmt = $pdo->prepare($export_query);
        $stmt->execute($params);
        $export_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate CSV
        $filename = 'data_mahasiswa_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Write header
        if (!empty($export_data)) {
            fputcsv($output, array_keys($export_data[0]));
            
            // Write data
            foreach ($export_data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        
        // Log activity
        logAktivitas($pdo, $_SESSION['user_id'], 'Export Data Mahasiswa', "Export " . count($export_data) . " data mahasiswa");
        exit;
    }
    
    // 🆕 NEW: Unassign Single Mahasiswa
    if ($action === 'unassign_single') {
        $mahasiswa_id = (int)($_POST['mahasiswa_id'] ?? 0);
        
        if (!$mahasiswa_id) {
            $error = 'ID mahasiswa tidak valid!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Get mahasiswa data for logging
                $stmt = $pdo->prepare("SELECT nim, nama_lengkap FROM mahasiswa WHERE id = ?");
                $stmt->execute([$mahasiswa_id]);
                $mahasiswa = $stmt->fetch();
                
                if (!$mahasiswa) {
                    throw new Exception('Mahasiswa tidak ditemukan');
                }
                
                // Reset assignment
                $stmt = $pdo->prepare("UPDATE mahasiswa SET assigned_dosen_id = NULL WHERE id = ?");
                $stmt->execute([$mahasiswa_id]);
                
                // Delete any existing evaluations
                $stmt = $pdo->prepare("DELETE FROM penilaian_rpl WHERE mahasiswa_id = ?");
                $stmt->execute([$mahasiswa_id]);
                
                $pdo->commit();
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Unassign Mahasiswa', "Batalkan assignment: {$mahasiswa['nim']} - {$mahasiswa['nama_lengkap']}");
                $message = "Berhasil membatalkan assignment untuk {$mahasiswa['nama_lengkap']}!";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Gagal membatalkan assignment: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'assign_mahasiswa') {
        $mahasiswa_ids = $_POST['mahasiswa_ids'] ?? [];
        $dosen_id = (int)($_POST['assign_dosen_id'] ?? 0);
        
        if (empty($mahasiswa_ids) || !$dosen_id) {
            $error = 'Pilih mahasiswa dan dosen yang akan ditugaskan!';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE mahasiswa SET assigned_dosen_id = ? WHERE id = ?");
                $assigned = 0;
                
                foreach ($mahasiswa_ids as $mahasiswa_id) {
                    $stmt->execute([$dosen_id, (int)$mahasiswa_id]);
                    $assigned++;
                }
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Assign Mahasiswa Manual', "Assign $assigned mahasiswa ke dosen ID: $dosen_id");
                $message = "Berhasil assign $assigned mahasiswa ke dosen!";
                
            } catch (PDOException $e) {
                $error = 'Gagal assign mahasiswa: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'reset_assignment') {
        $mahasiswa_ids = $_POST['mahasiswa_ids'] ?? [];
        
        if (empty($mahasiswa_ids)) {
            $error = 'Pilih mahasiswa yang akan direset!';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE mahasiswa SET assigned_dosen_id = NULL, status_penilaian = 'belum_dinilai' WHERE id = ?");
                $reset = 0;
                
                foreach ($mahasiswa_ids as $mahasiswa_id) {
                    $stmt->execute([(int)$mahasiswa_id]);
                    $reset++;
                }
                
                // Hapus semua penilaian
                $ids_placeholder = str_repeat('?,', count($mahasiswa_ids) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM penilaian_rpl WHERE mahasiswa_id IN ($ids_placeholder)");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Reset Assignment Mahasiswa', "Reset $reset mahasiswa");
                $message = "Berhasil reset assignment $reset mahasiswa!";
                
            } catch (PDOException $e) {
                $error = 'Gagal reset assignment: ' . $e->getMessage();
            }
        }
    }
    
    // DELETE and other existing actions remain the same...
    if ($action === 'delete_mahasiswa') {
        $mahasiswa_ids = $_POST['mahasiswa_ids'] ?? [];
        
        if (empty($mahasiswa_ids)) {
            $error = 'Pilih mahasiswa yang akan dihapus!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Get nama mahasiswa untuk log
                $ids_placeholder = str_repeat('?,', count($mahasiswa_ids) - 1) . '?';
                $stmt = $pdo->prepare("SELECT nim, nama_lengkap FROM mahasiswa WHERE id IN ($ids_placeholder)");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                $mahasiswa_names = $stmt->fetchAll();
                
                // Hapus penilaian terkait (CASCADE akan handle ini, tapi explicit lebih baik)
                $stmt = $pdo->prepare("DELETE FROM penilaian_rpl WHERE mahasiswa_id IN ($ids_placeholder)");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                
                // Hapus dokumen perangkat terkait
                $stmt = $pdo->prepare("DELETE FROM dokumen_perangkat WHERE mahasiswa_id IN ($ids_placeholder)");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                
                // Hapus mahasiswa
                $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id IN ($ids_placeholder)");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                $deleted = $stmt->rowCount();
                
                $pdo->commit();
                
                // Log detail mahasiswa yang dihapus
                $nama_list = array_map(function($m) { return $m['nim'] . ' - ' . $m['nama_lengkap']; }, $mahasiswa_names);
                logAktivitas($pdo, $_SESSION['user_id'], 'Delete Mahasiswa', "Hapus $deleted mahasiswa: " . implode(', ', $nama_list));
                
                $message = "Berhasil menghapus $deleted mahasiswa beserta semua data penilaiannya!";
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Gagal menghapus mahasiswa: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'reset_penilaian') {
        $mahasiswa_ids = $_POST['mahasiswa_ids'] ?? [];
        
        if (empty($mahasiswa_ids)) {
            $error = 'Pilih mahasiswa yang penilaiannya akan direset!';
        } else {
            try {
                // Update status penilaian dari final ke draft
                $ids_placeholder = str_repeat('?,', count($mahasiswa_ids) - 1) . '?';
                $stmt = $pdo->prepare("
                    UPDATE penilaian_rpl 
                    SET status_penilaian = 'draft' 
                    WHERE mahasiswa_id IN ($ids_placeholder) AND status_penilaian = 'final'
                ");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                $reset_count = $stmt->rowCount();
                
                // Update status mahasiswa
                $stmt = $pdo->prepare("
                    UPDATE mahasiswa 
                    SET status_penilaian = 'sedang_dinilai' 
                    WHERE id IN ($ids_placeholder) AND status_penilaian = 'selesai'
                ");
                $stmt->execute(array_map('intval', $mahasiswa_ids));
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Reset Penilaian Final', "Reset $reset_count penilaian final ke draft");
                
                if ($reset_count > 0) {
                    $message = "Berhasil mereset $reset_count penilaian dari status final ke draft. Dosen dapat mengedit kembali.";
                } else {
                    $message = "Tidak ada penilaian final yang direset. Pastikan mahasiswa yang dipilih memiliki penilaian final.";
                }
                
            } catch (PDOException $e) {
                $error = 'Gagal reset penilaian: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'delete_single') {
        $mahasiswa_id = (int)($_POST['mahasiswa_id'] ?? 0);
        
        if (!$mahasiswa_id) {
            $error = 'ID mahasiswa tidak valid!';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Get data mahasiswa untuk log
                $stmt = $pdo->prepare("SELECT nim, nama_lengkap FROM mahasiswa WHERE id = ?");
                $stmt->execute([$mahasiswa_id]);
                $mahasiswa = $stmt->fetch();
                
                if (!$mahasiswa) {
                    throw new Exception('Mahasiswa tidak ditemukan');
                }
                
                // Hapus penilaian terkait
                $stmt = $pdo->prepare("DELETE FROM penilaian_rpl WHERE mahasiswa_id = ?");
                $stmt->execute([$mahasiswa_id]);
                
                // Hapus dokumen perangkat terkait
                $stmt = $pdo->prepare("DELETE FROM dokumen_perangkat WHERE mahasiswa_id = ?");
                $stmt->execute([$mahasiswa_id]);
                
                // Hapus mahasiswa
                $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
                $stmt->execute([$mahasiswa_id]);
                
                $pdo->commit();
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Delete Mahasiswa Single', "Hapus mahasiswa: {$mahasiswa['nim']} - {$mahasiswa['nama_lengkap']}");
                $message = "Berhasil menghapus mahasiswa {$mahasiswa['nama_lengkap']} beserta semua data penilaiannya!";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Gagal menghapus mahasiswa: ' . $e->getMessage();
            }
        }
    }
}

// Build query with filters (unchanged)
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(m.nim LIKE ? OR m.nama_lengkap LIKE ? OR m.tempat_tugas LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($jenjang_filter) {
    $where_conditions[] = "m.jenjang = ?";
    $params[] = $jenjang_filter;
}

if ($status_filter) {
    $where_conditions[] = "m.status_penilaian = ?";
    $params[] = $status_filter;
}

if ($dosen_filter) {
    $where_conditions[] = "m.assigned_dosen_id = ?";
    $params[] = $dosen_filter;
}

$where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// Get total count
try {
    $count_sql = "SELECT COUNT(*) as total FROM mahasiswa m $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 1;
}

// Get mahasiswa data
try {
    $sql = "
        SELECT m.*, 
               u.nama_lengkap as nama_dosen,
               p.status_penilaian as penilaian_status,
               p.updated_at as tanggal_penilaian
        FROM mahasiswa m 
        LEFT JOIN users u ON m.assigned_dosen_id = u.id
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id
        $where_clause
        ORDER BY m.nama_lengkap
        LIMIT $per_page OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $mahasiswa_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $mahasiswa_list = [];
}

// Get dosen list for assignment
try {
    $stmt = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'dosen' AND status = 'active' ORDER BY nama_lengkap");
    $dosen_options = $stmt->fetchAll();
} catch (PDOException $e) {
    $dosen_options = [];
}

// Get statistics
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(assigned_dosen_id) as assigned,
            COUNT(CASE WHEN status_penilaian = 'selesai' THEN 1 END) as selesai,
            COUNT(CASE WHEN status_penilaian = 'sedang_dinilai' THEN 1 END) as sedang_dinilai
        FROM mahasiswa
    ");
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    $stats = ['total' => 0, 'assigned' => 0, 'selesai' => 0, 'sedang_dinilai' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mahasiswa - <?= APP_NAME ?></title>
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
            padding: 1rem 2rem;
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
        
        /* 🆕 NEW: Export Section Styles */
        .export-section {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .export-section h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .export-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .btn-export {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-export:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        /* 🆕 NEW: Unassign Button Styles */
        .btn-unassign {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 0.25rem;
        }
        
        .btn-unassign:hover {
            background: #c82333;
            transform: translateY(-1px);
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
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        
        .btn:hover { opacity: 0.9; }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .bulk-actions {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            display: none;
        }
        
        .bulk-actions.show {
            display: block;
        }
        
        .action-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
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
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }
        
        .pagination a.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .pagination a:hover {
            background: #f8f9fa;
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
        
        .btn-delete {
            background: #dc3545;
            border: 2px solid #dc3545;
        }
        
        .btn-delete:hover {
            background: #c82333;
            border-color: #c82333;
        }
        
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 8px;
            background: #f8d7da;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .danger-zone h4 {
            color: #721c24;
            margin-bottom: 0.5rem;
        }

        /* NEW: Per Page Selection Styles */
        .per-page-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
            margin-bottom: 1rem;
        }
        
        .per-page-selector label {
            margin: 0;
            font-weight: 600;
            color: white;
        }
        
        .per-page-selector select {
            width: auto;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .per-page-selector select:focus {
            outline: 2px solid rgba(255, 255, 255, 0.8);
            background: white;
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .filter-row { grid-template-columns: 1fr; }
            .export-section { flex-direction: column; text-align: center; gap: 1rem; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
            .action-group { flex-direction: column; }
            .per-page-selector { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Data Mahasiswa</h1>
        <div>
            <a href="import_mahasiswa.php" class="btn btn-success">Import Data</a>
            <a href="dashboard_admin.php" class="btn btn-primary">← Kembali</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
        <?php endif; ?>

        <!-- NEW: Per Page Selection -->
        <div class="per-page-selector">
            <label for="per_page_select">Tampilkan per halaman:</label>
            <select id="per_page_select" onchange="changePerPage(this.value)">
                <?php foreach ($per_page_options as $option): ?>
                    <option value="<?= $option ?>" <?= $per_page === $option ? 'selected' : '' ?>>
                        <?= $option ?> mahasiswa
                    </option>
                <?php endforeach; ?>
            </select>
            <span style="opacity: 0.9;">Saat ini menampilkan: <?= min($per_page, $total_records) ?> dari <?= number_format($total_records) ?> mahasiswa</span>
        </div>
        
        <!-- 🆕 NEW: Export Section -->
        <div class="export-section">
            <div>
                <h3>Export Data Mahasiswa</h3>
                <p>Export data mahasiswa (<?= number_format($total_records) ?> records) sesuai filter yang diterapkan</p>
            </div>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="export_data">
                <!-- Pass current filters -->
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="jenjang" value="<?= htmlspecialchars($jenjang_filter) ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                <input type="hidden" name="dosen" value="<?= $dosen_filter ?>">
                <button type="submit" class="btn-export">Export ke CSV</button>
            </form>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['assigned']) ?></div>
                <div class="stat-label">Sudah Di-assign</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['sedang_dinilai']) ?></div>
                <div class="stat-label">Sedang Dinilai</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['selesai']) ?></div>
                <div class="stat-label">Selesai Dinilai</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <h3 style="margin-bottom: 1rem;">Filter & Pencarian</h3>
            <form method="GET">
                <!-- NEW: Include per_page in filters -->
                <input type="hidden" name="per_page" value="<?= $per_page ?>">
                
                <div class="filter-row">
                    <div class="form-group">
                        <label for="search">Cari (NIM/Nama/Tempat Tugas)</label>
                        <input type="text" id="search" name="search" value="<?= sanitizeInput($search) ?>" 
                               placeholder="Ketik untuk mencari...">
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
                        <label for="status">Status Penilaian</label>
                        <select id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="belum_dinilai" <?= $status_filter === 'belum_dinilai' ? 'selected' : '' ?>>Belum Dinilai</option>
                            <option value="sedang_dinilai" <?= $status_filter === 'sedang_dinilai' ? 'selected' : '' ?>>Sedang Dinilai</option>
                            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosen">Dosen Penilai</label>
                        <select id="dosen" name="dosen">
                            <option value="">Semua Dosen</option>
                            <option value="0" <?= $dosen_filter === 0 && isset($_GET['dosen']) ? 'selected' : '' ?>>Belum Di-assign</option>
                            <?php foreach ($dosen_options as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>" <?= $dosen_filter === $dosen['id'] ? 'selected' : '' ?>>
                                    <?= sanitizeInput($dosen['nama_lengkap']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="manage_mahasiswa.php" class="btn btn-warning">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h3>Data Mahasiswa (<?= number_format($total_records) ?> total)</h3>
                <div>
                    <button onclick="toggleSelectAll()" class="btn btn-sm btn-warning">Pilih Semua</button>
                    <button onclick="showBulkActions()" class="btn btn-sm btn-success">Aksi Massal</button>
                </div>
            </div>
            
            <div id="bulkActions" class="bulk-actions">
                <div style="margin-bottom: 1rem;">
                    <span id="selectedCount">0</span> mahasiswa terpilih
                </div>
                
                <!-- Assignment Actions -->
                <div class="action-group">
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label for="assign_dosen_id">Assign ke dosen:</label>
                        <select id="assign_dosen_id" style="width: auto;">
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($dosen_options as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>"><?= sanitizeInput($dosen['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="assignMahasiswa()" class="btn btn-sm btn-success">
                            Assign
                        </button>
                    </div>
                    
                    <button type="button" onclick="resetAssignment()" class="btn btn-sm btn-warning">
                        Reset Assignment
                    </button>
                </div>
                                
                <!-- Reset Penilaian Actions -->
                <div class="action-group">
                    <button type="button" onclick="resetPenilaian()" class="btn btn-sm btn-info">
                        Reset Penilaian ke Draft
                    </button>
                </div>
                
                <!-- Danger Zone -->
                <div class="danger-zone">
                    <h4>Zona Berbahaya</h4>
                    <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">Operasi di bawah akan menghapus data secara permanen!</p>
                    <button type="button" onclick="deleteMahasiswa()" class="btn btn-sm btn-delete">
                        Hapus Mahasiswa Terpilih
                    </button>
                </div>
            </div>
            
            <?php if (empty($mahasiswa_list)): ?>
                <div style="padding: 3rem; text-align: center; color: #666;">
                    <p>Tidak ada data mahasiswa yang sesuai dengan filter.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>L/P</th>
                            <th>Jenjang</th>
                            <th>Tempat Tugas</th>
                            <th>Status Pegawai</th>
                            <th>Kabupaten</th>
                            <th>Dosen Penilai</th>
                            <th>Status Penilaian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="mahasiswa_ids[]" value="<?= $mhs['id'] ?>" 
                                           class="mahasiswa-checkbox" onchange="updateSelectedCount()">
                                </td>
                                <td><?= sanitizeInput($mhs['nim']) ?></td>
                                <td>
                                    <strong><?= sanitizeInput($mhs['nama_lengkap']) ?></strong>
                                    <?php if ($mhs['no_telepon']): ?>
                                        <br><small style="color: #666;"><?= sanitizeInput($mhs['no_telepon']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= ($mhs['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'badge-info' : 'badge-warning' ?>">
                                        <?= $mhs['jenis_kelamin'] === 'Laki-laki' ? 'L' : ($mhs['jenis_kelamin'] === 'Perempuan' ? 'P' : '?') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= sanitizeInput($mhs['jenjang']) ?></span>
                                </td>
                                <td>
                                    <?= sanitizeInput($mhs['tempat_tugas']) ?>
                                    <?php if ($mhs['provinsi']): ?>
                                        <br><small style="color: #666;"><?= sanitizeInput($mhs['kabupaten'] ?? '') ?>, <?= sanitizeInput($mhs['provinsi']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mhs['status_pegawai']): ?>
                                        <span class="badge badge-success"><?= sanitizeInput($mhs['status_pegawai']) ?></span>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.8rem;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= sanitizeInput($mhs['kabupaten'] ?? '-') ?></td>
                                <td>
                                    <?php if ($mhs['nama_dosen']): ?>
                                        <?= sanitizeInput($mhs['nama_dosen']) ?>
                                        <!-- 🆕 NEW: Unassign Button -->
                                        <button onclick="unassignSingle(<?= $mhs['id'] ?>, '<?= addslashes($mhs['nama_lengkap']) ?>')" 
                                                class="btn-unassign" title="Batalkan Assignment">
                                            ❌
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Belum Di-assign</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mhs['penilaian_status'] === 'final'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php elseif ($mhs['penilaian_status'] === 'draft'): ?>
                                        <span class="badge badge-warning">Draft</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mhs['nama_dosen']): ?>
                                        <a href="penilaian.php?id=<?= $mhs['id'] ?>" class="btn btn-sm btn-primary">
                                            Lihat
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button onclick="deleteSingle(<?= $mhs['id'] ?>, '<?= sanitizeInput($mhs['nama_lengkap']) ?>')" 
                                            class="btn btn-sm btn-danger" title="Hapus mahasiswa">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Prev</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next »</a>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; color: #666; margin-bottom: 2rem;">
                Halaman <?= $page ?> dari <?= $total_pages ?> 
                (<?= number_format($total_records) ?> total data, menampilkan <?= count($mahasiswa_list) ?> per halaman)
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Hidden forms for POST actions -->
    <form id="bulkActionForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="bulkAction">
        <div id="selectedIds"></div>
    </form>
    
    <form id="singleActionForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="singleAction">
        <input type="hidden" name="mahasiswa_id" id="singleMahasiswaId">
    </form>
    
    <!-- 🆕 NEW: Unassign Single Form -->
    <form id="unassignSingleForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="unassign_single">
        <input type="hidden" id="unassign_mahasiswa_id" name="mahasiswa_id">
    </form>
    
    <script>
        let allSelected = false;
        
        // NEW: Change per page function
        function changePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset to first page when changing per_page
            window.location.href = url.toString();
        }
        
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.mahasiswa-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            
            updateSelectedCount();
        }
        
        function updateSelectedCount() {
            const selected = document.querySelectorAll('.mahasiswa-checkbox:checked');
            const countElement = document.getElementById('selectedCount');
            if (countElement) {
                countElement.textContent = selected.length;
            }
            
            // Update selectAll checkbox
            const total = document.querySelectorAll('.mahasiswa-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            if (selectAllCheckbox) {
                if (selected.length === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (selected.length === total.length) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            }
        }
        
        function showBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            bulkActions.classList.toggle('show');
        }
        
        function getSelectedIds() {
            const selected = document.querySelectorAll('.mahasiswa-checkbox:checked');
            return Array.from(selected).map(cb => cb.value);
        }
        
        function submitBulkAction(action) {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Pilih minimal satu mahasiswa!');
                return;
            }
            
            document.getElementById('bulkAction').value = action;
                    
            // Add selected IDs to form
            const idsContainer = document.getElementById('selectedIds');
            idsContainer.innerHTML = '';
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'mahasiswa_ids[]';
                input.value = id;
                idsContainer.appendChild(input);
            });
            
            document.getElementById('bulkActionForm').submit();
        }
        
        function resetAssignment() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Pilih minimal satu mahasiswa!');
                return;
            }
            
            if (confirm(`Reset assignment ${selectedIds.length} mahasiswa? Penilaian yang ada akan dihapus!`)) {
                submitBulkAction('reset_assignment');
            }
        }
        
        function resetPenilaian() {
            const count = getSelectedIds().length;
            if (count === 0) {
                alert('Pilih minimal satu mahasiswa!');
                return;
            }
            
            if (confirm(`Reset ${count} penilaian dari status FINAL ke DRAFT? Dosen akan dapat mengedit kembali penilaian tersebut.`)) {
                submitBulkAction('reset_penilaian');
            }
        }
        
        function deleteMahasiswa() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                alert('Pilih minimal satu mahasiswa!');
                return;
            }
            
            const count = selectedIds.length;
            if (confirm(`PERINGATAN KERAS! 
            
Anda akan menghapus ${count} mahasiswa secara PERMANEN!

Data yang akan dihapus:
• ${count} Data mahasiswa
• Semua penilaian terkait
• Semua dokumen terkait
• Assignment ke dosen

Operasi ini TIDAK DAPAT DIBATALKAN!

Ketik "HAPUS" untuk melanjutkan:`)) {
                const confirmation = prompt('Ketik "HAPUS" untuk konfirmasi:');
                if (confirmation === 'HAPUS') {
                    submitBulkAction('delete_mahasiswa');
                } else {
                    alert('Konfirmasi tidak sesuai. Operasi dibatalkan.');
                }
            }
        }
        
        function deleteSingle(mahasiswaId, namaMahasiswa) {
            if (confirm(`PERINGATAN KERAS!

Anda akan menghapus mahasiswa "${namaMahasiswa}" secara PERMANEN!

Data yang akan dihapus:
• Data mahasiswa
• Semua penilaian terkait
• Semua dokumen terkait
• Assignment ke dosen

Operasi ini TIDAK DAPAT DIBATALKAN!

Lanjutkan?`)) {
                const confirmation = prompt('Ketik "HAPUS" untuk konfirmasi:');
                if (confirmation === 'HAPUS') {
                    document.getElementById('singleAction').value = 'delete_single';
                    document.getElementById('singleMahasiswaId').value = mahasiswaId;
                    document.getElementById('singleActionForm').submit();
                } else {
                    alert('Konfirmasi tidak sesuai. Operasi dibatalkan.');
                }
            }
        }
        
        // 🆕 NEW: Unassign Single Function
        function unassignSingle(mahasiswaId, mahasiswaNama) {
            const confirmMsg = `KONFIRMASI BATALKAN ASSIGNMENT

Anda akan membatalkan assignment untuk mahasiswa:
"${mahasiswaNama}"

Setelah dibatalkan:
• Status akan menjadi "Belum Di-assign"
• Semua penilaian yang ada akan dihapus
• Mahasiswa bisa di-assign ulang ke dosen lain

Lanjutkan?`;

            if (confirm(confirmMsg)) {
                const confirmation = prompt('Ketik "BATALKAN" untuk konfirmasi:');
                if (confirmation === 'BATALKAN') {
                    document.getElementById('unassign_mahasiswa_id').value = mahasiswaId;
                    document.getElementById('unassignSingleForm').submit();
                } else {
                    alert('Konfirmasi tidak sesuai. Operasi dibatalkan.');
                }
            }
        }
        
        // Initialize
        updateSelectedCount();
    </script>

    <script>
    function assignMahasiswa() {
        const selectedIds = getSelectedIds();
        const dosenId = document.getElementById('assign_dosen_id').value;
        
        if (selectedIds.length === 0) {
            alert('Pilih minimal satu mahasiswa!');
            return;
        }
        
        if (!dosenId) {
            alert('Pilih dosen yang akan ditugaskan!');
            return;
        }
        
        // Create form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        // Add action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'assign_mahasiswa';
        form.appendChild(actionInput);
        
        // Add dosen ID
        const dosenInput = document.createElement('input');
        dosenInput.type = 'hidden';
        dosenInput.name = 'assign_dosen_id';
        dosenInput.value = dosenId;
        form.appendChild(dosenInput);
        
        // Add selected mahasiswa IDs
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'mahasiswa_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
    </script>

</body>
</html>