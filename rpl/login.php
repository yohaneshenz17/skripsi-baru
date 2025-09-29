<?php
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: dashboard_admin.php');
    } else {
        header('Location: dashboard_dosen.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap, email, role, status FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Log aktivitas
                logAktivitas($pdo, $user['id'], 'Login', 'User berhasil login');
                
                // Redirect berdasarkan role
                if ($user['role'] === 'admin') {
                    header('Location: dashboard_admin.php');
                } else {
                    header('Location: dashboard_dosen.php');
                }
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penilaian RPL STK St. Yakobus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            object-fit: contain;
        }
        
        .logo h1 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
            line-height: 1.4;
            font-weight: 600;
        }
        
        .logo h2 {
            color: #667eea;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 500;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .error {
            background: #ffe6e6;
            color: #d63031;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #ff7675;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #999;
            font-size: 0.8rem;
        }
        
        .footer p {
            margin-top: 0.2rem;
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
                max-width: 350px;
            }
            
            .logo img {
                width: 60px;
                height: 60px;
            }
            
            .logo h1 {
                font-size: 1.1rem;
            }
            
            .logo h2 {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/logo-stk.png" alt="Logo STK Yakobus" onerror="this.style.display='none'">
            <h1>Sistem Informasi Penilaian RPL</h1>
            <h2>Sekolah Tinggi Katolik St. Yakobus Merauke</h2>
            <p>Silakan masuk untuk melanjutkan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       value="<?= isset($_POST['username']) ? sanitizeInput($_POST['username']) : '' ?>"
                       placeholder="Masukkan username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn">Masuk</button>
        </form>
        
        <div class="footer">
            @ Made With Love by SIPD @
            <p>2025</p>
        </div>
    </div>
</body>
</html>