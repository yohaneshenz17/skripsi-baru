<?php
require_once 'config.php';
requireLogin();

// Hanya dosen yang bisa akses
if (!isDosen()) {
    header('Location: dashboard_admin.php');
    exit();
}

$dosen_id = $_SESSION['user_id'];

// Ambil statistik dosen
try {
    // Total mahasiswa yang ditugaskan
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mahasiswa WHERE assigned_dosen_id = ?");
    $stmt->execute([$dosen_id]);
    $total_mahasiswa = $stmt->fetch()['total'];
    
    // Mahasiswa sudah dinilai
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM penilaian_rpl 
        WHERE dosen_penilai_id = ? AND status_penilaian = 'final'
    ");
    $stmt->execute([$dosen_id]);
    $sudah_dinilai = $stmt->fetch()['total'];
    
    // Mahasiswa dalam proses penilaian (draft)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM penilaian_rpl 
        WHERE dosen_penilai_id = ? AND status_penilaian = 'draft'
    ");
    $stmt->execute([$dosen_id]);
    $sedang_dinilai = $stmt->fetch()['total'];
    
    $belum_dinilai = $total_mahasiswa - $sudah_dinilai - $sedang_dinilai;
    $progress_persen = $total_mahasiswa > 0 ? round(($sudah_dinilai / $total_mahasiswa) * 100, 1) : 0;
    
} catch (PDOException $e) {
    $total_mahasiswa = 0;
    $sudah_dinilai = 0;
    $sedang_dinilai = 0;
    $belum_dinilai = 0;
    $progress_persen = 0;
}

// FITUR BARU: Ambil statistik nilai RPL per bidang (hanya yang sudah final)
$statistik_rpl = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            rpl01_pedagogik, rpl02_perangkat, rpl03_profesional, 
            rpl04_administrasi, rpl05_inovasi
        FROM penilaian_rpl 
        WHERE dosen_penilai_id = ? AND status_penilaian = 'final'
    ");
    $stmt->execute([$dosen_id]);
    $all_nilai = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proses statistik untuk setiap bidang
    $bidang_list = [
        'rpl01_pedagogik' => 'RPL01 - Pedagogik',
        'rpl02_perangkat' => 'RPL02 - Perangkat',
        'rpl03_profesional' => 'RPL03 - Profesional',
        'rpl04_administrasi' => 'RPL04 - Administrasi',
        'rpl05_inovasi' => 'RPL05 - Inovasi'
    ];
    
    foreach ($bidang_list as $key => $label) {
        $nilai_array = array_filter(array_column($all_nilai, $key), function($v) {
            return $v !== null && $v !== '';
        });
        
        if (count($nilai_array) > 0) {
            $max = max($nilai_array);
            $min = min($nilai_array);
            $avg = round(array_sum($nilai_array) / count($nilai_array), 2);
            
            // Hitung jumlah nilai A, B, C, D, E
            $count_a = count(array_filter($nilai_array, fn($v) => $v >= 80 && $v <= 100));
            $count_b = count(array_filter($nilai_array, fn($v) => $v >= 70 && $v < 80));
            $count_c = count(array_filter($nilai_array, fn($v) => $v >= 60 && $v < 70));
            $count_d = count(array_filter($nilai_array, fn($v) => $v >= 50 && $v < 60));
            $count_e = count(array_filter($nilai_array, fn($v) => $v >= 0 && $v < 50));
        } else {
            $max = 0;
            $min = 0;
            $avg = 0;
            $count_a = 0;
            $count_b = 0;
            $count_c = 0;
            $count_d = 0;
            $count_e = 0;
        }
        
        $statistik_rpl[$key] = [
            'label' => $label,
            'max' => $max,
            'min' => $min,
            'avg' => $avg,
            'count_a' => $count_a,
            'count_b' => $count_b,
            'count_c' => $count_c,
            'count_d' => $count_d,
            'count_e' => $count_e
        ];
    }
    
} catch (PDOException $e) {
    // Jika error, buat data kosong
    $bidang_list = [
        'rpl01_pedagogik' => 'RPL01 - Pedagogik',
        'rpl02_perangkat' => 'RPL02 - Perangkat',
        'rpl03_profesional' => 'RPL03 - Profesional',
        'rpl04_administrasi' => 'RPL04 - Administrasi',
        'rpl05_inovasi' => 'RPL05 - Inovasi'
    ];
    
    foreach ($bidang_list as $key => $label) {
        $statistik_rpl[$key] = [
            'label' => $label,
            'max' => 0,
            'min' => 0,
            'avg' => 0,
            'count_a' => 0,
            'count_b' => 0,
            'count_c' => 0,
            'count_d' => 0,
            'count_e' => 0
        ];
    }
}

