<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Dosen') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Profil Dosen</div>
    </div>
	<div class="card-body">
        <table class="table table-borderless" style="width: auto;">
            <tbody>
                <tr>
                    <td style="width: 150px;"><strong>NIDN</strong></td>
                    <td>: <span class="nip">Memuat...</span></td>
                </tr>
                <tr>
                    <td><strong>Nama</strong></td>
                    <td>: <span class="nama">Memuat...</span></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td>: <span class="email">Memuat...</span></td>
                </tr>
                <tr>
                    <td><strong>Nomor Telepon</strong></td>
                    <td>: <span class="nomor_telepon">Memuat...</span></td>
                </tr>
            </tbody>
        </table>
        <hr>
        <a href="<?= base_url() ?>dosen/profil" class="btn btn-primary">
            <i class="fa fa-edit"></i> Edit Data
        </a>
	</div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<link rel="stylesheet" href="<?= base_url() ?>cdn/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<script src="<?= base_url() ?>cdn/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>cdn/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script>
	base_url = '<?= base_url(); ?>'
	$.ajax({
		url: base_url + 'api/dosen/get_byid',
		type: 'post',
		dataType: 'json',
		data: {
			id: <?= $this->session->userdata('id'); ?>
		},
		success: function(res) {
            // Script ini tidak diubah dan akan tetap berfungsi
			$.each(res.data, function(i, item) {
				$(".nip").html(item.nip)
				$(".nama").html(item.nama)
				$(".email").html(item.email)
				$(".nomor_telepon").html(item.nomor_telepon)
			})
		}
	})
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>