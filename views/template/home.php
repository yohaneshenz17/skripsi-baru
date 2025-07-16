<!DOCTYPE html>
<html>
<?php $app = json_decode(file_get_contents(base_url('cdn/db/app.json'))); ?>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistem Informasi Skripsi STK St. Yakobus Merauke">
  <meta name="author" content="STK St. Yakobus">
  <title><?= $app->nama ?> - <?= $title ?></title>
  <?php include('_main/css.php') ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
  <style>
    /* Modern styling improvements */
    .navbar-modern {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95) !important;
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .btn-modern {
      border-radius: 25px;
      padding: 8px 20px;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.875rem;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-modern:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .btn-outline-modern {
      background: transparent;
      border: 2px solid #5e72e4;
      color: #5e72e4;
    }
    
    .btn-outline-modern:hover {
      background: #5e72e4;
      color: white;
    }
    
    .btn-solid-modern {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
    }
    
    .btn-solid-modern:hover {
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
      color: white;
    }
    
    .navbar-brand img {
      height: 40px;
      width: auto;
    }
    
    .main-content {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .content-wrapper {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    /* Mobile spacing improvements */
    @media (max-width: 768px) {
      .navbar-nav .nav-item {
        margin: 5px 0;
      }
      
      .btn-mobile-stack {
        display: block;
        width: 100%;
        margin: 8px 0;
      }
      
      .navbar-collapse {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
      }
      
      .content-wrapper {
        margin: 10px;
        border-radius: 15px;
      }
    }
    
    /* Responsive text */
    @media (max-width: 576px) {
      .btn-modern {
        padding: 10px 15px;
        font-size: 0.8rem;
      }
      
      .navbar-brand img {
        height: 35px;
      }
    }
    
    /* Animation for content */
    .fade-in {
      animation: fadeIn 0.6s ease-in;
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>
  <!-- Modern Navbar -->
  <nav id="navbar-main" class="navbar navbar-expand-lg navbar-modern fixed-top">
    <div class="container">
      <a class="navbar-brand" href="<?= base_url() ?>">
        <img src="<?= base_url() ?>cdn/img/icons/<?= $app->icon ? $app->icon : 'default.png' ?>" alt="STK St. Yakobus">
      </a>
      
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse" aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="navbar-collapse collapse" id="navbar-collapse">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a href="<?= base_url() ?>" class="nav-link <?= (strtolower($title) == 'home') ? "font-weight-bold text-primary" : "text-dark" ?>">
              <span class="nav-link-inner--text">Home</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url() ?>home/registrasi" class="nav-link <?= (strtolower($title) == 'registrasi') ? "font-weight-bold text-primary" : "text-dark" ?>">
              <span class="nav-link-inner--text">Registrasi</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= base_url() ?>home/cek" class="nav-link <?= (strtolower($title) == 'cek') ? "font-weight-bold text-primary" : "text-dark" ?>">
              <span class="nav-link-inner--text">Cek Status</span>
            </a>
          </li>
        </ul>
        
        <!-- Modern buttons with better spacing -->
        <ul class="navbar-nav ml-auto">
          <li class="nav-item d-lg-none">
            <a href="<?= base_url() ?>home/registrasi" class="btn btn-outline-modern btn-mobile-stack">
              <i class="fas fa-user-plus mr-2"></i>Registrasi
            </a>
          </li>
          <li class="nav-item d-lg-none">
            <a href="<?= base_url() ?>auth/login" class="btn btn-solid-modern btn-mobile-stack">
              <i class="fas fa-sign-in-alt mr-2"></i>Login
            </a>
          </li>
          
          <!-- Desktop buttons -->
          <li class="nav-item d-none d-lg-block mr-2">
            <a href="<?= base_url() ?>home/registrasi" class="btn btn-outline-modern btn-modern">
              <i class="fas fa-user-plus mr-2"></i>Registrasi
            </a>
          </li>
          <li class="nav-item d-none d-lg-block">
            <a href="<?= base_url() ?>auth/login" class="btn btn-solid-modern btn-modern">
              <i class="fas fa-sign-in-alt mr-2"></i>Login
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main content -->
  <div class="main-content">
    <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
      <div class="content-wrapper fade-in">
        <?= $content ?>
      </div>
    </div>
  </div>

  <!-- Modern Footer -->
  <footer class="py-4" style="background: rgba(0, 0, 0, 0.05);">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="text-center text-md-left text-muted">
            &copy; <?= date('Y') ?> <a href="https://stkyakobus.ac.id" class="font-weight-bold text-primary">STK St. Yakobus Merauke</a>
          </div>
        </div>
        <div class="col-md-6">
          <div class="text-center text-md-right">
            <a href="https://stkyakobus.ac.id" class="text-muted mr-3">Website</a>
            <a href="mailto:sipd@stkyakobus.ac.id" class="text-muted">Support</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <?php include('_main/js.php') ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  
  <script>
    // Modern navbar scroll effect
    $(window).scroll(function() {
      if ($(window).scrollTop() > 50) {
        $('.navbar-modern').addClass('shadow-lg').css('background', 'rgba(255, 255, 255, 0.98)');
      } else {
        $('.navbar-modern').removeClass('shadow-lg').css('background', 'rgba(255, 255, 255, 0.95)');
      }
    });
    
    // Smooth animations
    $(document).ready(function() {
      $('.fade-in').addClass('animated');
    });
  </script>
  
  <?= isset($script) ? $script : '' ?>
</body>
</html>