<?php
// update.php
// API untuk update data mahasiswa sebelum konfirmasi

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
    
    // Validasi input wajib
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
    
    // Cek apakah data sudah dikonfirmasi (terkunci)
    if ($student['confirmed']) {
        jsonResponse([
            'success' => false,
            'message' => 'Data sudah dikonfirmasi dan tidak dapat diubah'
        ], 400);
    }
    
    // Field yang dapat diupdate
    $updatable_fields = [
        'nama', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'nama_ibu', 'email', 'no_handphone', 'agama', 'desa', 
        'kewarganegaraan', 'npwp', 'alamat_jalan', 'provinsi', 
        'kabupaten_kota', 'kecamatan'
    ];
    
    // Buat array untuk update
    $update_data = [];
    $update_fields = [];
    $update_values = [];
    
    foreach ($updatable_fields as $field) {
        if (isset($input[$field])) {
            $value = trim($input[$field]);
            
            // Validasi khusus untuk field tertentu
            switch ($field) {
                case 'nik':
                    if (!empty($value) && !preg_match('/^\d{16}$/', $value)) {
                        jsonResponse([
                            'success' => false,
                            'message' => 'NIK harus berupa 16 digit angka'
                        ], 400);
                    }
                    break;
                    
                case 'email':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        jsonResponse([
                            'success' => false,
                            'message' => 'Format email tidak valid'
                        ], 400);
                    }
                    break;
                    
                case 'no_handphone':
                    if (!empty($value) && !preg_match('/^[0-9+\-\s]{8,}$/', $value)) {
                        jsonResponse([
                            'success' => false,
                            'message' => 'Format nomor handphone tidak valid'
                        ], 400);
                    }
                    break;
                    
                case 'tanggal_lahir':
                    if (!empty($value)) {
                        $date = DateTime::createFromFormat('Y-m-d', $value);
                        if (!$date || $date->format('Y-m-d') !== $value) {
                            jsonResponse([
                                'success' => false,
                                'message' => 'Format tanggal lahir tidak valid (YYYY-MM-DD)'
                            ], 400);
                        }
                    }
                    break;
                    
                case 'nama':
                    if (empty($value)) {
                        jsonResponse([
                            'success' => false,
                            'message' => 'Nama wajib diisi'
                        ], 400);
                    }
                    break;
                    
                case 'provinsi':
                    if (!empty($value)) {
                        $valid_provinces = [
                            'Papua Selatan', 'Papua', 'Papua Barat', 'Papua Tengah', 
                            'Papua Pegunungan', 'Papua Barat Daya'
                        ];
                        if (!in_array($value, $valid_provinces)) {
                            jsonResponse([
                                'success' => false,
                                'message' => 'Provinsi tidak valid'
                            ], 400);
                        }
                    }
                    break;
            }
            
            $update_fields[] = "$field = ?";
            $update_values[] = $value;
            $update_data[$field] = $value;
        }
    }
    
    if (empty($update_fields)) {
        jsonResponse([
            'success' => false,
            'message' => 'Tidak ada data yang dapat diupdate'
        ], 400);
    }
    
    // Mulai transaksi
    $pdo->beginTransaction();
    
    try {
        // Update data mahasiswa
        $updated_at = date('Y-m-d H:i:s');
        $update_fields[] = "updated_at = ?";
        $update_values[] = $updated_at;
        $update_values[] = $nim; // untuk WHERE clause
        
        $sql = "UPDATE mahasiswa SET " . implode(', ', $update_fields) . " WHERE nim = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($update_values);
        
        // Cek apakah ada baris yang terupdate
        if ($stmt->rowCount() === 0) {
            throw new Exception('Tidak ada data yang diupdate');
        }
        
        // Log aktivitas update
        logActivity($nim, 'DATA_UPDATED', json_encode($update_data), [
            'updated_fields' => array_keys($update_data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Ambil data terbaru
        $stmt = $pdo->prepare("
            SELECT 
                id, nim, nisn, nama, nik, tempat_lahir, tanggal_lahir, 
                jenis_kelamin, no_handphone, email, agama, desa, nama_ibu,
                kewarganegaraan, npwp, alamat_jalan, provinsi, kabupaten_kota,
                kecamatan, confirmed, confirmed_at, created_at, updated_at
            FROM mahasiswa 
            WHERE nim = ?
        ");
        $stmt->execute([$nim]);
        $updated_student = $stmt->fetch();
        
        // Format tanggal untuk frontend
        if ($updated_student['tanggal_lahir']) {
            $updated_student['tanggal_lahir'] = date('Y-m-d', strtotime($updated_student['tanggal_lahir']));
        }
        
        // Sanitize output
        $updated_student = sanitizeOutput($updated_student);
        
        // Commit transaksi
        $pdo->commit();
        
        // Response sukses
        jsonResponse([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $updated_student,
            'updated_fields' => array_keys($update_data)
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Update error: " . $e->getMessage());
    
    // Cek jika error karena duplicate entry
    if ($e->getCode() == 23000) {
        jsonResponse([
            'success' => false,
            'message' => 'Data yang dimasukkan sudah ada (duplikat)'
        ], 400);
    }
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
    ], 500);
    
} catch (Exception $e) {
    error_log("General update error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ], 500);
}
?>