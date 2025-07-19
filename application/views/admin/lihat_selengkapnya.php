<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Lihat Selengkapnya') ?>

<?php $this->app->section() ?>
<div class="row">
    <div class="col-md-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-6">
                        <div class="card-title">Data Mahasiswa yang Dibimbing</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Nama Prodi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_bimbingan as $i => $value) { ?>
                                <tr>
                                    <td><?= $i + 1; ?></td>
                                    <td><?= $value->nim; ?></td>
                                    <td><?= $value->nama_mahasiswa; ?></td>
                                    <td><?= $value->nama_prodi; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-xs-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-6">
                        <div class="card-title">Data Mahasiswa yang Diuji</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover dataTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Nama Prodi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_penguji as $index => $item) { ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= $item->nim; ?></td>
                                    <td><?= $item->nama_mahasiswa; ?></td>
                                    <td><?= $item->nama_prodi; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
    $('.dataTable').dataTable();
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>