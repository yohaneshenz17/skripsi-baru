<?php
// config.php
// Konfigurasi database dan fungsi utama untuk verifikasi mahasiswa

// Informasi database cPanel
define('DB_HOST', 'localhost');
define('DB_NAME', 'stkp7133_verifikasi_mahasiswa'); // Format: cpanelusername_databasename
define('DB_USER', 'stkp7133_verif_user');           // Format: cpanelusername_username
define('DB_PASS', 'stkmerauke01');                   // Password yang Anda buat

// Konfigurasi aplikasi
define('APP_NAME', 'Verifikasi Data Mahasiswa PPG');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Jayapura');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Fungsi koneksi database
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log error (jangan tampilkan detail error ke user)
        error_log("Database connection failed: " . $e->getMessage());
        
        // Return error response
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Koneksi database gagal. Silakan hubungi administrator.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Header CORS untuk mengizinkan akses dari frontend
function setCORSHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    
    // Handle preflight request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Fungsi untuk response JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Fungsi untuk sanitize output
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    if (is_string($data)) {
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $data;
}

// Fungsi untuk logging aktivitas
function logActivity($nim, $action, $oldData = null, $newData = null) {
    try {
        $pdo = getDBConnection();
        
        // Pastikan tabel activity_log ada
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nim VARCHAR(20) NOT NULL,
                action VARCHAR(50) NOT NULL,
                old_data TEXT NULL,
                new_data TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_nim (nim),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $stmt->execute();
        
        // Insert log
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (nim, action, old_data, new_data, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $nim,
            $action,
            $oldData ? (is_string($oldData) ? $oldData : json_encode($oldData, JSON_UNESCAPED_UNICODE)) : null,
            $newData ? (is_string($newData) ? $newData : json_encode($newData, JSON_UNESCAPED_UNICODE)) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Log activity failed: " . $e->getMessage());
        // Jangan stop eksekusi jika logging gagal
    }
}

// Fungsi untuk validasi input
function validateInput($data, $required_fields = []) {
    $errors = [];
    
    // Validasi field wajib
    foreach ($required_fields as $field) {
        if (empty($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "Field {$field} wajib diisi";
        }
    }
    
    // Validasi NIK (16 digit)
    if (!empty($data['nik'])) {
        $nik = trim($data['nik']);
        if (!preg_match('/^\d{16}$/', $nik)) {
            $errors[] = "NIK harus berupa 16 digit angka";
        }
    }
    
    // Validasi email
    if (!empty($data['email'])) {
        $email = trim($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        }
    }
    
    // Validasi nomor handphone
    if (!empty($data['no_handphone'])) {
        $phone = trim($data['no_handphone']);
        if (!preg_match('/^[0-9+\-\s()]{8,15}$/', $phone)) {
            $errors[] = "Format nomor handphone tidak valid (8-15 karakter, hanya angka, +, -, spasi, kurung)";
        }
    }
    
    // Validasi nama (tidak boleh kosong dan minimal 2 karakter)
    if (!empty($data['nama'])) {
        $nama = trim($data['nama']);
        if (strlen($nama) < 2) {
            $errors[] = "Nama minimal 2 karakter";
        }
        if (!preg_match('/^[a-zA-Z\s\.\,\-\']+$/u', $nama)) {
            $errors[] = "Nama hanya boleh mengandung huruf, spasi, titik, koma, tanda strip, dan apostrof";
        }
    }
    
    // Validasi tanggal lahir
    if (!empty($data['tanggal_lahir'])) {
        $date = DateTime::createFromFormat('Y-m-d', $data['tanggal_lahir']);
        if (!$date || $date->format('Y-m-d') !== $data['tanggal_lahir']) {
            $errors[] = "Format tanggal lahir tidak valid (YYYY-MM-DD)";
        } else {
            // Cek umur minimal 17 tahun dan maksimal 100 tahun
            $now = new DateTime();
            $age = $now->diff($date)->y;
            if ($age < 17 || $age > 100) {
                $errors[] = "Umur harus antara 17-100 tahun";
            }
        }
    }
    
    // Validasi jenis kelamin
    if (!empty($data['jenis_kelamin'])) {
        $valid_gender = ['Laki-laki', 'Perempuan'];
        if (!in_array($data['jenis_kelamin'], $valid_gender)) {
            $errors[] = "Jenis kelamin harus 'Laki-laki' atau 'Perempuan'";
        }
    }
    
    // Validasi provinsi - UPDATE: Fleksibel untuk seluruh Indonesia
    if (!empty($data['provinsi'])) {
        $provinsi = trim($data['provinsi']);
        
        // Validasi minimal panjang nama provinsi
        if (strlen($provinsi) < 3) {
            $errors[] = "Nama provinsi minimal 3 karakter";
        }
        
        // Validasi format nama provinsi (hanya huruf, spasi, dan beberapa karakter khusus)
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $provinsi)) {
            $errors[] = "Nama provinsi hanya boleh berisi huruf, spasi, dan tanda hubung";
        }
        
        // Optional: Validasi strict terhadap daftar provinsi yang valid
        // Uncomment jika ingin validasi ketat:
        /*
        $valid_provinces = [
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi', 'Sumatera Selatan',
            'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung', 'Kepulauan Riau',
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Banten',
            'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat',
            'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara',
            'Gorontalo', 'Sulawesi Barat', 'Maluku', 'Maluku Utara', 'Papua Barat',
            'Papua', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'
        ];
        
        if (!in_array($provinsi, $valid_provinces)) {
            $errors[] = "Provinsi '$provinsi' tidak valid";
        }
        */
    }
    
    // Validasi kabupaten/kota - UPDATE: Lebih fleksibel
    if (!empty($data['kabupaten_kota'])) {
        $kabupaten = trim($data['kabupaten_kota']);
        
        if (strlen($kabupaten) < 3) {
            $errors[] = "Kabupaten/Kota minimal 3 karakter";
        }
        
        // Validasi format (boleh ada kata "Kabupaten", "Kota", "Kab.", dll)
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $kabupaten)) {
            $errors[] = "Nama kabupaten/kota hanya boleh berisi huruf, spasi, dan tanda hubung";
        }
    }
    
    // Validasi kecamatan - UPDATE: Lebih fleksibel
    if (!empty($data['kecamatan'])) {
        $kecamatan = trim($data['kecamatan']);
        
        if (strlen($kecamatan) < 2) {
            $errors[] = "Kecamatan minimal 2 karakter";
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $kecamatan)) {
            $errors[] = "Nama kecamatan hanya boleh berisi huruf, spasi, dan tanda hubung";
        }
    }
    
    // Validasi desa/kelurahan - UPDATE: Lebih fleksibel
    if (!empty($data['desa'])) {
        $desa = trim($data['desa']);
        
        if (strlen($desa) < 2) {
            $errors[] = "Desa/Kelurahan minimal 2 karakter";
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $desa)) {
            $errors[] = "Nama desa/kelurahan hanya boleh berisi huruf, spasi, dan tanda hubung";
        }
    }
    
    return $errors;
}

