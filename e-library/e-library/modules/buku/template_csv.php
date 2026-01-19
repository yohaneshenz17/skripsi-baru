<?php
// SAVE AS: modules/buku/template_csv.php
// Generate CSV template tanpa library

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

// Header row
fputcsv($output, [
    'Nomor Buku',
    'Judul Buku',
    'Pengarang',
    'Penerbit',
    'Tahun Terbit',
    'Stok'
]);

// Example data rows
fputcsv($output, ['BK001', 'Alkitab', 'Berbagai Penulis', 'LAI', '2020', '10']);
fputcsv($output, ['BK002', 'Katekismus Gereja Katolik', 'Gereja Katolik', 'Kanisius', '2019', '5']);
fputcsv($output, ['BK003', 'Teologi Sistematika', 'Dr. John Doe', 'Penerbit XYZ', '2021', '3']);

fclose($output);
exit;
?>
