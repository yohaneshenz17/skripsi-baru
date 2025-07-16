<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', "Hasil Kegiatan") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md">
                <div class="card-title">Data Hasil Kegiatan</div>
            </div>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="hapus">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hapus">
                <div class="modal-header">
                    <div class="modal-title">Hapus Hasil Kegiatan</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <input type="hidden" class="file">
                    <input type="hidden" class="file_kegiatan">
                    <p>Anda yakin ingin menghapus ?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
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

        call('api/proposal_mahasiswa').done(function(res) {
            proposal_mahasiswa = `<option value="">- Pilih Proposal -</option>`;
            if (res.data) {
                res.data.forEach(obj => {
                    if (obj.status == '1') {
                        proposal_mahasiswa += `<option value="` + obj.id + `">` + obj.judul + `</option>`;
                    }
                })
            }
            $('[name=proposal_mahasiswa_id]').html(proposal_mahasiswa);
        })

        $(document).on('change', 'form#tambah [name=pilih-file]', function() {
            read('[name=pilih-file]', function(data) {
                $('[name=file]').val(data.result);
            })
        });

        function show() {
            $('#data-hasil-kegiatan').DataTable().destroy();
            $('#data-hasil-kegiatan').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + '/api/hasil_kegiatan',
                    "method": "POST",
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
                    {
                        data: null,
                        render: function(data) {
                            return `
                            <button class="btn btn-sm btn-danger btn-hapus" type="button" data-toggle="modal" data-target="#hapus" data-id="` + data.id + `" data-file="` + data.file + `" data-file_kegiatan="` + data.file_kegiatan + `" data-kegiatan="` + data.kegiatan + `">
                                <i class="fa fa-trash"></i>
                            </button>
                            `;
                        }
                    }
                ],
                "language": {
                    "zeroRecords": "data tidak tersedia"
                }
            });
        }

        show();

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

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
            $('form#hapus .file').val($(this).data('file'));
            $('form#hapus .file_kegiatan').val($(this).data('file_kegiatan'));
        });

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault()
            const id = $('form#hapus .id').val();
            const file = $('form#hapus .file').val();
            const file_kegiatan = $('form#hapus .file_kegiatan').val();
            // call('api/hasil_kegiatan/hapus/' + id).done(function(res) {
            //     if (res.error) {
            //         notif(res.message, 'error', true);
            //     } else {
            //         $('div#hapus').modal('hide');
            //         notif(res.message, 'success');
            //         show();
            //     }
            // })
            $.ajax({
                url: base_url + 'api/hasil_kegiatan/hapus/' + id,
                type: 'post',
                data: {
                    file: file,
                    file_kegiatan: file_kegiatan
                },
                success: function(res) {
                    $('div#hapus').modal('hide');
                    notif(res.message, 'success');
                    show();
                }
            })
        });

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>