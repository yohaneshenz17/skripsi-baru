<?php
// update.php
// API untuk update data mahasiswa

require_once 'config.php';

// Set CORS headers
setCORSHeaders();

// Hanya izinkan method POST dan PUT
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
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
    $required_fields = ['nim', 'nama'];
    $validation_errors = validateInput($input, $required_fields);
    
    if (!empty($validation_errors)) {
        jsonResponse([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $validation_errors
        ], 400);
    }
    
    $nim = trim($input['nim']);
    
    // Koneksi database
    $pdo = getDBConnection();
    
    // Cek apakah mahasiswa ada dan belum dikonfirmasi
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ? LIMIT 1");
    $stmt->execute([$nim]);
    $existing_student = $stmt->fetch();
    
    if (!$existing_student) {
        jsonResponse([
            'success' => false,
            'message' => 'Data mahasiswa tidak ditemukan'
        ], 404);
    }
    
    if ($existing_student['confirmed']) {
        jsonResponse([
            'success' => false,
            'message' => 'Data telah dikonfirmasi dan tidak dapat diubah'
        ], 403);
    }
    
    // Persiapkan data untuk update - Fixed field mapping untuk konsistensi dengan frontend
    $update_fields = [
        'nama' => $input['nama'] ?? $existing_student['nama'],
        'nik' => $input['nik'] ?? $existing_student['nik'],
        'tempat_lahir' => $input['tempat_lahir'] ?? $existing_student['tempat_lahir'],
        'tanggal_lahir' => $input['tanggal_lahir'] ?? $existing_student['tanggal_lahir'],
        'jenis_kelamin' => $input['jenis_kelamin'] ?? $existing_student['jenis_kelamin'],
        'no_handphone' => $input['no_handphone'] ?? $existing_student['no_handphone'],
        'email' => $input['email'] ?? $existing_student['email'],
        'agama' => $input['agama'] ?? $existing_student['agama'],
        'desa' => $input['desa'] ?? $existing_student['desa'],
        'nama_ibu' => $input['nama_ibu'] ?? $existing_student['nama_ibu'],
        'kewarganegaraan' => $input['kewarganegaraan'] ?? $existing_student['kewarganegaraan'],
        'npwp' => $input['npwp'] ?? $existing_student['npwp'],
        'alamat_jalan' => $input['alamat_jalan'] ?? $existing_student['alamat_jalan'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Validasi data update
    $validation_errors = validateInput($update_fields, ['nama']);
    
    if (!empty($validation_errors)) {
        jsonResponse([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $validation_errors
        ], 400);
    }
    
    // Mulai transaksi
    $pdo->beginTransaction();
    
    try {
        // Update data mahasiswa
        $sql = "UPDATE mahasiswa SET 
                nama = ?, nik = ?, tempat_lahir = ?, tanggal_lahir = ?, 
                jenis_kelamin = ?, no_handphone = ?, email = ?, agama = ?, 
                desa = ?, nama_ibu = ?, kewarganegaraan = ?, npwp = ?, 
                alamat_jalan = ?, updated_at = ?
                WHERE nim = ?";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $update_fields['nama'],
            $update_fields['nik'],
            $update_fields['tempat_lahir'],
            $update_fields['tanggal_lahir'],
            $update_fields['jenis_kelamin'],
            $update_fields['no_handphone'],
            $update_fields['email'],
            $update_fields['agama'],
            $update_fields['desa'],
            $update_fields['nama_ibu'],
            $update_fields['kewarganegaraan'],
            $update_fields['npwp'],
            $update_fields['alamat_jalan'],
            $update_fields['updated_at'],
            $nim
        ]);
        
        if (!$result) {
            throw new Exception("Failed to update student data");
        }
        
        // Log aktivitas update
        logActivity($nim, 'DATA_UPDATED', $existing_student, $update_fields);
        
        // Commit transaksi
        $pdo->commit();
        
        // Ambil data yang sudah diupdate
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ? LIMIT 1");
        $stmt->execute([$nim]);
        $updated_student = $stmt->fetch();
        
        if (!$updated_student) {
            throw new Exception("Failed to retrieve updated student data");
        }
        
        // Format tanggal untuk frontend
        if ($updated_student['tanggal_lahir']) {
            $updated_student['tanggal_lahir'] = date('Y-m-d', strtotime($updated_student['tanggal_lahir']));
        }
        
        // Sanitize output
        $updated_student = sanitizeOutput($updated_student);
        
        jsonResponse([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $updated_student
        ]);
        
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Update error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan saat memperbarui data'
    ], 500);
    
} catch (Exception $e) {
    error_log("General update error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan yang tidak terduga'
    ], 500);
}
?>