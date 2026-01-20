<?php
/**
 * Konfigurasi Modul Surat Keterangan
 * Sekolah Tinggi Katolik Santo Yakobus Merauke
 * VERSION: 1.1 - DISESUAIKAN DENGAN DATABASE REAL
 */

// Koneksi database - sesuaikan dengan config existing Anda
require_once '../../config/database.php'; // Sesuaikan path ini

// Konstanta untuk modul surat keterangan
define('SK_MODULE_PATH', dirname(__FILE__));
define('SK_PDF_PATH', SK_MODULE_PATH . '/pdf_generated/');
define('SK_ASSETS_PATH', SK_MODULE_PATH . '/../../assets/images/'); // Path ke logo dan ttd
define('SK_LIB_PATH', SK_MODULE_PATH . '/lib/');

// Konstanta untuk surat
define('SK_KEPALA_PERPUS', 'Yuliana Mangera, S.S.I');
define('SK_JABATAN_PERPUS', 'Kepala Perpustakaan');
define('SK_INSTITUSI', 'SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE');
define('SK_KEMENTERIAN', 'KEMENTERIAN AGAMA REPUBLIK INDONESIA');
define('SK_YAYASAN', 'YAYASAN PENDIDIKAN DAN PERSEKOLAHAN KATOLIK');
define('SK_ALAMAT', 'Jalan Missi II Merauke Papua Selatan 99616');
define('SK_KONTAK', 'Telepon / Faks. (0971) 3330264, Email: humas@stkyakobus.ac.id, Website: www.stkyakobus.ac.id');

// Format nomor surat
define('SK_FORMAT_NOMOR', '{nomor}/SKP-PERP/STK/{bulan}/{tahun}');

// Jenis surat
define('SK_JENIS_UAS', 'UAS');
define('SK_JENIS_PPA', 'PPA');

// Bulan dalam angka Romawi
$BULAN_ROMAWI = [
    1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 
    5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
    9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
];

// Nama bulan Indonesia
$BULAN_INDONESIA = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

/**
 * Fungsi helper untuk generate nomor surat
 */
function generateNomorSurat($jenis_surat, $tahun = null) {
    global $conn, $BULAN_ROMAWI;
    
    if (!$tahun) {
        $tahun = date('Y');
    }
    
    $bulan = date('n'); // 1-12
    $bulan_romawi = $BULAN_ROMAWI[$bulan];
    
    // Cari nomor urut terakhir di tahun ini
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
    
    // Format: 001/SKP-PERP/STK/I/2026
    $nomor_surat = "{$nomor_urut}/SKP-PERP/STK/{$bulan_romawi}/{$tahun}";
    
    return $nomor_surat;
}

/**
 * Fungsi untuk cek peminjaman aktif mahasiswa
 * DISESUAIKAN: Database menggunakan peminjam_id yang join ke mahasiswa
 */
function cekPeminjamanAktif($nim) {
    global $conn;
    
    // Join ke tabel mahasiswa karena peminjaman pakai peminjam_id
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

/**
 * Fungsi untuk cek tunggakan/denda mahasiswa
 * DISESUAIKAN: Database menggunakan tabel pengembalian dengan kolom sisa_denda
 */
function cekTunggakan($nim) {
    global $conn;
    
    // Cek sisa denda dari tabel pengembalian
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

/**
 * Fungsi untuk get data mahasiswa
 * DISESUAIKAN: Database tidak pakai relasi program_studi, tapi langsung varchar
 */
function getDataMahasiswa($nim) {
    global $conn;
    
    // Kolom: nama (bukan nama_mahasiswa), program_studi (varchar langsung)
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

/**
 * Fungsi untuk format tanggal Indonesia
 */
function formatTanggalIndonesia($tanggal) {
    global $BULAN_INDONESIA;
    
    $timestamp = strtotime($tanggal);
    $hari = date('d', $timestamp);
    $bulan = $BULAN_INDONESIA[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    return "{$hari} {$bulan} {$tahun}";
}

/**
 * Fungsi untuk validasi surat via QR code
 * DISESUAIKAN: Sesuaikan dengan struktur database real
 */
function validasiSuratByQR($qr_data) {
    global $conn;
    
    // QR format: 2202034|MARIUS MARAWA|001/SKP-PERP/STK/I/2026|02 Desember 2025
    $parts = explode('|', $qr_data);
    
    if (count($parts) != 4) {
        return ['valid' => false, 'message' => 'Format QR Code tidak valid'];
    }
    
    list($nim, $nama, $nomor_surat, $tanggal) = $parts;
    
    // Join ke mahasiswa dengan alias yang sesuai
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

/**
 * Fungsi untuk generate QR code data
 */
function generateQRData($nim, $nama, $nomor_surat, $tanggal_terbit) {
    $tanggal_format = formatTanggalIndonesia($tanggal_terbit);
    return "{$nim}|{$nama}|{$nomor_surat}|{$tanggal_format}";
}

/**
 * Fungsi untuk log aktivitas
 * DISESUAIKAN: Cek apakah tabel log_aktivitas ada, jika tidak skip
 */
function logAktivitas($aktivitas, $detail, $admin_id = null) {
    global $conn;
    
    if (!$admin_id && isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
    }
    
    // Cek apakah tabel log_aktivitas ada
    $check = $conn->query("SHOW TABLES LIKE 'log_aktivitas'");
    if ($check->num_rows == 0) {
        // Tabel tidak ada, skip logging
        return;
    }
    
    $sql = "INSERT INTO log_aktivitas (admin_id, aktivitas, detail, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $admin_id, $aktivitas, $detail);
    $stmt->execute();
}

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek login admin (sesuaikan dengan sistem auth Anda)
function cekLoginAdmin() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['nama'])) {
        header('Location: ../../login.php');
        exit();
    }
}
?>