// Ambil daftar mahasiswa yang ditugaskan
try {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               p.status_penilaian,
               p.updated_at as tanggal_penilaian,
               p.rpl01_pedagogik, p.rpl02_perangkat, p.rpl03_profesional, 
               p.rpl04_administrasi, p.rpl05_inovasi
        FROM mahasiswa m 
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.dosen_penilai_id = ?
        WHERE m.assigned_dosen_id = ?
        ORDER BY 
            CASE 
                WHEN p.status_penilaian IS NULL THEN 1
                WHEN p.status_penilaian = 'draft' THEN 2
                WHEN p.status_penilaian = 'final' THEN 3
            END,
            m.nama_lengkap
    ");
    $stmt->execute([$dosen_id, $dosen_id]);
    $mahasiswa_list = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $mahasiswa_list = [];
}

// Handle quick actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mulai_penilaian') {
        $mahasiswa_id = (int)($_POST['mahasiswa_id'] ?? 0);
        
        try {
            // Cek apakah sudah ada penilaian
            $stmt = $pdo->prepare("SELECT id FROM penilaian_rpl WHERE mahasiswa_id = ? AND dosen_penilai_id = ?");
            $stmt->execute([$mahasiswa_id, $dosen_id]);
            
            if (!$stmt->fetch()) {
                // Buat record penilaian baru dengan status draft
                $stmt = $pdo->prepare("
                    INSERT INTO penilaian_rpl (mahasiswa_id, dosen_penilai_id, status_penilaian) 
                    VALUES (?, ?, 'draft')
                ");
                $stmt->execute([$mahasiswa_id, $dosen_id]);
                
                logAktivitas($pdo, $dosen_id, 'Mulai Penilaian', "Mahasiswa ID: $mahasiswa_id");
            }
            
            header("Location: penilaian.php?id=$mahasiswa_id");
            exit();
            
        } catch (PDOException $e) {
            $error = 'Gagal memulai penilaian: ' . $e->getMessage();
        }
    }
    
    // Handle ubah password - FITUR BARU
    if ($action === 'ubah_password') {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
        
        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $error = 'Semua field harus diisi!';
        } elseif ($password_baru !== $konfirmasi_password) {
            $error = 'Password baru dan konfirmasi password tidak cocok!';
        } elseif (strlen($password_baru) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } else {
            try {
                // Ambil password lama dari database
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$dosen_id]);
                $user = $stmt->fetch();
                
                // Verifikasi password lama
                if (!password_verify($password_lama, $user['password'])) {
                    $error = 'Password lama tidak sesuai!';
                } else {
                    // Hash password baru
                    $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
                    
                    // Update password
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$password_hash, $dosen_id]);
                    
                    logAktivitas($pdo, $dosen_id, 'Ubah Password', 'Dosen mengubah password sendiri');
                    
                    $message = 'Password berhasil diubah!';
                }
            } catch (PDOException $e) {
                $error = 'Gagal mengubah password: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - <?= APP_NAME ?></title>
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
            background: #27ae60;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .container {
            padding: 2rem;
            max-width: 1200px;
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
            border-left: 4px solid #27ae60;
            text-align: center;
        }
        
        .stat-card.warning {
            border-left-color: #f39c12;
        }
        
        .stat-card.danger {
            border-left-color: #e74c3c;
        }
        
        .stat-card.info {
            border-left-color: #3498db;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #7f8c8d;
            margin-top: 0.5rem;
        }
        
        .progress-bar {
            background: #ecf0f1;
            height: 10px;
            border-radius: 5px;
            margin-top: 1rem;
            overflow: hidden;
        }
        
        .progress-fill {
            background: #27ae60;
            height: 100%;
            transition: width 0.3s;
        }
        
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .card h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
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
            color: #2c3e50;
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
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .mahasiswa-row {
            transition: background 0.3s;
        }
        
        .mahasiswa-row:hover {
            background: #f8f9fa;
        }
        
        /* MODAL STYLES - FITUR BARU */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal.active {
            display: block;
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            animation: slideDown 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .modal-header h2 {
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        .close {
            font-size: 2rem;
            font-weight: bold;
            color: #95a5a6;
            cursor: pointer;
            transition: color 0.3s;
            line-height: 1;
        }
        
        .close:hover {
            color: #e74c3c;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #27ae60;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        
        /* STATISTIK RPL STYLES - FITUR BARU */
        .statistik-rpl-card {
            background: white;
            padding: 1.2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .statistik-rpl-card h3 {
            margin-bottom: 0.8rem;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .table-statistik {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .table-statistik th {
            background: #34495e;
            color: white;
            padding: 0.5rem 0.4rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .table-statistik td {
            padding: 0.5rem 0.4rem;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .table-statistik tbody tr:hover {
            background: #f8f9fa;
        }
        
        .table-statistik td:first-child {
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .nilai-max {
            color: #27ae60;
            font-weight: bold;
        }
        
        .nilai-min {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .nilai-avg {
            color: #3498db;
            font-weight: bold;
        }
        
        .grade-a {
            background: #d4edda;
            color: #155724;
            font-weight: 600;
        }
        
        .grade-b {
            background: #d1ecf1;
            color: #0c5460;
            font-weight: 600;
        }
        
        .grade-c {
            background: #fff3cd;
            color: #856404;
            font-weight: 600;
        }
        
        .grade-d {
            background: #f8d7da;
            color: #721c24;
            font-weight: 600;
        }
        
        .grade-e {
            background: #f8d7da;
            color: #721c24;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .modal-content {
                margin: 20% auto;
                width: 95%;
                padding: 1.5rem;
            }
            
            .table-statistik {
                font-size: 0.75rem;
            }
            
            .table-statistik th,
            .table-statistik td {
                padding: 0.4rem 0.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Dosen Penilai RPL</h1>
        <div class="user-info">
            <span>Selamat datang, <?= sanitizeInput($_SESSION['nama_lengkap']) ?></span>
            <button onclick="openModal()" class="btn btn-warning">🔒 Ubah Password</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
        <?php endif; ?>
        
        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_mahasiswa ?></div>
                <div class="stat-label">Total Mahasiswa Ditugaskan</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= $sudah_dinilai ?></div>
                <div class="stat-label">Sudah Selesai Dinilai</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?= $sedang_dinilai ?></div>
                <div class="stat-label">Sedang Dalam Proses</div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-number"><?= $belum_dinilai ?></div>
                <div class="stat-label">Belum Dinilai</div>
            </div>
        </div>
        
        <!-- STATISTIK NILAI RPL PER BIDANG - FITUR BARU -->
        <div class="statistik-rpl-card">
            <h3>📊 Statistik Nilai Per Bidang RPL (Penilaian Final)</h3>
            <table class="table-statistik">
                <thead>
                    <tr>
                        <th>Bidang</th>
                        <th>Nilai Maks.</th>
                        <th>Nilai Min.</th>
                        <th>Rata-rata</th>
                        <th>Nilai A</th>
                        <th>Nilai B</th>
                        <th>Nilai C</th>
                        <th>Nilai D</th>
                        <th>Nilai E</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statistik_rpl as $stat): ?>
                        <tr>
                            <td><?= sanitizeInput($stat['label']) ?></td>
                            <td class="nilai-max"><?= $stat['max'] > 0 ? number_format($stat['max'], 2) : '-' ?></td>
                            <td class="nilai-min"><?= $stat['min'] > 0 ? number_format($stat['min'], 2) : '-' ?></td>
                            <td class="nilai-avg"><?= $stat['avg'] > 0 ? number_format($stat['avg'], 2) : '-' ?></td>
                            <td class="grade-a"><?= $stat['count_a'] ?></td>
                            <td class="grade-b"><?= $stat['count_b'] ?></td>
                            <td class="grade-c"><?= $stat['count_c'] ?></td>
                            <td class="grade-d"><?= $stat['count_d'] ?></td>
                            <td class="grade-e"><?= $stat['count_e'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #7f8c8d;">
                <strong>Keterangan:</strong> A = 80-100 | B = 70-79.9 | C = 60-69.9 | D = 50-59.9 | E = 0-49.9
            </p>
        </div>
        
        <!-- Progress Bar -->
        <div class="card">
            <h3>Progress Penilaian Anda</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress_persen ?>%"></div>
            </div>
            <p style="margin-top: 0.5rem; color: #666;">
                <?= $progress_persen ?>% selesai 
                (<?= $sudah_dinilai ?> dari <?= $total_mahasiswa ?> mahasiswa)
            </p>
        </div>
        
        <!-- Daftar Mahasiswa -->
        <div class="card">
            <h3>Daftar Mahasiswa Yang Harus Dinilai</h3>
            
            <?php if (empty($mahasiswa_list)): ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <p>Belum ada mahasiswa yang ditugaskan kepada Anda.</p>
                    <p>Silakan hubungi admin untuk mendapatkan assignment mahasiswa.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Jenjang</th>
                            <th>Tempat Tugas</th>
                            <th>Status Penilaian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <tr class="mahasiswa-row">
                                <td><?= sanitizeInput($mhs['nim']) ?></td>
                                <td><?= sanitizeInput($mhs['nama_lengkap']) ?></td>
                                <td><?= sanitizeInput($mhs['jenjang']) ?></td>
                                <td><?= sanitizeInput($mhs['tempat_tugas']) ?></td>
                                <td>
                                    <?php if ($mhs['status_penilaian'] === 'final'): ?>
                                        <span class="badge badge-success">✓ Selesai</span>
                                        <br><small style="color: #666;">
                                            <?= formatTanggalIndo($mhs['tanggal_penilaian']) ?>
                                        </small>
                                    <?php elseif ($mhs['status_penilaian'] === 'draft'): ?>
                                        <span class="badge badge-warning">📝 Draft</span>
                                        <br><small style="color: #666;">Sedang dikerjakan</small>
                                    <?php else: ?>
                                        <span class="badge badge-danger">⏳ Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($mhs['status_penilaian'] === 'final'): ?>
                                        <a href="penilaian.php?id=<?= $mhs['id'] ?>" class="btn btn-primary btn-sm">
                                            👁️ Lihat Hasil
                                        </a>
                                    <?php elseif ($mhs['status_penilaian'] === 'draft'): ?>
                                        <a href="penilaian.php?id=<?= $mhs['id'] ?>" class="btn btn-warning btn-sm">
                                            📝 Lanjutkan
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="mulai_penilaian">
                                            <input type="hidden" name="mahasiswa_id" value="<?= $mhs['id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                🚀 Mulai Penilaian
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($mahasiswa_list)): ?>
            <!-- Tips -->
            <div class="card">
                <h3>💡 Tips Penilaian</h3>
                <ul style="margin-left: 1.5rem; color: #666;">
                    <li>Klik "Mulai Penilaian" untuk memulai menilai mahasiswa baru</li>
                    <li>Anda dapat menyimpan penilaian sebagai draft dan melanjutkan nanti</li>
                    <li>Pastikan semua dokumen di Google Drive dapat diakses sebelum menilai</li>
                    <li>Berikan skor 0-100 untuk setiap bidang sesuai rubrik yang tersedia</li>
                    <li>Setelah finalisasi, penilaian tidak dapat diubah lagi</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- MODAL UBAH PASSWORD - FITUR BARU -->
    <div id="modalPassword" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔒 Ubah Password</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" onsubmit="return validatePassword()">
                <input type="hidden" name="action" value="ubah_password">
                
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" required>
                </div>
                
                <div class="form-group">
                    <label for="password_baru">Password Baru (minimal 6 karakter)</label>
                    <input type="password" id="password_baru" name="password_baru" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // JAVASCRIPT UNTUK MODAL - FITUR BARU
        function openModal() {
            document.getElementById('modalPassword').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('modalPassword').classList.remove('active');
            document.getElementById('password_lama').value = '';
            document.getElementById('password_baru').value = '';
            document.getElementById('konfirmasi_password').value = '';
        }
        
        function validatePassword() {
            var passwordBaru = document.getElementById('password_baru').value;
            var konfirmasiPassword = document.getElementById('konfirmasi_password').value;
            
            if (passwordBaru !== konfirmasiPassword) {
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }
            
            if (passwordBaru.length < 6) {
                alert('Password baru minimal 6 karakter!');
                return false;
            }
            
            return true;
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('modalPassword');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Auto-close alert after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>