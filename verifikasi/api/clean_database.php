<?php
// clean_database.php
// Script untuk membersihkan data corrupt dari database

require_once 'config.php';

// Password admin
$admin_password = 'admin123';
$input_password = $_GET['password'] ?? '';

if ($input_password !== $admin_password) {
    die('Akses ditolak. Gunakan URL: clean_database.php?password=admin123');
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Cleanup</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;}";
echo ".success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".warning{background:#fff3cd;color:#856404;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#cce7ff;color:#004085;padding:15px;border-radius:5px;margin:10px 0;}";
echo "table{border-collapse:collapse;width:100%;margin:10px 0;}";
echo "th,td{border:1px solid #ddd;padding:8px;text-align:left;}";
echo "th{background:#f8f9fa;}";
echo "</style></head><body>";

echo "<h1>🧹 Database Cleanup Tool</h1>";

try {
    $pdo = getDBConnection();
    
    // Tampilkan data saat ini
    echo "<div class='info'>";
    echo "<h3>📊 Data Saat Ini</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total = $stmt->fetch()['total'];
    echo "<p>Total records: <strong>{$total}</strong></p>";
    
    // Tampilkan data corrupt
    $stmt = $pdo->query("
        SELECT id, nim, nama, created_at 
        FROM mahasiswa 
        WHERE nim LIKE '%tambahkan%' 
           OR nama LIKE '%WAJIB DIISI%' 
           OR nama LIKE '%tambahkan%'
           OR LENGTH(nim) > 20
        ORDER BY id
    ");
    $corrupt_data = $stmt->fetchAll();
    
    if (!empty($corrupt_data)) {
        echo "<h4>🔍 Data Corrupt Ditemukan:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Created At</th></tr>";
        foreach ($corrupt_data as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars(substr($row['nim'], 0, 50)) . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['nama'], 0, 50)) . "</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ Tidak ada data corrupt ditemukan</p>";
    }
    echo "</div>";
    
    // Handle cleanup action
    if (isset($_POST['cleanup'])) {
        echo "<div class='warning'>🧹 Memulai proses cleanup...</div>";
        
        $pdo->beginTransaction();
        
        try {
            // 1. Hapus data corrupt
            $stmt = $pdo->prepare("
                DELETE FROM mahasiswa 
                WHERE nim LIKE '%tambahkan%' 
                   OR nama LIKE '%WAJIB DIISI%' 
                   OR nama LIKE '%tambahkan%'
                   OR LENGTH(nim) > 20
                   OR nim = ''
                   OR nama = ''
            ");
            $deleted = $stmt->execute();
            $deleted_count = $stmt->rowCount();
            
            // 2. Reset auto increment
            $stmt = $pdo->query("SELECT MAX(id) as max_id FROM mahasiswa");
            $max_id = $stmt->fetch()['max_id'] ?? 0;
            $new_auto_increment = $max_id + 1;
            
            $pdo->exec("ALTER TABLE mahasiswa AUTO_INCREMENT = {$new_auto_increment}");
            
            // 3. Cleanup activity log untuk NIM yang tidak valid
            $stmt = $pdo->prepare("
                DELETE FROM activity_log 
                WHERE nim LIKE '%tambahkan%' 
                   OR nim = ''
                   OR LENGTH(nim) > 20
            ");
            $stmt->execute();
            $log_deleted = $stmt->rowCount();
            
            // 4. Update data yang masih valid tapi ada masalah format
            $stmt = $pdo->prepare("
                UPDATE mahasiswa 
                SET 
                    nim = TRIM(nim),
                    nama = TRIM(nama),
                    email = TRIM(email),
                    no_handphone = TRIM(no_handphone),
                    updated_at = NOW()
                WHERE nim REGEXP '^[0-9]+$' 
                  AND nama != ''
            ");
            $stmt->execute();
            $updated_count = $stmt->rowCount();
            
            $pdo->commit();
            
            echo "<div class='success'>";
            echo "<h3>✅ Cleanup Berhasil</h3>";
            echo "<p>🗑️ Data corrupt terhapus: <strong>{$deleted_count}</strong></p>";
            echo "<p>📝 Activity log dibersihkan: <strong>{$log_deleted}</strong></p>";
            echo "<p>🔄 Data diperbarui: <strong>{$updated_count}</strong></p>";
            echo "<p>🆔 Auto increment direset ke: <strong>{$new_auto_increment}</strong></p>";
            echo "</div>";
            
            // Log cleanup activity
            logActivity('SYSTEM', 'DATABASE_CLEANUP', null, [
                'deleted_count' => $deleted_count,
                'updated_count' => $updated_count,
                'log_deleted' => $log_deleted
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    // Handle reset database
    if (isset($_POST['reset_all'])) {
        if ($_POST['confirm_reset'] === 'DELETE ALL DATA') {
            echo "<div class='warning'>🔥 Mereset seluruh database...</div>";
            
            $pdo->beginTransaction();
            
            try {
                // Hapus semua data
                $pdo->exec("DELETE FROM activity_log");
                $pdo->exec("DELETE FROM mahasiswa");
                
                // Reset auto increment
                $pdo->exec("ALTER TABLE activity_log AUTO_INCREMENT = 1");
                $pdo->exec("ALTER TABLE mahasiswa AUTO_INCREMENT = 1");
                
                $pdo->commit();
                
                echo "<div class='success'>";
                echo "<h3>🔥 Database berhasil direset</h3>";
                echo "<p>Semua data telah dihapus dan auto increment direset</p>";
                echo "</div>";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            echo "<div class='error'>❌ Konfirmasi reset tidak valid</div>";
        }
    }
    
    // Tampilkan statistik setelah cleanup
    echo "<div class='info'>";
    echo "<h3>📊 Statistik Database</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as confirmed FROM mahasiswa WHERE confirmed = 1");
    $confirmed = $stmt->fetch()['confirmed'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as valid_nim FROM mahasiswa WHERE nim REGEXP '^[0-9]{8,}$'");
    $valid_nim = $stmt->fetch()['valid_nim'];
    
    echo "<p>📋 Total data: <strong>{$total}</strong></p>";
    echo "<p>✅ Data confirmed: <strong>{$confirmed}</strong></p>";
    echo "<p>🔢 NIM valid: <strong>{$valid_nim}</strong></p>";
    echo "</div>";
    
    // Tampilkan data valid
    if ($total > 0) {
        echo "<div class='info'>";
        echo "<h3>📋 Data Valid Saat Ini</h3>";
        
        $stmt = $pdo->query("
            SELECT nim, nama, email, confirmed, created_at 
            FROM mahasiswa 
            WHERE nim REGEXP '^[0-9]{8,}$' 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $valid_data = $stmt->fetchAll();
        
        if (!empty($valid_data)) {
            echo "<table>";
            echo "<tr><th>NIM</th><th>Nama</th><th>Email</th><th>Status</th><th>Created</th></tr>";
            foreach ($valid_data as $row) {
                $status = $row['confirmed'] ? '✅ Confirmed' : '⏳ Pending';
                echo "<tr>";
                echo "<td>{$row['nim']}</td>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>{$status}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Tidak ada data valid</p>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

// Form actions
echo "<hr>";
echo "<h3>🛠️ Actions</h3>";

// Cleanup form
echo "<form method='post' style='margin: 20px 0;'>";
echo "<p><strong>🧹 Cleanup Data Corrupt:</strong></p>";
echo "<p>Menghapus data yang mengandung teks instruksi Excel, NIM tidak valid, dll.</p>";
echo "<button type='submit' name='cleanup' style='background:#ffc107;color:#000;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;'>🧹 Cleanup Data Corrupt</button>";
echo "</form>";

// Reset form
echo "<form method='post' style='margin: 20px 0; border: 2px solid #dc3545; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>🔥 DANGER: Reset Seluruh Database</strong></p>";
echo "<p style='color: #dc3545;'>Ini akan menghapus SEMUA data mahasiswa dan activity log!</p>";
echo "<p>Ketik '<strong>DELETE ALL DATA</strong>' untuk konfirmasi:</p>";
echo "<input type='text' name='confirm_reset' placeholder='DELETE ALL DATA' style='padding:8px;margin:5px 0;' required>";
echo "<br><button type='submit' name='reset_all' style='background:#dc3545;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;margin-top:10px;'>🔥 Reset Database</button>";
echo "</form>";

// Navigation
echo "<hr>";
echo "<div style='margin: 30px 0;'>";
echo "<a href='import_excel_robust.php?password=admin123' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Import Excel</a>";
echo "<a href='../admin/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Dashboard Admin</a>";
echo "<a href='../' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎓 Aplikasi Mahasiswa</a>";
echo "</div>";

echo "</body></html>";
?>