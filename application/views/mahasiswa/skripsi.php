<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', 'Seminar Akhir') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <div class="card-title">Seminar Akhir / Skripsi</div>
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
            <table class="table table-hover" id="data-skripsi">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Status</th>
                        <th>Judul Skripsi</th>
                        <th>Dosen Pembimbing</th>
                        <th>Dosen Penguji</th>
                        <th>Jadwal Skripsi</th>
                        <th>Persetujuan</th>
                        <th>File Skripsi</th>
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
                        <label>Judul Skripsi</label>
                        <input type="text" class="form-control" name="judul_skripsi" placeholder="Masukkan Judul Skripsi">
                    </div>
                    <div class="form-group">
                        <label>Pembimbing</label>
                        <select name="dosen_id" class="form-control">
                            <option value="">- Pilih Pembimbing -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Penguji</label>
                        <select name="dosen_penguji_id" class="form-control">
                            <option value="">- Pilih Penguji -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jadwal Skripsi</label>
                        <input name="jadwal_skripsi" type="text" class="form-control dateTime" placeholder="Pilih Jadwal Skripsi" readonly>
                    </div>
                    <div class="form-group">
                        <label>Persetujuan</label>
                        <input type="file" class="form-control" name="pilih-persetujuan" accept="application/pdf">
                        <input type="hidden" name="persetujuan">
                        <input type="hidden" name="def_persetujuan">
                    </div>
                    <div class="form-group">
                        <label>File Skripsi</label>
                        <input type="file" class="form-control" name="pilih-file_skripsi" accept="application/pdf">
                        <input type="hidden" name="file_skripsi">
                        <input type="hidden" name="def_file_skripsi">
                    </div>
                    <div class="form-group">
                        <label>SK Tim</label>
                        <input type="file" class="form-control" name="pilih-sk_tim" accept="application/pdf">
                        <input type="hidden" name="sk_tim">
                        <input type="hidden" name="def_sk_tim">
                    </div>
                    <div class="form-group">
                        <label>Bukti Konsultasi</label>
                        <input type="file" class="form-control" name="pilih-bukti_konsultasi" accept="application/pdf">
                        <input type="hidden" name="bukti_konsultasi">
                        <input type="hidden" name="def_bukti_konsultasi">
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
<div class="modal fade" id="tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tambah">
                <div class="modal-header">
                    <div class="modal-title">Tambah Skripsi</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="mahasiswa_id" value="<?= $this->session->userdata('id'); ?>">
                    <div class="form-group">
                        <label>Judul Skripsi</label>
                        <input type="text" class="form-control" name="judul_skripsi" placeholder="Masukkan Judul Skripsi">
                    </div>
                    <div class="form-group">
                        <label>Pembimbing</label>
                        <select name="dosen_id" class="form-control">
                            <option value="">- Pilih Pembimbing -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Penguji</label>
                        <select name="dosen_penguji_id" class="form-control">
                            <option value="">- Pilih Penguji -</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jadwal Skripsi</label>
                        <input name="jadwal_skripsi" type="text" class="form-control dateTime" placeholder="Pilih Jadwal Skripsi" readonly>
                    </div>
                    <div class="form-group">
                        <label>Persetujuan</label>
                        <input type="file" class="form-control" name="pilih-persetujuan" accept="application/pdf">
                        <input type="hidden" name="persetujuan">
                    </div>
                    <div class="form-group">
                        <label>File Skripsi</label>
                        <input type="file" class="form-control" name="pilih-file_skripsi" accept="application/pdf">
                        <input type="hidden" name="file_skripsi">
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
        call('api/dosen').done(function(res) {
            dosen = `<option value="">- Pilih Dosen -</option>`;
            if (res.data) {
                res.data.forEach(obj => {
                    dosen += `<option value="` + obj.id + `">` + obj.nama + `</option>`;
                })
            }
            $('[name=dosen_id]').html(dosen);
            $('[name=dosen_penguji_id]').html(dosen);
        })

        show = () => {
            $('#data-skripsi').DataTable().destroy();
            $('#data-skripsi').DataTable({
                "deferRender": true,
                "ajax": {
                    "url": base_url + "api/skripsi",
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
                        data: "judul_skripsi"
                    },
                    {
                        data: "nama_pembimbing"
                    },
                    {
                        data: "nama_penguji"
                    },
                    {
                        data: "jadwal_skripsi"
                    },
                    {
                        data: "persetujuan",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/skripsi/persetujuan/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "file_skripsi",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/skripsi/file_skripsi/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "sk_tim",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/skripsi/sk_tim/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: "bukti_konsultasi",
                        render: function(data) {
                            return '<a href="' + base_url + 'cdn/vendor/skripsi/bukti_konsultasi/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            return '<div class="text-center">\
                            <button class="btn btn-sm btn-info btn-edit" type="button" data-toggle="modal" data-target="#edit" data-id="' + data.id + '" data-mahasiswa_id="' + data.mahasiswa_id + '" data-judul_skripsi="' + data.judul_skripsi + '" data-jadwal_skripsi="' + data.jadwal_skripsi + '" data-dosen_id="' + data.dosen_id + '" data-dosen_penguji_id="' + data.dosen_penguji_id + '" data-file_skripsi="' + data.file_skripsi + '" data-sk_tim="' + data.sk_tim + '" data-persetujuan="' + data.persetujuan + '" data-bukti_konsultasi="' + data.bukti_konsultasi + '">\
                                <i class="fa fa-pen"></i>\
                            </button>\
    						<button class="btn btn-danger btn-sm btn-hapus" type="button" data-toggle="modal" data-target="#hapus" data-id="' + data.id + '">\
    							<i class="fa fa-trash"></i>\
    						</button>\
    					</div>'
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
            call('api/skripsi/create', $(this).serialize()).done(function(res) {
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

        $(document).on('change', 'form#tambah [name=pilih-file_skripsi]', function() {
            read('form#tambah [name=pilih-file_skripsi]', function(data) {
                $('form#tambah [name=file_skripsi]').val(data.result);
            })
        })

        $(document).on('change', 'form#tambah [name=pilih-persetujuan]', function() {
            read('form#tambah [name=pilih-persetujuan]', function(data) {
                $('form#tambah [name=persetujuan]').val(data.result);
            })
        })

        $(document).on('change', 'form#tambah [name=pilih-bukti_konsultasi]', function() {
            read('form#tambah [name=pilih-bukti_konsultasi]', function(data) {
                $('form#tambah [name=bukti_konsultasi]').val(data.result);
            })
        })

        $(document).on('change', 'form#tambah [name=pilih-sk_tim]', function() {
            read('form#tambah [name=pilih-sk_tim]', function(data) {
                $('form#tambah [name=sk_tim]').val(data.result);
            })
        })

        $(document).on('change', 'form#edit [name=pilih-file_skripsi]', function() {
            read('form#edit [name=pilih-file_skripsi]', function(data) {
                $('form#edit [name=file_skripsi]').val(data.result);
            })
        })

        $(document).on('change', 'form#edit [name=pilih-sk_tim]', function() {
            read('form#edit [name=pilih-sk_tim]', function(data) {
                $('form#edit [name=sk_tim]').val(data.result);
            })
        })

        $(document).on('change', 'form#edit [name=pilih-persetujuan]', function() {
            read('form#edit [name=pilih-persetujuan]', function(data) {
                $('form#edit [name=persetujuan]').val(data.result);
            })
        })

        $(document).on('change', 'form#edit [name=pilih-bukti_konsultasi]', function() {
            read('form#edit [name=pilih-bukti_konsultasi]', function(data) {
                $('form#edit [name=bukti_konsultasi]').val(data.result);
            })
        })

        $(document).on('click', 'button.btn-hapus', function() {
            $('form#hapus .id').val($(this).data('id'));
        })

        $(document).on('submit', 'form#hapus', function(e) {
            e.preventDefault();
            const id = $('form#hapus .id').val();
            call('api/skripsi/destroy/' + id).done(function(res) {
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

    $(document).on('click', 'button.btn-edit', function() {
        $('form#edit .id').val($(this).data('id'));
        $('form#edit [name=mahasiswa_id]').val($(this).data('mahasiswa_id'));
        $('form#edit [name=judul_skripsi]').val($(this).data('judul_skripsi'));
        $('form#edit [name=dosen_id]').val($(this).data('dosen_id'));
        $('form#edit [name=dosen_penguji_id]').val($(this).data('dosen_penguji_id'));
        $('form#edit [name=jadwal_skripsi]').val($(this).data('jadwal_skripsi'));
        $('form#edit [name=def_file_skripsi]').val($(this).data('file_skripsi'));
        $('form#edit [name=def_sk_tim]').val($(this).data('sk_tim'));
        $('form#edit [name=def_persetujuan]').val($(this).data('persetujuan'));
        $('form#edit [name=def_bukti_konsultasi]').val($(this).data('bukti_konsultasi'));
    })

    $(document).on('submit', 'form#edit', function(e) {
        e.preventDefault();
        var id = $('form#edit .id').val();
        call('api/skripsi/update/' + id, $(this).serialize()).done(function(req) {
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
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>