<?php
require_once 'config.php';

// Redirect berdasarkan status login
if (isLoggedIn()) {
    // User sudah login, redirect ke dashboard sesuai role
    if (isAdmin()) {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_dosen.php');
    }
} else {
    // User belum login, redirect ke halaman login
    header('Location: login.php');
}

exit();
?>