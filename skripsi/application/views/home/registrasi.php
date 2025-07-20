<?php $this->app->extend('template/home') ?>

<?php $this->app->setVar('title', "Registrasi") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Registrasi Akun Mahasiswa</div>
    </div>
    <div class="card-body">
        
        <!-- PERBAIKAN: Tambah Flash Message Success -->
        <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle mr-2"></i>
                <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- PERBAIKAN: Tambah Flash Message Info dan Warning -->
        <?php if($this->session->flashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa fa-info-circle mr-2"></i>
                <strong>Info!</strong> <?= $this->session->flashdata('info') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle mr-2"></i>
                <strong>Peringatan!</strong> <?= $this->session->flashdata('warning') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <span class="text-danger">*</span> Harus Diisi
        <form id="form-registrasi" method="post" action="<?= base_url('home/proses_registrasi') ?>" style="margin-top: 10px;">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>NIM <span class="text-danger">*</span></label>
                        <input type="text" name="nim" class="form-control" placeholder="Masukkan NIM" required value="<?= isset($old_input['nim']) ? htmlspecialchars($old_input['nim']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap" required value="<?= isset($old_input['nama']) ? htmlspecialchars($old_input['nama']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Program Studi <span class="text-danger">*</span></label>
                        <select name="prodi_id" class="form-control" required>
                            <option value="">- Pilih Prodi -</option>
                            <?php if(isset($prodi)) { foreach($prodi as $p): ?>
                                <option value="<?= $p->id ?>" <?= (isset($old_input['prodi_id']) && $old_input['prodi_id'] == $p->id) ? 'selected' : '' ?>><?= $p->nama ?></option>
                            <?php endforeach; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="">- Pilih Jenis Kelamin -</option>
                            <option value="laki-laki" <?= (isset($old_input['jenis_kelamin']) && $old_input['jenis_kelamin'] == 'laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="perempuan" <?= (isset($old_input['jenis_kelamin']) && $old_input['jenis_kelamin'] == 'perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-control" placeholder="Masukkan Tempat Lahir" required value="<?= isset($old_input['tempat_lahir']) ? htmlspecialchars($old_input['tempat_lahir']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tanggal_lahir" id="tanggal_lahir" class="form-control" placeholder="Pilih Tanggal" required autocomplete="off" value="<?= isset($old_input['tanggal_lahir']) ? htmlspecialchars($old_input['tanggal_lahir']) : '' ?>">
                    </div>
                     <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan Email Aktif" required value="<?= isset($old_input['email']) ? htmlspecialchars($old_input['email']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                   <div class="form-group">
                        <label>Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" placeholder="Masukkan Alamat Tinggal" rows="4" class="form-control" required><?= isset($old_input['alamat']) ? htmlspecialchars($old_input['alamat']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon" class="form-control" placeholder="Nomor Whatsapp Aktif" required value="<?= isset($old_input['nomor_telepon']) ? htmlspecialchars($old_input['nomor_telepon']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Nomor HP Orang Dekat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon_orang_dekat" class="form-control" placeholder="Nomor HP Orang Terdekat" required value="<?= isset($old_input['nomor_telepon_orang_dekat']) ? htmlspecialchars($old_input['nomor_telepon_orang_dekat']) : '' ?>">
                    </div>
                     <div class="form-group">
                        <label>IPK <span class="text-danger">*</span></label>
                        <input type="text" name="ipk" class="form-control" placeholder="Contoh: 3.50" required value="<?= isset($old_input['ipk']) ? htmlspecialchars($old_input['ipk']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Konfirmasi) <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_konfirmasi" required>
                    </div>
                </div>
            </div>
            <div class="row">
                 <div class="col-md-12">
                     <div class="form-group">
                        <label>Foto Mahasiswa (JPG/JPEG, Maks. 1 MB)</label>
                        <div class="custom-file pilih-foto">
                            <input type="file" accept="image/jpeg, image/jpg" class="custom-file-input" id="file-foto">
                            <label class="custom-file-label" for="file-foto">Pilih file...</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="card shadow-sm p-3 text-center">
                            <input type="hidden" name="foto">
                            <img src="<?= base_url() ?>cdn/img/mahasiswa/default.png" class="foto img-fluid" style="max-height: 250px; object-fit: contain;">
                        </div>
                    </div>
                 </div>
            </div>
            <hr>
            <div class="text-right">
                <button class="btn btn-warning" type="reset">Reset</button>
                <button type="submit" class="btn btn-primary btn-act">Daftar</button>
            </div>
        </form>
        
        <!-- PERBAIKAN: Tambah info sudah punya akun -->
        <hr>
        <div class="text-center">
            <p class="mb-2">Sudah punya akun?</p>
            <a href="<?= base_url('auth/login') ?>" class="btn btn-outline-primary">
                <i class="fa fa-sign-in-alt mr-2"></i> Login di sini
            </a>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/canvasResize.js"></script>
<script>
    $(document).ready(function() {
        
        // PERBAIKAN: Auto-hide alerts setelah 5 detik
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        $('#tanggal_lahir').datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom"
        });

        $('#form-registrasi').on('submit', function(e) {
            const btn = $('.btn-act');
            const noHpPribadi = $('input[name="nomor_telepon"]').val();
            const noHpDekat = $('input[name="nomor_telepon_orang_dekat"]').val();
            const password = $('input[name="password"]').val();
            const konfirmasiPassword = $('input[name="password_konfirmasi"]').val();

            if (noHpPribadi && noHpDekat && noHpPribadi === noHpDekat) {
                e.preventDefault();
                alert('Nomor HP pribadi dan Nomor HP orang dekat tidak boleh sama.');
                return;
            }

            if (password !== konfirmasiPassword) {
                e.preventDefault();
                alert('Konfirmasi password harus sama dengan password.');
                return;
            }

            // PERBAIKAN: Loading state yang lebih baik
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Mendaftar...');
        });

        $('#file-foto').on('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('img.foto').attr('src', e.target.result);
                        $('[name=foto]').val(e.target.result);
                    };
                    reader.readAsDataURL(file);
                    $('.custom-file-label').html(file.name);
                } else {
                    alert('Tipe file yang dipilih tidak valid. Harap pilih file gambar.');
                    $(this).val('');
                    $('.custom-file-label').html('Pilih file...');
                }
            }
        });
    });
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>