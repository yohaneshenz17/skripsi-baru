<?php
// modules/mahasiswa/template_csv.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Set headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Template_Import_Mahasiswa_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'NIM',
    'Nama Lengkap',
    'Program Studi',
    'Angkatan',
    'No. HP'
]);

// Example data rows
fputcsv($output, ['2021001', 'John Doe', 'S1 Pendidikan Keagamaan Katolik', '2021', '081234567890']);
fputcsv($output, ['2022002', 'Jane Smith', 'S1 Pendidikan Guru Sekolah Dasar', '2022', '081234567891']);
fputcsv($output, ['2023003', 'Bob Johnson', 'Profesi Pendidikan Guru PAK', '2023', '081234567892']);

fclose($output);
exit;
?>