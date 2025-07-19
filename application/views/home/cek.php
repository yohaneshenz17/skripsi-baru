<?php $this->app->extend('template/home') ?>

<?php $this->app->setVar('title', "Cek") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-body">
        <form id="cek">
            <div class="input-group">
                <input type="text" name="nim" class="form-control" autocomplete="off" placeholder="Masukkan NIM Mahasiswa">
                <div class="input-group-append">
                    <button class="input-group-btn btn btn-primary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <div class="card-title">Detail Mahasiswa</div>
    </div>
    <div class="card-body">
        <div class="row" id="data-mahasiswa">
            <div class="col-12 text-center">
                Silahkan Mencari Mahasiswa
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="authentikasi">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="authentikasi">
                <div class="modal-header">
                    <div class="modal-title">Konfirmasi Mahasiswa</div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" class="mahasiswa_id">
                        <input type="password" class="form-control" name="password" placeholder="Masukkan Password" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {

        function show(mahasiswa_id = null) {
            call('api/mahasiswa/detail/' + mahasiswa_id).done(function(req) {
                if (req.data) {
                    mahasiswa = req.data;
                    $('input.mahasiswa_id').val(mahasiswa.id);
                    $('#data-mahasiswa').html('\
                    <div class="col-lg-12">\
                        <div class="row">\
                            <div class="col-md-6">\
                                <div class="row p-2">\
                                    <div class="col-6">NIM</div>\
                                    <div class="col-6"><strong>' + mahasiswa.nim + '</strong></div>\
                                </div>\
                                <div class="row p-2">\
                                    <div class="col-6">Nama</div>\
                                    <div class="col-6"><strong>' + mahasiswa.nama + '</strong></div>\
                                </div>\
                                <div class="row p-2">\
                                    <div class="col-6">Prodi</div>\
                                    <div class="col-6"><strong>' + mahasiswa.prodi.nama + '</strong></div>\
                                </div>\
                                <div class="row p-2">\
                                    <div class="col-6">Fakultas</div>\
                                    <div class="col-6"><strong>' + mahasiswa.prodi.fakultas.nama + '</strong></div>\
                                </div>\
                            </div>\
                        </div>\
                        <hr>\
                        <div class="text-right">\
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#authentikasi">\
                                Masuk\
                            </button>\
                        </div>\
                    </div>\
                    ');
                }
            })
        }

        $(document).on('submit', 'form#cek', function(e) {
            e.preventDefault();
            call('api/mahasiswa/search', $(this).serialize()).done(function(req) {
                if (req.data) {
                    show(req.data.id);
                    notif(req.message, 'success');
                } else {
                    notif(req.message, 'info', true);
                    $('#data-mahasiswa').html('<div class="col-12 text-center">' + req.message + '</div>');
                }
            })
        })

        $(document).on('submit', 'form#authentikasi', function(e) {
            e.preventDefault();
            const id = $('form#authentikasi .mahasiswa_id').val();
            call('api/mahasiswa/verifikasi/' + id, $(this).serialize()).done(function(res) {
                if (res.error == true) {
                    notif(res.message, 'error', true);
                } else {
                    $('form#authentikasi [name]').val('');
                    $('div#authentikasi').modal('hide');
                    notif(res.message, 'success').then(function() {
                        window.location = base_url + 'auth/cek/' + res.data.id + '/3';
                    })
                }
            })
        });

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>