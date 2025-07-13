<?php
/**
 * Script untuk reset password mahasiswa
 * Lokasi: /public_html/skripsi/reset_password.php
 * 
 * PERHATIAN: Hapus file ini setelah selesai digunakan untuk keamanan!
 */

// Konfigurasi database
$db_host = 'localhost';
$db_user = 'stkp7133_skripsi';
$db_pass = 'stkmerauke01';
$db_name = 'stkp7133_skripsi';

// Koneksi database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi database gagal: " . $mysqli->connect_error);
}

// Set charset
$mysqli->set_charset("utf8");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .success { color: green; }
        .error { color: red; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin: 5px;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .form-group {
            margin: 10px 0;
        }
        input[type="text"], input[type="password"] {
            padding: 8px;
            width: 300px;
        }
    </style>
</head>
<body>
    <h1>Reset Password Mahasiswa</h1>
    
    <?php
    // Proses reset password
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'reset_single' && isset($_POST['nim']) && isset($_POST['new_password'])) {
            $nim = $mysqli->real_escape_string($_POST['nim']);
            $new_password = $_POST['new_password'];
            
            // Hash password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update password
            $sql = "UPDATE mahasiswa SET password = ? WHERE nim = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $nim);
            
            if ($stmt->execute()) {
                echo "<p class='success'>✓ Password untuk NIM $nim berhasil direset!</p>";
            } else {
                echo "<p class='error'>✗ Error: " . $mysqli->error . "</p>";
            }
            $stmt->close();
        }
        
        elseif ($_POST['action'] == 'reset_all_to_nim') {
            $sql = "SELECT id, nim FROM mahasiswa";
            $result = $mysqli->query($sql);
            
            $success = 0;
            $failed = 0;
            
            while ($row = $result->fetch_assoc()) {
                $hashed_password = password_hash($row['nim'], PASSWORD_BCRYPT);
                $update_sql = "UPDATE mahasiswa SET password = ? WHERE id = ?";
                $stmt = $mysqli->prepare($update_sql);
                $stmt->bind_param("si", $hashed_password, $row['id']);
                
                if ($stmt->execute()) {
                    $success++;
                } else {
                    $failed++;
                }
                $stmt->close();
            }
            
            echo "<p class='success'>✓ Reset selesai! Berhasil: $success, Gagal: $failed</p>";
            echo "<p>Password semua mahasiswa telah direset menjadi NIM masing-masing.</p>";
        }
    }
    ?>
    
    <h2>1. Reset Password Mahasiswa Tertentu</h2>
    <form method="post">
        <input type="hidden" name="action" value="reset_single">
        <div class="form-group">
            <label>NIM Mahasiswa:</label><br>
            <input type="text" name="nim" required placeholder="Contoh: 2202007">
        </div>
        <div class="form-group">
            <label>Password Baru:</label><br>
            <input type="password" name="new_password" required placeholder="Masukkan password baru">
        </div>
        <button type="submit" class="btn">Reset Password</button>
    </form>
    
    <hr>
    
    <h2>2. Reset Semua Password Mahasiswa (Password = NIM)</h2>
    <form method="post" onsubmit="return confirm('Anda yakin ingin reset password SEMUA mahasiswa?')">
        <input type="hidden" name="action" value="reset_all_to_nim">
        <p>Ini akan mereset password semua mahasiswa menjadi NIM masing-masing.</p>
        <button type="submit" class="btn btn-danger">Reset Semua Password</button>
    </form>
    
    <hr>
    
    <h2>3. Daftar Mahasiswa</h2>
    <?php
    // Tampilkan daftar mahasiswa
    $sql = "SELECT m.nim, m.nama, m.email, p.nama as prodi, m.status 
            FROM mahasiswa m 
            JOIN prodi p ON m.prodi_id = p.id 
            ORDER BY m.nim";
    $result = $mysqli->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Prodi</th>
                <th>Status</th>
              </tr>";
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'] == '1' ? '<span style="color:green">Aktif</span>' : '<span style="color:red">Non-Aktif</span>';
            echo "<tr>
                    <td>{$row['nim']}</td>
                    <td>{$row['nama']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['prodi']}</td>
                    <td>$status</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada data mahasiswa.</p>";
    }
    ?>
    
    <hr>
    <p style="color: red; font-weight: bold;">
        ⚠️ PENTING: Hapus file ini setelah selesai digunakan untuk alasan keamanan!
    </p>
    
    <h3>Informasi Password Default:</h3>
    <ul>
        <li>Password yang telah di-hash (untuk testing):</li>
        <li><code>123456</code> = <code>$2y$10$4Zk9VYMsKKBieyQXJQnS1ORn8YBhfmWrL8C83fGdFhvMAJgDnGDmW</code></li>
        <li><code>password</code> = <code>$2y$10$L5aa2RGrSevnjUJBTIrDLuSLgeB0r0Qb12S287NBTiD4HH4FKHdeK</code></li>
    </ul>
</body>
</html>

<?php
// Tutup koneksi
$mysqli->close();
?>