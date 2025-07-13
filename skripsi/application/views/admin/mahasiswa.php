<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', "Mahasiswa") ?>

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
        <div class="card-title">Data Mahasiswa</div>
        <div class="card-tools mt-2">
            <span class="badge badge-success"><i class="fa fa-check"></i> Aktif</span>
            <span class="badge badge-danger ml-3"><i class="fa fa-times"></i> Tidak Aktif</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-mahasiswa">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prodi</th>
                        <th>
                            <center>Verifikasi</center>
                        </th>
                        <th>
                            <center>Usulan Proposal</center>
                        </th>
                        <th>
                            <center>Seminar Proposal</center>
                        </th>
                        <th>
                            <center>Hasil Penelitian</center>
                        </th>
                        <th>
                            <center>HK3</center>
                        </th>
                        <th>
                            <center>Skripsi</center>
                        </th>
                        <th>
                            <center>Aksi</center>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    $(document).ready(function() {
        getDataSelect();
        show();
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

    function show() {
        $('#data-mahasiswa').DataTable().destroy();
        $('#data-mahasiswa').DataTable({
            "deferRender": true,
            "ajax": {
                "url": base_url + 'api/mahasiswa',
                'method': "POST",
                'dataSrc': "data"
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
                    data: "nama"
                },
                {
                    data: "prodi",
                    render: function(prodi) {
                        return prodi.nama;
                    }
                },
                {
                    data: "status",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: "usulan_proposal",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: "seminar_proposal",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: "hasil_penelitian",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: "hk3",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: "skripsi",
                    render: function(data) {
                        if (data == '1') {
                            return '<center><span class="badge badge-success"><i class="fa fa-check"></i></span></center>';
                        } else {
                            return '<center><span class="badge badge-danger"><i class="fa fa-times"></i></span></center>';
                        }
                    }
                },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div class="text-center">
                                <a href="` + base_url + `admin/mahasiswa/detail/` + data.id + `" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="hapusData(` + data.id + `)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                            `;
                    }
                }
            ]
        });
    }

    function hapusData(id) {
        swal({
                title: "Anda yakin?",
                text: "Setelah menghapus, anda tidak bisa mengembalikan data yang telah terhapus",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: base_url + 'api/mahasiswa/destroy/' + id,
                        type: 'post',
                        success: function() {
                            swal('Sukses Menghapus data', {
                                icon: 'success'
                            })
                            show()
                        }
                    })
                } else {
                    swal("Batal Menghapus");
                }
            });
    }
</script>
<?php $this->app->endSection('script') ?>
<?php $this->app->init() ?>