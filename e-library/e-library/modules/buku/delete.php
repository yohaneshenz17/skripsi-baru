<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if book is being borrowed
$check = "SELECT COUNT(*) as total FROM peminjaman WHERE buku_id = ? AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
$stmt = $conn->prepare($check);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    setAlert('danger', 'Tidak dapat menghapus buku yang sedang dipinjam!');
    header('Location: index.php');
    exit;
}

// Delete book
$query = "DELETE FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    setAlert('success', 'Buku berhasil dihapus!');
} else {
    setAlert('danger', 'Gagal menghapus buku!');
}

header('Location: index.php');
exit;
?>
