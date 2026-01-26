<?php
// File: modules/mahasiswa/export.php

require_once '../../config/database.php';
require_once '../../config/functions.php';

// Cek login admin
requireLogin();

// 1. Set Header HTTP agar browser mengenali ini sebagai file download
$filename = "Data_Mahasiswa_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 2. Buka output stream PHP
$output = fopen('php://output', 'w');

// 3. Tulis BOM (Byte Order Mark) agar Excel bisa membaca karakter UTF-8 dengan benar
fputs($output, "\xEF\xBB\xBF");

// 4. Tulis Baris Header (Judul Kolom)
$csv_headers = array('No', 'NIM', 'Nama Lengkap', 'Program Studi', 'Angkatan', 'No. HP');
fputcsv($output, $csv_headers);

// 5. Ambil Data dari Database
// Urutkan berdasarkan Angkatan terbaru, lalu Nama abjad
$query = "SELECT * FROM mahasiswa ORDER BY angkatan DESC, nama ASC";
$result = $conn->query($query);

$no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Siapkan baris data
        // Pastikan No HP diperlakukan sebagai string agar angka 0 di depan tidak hilang
        $no_hp = " " . $row['no_hp']; 

        $csv_row = array(
            $no++,
            $row['nim'],
            $row['nama'],
            $row['program_studi'],
            $row['angkatan'],
            $no_hp
        );
        
        // Tulis baris ke file CSV
        fputcsv($output, $csv_row);
    }
}

// 6. Tutup stream dan hentikan script
fclose($output);
exit();
?>