<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

header('Content-Type: application/json');

$query = "SELECT id, nomor_buku, judul, pengarang, stok_tersedia 
          FROM buku 
          WHERE stok_tersedia > 0 
          ORDER BY judul ASC";

$result = $conn->query($query);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'nomor_buku' => $row['nomor_buku'],
            'judul' => $row['judul'],
            'pengarang' => $row['pengarang'],
            'stok_tersedia' => $row['stok_tersedia']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $data
]);