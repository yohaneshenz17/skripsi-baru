<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get data perpanjangan - FIXED: bind_result pattern
$query = "SELECT peminjaman_id, jatuh_tempo_lama, jatuh_tempo_baru FROM perpanjangan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($peminjaman_id, $jatuh_tempo_lama, $jatuh_tempo_baru);

if (!$stmt->fetch()) {
    $stmt->close();
    setAlert('danger', 'Data perpanjangan tidak ditemukan!');
    header('Location: index.php');
    exit;
}
$stmt->close();

// Delete perpanjangan
$delete = "DELETE FROM perpanjangan WHERE id = ?";
$stmt = $conn->prepare($delete);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    
    // Kembalikan status peminjaman ke 'dipinjam' dan tanggal jatuh tempo ke tanggal lama
    $update = "UPDATE peminjaman SET status = 'dipinjam', tanggal_jatuh_tempo = ? WHERE id = ?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("si", $jatuh_tempo_lama, $peminjaman_id);
    $stmt2->execute();
    $stmt2->close();
    
    setAlert('success', 'Perpanjangan berhasil dibatalkan! Status peminjaman dikembalikan.');
} else {
    $stmt->close();
    setAlert('danger', 'Gagal membatalkan perpanjangan!');
}

header('Location: index.php');
exit;
?>