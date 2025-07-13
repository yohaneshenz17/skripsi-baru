<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Pengaturan') ?>

<?php $this->app->section() ?>
<form id="edit">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-header">
					<div class="card-title">Icon Aplikasi</div>
				</div>
				<div class="card-body">
					<div class="form-group">
						<img src="<?= base_url() ?>cdn/img/mahasiswa/default.png" style="max-height: 100%; max-width: 100%; width: 100%" class="icongambar">
					</div>
					<div class="form-group">
						<input type="file" class="form-control" name="pilih-icon">
						<input type="hidden" name="icon">
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-header">
					<div class="card-title">Pengaturan Aplikasi</div>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label>Nama</label>
						<input type="text" class="form-control" placeholder="Masukkan Nama" name="nama">
					</div>
					<div class="form-group">
						<label>Nama</label>
						<input type="text" class="form-control" placeholder="Masukkan Instansi" name="instansi">
					</div>
					<div class="form-group">
						<label>Informasi</label>
						<textarea name="informasi" rows="7" class="form-control summernote" placeholder="Masukkan Informasi"></textarea>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary">Simpan</button>
				</div>
			</div>
		</div>
	</div>
</form>
<?php foreach ($dataEmail as $de) { ?>
	<form action="<?= base_url('atur-send-email'); ?>" method="POST">
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<div class="head-title">Send Email Setting</div>
					</div>
					<div class="card-body">
						<input type="hidden" name="id" value="<?= $de->id; ?>">
						<div class="form-group">
							<label for="smtp_host">SMTP Host</label>
							<input type="text" autocomplete="off" name="smtp_host" id="smtp_host" class="form-control" required value="<?= $de->smtp_host; ?>">
						</div>
						<div class="form-group">
							<label for="smtp_port">SMTP Port</label>
							<input type="text" autocomplete="off" name="smtp_port" id="smtp_port" class="form-control" required value="<?= $de->smtp_port; ?>">
						</div>
						<div class="form-group">
							<label for="account_gmail">Akun Mail</label>
							<input type="email" autocomplete="off" name="smtp_user" id="account_gmail" class="form-control" required value="<?= $de->email; ?>">
						</div>
						<div class="form-group">
							<label for="pass_gmail">Password Mail</label>
							<input type="password" autocomplete="off" name="smtp_password" id="pass_gmail" class="form-control" required value="<?= $de->password; ?>">
						</div>
						<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php } ?>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/summernote/summernote-lite.min.css">
<script src="<?= base_url() ?>cdn/plugins/summernote/summernote-lite.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/binaryajax.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/zepto.min.js"></script>
<script>
	$(document).ready(function() {

		$('.summernote').summernote({
			height: 200
		})

		function show() {
			call('api/pengaturan').done(function(res) {
				if (res.error == true) {
					notif(res.message, 'warning').then(function() {
						window.location = window.location.href;
					})
				} else {
					$('[name=nama]').val(res.data.nama);
					$('[name=instansi]').val(res.data.instansi);
					$('[name=informasi]').val(res.data.informasi);
					$('.note-editable').html(res.data.informasi);
					$('img.icongambar').attr('src', base_url + 'cdn/img/icons/' + (res.data.icon ? res.data.icon : 'default.png'));
				}
			})
		}

		show();

		$(document).on('change', '[name=pilih-icon]', function() {
			canvasResize(this.files[0], {
				width: 500,
				height: 500,
				crop: true,
				quality: 200,
				rotate: 0,
				callback: function(data) {
					$('[name=icon]').val(data);
					$('img.icongambar').attr('src', data);
				}
			})
		})

		$(document).on('submit', 'form#edit', function(e) {
			e.preventDefault();
			const data = {
				nama: $('[name=nama]').val(),
				instansi: $('[name=instansi]').val(),
				icon: $('[name=icon]').val(),
				informasi: $('.note-editable').html()
			};
			call('api/pengaturan/edit', data).done(function(res) {
				if (res.error == true) {
					notif(res.message, 'error', true);
				} else {
					notif(res.message, 'success').then(function() {
						window.location = window.location.href;
					})
				}
			})
		})

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>