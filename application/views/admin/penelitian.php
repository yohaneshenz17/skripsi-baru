<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Penelitian') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-body">
        <div class="card-title">Cari Mahasiswa : </div>
        <form id="form_cari" action="<?= base_url('hasil-pencarian-mahasiswa'); ?>" method="POST" onsubmit="disableBtn()">
            <input type="hidden" name="level" value="Admin">
            <select class="select2" name="id" required id="wadah_select2"> </select>
            <button class="btn btn-primary mt-3 btn-act" type="sumbit">Lihat Selengkapnya <i class="fa fa-chevron-right"></i></button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <div class="card-title">Data Penelitian</div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-penelitian">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Mahasiswa</th>
                        <th>Nama Prodi</th>
                        <th>Proposal</th>
                        <th>Bukti Persetujuan</th>
                        <th>File Seminar</th>
                        <th>SK Tim</th>
                        <th>Bukti Konsultasi</th>
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
                    <div class="modal-title">Hapus Penelitian</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <p>Anda yakin menghapus penelitian terpilih ?</p>
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
        getDataSelect()
        show = () => {
            $('#data-penelitian').DataTable().destroy();
            $('#data-penelitian').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + "api/penelitian",
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
                        data: "proposal_mahasiswa_judul"
                    },
                    {
                        data: "bukti",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/penelitian/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "file_seminar",
                        render: function(data) {
                            if (data) {
                                return '<a href="' + base_url + 'cdn/vendor/penelitian/file_seminar/' + data + '">' + data + '</a>';
                            } else {
                                return '<span class="badge badge-danger">Belum Tersedia</span>';
                            }
                        }
                    },
                    {
                        data: "sk_tim",
                        render: function(data) {
                            if (data) {
                                return '<a href="' + base_url + 'cdn/vendor/penelitian/sk_tim/' + data + '">' + data + '</a>';
                            } else {
                                return '<span class="badge badge-danger">Belum Tersedia</span>';
                            }
                        }
                    },
                    {
                        data: "bukti_konsultasi",
                        render: function(data) {
                            if (data) {
                                return '<a href="' + base_url + 'cdn/vendor/penelitian/bukti_konsultasi/' + data + '">' + data + '</a>';
                            } else {
                                return '<span class="badge badge-danger">Belum Tersedia</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                        <div class="text-center">
                            <a href="` + base_url + `admin/penelitian/detail/` + data.id + `" class="btn btn-sm btn-success">
                                <i class="fa fa-search"></i>
                            </a>
                            <button class="btn btn-danger btn-sm btn-hapus" type="button" data-toggle="modal" data-target="#hapus" data-id="` + data.id + `">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>`
                        }
                    }
                ],
                "language": {
                    "zeroRecords": "data tidak tersedia"
                }
            });
        }

        show();

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
        })

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault();
            const id = $('form#hapus .id').val();
            call('api/penelitian/destroy/' + id).done(function(res) {
                if (res.error == true) {
                    notif(res.message, 'error', true);
                } else {
                    notif(res.message, 'success');
                    $('div#hapus').modal('hide');
                    show();
                }
            })
        })

    })

    function getDataSelect() {
        $.ajax({
            url: base_url + 'getAllData/mahasiswa',
            dataType: 'json',
            type: 'get',
            success: function(res) {
                data = '<option value=""></option>'
                $.each(res, function(i, item) {
                    data += '<option value="' + item.id + '">(' + item.nim + ') ' + item.nama + '</option>'
                })
                $("#wadah_select2").html(data)
            }
        })
    }

    function disableBtn() {
        $(".btn-act").attr('disabled', true).html('Loading ...')
    }
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>