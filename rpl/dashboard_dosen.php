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
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Dosen RPL</h1>
        <div class="user-info">
            <span>Selamat datang, <?= sanitizeInput($_SESSION['nama_lengkap']) ?></span>
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
</body>
</html>