<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', "Hasil Kegiatan") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Data Hasil Kegiatan</div>
        <div class="col-md text-right">
            <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#tambah">
                <i class="fa fa-plus"></i>
                Tambah
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-hasil-kegiatan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>File Bukti Kegiatan</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tambah">
                <div class="modal-header">
                    <div class="modal-title">Tambah Hasil Kegiatan</div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kegiatan :</label>
                        <textarea rows="5" class="form-control" name="kegiatan" placeholder="Masukkan kegiatan"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" name="status" placeholder="Masukkan Status Anda Dalam Kegiatan" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>File Bukti Kegiatan</label>
                        <input type="file" name="pilih-file_kegiatan" class="form-control" accept="application/pdf">
                        <input type="hidden" name="file_kegiatan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {

        $(document).on('change', 'form#tambah [name=pilih-file_kegiatan]', function() {
            read('[name=pilih-file_kegiatan]', function(data) {
                $('[name=file_kegiatan]').val(data.result);
            })
        });

        $(document).on('submit', 'form#tambah', function(e) {
            e.preventDefault()
            call('api/hasil_kegiatan/tambah', $(this).serialize()).done(function(res) {
                if (res.error) {
                    notif(res.message, 'error', true);
                } else {
                    $('form#tambah [name]').val('');
                    $('div#tambah').modal('hide');
                    notif(res.message, 'success');
                    show();
                }
            })
        });

        function show() {
            $('#data-hasil-kegiatan').DataTable().destroy();
            $('#data-hasil-kegiatan').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + '/api/hasil_kegiatan',
                    "method": "POST",
                    "data": {
                        mahasiswa_id: '<?= $this->session->userdata('id') ?>'
                    },
                    "dataSrc": "data"
                },
                "columns": [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: "nim"
                    },
                    {
                        data: "nama_mahasiswa"
                    },
                    {
                        data: "nama_prodi"
                    },
                    {
                        data: "kegiatan"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: "file_kegiatan",
                        render: function(data) {
                            return '<a href="' + base_url + '/cdn/vendor/hasil_kegiatan/kegiatan/' + data + '" title="File Hasil" target="_blank" class="btn btn-success btn-sm">\
                            <i class="fa fa-download"></i> Download</a>';
                        }
                    },
                ],
                "language": {
                    "zeroRecords": "data tidak tersedia"
                }
            });
        }

        show();

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>