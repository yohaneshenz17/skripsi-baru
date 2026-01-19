<?php
require_once 'config/database.php';
require_once 'config/functions.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    
    // Cek username
    $query = "SELECT id, email FROM admin WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($admin_id, $email);
    
    if ($stmt->fetch()) {
        $stmt->close();
        
        // Generate token reset
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simpan token (dalam aplikasi production, gunakan tabel terpisah)
        // Untuk demo, kita akan langsung kirim password baru
        
        $new_password = bin2hex(random_bytes(4)); // Password temporary
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update = "UPDATE admin SET password = ? WHERE id = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("si", $hashed_password, $admin_id);
        
        if ($stmt2->execute()) {
            // Kirim email (simulasi)
            $to = $email;
            $subject = "Reset Password E-Library STK Yakobus";
            $message = "Password sementara Anda: " . $new_password . "\n\nSilakan login dan ubah password Anda segera.";
            $headers = "From: noreply@stkyakobus.ac.id";
            
            // Simulasi pengiriman email
            // mail($to, $subject, $message, $headers);
            
            $success = true;
            $temp_password = $new_password; // Untuk demo, ditampilkan di layar
        } else {
            $error = "Gagal mereset password. Silakan coba lagi.";
        }
        $stmt2->close();
    } else {
        $stmt->close();
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-container {
            max-width: 450px;
            width: 100%;
        }
        .forgot-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .forgot-body {
            padding: 40px;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="card forgot-card">
            <div class="forgot-header">
                <i class="bi bi-key fs-1 mb-3"></i>
                <h3 class="mb-1">Lupa Password</h3>
                <p class="mb-0">E-Library STK Yakobus</p>
            </div>
            <div class="forgot-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Password berhasil direset!</strong><br>
                        Password sementara telah dikirim ke email: <strong><?= $email ?></strong>
                        <br><br>
                        <div class="alert alert-warning mb-0">
                            <small><strong>Password Sementara (Demo):</strong> <?= $temp_password ?></small>
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="index.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Login
                        </a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-muted mb-4">Masukkan username Anda. Password baru akan dikirim ke email terdaftar.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="username" required autofocus>
                            </div>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-reset">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reset Password
                            </button>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>