<?php
// config.php
// Konfigurasi database untuk verifikasi mahasiswa

// Informasi database cPanel
define('DB_HOST', 'localhost');
define('DB_NAME', 'stkp7133_verifikasi_mahasiswa'); // Format: cpanelusername_databasename
define('DB_USER', 'stkp7133_verif_user');           // Format: cpanelusername_username
define('DB_PASS', 'stkmerauke01');          // Password yang Anda buat tadi

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
        ]);
        exit;
    }
}

// Fungsi untuk logging aktivitas
function logActivity($nim, $action, $oldData = null, $newData = null) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (nim, action, old_data, new_data, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $nim,
            $action,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Log activity failed: " . $e->getMessage());
    }
}

// Fungsi untuk validasi input
function validateInput($data, $required_fields = []) {
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field {$field} wajib diisi";
        }
    }
    
    // Validasi NIK (16 digit)
    if (!empty($data['nik']) && !preg_match('/^\d{16}$/', $data['nik'])) {
        $errors[] = "NIK harus 16 digit angka";
    }
    
    // Validasi email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Validasi nomor handphone
    if (!empty($data['no_handphone']) && !preg_match('/^[0-9+\-\s]{10,15}$/', $data['no_handphone'])) {
        $errors[] = "Format nomor handphone tidak valid";
    }
    
    return $errors;
}

// Header CORS untuk mengizinkan akses dari frontend
function setCORSHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fungsi untuk sanitize output
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}
?>