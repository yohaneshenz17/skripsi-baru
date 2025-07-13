<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Mahasiswa') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">
            Detail Mahasiswa
        </div>
    </div>
    <div class="card-body">

        <div>
            <span class="text-danger">*</span> Harus diisi
        </div>
        <form id="edit" style="margin-top: 10px;" onsubmit="loadingBtn()">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>NIM <span class="text-danger">*</span></label>
                        <input type="text" name="nim" autocomplete="off" autofocus="true" class="form-control" placeholder="Masukkan NIM">
                    </div>
                    <div class="form-group">
                        <label>Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" autocomplete="off" class="form-control" placeholder="Masukkan Nama">
                    </div>
                    <div class="form-group">
                        <label>Prodi <span class="text-danger">*</span></label>
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
                        <input type="text" name="tempat_lahir" autocomplete="off" class="form-control" placeholder="Masukkan Tempat Lahir">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="text" name="email" autocomplete="off" class="form-control" placeholder="Masukkan Email">
                    </div>
                    <div class="form-group">
                        <label>Alamat <span class="text-danger">*</span></label>
                        <textarea name="alamat" placeholder="Masukkan Alamat" rows="5" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon" autocomplete="off" class="form-control" placeholder="Masukkan Nomor Telepon">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nomor Telepon Orang Dekat <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon_orang_dekat" autocomplete="off" class="form-control" placeholder="Masukkan Nomor Telepon Orang Dekat">
                    </div>
                    <div class="form-group">
                        <label>IPK <span class="text-danger">*</span></label>
                        <input type="text" name="ipk" autocomplete="off" class="form-control" placeholder="Masukkan IPK">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <input type="hidden" name="def_status">
                        <select name="status" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Foto Mahasiswa</label>
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
                <button class="btn btn-default" type="reset">Reset</button>
                <button type="submit" class="btn btn-primary btn-act">Submit</button>
            </div>
        </form>
    </div>
    <div class="card-footer"></div>
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
    const mahasiswa_id = '<?= $mahasiswa_id ?>'
    $(document).ready(function() {

        loadProdi()

        function show() {
            call('api/mahasiswa/detail/' + mahasiswa_id).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error').then(function() {
                        window.location = base_url + 'admin/mahasiswa';
                    })
                } else {
                    mahasiswa = req.data;
                    $('form#edit [name=nim]').val(mahasiswa.nim);
                    $('form#edit [name=nama]').val(mahasiswa.nama);
                    $('form#edit [name=prodi_id]').val(mahasiswa.prodi_id);
                    console.log(mahasiswa);
                    $('form#edit [name=jenis_kelamin]').val(mahasiswa.jenis_kelamin);
                    $('form#edit [name=tempat_lahir]').val(mahasiswa.tempat_lahir);
                    $('form#edit [name=tanggal_lahir]').val(mahasiswa.tanggal_lahir);
                    $('form#edit [name=email]').val(mahasiswa.email);
                    $('form#edit [name=alamat]').val(mahasiswa.alamat);
                    $('form#edit [name=nomor_telepon]').val(mahasiswa.nomor_telepon);
                    $('form#edit [name=nomor_telepon_orang_dekat]').val(mahasiswa.nomor_telepon_orang_dekat);
                    $('form#edit [name=ipk]').val(mahasiswa.ipk);
                    $('form#edit [name=def_status]').val(mahasiswa.status);
                    $('form#edit [name=status]').val(mahasiswa.status);
                    $('form#edit img.foto').attr('src', base_url + '/cdn/img/mahasiswa/' + ((mahasiswa.foto) ? mahasiswa.foto : 'default.png'))
                }
            })
        }

        show()

        $(document).on('change', '.pilih-foto [type=file]', function(e) {
            canvasResize(this.files[0], {
                height: 500,
                width: 500,
                crop: true,
                rotate: 0,
                quality: 200,
                callback: function(data) {
                    $('img.foto').attr('src', data);
                    $('[name=foto]').val(data);
                }
            })
        })

        $(document).on('submit', 'form#edit', function(e) {
            e.preventDefault();
            call('api/mahasiswa/update/' + mahasiswa_id, $(this).serialize()).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                    $(".btn-act").attr('disabled', false).html('Submit')
                } else {
                    notif(req.message, 'success');
                    loadProdi()
                    show();
                    $(".btn-act").attr('disabled', false).html('Submit')
                }
            })
        })

    })

    function loadingBtn() {
        $(".btn-act").attr('disabled', true).html('Loading...')
    }

    function loadProdi() {
        call('api/prodi').done(function(req) {
            prodi = '<option value="">- Pilih Prodi -</option>';
            if (req.data) {
                req.data.forEach((obj) => {
                    prodi += '<option value="' + obj.id + '">' + obj.nama + '</option>'
                })
            }
            $('[name=prodi_id]').html(prodi);
        })
    }
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>