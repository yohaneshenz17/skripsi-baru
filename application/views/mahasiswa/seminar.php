<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', 'Seminar') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <div class="card-title">Data Seminar</div>
            </div>
            <div class="col text-right">
                <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#tambah">
                    <i class="fa fa-plus"></i>
                    Tambah
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-seminar">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Proposal</th>
                        <th>Tanggal</th>
                        <th>Tempat</th>
                        <th>Persetujuan</th>
                        <th>File Proposal</th>
                        <th>SK TIM Pembimbing & Penguji</th>
                        <th>Bukti Konsultasi</th>
                        <th>Aksi</th>
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
                    <div class="modal-title">Tambah Seminar</div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Proposal</label>
                        <select name="proposal_mahasiswa_id" class="form-control">
                            <option value="">- Pilih Proposal -</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control">
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <label>Jam</label>
                                <input type="time" name="jam" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tempat</label>
                        <textarea name="tempat" rows="3" class="form-control" placeholder="Masukkan Tempat Seminar"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Persetujuan</label>
                        <input type="file" class="form-control" name="pilih-persetujuan" accept="application/pdf">
                        <input type="hidden" name="persetujuan">
                    </div>
                    <div class="form-group">
                        <label>File Proposal</label>
                        <input type="file" class="form-control" name="pilih-file_proposal" accept="application/pdf">
                        <input type="hidden" name="file_proposal">
                    </div>
                    <div class="form-group">
                        <label>SK Tim</label>
                        <input type="file" class="form-control" name="pilih-sk_tim" accept="application/pdf">
                        <input type="hidden" name="sk_tim">
                    </div>
                    <div class="form-group">
                        <label>Bukti konsultasi</label>
                        <input type="file" class="form-control" name="pilih-bukti_konsultasi" accept="application/pdf">
                        <input type="hidden" name="bukti_konsultasi">
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
<div class="modal fade" id="hapus">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hapus">
                <div class="modal-header">
                    <div class="modal-title">Hapus Seminar</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <p>Anda yakin menghapus seminar terpilih ?</p>
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
    base_url = '<?= base_url() ?>'
    $(document).ready(function() {

        // call('api/konsultasi', {
        //     mahasiswa_id: '<?= $this->session->userdata('id') ?>'
        // }).done(function(req) {
        //     proposal = `<option value="">- Pilih Proposal -</option>`;
        //     if (req.data) {
        //         req.data.forEach(obj => {
        //             if (obj.proposal_mahasiswa_status == '1') {
        //                 proposal += `<option value="` + obj.proposal_mahasiswa_id + `">` + obj.proposal_mahasiswa_judul + `</option>`;
        //             }
        //         })
        //     }
        //     console.log(req.data)
        //     $('[name=proposal_mahasiswa_id]').html(proposal);
        // })

        $.ajax({
            url: base_url + 'getData/proposal_mahasiswa',
            type: 'post',
            data: {
                mahasiswa_id: <?= $this->session->userdata('id') ?>
            },
            dataType: 'json',
            success: function(res) {
                proposal = `<option value="">- Pilih Proposal -</option>`;
                $.each(res, function(i, item) {
                    if (item.status == '1') {
                        proposal += `<option value="` + item.id + `">` + item.judul + `</option>`;
                    }
                })
                $('[name=proposal_mahasiswa_id]').html(proposal);
            }
        })

        show = () => {
            $('#data-seminar').DataTable().destroy();
            $('#data-seminar').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + 'api/seminar',
                    "method": "POST",
                    "data": {
                        mahasiswa_id: '<?= $this->session->userdata('id') ?>'
                    },
                    "dataSrc": "data"
                },
                "columns": [{
                        data: null,
                        render: function(data, typw, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1
                        }
                    },
                    {
                        data: "proposal_mahasiswa_judul"
                    },
                    {
                        data: null,
                        render: function(data) {
                            return data.tanggal + ' ' + data.jam
                        }
                    },
                    {
                        data: "tempat"
                    },
                    {
                        data: "persetujuan",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/persetujuan/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "file_proposal",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/file_proposal/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "sk_tim",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/sk_tim/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "bukti_konsultasi",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/bukti_konsultasi/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
                        <div class="text-center">
                            <a href="` + base_url + `mahasiswa/seminar/detail/` + data.id + `" class="btn btn-sm btn-success">
                                <i class="fa fa-search"></i>
                            </a>
                            <button class="btn btn-danger btn-hapus btn-sm" type="button" data-toggle="modal" data-target="#hapus" data-id="` + data.id + `">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        `;
                        }
                    }
                ],
                "language": {
                    "zeroRecords": "data tidak tersedia"
                }
            })
        }

        show();

        $(document).on('submit', 'form#tambah', function(e) {
            e.preventDefault();
            call('api/seminar/create', $(this).serialize()).done(function(res) {
                if (res.error == true) {
                    notif(res.message, 'error', true);
                } else {
                    notif(res.message, 'success');
                    $('form#tambah [name]').val('');
                    $('div#tambah').modal('hide');
                    show();
                }
            })
        })

        $(document).on('change', '[name=pilih-file_proposal]', function() {
            read('[name=pilih-file_proposal]', function(data) {
                $('[name=file_proposal]').val(data.result);
            })
        })

        $(document).on('change', '[name=pilih-sk_tim]', function() {
            read('[name=pilih-sk_tim]', function(data) {
                $('[name=sk_tim]').val(data.result);
            })
        })

        $(document).on('change', '[name=pilih-persetujuan]', function() {
            read('[name=pilih-persetujuan]', function(data) {
                $('[name=persetujuan]').val(data.result);
            })
        })

        $(document).on('change', '[name=pilih-bukti_konsultasi]', function() {
            read('[name=pilih-bukti_konsultasi]', function(data) {
                $('[name=bukti_konsultasi]').val(data.result);
            })
        })

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
        })

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault();
            const id = $('form#hapus .id').val();
            call('api/seminar/destroy/' + id).done(function(res) {
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
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>