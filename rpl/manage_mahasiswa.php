<?php
require_once 'config.php';
requireAdmin();

$message = '';
$error = '';

// Filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$jenjang_filter = $_GET['jenjang'] ?? '';
$status_filter = $_GET['status'] ?? '';
$dosen_filter = (int)($_GET['dosen'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
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
                
                // Hapus penilaian yang ada
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
}

// Build query with filters
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
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .filter-row { grid-template-columns: 1fr; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Data Mahasiswa</h1>
        <div>
            <a href="import_mahasiswa.php" class="btn btn-success">📤 Import Data</a>
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
            <h3 style="margin-bottom: 1rem;">🔍 Filter & Pencarian</h3>
            <form method="GET">
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
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <a href="manage_mahasiswa.php" class="btn btn-warning">🔄 Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Data Mahasiswa (<?= number_format($total_records) ?> total)</h3>
                <div>
                    <button onclick="toggleSelectAll()" class="btn btn-sm btn-warning">☑️ Pilih Semua</button>
                    <button onclick="showBulkActions()" class="btn btn-sm btn-success">⚡ Aksi Massal</button>
                </div>
            </div>
            
            <div id="bulkActions" class="bulk-actions">
                <form method="POST" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div>
                        <span id="selectedCount">0</span> mahasiswa terpilih
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label for="assign_dosen_id">Assign ke dosen:</label>
                        <select id="assign_dosen_id" name="assign_dosen_id">
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($dosen_options as $dosen): ?>
                                <option value="<?= $dosen['id'] ?>"><?= sanitizeInput($dosen['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="action" value="assign_mahasiswa" class="btn btn-sm btn-success">
                            👨‍🏫 Assign
                        </button>
                    </div>
                    
                    <button type="submit" name="action" value="reset_assignment" class="btn btn-sm btn-danger"
                            onclick="return confirm('Reset assignment mahasiswa terpilih? Penilaian yang ada akan dihapus!')">
                        🔄 Reset Assignment
                    </button>
                </form>
            </div>
            
            <?php if (empty($mahasiswa_list)): ?>
                <div style="padding: 3rem; text-align: center; color: #666;">
                    <p>Tidak ada data mahasiswa yang sesuai dengan filter.</p>
                </div>
            <?php else: ?>
                <form id="bulkForm" method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Jenjang</th>
                                <th>Tempat Tugas</th>
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
                                    <td><?= sanitizeInput($mhs['nama_lengkap']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= sanitizeInput($mhs['jenjang']) ?></span>
                                    </td>
                                    <td><?= sanitizeInput($mhs['tempat_tugas']) ?></td>
                                    <td>
                                        <?php if ($mhs['nama_dosen']): ?>
                                            <?= sanitizeInput($mhs['nama_dosen']) ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Belum Di-assign</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($mhs['penilaian_status'] === 'final'): ?>
                                            <span class="badge badge-success">✅ Selesai</span>
                                        <?php elseif ($mhs['penilaian_status'] === 'draft'): ?>
                                            <span class="badge badge-warning">📝 Draft</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">⏳ Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($mhs['nama_dosen']): ?>
                                            <a href="penilaian.php?id=<?= $mhs['id'] ?>" class="btn btn-sm btn-primary">
                                                👁️ Lihat
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
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
                (<?= number_format($total_records) ?> total data)
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        let allSelected = false;
        
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
            document.getElementById('selectedCount').textContent = selected.length;
            
            // Update selectAll checkbox
            const total = document.querySelectorAll('.mahasiswa-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            
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
        
        function showBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            bulkActions.classList.toggle('show');
        }
        
        // Initialize
        updateSelectedCount();
    </script>
</body>
</html>