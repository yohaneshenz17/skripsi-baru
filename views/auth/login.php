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
    /* Custom styles for better layout */
    body, html {
      height: 100vh;
      overflow: hidden;
    }
    
    /* Allow scroll on mobile devices */
    @media (max-width: 768px) {
      body, html {
        overflow-y: auto;
        min-height: 100vh;
      }
    }
    
    .main-content {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    /* Mobile adjustments */
    @media (max-width: 768px) {
      .main-content {
        min-height: 100vh;
        height: auto;
      }
    }
    
    .header {
      /* Mengurangi padding header */
      padding: 1rem 0 !important;
      flex-shrink: 0;
    }
    
    .header-body {
      margin-bottom: 1.2rem !important;
    }
    
    .container-login {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 0;
      margin-top: -7rem; 
    }
    
    .logo-header {
      max-width: 80px; 
      margin-bottom: 1rem;
    }
    
    .welcome-title {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }
    
    .welcome-desc {
      font-size: 0.9rem;
      margin-bottom: 0;
    }
    
    .login-card {
      max-width: 400px;
      width: 100%;
      margin: 0 auto;
    }
    
    .credit-text {
      text-align: center;
      padding: 0;
      margin-top: -1.5rem;
      position: relative;
      z-index: 10;
    }
    
    .credit-text p {
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.00rem;
      margin: 0;
      padding: 0.5rem 0 1rem 0;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .container-login {
        margin-top: -3rem;
        padding: 1rem 0;
      }
      
      .credit-text {
        margin-top: -1rem;
        padding: 0;
      }
      
      .credit-text p {
        font-size: 1.0rem;
        padding: 0.3rem 0 2rem 0;
      }
    }
    
    @media (max-height: 700px) {
      .header {
        padding: 1rem 0 !important;
      }
      
      .logo-header {
        max-width: 80px;
      }
      
      .welcome-title {
        font-size: 1.5rem;
        font-weight: 500;
      }
      
      .welcome-desc {
        font-size: 1.0rem;
        font-weight: 500;
      }
      
      .credit-text {
        margin-top: -2rem;
      }
      
      .credit-text p {
        padding: 0.2rem 0 0.5rem 0;
        font-size: 1.0rem;
      }
    }
    
    @media (max-height: 600px) {
      .header-body {
        margin-bottom: 0.5rem !important;
      }
      
      .card-body {
        padding: 1.5rem !important;
      }
      
      .credit-text {
        margin-top: -2.5rem;
      }
      
      .credit-text p {
        font-size: 0.9rem;
        padding: 0.1rem 0 0.3rem 0;
      }
    }
    
    /* Untuk layar yang sangat kecil */
    @media (max-height: 500px) {
      .credit-text {
        margin-top: -3rem;
      }
      
      .credit-text p {
        font-size: 0.9rem;
        padding: 0;
      }
    }
  </style>
</head>

<body class="bg-default">
  <div class="main-content">
    <!-- Header Section - Lebih Compact -->
    <div class="header bg-gradient-primary">
      <div class="container">
        <div class="header-body text-center">
          <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-10">
              <img src="<?= base_url('cdn/img/icons/Logo STK.png') ?>" class="logo-header" alt="Logo STK Yakobus Merauke">
              <h1 class="text-white welcome-title">Selamat Datang!</h1>
              <p class="text-lead text-white welcome-desc">Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus Merauke</p>
            </div>
          </div>
        </div>
      </div>
      <div class="separator separator-bottom separator-skew zindex-100">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <polygon class="fill-default" points="2560 0 2560 100 0 100"></polygon>
        </svg>
      </div>
    </div>
    
    <!-- Login Form Section -->
    <div class="container-login">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 col-md-7 col-sm-8">
            <div class="card bg-secondary border-0 mb-0 login-card">
              <div class="card-body px-lg-4 py-lg-4">
                <div class="text-center text-muted mb-3">
                  <small>Silakan login untuk melanjutkan</small>
                </div>
                
                <?php if($this->session->flashdata('error')): ?>
                  <div class="alert alert-danger text-center" role="alert">
                      <?= $this->session->flashdata('error') ?>
                  </div>
                <?php endif; ?>
                <?php if($this->session->flashdata('success')): ?>
                  <div class="alert alert-success text-center" role="alert">
                      <?= $this->session->flashdata('success') ?>
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
                
                <div class="text-center mt-3">
                    <a href="<?= base_url() ?>" class="text-muted"><small>Kembali ke Halaman Utama</small></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Credit Text -->
    <div class="credit-text">
      <p><em>Made with love by SIPD STK</em></p>
    </div>
  </div>
  
  <script src="<?= base_url() ?>cdn/vendor/jquery/dist/jquery.min.js"></script>
  <script src="<?= base_url() ?>cdn/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>cdn/js/argon.js?v=1.2.0"></script>
  <script>
    $(document).ready(function() {
        $('#customCheckLogin').on('click', function() {
            var passwordInput = $('input[name="password"]');
            if ($(this).prop('checked')) {
                passwordInput.attr('type', 'text');
            } else {
                passwordInput.attr('type', 'password');
            }
        });
    });
  </script>
</body>
</html>