<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', 'Penelitian') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <div class="card-title">Data Penelitian</div>
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
            <table class="table table-hover" id="data-penelitian">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Penelitian</th>
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
<div class="modal fade" id="tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tambah">
                <div class="modal-header">
                    <div class="modal-title">Tambah Penelitian</div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul Penelitian</label>
                        <input type="text" class="form-control" name="judul_penelitian" placeholder="Masukkan Judul Penelitian">
                    </div>
                    <div class="form-group">
                        <label>Proposal</label>
                        <select name="proposal_mahasiswa_id" class="form-control">
                            <option value="">- Pilih Proposal -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pembimbing</label>
                        <select name="pembimbing_id" class="form-control">
                            <option value="">- Pilih Pembimbing -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Penguji</label>
                        <select name="penguji_id" class="form-control">
                            <option value="">- Pilih Penguji -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lembar Persetujuan</label>
                        <input type="file" class="form-control" name="pilih-bukti" accept="application/pdf">
                        <input type="hidden" name="bukti">
                    </div>
                    <div class="form-group">
                        <label>File Seminar</label>
                        <input type="file" class="form-control" name="pilih-file_seminar" accept="application/pdf">
                        <input type="hidden" name="file_seminar">
                    </div>
                    <div class="form-group">
                        <label>SK Tim</label>
                        <input type="file" class="form-control" name="pilih-sk_tim" accept="application/pdf">
                        <input type="hidden" name="sk_tim">
                    </div>
                    <div class="form-group">
                        <label>Bukti Konsultasi</label>
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

        call('api/seminar', {
            mahasiswa_id: '<?= $this->session->userdata('id') ?>'
        }).done(function(req) {
            proposal = `<option value="">- Pilih Proposal -</option>`;
            if (req.data) {
                req.data.forEach(obj => {
                    if (obj.hasil_seminar_status == '1' || obj.hasil_seminar_status == '2') {
                        proposal += `<option value="` + obj.proposal_mahasiswa_id + `">` + obj.proposal_mahasiswa_judul + `</option>`;
                    } else {
                        proposal = '<option value="">- Belum Tersedia -</option>';
                    }
                })
            }
            $('[name=proposal_mahasiswa_id]').html(proposal);
        })

        call('api/dosen').done(function(res) {
            dosen = `<option value="">- Pilih Dosen -</option>`;
            if (res.data) {
                res.data.forEach(obj => {
                    dosen += `<option value="` + obj.id + `">` + obj.nama + `</option>`;
                })
            }
            $('[name=pembimbing_id]').html(dosen);
            $('[name=penguji_id]').html(dosen);
        })

        show = () => {
            $('#data-penelitian').DataTable().destroy();
            $('#data-penelitian').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + "api/penelitian",
                    "method": "POST",
                    "data": {
                        mahasiswa_id: "<?= $this->session->userdata('id') ?>"
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
                        data: "judul_penelitian"
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
                            return '<a href="' + base_url + 'cdn/vendor/penelitian/file_seminar/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "sk_tim",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/penelitian/sk_tim/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "bukti_konsultasi",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/penelitian/bukti_konsultasi/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return `
    					<div class="text-center">
    						<a href="` + base_url + `mahasiswa/penelitian/detail/` + data.id + `" class="btn btn-sm btn-success">
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

        $(document).on('submit', 'form#tambah', function(e) {
            e.preventDefault();
            call('api/penelitian/create', $(this).serialize()).done(function(res) {
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

        $(document).on('change', '[name=pilih-bukti]', function() {
            read('[name=pilih-bukti]', function(data) {
                $('[name=bukti]').val(data.result);
            })
        })

        $(document).on('change', '[name=pilih-file_seminar]', function() {
            read('[name=pilih-file_seminar]', function(data) {
                $('[name=file_seminar]').val(data.result);
            })
        })

        $(document).on('change', '[name=pilih-sk_tim]', function() {
            read('[name=pilih-sk_tim]', function(data) {
                $('[name=sk_tim]').val(data.result);
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
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>