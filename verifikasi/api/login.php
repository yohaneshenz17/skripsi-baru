<?php
// login.php
// API untuk login mahasiswa menggunakan NIM/NISN

require_once 'config.php';

// Set CORS headers
setCORSHeaders();

// Hanya izinkan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ], 405);
}

try {
    // Ambil input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse([
            'success' => false,
            'message' => 'Format JSON tidak valid'
        ], 400);
    }
    
    // Validasi input
    if (empty($input['account'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Nomor akun (NIM/NISN) wajib diisi'
        ], 400);
    }
    
    $account = trim($input['account']);
    
    // Validasi format nomor akun (minimal 8 digit)
    if (!preg_match('/^\d{8,}$/', $account)) {
        jsonResponse([
            'success' => false,
            'message' => 'Format nomor akun tidak valid. Harus berupa angka minimal 8 digit.'
        ], 400);
    }
    
    // Koneksi database
    $pdo = getDBConnection();
    
    // Cari mahasiswa berdasarkan NIM atau NISN - UPDATED query dengan kolom baru
    $stmt = $pdo->prepare("
        SELECT 
            id, nim, nisn, nama, nik, tempat_lahir, tanggal_lahir, 
            jenis_kelamin, no_handphone, email, agama, provinsi,
            kabupaten_kota, kecamatan, desa, nama_ibu, kewarganegaraan, 
            npwp, alamat_jalan, confirmed, confirmed_at, created_at, updated_at
        FROM mahasiswa 
        WHERE nim = ? OR nisn = ?
        LIMIT 1
    ");
    
    $stmt->execute([$account, $account]);
    $student = $stmt->fetch();
    
    if (!$student) {
        // Log aktivitas login gagal
        logActivity($account, 'LOGIN_FAILED');
        
        jsonResponse([
            'success' => false,
            'message' => 'Nomor akun tidak ditemukan. Pastikan Anda memasukkan NIM atau NISN yang benar.'
        ], 404);
    }
    
    // Format tanggal untuk frontend
    if ($student['tanggal_lahir']) {
        $student['tanggal_lahir'] = date('Y-m-d', strtotime($student['tanggal_lahir']));
    }
    
    // Sanitize output
    $student = sanitizeOutput($student);
    
    // Log aktivitas login berhasil
    logActivity($student['nim'], 'LOGIN_SUCCESS');
    
    // Response sukses
    jsonResponse([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => $student
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
    ], 500);
    
} catch (Exception $e) {
    error_log("General login error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan yang tidak terduga.'
    ], 500);
}
?>