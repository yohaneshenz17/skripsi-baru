<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        setAlert('danger', 'Semua field harus diisi!');
    } elseif ($new_password !== $confirm_password) {
        setAlert('danger', 'Password baru dan konfirmasi tidak cocok!');
    } elseif (strlen($new_password) < 6) {
        setAlert('danger', 'Password baru minimal 6 karakter!');
    } else {
        // Cek password lama
        $query = "SELECT password FROM admin WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();
        
        if (password_verify($old_password, $db_password)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE admin SET password = ? WHERE id = ?";
            $stmt2 = $conn->prepare($update);
            $stmt2->bind_param("si", $hashed_password, $_SESSION['admin_id']);
            
            if ($stmt2->execute()) {
                setAlert('success', 'Password berhasil diubah!');
                header('Location: dashboard.php');
                exit;
            } else {
                setAlert('danger', 'Gagal mengubah password!');
            }
            $stmt2->close();
        } else {
            setAlert('danger', 'Password lama tidak sesuai!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-key me-2"></i>Ganti Password</h1>
                </div>

                <?php showAlert(); ?>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Password Lama</label>
                                        <input type="password" class="form-control" name="old_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" name="new_password" minlength="6" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Simpan
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-2"></i>Batal
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>