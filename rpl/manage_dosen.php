<?php
require_once 'config.php';
requireAdmin();

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_dosen') {
        $username = sanitizeInput($_POST['username'] ?? '');
        $nama_lengkap = sanitizeInput($_POST['nama_lengkap'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($nama_lengkap) || empty($email) || empty($password)) {
            $error = 'Semua field harus diisi!';
        } else {
            try {
                // Cek username sudah ada atau belum
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetch()) {
                    $error = 'Username sudah digunakan!';
                } else {
                    // Insert dosen baru
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password, nama_lengkap, email, role) 
                        VALUES (?, ?, ?, ?, 'dosen')
                    ");
                    $stmt->execute([$username, $hashed_password, $nama_lengkap, $email]);
                    
                    logAktivitas($pdo, $_SESSION['user_id'], 'Tambah Dosen', "Username: $username, Nama: $nama_lengkap");
                    $message = 'Dosen berhasil ditambahkan!';
                }
            } catch (PDOException $e) {
                $error = 'Gagal menambah dosen: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'edit_dosen') {
        $id = (int)($_POST['id'] ?? 0);
        $nama_lengkap = sanitizeInput($_POST['nama_lengkap'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $status = $_POST['status'] ?? '';
        
        if (empty($nama_lengkap) || empty($email)) {
            $error = 'Nama lengkap dan email harus diisi!';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users SET nama_lengkap = ?, email = ?, status = ? 
                    WHERE id = ? AND role = 'dosen'
                ");
                $stmt->execute([$nama_lengkap, $email, $status, $id]);
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Edit Dosen', "ID: $id, Nama: $nama_lengkap");
                $message = 'Data dosen berhasil diupdate!';
            } catch (PDOException $e) {
                $error = 'Gagal update dosen: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'reset_password') {
        $id = (int)($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($new_password)) {
            $error = 'Password baru harus diisi!';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'dosen'");
                $stmt->execute([$hashed_password, $id]);
                
                logAktivitas($pdo, $_SESSION['user_id'], 'Reset Password Dosen', "ID: $id");
                $message = 'Password dosen berhasil direset!';
            } catch (PDOException $e) {
                $error = 'Gagal reset password: ' . $e->getMessage();
            }
        }
    }
}

// Ambil data dosen dengan statistik
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(m.id) as jumlah_mahasiswa,
               COUNT(p.id) as sudah_dinilai,
               COUNT(CASE WHEN p.status_penilaian = 'draft' THEN 1 END) as sedang_dinilai
        FROM users u 
        LEFT JOIN mahasiswa m ON u.id = m.assigned_dosen_id 
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.dosen_penilai_id = u.id
        WHERE u.role = 'dosen'
        GROUP BY u.id, u.username, u.nama_lengkap, u.email, u.status, u.created_at
        ORDER BY u.nama_lengkap
    ");
    $dosen_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $dosen_list = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dosen - <?= APP_NAME ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
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
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0.5rem;
            font-size: 0.8rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            table { font-size: 0.8rem; }
            .modal-content { margin: 10% auto; width: 95%; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kelola Dosen Penilai RPL</h1>
        <a href="dashboard_admin.php" class="btn btn-primary">← Kembali</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
        <?php endif; ?>
        
        <!-- Add Dosen Form -->
        <div class="card">
            <h3>➕ Tambah Dosen Baru</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <input type="hidden" name="action" value="add_dosen">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Tambah Dosen</button>
                </div>
            </form>
        </div>
        
        <!-- Dosen List -->
        <div class="card">
            <h3>👨‍🏫 Daftar Dosen Penilai (<?= count($dosen_list) ?> orang)</h3>
            
            <?php if (empty($dosen_list)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada dosen penilai terdaftar.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Statistik Penilaian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dosen_list as $dosen): ?>
                            <tr>
                                <td><?= sanitizeInput($dosen['username']) ?></td>
                                <td><?= sanitizeInput($dosen['nama_lengkap']) ?></td>
                                <td><?= sanitizeInput($dosen['email']) ?></td>
                                <td>
                                    <?php if ($dosen['status'] === 'active'): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="stats-row">
                                        <div class="stat-item">
                                            <div style="font-weight: bold;"><?= $dosen['jumlah_mahasiswa'] ?></div>
                                            <div>Ditugaskan</div>
                                        </div>
                                        <div class="stat-item">
                                            <div style="font-weight: bold; color: #27ae60;"><?= $dosen['sudah_dinilai'] ?></div>
                                            <div>Selesai</div>
                                        </div>
                                        <div class="stat-item">
                                            <div style="font-weight: bold; color: #f39c12;"><?= $dosen['sedang_dinilai'] ?></div>
                                            <div>Draft</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button onclick="editDosen(<?= $dosen['id'] ?>, '<?= sanitizeInput($dosen['nama_lengkap']) ?>', '<?= sanitizeInput($dosen['email']) ?>', '<?= $dosen['status'] ?>')" 
                                            class="btn btn-warning btn-sm">✏️ Edit</button>
                                    <button onclick="resetPassword(<?= $dosen['id'] ?>, '<?= sanitizeInput($dosen['nama_lengkap']) ?>')" 
                                            class="btn btn-danger btn-sm">🔑 Reset PW</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Edit Dosen -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>✏️ Edit Data Dosen</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit_dosen">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                
                <div style="text-align: right; margin-top: 1rem;">
                    <button type="button" onclick="closeModal('editModal')" class="btn btn-danger">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Reset Password -->
    <div id="resetModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('resetModal')">&times;</span>
            <h3>🔑 Reset Password</h3>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" id="reset_id" name="id">
                
                <p id="reset_confirm_text" style="margin-bottom: 1rem; color: #666;"></p>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div style="text-align: right; margin-top: 1rem;">
                    <button type="button" onclick="closeModal('resetModal')" class="btn btn-danger">Batal</button>
                    <button type="submit" class="btn btn-success">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editDosen(id, nama, email, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_lengkap').value = nama;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_status').value = status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function resetPassword(id, nama) {
            document.getElementById('reset_id').value = id;
            document.getElementById('reset_confirm_text').textContent = 
                'Reset password untuk dosen: ' + nama;
            document.getElementById('resetModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>