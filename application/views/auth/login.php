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
</head>

<body class="bg-default">
  <div class="main-content">
    <div class="header bg-gradient-primary py-5">
      <div class="container">
        <div class="header-body text-center mb-6">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 px-5">
              <img src="<?= base_url('cdn/img/icons/Logo STK.png') ?>" style="max-width: 120px; margin-bottom: 1.5rem;" alt="Logo STK Yakobus Merauke">
              <h1 class="text-white">Selamat Datang!</h1>
              <p class="text-lead text-white">Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus Merauke.</p>
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
    <div class="container mt--7 pb-5">
      <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
          <div class="card bg-secondary border-0 mb-0">
            <div class="card-body px-lg-5 py-lg-5">
              <div class="text-center text-muted mb-4">
                <small>Silakan login untuk melanjutkan</small>
              </div>
              
              <?php if($this->session->flashdata('error')): ?>
              <div class="alert alert-danger text-center" role="alert">
                  <?= $this->session->flashdata('error') ?>
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
                <div class="form-group">
                  <div class="input-group input-group-merge input-group-alternative">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                    </div>
                    <input class="form-control" placeholder="Password" type="password" name="password" required>
                  </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-control-alternative custom-checkbox">
                        <input class="custom-control-input" id="customCheckLogin" type="checkbox">
                        <label class="custom-control-label" for="customCheckLogin">
                            <span class="text-muted">Lihat Password</span>
                        </label>
                    </div>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn btn-primary btn-block my-4">Masuk</button>
                </div>
              </form>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12 text-center">
                <a href="<?= base_url() ?>" class="text-light"><small>Kembali ke Halaman Utama</small></a>
            </div>
          </div>
        </div>
      </div>
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