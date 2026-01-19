<?php
// modules/dosen/template_csv.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

// Set headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Template_Import_Dosen_' . date('Y-m-d') . '.csv');

// Open output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'NUPTK',
    'Nama Lengkap',
    'Program Studi',
    'No. HP'
]);

// Example data rows
fputcsv($output, ['1234567890123456', 'Dr. John Doe', 'S1 Pendidikan Keagamaan Katolik', '081234567890']);
fputcsv($output, ['2345678901234567', 'Dr. Jane Smith', 'S1 Pendidikan Guru Sekolah Dasar', '081234567891']);
fputcsv($output, ['3456789012345678', 'Prof. Bob Johnson', 'Profesi Pendidikan Guru PAK', '081234567892']);

fclose($output);
exit;
?>