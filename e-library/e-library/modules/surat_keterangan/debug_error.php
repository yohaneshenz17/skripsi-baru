<?php
/**
 * DEBUG ERROR 500 - Cari tahu PERSIS dimana masalahnya
 * Upload ke: /modules/surat_keterangan/debug_error.php
 * Akses: https://stkyakobus.ac.id/e-library/modules/surat_keterangan/debug_error.php
 */

// Tampilkan semua error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>DEBUG</title>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} pre{background:#f5f5f5;padding:10px;}</style>";
echo "</head><body>";
echo "<h1>🔍 DEBUG ERROR 500</h1>";
echo "<hr>";

// =============================================
// TEST 1: PHP VERSION
// =============================================
echo "<h2>1. PHP Version</h2>";
echo "<p class='ok'>✓ PHP Version: <strong>" . phpversion() . "</strong></p>";

// =============================================
// TEST 2: FILE ../../config/database.php
// =============================================
echo "<h2>2. Test File database.php</h2>";
$db_file = '../../config/database.php';

if (file_exists($db_file)) {
    echo "<p class='ok'>✓ File EXISTS: {$db_file}</p>";
    
    // Coba include
    try {
        require_once $db_file;
        echo "<p class='ok'>✓ File LOADED successfully</p>";
        
        // Cek variable $conn
        if (isset($conn)) {
            echo "<p class='ok'>✓ Variable \$conn EXISTS</p>";
            
            // Test connection
            if ($conn->ping()) {
                echo "<p class='ok'>✓ Database CONNECTION OK</p>";
                
                // Get database name
                $db_name = $conn->query("SELECT DATABASE()")->fetch_row()[0];
                echo "<p class='ok'>✓ Database Name: <strong>{$db_name}</strong></p>";
                
            } else {
                echo "<p class='error'>✗ Database connection FAILED</p>";
            }
        } else {
            echo "<p class='error'>✗ Variable \$conn NOT FOUND in database.php</p>";
            echo "<p>File database.php harus define variable \$conn</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error loading database.php: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p class='error'>✗ File NOT FOUND: {$db_file}</p>";
    echo "<p>Cek apakah path benar. Current dir: " . __DIR__ . "</p>";
}

// =============================================
// TEST 3: FILE ../../config/functions.php
// =============================================
echo "<h2>3. Test File functions.php</h2>";
$func_file = '../../config/functions.php';

if (file_exists($func_file)) {
    echo "<p class='ok'>✓ File EXISTS: {$func_file}</p>";
} else {
    echo "<p class='error'>✗ File NOT FOUND: {$func_file}</p>";
    echo "<p><strong>INI MASALAHNYA!</strong> File functions.php tidak ada!</p>";
}

// =============================================
// TEST 4: SESSION
// =============================================
echo "<h2>4. Test Session</h2>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    echo "<p class='ok'>✓ Session admin_id: <strong>" . $_SESSION['admin_id'] . "</strong></p>";
    echo "<p class='ok'>✓ Session admin_username: <strong>" . ($_SESSION['admin_username'] ?? 'N/A') . "</strong></p>";
} else {
    echo "<p class='error'>✗ Session admin_id NOT SET (belum login)</p>";
}

// =============================================
// TEST 5: TABEL DATABASE
// =============================================
echo "<h2>5. Test Tabel Database</h2>";

if (isset($conn)) {
    // Cek tabel mahasiswa
    $result = $conn->query("SHOW TABLES LIKE 'mahasiswa'");
    if ($result->num_rows > 0) {
        echo "<p class='ok'>✓ Tabel 'mahasiswa' EXISTS</p>";
        
        // Show columns
        $cols = $conn->query("SHOW COLUMNS FROM mahasiswa");
        echo "<p><strong>Kolom tabel mahasiswa:</strong></p>";
        echo "<pre>";
        while ($col = $cols->fetch_assoc()) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "</pre>";
        
        // Count records
        $count = $conn->query("SELECT COUNT(*) as total FROM mahasiswa")->fetch_assoc()['total'];
        echo "<p class='ok'>✓ Total mahasiswa: <strong>{$count}</strong></p>";
        
    } else {
        echo "<p class='error'>✗ Tabel 'mahasiswa' NOT FOUND</p>";
    }
    
    // Cek tabel surat_keterangan
    $result = $conn->query("SHOW TABLES LIKE 'surat_keterangan'");
    if ($result->num_rows > 0) {
        echo "<p class='ok'>✓ Tabel 'surat_keterangan' EXISTS</p>";
        
        // Show columns
        $cols = $conn->query("SHOW COLUMNS FROM surat_keterangan");
        echo "<p><strong>Kolom tabel surat_keterangan:</strong></p>";
        echo "<pre>";
        while ($col = $cols->fetch_assoc()) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        echo "</pre>";
        
    } else {
        echo "<p class='error'>✗ Tabel 'surat_keterangan' NOT FOUND</p>";
    }
}

// =============================================
// TEST 6: TEST SEARCH QUERY
// =============================================
echo "<h2>6. Test Search Query</h2>";

if (isset($conn)) {
    $term = "Abner";
    $search = "%{$term}%";
    
    try {
        $query = "SELECT nim, nama, program_studi, angkatan 
                  FROM mahasiswa 
                  WHERE nim LIKE ? OR nama LIKE ?
                  LIMIT 5";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<p class='ok'>✓ Query BERHASIL</p>";
        echo "<p>Search term: <strong>{$term}</strong></p>";
        echo "<p>Found: <strong>{$result->num_rows}</strong> results</p>";
        
        if ($result->num_rows > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
            echo "<tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Angkatan</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['nim']}</td>";
                echo "<td>{$row['nama']}</td>";
                echo "<td>{$row['program_studi']}</td>";
                echo "<td>{$row['angkatan']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Query ERROR: " . $e->getMessage() . "</p>";
    }
}

// =============================================
// TEST 7: FILE PERMISSIONS
// =============================================
echo "<h2>7. File Permissions</h2>";

$files_to_check = [
    'ajax_handler.php',
    'index.php',
    'config.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "<p>File: <strong>{$file}</strong> - Permission: {$perms}</p>";
    } else {
        echo "<p class='error'>File not found: {$file}</p>";
    }
}

// =============================================
// KESIMPULAN
// =============================================
echo "<hr>";
echo "<h2>📋 KESIMPULAN</h2>";

if (!file_exists($func_file)) {
    echo "<div style='background:#ffebee;padding:15px;border-left:4px solid red;'>";
    echo "<h3>❌ MASALAH UTAMA:</h3>";
    echo "<p><strong>File functions.php TIDAK ADA!</strong></p>";
    echo "<p>Path: {$func_file}</p>";
    echo "<p><strong>SOLUSI:</strong> Pakai ajax_handler yang TIDAK require functions.php</p>";
    echo "</div>";
} else if (!isset($conn) || !$conn->ping()) {
    echo "<div style='background:#ffebee;padding:15px;border-left:4px solid red;'>";
    echo "<h3>❌ MASALAH:</h3>";
    echo "<p><strong>Database connection GAGAL!</strong></p>";
    echo "<p><strong>SOLUSI:</strong> Cek file config/database.php</p>";
    echo "</div>";
} else {
    echo "<div style='background:#e8f5e9;padding:15px;border-left:4px solid green;'>";
    echo "<h3>✅ SEMUA OK!</h3>";
    echo "<p>Database connect, tabel ada, query jalan.</p>";
    echo "<p>Masalah kemungkinan di file ajax_handler.php</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>SCREENSHOT HALAMAN INI DAN KIRIM KE SAYA!</strong></p>";

echo "</body></html>";
?>
