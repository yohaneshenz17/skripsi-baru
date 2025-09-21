<?php
require_once 'config.php';

// Log aktivitas logout jika user sedang login
if (isLoggedIn()) {
    try {
        logAktivitas($pdo, $_SESSION['user_id'], 'Logout', 'User berhasil logout');
    } catch (Exception $e) {
        // Silent error untuk log
    }
}

// Hapus semua session
session_destroy();

// Redirect ke halaman login
header('Location: login.php?message=logout_success');
exit();
?>