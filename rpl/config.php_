<?php
// config.php - Konfigurasi Database dan Session
session_start();

// Konfigurasi Database
define('DB_HOST', 'localhost'); // atau sesuai dengan setting cPanel
define('DB_USER', 'stkyakobus_rpl'); // username database
define('DB_PASS', 'stkmerauke01'); // password database
define('DB_NAME', 'stkyakobus_rpl_system'); // nama database

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
define('BASE_URL', 'https://stkyakobus.ac.id/rpl/'); // sesuaikan dengan URL aplikasi

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

// Fungsi untuk konversi skor ke huruf mutu berdasarkan rubrik
function skorKeHurufMutu($skor) {
    if ($skor >= 85) return 'A';
    if ($skor >= 80) return 'B';
    if ($skor >= 75) return 'C';
    if ($skor >= 70) return 'D';
    if ($skor >= 60) return 'E';
    return 'F';
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

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>