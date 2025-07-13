<?php $this->app->extend('template/admin') ?>

<?php $this->app->setVar('title', 'Penelitian') ?>

<?php $this->app->section() ?>
<div class="card">
	<div class="card-header">
		<div class="card-title">Detail Penelitian</div>
	</div>
	<form id="edit">
		<div class="card-body">
			<input type="hidden" name="persetujuan">
			<input type="hidden" name="sk_tim">
			<input type="hidden" name="berita_acara">
			<input type="hidden" name="masukan">
			<div class="table-responsive">
				<table class="table table-hover">
					<tr>
						<td>Proposal</td>
						<th class="proposal_mahasiswa_judul">-</th>
					</tr>
					<tr>
						<td>Penguji</td>
						<th class="penguji_nama">-</th>
					</tr>
					<tr>
						<td>Pembimbing</td>
						<th class="pembimbing_nama">-</th>
					</tr>
					<tr>
						<td>Lembar Persetujuan</td>
						<th class="bukti">-</th>
					</tr>
					<tr>
						<td>File Seminar</td>
						<th class="file_seminar">-</th>
					</tr>
					<tr>
						<td>SK Tim</td>
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
						<th class="status">
							<select name="status" class="form-control">
								<option value="1">Lulus</option>
								<option value="2">Belum/Tidak Lulus</option>
							</select>
						</th>
					</tr>
				</table>
			</div>
		</div>
		<input type="hidden" name="email">
		<input type="hidden" name="def_status">
		<div class="card-footer text-right">
			<a href="<?= base_url() ?>admin/penelitian" class="btn btn-default">Kembali</a>
			<button type="submit" class="btn btn-primary btn-act">Simpan</button>
		</div>
	</form>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
	var penelitian_id = '<?= $penelitian_id ?>';
	$(document).ready(function() {

		function show() {
			call('api/penelitian/details/' + penelitian_id).done(function(res) {
				if (res.error == true) {
					notif(res.message, 'warning').then(function() {
						window.location = base_url + 'admin/penelitian';
					});
				} else {
					$('.proposal_mahasiswa_judul').html(res.data.proposal.judul);
					$('.penguji_nama').html(res.data.penguji.nama);
					$('.pembimbing_nama').html(res.data.pembimbing.nama);
					$('.bukti').html('<a href="' + base_url + 'cdn/vendor/penelitian/' + res.data.bukti + '">' + res.data.bukti + '</a>');
					$('.file_seminar').html((res.data.file_seminar) ? `<a href="` + base_url + `cdn/vendor/penelitian/file_seminar/` + res.data.file_seminar + `">` + res.data.file_seminar + `</a>` : '<input type="file" accept="application/pdf" class="form-control" name="pilih-sk_tim">');
					$('.sk_tim').html((res.data.sk_tim) ? `<a href="` + base_url + `cdn/vendor/penelitian/sk_tim/` + res.data.sk_tim + `">` + res.data.sk_tim + `</a>` : '<input type="file" accept="application/pdf" class="form-control" name="pilih-sk_tim">');
					$('.bukti_konsultasi').html((res.data.bukti_konsultasi) ? `<a href="` + base_url + `cdn/vendor/penelitian/bukti_konsultasi/` + res.data.bukti_konsultasi + `">` + res.data.bukti_konsultasi + `</a>` : '<input type="file" accept="application/pdf" class="form-control" name="pilih-sk_tim">');
					$('.berita_acara').html((res.data.hasil.berita_acara) ? `<a href="` + base_url + `cdn/vendor/berita_acara/` + res.data.hasil.berita_acara + `">` + res.data.hasil.berita_acara + `</a>` : '<input type="file" accept="application/pdf" class="form-control" name="pilih-berita_acara">');
					$('.masukan').html((res.data.hasil.masukan) ? `<a href="` + base_url + `cdn/vendor/masukan/` + res.data.hasil.masukan + `">` + res.data.hasil.masukan + `</a>` : '<input type="file" accept="application/pdf" class="form-control" name="pilih-masukan">');
					$('[name=status]').val(res.data.hasil.status);
					$('[name=email]').val(res.data.proposal.email);
					$('[name=def_status]').val(res.data.hasil.status);
				}
			})
		}

		show();

		$(document).on('change', '[name=pilih-sk_tim]', function() {
			read('[name=pilih-sk_tim]', function(data) {
				$('[name=sk_tim]').val(data.result);
			})
		})

		$(document).on('change', '[name=pilih-berita_acara]', function() {
			read('[name=pilih-berita_acara]', function(data) {
				$('[name=berita_acara]').val(data.result);
			})
		})

		$(document).on('change', '[name=pilih-masukan]', function() {
			read('[name=pilih-masukan]', function(data) {
				$('[name=masukan]').val(data.result);
			})
		})

		$(document).on('submit', 'form#edit', function(e) {
			e.preventDefault();
			$(".btn-act").attr('disabled', true).html('Loading...')
			const data = {
				status: $('[name=status]').val(),
				tipe: 'admin',
				berita_acara: $('[name=berita_acara]').val(),
				masukan: $('[name=masukan]').val(),
				sk_tim: $('[name=sk_tim]').val(),
				email: $('[name=email]').val(),
				def_status: $('[name=def_status]').val(),
			}
			if (data.persetujuan == 'penguji') {
				data.komentar_penguji = $('[name=komentar_penguji]').val()
			} else {
				data.komentar_pembimbing = $('[name=komentar_pembimbing]').val()
			}
			call('api/hasil_penelitian/edit/' + penelitian_id, data).done(function(res) {
				if (res.error == true) {
					notif(res.message, 'error', true);
					$(".btn-act").attr('disabled', false).html('Simpan')
				} else {
					notif(res.message, 'success');
					show();
					$(".btn-act").attr('disabled', false).html('Simpan')
				}
			})
		})

	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>