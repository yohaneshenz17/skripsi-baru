<?php
// migrate_database.php
// Script untuk migrasi database - menambahkan kolom baru
// Jalankan sekali saja: http://domain.com/verifikasi/api/migrate_database.php?password=admin123

$admin_password = 'admin123';
if (($_GET['password'] ?? '') !== $admin_password) {
    die('Akses ditolak. Gunakan: migrate_database.php?password=admin123');
}

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Migration</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;}";
echo ".success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#cce7ff;color:#004085;padding:15px;border-radius:5px;margin:10px 0;}";
echo "</style></head><body>";

echo "<h1>🚀 Database Migration Script</h1>";
echo "<p>Script untuk menambahkan kolom-kolom baru ke database mahasiswa</p>";

try {
    $pdo = getDBConnection();
    
    echo "<div class='info'>";
    echo "<h3>📊 Status Database Sebelum Migration</h3>";
    
    // Cek struktur tabel saat ini
    $stmt = $pdo->query("DESCRIBE mahasiswa");
    $columns = $stmt->fetchAll();
    
    $existing_columns = [];
    foreach ($columns as $column) {
        $existing_columns[] = $column['Field'];
    }
    
    echo "<p>Kolom yang sudah ada: " . implode(', ', $existing_columns) . "</p>";
    echo "</div>";
    
    // Kolom yang perlu ditambahkan
    $new_columns = [
        'provinsi' => "VARCHAR(50) NULL AFTER agama",
        'kabupaten_kota' => "VARCHAR(50) NULL AFTER provinsi", 
        'kecamatan' => "VARCHAR(50) NULL AFTER kabupaten_kota"
    ];
    
    $migration_needed = [];
    $already_exists = [];
    
    foreach ($new_columns as $column_name => $column_definition) {
        if (!in_array($column_name, $existing_columns)) {
            $migration_needed[$column_name] = $column_definition;
        } else {
            $already_exists[] = $column_name;
        }
    }
    
    if (!empty($already_exists)) {
        echo "<div class='info'>";
        echo "<h3>✅ Kolom Yang Sudah Ada</h3>";
        echo "<p>" . implode(', ', $already_exists) . "</p>";
        echo "</div>";
    }
    
    if (empty($migration_needed)) {
        echo "<div class='success'>";
        echo "<h3>✅ Database Sudah Up-to-Date</h3>";
        echo "<p>Semua kolom yang diperlukan sudah ada di database.</p>";
        echo "</div>";
    } else {
        echo "<div class='info'>";
        echo "<h3>🔄 Memulai Migration...</h3>";
        echo "</div>";
        
        $pdo->beginTransaction();
        
        try {
            // Tambahkan kolom-kolom baru
            foreach ($migration_needed as $column_name => $column_definition) {
                $sql = "ALTER TABLE mahasiswa ADD COLUMN $column_name $column_definition";
                echo "<p>Menambahkan kolom: <strong>$column_name</strong></p>";
                $pdo->exec($sql);
            }
            
            // Pastikan kolom desa di posisi yang benar
            if (in_array('desa', $existing_columns)) {
                echo "<p>Memindahkan kolom desa ke posisi yang benar...</p>";
                $pdo->exec("ALTER TABLE mahasiswa MODIFY COLUMN desa VARCHAR(50) NULL AFTER kecamatan");
            }
            
            // Tambahkan index untuk performa
            echo "<p>Menambahkan index untuk performa...</p>";
            $indexes = [
                'idx_provinsi' => 'provinsi',
                'idx_kabupaten_kota' => 'kabupaten_kota', 
                'idx_kecamatan' => 'kecamatan'
            ];
            
            foreach ($indexes as $index_name => $column) {
                try {
                    $pdo->exec("ALTER TABLE mahasiswa ADD INDEX $index_name ($column)");
                } catch (Exception $e) {
                    // Index mungkin sudah ada, skip error
                    echo "<p>Index $index_name sudah ada atau tidak bisa dibuat</p>";
                }
            }
            
            // Update data existing
            echo "<p>Mengupdate data kewarganegaraan yang kosong...</p>";
            $stmt = $pdo->prepare("UPDATE mahasiswa SET kewarganegaraan = 'Indonesia' WHERE kewarganegaraan IS NULL OR kewarganegaraan = ''");
            $stmt->execute();
            $updated_rows = $stmt->rowCount();
            echo "<p>Diupdate: $updated_rows baris</p>";
            
            $pdo->commit();
            
            echo "<div class='success'>";
            echo "<h3>✅ Migration Berhasil!</h3>";
            echo "<p>Kolom-kolom baru telah ditambahkan ke database:</p>";
            echo "<ul>";
            foreach ($migration_needed as $column_name => $column_definition) {
                echo "<li><strong>$column_name</strong></li>";
            }
            echo "</ul>";
            echo "</div>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    // Tampilkan status akhir
    echo "<div class='info'>";
    echo "<h3>📊 Status Database Setelah Migration</h3>";
    
    $stmt = $pdo->query("DESCRIBE mahasiswa");
    $columns_after = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>Kolom</th><th>Tipe</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns_after as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Statistik data
    echo "<div class='info'>";
    echo "<h3>📈 Statistik Data</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as confirmed FROM mahasiswa WHERE confirmed = 1");
    $confirmed = $stmt->fetch()['confirmed'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as with_provinsi FROM mahasiswa WHERE provinsi IS NOT NULL AND provinsi != ''");
    $with_provinsi = $stmt->fetch()['with_provinsi'];
    
    echo "<p>📋 Total mahasiswa: <strong>$total</strong></p>";
    echo "<p>✅ Data confirmed: <strong>$confirmed</strong></p>";
    echo "<p>🗺️ Memiliki data provinsi: <strong>$with_provinsi</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='margin: 30px 0;'>";
echo "<a href='../admin/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Dashboard Admin</a>";
echo "<a href='../' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎓 Aplikasi Mahasiswa</a>";
echo "</div>";

echo "</body></html>";
?>