<?php
// File: modules/buku/export.php

require_once '../../config/database.php';
require_once '../../config/functions.php';

// Cek login admin
requireLogin();

// 1. Set Header HTTP agar browser mengenali ini sebagai file download
$filename = "Data_Buku_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 2. Buka output stream PHP
$output = fopen('php://output', 'w');

// 3. Tulis BOM (Byte Order Mark) agar Excel bisa membaca karakter UTF-8 dengan benar
fputs($output, "\xEF\xBB\xBF");

// 4. Tulis Baris Header (Judul Kolom) - TAMBAH KOLOM REFERENSI
$csv_headers = array('No', 'Nomor Buku', 'Judul Buku', 'Pengarang', 'Penerbit', 'Tahun Terbit', 'Stok Total', 'Stok Tersedia', 'Referensi');
fputcsv($output, $csv_headers);

// 5. Ambil Data dari Database
$query = "SELECT * FROM buku ORDER BY judul ASC";
$result = $conn->query($query);

$no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Siapkan baris data - TAMBAH IS_REFERENSI
        $csv_row = array(
            $no++,
            $row['nomor_buku'],
            $row['judul'],
            $row['pengarang'],
            $row['penerbit'],
            $row['tahun_terbit'],
            $row['stok'],
            $row['stok_tersedia'],
            $row['is_referensi'] == 1 ? 'Ya' : 'Tidak'
        );
        
        // Tulis baris ke file CSV
        fputcsv($output, $csv_row);
    }
}

// 6. Tutup stream dan hentikan script
fclose($output);
exit();
?>