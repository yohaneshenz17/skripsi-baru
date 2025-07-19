<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Konsultasi') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Data Konsultasi</div>
        <div class="card-tools mt-2">
            <span class="badge badge-success"><i class="fa fa-check"></i> Disetujui</span>
            <span class="badge badge-danger ml-3"><i class="fa fa-times"></i> Belum/Tidak Disetujui</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="data-konsultasi">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Proposal / Skripsi</th>
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
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="setuju">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="setuju">
                <div class="modal-header">
                    <div class="modal-title">Konfirmasi Konsultasi</div>
                </div>
                <div class="modal-body">
                    <input type="hidden" class="persetujuan">
                    <input type="hidden" class="id">
                    <input type="hidden" class="setuju">
                    <p>Sebagai <strong class="persetujuan">Pembimbing/Kaprodi</strong></p>
                    <p>Anda yakin <span class="setuju">Menyetujui</span><span class="nonsetuju">Batal Menyetujui</span> konsultasi terpilih ?</p>
                    <div class="nonsetuju">
                        Sertakan alasan :
                        <textarea name="komentar" rows="5" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
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
                            hasil = ``;
                            if (data.proposal_mahasiswa_pembimbing_id == '<?= $this->session->userdata('id') ?>' || data.proposal_mahasiswa_pembimbing2_id == '<?= $this->session->userdata('id') ?>') {
                                if (data.persetujuan_pembimbing == '1') {
                                    hasil += `
                                    <button 
                                        class="btn btn-setuju btn-sm btn-success" 
                                        type="button" 
                                        data-toggle="modal" 
                                        data-target="#setuju" 
                                        data-id="`+data.id+`"
                                        data-persetujuan="pembimbing"
                                        data-setuju="0"
                                        >
                                        <i class="fa fa-check"></i> Pembimbing
                                    </button>
                                    `
                                } else {
                                    hasil += `
                                    <button 
                                        class="btn btn-setuju btn-sm btn-danger" 
                                        type="button" 
                                        data-toggle="modal" 
                                        data-target="#setuju" 
                                        data-id="`+data.id+`"
                                        data-persetujuan="pembimbing"
                                        data-setuju="1"
                                        >
                                        <i class="fa fa-times"></i> Pembimbing
                                    </button>
                                    `
                                }
                            } else {
                                if (data.persetujuan_pembimbing == '1') {
                                    hasil += `
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Pembimbing
                                    </span>
                                    `
                                } else {
                                    hasil += `
                                    <span class="badge badge-danger">
                                        <i class="fa fa-times"></i> Pembimbing
                                    </span>
                                    `
                                }
                            }
                            if (data.prodi_kaprodi_id == '<?= $this->session->userdata('id') ?>') {
                                if (data.persetujuan_kaprodi == '1') {
                                    hasil += `
                                    <button 
                                        class="btn btn-setuju btn-sm btn-success" 
                                        type="button" 
                                        data-toggle="modal" 
                                        data-target="#setuju" 
                                        data-id="`+data.id+`"
                                        data-persetujuan="kaprodi"
                                        data-setuju="0"
                                        >
                                        <i class="fa fa-check"></i> Kaprodi
                                    </button>
                                    `
                                } else {
                                    hasil += `
                                    <button 
                                        class="btn btn-setuju btn-sm btn-danger" 
                                        type="button" 
                                        data-toggle="modal" 
                                        data-target="#setuju" 
                                        data-id="`+data.id+`"
                                        data-persetujuan="kaprodi"
                                        data-setuju="1"
                                        >
                                        <i class="fa fa-times"></i> Kaprodi
                                    </button>
                                    `
                                }
                            } else {
                                if (data.persetujuan_kaprodi == '1') {
                                    hasil += `
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Kaprodi
                                    </span>
                                    `
                                } else {
                                    hasil += `
                                    <span class="badge badge-danger">
                                        <i class="fa fa-times"></i> Kaprodi
                                    </span>
                                    `
                                }
                            }
                            return hasil;
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

        $(document).on('click', 'button.btn-setuju', function() {
            $('form#setuju .id').val($(this).data('id'));
            $('form#setuju .persetujuan').val($(this).data('persetujuan'));
            $('form#setuju .persetujuan').html($(this).data('persetujuan'));
            $('form#setuju .setuju').val($(this).data('setuju'));
            if ($(this).data('setuju') == '1') {
                $('form#setuju span.setuju').show();
                $('form#setuju .nonsetuju').hide();
            } else {
                $('form#setuju span.setuju').hide();
                $('form#setuju .nonsetuju').show();
            }
        })

        $(document).on('submit', 'form#setuju', function(e) {
            e.preventDefault();
            const id = $('form#setuju .id').val();
            const data = {
                komentar: $('form#setuju [name=komentar]').val()
            };
            if ($('form#setuju .persetujuan').val() == 'pembimbing') {
                data.pembimbing_id = '1';
            } else {
                data.kaprodi_id = '1';
            }
            if ($('form#setuju .setuju').val() == '1') {
                call('api/konsultasi/agree/' + id, data).done(function(res) {
                    if (res.error == true) {
                        notif(res.message, 'error', true);
                    } else {
                        notif(res.message, 'success');
                        $('div#setuju').modal('hide');
                        show();
                    }
                })
            } else {
                call('api/konsultasi/disagree/' + id, data).done(function(res) {
                    if (res.error == true) {
                        notif(res.message, 'error', true);
                    } else {
                        notif(res.message, 'success');
                        $('div#setuju').modal('hide');
                        show();
                    }
                })
            }
        })

    })
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>