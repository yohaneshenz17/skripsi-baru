<?php
// admin_dashboard.php
// Dashboard admin untuk monitoring verifikasi data mahasiswa

require_once '../api/config.php';

// Simple authentication
session_start();
$admin_username = 'admin';
$admin_password = 'stky2025'; // Ganti dengan password yang aman

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Username atau password salah';
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - Verifikasi PPG</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            button:hover { background: #005a8b; }
            .error { color: red; margin-bottom: 15px; }
            h2 { text-align: center; margin-bottom: 30px; color: #333; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Admin Dashboard</h2>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Admin is logged in, show dashboard
try {
    $pdo = getDBConnection();
    
    // Handle reset confirmation action
    if (isset($_POST['reset_confirmation']) && isset($_POST['nim'])) {
        $nim = $_POST['nim'];
        
        $stmt = $pdo->prepare("UPDATE mahasiswa SET confirmed = 0, confirmed_at = NULL WHERE nim = ?");
        if ($stmt->execute([$nim])) {
            // Log activity
            $log_stmt = $pdo->prepare("INSERT INTO activity_log (nim, action, details, created_at) VALUES (?, 'CONFIRMATION_RESET', 'Admin membatalkan konfirmasi data', NOW())");
            $log_stmt->execute([$nim]);
            
            $success_message = "Status konfirmasi berhasil dibatalkan untuk NIM: $nim";
        } else {
            $error_message = "Gagal membatalkan konfirmasi untuk NIM: $nim";
        }
    }
    
    // Get statistics
    $stats = [];
    
    // Total mahasiswa
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $stats['total'] = $stmt->fetch()['total'];
    
    // Mahasiswa yang sudah konfirmasi
    $stmt = $pdo->query("SELECT COUNT(*) as confirmed FROM mahasiswa WHERE confirmed = 1");
    $stats['confirmed'] = $stmt->fetch()['confirmed'];
    
    // Mahasiswa yang belum konfirmasi
    $stats['unconfirmed'] = $stats['total'] - $stats['confirmed'];
    
    // Konfirmasi hari ini
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM mahasiswa WHERE DATE(confirmed_at) = CURDATE()");
    $stats['confirmed_today'] = $stmt->fetch()['today'];
    
    // Get search parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Build query for student list
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nim LIKE ? OR nama LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($status_filter === 'confirmed') {
        $where_conditions[] = "confirmed = 1";
    } elseif ($status_filter === 'unconfirmed') {
        $where_conditions[] = "confirmed = 0";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get student list with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $count_query = "SELECT COUNT(*) as total FROM mahasiswa $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);
    
    $query = "
        SELECT nim, nama, email, no_handphone, confirmed, confirmed_at, created_at
        FROM mahasiswa 
        $where_clause 
        ORDER BY created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
    // Recent confirmations
    $stmt = $pdo->query("
        SELECT nama, nim, confirmed_at 
        FROM mahasiswa 
        WHERE confirmed = 1 
        ORDER BY confirmed_at DESC 
        LIMIT 10
    ");
    $recent_confirmations = $stmt->fetchAll();
    
    // Activity log
    $stmt = $pdo->query("
        SELECT l.*, m.nama 
        FROM activity_log l 
        LEFT JOIN mahasiswa m ON l.nim = m.nim 
        ORDER BY l.created_at DESC 
        LIMIT 20
    ");
    $activity_log = $stmt->fetchAll();
    
    // Export confirmed data
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="mahasiswa_confirmed_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'NIM', 'NISN', 'Nama', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 
            'Jenis Kelamin', 'No HP', 'Email', 'Agama', 'Desa', 'Nama Ibu',
            'Kewarganegaraan', 'NPWP', 'Alamat', 'Tanggal Konfirmasi'
        ]);
        
        // Data mahasiswa yang sudah konfirmasi
        $stmt = $pdo->query("SELECT * FROM mahasiswa WHERE confirmed = 1 ORDER BY confirmed_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['nim'], $row['nisn'], $row['nama'], $row['nik'], 
                $row['tempat_lahir'], $row['tanggal_lahir'], $row['jenis_kelamin'],
                $row['no_handphone'], $row['email'], $row['agama'], $row['desa'],
                $row['nama_ibu'], $row['kewarganegaraan'], $row['npwp'],
                $row['alamat_jalan'], $row['confirmed_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Verifikasi Data Mahasiswa PPG</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        
        .header { background: #007cba; color: white; padding: 15px 20px; }
        .header h1 { font-size: 24px; }
        .header .user-info { float: right; margin-top: 5px; }
        .header::after { content: ""; display: table; clear: both; }
        
        .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 36px; font-weight: bold; margin-bottom: 5px; }
        .stat-label { color: #666; font-size: 14px; }
        
        .total { color: #007cba; }
        .confirmed { color: #28a745; }
        .unconfirmed { color: #ffc107; }
        .today { color: #17a2b8; }
        
        .section { background: white; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section-header { padding: 15px 20px; border-bottom: 1px solid #eee; font-weight: bold; }
        .section-content { padding: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; }
        
        .btn { padding: 8px 16px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #005a8b; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-small { padding: 4px 8px; font-size: 12px; margin: 0 2px; }
        
        .action-bar { margin-bottom: 20px; }
        .action-bar .btn { margin-right: 10px; }
        
        .status-confirmed { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        
        .timestamp { color: #666; font-size: 12px; }
        
        .search-filters { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .search-form { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .search-form input, .search-form select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .search-form input[type="text"] { min-width: 250px; }
        
        .pagination { text-align: center; margin: 20px 0; }
        .pagination a { display: inline-block; padding: 8px 12px; margin: 0 2px; text-decoration: none; color: #007cba; border: 1px solid #ddd; border-radius: 4px; }
        .pagination a:hover { background: #f8f9fa; }
        .pagination .current { background: #007cba; color: white; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 400px; }
        .modal-header { font-weight: bold; margin-bottom: 15px; }
        .modal-footer { margin-top: 20px; text-align: right; }
        .modal-footer button { margin-left: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard - Verifikasi Data Mahasiswa PPG</h1>
        <div class="user-info">
            Selamat datang, Admin | 
            <form method="post" style="display: inline;">
                <button type="submit" name="logout" style="background: none; border: none; color: white; cursor: pointer; text-decoration: underline;">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number total"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number confirmed"><?php echo number_format($stats['confirmed']); ?></div>
                <div class="stat-label">Sudah Konfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number unconfirmed"><?php echo number_format($stats['unconfirmed']); ?></div>
                <div class="stat-label">Belum Konfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number today"><?php echo number_format($stats['confirmed_today']); ?></div>
                <div class="stat-label">Konfirmasi Hari Ini</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <a href="?export=1" class="btn btn-success">Export Data Konfirmasi</a>
            <a href="../api/import_excel.php?password=admin123" class="btn" target="_blank">Import Data Excel</a>
            <a href="../" class="btn">Lihat Aplikasi Mahasiswa</a>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Cari NIM, Nama, atau Email..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Sudah Konfirmasi</option>
                    <option value="unconfirmed" <?php echo $status_filter === 'unconfirmed' ? 'selected' : ''; ?>>Belum Konfirmasi</option>
                </select>
                <button type="submit" class="btn">Cari</button>
                <a href="?" class="btn" style="background: #6c757d;">Reset</a>
            </form>
        </div>

        <!-- Student List -->
        <div class="section">
            <div class="section-header">
                Daftar Mahasiswa (<?php echo number_format($total_records); ?> data)
                <?php if ($search || $status_filter !== 'all'): ?>
                    - Hasil pencarian/filter
                <?php endif; ?>
            </div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Status</th>
                            <th>Tgl Konfirmasi</th>
                            <th>Tgl Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['nim']); ?></td>
                            <td><?php echo htmlspecialchars($student['nama']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['no_handphone']); ?></td>
                            <td>
                                <?php if ($student['confirmed']): ?>
                                    <span class="status-confirmed">Sudah Konfirmasi</span>
                                <?php else: ?>
                                    <span class="status-pending">Belum Konfirmasi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($student['confirmed_at']) {
                                    echo date('d/m/Y H:i', strtotime($student['confirmed_at']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($student['created_at'])); ?></td>
                            <td>
                                <?php if ($student['confirmed']): ?>
                                    <button onclick="resetConfirmation('<?php echo htmlspecialchars($student['nim']); ?>', '<?php echo htmlspecialchars($student['nama']); ?>')" 
                                            class="btn btn-danger btn-small">
                                        Batal Konfirmasi
                                    </button>
                                <?php else: ?>
                                    <span style="color: #6c757d; font-size: 12px;">Belum dikonfirmasi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php
                        $params_string = http_build_query(array_merge($_GET, ['page' => $i]));
                        ?>
                        <a href="?<?php echo $params_string; ?>" 
                           class="<?php echo $i === $page ? 'current' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Confirmations -->
        <div class="section">
            <div class="section-header">Konfirmasi Terbaru</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Waktu Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_confirmations as $confirmation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($confirmation['nim']); ?></td>
                            <td><?php echo htmlspecialchars($confirmation['nama']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($confirmation['confirmed_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="section">
            <div class="section-header">Log Aktivitas Terbaru</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activity_log as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['nim']); ?></td>
                            <td><?php echo htmlspecialchars($log['nama'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                switch($log['action']) {
                                    case 'LOGIN_SUCCESS': echo '<span style="color: #007cba;">Login Berhasil</span>'; break;
                                    case 'LOGIN_FAILED': echo '<span style="color: #dc3545;">Login Gagal</span>'; break;
                                    case 'DATA_UPDATED': echo '<span style="color: #ffc107;">Data Diperbarui</span>'; break;
                                    case 'DATA_CONFIRMED': echo '<span style="color: #28a745;">Data Dikonfirmasi</span>'; break;
                                    case 'CONFIRMATION_RESET': echo '<span style="color: #dc3545;">Konfirmasi Dibatalkan</span>'; break;
                                    default: echo htmlspecialchars($log['action']);
                                }
                                ?>
                            </td>
                            <td class="timestamp"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Reset Confirmation Modal -->
    <div id="resetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Konfirmasi Pembatalan</div>
            <div id="resetModalBody">
                Apakah Anda yakin ingin membatalkan status konfirmasi untuk mahasiswa ini?<br>
                Mahasiswa akan dapat mengedit data mereka kembali.
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeResetModal()" class="btn" style="background: #6c757d;">Batal</button>
                <button type="button" onclick="confirmReset()" class="btn btn-danger">Ya, Batalkan Konfirmasi</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for reset confirmation -->
    <form id="resetForm" method="post" style="display: none;">
        <input type="hidden" name="reset_confirmation" value="1">
        <input type="hidden" name="nim" id="resetNim">
    </form>

    <script>
        let currentNim = '';
        let currentNama = '';

        function resetConfirmation(nim, nama) {
            currentNim = nim;
            currentNama = nama;
            
            document.getElementById('resetModalBody').innerHTML = 
                `Apakah Anda yakin ingin membatalkan status konfirmasi untuk:<br><br>
                <strong>NIM:</strong> ${nim}<br>
                <strong>Nama:</strong> ${nama}<br><br>
                Mahasiswa akan dapat mengedit data mereka kembali.`;
            
            document.getElementById('resetModal').style.display = 'block';
        }

        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }

        function confirmReset() {
            document.getElementById('resetNim').value = currentNim;
            document.getElementById('resetForm').submit();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('resetModal');
            if (event.target === modal) {
                closeResetModal();
            }
        }
    </script>
</body>
</html>