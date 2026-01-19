<?php
// -------------------------------------------------------------------
// SAVE THIS SEPARATELY AS: modules/dosen/delete.php
// -------------------------------------------------------------------
/*
<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if dosen has active peminjaman
$check = "SELECT COUNT(*) as total FROM peminjaman WHERE jenis_peminjam = 'dosen' AND peminjam_id = ? AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
$stmt = $conn->prepare($check);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

if ($total > 0) {
    setAlert('danger', 'Tidak dapat menghapus dosen yang masih memiliki peminjaman aktif!');
    header('Location: index.php');
    exit;
}

// Get foto to delete
$query = "SELECT foto FROM dosen WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($foto);
$stmt->fetch();
$stmt->close();

// Delete dosen
$query = "DELETE FROM dosen WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete foto file if exists
    if ($foto && file_exists(UPLOAD_DOSEN . $foto)) {
        unlink(UPLOAD_DOSEN . $foto);
    }
    
    $stmt->close();
    setAlert('success', 'Dosen berhasil dihapus!');
} else {
    $stmt->close();
    setAlert('danger', 'Gagal menghapus dosen!');
}

header('Location: index.php');
exit;
?>
*/