<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'stkp7133_admin');
define('DB_PASS', '@Merauke99616');
define('DB_NAME', 'stkp7133_e_library');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', 'https://stkyakobus.ac.id/e-library/');

// Upload paths
define('UPLOAD_MAHASISWA', __DIR__ . '/../uploads/mahasiswa/');
define('UPLOAD_DOSEN', __DIR__ . '/../uploads/dosen/');
define('UPLOAD_EXCEL', __DIR__ . '/../uploads/excel/');
define('UPLOAD_PDF', __DIR__ . '/../uploads/pdf/');

// Create upload directories if not exist
if (!file_exists(UPLOAD_MAHASISWA)) mkdir(UPLOAD_MAHASISWA, 0777, true);
if (!file_exists(UPLOAD_DOSEN)) mkdir(UPLOAD_DOSEN, 0777, true);
if (!file_exists(UPLOAD_EXCEL)) mkdir(UPLOAD_EXCEL, 0777, true);
if (!file_exists(UPLOAD_PDF)) mkdir(UPLOAD_PDF, 0777, true);

?>
