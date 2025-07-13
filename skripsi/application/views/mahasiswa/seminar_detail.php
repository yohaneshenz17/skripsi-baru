<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', 'Seminar') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="card-title">Detail Seminar</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-hover">
				<tr>
					<td>Proposal</td>
					<th class="proposal_mahasiswa_judul">-</th>
				</tr>
				<tr>
					<td>Waktu Seminar</td>
					<th class="tanggal_jam">0000-00-00 00:00 AM</th>
				</tr>
				<tr>
					<td>Tempat</td>
					<th class="tempat">-</th>
				</tr>
				<tr>
					<td>Persetujuan</td>
					<th class="persetujuan">-</th>
				</tr>
				<tr>
					<td>File Proposal</td>
					<th class="file_proposal">-</th>
				</tr>
				<tr>
					<td>SK TIM Pembimbing & Penguji</td>
					<th class="sk_tim">-</th>
				</tr>
				<tr>
					<td>Bukti Konsultasi</td>
					<th class="bukti_konsultasi">-</th>
				</tr>
				<tr>
					<td>Berita Acara</td>
					<th class="berita_acara">-</th>
				</tr>
				<tr>
					<td>Masukan</td>
					<th class="masukan">-</th>
				</tr>
				<tr>
					<td>Status</td>
					<th class="status"></th>
				</tr>
			</table>
		</div>
	</div>
	<div class="card-footer text-right">
		<a href="<?= base_url() ?>mahasiswa/seminar" class="btn btn-default">Kembali</a>
	</div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
	var seminar_id = '<?= $seminar_id ?>'

	$(document).ready(function() {

		function show() {
			call('api/seminar/details/' + seminar_id).done(function(res) {
				if (res.error == true) {
					notif(res.message, 'warning').then(function() {
						window.location = base_url + 'mahasiswa/seminar';
					})
				} else {
					console.log(res.data)
					$('.proposal_mahasiswa_judul').html(res.data.proposal_mahasiswa_judul);
					$('.tanggal_jam').html(res.data.tanggal + ' ' + res.data.jam);
					$('.tempat').html(res.data.tempat);
					$('.persetujuan').html(`<a href="` + base_url + `cdn/vendor/persetujuan/` + res.data.persetujuan + `">` + res.data.persetujuan + `</a>`);
					$('.file_proposal').html(`<a href="` + base_url + `cdn/vendor/file_proposal/` + res.data.file_proposal + `">` + res.data.file_proposal + `</a>`);
					$('.sk_tim').html(`<a href="` + base_url + `cdn/vendor/sk_tim/` + res.data.sk_tim + `">` + res.data.sk_tim + `</a>`);
					$('.bukti_konsultasi').html(`<a href="` + base_url + `cdn/vendor/bukti_konsultasi/` + res.data.bukti_konsultasi + `">` + res.data.bukti_konsultasi + `</a>`);
					$('.berita_acara').html((res.data.hasil.berita_acara) ? `<a href="` + base_url + `cdn/vendor/berita_acara/` + res.data.hasil.berita_acara + `">` + res.data.hasil.berita_acara + `</a>` : '-');
					$('.masukan').html((res.data.hasil.masukan) ? `<a href="` + base_url + `cdn/vendor/masukan/` + res.data.hasil.masukan + `">` + res.data.hasil.masukan + `</a>` : '-');
					if (res.data.hasil.status == '1') {
						status = 'Lanjut (Sempurna)';
					} else if (res.data.hasil.status == '2') {
						status = 'Lanjut (Perbaikan)';
					} else {
						status = 'Ditolak/Belum Dinilai';
					}
					$('.status').html(status);
				}
			})
		}

		show()

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>