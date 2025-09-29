<?php
require_once 'config.php';
requireAdmin();

// Ambil statistik
try {
    $stats = [];
    
    // Total mahasiswa
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $stats['total_mahasiswa'] = $stmt->fetch()['total'];
    
    // Total dosen
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'dosen'");
    $stats['total_dosen'] = $stmt->fetch()['total'];
    
    // Mahasiswa sudah dinilai
    $stmt = $pdo->query("SELECT COUNT(DISTINCT mahasiswa_id) as total FROM penilaian_rpl WHERE status_penilaian = 'final'");
    $stats['mahasiswa_dinilai'] = $stmt->fetch()['total'];
    
    // Mahasiswa belum dinilai
    $stats['mahasiswa_belum_dinilai'] = $stats['total_mahasiswa'] - $stats['mahasiswa_dinilai'];
    
    // Progress penilaian
    $stats['progress_persen'] = $stats['total_mahasiswa'] > 0 ? 
        round(($stats['mahasiswa_dinilai'] / $stats['total_mahasiswa']) * 100, 1) : 0;
    
} catch (PDOException $e) {
    $stats = [
        'total_mahasiswa' => 0,
        'total_dosen' => 0, 
        'mahasiswa_dinilai' => 0,
        'mahasiswa_belum_dinilai' => 0,
        'progress_persen' => 0
    ];
}

// Handle actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'import_mahasiswa') {
        // Import data mahasiswa dari file Excel (placeholder - nanti bisa dikembangkan)
        $message = 'Fitur import akan dikembangkan. Untuk sementara gunakan insert manual ke database.';
    }
    
    // IMPLEMENTASI BATCH AUTO ASSIGN YANG AMAN
    if ($action === 'assign_random') {
        try {
            // Increase limits untuk operasi besar
            set_time_limit(300); // 5 menit
            ini_set('memory_limit', '256M');
            
            $pdo->beginTransaction();
            
            // Ambil semua dosen aktif
            $stmt = $pdo->query("SELECT id FROM users WHERE role = 'dosen' AND status = 'active'");
            $dosen_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($dosen_list)) {
                throw new Exception('Tidak ada dosen aktif ditemukan');
            }
            
            $dosen_count = count($dosen_list);
            
            // Hitung total mahasiswa yang belum di-assign
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa WHERE assigned_dosen_id IS NULL");
            $total_unassigned = $stmt->fetch()['total'];
            
            if ($total_unassigned == 0) {
                throw new Exception('Tidak ada mahasiswa yang perlu di-assign');
            }
            
            // BATCH PROCESSING - Process dalam chunk kecil untuk keamanan
            $batch_size = 500; // Process 500 records per batch
            $offset = 0;
            $total_assigned = 0;
            $batch_number = 1;
            
            while ($offset < $total_unassigned) {
                // Ambil batch mahasiswa yang belum di-assign
                $stmt = $pdo->prepare("
                    SELECT id FROM mahasiswa 
                    WHERE assigned_dosen_id IS NULL 
                    ORDER BY id 
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute([$batch_size, $offset]);
                $mahasiswa_batch = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($mahasiswa_batch)) {
                    break; // No more records
                }
                
                // Prepare batch update menggunakan CASE WHEN untuk efisiensi
                $update_cases = [];
                $update_params = [];
                $where_ids = [];
                
                foreach ($mahasiswa_batch as $batch_index => $mahasiswa_id) {
                    $global_index = $offset + $batch_index;
                    $dosen_id = $dosen_list[$global_index % $dosen_count];
                    
                    $update_cases[] = "WHEN ? THEN ?";
                    $update_params[] = $mahasiswa_id;
                    $update_params[] = $dosen_id;
                    $where_ids[] = $mahasiswa_id;
                }
                
                // Build CASE WHEN query untuk batch update
                $case_when = implode(' ', $update_cases);
                $where_in = str_repeat('?,', count($where_ids) - 1) . '?';
                
                $batch_update_sql = "
                    UPDATE mahasiswa 
                    SET assigned_dosen_id = CASE id 
                        {$case_when}
                    END 
                    WHERE id IN ({$where_in}) AND assigned_dosen_id IS NULL
                ";
                
                // Execute batch update
                $stmt = $pdo->prepare($batch_update_sql);
                $all_params = array_merge($update_params, $where_ids);
                $stmt->execute($all_params);
                
                $batch_affected = $stmt->rowCount();
                $total_assigned += $batch_affected;
                
                // Log progress setiap 1000 records untuk monitoring
                if ($total_assigned % 1000 == 0 || $batch_number % 3 == 0) {
                    error_log("Auto Assign Progress: Batch {$batch_number} - {$total_assigned}/{$total_unassigned} mahasiswa assigned");
                }
                
                $offset += $batch_size;
                $batch_number++;
                
                // Safety check - avoid infinite loop
                if ($batch_number > 15) { // Max 15 batches (15 * 500 = 7,500 records max per run)
                    error_log("Auto Assign: Reached batch limit for safety, continuing in next run if needed");
                    break;
                }
            }
            
            $pdo->commit();
            
            // Log hasil akhir
            logAktivitas($pdo, $_SESSION['user_id'], 'Assign Mahasiswa', 
                "Berhasil assign {$total_assigned} mahasiswa ke {$dosen_count} dosen dalam {$batch_number} batch");
            
            // Hitung distribusi per dosen
            $avg_per_dosen = round($total_assigned / $dosen_count);
            
            $message = "✅ Berhasil assign {$total_assigned} mahasiswa ke {$dosen_count} dosen secara otomatis dalam {$batch_number} batch. Rata-rata {$avg_per_dosen} mahasiswa per dosen.";
            
            // Jika masih ada yang belum ter-assign karena batch limit
            if ($total_assigned < $total_unassigned) {
                $remaining = $total_unassigned - $total_assigned;
                $message .= " Tersisa {$remaining} mahasiswa - jalankan lagi untuk melanjutkan.";
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal assign mahasiswa: ' . $e->getMessage();
            error_log("Auto assign batch error: " . $e->getMessage());
        } finally {
            // Reset limits ke default
            set_time_limit(30);
            ini_set('memory_limit', '128M');
        }
    }
}

