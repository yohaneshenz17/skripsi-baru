<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Mahasiswa') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			Detail Mahasiswa
		</div>
	</div>
	<div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <td>NIM</td>
                    <th class="nim"></th>
                </tr>
                <tr>
                    <td>Nama</td>
                    <th class="nama"></th>
                </tr>
                <tr>
                    <td>Prodi</td>
                    <th class="prodi_nama"></th>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <th class="jenis_kelamin"></th>
                </tr>
                <tr>
                    <td>Tempat / Tanggal Lahir</td>
                    <th class="tempat_tanggal_lahir"></th>
                </tr>
                <tr>
                    <td>Email</td>
                    <th class="email"></th>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <th class="alamat"></th>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <th class="nomor_telepon"></th>
                </tr>
                <tr>
                    <td>Alamat Orang Tua</td>
                    <th class="alamat_orang_tua"></th>
                </tr>
                <tr>
                    <td>Nomor Telepon Orang Tua</td>
                    <th class="nomor_telepon_orang_tua"></th>
                </tr>
                <tr>
                    <td>Nomor Telepon Orang Dekat</td>
                    <th class="nomor_telepon_orang_dekat"></th>
                </tr>
                <tr>
                    <td>IPK</td>
                    <th class="ipk"></th>
                </tr>
                <tr>
                    <td>Foto</td>
                    <th>
                        <img src="" class="foto" style="max-width: 100px;">
                    </th>
                </tr>
            </table>
        </div>
	</div>
	<div class="card-footer"></div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/jquery.exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/canvasResize.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/exif.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/binaryajax.js"></script>
<script src="<?= base_url() ?>cdn/plugins/canvas-resize/zepto.min.js"></script>
<script>
	const mahasiswa_id = '<?= $mahasiswa_id ?>'
	$(document).ready(function() {

		function show() {
			call('api/mahasiswa/detail/'+mahasiswa_id).done(function(req) {
				if (req.error == true) {
					notif(req.message, 'error').then(function() {
						window.location = base_url + 'dosen/mahasiswa';
					})
				} else {
					mahasiswa = req.data;
					$('.nim').html(mahasiswa.nim);
					$('.nama').html(mahasiswa.nama);
					$('.prodi_nama').html(mahasiswa.prodi.nama);
					$('.jenis_kelamin').html(mahasiswa.jenis_kelamin);
					$('.tempat_tanggal_lahir').html(mahasiswa.tempat_lahir + ', ' +mahasiswa.tanggal_lahir);
					$('.email').html(mahasiswa.email);
					$('.alamat').html(mahasiswa.alamat);
					$('.nomor_telepon').html(mahasiswa.nomor_telepon);
					$('.nomor_telepon_orang_dekat').html(mahasiswa.nomor_telepon_orang_dekat);
					$('.ipk').html(mahasiswa.ipk);
					$('img.foto').attr('src', base_url+'/cdn/img/mahasiswa/'+((mahasiswa.foto) ? mahasiswa.foto : 'default.png'))
				}
			})
		}

		show()

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>