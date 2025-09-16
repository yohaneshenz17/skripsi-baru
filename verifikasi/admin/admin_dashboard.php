<?php
// admin_dashboard.php - Enhanced version dengan fitur hapus dan tambah
require_once '../api/config.php';

session_start();
$admin_username = 'admin';
$admin_password = 'stkmerauke01';

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

if (!isset($_SESSION['admin_logged_in'])) {
    // Login form code (same as before)
    // ...existing login form code...
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Handle tambah mahasiswa baru
    if (isset($_POST['add_student'])) {
        $nim = trim($_POST['nim']);
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $no_hp = trim($_POST['no_hp']);
        $nisn = trim($_POST['nisn'] ?? '');
        
        // Validasi input
        if (empty($nim) || empty($nama) || empty($email)) {
            $error_message = "NIM, Nama, dan Email wajib diisi";
        } elseif (strlen($nim) < 8) {
            $error_message = "NIM harus minimal 8 digit";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Format email tidak valid";
        } else {
            // Cek apakah NIM sudah ada
            $check_stmt = $pdo->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
            $check_stmt->execute([$nim]);
            
            if ($check_stmt->rowCount() > 0) {
                $error_message = "NIM $nim sudah terdaftar";
            } else {
                // Insert mahasiswa baru
                $insert_stmt = $pdo->prepare("
                    INSERT INTO mahasiswa (nim, nisn, nama, email, no_handphone, confirmed, created_at) 
                    VALUES (?, ?, ?, ?, ?, 0, NOW())
                ");
                
                if ($insert_stmt->execute([$nim, $nisn, $nama, $email, $no_hp])) {
                    $success_message = "Mahasiswa $nama berhasil ditambahkan";
                } else {
                    $error_message = "Gagal menambahkan mahasiswa";
                }
            }
        }
    }
    
    // Handle hapus mahasiswa
    if (isset($_POST['delete_student']) && isset($_POST['nim'])) {
        $nim = $_POST['nim'];
        
        $delete_stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE nim = ?");
        if ($delete_stmt->execute([$nim])) {
            $success_message = "Mahasiswa dengan NIM $nim berhasil dihapus";
        } else {
            $error_message = "Gagal menghapus mahasiswa";
        }
    }
    
    // Handle reset confirmation (existing code)
    if (isset($_POST['reset_confirmation']) && isset($_POST['nim'])) {
        $nim = $_POST['nim'];
        
        $stmt = $pdo->prepare("UPDATE mahasiswa SET confirmed = 0, confirmed_at = NULL WHERE nim = ?");
        if ($stmt->execute([$nim])) {
            $success_message = "Status konfirmasi untuk NIM $nim berhasil dibatalkan";
        } else {
            $error_message = "Gagal membatalkan konfirmasi";
        }
    }
    
    // Get statistics
    $total_students = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
    $confirmed_students = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE confirmed = 1")->fetchColumn();
    $unconfirmed_students = $total_students - $confirmed_students;
    $today_confirmed = $pdo->query("SELECT COUNT(*) FROM mahasiswa WHERE DATE(confirmed_at) = CURDATE()")->fetchColumn();
    
    // Get search parameters
    $search = $_GET['search'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Build query with filters
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(nim LIKE ? OR nama LIKE ? OR email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    if ($status_filter === 'confirmed') {
        $where_conditions[] = "confirmed = 1";
    } elseif ($status_filter === 'unconfirmed') {
        $where_conditions[] = "confirmed = 0";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) FROM mahasiswa $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_filtered = $count_stmt->fetchColumn();
    $total_pages = ceil($total_filtered / $per_page);
    
    // Get students data
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
        .section-header { padding: 15px 20px; border-bottom: 1px solid #eee; font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .section-content { padding: 20px; }
        
        .btn { display: inline-block; padding: 8px 16px; margin: 2px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #005a8b; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-small { padding: 4px 8px; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; }
        
        .search-section { margin-bottom: 20px; }
        .search-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .search-form input, .search-form select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .search-form input[type="text"] { min-width: 200px; }
        
        .pagination { margin-top: 20px; text-align: center; }
        .pagination a { display: inline-block; padding: 8px 12px; margin: 0 2px; text-decoration: none; border: 1px solid #ddd; border-radius: 4px; }
        .pagination a.active { background: #007cba; color: white; border-color: #007cba; }
        .pagination a:hover:not(.active) { background: #f8f9fa; }
        
        .status-confirmed { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
        .modal-header { padding: 20px; border-bottom: 1px solid #eee; font-weight: bold; font-size: 18px; }
        .modal-body { padding: 20px; }
        .modal-footer { padding: 15px 20px; border-top: 1px solid #eee; text-align: right; }
        .modal-footer button { margin-left: 10px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #007cba; }
        
        .action-buttons { display: flex; gap: 5px; }
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
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number total"><?php echo number_format($total_students); ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number confirmed"><?php echo number_format($confirmed_students); ?></div>
                <div class="stat-label">Sudah Konfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number unconfirmed"><?php echo number_format($unconfirmed_students); ?></div>
                <div class="stat-label">Belum Konfirmasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number today"><?php echo number_format($today_confirmed); ?></div>
                <div class="stat-label">Konfirmasi Hari Ini</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="section">
            <div class="section-content">
                <button class="btn btn-success" onclick="openAddModal()">+ Tambah Mahasiswa</button>
                <a href="?export=confirmed" class="btn">Export Data Konfirmasi</a>
                <a href="../api/smart_csv_import.php" class="btn">Import Data Excel</a>
                <a href="../" class="btn">Lihat Aplikasi Mahasiswa</a>
            </div>
        </div>

        <!-- Search Section -->
        <div class="section">
            <div class="section-header">
                Pencarian dan Filter
            </div>
            <div class="section-content">
                <form method="get" class="search-form">
                    <input type="text" name="search" placeholder="Cari NIM, Nama, atau Email..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Sudah Konfirmasi</option>
                        <option value="unconfirmed" <?php echo $status_filter === 'unconfirmed' ? 'selected' : ''; ?>>Belum Konfirmasi</option>
                    </select>
                    <button type="submit" class="btn">Cari</button>
                    <a href="?" class="btn">Reset</a>
                </form>
            </div>
        </div>

        <!-- Students List -->
        <div class="section">
            <div class="section-header">
                Daftar Mahasiswa (<?php echo number_format($total_filtered); ?> data)
            </div>
            <div class="section-content">
                <div style="overflow-x: auto;">
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
                                    <div class="action-buttons">
                                        <?php if ($student['confirmed']): ?>
                                            <button onclick="resetConfirmation('<?php echo htmlspecialchars($student['nim']); ?>', '<?php echo htmlspecialchars($student['nama']); ?>')" 
                                                    class="btn btn-danger btn-small">
                                                Batal Konfirmasi
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteStudent('<?php echo htmlspecialchars($student['nim']); ?>', '<?php echo htmlspecialchars($student['nama']); ?>')" 
                                                class="btn btn-danger btn-small">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php $params_string = http_build_query(array_merge($_GET, ['page' => $i])); ?>
                        <a href="?<?php echo $params_string; ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Mahasiswa -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Tambah Mahasiswa Baru</div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nim">NIM *</label>
                        <input type="text" id="nim" name="nim" required maxlength="20" placeholder="Minimal 8 digit">
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Lengkap *</label>
                        <input type="text" id="nama" name="nama" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="no_hp">No. HP</label>
                        <input type="text" id="no_hp" name="no_hp" maxlength="20" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="form-group">
                        <label for="nisn">NISN (Opsional)</label>
                        <input type="text" id="nisn" name="nisn" maxlength="20">
                    </div>
                    <p><small>* Field wajib diisi</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeAddModal()">Batal</button>
                    <button type="submit" name="add_student" class="btn btn-success">Tambah Mahasiswa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Konfirmasi Hapus Mahasiswa</div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus mahasiswa berikut?</p>
                <div id="deleteModalBody"></div>
                <p><strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeDeleteModal()">Batal</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" id="delete_nim" name="nim">
                    <button type="submit" name="delete_student" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reset Konfirmasi -->
    <div id="resetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Konfirmasi Pembatalan</div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan status konfirmasi untuk mahasiswa berikut?</p>
                <div id="resetModalBody"></div>
                <p>Mahasiswa akan dapat mengedit data mereka kembali.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeResetModal()">Batal</button>
                <form method="post" style="display: inline;">
                    <input type="hidden" id="reset_nim" name="nim">
                    <button type="submit" name="reset_confirmation" class="btn btn-danger">Ya, Batalkan</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function deleteStudent(nim, nama) {
            document.getElementById('deleteModalBody').innerHTML = `
                <strong>NIM:</strong> ${nim}<br>
                <strong>Nama:</strong> ${nama}
            `;
            document.getElementById('delete_nim').value = nim;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function resetConfirmation(nim, nama) {
            document.getElementById('resetModalBody').innerHTML = `
                <strong>NIM:</strong> ${nim}<br>
                <strong>Nama:</strong> ${nama}
            `;
            document.getElementById('reset_nim').value = nim;
            document.getElementById('resetModal').style.display = 'block';
        }

        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('nim').addEventListener('input', function() {
            const nim = this.value;
            if (nim.length < 8) {
                this.setCustomValidity('NIM harus minimal 8 digit');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>