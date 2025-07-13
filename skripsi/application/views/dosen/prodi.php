<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Prodi') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="card-title">Data Prodi</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover" id="data-prodi">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode</th>
						<th>Nama</th>
						<th>Fakultas</th>
						<th>Ketua Prodi</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
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
			$('#data-prodi').DataTable().destroy();
			$('#data-prodi').DataTable({
				"deferRender" : true,
				"ajax" : {
					"url" : base_url + "api/prodi",
					"method" : "POST",
					"dataSrc" : "data"
				},
				"columns" : [
					{
						data : null,
						render : function(data, type, row, meta) {
							return meta.row + meta.settings._iDisplayStart + 1;
						}
					},
					{
						data : "kode"
					},
					{
						data : "nama"
					},
					{
						data : "fakultas",
						render : function(data) {
							return data.nama
						}
					},
					{
						data : "kaprodi",
						render : function(data) {
							return data.nama
						}
					}
				]
			})
		}

		show();

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>
