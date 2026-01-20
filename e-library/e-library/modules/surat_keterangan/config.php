<?php
/**
 * Konfigurasi Modul Surat Keterangan
 * VERSION: 1.2 - FIX SESSION ERROR
 */

// Koneksi database
require_once '../../config/database.php';

// Konstanta
define('SK_MODULE_PATH', dirname(__FILE__));
define('SK_PDF_PATH', SK_MODULE_PATH . '/pdf_generated/');
define('SK_ASSETS_PATH', SK_MODULE_PATH . '/../../assets/images/');
define('SK_LIB_PATH', SK_MODULE_PATH . '/lib/');

define('SK_KEPALA_PERPUS', 'Yuliana Mangera, S.S.I');
define('SK_JABATAN_PERPUS', 'Kepala Perpustakaan');
define('SK_INSTITUSI', 'SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE');
define('SK_KEMENTERIAN', 'KEMENTERIAN AGAMA REPUBLIK INDONESIA');
define('SK_YAYASAN', 'YAYASAN PENDIDIKAN DAN PERSEKOLAHAN KATOLIK');
define('SK_ALAMAT', 'Jalan Missi II Merauke Papua Selatan 99616');
define('SK_KONTAK', 'Telepon / Faks. (0971) 3330264, Email: humas@stkyakobus.ac.id, Website: www.stkyakobus.ac.id');

define('SK_FORMAT_NOMOR', '{nomor}/SKP-PERP/STK/{bulan}/{tahun}');
define('SK_JENIS_UAS', 'UAS');
define('SK_JENIS_PPA', 'PPA');

$BULAN_ROMAWI = [
    1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 
    5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
    9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
];

$BULAN_INDONESIA = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

function generateNomorSurat($jenis_surat, $tahun = null) {
    global $conn, $BULAN_ROMAWI;
    
    if (!$tahun) {
        $tahun = date('Y');
    }
    
    $bulan = date('n');
    $bulan_romawi = $BULAN_ROMAWI[$bulan];
    
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_surat, '/', 1) AS UNSIGNED)) as last_number 
            FROM surat_keterangan 
            WHERE tahun_periode = ? AND status = 'terbit'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tahun);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $next_number = ($row['last_number'] ?? 0) + 1;
    $nomor_urut = str_pad($next_number, 3, '0', STR_PAD_LEFT);
    
    $nomor_surat = "{$nomor_urut}/SKP-PERP/STK/{$bulan_romawi}/{$tahun}";
    
    return $nomor_surat;
}

function cekPeminjamanAktif($nim) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total 
            FROM peminjaman p
            INNER JOIN mahasiswa m ON p.peminjam_id = m.id
            WHERE m.nim = ? 
            AND p.jenis_peminjam = 'mahasiswa' 
            AND p.status IN ('dipinjam', 'diperpanjang')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] > 0;
}

function cekTunggakan($nim) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total, SUM(pg.sisa_denda) as total_denda
            FROM pengembalian pg
            INNER JOIN peminjaman pm ON pg.peminjaman_id = pm.id
            INNER JOIN mahasiswa m ON pm.peminjam_id = m.id
            WHERE m.nim = ? 
            AND pm.jenis_peminjam = 'mahasiswa'
            AND pg.sisa_denda > 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return [
        'ada_tunggakan' => $row['total'] > 0,
        'jumlah' => $row['total'] ?? 0,
        'total_denda' => $row['total_denda'] ?? 0
    ];
}

function getDataMahasiswa($nim) {
    global $conn;
    
    $sql = "SELECT 
                nim, 
                nama as nama_mahasiswa, 
                program_studi as nama_prodi, 
                angkatan,
                id,
                no_hp,
                foto
            FROM mahasiswa
            WHERE nim = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function formatTanggalIndonesia($tanggal) {
    global $BULAN_INDONESIA;
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = $BULAN_INDONESIA[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    return "{$hari} {$bulan} {$tahun}";
}

function validasiSuratByQR($qr_data) {
    global $conn;
    
    $parts = explode('|', $qr_data);
    
    if (count($parts) != 4) {
        return ['valid' => false, 'message' => 'Format QR Code tidak valid'];
    }
    
    list($nim, $nama, $nomor_surat, $tanggal) = $parts;
    
    $sql = "SELECT 
                sk.*, 
                m.nama as nama_mahasiswa, 
                m.program_studi as nama_prodi,
                m.angkatan
            FROM surat_keterangan sk
            INNER JOIN mahasiswa m ON sk.nim = m.nim
            WHERE sk.nomor_surat = ? 
            AND sk.nim = ? 
            AND sk.status = 'terbit'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nomor_surat, $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['valid' => true, 'data' => $result->fetch_assoc()];
    } else {
        return ['valid' => false, 'message' => 'Surat tidak ditemukan atau sudah dibatalkan'];
    }
}

function generateQRData($nim, $nama, $nomor_surat, $tanggal_terbit) {
    $tanggal_format = formatTanggalIndonesia($tanggal_terbit);
    return "{$nim}|{$nama}|{$nomor_surat}|{$tanggal_format}";
}

function logAktivitas($aktivitas, $detail, $admin_id = null) {
    global $conn;
    
    if (!$admin_id && isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
    }
    
    $check = $conn->query("SHOW TABLES LIKE 'log_aktivitas'");
    if ($check->num_rows == 0) {
        return;
    }
    
    $sql = "INSERT INTO log_aktivitas (admin_id, aktivitas, detail, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $admin_id, $aktivitas, $detail);
    $stmt->execute();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * CEK LOGIN ADMIN - FLEKSIBEL
 * Mengecek berbagai kemungkinan session variable
 */
function cekLoginAdmin() {
    // Jika sudah ada session admin_id, berarti sudah login
    if (isset($_SESSION['admin_id'])) {
        return; // OK, sudah login
    }
    
    // Jika tidak ada admin_id, redirect ke login
    header('Location: /e-library/login.php');
    exit();
}
?>
