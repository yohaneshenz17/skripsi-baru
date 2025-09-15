<?php
// confirm.php
// API untuk konfirmasi data mahasiswa (mengunci data)

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
    if (empty($input['nim'])) {
        jsonResponse([
            'success' => false,
            'message' => 'NIM wajib diisi'
        ], 400);
    }
    
    $nim = trim($input['nim']);
    
    // Koneksi database
    $pdo = getDBConnection();
    
    // Cek apakah mahasiswa ada
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ? LIMIT 1");
    $stmt->execute([$nim]);
    $student = $stmt->fetch();
    
    if (!$student) {
        jsonResponse([
            'success' => false,
            'message' => 'Data mahasiswa tidak ditemukan'
        ], 404);
    }
    
    // Cek apakah sudah dikonfirmasi
    if ($student['confirmed']) {
        jsonResponse([
            'success' => false,
            'message' => 'Data sudah dikonfirmasi sebelumnya',
            'confirmed_at' => $student['confirmed_at']
        ], 400);
    }
    
    // Validasi data wajib sebelum konfirmasi
    $required_for_confirmation = [
        'nama' => 'Nama',
        'nik' => 'NIK',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'jenis_kelamin' => 'Jenis Kelamin',
        'nama_ibu' => 'Nama Ibu',
        'email' => 'Email',
        'no_handphone' => 'No. Handphone'
    ];
    
    $missing_fields = [];
    foreach ($required_for_confirmation as $field => $label) {
        if (empty($student[$field])) {
            $missing_fields[] = $label;
        }
    }
    
    if (!empty($missing_fields)) {
        jsonResponse([
            'success' => false,
            'message' => 'Data tidak lengkap. Field berikut wajib diisi: ' . implode(', ', $missing_fields)
        ], 400);
    }
    
    // Mulai transaksi
    $pdo->beginTransaction();
    
    try {
        // Update status konfirmasi
        $confirmed_at = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            UPDATE mahasiswa 
            SET confirmed = 1, confirmed_at = ?, updated_at = ? 
            WHERE nim = ?
        ");
        $stmt->execute([$confirmed_at, $confirmed_at, $nim]);
        
        // Log aktivitas konfirmasi
        logActivity($nim, 'DATA_CONFIRMED', null, [
            'confirmed_at' => $confirmed_at,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Commit transaksi
        $pdo->commit();
        
        // Ambil data yang sudah dikonfirmasi
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ? LIMIT 1");
        $stmt->execute([$nim]);
        $confirmed_student = $stmt->fetch();
        
        // Format tanggal untuk frontend
        if ($confirmed_student['tanggal_lahir']) {
            $confirmed_student['tanggal_lahir'] = date('Y-m-d', strtotime($confirmed_student['tanggal_lahir']));
        }
        
        // Sanitize output
        $confirmed_student = sanitizeOutput($confirmed_student);
        
        jsonResponse([
            'success' => true,
            'message' => 'Data berhasil dikonfirmasi dan telah terkunci',
            'data' => $confirmed_student,
            'confirmed_at' => $confirmed_at
        ]);
        
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Confirmation error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan saat konfirmasi data'
    ], 500);
    
} catch (Exception $e) {
    error_log("General confirmation error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan yang tidak terduga'
    ], 500);
}
?>