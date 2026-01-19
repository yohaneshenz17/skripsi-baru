<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get peminjaman data untuk cek status
$query = "SELECT buku_id, status FROM peminjaman WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($buku_id, $status);

if ($stmt->fetch()) {
    $stmt->close();
    
    // Hanya bisa hapus peminjaman dengan status 'dipinjam'
    if ($status !== 'dipinjam') {
        setAlert('danger', 'Hanya peminjaman dengan status "Dipinjam" yang bisa dihapus!');
        header('Location: index.php');
        exit;
    }
    
    // Kembalikan stok buku
    $update_stok = "UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id = ?";
    $stmt2 = $conn->prepare($update_stok);
    $stmt2->bind_param("i", $buku_id);
    $stmt2->execute();
    $stmt2->close();
    
    // Hapus peminjaman
    $delete = "DELETE FROM peminjaman WHERE id = ?";
    $stmt3 = $conn->prepare($delete);
    $stmt3->bind_param("i", $id);
    
    if ($stmt3->execute()) {
        $stmt3->close();
        setAlert('success', 'Peminjaman berhasil dihapus dan stok buku dikembalikan!');
    } else {
        $stmt3->close();
        setAlert('danger', 'Gagal menghapus peminjaman!');
    }
} else {
    $stmt->close();
    setAlert('danger', 'Peminjaman tidak ditemukan!');
}

header('Location: index.php');
exit;
?>