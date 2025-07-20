<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Login Sistem Informasi Skripsi STK Yakobus Merauke">
  <meta name="author" content="STK Yakobus Merauke">
  <title>Login Sistem Informasi Skripsi</title>
  <link rel="icon" href="<?= base_url() ?>cdn/img/icons/favicon.png" type="image/png">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/nucleo/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/css/argon.css?v=1.2.0" type="text/css">
  <style>
    /* PERBAIKAN LAYOUT - Custom styles for smoother, more compact layout */
    body, html {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      /* Perbaikan untuk desktop - izinkan scroll jika diperlukan */
      overflow-x: hidden;
      overflow-y: auto;
    }
    
    .main-content {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
      /* Perbaikan untuk desktop - height yang fleksibel */
      height: auto;
    }
    
    /* Desktop optimizations */
    @media (min-width: 769px) {
      .main-content {
        min-height: 100vh;
        justify-content: space-between;
        padding-bottom: 20px; /* Pastikan ada ruang untuk footer */
      }
    }
    
    /* Mobile adjustments */
    @media (max-width: 768px) {
      .main-content {
        min-height: 100vh;
        height: auto;
      }
    }
    
    /* PERBAIKAN 1: Header yang lebih compact TANPA separator yang kontras */
    .header {
      padding: 2rem 0 1rem 0 !important;
      flex-shrink: 0;
      position: relative;
      z-index: 10;
      /* MENGHILANGKAN background gradient yang menyebabkan kontras */
      background: transparent !important;
    }
    
    /* Desktop optimizations untuk header */
    @media (min-width: 769px) {
      .header {
        padding: 1.5rem 0 0.8rem 0 !important; /* Kurangi padding untuk desktop */
      }
    }
    
    /* Laptop specific (tinggi layar terbatas) */
    @media (min-width: 769px) and (max-height: 800px) {
      .header {
        padding: 1.2rem 0 0.6rem 0 !important; /* Lebih compact untuk laptop */
      }
    }
    
    .header-body {
      margin-bottom: 0rem !important; /* Dikurangi drastis untuk mendekatkan jarak */
      position: relative;
      z-index: 15;
    }
    
    /* PERBAIKAN 2: MENGHILANGKAN separator yang menyebabkan garis kontras */
    .separator {
      display: none !important; /* Hilangkan completely separator */
    }
    
    /* PERBAIKAN 3: Container login positioning - lebih dekat dengan header */
    .container-login {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 0;
      margin-top: -2rem !important;
      position: relative;
      z-index: 10;
    }
    
    /* Desktop optimizations untuk container login */
    @media (min-width: 769px) {
      .container-login {
        margin-top: -1.5rem !important; /* Kurangi margin untuk desktop */
        padding: 0.3rem 0;
      }
    }
    
    /* Laptop specific optimizations */
    @media (min-width: 769px) and (max-height: 800px) {
      .container-login {
        margin-top: -1rem !important; /* Lebih dekat untuk laptop */
        padding: 0.2rem 0;
      }
    }
    
    /* PERBAIKAN 4: Logo dan text spacing yang lebih rapat */
    .logo-header {
      max-width: 70px;
      margin-bottom: 0.8rem;
      position: relative;
      z-index: 20;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    
    .welcome-title {
      font-size: 1.6rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
      position: relative;
      z-index: 20;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .welcome-desc {
      font-size: 0.95rem;
      margin-bottom: 0;
      line-height: 1.4;
      position: relative;
      z-index: 20;
      padding: 0 1rem;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    /* Desktop optimizations untuk logo dan text */
    @media (min-width: 769px) {
      .logo-header {
        max-width: 65px; /* Sedikit lebih kecil untuk desktop */
        margin-bottom: 0.6rem;
      }
      
      .welcome-title {
        font-size: 1.5rem; /* Sedikit lebih kecil untuk desktop */
        margin-bottom: 0.4rem;
      }
      
      .welcome-desc {
        font-size: 0.9rem; /* Sedikit lebih kecil untuk desktop */
        padding: 0 0.8rem;
      }
    }
    
    /* Laptop specific optimizations */
    @media (min-width: 769px) and (max-height: 800px) {
      .logo-header {
        max-width: 60px; /* Lebih kecil untuk laptop */
        margin-bottom: 0.5rem;
      }
      
      .welcome-title {
        font-size: 1.4rem; /* Lebih kecil untuk laptop */
        margin-bottom: 0.3rem;
      }
      
      .welcome-desc {
        font-size: 0.85rem; /* Lebih kecil untuk laptop */
        padding: 0 0.6rem;
        line-height: 1.3;
      }
    }
    
    /* PERBAIKAN 5: Container untuk welcome text dengan background subtle */
    .welcome-text-container {
      position: relative;
      z-index: 25;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 15px;
      padding: 1rem 1.5rem;
      margin: 0 auto 1rem auto;
      max-width: 90%;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    /* Desktop optimizations untuk welcome text */
    @media (min-width: 769px) {
      .welcome-text-container {
        padding: 0.8rem 1.2rem; /* Kurangi padding untuk desktop */
        margin: 0 auto 0.8rem auto;
      }
    }
    
    /* Laptop specific optimizations */
    @media (min-width: 769px) and (max-height: 800px) {
      .welcome-text-container {
        padding: 0.6rem 1rem; /* Lebih compact untuk laptop */
        margin: 0 auto 0.6rem auto;
      }
    }
    
    .login-card {
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      border-radius: 15px;
      border: none;
      overflow: hidden;
    }
    
    .card-body {
      padding: 2rem !important;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }
    
    /* Desktop optimizations untuk card body */
    @media (min-width: 769px) {
      .card-body {
        padding: 1.8rem !important; /* Kurangi padding untuk desktop */
      }
    }
    
    /* Laptop specific optimizations */
    @media (min-width: 769px) and (max-height: 800px) {
      .card-body {
        padding: 1.5rem !important; /* Lebih compact untuk laptop */
      }
    }
    
    /* PERBAIKAN 6: Form styling improvements */
    .form-control {
      border-radius: 10px;
      border: 1px solid rgba(0,0,0,0.1);
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
    }
    
    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .input-group-text {
      border-radius: 10px 0 0 10px;
      background: #f8f9fa;
      border: 1px solid rgba(0,0,0,0.1);
      border-right: none;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 10px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .btn-outline-primary {
      border: 2px solid #667eea;
      color: #667eea;
      border-radius: 8px;
      font-weight: 500;
    }
    
    .btn-outline-primary:hover {
      background: #667eea;
      border-color: #667eea;
      color: white;
    }
    
    /* PERBAIKAN 7: Credit text positioning yang lebih baik - TETAP TERLIHAT */
    .credit-text {
      text-align: center;
      padding: 0;
      margin-top: 1rem;
      position: relative;
      z-index: 10;
      flex-shrink: 0;
      /* Memastikan footer selalu terlihat */
      min-height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .credit-text p {
      color: rgba(255, 255, 255, 0.95) !important;
      font-size: 0.9rem;
      margin: 0;
      padding: 0.5rem 0 1rem 0;
      text-shadow: 0 1px 3px rgba(0,0,0,0.2);
      font-style: italic;
      font-weight: 500;
      /* Memastikan teks tetap terlihat */
      visibility: visible !important;
      opacity: 1 !important;
      display: block !important;
    }
    
    /* Desktop optimizations untuk footer - PASTIKAN SELALU TERLIHAT */
    @media (min-width: 769px) {
      .credit-text {
        margin-top: 0.8rem; /* Kurangi margin untuk desktop */
        min-height: 35px;
        padding: 0 0 0.5rem 0; /* Tambah padding bottom */
      }
      
      .credit-text p {
        padding: 0.3rem 0 0.8rem 0; /* Adjust padding untuk desktop */
        font-size: 0.9rem;
      }
    }
    
    /* Laptop specific optimizations - PENTING UNTUK FOOTER TERLIHAT */
    @media (min-width: 769px) and (max-height: 800px) {
      .credit-text {
        margin-top: 0.5rem; /* Margin minimal untuk laptop */
        min-height: 30px;
        padding: 0 0 0.3rem 0; /* Padding bottom minimal */
      }
      
      .credit-text p {
        padding: 0.2rem 0 0.5rem 0; /* Padding minimal untuk laptop */
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.95) !important;
        visibility: visible !important;
        opacity: 1 !important;
      }
    }
    
    /* PERBAIKAN 8: Responsive adjustments untuk mobile */
    @media (max-width: 768px) {
      .container-login {
        margin-top: -1rem !important;
        padding: 1rem 0;
      }
      
      .header {
        padding: 1.5rem 0 0.5rem 0 !important;
      }
      
      .header-body {
        margin-bottom: 0rem !important;
      }
      
      .welcome-desc {
        padding: 0 0.5rem;
        font-size: 0.85rem;
      }
      
      .welcome-text-container {
        max-width: 95%;
        padding: 0.8rem 1rem;
        margin: 0 auto 0.8rem auto;
      }
      
      .credit-text {
        margin-top: 0.5rem;
        padding: 0.5rem 0;
        /* Memastikan footer terlihat di mobile */
        min-height: 35px;
      }
      
      .credit-text p {
        font-size: 0.85rem;
        padding: 0.3rem 0 1rem 0;
        color: rgba(255, 255, 255, 0.95) !important;
        /* Memastikan teks tetap terlihat di mobile */
        visibility: visible !important;
        opacity: 1 !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
      }
      
      .logo-header {
        max-width: 60px;
        margin-bottom: 0.6rem;
      }
      
      .welcome-title {
        font-size: 1.3rem;
        margin-bottom: 0.3rem;
      }
      
      .card-body {
        padding: 1.5rem !important;
      }
    }
    
    /* PERBAIKAN 9: Adjustments untuk layar medium height */
    @media (max-height: 700px) {
      .header {
        padding: 1rem 0 0.5rem 0 !important;
      }
      
      .logo-header {
        max-width: 60px;
        margin-bottom: 0.5rem;
      }
      
      .welcome-title {
        font-size: 1.4rem;
        margin-bottom: 0.3rem;
      }
      
      .welcome-desc {
        font-size: 0.85rem;
      }
      
      .welcome-text-container {
        max-width: 92%;
        padding: 0.8rem 1.2rem;
        margin: 0 auto 0.8rem auto;
      }
      
      .container-login {
        margin-top: -1.5rem !important;
      }
      
      .credit-text {
        margin-top: 0.5rem;
        /* Memastikan footer terlihat di layar medium */
        min-height: 35px;
      }
      
      .credit-text p {
        font-size: 0.85rem;
        padding: 0.3rem 0 0.8rem 0;
        color: rgba(255, 255, 255, 0.95) !important;
        visibility: visible !important;
        opacity: 1 !important;
      }
      
      .card-body {
        padding: 1.5rem !important;
      }
    }
    
    /* PERBAIKAN 10: Adjustments untuk layar short */
    @media (max-height: 600px) {
      .header {
        padding: 0.8rem 0 0.3rem 0 !important;
      }
      
      .card-body {
        padding: 1.2rem !important;
      }
      
      .logo-header {
        max-width: 50px;
        margin-bottom: 0.3rem;
      }
      
      .welcome-title {
        font-size: 1.2rem;
        margin-bottom: 0.2rem;
      }
      
      .welcome-desc {
        font-size: 0.8rem;
      }
      
      .welcome-text-container {
        max-width: 90%;
        padding: 0.6rem 1rem;
        margin: 0 auto 0.6rem auto;
      }
      
      .container-login {
        margin-top: -1rem !important;
      }
      
      .credit-text {
        margin-top: 0.3rem;
        /* Memastikan footer selalu terlihat bahkan di layar kecil */
        min-height: 30px;
      }
      
      .credit-text p {
        font-size: 0.8rem;
        padding: 0.2rem 0 0.5rem 0;
        color: rgba(255, 255, 255, 0.95) !important;
        visibility: visible !important;
        opacity: 1 !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
      }
    }
    
    /* PERBAIKAN 11: Media query khusus untuk layar desktop pendek */
    @media (min-width: 769px) and (max-height: 700px) {
      .header {
        padding: 1rem 0 0.4rem 0 !important; /* Sangat compact */
      }
      
      .container-login {
        margin-top: -0.8rem !important; /* Minimal margin */
        padding: 0.1rem 0;
      }
      
      .welcome-text-container {
        padding: 0.5rem 0.8rem;
        margin: 0 auto 0.5rem auto;
      }
      
      .card-body {
        padding: 1.3rem !important; /* Compact card */
      }
      
      .credit-text {
        margin-top: 0.3rem;
        min-height: 25px;
        padding: 0;
      }
      
      .credit-text p {
        padding: 0.1rem 0 0.3rem 0;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.95) !important;
        visibility: visible !important;
        opacity: 1 !important;
      }
    }
    
    /* PERBAIKAN 12: Media query untuk layar desktop sangat pendek */
    @media (min-width: 769px) and (max-height: 650px) {
      .header {
        padding: 0.8rem 0 0.2rem 0 !important;
      }
      
      .logo-header {
        max-width: 55px;
        margin-bottom: 0.3rem;
      }
      
      .welcome-title {
        font-size: 1.3rem;
        margin-bottom: 0.2rem;
      }
      
      .welcome-desc {
        font-size: 0.8rem;
        line-height: 1.2;
      }
      
      .welcome-text-container {
        padding: 0.4rem 0.6rem;
        margin: 0 auto 0.4rem auto;
      }
      
      .container-login {
        margin-top: -0.5rem !important;
        padding: 0;
      }
      
      .card-body {
        padding: 1.1rem !important;
      }
      
      .credit-text {
        margin-top: 0.2rem;
        min-height: 20px;
        padding: 0;
      }
      
      .credit-text p {
        padding: 0 0 0.2rem 0;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.95) !important;
        visibility: visible !important;
        opacity: 1 !important;
      }
    }
    .alert {
      border-radius: 10px;
      border: none;
      font-size: 0.9rem;
    }
    
    /* PERBAIKAN 14: Link styling */
    .text-primary {
      color: #667eea !important;
    }
    
    .text-muted {
      color: #6c757d !important;
    }
    
    a {
      transition: all 0.2s ease;
    }
    
    a:hover {
      text-decoration: none;
    }
  </style>
</head>

<body class="bg-default">
  <div class="main-content">
    <!-- Header Section - Compact dan Tanpa Separator Kontras -->
    <div class="header">
      <div class="container">
        <div class="header-body text-center">
          <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-10">
              <img src="<?= base_url('cdn/img/icons/Logo STK.png') ?>" class="logo-header" alt="Logo STK Yakobus Merauke">
              <h1 class="text-white welcome-title">Selamat Datang!</h1>
              <div class="welcome-text-container">
                <p class="text-white welcome-desc">Sistem Informasi Manajemen Skripsi STK St. Yakobus Merauke</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Separator DIHILANGKAN untuk menghindari garis kontras -->
    </div>
    
    <!-- Login Form Section - Posisi yang Lebih Dekat dengan Header -->
    <div class="container-login">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 col-md-7 col-sm-8">
            <div class="card bg-secondary border-0 mb-0 login-card">
              <div class="card-body px-lg-4 py-lg-4">
                <div class="text-center text-muted mb-4">
                  <small>Silakan login untuk melanjutkan</small>
                </div>
                
                <!-- Flash Messages Lengkap -->
                <?php if($this->session->flashdata('error')): ?>
                  <div class="alert alert-danger text-center" role="alert">
                      <i class="fas fa-exclamation-triangle mr-2"></i>
                      <?= $this->session->flashdata('error') ?>
                  </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('success')): ?>
                  <div class="alert alert-success text-center" role="alert">
                      <i class="fas fa-check-circle mr-2"></i>
                      <?= $this->session->flashdata('success') ?>
                  </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('info')): ?>
                  <div class="alert alert-info text-center" role="alert">
                      <i class="fas fa-info-circle mr-2"></i>
                      <?= $this->session->flashdata('info') ?>
                  </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('warning')): ?>
                  <div class="alert alert-warning text-center" role="alert">
                      <i class="fas fa-exclamation-circle mr-2"></i>
                      <?= $this->session->flashdata('warning') ?>
                  </div>
                <?php endif; ?>

                <form role="form" method="post" action="<?= base_url('auth/cek_login') ?>">
                  <div class="form-group mb-3">
                    <div class="input-group input-group-merge input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                      </div>
                      <input class="form-control" placeholder="Email" type="email" name="email" required>
                    </div>
                  </div>
                  <div class="form-group mb-3">
                    <div class="input-group input-group-merge input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                      </div>
                      <input class="form-control" placeholder="Password" type="password" name="password" required>
                    </div>
                  </div>
                  <div class="form-group mb-3">
                      <div class="custom-control custom-control-alternative custom-checkbox">
                          <input class="custom-control-input" id="customCheckLogin" type="checkbox">
                          <label class="custom-control-label" for="customCheckLogin">
                              <span class="text-muted">Lihat Password</span>
                          </label>
                      </div>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block my-3">Masuk</button>
                  </div>
                </form>
                
                <!-- Link Registrasi dan Navigasi -->
                <div class="row px-3">
                  <div class="col-12">
                    <div class="text-center">
                      <p class="text-muted mb-2"><small>Belum punya akun?</small></p>
                      <a href="<?= base_url('home/registrasi') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="ni ni-circle-08 mr-1"></i>
                        Registrasi Mahasiswa Baru
                      </a>
                    </div>
                    <hr class="my-3">
                    
                    <!-- Link Lupa Password yang Menonjol -->
                    <div class="text-center mb-2">
                      <a href="<?= base_url('auth/forgot_password') ?>" class="text-primary font-weight-bold">
                        <small><i class="fas fa-key mr-1"></i>Lupa Password?</small>
                      </a>
                    </div>
                    
                    <!-- Navigasi yang sudah ada -->
                    <div class="text-center">
                      <a href="<?= base_url() ?>" class="text-muted mr-3">
                        <small><i class="ni ni-shop mr-1"></i>Beranda</small>
                      </a>
                      <a href="<?= base_url('home/cek') ?>" class="text-muted">
                        <small><i class="ni ni-zoom-split-in mr-1"></i>Cek Status</small>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- FOOTER CREDIT - JANGAN DIHAPUS: "Made with Love by SIPD" -->
    <div class="credit-text">
      <p><em>Made with Love by SIPD</em></p>
    </div>
  </div>
  
  <script src="<?= base_url() ?>cdn/vendor/jquery/dist/jquery.min.js"></script>
  <script src="<?= base_url() ?>cdn/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>cdn/js/argon.js?v=1.2.0"></script>
  <script>
    $(document).ready(function() {
        // Existing password toggle
        $('#customCheckLogin').on('click', function() {
            var passwordInput = $('input[name="password"]');
            if ($(this).prop('checked')) {
                passwordInput.attr('type', 'text');
            } else {
                passwordInput.attr('type', 'password');
            }
        });
        
        // Auto-hide alerts setelah 6 detik
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 6000);
        
        // Focus pada email field saat halaman dimuat
        $('input[name="email"]').focus();
        
        // Loading state untuk tombol submit
        $('form').on('submit', function() {
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="ni ni-notification-70 mr-2"></i>Memproses...');
        });
    });
  </script>
</body>
</html>