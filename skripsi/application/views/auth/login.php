<?php $this->app->extend('template/home') ?>

<?php $this->app->setVar('title', 'Login') ?>

<?php $this->app->section() ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body">
                <div class="text-center text-muted mb-4">
                    <h4>Login Sistem Informasi Skripsi</h4>
                    <p>Silahkan masukkan email dan password Anda</p>
                </div>
                <form id="login">
                    <div class="form-group mb-3">
                        <div class="input-group input-group-merge input-group-alternative">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                            </div>
                            <input class="form-control" name="email" autocomplete="off" placeholder="Email" type="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group input-group-merge input-group-alternative">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                            </div>
                            <input class="form-control" name="password" autocomplete="off" placeholder="Password" type="password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <small class="text-muted">
                        <b>Untuk Mahasiswa:</b> Gunakan password yang telah didaftarkan
                        </b>Hubungi Tim SIPD jika Anda lupa password
                        </small>
                    </div>
                    <div class="custom-control custom-control-alternative custom-checkbox">
                        <input class="custom-control-input" id="lihat-password" type="checkbox">
                        <label class="custom-control-label" for="lihat-password">
                            <span class="text-muted">Lihat Password</span>
                        </label>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary my-4">Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
    $(document).ready(function() {

        $(document).on('click', '#lihat-password', function() {
            if ($(this).prop('checked')) {
                $('[name=password]').attr('type', 'text');
            } else {
                $('[name=password]').attr('type', 'password');
            }
        })

        $(document).on('submit', 'form#login', function(e) {
            e.preventDefault();
            
            // Disable button saat proses login
            var btn = $(this).find('button[type=submit]');
            var btnText = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
            
            $.ajax({
                url: base_url + 'api/auth/login',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.error == true) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Gagal',
                            text: res.message
                        });
                        btn.prop('disabled', false).html(btnText);
                    } else {
                        $('form#login [name]').val('');
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            // Redirect berdasarkan level user
                            if(res.data.level == '1') {
                                window.location = base_url + 'admin/dashboard';
                            } else if(res.data.level == '2') {
                                window.location = base_url + 'dosen/dashboard';
                            } else if(res.data.level == '3' || res.data.type == 'mahasiswa') {
                                window.location = base_url + 'mahasiswa/dashboard';
                            } else {
                                window.location = base_url + 'auth/cek/' + res.data.id + '/' + res.data.level;
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem. Silahkan coba lagi!'
                    });
                    btn.prop('disabled', false).html(btnText);
                }
            });
        })

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>