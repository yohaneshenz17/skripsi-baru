<?php
/**
 * Upload Batch Foto Mahasiswa
 * File: upload_foto_batch.php
 * 
 * Cara Pakai:
 * 1. Buat folder di server: /rpl/uploads/foto_mahasiswa/
 * 2. Upload semua foto JPG dari folder lokal ke folder tersebut via FTP/File Manager
 * 3. Akses script ini: https://stkyakobus.ac.id/rpl/upload_foto_batch.php
 */

session_start();
require_once 'config.php';

// Security: Hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('❌ Akses ditolak! Hanya admin yang dapat mengakses halaman ini.');
}

// Fungsi untuk konversi nama lengkap ke format nama file
function nameToFilename($nama_lengkap) {
    // Hapus karakter khusus, ganti spasi dengan underscore, uppercase
    $nama = strtoupper($nama_lengkap);
    $nama = preg_replace('/[^A-Z0-9\s_]/', '', $nama); // Hapus karakter spesial
    $nama = preg_replace('/\s+/', '_', $nama); // Ganti spasi dengan underscore
    return $nama . '.jpg';
}

// Proses update database
$hasil = [
    'total' => 0,
    'berhasil' => 0,
    'gagal' => 0,
    'tidak_ada_foto' => 0,
    'detail' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses'])) {
    try {
        // Path folder foto
        $foto_dir = __DIR__ . '/uploads/foto_mahasiswa/';
        
        // Cek folder ada
        if (!is_dir($foto_dir)) {
            throw new Exception("Folder foto tidak ditemukan: $foto_dir");
        }
        
        // Ambil semua mahasiswa
        $stmt = $pdo->query("SELECT id, nama_lengkap, foto FROM mahasiswa ORDER BY nama_lengkap");
        $mahasiswa_list = $stmt->fetchAll();
        
        $hasil['total'] = count($mahasiswa_list);
        
        foreach ($mahasiswa_list as $mhs) {
            $nama_file = nameToFilename($mhs['nama_lengkap']);
            $foto_path = $foto_dir . $nama_file;
            
            // Cek file foto ada
            if (file_exists($foto_path)) {
                // Update database
                $stmt_update = $pdo->prepare("UPDATE mahasiswa SET foto = ? WHERE id = ?");
                if ($stmt_update->execute([$nama_file, $mhs['id']])) {
                    $hasil['berhasil']++;
                    $hasil['detail'][] = [
                        'status' => 'success',
                        'nim' => $mhs['id'],
                        'nama' => $mhs['nama_lengkap'],
                        'file' => $nama_file
                    ];
                } else {
                    $hasil['gagal']++;
                    $hasil['detail'][] = [
                        'status' => 'error',
                        'nama' => $mhs['nama_lengkap'],
                        'pesan' => 'Gagal update database'
                    ];
                }
            } else {
                $hasil['tidak_ada_foto']++;
                $hasil['detail'][] = [
                    'status' => 'no_photo',
                    'nama' => $mhs['nama_lengkap'],
                    'file' => $nama_file
                ];
            }
        }
        
        // Log aktivitas
        $log_detail = sprintf(
            'Total: %d, Berhasil: %d, Tidak Ada Foto: %d, Gagal: %d',
            $hasil['total'],
            $hasil['berhasil'],
            $hasil['tidak_ada_foto'],
            $hasil['gagal']
        );
        
        $stmt = $pdo->prepare("
            INSERT INTO log_aktivitas (user_id, aktivitas, detail, ip_address) 
            VALUES (?, 'Upload Foto Batch', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $log_detail,
            $_SERVER['REMOTE_ADDR']
        ]);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto Batch - Sistem RPL</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
            border-bottom: 3px solid #3498db;
            padding-bottom: 0.5rem;
        }
        
        .info-box {
            background: #e8f4fd;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #3498db;
        }
        
        .warning-box {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #ffc107;
        }
        
        .success-box {
            background: #d4edda;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        
        .error-box {
            background: #f8d7da;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #dc3545;
        }
        
        ol, ul {
            margin-left: 2rem;
            margin-top: 0.5rem;
        }
        
        li {
            margin: 0.5rem 0;
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-back {
            background: #95a5a6;
            margin-left: 1rem;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
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
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        code {
            background: #f8f9fa;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Upload Foto Mahasiswa Batch</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-box">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($_POST['proses'])): ?>
            <!-- Form Instruksi -->
            <div class="info-box">
                <h3>📋 Langkah-Langkah Upload Foto:</h3>
                <ol>
                    <li><strong>Buat folder</strong> di server: <code>/rpl/uploads/foto_mahasiswa/</code></li>
                    <li><strong>Upload semua foto JPG</strong> dari folder lokal Anda:
                        <br><code>E:\Files\STK St. Yakobus\Dokumen PPG\PPG 2025\Batch 4\Extract Foto\downloaded_photos</code>
                        <br>ke folder server menggunakan <strong>File Manager cPanel</strong> atau <strong>FTP</strong>
                    </li>
                    <li><strong>Pastikan format nama file</strong> sesuai: <code>NAMA_LENGKAP.jpg</code>
                        <br>Contoh: <code>ADELINA_FARIA_RITA.jpg</code>
                    </li>
                    <li><strong>Klik tombol "Proses Upload"</strong> di bawah ini</li>
                </ol>
            </div>
            
            <div class="warning-box">
                <h3>⚠️ Perhatian:</h3>
                <ul>
                    <li>Sistem akan <strong>otomatis mencocokkan</strong> nama mahasiswa dengan nama file foto</li>
                    <li>Mahasiswa yang <strong>tidak ada fotonya</strong> akan tetap berfungsi normal (foto tidak wajib)</li>
                    <li>Format nama file: <strong>huruf besar, spasi diganti underscore (_)</strong></li>
                    <li>File yang sudah diupload ke database <strong>tidak akan diupload ulang</strong></li>
                </ul>
            </div>
            
            <form method="POST" style="margin-top: 2rem;">
                <button type="submit" name="proses" class="btn">🚀 Proses Upload Foto</button>
                <a href="dashboard_admin.php" class="btn btn-back">⬅️ Kembali</a>
            </form>
            
        <?php else: ?>
            <!-- Hasil Upload -->
            <div class="success-box">
                <h3>✅ Proses Upload Selesai!</h3>
            </div>
            
            <div class="stats">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-number"><?= $hasil['total'] ?></div>
                    <div class="stat-label">Total Mahasiswa</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="stat-number"><?= $hasil['berhasil'] ?></div>
                    <div class="stat-label">Foto Berhasil</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="stat-number"><?= $hasil['tidak_ada_foto'] ?></div>
                    <div class="stat-label">Tidak Ada Foto</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="stat-number"><?= $hasil['gagal'] ?></div>
                    <div class="stat-label">Gagal</div>
                </div>
            </div>
            
            <?php if (!empty($hasil['detail'])): ?>
                <h3 style="margin-top: 2rem;">📊 Detail Upload:</h3>
                <div style="overflow-x: auto; max-height: 500px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mahasiswa</th>
                                <th>Nama File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hasil['detail'] as $idx => $item): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($item['nama']) ?></td>
                                    <td><?= isset($item['file']) ? htmlspecialchars($item['file']) : '-' ?></td>
                                    <td>
                                        <?php if ($item['status'] === 'success'): ?>
                                            <span class="status-badge badge-success">✓ Berhasil</span>
                                        <?php elseif ($item['status'] === 'no_photo'): ?>
                                            <span class="status-badge badge-warning">⚠ Tidak Ada Foto</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-error">✗ Gagal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 2rem;">
                <form method="POST">
                    <button type="submit" name="proses" class="btn">🔄 Upload Ulang</button>
                </form>
                <a href="dashboard_admin.php" class="btn btn-back">⬅️ Kembali ke Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
