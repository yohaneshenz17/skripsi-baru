<?php
// SAVE AS: modules/buku/template_csv.php
// Generate CSV template tanpa library - UPDATED WITH IS_REFERENSI

require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Set headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Template_Import_Buku_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row - TAMBAH KOLOM REFERENSI
fputcsv($output, [
    'Nomor Buku',
    'Judul Buku',
    'Pengarang',
    'Penerbit',
    'Tahun Terbit',
    'Stok',
    'Referensi'
]);

// Example data rows - TAMBAH CONTOH REFERENSI
fputcsv($output, ['BK001', 'Alkitab', 'Berbagai Penulis', 'LAI', '2020', '10', 'Tidak']);
fputcsv($output, ['BK002', 'Katekismus Gereja Katolik', 'Gereja Katolik', 'Kanisius', '2019', '5', 'Ya']);
fputcsv($output, ['BK003', 'Ensiklopedia Katolik', 'Tim Penulis', 'Obor', '2021', '3', 'Ya']);

fclose($output);
exit;
?>