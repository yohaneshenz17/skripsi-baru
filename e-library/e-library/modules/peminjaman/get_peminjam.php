<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

header('Content-Type: application/json');

$jenis = isset($_GET['jenis']) ? sanitize($_GET['jenis']) : '';

if (empty($jenis) || !in_array($jenis, ['mahasiswa', 'dosen'])) {
    echo json_encode(['success' => false, 'message' => 'Jenis peminjam tidak valid']);
    exit;
}

$table = $jenis === 'mahasiswa' ? 'mahasiswa' : 'dosen';
$identifier = $jenis === 'mahasiswa' ? 'nim' : 'nuptk';

$query = "SELECT id, nama, $identifier FROM $table ORDER BY nama ASC";
$result = $conn->query($query);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            $identifier => $row[$identifier]
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);