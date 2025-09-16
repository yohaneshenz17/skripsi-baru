<?php
// verifikasi/api/wilayah.php
// API untuk mengambil data wilayah Indonesia

require_once 'config.php';

// Set CORS headers
setCORSHeaders();

// Hanya izinkan method GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ], 405);
}

try {
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';
    
    // Base URL API wilayah Indonesia
    $base_url = 'https://emsifa.github.io/api-wilayah-indonesia/api';
    
    switch ($type) {
        case 'provinces':
            $url = "$base_url/provinces.json";
            break;
            
        case 'regencies':
            if (empty($id)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ID provinsi wajib diisi'
                ], 400);
            }
            $url = "$base_url/regencies/$id.json";
            break;
            
        case 'districts':
            if (empty($id)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ID kabupaten wajib diisi'
                ], 400);
            }
            $url = "$base_url/districts/$id.json";
            break;
            
        case 'villages':
            if (empty($id)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'ID kecamatan wajib diisi'
                ], 400);
            }
            $url = "$base_url/villages/$id.json";
            break;
            
        default:
            jsonResponse([
                'success' => false,
                'message' => 'Tipe data tidak valid. Gunakan: provinces, regencies, districts, atau villages'
            ], 400);
    }
    
    // Ambil data dari API dengan caching
    $cache_key = "wilayah_" . $type . "_" . $id;
    $cache_file = __DIR__ . "/cache/$cache_key.json";
    $cache_duration = 24 * 60 * 60; // 24 jam
    
    // Buat direktori cache jika belum ada
    if (!is_dir(__DIR__ . "/cache")) {
        mkdir(__DIR__ . "/cache", 0755, true);
    }
    
    $use_cache = false;
    
    // Cek apakah cache masih valid
    if (file_exists($cache_file)) {
        $cache_time = filemtime($cache_file);
        if ((time() - $cache_time) < $cache_duration) {
            $use_cache = true;
        }
    }
    
    if ($use_cache) {
        // Gunakan data dari cache
        $data = json_decode(file_get_contents($cache_file), true);
    } else {
        // Ambil data dari API
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'STK Yakobus Verifikasi System'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            jsonResponse([
                'success' => false,
                'message' => 'Gagal mengambil data wilayah. Silakan coba lagi.'
            ], 500);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonResponse([
                'success' => false,
                'message' => 'Data wilayah tidak valid'
            ], 500);
        }
        
        // Simpan ke cache
        file_put_contents($cache_file, json_encode($data));
    }
    
    // Format response
    jsonResponse([
        'success' => true,
        'data' => $data,
        'cached' => $use_cache,
        'type' => $type,
        'count' => count($data)
    ]);
    
} catch (Exception $e) {
    error_log("Wilayah API Error: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Terjadi kesalahan internal server'
    ], 500);
}
?>