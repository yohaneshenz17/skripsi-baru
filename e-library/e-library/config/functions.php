<?php
// Helper Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    if (empty($tanggal)) return '-';
    
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    if (count($pecahkan) != 3) return $tanggal;
    
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Format bulan romawi
function formatBulanRomawi($bulan) {
    $romawi = array(1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII');
    return $romawi[(int)$bulan];
}

// Hitung keterlambatan
function hitungKeterlambatan($tanggal_jatuh_tempo) {
    $now = new DateTime();
    $jatuh_tempo = new DateTime($tanggal_jatuh_tempo);
    
    if ($now > $jatuh_tempo) {
        $diff = $now->diff($jatuh_tempo);
        return $diff->days;
    }
    
    return 0;
}

// Hitung denda
function hitungDenda($hari_terlambat, $tarif_per_hari = 1000) {
    return $hari_terlambat * $tarif_per_hari;
}

// Generate kode peminjaman
function generateKodePeminjaman() {
    return 'PJM' . date('Ymd') . rand(1000, 9999);
}

// Generate nomor surat
function generateNomorSurat($conn) {
    $tahun = date('Y');
    $bulan_romawi = formatBulanRomawi(date('n'));
    
    // Cek counter tahun ini
    $query = "SELECT counter FROM nomor_surat_counter WHERE tahun = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $tahun);
    $stmt->execute();
    $stmt->bind_result($current_counter);
    
    if ($stmt->fetch()) {
        $counter = $current_counter + 1;
        $stmt->close();
        
        // Update counter
        $update = "UPDATE nomor_surat_counter SET counter = ? WHERE tahun = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("is", $counter, $tahun);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $counter = 1;
        $stmt->close();
        
        // Insert counter baru
        $insert = "INSERT INTO nomor_surat_counter (tahun, counter) VALUES (?, ?)";
        $stmt2 = $conn->prepare($insert);
        $stmt2->bind_param("si", $tahun, $counter);
        $stmt2->execute();
        $stmt2->close();
    }
    
    // Format: 001/SKP-PERP/STK/I/2026
    $nomor = sprintf("%03d", $counter) . "/SKP-PERP/STK/" . $bulan_romawi . "/" . $tahun;
    
    return $nomor;
}

// Upload foto
function uploadFoto($file, $folder) {
    $allowed = array('jpg', 'jpeg', 'png');
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return array('success' => false, 'message' => 'Format file tidak diizinkan. Gunakan JPG, JPEG, atau PNG.');
    }
    
    if ($file['size'] > 2097152) { // 2MB
        return array('success' => false, 'message' => 'Ukuran file maksimal 2MB.');
    }
    
    $newname = uniqid() . '.' . $ext;
    $destination = $folder . $newname;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return array('success' => true, 'filename' => $newname);
    } else {
        return array('success' => false, 'message' => 'Gagal mengupload file.');
    }
}

// Alert helper
function setAlert($type, $message) {
    $_SESSION['alert'] = array('type' => $type, 'message' => $message);
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">
                ' . $alert['message'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['alert']);
    }
}

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Get nama peminjam
function getNamaPeminjam($conn, $jenis, $id) {
    if ($jenis == 'mahasiswa') {
        $query = "SELECT nama FROM mahasiswa WHERE id = ?";
    } else {
        $query = "SELECT nama FROM dosen WHERE id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nama);
    
    if ($stmt->fetch()) {
        $stmt->close();
        return $nama;
    }
    
    $stmt->close();
    return '-';
}

// Get identifier peminjam (NIM/NUPTK)
function getIdentifierPeminjam($conn, $jenis, $id) {
    if ($jenis == 'mahasiswa') {
        $query = "SELECT nim as identifier FROM mahasiswa WHERE id = ?";
    } else {
        $query = "SELECT nuptk as identifier FROM dosen WHERE id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($identifier);
    
    if ($stmt->fetch()) {
        $stmt->close();
        return $identifier;
    }
    
    $stmt->close();
    return '-';
}

// Update status peminjaman
function updateStatusPeminjaman($conn) {
    $today = date('Y-m-d');
    
    // Update status terlambat
    $query = "UPDATE peminjaman 
              SET status = 'terlambat' 
              WHERE tanggal_jatuh_tempo < ? 
              AND status IN ('dipinjam', 'diperpanjang')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
}
?>