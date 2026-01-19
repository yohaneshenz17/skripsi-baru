<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if mahasiswa has active peminjaman
$check = "SELECT COUNT(*) as total FROM peminjaman WHERE jenis_peminjam = 'mahasiswa' AND peminjam_id = ? AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
$stmt = $conn->prepare($check);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

if ($total > 0) {
    setAlert('danger', 'Tidak dapat menghapus mahasiswa yang masih memiliki peminjaman aktif!');
    header('Location: index.php');
    exit;
}

// Get foto to delete
$query = "SELECT foto FROM mahasiswa WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($foto);
$stmt->fetch();
$stmt->close();

// Delete mahasiswa
$query = "DELETE FROM mahasiswa WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete foto file if exists
    if ($foto && file_exists(UPLOAD_MAHASISWA . $foto)) {
        unlink(UPLOAD_MAHASISWA . $foto);
    }
    
    $stmt->close();
    setAlert('success', 'Mahasiswa berhasil dihapus!');
} else {
    $stmt->close();
    setAlert('danger', 'Gagal menghapus mahasiswa!');
}

header('Location: index.php');
exit;
?>