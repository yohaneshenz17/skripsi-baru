<?php
// simple_excel_to_csv.php
// Script sederhana untuk convert Excel ke CSV dan import ke database

require_once 'config.php';

$admin_password = 'admin123';
if (($_GET['password'] ?? '') !== $admin_password) {
    die('Akses ditolak. Gunakan: simple_excel_to_csv.php?password=admin123');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel to CSV Import - STKY Merauke</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; line-height: 1.6; }
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
        .upload-area:hover { border-color: #005a8b; background: #f0f8ff; }
        .progress { background: #e9ecef; border-radius: 5px; margin: 15px 0; height: 25px; }
        .progress-bar { background: #007cba; height: 100%; border-radius: 5px; transition: width 0.3s ease; text-align: center; line-height: 25px; color: white; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f8f9fa; font-weight: bold; }
        ol { padding-left: 20px; }
        ol li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Import Data Mahasiswa via CSV</h1>
        <p><strong>Sekolah Tinggi Katolik Santo Yakobus Merauke</strong></p>
        
        <?php
        // Handle file upload dan import
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['cleanup_db'])) {
                handleCleanupDatabase();
            } elseif (isset($_FILES['csv_file'])) {
                handleCSVImport();
            }
        }
        
        // Function untuk cleanup database
        function handleCleanupDatabase() {
            try {
                $pdo = getDBConnection();
                
                echo "<div class='warning'><h3>🧹 Membersihkan Database...</h3></div>";
                
                // Hapus data corrupt
                $stmt = $pdo->prepare("
                    DELETE FROM mahasiswa 
                    WHERE nim LIKE '%tambahkan%' 
                       OR nama LIKE '%WAJIB DIISI%' 
                       OR nama LIKE '%tambahkan%'
                       OR LENGTH(nim) > 20
                       OR nim = ''
                       OR nama = ''
                       OR nim IS NULL
                       OR nama IS NULL
                ");
                $stmt->execute();
                $deleted = $stmt->rowCount();
                
                // Reset auto increment
                $stmt = $pdo->query("SELECT MAX(id) as max_id FROM mahasiswa");
                $max_id = $stmt->fetch()['max_id'] ?? 0;
                $new_auto_increment = $max_id + 1;
                $pdo->exec("ALTER TABLE mahasiswa AUTO_INCREMENT = {$new_auto_increment}");
                
                echo "<div class='success'>";
                echo "<h3>✅ Database Berhasil Dibersihkan</h3>";
                echo "<p>🗑️ Data corrupt terhapus: <strong>{$deleted}</strong></p>";
                echo "<p>🆔 Auto increment direset ke: <strong>{$new_auto_increment}</strong></p>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'><h3>❌ Error Cleanup</h3><p>" . $e->getMessage() . "</p></div>";
            }
        }
        
        // Function untuk import CSV
        function handleCSVImport() {
            try {
                if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error upload file: " . $_FILES['csv_file']['error']);
                }
                
                $csv_file = $_FILES['csv_file']['tmp_name'];
                $pdo = getDBConnection();
                
                echo "<div class='info'><h3>📄 Memproses file CSV...</h3></div>";
                
                $file_handle = fopen($csv_file, 'r');
                if (!$file_handle) {
                    throw new Exception("Tidak dapat membaca file CSV");
                }
                
                // Skip header jika ada
                $header = fgetcsv($file_handle);
                echo "<p><strong>Header CSV:</strong> " . implode(', ', array_slice($header, 0, 10)) . "...</p>";
                
                // Prepare statement
                $stmt = $pdo->prepare("
                    INSERT INTO mahasiswa (
                        nim, nisn, nama, nik, tempat_lahir, tanggal_lahir, jenis_kelamin,
                        no_handphone, email, agama, desa, nama_ibu, kode_prodi, 
                        tanggal_masuk, semester_masuk, jenis_pendaftaran, jalur_pendaftaran,
                        biaya_awal_masuk, jenis_pembiayaan, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                    ) ON DUPLICATE KEY UPDATE
                        nama = VALUES(nama),
                        email = VALUES(email),
                        no_handphone = VALUES(no_handphone),
                        updated_at = NOW()
                ");
                
                $success_count = 0;
                $error_count = 0;
                $line_number = 1;
                $errors = [];
                
                echo "<div class='progress'><div class='progress-bar' id='progressBar' style='width: 0%;'>0%</div></div>";
                echo "<div id='progressText'>Memulai import...</div>";
                
                while (($data = fgetcsv($file_handle, 0, ',')) !== FALSE) {
                    $line_number++;
                    
                    // Skip empty lines
                    if (empty($data[1]) || empty($data[3])) continue;
                    
                    try {
                        // Clean dan extract data
                        $nim = cleanCSVData($data[1]);
                        $nisn = cleanCSVData($data[2]);
                        $nama = cleanCSVData($data[3]);
                        $nik = cleanCSVData($data[4]);
                        $tempat_lahir = cleanCSVData($data[5]);
                        $tanggal_lahir = formatCSVDate($data[6]);
                        $jenis_kelamin = cleanCSVData($data[7]);
                        $no_handphone = cleanCSVData($data[8]);
                        $email = cleanCSVData($data[9]);
                        $agama = cleanCSVData($data[10] ?? 'Katolik');
                        $desa = cleanCSVData($data[11]);
                        $nama_ibu = cleanCSVData($data[13]);
                        $kode_prodi = cleanCSVData($data[14]);
                        $tanggal_masuk = formatCSVDate($data[15]);
                        $semester_masuk = cleanCSVData($data[16]);
                        $jenis_pendaftaran = cleanCSVData($data[17]);
                        $jalur_pendaftaran = cleanCSVData($data[18]);
                        $biaya_awal_masuk = is_numeric($data[21] ?? 0) ? floatval($data[21]) : 0;
                        $jenis_pembiayaan = cleanCSVData($data[22]);
                        
                        // Validasi data wajib
                        if (empty($nim) || empty($nama)) {
                            throw new Exception("NIM dan Nama wajib diisi");
                        }
                        
                        if (!preg_match('/^\d{8,}$/', $nim)) {
                            throw new Exception("Format NIM tidak valid: {$nim}");
                        }
                        
                        // Execute insert
                        $stmt->execute([
                            $nim, $nisn, $nama, $nik, $tempat_lahir, $tanggal_lahir,
                            $jenis_kelamin, $no_handphone, $email, $agama, $desa, $nama_ibu,
                            $kode_prodi, $tanggal_masuk, $semester_masuk, $jenis_pendaftaran,
                            $jalur_pendaftaran, $biaya_awal_masuk, $jenis_pembiayaan
                        ]);
                        
                        $success_count++;
                        
                        // Update progress setiap 50 record
                        if ($success_count % 50 == 0) {
                            $progress = min(($line_number / 2200) * 100, 100);
                            echo "<script>
                                document.getElementById('progressBar').style.width = '{$progress}%';
                                document.getElementById('progressBar').textContent = '{$progress}%';
                                document.getElementById('progressText').textContent = 'Berhasil: {$success_count} data...';
                            </script>";
                            flush();
                        }
                        
                    } catch (Exception $e) {
                        $error_count++;
                        if (count($errors) < 20) {
                            $errors[] = "Baris {$line_number}: " . $e->getMessage();
                        }
                        
                        if ($error_count > 100) {
                            echo "<div class='warning'>⚠️ Terlalu banyak error, import dihentikan</div>";
                            break;
                        }
                    }
                }
                
                fclose($file_handle);
                
                echo "<script>
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('progressBar').textContent = '100%';
                    document.getElementById('progressText').textContent = 'Import selesai!';
                </script>";
                
                echo "<div class='success'>";
                echo "<h3>✅ Import CSV Berhasil</h3>";
                echo "<p>📊 <strong>Statistik Import:</strong></p>";
                echo "<p>✅ Berhasil: <strong>{$success_count}</strong> data</p>";
                echo "<p>❌ Error: <strong>{$error_count}</strong> data</p>";
                echo "<p>📁 File: " . $_FILES['csv_file']['name'] . "</p>";
                echo "<p>⏰ Waktu: " . date('d/m/Y H:i:s') . "</p>";
                echo "</div>";
                
                if (!empty($errors)) {
                    echo "<div class='warning'>";
                    echo "<h4>⚠️ Detail Error:</h4>";
                    foreach ($errors as $error) {
                        echo "<p>• {$error}</p>";
                    }
                    if (count($errors) >= 20) {
                        echo "<p>• ... dan error lainnya (cek log untuk detail lengkap)</p>";
                    }
                    echo "</div>";
                }
                
                // Navigation links
                echo "<div style='margin-top: 30px;'>";
                echo "<a href='../admin/' class='btn btn-success'>📊 Dashboard Admin</a>";
                echo "<a href='../' class='btn'>🎓 Aplikasi Mahasiswa</a>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'><h3>❌ Error Import</h3><p>" . $e->getMessage() . "</p></div>";
            }
        }
        
        // Utility functions
        function cleanCSVData($value) {
            if ($value === null || $value === '') return null;
            $cleaned = trim(strval($value));
            $cleaned = str_replace(["\t", "\r", "\n", "\0"], '', $cleaned);
            
            // Skip instruksi Excel
            if (strpos($cleaned, 'tambahkan tanda pet') !== false || 
                strpos($cleaned, 'WAJIB DIISI') !== false ||
                strpos($cleaned, 'untuk ref') !== false) {
                return null;
            }
            
            return $cleaned === '' ? null : $cleaned;
        }
        
        function formatCSVDate($dateValue) {
            if (empty($dateValue)) return null;
            
            try {
                // Excel date serial number
                if (is_numeric($dateValue) && $dateValue > 30000) {
                    $unix_date = ($dateValue - 25569) * 86400;
                    return date('Y-m-d', $unix_date);
                }
                
                // String date
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
            <p>Hapus data corrupt sebelum import ulang (Recommended)</p>
            <form method="post" style="margin: 10px 0;">
                <button type="submit" name="cleanup_db" class="btn btn-danger">🗑️ Cleanup Database</button>
            </form>
        </div>
        
        <!-- Excel to CSV Conversion Instructions -->
        <div class="info">
            <h3>📋 Langkah 2: Konversi Excel ke CSV</h3>
            <p><strong>Ikuti langkah berikut untuk mengkonversi file Excel Anda:</strong></p>
            <ol>
                <li><strong>Buka file Excel</strong> <code>template_mahasiswa_PDDIKTI.xls.xls</code></li>
                <li><strong>Periksa data:</strong>
                    <ul>
                        <li>Pastikan baris 1 adalah header</li>
                        <li>Data dimulai dari baris 2</li>
                        <li>Tidak ada baris kosong di tengah data</li>
                        <li>Hapus instruksi/keterangan yang tidak perlu</li>
                    </ul>
                </li>
                <li><strong>Save As:</strong> File → Save As → CSV (Comma delimited) (*.csv)</li>
                <li><strong>Nama file:</strong> <code>mahasiswa_data.csv</code></li>
                <li><strong>Upload file CSV</strong> menggunakan form di bawah</li>
            </ol>
        </div>
        
        <!-- CSV Upload Section -->
        <div class="upload-area">
            <h3>📤 Langkah 3: Upload File CSV</h3>
            <form method="post" enctype="multipart/form-data">
                <p><strong>Pilih file CSV yang sudah dikonversi:</strong></p>
                <input type="file" name="csv_file" accept=".csv" required style="margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 100%; max-width: 400px;">
                <br>
                <button type="submit" class="btn btn-success">🚀 Import CSV ke Database</button>
            </form>
        </div>
        
        <!-- Additional Tools -->
        <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h3>🔧 Tools Tambahan</h3>
            <a href="../admin/" class="btn">📊 Dashboard Admin</a>
            <a href="clean_database.php?password=admin123" class="btn">🧹 Advanced Cleanup</a>
            <a href="../" class="btn">🎓 Aplikasi Mahasiswa</a>
        </div>
        
        <!-- Troubleshooting -->
        <div class="warning" style="margin-top: 30px;">
            <h3>🆘 Troubleshooting</h3>
            <p><strong>Jika masih ada masalah:</strong></p>
            <ul>
                <li><strong>File Excel corrupt:</strong> Coba buka di Microsoft Excel dan save as .xlsx terlebih dahulu</li>
                <li><strong>Format tanggal salah:</strong> Ubah format tanggal ke YYYY-MM-DD di Excel</li>
                <li><strong>NIM tidak valid:</strong> Pastikan NIM berupa angka minimal 8 digit</li>
                <li><strong>Data bercampur:</strong> Hapus baris instruksi/header yang tidak perlu</li>
            </ul>
        </div>
    </div>
</body>
</html>