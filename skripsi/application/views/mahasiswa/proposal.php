<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', "Proposal") ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <div class="card-title">Data Proposal</div>
            </div>
            <div class="col text-right">
                <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#tambah">
                    <i class="fa fa-plus"></i>
                    Tambah
                </button>
            </div>
        </div>
        <div class="card-tools mt-2">
            <span class="badge badge-success"><i class="fa fa-check"></i> Disetujui</span>
            <span class="badge badge-danger ml-3"><i class="fa fa-times"></i> Belum/Tidak Disetujui</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-proposal">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Ringkasan</th>
                        <th>Pembimbing</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tambah" action="javascript:;">
                <div class="modal-header">
                    <div class="modal-title">Tambah Proposal</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="mahasiswa_id" value="<?= $this->session->userdata('id') ?>">
                    <div class="form-group">
                        <label>Judul</label>
                        <input name="judul" placeholder="Masukkan Judul" autocomplete="off" type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Dosen Pembimbing 1</label>
                        <select name="dosen_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dosen Pembimbing 2</label>
                        <select name="dosen2_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dosen Penguji</label>
                        <select name="dosen_penguji_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ringkasan</label>
                        <textarea name="ringkasan" rows="5" class="form-control" placeholder="Masukkan Ringkasan"></textarea>
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
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit">
                <div class="modal-header">
                    <div class="modal-title">Edit Proposal</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <input type="hidden" name="mahasiswa_id" value="<?= $this->session->userdata('id') ?>">
                    <div class="form-group">
                        <label>Judul</label>
                        <input name="judul" placeholder="Masukkan Judul" autocomplete="off" type="text" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Dosen Pembimbing 1</label>
                        <select name="dosen_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dosen Pembimbing 2</label>
                        <select name="dosen2_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dosen Penguji</label>
                        <select name="dosen_penguji_id" class="form-control">
                            <option value="">- Pilih Dosen -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ringkasan</label>
                        <textarea name="ringkasan" rows="5" class="form-control" placeholder="Masukkan Ringkasan"></textarea>
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
                    <div class="modal-title">Hapus Proposal</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="id">
                    <p>Anda yakin menghapus proposal <strong class="judul">Judul Proposal</strong> ?</p>
                    <li>Konsultasi proposal juga akan terhapus</li>
                    <li>Seminar proposal juga akan terhapus</li>
                    <li>Penelitian proposal juga akan terhapus</li>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="buat-konsultasi">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="buat-konsultasi">
                <div class="modal-header">
                    <div class="modal-title">Buat Konsultasi</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="proposal_mahasiswa_id">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input name="tanggal" type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jam</label>
                                <input name="jam" type="time" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Isi</label>
                        <textarea name="isi" rows="5" class="form-control" placeholder="Masukkan Isi"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Bukti</label>
                        <div class="custom-file pilih-bukti">
                            <label class="custom-file-label">Pilih File</label>
                            <input type="file" class="custom-file-input">
                        </div>
                    </div>
                    <input type="hidden" name="bukti">
                    <input type="hidden" name="bukti_file">
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
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {

        function show() {
            $('#data-proposal').DataTable().destroy();
            $('#data-proposal').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + 'api/proposal_mahasiswa',
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
                        data: "judul"
                    },
                    {
                        data: "ringkasan"
                    },
                    {
                        data: null,
                        render: function(data) {
                            return '1. ' + data.pembimbing.nama + ' <br>2. ' + data.pembimbing2.nama
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            if (data.status == '1') {
                                status = '\
                            <span class="badge badge-success mr-2"><i class="fa fa-check"></i></span>';
                            } else {
                                status = '\
                            <span class="badge badge-danger"><i class="fa fa-times"></i></span>\
                            ';
                            }
                            return '\
                            <div class="text-center">' + status + '</div>\
                            ';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return '\
                            <div class="text-center">\
                            <button class="btn btn-sm btn-info btn-edit" type="button" data-toggle="modal" data-target="#edit" data-id="' + data.id + '" data-mahasiswa_id="' + data.mahasiswa_id + '" data-judul="' + data.judul + '" data-ringkasan="' + data.ringkasan + '" data-dosen_id="' + data.dosen_id + '" data-dosen2_id="' + data.dosen2_id + '" data-dosen_penguji_id="' + data.dosen_penguji_id + '">\
                                <i class="fa fa-pen"></i>\
                            </button>\
                            <button class="btn btn-sm btn-danger btn-hapus" type="button" data-toggle="modal" data-target="#hapus" data-id="' + data.id + '" data-judul="' + data.judul + '">\
                                <i class="fa fa-trash"></i>\
                            </button>\
                            </div>\
                            ';
                        }
                    }
                ],
                "language": {
                    "zeroRecords": "data tidak tersedia"
                }
            });
        }

        show();

        call('api/dosen').done(function(req) {
            dosen = '<option value="">- Pilih Dosen -</option>';
            if (req.data) {
                $.each(req.data, function(index, obj) {
                    dosen += '<option value="' + obj.id + '">' + obj.nama + '</option>';
                })
            }
            $('[name=dosen_id]').html(dosen);
            $('[name=dosen2_id]').html(dosen);
            $('[name=dosen_penguji_id]').html(dosen);
        })

        $(document).on('submit', 'form#tambah', function(e) {
            // e.preventDefault();
            call('api/proposal_mahasiswa/create', $(this).serialize()).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    $('form#tambah [name]').val('');
                    $('div#tambah').modal('hide');
                    show();
                }
            })
        })

        $(document).on('click', 'button.btn-edit', function() {
            $('form#edit .id').val($(this).data('id'));
            $('form#edit [name=mahasiswa_id]').val($(this).data('mahasiswa_id'));
            $('form#edit [name=judul]').val($(this).data('judul'));
            $('form#edit [name=dosen_id]').val($(this).data('dosen_id'));
            $('form#edit [name=dosen2_id]').val($(this).data('dosen2_id'));
            $('form#edit [name=dosen_penguji_id]').val($(this).data('dosen_penguji_id'));
            $('form#edit [name=ringkasan]').val($(this).data('ringkasan'));
        })

        $(document).on('submit', 'form#edit', function(e) {
            e.preventDefault();
            var id = $('form#edit .id').val();
            call('api/proposal_mahasiswa/update/' + id, $(this).serialize()).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    $('form#edit [name]').val('');
                    $('div#edit').modal('hide');
                    show();
                }
            })
        })

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
            $('form#hapus .judul').html($(this).data('judul'));
        })

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault();
            var id = $('form#hapus .id').val();
            call('api/proposal_mahasiswa/destroy/' + id).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    $('form#hapus [name]').val('');
                    $('div#hapus').modal('hide');
                    show();
                }
            })
        })

        $(document).on('click', 'button.buat-konsultasi', function() {
            $('form#buat-konsultasi [name=proposal_mahasiswa_id]').val($(this).data('proposal_mahasiswa_id'));
        })

        $(document).on('change', '.pilih-bukti', function() {
            read('.pilih-bukti [type=file]', function(data) {
                $('form#buat-konsultasi [name=bukti]').val(data.result);
                $('form#buat-konsultasi [name=bukti_file]').val(data.file);
            })
        })

        $(document).on('submit', 'form#buat-konsultasi', function(e) {
            e.preventDefault();
            call('api/konsultasi/create', $(this).serialize()).done(function(req) {
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    $('form#buat-konsultasi [name]').val('');
                    $('div#buat-konsultasi').modal('hide');
                    show();
                }
            })
        })

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>