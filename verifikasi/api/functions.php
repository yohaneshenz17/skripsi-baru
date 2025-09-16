<?php
// functions.php
// Fungsi-fungsi tambahan untuk aplikasi verifikasi mahasiswa
// File ini opsional karena fungsi utama sudah ada di config.php

// Fungsi untuk format response API yang konsisten
function apiResponse($success, $message, $data = null, $statusCode = 200) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c'),
        'version' => APP_VERSION
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    jsonResponse($response, $statusCode);
}

// Fungsi untuk validasi NIM format STK Yakobus
function validateNIM($nim) {
    $nim = trim($nim);
    
    // NIM STK Yakobus biasanya format: YYYYMMDDXXX
    // Atau format lain sesuai kebijakan kampus
    if (!preg_match('/^\d{8,15}$/', $nim)) {
        return false;
    }
    
    return true;
}

// Fungsi untuk generate response code unik
function generateResponseCode() {
    return 'STK' . date('Ymd') . substr(md5(uniqid()), 0, 6);
}

// Fungsi untuk cek duplikasi data
function checkDuplicateData($pdo, $field, $value, $excludeNim = null) {
    $sql = "SELECT nim FROM mahasiswa WHERE $field = ?";
    $params = [$value];
    
    if ($excludeNim) {
        $sql .= " AND nim != ?";
        $params[] = $excludeNim;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetch() !== false;
}

// Fungsi untuk format nomor handphone
function formatPhoneNumber($phone) {
    if (empty($phone)) return '';
    
    // Remove semua karakter non-digit
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Convert format 08xx ke +628xx
    if (substr($phone, 0, 2) === '08') {
        $phone = '+62' . substr($phone, 1);
    }
    
    // Add +62 jika dimulai dengan 8
    if (substr($phone, 0, 1) === '8') {
        $phone = '+62' . $phone;
    }
    
    return $phone;
}

// Fungsi untuk validasi email khusus domain kampus
function validateCampusEmail($email) {
    $email = strtolower(trim($email));
    
    // Email kampus harus menggunakan domain stkyakobus.ac.id
    $allowedDomains = ['stkyakobus.ac.id', 'gmail.com', 'yahoo.com', 'outlook.com'];
    
    $domain = substr(strrchr($email, "@"), 1);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Untuk saat ini izinkan semua domain email umum
    // Uncomment baris berikut jika ingin restrict ke domain kampus
    // return in_array($domain, $allowedDomains);
    
    return true;
}

// Fungsi untuk generate password temporary
function generateTempPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $password;
}

