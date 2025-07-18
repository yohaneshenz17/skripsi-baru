<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Prodi') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="row">
			<div class="col-6">
				<div class="card-title">Data Prodi</div>
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
			<table class="table table-hover" id="data-prodi">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode</th>
						<th>Nama</th>
						<th>Fakultas</th>
						<th>Ketua Prodi</th>
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
					<div class="modal-title">Tambah Prodi</div>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Kode</label><input type="text" class="form-control" name="kode" placeholder="Masukkan Kode" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Nama</label><input type="text" class="form-control" name="nama" placeholder="Masukkan Nama" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Fakultas</label>
						<select name="fakultas_id" class="form-control">
							<option value="">- Pilih Fakultas -</option>
						</select>
					</div>
					<div class="form-group">
						<label>Ketua Prodi</label>
						<select name="dosen_id" class="form-control">
							<option value="">- Pilih Dosen -</option>
						</select>
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
					<div class="modal-title">Edit Prodi</div>
				</div>
				<div class="modal-body">
					<input type="hidden" class="id">
					<div class="form-group">
						<label>Kode</label><input type="text" class="form-control" name="kode" placeholder="Masukkan Kode" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Nama</label><input type="text" class="form-control" name="nama" placeholder="Masukkan Nama" autocomplete="off">
					</div>
					<div class="form-group">
						<label>Fakultas</label>
						<select name="fakultas_id" class="form-control">
							<option value="">- Pilih Fakultas -</option>
						</select>
					</div>
					<div class="form-group">
						<label>Ketua Prodi</label>
						<select name="dosen_id" class="form-control">
							<option value="">- Pilih Dosen -</option>
						</select>
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
					<div class="modal-title">Hapus Prodi</div>
				</div>
				<div class="modal-body">
					<input type="hidden" class="id">
					<p>Anda yakin menghapus prodi <strong class="nama">Nama Posen</strong> ?</p>
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

		call('api/dosen').done(function(req) {
			dosen = '<option value="">- Pilih Dosen -</option>';
			if (req.data) {
				req.data.forEach(obj => {
					dosen += '<option value="'+obj.id+'">'+obj.nama+'</option>'
				})
			}
			$('[name=dosen_id]').html(dosen);
		})
		
		call('api/fakultas').done(function(req) {
			fakultas = '<option value="">- Pilih Fakultas -</option>';
			if (req.data) {
				req.data.forEach(obj => {
					fakultas += '<option value="'+obj.id+'">'+obj.nama+'</option>'
				})
			}
			$('[name=fakultas_id]').html(fakultas);
		})
		
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
										data-kode="`+data.kode+`"
										data-nama="`+data.nama+`"
										data-dosen_id="`+data.dosen_id+`"
										data-fakultas_id="`+data.fakultas_id+`"
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
			call('api/prodi/create', $(this).serialize()).done(function(req) {
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
			$('form#edit [name=kode]').val($(this).data('kode'));
			$('form#edit [name=nama]').val($(this).data('nama'));
			$('form#edit [name=dosen_id]').val($(this).data('dosen_id'));
			$('form#edit [name=fakultas_id]').val($(this).data('fakultas_id'));
		})

		$(document).on('submit', 'form#edit', function(e) {
			e.preventDefault();
			const id = $('form#edit .id').val();
			call('api/prodi/update/'+id, $(this).serialize()).done(function(req) {
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
			call('api/prodi/destroy/'+id).done(function(req) {
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
