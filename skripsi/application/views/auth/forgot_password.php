<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Lupa Password - Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus">
  <meta name="author" content="STK Santo Yakobus Merauke">
  <title>Lupa Password - SIM Tugas Akhir</title>
  <link rel="icon" href="<?= base_url() ?>cdn/img/icons/favicon.png" type="image/png">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/nucleo/css/nucleo.css" type="text/css">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">
  <link rel="stylesheet" href="<?= base_url() ?>cdn/css/argon.css?v=1.2.0" type="text/css">
  <style>
    /* MENGGUNAKAN STYLE YANG SAMA dengan login.php */
    body, html {
      height: 100vh;
      overflow: hidden;
    }
    
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
    
    @media (max-width: 768px) {
      .main-content {
        min-height: 100vh;
        height: auto;
      }
    }
    
    .header {
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
    
    /* Responsive adjustments - sama dengan login.php */
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
  </style>
</head>

<body class="bg-default">
  <div class="main-content">
    <!-- Header Section - SAMA dengan login.php -->
    <div class="header bg-gradient-primary">
      <div class="container">
        <div class="header-body text-center">
          <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-8 col-md-10">
              <img src="<?= base_url('cdn/img/icons/Logo STK.png') ?>" class="logo-header" alt="Logo STK Yakobus Merauke">
              <h1 class="text-white welcome-title">Lupa Password?</h1>
              <p class="text-lead text-white welcome-desc">Masukkan email Anda untuk mendapatkan informasi login</p>
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
    
    <!-- Form Section -->
    <div class="container-login">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 col-md-7 col-sm-8">
            <div class="card bg-secondary border-0 mb-0 login-card">
              <div class="card-body px-lg-4 py-lg-4">
                <div class="text-center text-muted mb-3">
                  <small><i class="fas fa-key mr-2"></i>Kirim informasi login ke email Anda</small>
                </div>
                
                <!-- Flash Messages - SAMA dengan login.php -->
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

                <form role="form" method="post" action="<?= base_url('auth/forgot_password') ?>">
                  <div class="form-group mb-3">
                    <div class="input-group input-group-merge input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                      </div>
                      <input class="form-control" placeholder="Masukkan email Anda" type="email" name="email" required>
                    </div>
                    <small class="form-text text-muted mt-2">
                      <i class="fas fa-info-circle mr-1"></i>
                      Kami akan mengirim informasi username dan password ke email yang terdaftar
                    </small>
                  </div>
                  
                  <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-block my-3">
                      <i class="fas fa-paper-plane mr-2"></i>Kirim Informasi Login
                    </button>
                  </div>
                </form>
                
                <!-- Navigasi - SAMA dengan login.php -->
                <div class="row px-3">
                  <div class="col-12">
                    <hr class="my-3">
                    <div class="text-center">
                      <a href="<?= base_url('auth/login') ?>" class="text-muted mr-3">
                        <small><i class="fas fa-arrow-left mr-1"></i>Kembali ke Login</small>
                      </a>
                      <a href="<?= base_url() ?>" class="text-muted">
                        <small><i class="ni ni-shop mr-1"></i>Beranda</small>
                      </a>
                    </div>
                  </div>
                </div>
                
                <!-- Info Penting -->
                <div class="row px-3 mt-3">
                  <div class="col-12">
                    <div class="alert alert-light" role="alert" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                      <h6 class="alert-heading text-primary mb-2">
                        <i class="fas fa-lightbulb mr-2"></i>Informasi Penting:
                      </h6>
                      <ul class="text-muted mb-0" style="font-size: 13px;">
                        <li><strong>Dosen/Admin/Kaprodi:</strong> Password adalah NIDN</li>
                        <li><strong>Mahasiswa:</strong> Password sesuai saat registrasi</li>
                        <li>Periksa folder <strong>spam/junk</strong> jika email tidak masuk</li>
                        <li>Hubungi admin jika masalah: sipd@stkyakobus.ac.id</li>
                      </ul>
                    </div>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Credit Text - SAMA dengan login.php -->
    <div class="credit-text">
      <p><em>Made with love by SIPD STK</em></p>
    </div>
  </div>
  
  <!-- Scripts - SAMA dengan login.php -->
  <script src="<?= base_url() ?>cdn/vendor/jquery/dist/jquery.min.js"></script>
  <script src="<?= base_url() ?>cdn/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url() ?>cdn/js/argon.js?v=1.2.0"></script>
  
  <script>
    $(document).ready(function() {
        // Auto-hide alerts - SAMA dengan login.php
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 6000);
        
        // Focus pada email field
        $('input[name="email"]').focus();
        
        // Loading state untuk tombol submit
        $('form').on('submit', function() {
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim Email...');
        });
    });
  </script>
</body>
</html>