// Fungsi untuk backup data sebelum update
function backupStudentData($pdo, $nim) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
        $stmt->execute([$nim]);
        $data = $stmt->fetch();
        
        if ($data) {
            // Create backup table if not exists
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS mahasiswa_backup (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    original_id INT,
                    nim VARCHAR(20),
                    data_backup TEXT,
                    backup_reason VARCHAR(100),
                    backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_nim (nim),
                    INDEX idx_backup_date (backup_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Insert backup
            $stmt = $pdo->prepare("
                INSERT INTO mahasiswa_backup (original_id, nim, data_backup, backup_reason) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['id'],
                $nim,
                json_encode($data, JSON_UNESCAPED_UNICODE),
                'Pre-update backup'
            ]);
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Backup failed: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk send notification (placeholder)
function sendNotification($nim, $type, $message) {
    // Placeholder untuk sistem notifikasi
    // Bisa dikembangkan untuk email, SMS, atau push notification
    
    logActivity($nim, 'NOTIFICATION_SENT', null, [
        'type' => $type,
        'message' => $message,
        'sent_at' => date('c')
    ]);
    
    return true;
}

// Fungsi untuk export data mahasiswa ke format tertentu
function exportStudentData($pdo, $format = 'json', $filters = []) {
    try {
        $sql = "SELECT * FROM mahasiswa WHERE 1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['confirmed'])) {
            $sql .= " AND confirmed = ?";
            $params[] = $filters['confirmed'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        switch ($format) {
            case 'csv':
                return exportToCSV($data);
            case 'excel':
                return exportToExcel($data);
            case 'json':
            default:
                return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
    } catch (Exception $e) {
        error_log("Export failed: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk export ke CSV
function exportToCSV($data) {
    if (empty($data)) return false;
    
    $output = fopen('php://temp', 'r+');
    
    // Write header
    fputcsv($output, array_keys($data[0]));
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

// Fungsi untuk statistik dashboard admin
function getStatistics($pdo) {
    try {
        $stats = [];
        
        // Total mahasiswa
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
        $stats['total_students'] = $stmt->fetch()['total'];
        
        // Mahasiswa yang sudah dikonfirmasi
        $stmt = $pdo->query("SELECT COUNT(*) as confirmed FROM mahasiswa WHERE confirmed = 1");
        $stats['confirmed_students'] = $stmt->fetch()['confirmed'];
        
        // Mahasiswa yang belum dikonfirmasi
        $stats['pending_students'] = $stats['total_students'] - $stats['confirmed_students'];
        
        // Data hari ini
        $stmt = $pdo->query("SELECT COUNT(*) as today FROM mahasiswa WHERE DATE(created_at) = CURDATE()");
        $stats['today_registrations'] = $stmt->fetch()['today'];
        
        // Data minggu ini
        $stmt = $pdo->query("SELECT COUNT(*) as week FROM mahasiswa WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
        $stats['week_registrations'] = $stmt->fetch()['week'];
        
        // Aktivitas login terakhir
        $stmt = $pdo->query("
            SELECT COUNT(*) as logins 
            FROM activity_log 
            WHERE action = 'LOGIN_SUCCESS' 
            AND DATE(created_at) = CURDATE()
        ");
        $stats['today_logins'] = $stmt->fetch()['logins'];
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Statistics error: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk cleanup database (hapus data lama, optimize tables)
function cleanupDatabase($pdo) {
    try {
        // Hapus log aktivitas yang lebih dari 90 hari
        $stmt = $pdo->prepare("DELETE FROM activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $stmt->execute();
        $deleted_logs = $stmt->rowCount();
        
        // Hapus backup data yang lebih dari 30 hari
        $stmt = $pdo->prepare("DELETE FROM mahasiswa_backup WHERE backup_date < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $deleted_backups = $stmt->rowCount();
        
        // Optimize tables
        $pdo->exec("OPTIMIZE TABLE mahasiswa, activity_log");
        
        logActivity('SYSTEM', 'DATABASE_CLEANUP', null, [
            'deleted_logs' => $deleted_logs,
            'deleted_backups' => $deleted_backups,
            'cleanup_date' => date('c')
        ]);
        
        return [
            'deleted_logs' => $deleted_logs,
            'deleted_backups' => $deleted_backups
        ];
        
    } catch (Exception $e) {
        error_log("Cleanup error: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk validate file upload
function validateFileUpload($file, $allowedTypes = ['csv', 'xlsx', 'xls'], $maxSize = 5242880) {
    $errors = [];
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'File tidak valid atau tidak ada file yang diupload';
        return $errors;
    }
    
    // Check file size (default 5MB)
    if ($file['size'] > $maxSize) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal ' . ($maxSize / 1024 / 1024) . 'MB';
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        $errors[] = 'Format file tidak didukung. Hanya diizinkan: ' . implode(', ', $allowedTypes);
    }
    
    // Check MIME type for additional security
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    if (!in_array($mimeType, $allowedMimes)) {
        $errors[] = 'MIME type file tidak valid';
    }
    
    return $errors;
}

// Fungsi untuk rate limiting
function checkRateLimit($nim, $action, $maxAttempts = 5, $timeWindow = 300) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempts 
            FROM activity_log 
            WHERE nim = ? 
            AND action = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$nim, $action, $timeWindow]);
        $attempts = $stmt->fetch()['attempts'];
        
        return $attempts < $maxAttempts;
        
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        return true; // Allow if error
    }
}
?>