// Fungsi untuk membersihkan string
function cleanString($str) {
    if (empty($str)) return '';
    
    // Trim whitespace
    $str = trim($str);
    
    // Remove multiple spaces
    $str = preg_replace('/\s+/', ' ', $str);
    
    // Remove special characters yang tidak diinginkan (kecuali untuk field tertentu)
    $str = preg_replace('/[^\p{L}\p{N}\s\.\,\-\'\(\)\/\@\+]/u', '', $str);
    
    return $str;
}

// Fungsi untuk format nama dengan benar
function formatName($name) {
    if (empty($name)) return '';
    
    $name = cleanString($name);
    
    // Capitalize each word
    $name = ucwords(strtolower($name));
    
    // Handle special cases
    $name = str_replace([' Bin ', ' Binti ', ' Al ', ' El '], [' bin ', ' binti ', ' al ', ' el '], $name);
    
    return $name;
}

// Fungsi untuk cek apakah database table sudah ada
function ensureDatabaseTables() {
    try {
        $pdo = getDBConnection();
        
        // Create mahasiswa table if not exists
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS mahasiswa (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nim VARCHAR(20) UNIQUE NOT NULL,
                nisn VARCHAR(20) NULL,
                nama VARCHAR(100) NOT NULL,
                nik VARCHAR(16) NULL,
                tempat_lahir VARCHAR(50) NULL,
                tanggal_lahir DATE NULL,
                jenis_kelamin ENUM('Laki-laki', 'Perempuan') NULL,
                no_handphone VARCHAR(20) NULL,
                email VARCHAR(100) NULL,
                agama VARCHAR(20) NULL,
                provinsi VARCHAR(50) NULL,
                kabupaten_kota VARCHAR(50) NULL,
                kecamatan VARCHAR(50) NULL,
                desa VARCHAR(50) NULL,
                nama_ibu VARCHAR(100) NULL,
                kewarganegaraan VARCHAR(30) DEFAULT 'Indonesia',
                npwp VARCHAR(20) NULL,
                alamat_jalan TEXT NULL,
                kode_prodi VARCHAR(10) NULL,
                tanggal_masuk DATE NULL,
                semester_masuk VARCHAR(10) NULL,
                jenis_pendaftaran VARCHAR(50) NULL,
                jalur_pendaftaran VARCHAR(50) NULL,
                biaya_awal_masuk DECIMAL(15,2) NULL,
                jenis_pembiayaan VARCHAR(50) NULL,
                confirmed BOOLEAN DEFAULT FALSE,
                confirmed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_nim (nim),
                INDEX idx_nisn (nisn),
                INDEX idx_nama (nama),
                INDEX idx_confirmed (confirmed),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $stmt->execute();
        
        // Create activity_log table if not exists
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nim VARCHAR(20) NOT NULL,
                action VARCHAR(50) NOT NULL,
                old_data TEXT NULL,
                new_data TEXT NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_nim (nim),
                INDEX idx_action (action),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $stmt->execute();
        
        return true;
    } catch (Exception $e) {
        error_log("Database table creation failed: " . $e->getMessage());
        return false;
    }
}

// Auto-create tables on first run
ensureDatabaseTables();

// Fungsi untuk debugging (hanya untuk development)
function debugLog($message, $data = null) {
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        $log = date('Y-m-d H:i:s') . " - " . $message;
        if ($data !== null) {
            $log .= " - " . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        error_log($log);
    }
}

// Set error reporting untuk production
if (!defined('DEBUG_MODE')) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>