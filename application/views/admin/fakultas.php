<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Fakultas') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="row">
			<div class="col-6">
				<div class="card-title">Data Fakultas</div>
			</div>
			<div class="col-6 text-right">
				<button class="btn btn-primary" type="button" data-toggle="modal" data-target="#tambah">
					<i class="fa fa-plus"></i>
					Tambah
				</button>
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover" id="data-fakultas">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama</th>
						<th>Dekan Fakultas</th>
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
					<div class="modal-title">Tambah Fakultas</div>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Nama</label><input type="text" class="form-control" name="nama" placeholder="Masukkan Nama" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Dekan Fakultas</label><input type="text" class="form-control" name="dekan" placeholder="Masukkan Dekan" autocomplete="off">
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
					<div class="modal-title">Edit Fakultas</div>
				</div>
				<div class="modal-body">
					<input type="hidden" class="id">
					<div class="form-group">
						<label>Nama</label><input type="text" class="form-control" name="nama" placeholder="Masukkan Nama" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Dekan Fakultas</label><input type="text" class="form-control" name="dekan" placeholder="Masukkan Dekan" autocomplete="off">
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
					<div class="modal-title">Hapus Fakultas</div>
				</div>
				<div class="modal-body">
					<input type="hidden" class="id">
					<p>Anda yakin menghapus fakultas <strong class="nama">Nama Posen</strong> ?</p>
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" typpe="button" data-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-danger">Hapus</button>
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
					},
					{
						data : null,
						render: function(data) {
							if (level == '1') {
								return `
								<div class="text-center">
									<button 
										class="btn btn-edit btn-info btn-sm" 
										type="button" 
										data-toggle="modal" 
										data-target="#edit"
										data-id="`+data.id+`"
										data-nama="`+data.nama+`"
										data-dekan="`+data.dekan+`"
									>
										<i class="fa fa-pen"></i>
									</button>
									<button 
										class="btn btn-hapus btn-danger btn-sm" 
										type="button" 
										data-toggle="modal" 
										data-target="#hapus"
										data-id="`+data.id+`"
										data-nama="`+data.nama+`"
									>
										<i class="fa fa-trash"></i>
									</button>
								</div>
								`
							} else {
								return '-';
							}
						}
					}
				]
			})
		}

		show();

		$(document).on('submit', 'form#tambah', function(e) {
			e.preventDefault();
			call('api/fakultas/create', $(this).serialize()).done(function(req) {
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
			$('form#edit [name=nama]').val($(this).data('nama'));
			$('form#edit [name=dekan]').val($(this).data('dekan'));
		})

		$(document).on('submit', 'form#edit', function(e) {
			e.preventDefault();
			const id = $('form#edit .id').val();
			call('api/fakultas/update/'+id, $(this).serialize()).done(function(req) {
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
			$('form#hapus .nama').html($(this).data('nama'));
		})

		$(document).on('submit', 'form#hapus', function(e) {
			e.preventDefault();
			const id = $('form#hapus .id').val();
			call('api/fakultas/destroy/'+id).done(function(req) {
				if (req.error == true) {
					notif(req.message, 'error', true);
				} else {
					notif(req.message, 'success');
					$('div#hapus').modal('hide');
					show();
				}
			})
		})

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>
