<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Profil') ?>

<?php $this->app->section() ?>
<div class="card">
	<form id="edit">
		<div class="card-header">
			<div class="card-title">Profil</div>
		</div>
		<div class="card-body">
			<div class="form-group">
				<label>NIP</label><input type="text" class="form-control" name="nip" placeholder="Masukkan NIP" autocomplete="off">
			</div>
			<div class="form-group">
				<label>Nama</label><input type="text" class="form-control" name="nama" placeholder="Masukkan Nama" autocomplete="off">
			</div>
			<div class="form-group">
				<label>Nomor Telepon</label><input type="text" class="form-control" name="nomor_telepon" placeholder="Masukkan Nomor Telepon" autocomplete="off">
			</div>
			<div class="form-group">
				<label>Email</label><input type="email" class="form-control" name="email" placeholder="Masukkan Email" autocomplete="off">
			</div>
		</div>
		<div class="card-footer text-right">
			<button type="submit" class="btn btn-primary">Simpan</button>
		</div>
	</form>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
	$(document).ready(function() {

		var id = '<?= $this->session->userdata('id') ?>'
		
		function show() {
			call('api/dosen/details/' + id).done(function(res) {
				if (res.error == true) {
					notif(res.message, 'warning').then(function() {
						window.location = base_url + 'auth/logout';
					})
				} else {
					$('[name=nip]').val(res.data.nip);
					$('[name=nama]').val(res.data.nama);
					$('[name=nomor_telepon]').val(res.data.nomor_telepon);
					$('[name=email]').val(res.data.email);
				}
			})
		}

		show();

		$(document).on('submit', 'form#edit', function(e) {
			e.preventDefault();
			call('api/dosen/update/' + id, $(this).serialize()).done(function(req) {
				if (req.error == true) {
					notif(req.message, 'error', true);
				} else {
					notif(req.message, 'success');
					$('form#edit [name]').val('');
					show();
				}
			})
		})

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>