// Ambil data dosen dengan jumlah mahasiswa yang di-assign
try {
    $stmt = $pdo->query("
        SELECT u.id, u.nama_lengkap, u.email, 
               COUNT(m.id) as jumlah_mahasiswa,
               COUNT(p.id) as sudah_dinilai
        FROM users u 
        LEFT JOIN mahasiswa m ON u.id = m.assigned_dosen_id 
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.status_penilaian = 'final'
        WHERE u.role = 'dosen' AND u.status = 'active'
        GROUP BY u.id, u.nama_lengkap, u.email
        ORDER BY u.nama_lengkap
    ");
    $dosen_data = $stmt->fetchAll();
} catch (PDOException $e) {
    $dosen_data = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?= APP_NAME ?></title>
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
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .stat-card.success {
            border-left-color: #27ae60;
        }
        
        .stat-card.warning {
            border-left-color: #f39c12;
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
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
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
        
        /* LOADING INDICATOR STYLES */
        .loading-indicator {
            display: none;
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
            margin-top: 10px;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .loading-indicator.show {
            display: block;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .loading-text {
            font-weight: bold;
            color: #1976d2;
            margin-bottom: 5px;
        }
        
        .loading-details {
            font-size: 0.9rem;
            color: #666;
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
            
            .actions {
                flex-direction: column;
            }
            
            table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Admin RPL</h1>
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
                <div class="stat-number"><?= number_format($stats['total_mahasiswa']) ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_dosen']) ?></div>
                <div class="stat-label">Total Dosen Penilai</div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-number"><?= number_format($stats['mahasiswa_dinilai']) ?></div>
                <div class="stat-label">Sudah Dinilai</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-number"><?= number_format($stats['mahasiswa_belum_dinilai']) ?></div>
                <div class="stat-label">Belum Dinilai</div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="card">
            <h3>Progress Penilaian</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $stats['progress_persen'] ?>%"></div>
            </div>
            <p style="margin-top: 0.5rem; color: #666;">
                <?= $stats['progress_persen'] ?>% selesai 
                (<?= $stats['mahasiswa_dinilai'] ?> dari <?= $stats['total_mahasiswa'] ?> mahasiswa)
            </p>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <h3>Aksi Cepat</h3>
            <div class="actions">
                <form method="POST" style="display: inline;" id="autoAssignForm">
                    <input type="hidden" name="action" value="assign_random">
                    <button type="submit" class="btn btn-success" id="autoAssignBtn"
                            onclick="return confirmAutoAssign()">
                        🎯 Auto Assign ke Dosen (Batch Mode)
                    </button>
                </form>
                
                <a href="manage_mahasiswa.php" class="btn btn-primary">👥 Kelola Mahasiswa</a>
                <a href="manage_dosen.php" class="btn btn-primary">👨‍🏫 Kelola Dosen</a>
                <a href="laporan.php" class="btn btn-primary">📊 Lihat Laporan</a>
            </div>
            
            <!-- LOADING INDICATOR -->
            <div id="loadingIndicator" class="loading-indicator">
                <div class="loading-text">⏳ Sedang memproses assignment dalam batch...</div>
                <div class="loading-details">
                    Sistem sedang memproses data secara aman dalam batch 500 mahasiswa per iterasi. 
                    Proses mungkin memakan waktu 1-3 menit. Mohon tunggu dan jangan refresh halaman.
                </div>
            </div>
        </div>
        
        <!-- Data Dosen -->
        <div class="card">
            <h3>Data Dosen Penilai</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama Dosen</th>
                        <th>Email</th>
                        <th>Mahasiswa Ditugaskan</th>
                        <th>Sudah Dinilai</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dosen_data as $dosen): ?>
                        <?php 
                        $progress = $dosen['jumlah_mahasiswa'] > 0 ? 
                            round(($dosen['sudah_dinilai'] / $dosen['jumlah_mahasiswa']) * 100) : 0;
                        ?>
                        <tr>
                            <td><?= sanitizeInput($dosen['nama_lengkap']) ?></td>
                            <td><?= sanitizeInput($dosen['email']) ?></td>
                            <td><?= $dosen['jumlah_mahasiswa'] ?> mahasiswa</td>
                            <td><?= $dosen['sudah_dinilai'] ?> selesai</td>
                            <td>
                                <?php if ($progress == 100): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif ($progress > 0): ?>
                                    <span class="badge badge-warning"><?= $progress ?>%</span>
                                <?php else: ?>
                                    <span class="badge">Belum mulai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($dosen_data)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #666;">
                                Belum ada data dosen penilai
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmAutoAssign() {
            const confirmed = confirm(
                'Assign mahasiswa secara otomatis ke dosen dengan Batch Mode?\n\n' +
                '✅ KEAMANAN:\n' +
                '• Proses dalam batch 500 mahasiswa per iterasi\n' +
                '• Timeout protection & memory management\n' +
                '• Transaction rollback jika ada error\n\n' +
                '📊 DISTRIBUSI:\n' +
                '• Mahasiswa dibagi merata ke semua dosen aktif\n' +
                '• Mahasiswa yang sudah di-assign tidak akan berubah\n\n' +
                '⏱️ ESTIMASI WAKTU: 1-3 menit untuk 2260 mahasiswa\n\n' +
                'Lanjutkan proses assignment?'
            );
            
            if (confirmed) {
                // Show loading indicator dan disable button
                const btn = document.getElementById('autoAssignBtn');
                const loading = document.getElementById('loadingIndicator');
                
                btn.disabled = true;
                btn.innerHTML = '⏳ Processing...';
                loading.classList.add('show');
                
                // Scroll ke loading indicator
                loading.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                return true;
            }
            return false;
        }
        
        // Auto-hide loading jika ada error atau selesai
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            const loading = document.getElementById('loadingIndicator');
            
            if (alerts.length > 0) {
                loading.classList.remove('show');
            }
        });
    </script>
</body>
</html>