<?php $this->app->extend('template/home') ?>

<?php $this->app->setVar('title', "Registrasi") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Registrasi Akun Mahasiswa</div>
    </div>
    <div class="card-body">
        <span class="text-danger">*</span> Harus Diisi
        <form id="registrasi" style="margin-top: 10px;" onsubmit="loadingBtn()">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
                        <input id="nim" type="text" name="nim" autocomplete="off" autofocus="true" class="form-control" placeholder="Masukkan NIM Sesuai SIAKAD" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" autocomplete="off" class="form-control" placeholder="Masukkan Nama Lengkap Sesuai SIAKAD">
                    </div>
                    <div class="form-group">
                        <label>Program Studi <span class="text-danger">*</span></label>
                        <select name="prodi_id" class="form-control">
                            <option value="">- Pilih Prodi -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="">- Pilih Jenis Kelamin -</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_lahir" autocomplete="off" class="form-control" placeholder="Masukkan Tempat Lahir sesuai SIAKAD">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tanggal_lahir" id="tanggal_lahir" class="form-control" placeholder="dd/mm/yyyy" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="text" name="email" autocomplete="off" class="form-control" placeholder="Masukkan Email Aktif">
                    </div>
                    <div class="form-group">
                        <label>Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="alamat" placeholder="Masukkan Alamat Tinggal Saat Ini" rows="5" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon" autocomplete="off" class="form-control" placeholder="Masukkan Nomor Whatsapp Aktif">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nomor HP Orang Dekat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon_orang_dekat" autocomplete="off" class="form-control" placeholder="Masukkan Nomor HP Orang Dekat">
                    </div>
                    <div class="form-group">
                        <label>Indeks Prestasi Kumulatif <span class="text-danger">*</span></label>
                        <input type="text" name="ipk" autocomplete="off" class="form-control" placeholder="Masukkan IPK Terakhir">
                    </div>
                    <div class="form-group">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="password" autocomplete="off" placeholder="Masukkan Password (Mohon Diingat Baik)">
                    </div>
                    <div class="form-group">
                        <label>Password (Konfirmasi) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="password_konfirmasi" autocomplete="off" placeholder="Masukkan Password Sama dengan Di Atas">
                    </div>
                    <div class="form-group">
                        <label>Foto Mahasiswa (JPG Maks. 1 MB)</label>
                        <div class="custom-file pilih-foto">
                            <input type="file" accept="image/*" class="custom-file-input">
                            <label class="custom-file-label"></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="card shadow p-3 text-center" style="height: 300px">
                            <input type="hidden" name="foto">
                            <img src="<?= base_url() ?>cdn/img/mahasiswa/default.png" class="foto foto-fluid">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-right">
                <button class="btn btn-warning" type="reset">Reset</button>
                <button type="submit" class="btn btn-default btn-act">Submit</button>
            </div>
        </form>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/binaryajax.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/zepto.min.js"></script>
<script>
    function loadingBtn() {
        $(".btn-act").attr('disabled', true).html('Loading...')
    }

    $(document).ready(function() {
        $("#nim").keyup(function() {
            var nim_input = $(this).val();

            // Cek apakah input hanya berisi angka (atau kosong)
             if (/^\d*$/.test(nim_input)) {

                 // Cek apakah panjangnya antara 7 dan 10 digit
                  if (nim_input.length >= 7 && nim_input.length <= 10) {
                     $(".btn-act").attr('disabled', false);
                 } else {
                    // Jika panjangnya di luar rentang (tapi tidak kosong), tampilkan notifikasi
                    if (nim_input.length > 0) {
                    notif('NIM harus terdiri dari 7 hingga 10 digit angka', 'info', true);
                }
                $(".btn-act").attr('disabled', true);
             }

         } else {
            // Jika input berisi selain angka, tampilkan notifikasi error
            notif('NIM hanya boleh berisi angka', 'error', true);
            $(".btn-act").attr('disabled', true);
        }
    });
        
            // KODE BARU UNTUK DATEPICKER
            $('#tanggal_lahir').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true,
                todayHighlight: true
    });
        
        call('api/prodi').done(function(req) {
            prodi = '<option value="">- Pilih Prodi -</option>';
            if (req.data) {
                $.each(req.data, function(index, obj) {
                    prodi += '<option value="' + obj.id + '">' + obj.nama + '</option>'
                })
            }
            $('[name=prodi_id]').html(prodi);
        })

        $(document).on('submit', 'form#registrasi', function(e) {
            e.preventDefault();
            if ($('form#registrasi [name=password]').val() == $('form#registrasi [name=password_konfirmasi]').val()) {
                call('api/mahasiswa/create', $(this).serialize()).done(function(req) {
                    if (req.error == true) {
                        notif(req.message, 'error', true);
                        $(".btn-act").attr('disabled', false).html('Submit')
                    } else {
                        $('form#registrasi [name]').val('');
                        $('img.foto').attr('src', '');
                        notif(req.message, 'success');
                        $(".btn-act").attr('disabled', false).html('Submit')
                    }
                })
            } else {
                notif('konfirmasi password harus sama', 'error', true);
                $(".btn-act").attr('disabled', false).html('Submit')
            }
        })

                $(document).on('change', '.pilih-foto [type=file]', function(e) {
            const file = this.files[0];
            const self = this;
        
            if (!file) {
                return;
            }
        
            // Validasi Tipe File (hanya jpg/jpeg)
            if (file.type !== 'image/jpeg' && file.type !== 'image/jpg') {
                notif('Tipe file harus JPG atau JPEG', 'error', true);
                $(self).val(null);
                return;
            }
        
            // Validasi Ukuran File (maksimal 1MB)
            const maxSizeInBytes = 1024 * 1024; // 1MB
            if (file.size > maxSizeInBytes) {
                notif('Ukuran file tidak boleh melebihi 1 MB', 'error', true);
                $(self).val(null);
                return;
            }
        
            // Jika lolos, langsung proses dan potong gambar menjadi 500x500
            canvasResize(file, {
                height: 500,
                width: 500,
                crop: true, // Opsi ini akan memotong gambar menjadi persegi
                rotate: 0,
                quality: 200,
                callback: function(data) {
                    $('img.foto').attr('src', data);
                    $('[name=foto]').val(data);
                }
            });
        });

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>