<?php
// config.php - Updated dengan konversi nilai baru dan fungsi dokumen multiple
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'stkp7133_stkyakobus_rpl');
define('DB_PASS', 'stkmerauke01');
define('DB_NAME', 'stkp7133_stkyakobus_rpl_system');

// Membuat koneksi database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Konfigurasi aplikasi
define('APP_NAME', 'Sistem Penilaian RPL - STKYAKOBUS');
define('BASE_URL', 'https://stkyakobus.ac.id/rpl/');

// Fungsi untuk mengecek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Fungsi untuk mengecek role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isDosen() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'dosen';
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

// Fungsi untuk konversi skor ke huruf mutu - UPDATED sesuai konfirmasi
function skorKeHurufMutu($skor) {
    if ($skor >= 80) return 'A';     // 80-100 = A
    if ($skor >= 70) return 'B';     // 70-79.9 = B  
    if ($skor >= 60) return 'C';     // 60-69.9 = C
    if ($skor >= 50) return 'D';     // 50-59.9 = D
    return 'E';                      // 0-49.9 = E
}

// Fungsi untuk mendapatkan keterangan nilai
function getKeteranganNilai($skor) {
    if ($skor >= 80) return 'Sangat Baik';
    if ($skor >= 70) return 'Baik';
    if ($skor >= 60) return 'Cukup';
    if ($skor >= 50) return 'Kurang';
    return 'Sangat Kurang';
}

// Fungsi untuk konversi ke nilai numerik (GPA)
function skorKeNumerik($skor) {
    if ($skor >= 80) return 4.0;    // A
    if ($skor >= 70) return 3.0;    // B
    if ($skor >= 60) return 2.0;    // C
    if ($skor >= 50) return 1.0;    // D
    return 0.0;                     // E
}

// Fungsi untuk log aktivitas
function logAktivitas($pdo, $user_id, $aktivitas, $detail = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aktivitas, detail, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $aktivitas, $detail, $ip]);
}

// Fungsi untuk format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

// Fungsi untuk sanitasi input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

// Fungsi untuk mendapatkan daftar semester (untuk RPL.02 dan RPL.03)
function getSemesterList() {
    return [
        'ganjil_2019' => '2019/2020 Ganjil',
        'genap_2019' => '2019/2020 Genap',
        'ganjil_2020' => '2020/2021 Ganjil',
        'genap_2020' => '2020/2021 Genap',
        'ganjil_2021' => '2021/2022 Ganjil',
        'genap_2021' => '2021/2022 Genap',
        'ganjil_2022' => '2022/2023 Ganjil',
        'genap_2022' => '2022/2023 Genap',
        'ganjil_2023' => '2023/2024 Ganjil',
        'genap_2023' => '2023/2024 Genap',
        'ganjil_2024' => '2024/2025 Ganjil',
        'genap_2024' => '2024/2025 Genap'
    ];
}

// Fungsi untuk mendapatkan dokumen RPL.02 (Perangkat Pembelajaran)
function getRPL02Documents($mahasiswa_data) {
    $semesters = getSemesterList();
    $documents = [];
    
    foreach ($semesters as $key => $label) {
        $field_name = 'rpl02_perangkat_' . $key;
        $documents[$key] = [
            'label' => $label,
            'link' => $mahasiswa_data[$field_name] ?? null
        ];
    }
    
    return $documents;
}

// Fungsi untuk mendapatkan dokumen RPL.03 (Pengembangan Profesional)
function getRPL03Documents($mahasiswa_data) {
    $semesters = getSemesterList();
    $documents = [];
    
    foreach ($semesters as $key => $label) {
        $field_name = 'rpl03_pengembangan_' . $key;
        $documents[$key] = [
            'label' => $label,
            'link' => $mahasiswa_data[$field_name] ?? null
        ];
    }
    
    return $documents;
}

// Fungsi untuk menghitung berapa dokumen yang tersedia
function countAvailableDocuments($documents) {
    return array_reduce($documents, function($count, $doc) {
        return $count + (!empty($doc['link']) ? 1 : 0);
    }, 0);
}

// Fungsi untuk validasi link Google Drive
function isValidGoogleDriveLink($link) {
    if (empty($link)) return false;
    
    $patterns = [
        '/^https:\/\/drive\.google\.com\//',
        '/^https:\/\/docs\.google\.com\//',
        '/\/file\/d\/([a-zA-Z0-9-_]+)/',
        '/\/open\?id=([a-zA-Z0-9-_]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $link)) {
            return true;
        }
    }
    
    return false;
}

// Fungsi untuk format link Google Drive untuk viewing
function formatGoogleDriveViewLink($link) {
    if (empty($link)) return '';
    
    // Extract file ID from various Google Drive URL formats
    if (preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $link, $matches)) {
        $fileId = $matches[1];
        return "https://drive.google.com/file/d/$fileId/view";
    }
    
    if (preg_match('/\/open\?id=([a-zA-Z0-9-_]+)/', $link, $matches)) {
        $fileId = $matches[1];
        return "https://drive.google.com/file/d/$fileId/view";
    }
    
    return $link; // Return original if can't parse
}

// Fungsi untuk mendapatkan rubrik penilaian lengkap
function getRubrikPenilaian() {
    return [
        'rpl01_pedagogik' => [
            'nama' => 'RPL.01 - Pengembangan Kompetensi Pedagogik',
            'sks' => 6,
            'deskripsi' => 'Penilaian berdasarkan dokumen SK/Surat Tugas mengajar',
            'dokumen_type' => 'single',
            'field_name' => 'link_sk_mengajar'  // FIXED: gunakan nama kolom yang benar
        ],
        'rpl02_perangkat' => [
            'nama' => 'RPL.02 - Penyusunan Perangkat Pembelajaran', 
            'sks' => 6,
            'deskripsi' => 'Penilaian berdasarkan dokumen perangkat pembelajaran per semester (12 semester)',
            'dokumen_type' => 'multiple',
            'semester_count' => 12
        ],
        'rpl03_profesional' => [
            'nama' => 'RPL.03 - Pengembangan Kompetensi Profesional',
            'sks' => 6, 
            'deskripsi' => 'Penilaian berdasarkan sertifikat kegiatan ilmiah/KKG/MGMP per semester (12 semester)',
            'dokumen_type' => 'multiple',
            'semester_count' => 12
        ],
        'rpl04_administrasi' => [
            'nama' => 'RPL.04 - Pengelolaan Administrasi Pembelajaran',
            'sks' => 6,
            'deskripsi' => 'Penilaian berdasarkan surat keterangan administrasi pembelajaran',
            'dokumen_type' => 'single',
            'field_name' => 'link_administrasi'  // FIXED: gunakan nama kolom yang benar
        ],
        'rpl05_inovasi' => [
            'nama' => 'RPL.05 - Inovasi Pembelajaran',
            'sks' => 3,
            'deskripsi' => 'Penilaian berdasarkan surat keterangan inovasi pembelajaran',
            'dokumen_type' => 'single',
            'field_name' => 'link_inovasi'  // FIXED: gunakan nama kolom yang benar
        ]
    ];
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>