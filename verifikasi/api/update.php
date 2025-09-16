<?php
// Update untuk update.php - bagian validasi provinsi

// Ganti case 'provinsi': yang lama dengan ini:

case 'provinsi':
    if (!empty($value)) {
        $provinsi = trim($value);
        
        // Validasi minimal panjang nama provinsi
        if (strlen($provinsi) < 3) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama provinsi minimal 3 karakter'
            ], 400);
        }
        
        // Validasi format nama provinsi
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $provinsi)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama provinsi hanya boleh berisi huruf, spasi, dan tanda hubung'
            ], 400);
        }
        
        // Optional: Uncomment jika ingin strict validation terhadap daftar provinsi
        /*
        $valid_provinces = [
            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi', 
            'Sumatera Selatan', 'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung', 
            'Kepulauan Riau', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 
            'DI Yogyakarta', 'Jawa Timur', 'Banten', 'Bali', 'Nusa Tenggara Barat', 
            'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah', 
            'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 
            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 
            'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat', 'Maluku', 
            'Maluku Utara', 'Papua Barat', 'Papua', 'Papua Selatan', 
            'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'
        ];
        
        if (!in_array($provinsi, $valid_provinces)) {
            jsonResponse([
                'success' => false,
                'message' => "Provinsi '$provinsi' tidak valid"
            ], 400);
        }
        */
    }
    break;

// Tambahkan juga case untuk kabupaten, kecamatan, desa jika belum ada:

case 'kabupaten_kota':
    if (!empty($value)) {
        $kabupaten = trim($value);
        
        if (strlen($kabupaten) < 3) {
            jsonResponse([
                'success' => false,
                'message' => 'Kabupaten/Kota minimal 3 karakter'
            ], 400);
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $kabupaten)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama kabupaten/kota hanya boleh berisi huruf, spasi, dan tanda hubung'
            ], 400);
        }
    }
    break;

case 'kecamatan':
    if (!empty($value)) {
        $kecamatan = trim($value);
        
        if (strlen($kecamatan) < 2) {
            jsonResponse([
                'success' => false,
                'message' => 'Kecamatan minimal 2 karakter'
            ], 400);
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $kecamatan)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama kecamatan hanya boleh berisi huruf, spasi, dan tanda hubung'
            ], 400);
        }
    }
    break;

case 'desa':
    if (!empty($value)) {
        $desa = trim($value);
        
        if (strlen($desa) < 2) {
            jsonResponse([
                'success' => false,
                'message' => 'Desa/Kelurahan minimal 2 karakter'
            ], 400);
        }
        
        if (!preg_match('/^[a-zA-Z\s\-\.]+$/u', $desa)) {
            jsonResponse([
                'success' => false,
                'message' => 'Nama desa/kelurahan hanya boleh berisi huruf, spasi, dan tanda hubung'
            ], 400);
        }
    }
    break;
?>