<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Fakultas') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="card-title">Data Fakultas</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover" id="data-fakultas">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama</th>
						<th>Dekan Fakultas</th>
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
			$('#data-fakultas').DataTable().destroy();
			$('#data-fakultas').DataTable({
				"deferRender" : true,
				"ajax" : {
					"url" : base_url + "api/fakultas",
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
						data : "nama"
					},
					{
						data : "dekan"
					}
				]
			})
		}

		show();

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>
