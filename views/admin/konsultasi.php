<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Konsultasi') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Data Konsultasi</div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-konsultasi">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Proposal</th>
                        <th>Isi</th>
                        <th>Waktu</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Detail Konsultasi</div>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tr>
                        <td>Laporan Kaprodi</td>
                        <th class="persetujuan_kaprodi"></th>
                    </tr>
                    <tr>
                        <td>Laporan Pembimbing</td>
                        <th class="persetujuan_pembimbing"></th>
                    </tr>
                    <tr>
                        <td>SK Tim</td>
                        <th class="sk_tim">
                            <span class="badge badge-danger">Belum Tersedia</span>
                        </th>
                    </tr>
                </table>
                <hr>
                <form id="upload-sktim">
                    <div class="text-center">
                        <label for="">Upload SK Tim</label>
                    </div>
                    <input type="hidden" class="id">
                    <input type="hidden" name="sktim">
                    <div class="input-group">
                        <input type="file" accept="application/pdf" class="form-control pilih-sktim">
                        <div class="input-group-append">
                            <button type="submit" class="input-group-btn btn btn-primary">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="hapus">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hapus">
                <div class="modal-header">
                    <div class="modal-title">Hapus Konsultasi</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <p>Anda yakin menghapus konsultasi terpilih ?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Hapus</button>
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

        function show() {
            $('#data-konsultasi').DataTable().destroy();
            $('#data-konsultasi').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + 'api/konsultasi',
                    "method": "POST",
                    "dataSrc": "data"
                },
                "columns": [
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "mahasiswa_nama"
                    },
                    {
                        data: "proposal_mahasiswa_judul"
                    },
                    {
                        data: "isi"
                    },
                    {
                        data: null,
                        render: function(data) {
                            return data.tanggal + ' ' + data.jam
                        }
                    },
                    {
                        data: "bukti",
                        render: function(data) {
                            return '\
                            <a href="' + base_url + 'cdn/vendor/bukti/' + data + '" target="_blank">' + data + '</a>\
                            '
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            hapus = `
                            <button class="btn btn-hapus btn-danger btn-sm" data-id="`+data.id+`" type="button" data-toggle="modal" data-target="#hapus">
                                <i class="fa fa-trash"></i>
                            </button>
                            `;
                            return '\
                            <div class="text-center">\
                                <button class="btn btn-sm btn-info btn-detail" data-id="'+data.id+'" data-persetujuan_kaprodi="' + data.persetujuan_kaprodi + '" data-persetujuan_pembimbing="' + data.persetujuan_pembimbing + '" data-komentar_kaprodi="' + data.komentar_kaprodi + '" data-komentar_pembimbing="' + data.komentar_pembimbing + '" data-sk_tim="' + data.sk_tim + '" type="button" data-toggle="modal" data-target="#detail">\
                                    <i class="fa fa-pen"></i>\
                                </button>\
                                ' + hapus + '\
                            </div>\
                            '
                        }
                    }
                ],
                "language" : {
                    "zeroRecords" : "data tidak tersedia"
                }
            });
        }

        show();

        $(document).on('click', 'button.btn-detail', function() {
            persetujuan_kaprodi = ($(this).data('persetujuan_kaprodi') == '1') ? '<span class="badge badge-success">Disetujui</span>' : $(this).data('komentar_kaprodi');
            persetujuan_pembimbing = ($(this).data('persetujuan_pembimbing') == '1') ? '<span class="badge badge-success">Disetujui</span>' : $(this).data('komentar_pembimbing');
            sk_tim = (!$(this).data('sk_tim')) ? '<span class="badge badge-danger">Belum Tersedia</span>' : '<a class="btn btn-primary btn-sm" href="' + base_url + 'cdn/vendor/sk_tim/' + $(this).data('sk_tim') + '">' + $(this).data('sk_tim') + '</a>'
            $('form#upload-sktim .id').val($(this).data('id'));
            $('th.persetujuan_kaprodi').html(persetujuan_kaprodi);
            $('th.persetujuan_pembimbing').html(persetujuan_pembimbing);
            $('th.sk_tim').html(sk_tim);
        })

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
        })

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault();
            const id = $('form#hapus .id').val();
            call('api/konsultasi/destroy/'+id).done(function (req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    show();
                    $('div#hapus').modal('hide');
                }
            })
        })

        $(document).on('change', 'input.pilih-sktim', function() {
            read('input.pilih-sktim', function(data) {
                $('form#upload-sktim [name=sktim]').val(data.result);
            })
        })

        $(document).on('submit', 'form#upload-sktim', function(e) {
            e.preventDefault();
            const id = $('form#upload-sktim .id').val();
            call('api/konsultasi/uploadsktim/'+id, $(this).serialize()).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    $('div#detail').modal('hide');
                    show();
                }
            })
        })

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>