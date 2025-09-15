<?php
// smart_csv_import.php
// Import CSV yang cerdas - otomatis detect dan fix masalah data

require_once 'config.php';

$admin_password = 'admin123';
if (($_GET['password'] ?? '') !== $admin_password) {
    die('Akses ditolak. Gunakan: smart_csv_import.php?password=admin123');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart CSV Import - STKY Merauke</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .info { background: #cce7ff; color: #004085; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; margin: 5px; border: none; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #005a8b; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .upload-area { border: 2px dashed #007cba; border-radius: 10px; padding: 40px; text-align: center; background: #f8fbff; margin: 20px 0; }
        .progress { background: #e9ecef; border-radius: 5px; margin: 15px 0; height: 25px; }
        .progress-bar { background: #007cba; height: 100%; border-radius: 5px; transition: width 0.3s ease; text-align: center; line-height: 25px; color: white; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f8f9fa; font-weight: bold; }
        .highlight { background: #fffacd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧠 Smart CSV Import</h1>
        <p><strong>Import CSV dengan deteksi dan perbaikan data otomatis</strong></p>
        
        <?php
        // Handle different actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['cleanup_db'])) {
                handleCleanupDatabase();
            } elseif (isset($_FILES['csv_file'])) {
                handleSmartCSVImport();
            } elseif (isset($_POST['analyze_csv'])) {
                analyzeMahasiswaDataCSV();
            }
        }
        
        // Function untuk smart CSV import
        function handleSmartCSVImport() {
            try {
                if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error upload file: " . $_FILES['csv_file']['error']);
                }
                
                $csv_file = $_FILES['csv_file']['tmp_name'];
                $pdo = getDBConnection();
                
                echo "<div class='info'><h3>🧠 Smart Analysis & Import</h3></div>";
                
                $file_handle = fopen($csv_file, 'r');
                if (!$file_handle) {
                    throw new Exception("Tidak dapat membaca file CSV");
                }
                
                // Read all data first untuk analysis
                $all_data = [];
                while (($data = fgetcsv($file_handle, 0, ',')) !== FALSE) {
                    $all_data[] = $data;
                }
                fclose($file_handle);
                
                echo "<p><strong>📊 Analisis File:</strong></p>";
                echo "<p>- Total baris: " . count($all_data) . "</p>";
                echo "<p>- Kolom per baris: " . (count($all_data[0] ?? [])) . "</p>";
                
                // Smart analysis untuk mencari kolom yang tepat
                $nim_column = detectNIMColumn($all_data);
                $nama_column = detectNamaColumn($all_data);
                
                echo "<p>- Kolom NIM terdeteksi: <strong>" . ($nim_column !== false ? $nim_column : 'TIDAK DITEMUKAN') . "</strong></p>";
                echo "<p>- Kolom Nama terdeteksi: <strong>" . ($nama_column !== false ? $nama_column : 'TIDAK DITEMUKAN') . "</strong></p>";
                
                if ($nim_column === false || $nama_column === false) {
                    echo "<div class='error'>❌ Struktur file CSV tidak dapat dideteksi otomatis. Pastikan file memiliki kolom NIM dan Nama yang valid.</div>";
                    return;
                }
                
                // Prepare statement
                $stmt = $pdo->prepare("
                    INSERT INTO mahasiswa (
                        nim, nama, nisn, nik, tempat_lahir, tanggal_lahir, jenis_kelamin,
                        no_handphone, email, agama, desa, nama_ibu, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                    ) ON DUPLICATE KEY UPDATE
                        nama = VALUES(nama),
                        email = VALUES(email),
                        no_handphone = VALUES(no_handphone),
                        updated_at = NOW()
                ");
                
                $success_count = 0;
                $error_count = 0;
                $skip_count = 0;
                $errors = [];
                
                echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%;'>0%</div></div>";
                echo "<div id='progressText'>Memulai smart import...</div>";
                
                // Process data dengan smart detection
                for ($i = 1; $i < count($all_data); $i++) { // Skip header
                    $row = $all_data[$i];
                    
                    try {
                        // Smart extract data
                        $nim = smartExtractNIM($row, $nim_column);
                        $nama = smartExtractNama($row, $nama_column);
                        
                        // Skip jika data tidak valid
                        if (empty($nim) || empty($nama)) {
                            $skip_count++;
                            continue;
                        }
                        
                        // Validasi NIM format
                        if (!preg_match('/^\d{8,}$/', $nim)) {
                            $skip_count++;
                            if (count($errors) < 10) {
                                $errors[] = "Baris " . ($i + 1) . ": NIM tidak valid '{$nim}'";
                            }
                            continue;
                        }
                        
                        // Extract data lainnya dengan safe method
                        $nisn = safeExtract($row, $nim_column + 1); // Asumsi NISN setelah NIM
                        $nik = safeExtract($row, 4);
                        $tempat_lahir = safeExtract($row, 5);
                        $tanggal_lahir = smartFormatDate(safeExtract($row, 6));
                        $jenis_kelamin = safeExtract($row, 7);
                        $no_handphone = safeExtract($row, 8);
                        $email = safeExtract($row, 9);
                        $agama = safeExtract($row, 10, 'Katolik');
                        $desa = safeExtract($row, 11);
                        $nama_ibu = safeExtract($row, 13);
                        
                        // Execute insert
                        $stmt->execute([
                            $nim, $nama, $nisn, $nik, $tempat_lahir, $tanggal_lahir,
                            $jenis_kelamin, $no_handphone, $email, $agama, $desa, $nama_ibu
                        ]);
                        
                        $success_count++;
                        
                        // Update progress
                        if ($success_count % 20 == 0) {
                            $progress = min(($i / count($all_data)) * 100, 100);
                            echo "<script>
                                document.getElementById('progressBar').style.width = '{$progress}%';
                                document.getElementById('progressBar').textContent = '{$progress}%';
                                document.getElementById('progressText').textContent = 'Berhasil: {$success_count} data...';
                            </script>";
                            flush();
                        }
                        
                    } catch (Exception $e) {
                        $error_count++;
                        if (count($errors) < 10) {
                            $errors[] = "Baris " . ($i + 1) . ": " . $e->getMessage();
                        }
                    }
                }
                
                echo "<script>
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('progressBar').textContent = '100%';
                    document.getElementById('progressText').textContent = 'Smart import selesai!';
                </script>";
                
                echo "<div class='success'>";
                echo "<h3>✅ Smart CSV Import Berhasil</h3>";
                echo "<p>📊 <strong>Statistik Smart Import:</strong></p>";
                echo "<p>✅ Berhasil diimport: <strong>{$success_count}</strong> data</p>";
                echo "<p>⏭️ Dilewati (data tidak valid): <strong>{$skip_count}</strong> data</p>";
                echo "<p>❌ Error: <strong>{$error_count}</strong> data</p>";
                echo "<p>📁 File: " . $_FILES['csv_file']['name'] . "</p>";
                echo "<p>⏰ Waktu: " . date('d/m/Y H:i:s') . "</p>";
                echo "</div>";
                
                if (!empty($errors)) {
                    echo "<div class='warning'>";
                    echo "<h4>⚠️ Sample Errors (untuk debugging):</h4>";
                    foreach (array_slice($errors, 0, 10) as $error) {
                        echo "<p>• {$error}</p>";
                    }
                    echo "</div>";
                }
                
                // Navigation links
                echo "<div style='margin-top: 30px;'>";
                echo "<a href='../admin/' class='btn btn-success'>📊 Dashboard Admin</a>";
                echo "<a href='../' class='btn'>🎓 Aplikasi Mahasiswa</a>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'><h3>❌ Error Smart Import</h3><p>" . $e->getMessage() . "</p></div>";
            }
        }
        
        // Function untuk cleanup database
        function handleCleanupDatabase() {
            try {
                $pdo = getDBConnection();
                
                echo "<div class='warning'><h3>🧹 Membersihkan Database...</h3></div>";
                
                $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE nim NOT REGEXP '^[0-9]{8,}$' OR nama = '' OR nama IS NULL");
                $stmt->execute();
                $deleted = $stmt->rowCount();
                
                // Reset auto increment
                $stmt = $pdo->query("SELECT MAX(id) as max_id FROM mahasiswa");
                $max_id = $stmt->fetch()['max_id'] ?? 0;
                $pdo->exec("ALTER TABLE mahasiswa AUTO_INCREMENT = " . ($max_id + 1));
                
                echo "<div class='success'>";
                echo "<h3>✅ Database Berhasil Dibersihkan</h3>";
                echo "<p>🗑️ Data tidak valid terhapus: <strong>{$deleted}</strong></p>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'><h3>❌ Error Cleanup</h3><p>" . $e->getMessage() . "</p></div>";
            }
        }
        
        // Function untuk analisis mahasiswa_data.csv yang sudah ada
        function analyzeMahasiswaDataCSV() {
            $csv_file = 'mahasiswa_data.csv';
            if (!file_exists($csv_file)) {
                echo "<div class='error'>❌ File mahasiswa_data.csv tidak ditemukan</div>";
                return;
            }
            
            echo "<div class='info'><h3>🔍 Analisis File mahasiswa_data.csv</h3></div>";
            
            $file_handle = fopen($csv_file, 'r');
            $line_count = 0;
            $sample_data = [];
            $nim_problems = [];
            
            while (($data = fgetcsv($file_handle, 0, ',')) !== FALSE && $line_count < 20) {
                $sample_data[] = $data;
                
                // Check NIM di berbagai kolom
                for ($i = 0; $i < min(10, count($data)); $i++) {
                    $cell = trim($data[$i]);
                    if (!empty($cell) && !preg_match('/^\d{8,}$/', $cell)) {
                        if ($line_count > 0) { // Skip header
                            $nim_problems[] = "Baris " . ($line_count + 1) . " Kolom {$i}: '{$cell}'";
                        }
                    }
                }
                $line_count++;
            }
            fclose($file_handle);
            
            echo "<p><strong>📊 Hasil Analisis:</strong></p>";
            echo "<p>- Baris yang dianalisis: {$line_count}</p>";
            echo "<p>- Kolom per baris: " . (count($sample_data[0] ?? [])) . "</p>";
            
            if (!empty($sample_data)) {
                echo "<h4>📋 Sample Data (5 baris pertama):</h4>";
                echo "<table class='highlight'>";
                echo "<tr><th>Baris</th>";
                for ($i = 0; $i < min(10, count($sample_data[0])); $i++) {
                    echo "<th>Kolom {$i}</th>";
                }
                echo "</tr>";
                
                for ($row = 0; $row < min(5, count($sample_data)); $row++) {
                    echo "<tr><td>" . ($row + 1) . "</td>";
                    for ($col = 0; $col < min(10, count($sample_data[$row])); $col++) {
                        $cell = htmlspecialchars(substr($sample_data[$row][$col], 0, 30));
                        echo "<td>{$cell}</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            if (!empty($nim_problems)) {
                echo "<h4>⚠️ Masalah Terdeteksi (sample):</h4>";
                foreach (array_slice($nim_problems, 0, 10) as $problem) {
                    echo "<p>• {$problem}</p>";
                }
            }
        }
        
        // Smart detection functions
        function detectNIMColumn($data) {
            if (empty($data)) return false;
            
            // Cek setiap kolom untuk pattern NIM yang valid
            for ($col = 0; $col < count($data[0]); $col++) {
                $valid_nims = 0;
                $total_checked = 0;
                
                // Check 10 baris pertama (skip header)
                for ($row = 1; $row < min(11, count($data)); $row++) {
                    if (isset($data[$row][$col])) {
                        $cell = trim($data[$row][$col]);
                        if (!empty($cell)) {
                            $total_checked++;
                            if (preg_match('/^\d{8,}$/', $cell)) {
                                $valid_nims++;
                            }
                        }
                    }
                }
                
                // Jika >80% data di kolom ini adalah NIM valid
                if ($total_checked > 0 && ($valid_nims / $total_checked) > 0.8) {
                    return $col;
                }
            }
            
            return false;
        }
        
        function detectNamaColumn($data) {
            if (empty($data)) return false;
            
            // Cek kolom untuk pattern nama (huruf, spasi, panjang 3-50 karakter)
            for ($col = 0; $col < count($data[0]); $col++) {
                $valid_names = 0;
                $total_checked = 0;
                
                for ($row = 1; $row < min(11, count($data)); $row++) {
                    if (isset($data[$row][$col])) {
                        $cell = trim($data[$row][$col]);
                        if (!empty($cell)) {
                            $total_checked++;
                            // Pattern nama: huruf, spasi, 3-50 karakter
                            if (preg_match('/^[A-Za-z\s]{3,50}$/', $cell) && !preg_match('/^\d/', $cell)) {
                                $valid_names++;
                            }
                        }
                    }
                }
                
                if ($total_checked > 0 && ($valid_names / $total_checked) > 0.8) {
                    return $col;
                }
            }
            
            return false;
        }
        
        function smartExtractNIM($row, $column) {
            $nim = safeExtract($row, $column);
            if (empty($nim)) return null;
            
            // Clean NIM - ambil hanya angka
            $nim = preg_replace('/[^0-9]/', '', $nim);
            
            return (strlen($nim) >= 8) ? $nim : null;
        }
        
        function smartExtractNama($row, $column) {
            $nama = safeExtract($row, $column);
            if (empty($nama)) return null;
            
            // Clean nama - hapus karakter aneh
            $nama = preg_replace('/[^A-Za-z\s]/', '', $nama);
            $nama = trim($nama);
            
            return (strlen($nama) >= 3) ? $nama : null;
        }
        
        function safeExtract($row, $index, $default = null) {
            return isset($row[$index]) && !empty(trim($row[$index])) ? trim($row[$index]) : $default;
        }
        
        function smartFormatDate($dateValue) {
            if (empty($dateValue)) return null;
            
            try {
                if (is_numeric($dateValue) && $dateValue > 30000) {
                    $unix_date = ($dateValue - 25569) * 86400;
                    return date('Y-m-d', $unix_date);
                }
                
                if (is_string($dateValue)) {
                    $date = new DateTime($dateValue);
                    return $date->format('Y-m-d');
                }
            } catch (Exception $e) {
                return null;
            }
            
            return null;
        }
        
        // Show current database status
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
            $total = $stmt->fetch()['total'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as confirmed FROM mahasiswa WHERE confirmed = 1");
            $confirmed = $stmt->fetch()['confirmed'];
            
            echo "<div class='info'>";
            echo "<h3>📊 Status Database Saat Ini</h3>";
            echo "<p>📋 Total data: <strong>{$total}</strong></p>";
            echo "<p>✅ Data confirmed: <strong>{$confirmed}</strong></p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>Error database: " . $e->getMessage() . "</div>";
        }
        ?>
        
        <!-- Cleanup Database Section -->
        <div class="warning">
            <h3>🧹 Langkah 1: Bersihkan Database</h3>
            <p>Hapus data tidak valid dari import sebelumnya</p>
            <form method="post" style="margin: 10px 0;">
                <button type="submit" name="cleanup_db" class="btn btn-danger">🗑️ Cleanup Database</button>
            </form>
        </div>
        
        <!-- Analyze Existing CSV -->
        <div class="info">
            <h3>🔍 Langkah 2: Analisis CSV yang Ada</h3>
            <p>Analisis file mahasiswa_data.csv yang sudah diupload sebelumnya</p>
            <form method="post" style="margin: 10px 0;">
                <button type="submit" name="analyze_csv" class="btn">🔍 Analisis mahasiswa_data.csv</button>
            </form>
        </div>
        
        <!-- Smart CSV Upload Section -->
        <div class="upload-area">
            <h3>🧠 Langkah 3: Smart CSV Import</h3>
            <p><strong>Upload file CSV dengan deteksi otomatis struktur data</strong></p>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required style="margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 400px;">
                <br>
                <button type="submit" class="btn btn-success">🚀 Smart Import CSV</button>
            </form>
            <p><small><strong>Smart Import</strong> akan otomatis mendeteksi kolom NIM dan Nama, mengabaikan data yang tidak valid</small></p>
        </div>
        
        <!-- Additional Tools -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h3>🔧 Tools Tambahan</h3>
            <a href="../admin/" class="btn">📊 Dashboard Admin</a>
            <a href="simple_excel_to_csv.php?password=admin123" class="btn">📄 Manual CSV Import</a>
            <a href="../" class="btn">🎓 Aplikasi Mahasiswa</a>
        </div>
        
        <!-- Instructions -->
        <div class="warning" style="margin-top: 30px;">
            <h3>💡 Tips untuk File Excel/CSV</h3>
            <p><strong>Jika masih bermasalah, coba cara manual:</strong></p>
            <ol>
                <li><strong>Buka Excel</strong> → Pilih hanya kolom yang berisi NIM dan Nama yang valid</li>
                <li><strong>Copy data</strong> → Paste ke Excel baru</li>
                <li><strong>Save as CSV</strong> → Upload menggunakan Smart Import</li>
                <li><strong>Smart Import</strong> akan otomatis mendeteksi dan mengabaikan data invalid</li>
            </ol>
        </div>
    </div>
</body>
</html>