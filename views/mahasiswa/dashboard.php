<?php
$id_user = $this->session->userdata('id');
$verifikasi = '';
$dataUser = $this->db->get_where('mahasiswa', array('id' => $id_user))->result();
foreach ($dataUser as $du) {
    $verifikasi = $du->status;
}
?>
<?php $this->app->extend('template/mahasiswa') ?>

<?php $this->app->setVar('title', "Dashboard") ?>

<?php $this->app->section() ?>
<?php if ($verifikasi == 1) { ?>
    <!-- Pengumuman Tahapan Skripsi -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title text-uppercase text-muted mb-0">
                <i class="ni ni-bell-55 text-primary"></i> Pengumuman Tahapan Skripsi
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 10%">No</th>
                            <th style="width: 30%">Tahapan</th>
                            <th style="width: 25%">Tanggal Deadline</th>
                            <th style="width: 35%">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data pengumuman tahapan
                        $pengumuman = $this->db->get_where('pengumuman_tahapan', ['aktif' => '1'])->result();
                        
                        if (!empty($pengumuman)) {
                            foreach($pengumuman as $p): 
                                // Hitung status deadline
                                $deadline_date = new DateTime($p->tanggal_deadline);
                                $today = new DateTime();
                                $interval = $today->diff($deadline_date);
                                
                                if ($today > $deadline_date) {
                                    $status_class = 'text-danger';
                                    $status_text = 'Telah lewat';
                                } elseif ($interval->days <= 7) {
                                    $status_class = 'text-warning';
                                    $status_text = $interval->days . ' hari lagi';
                                } else {
                                    $status_class = 'text-success';
                                    $status_text = $interval->days . ' hari lagi';
                                }
                        ?>
                        <tr>
                            <td><strong><?= $p->no ?></strong></td>
                            <td><?= $p->tahapan ?></td>
                            <td>
                                <span class="<?= $status_class ?>"><?= date('d F Y', strtotime($p->tanggal_deadline)) ?></span>
                                <br><small class="<?= $status_class ?>"><?= $status_text ?></small>
                            </td>
                            <td><?= $p->keterangan ? $p->keterangan : '-' ?></td>
                        </tr>
                        <?php 
                            endforeach; 
                        } else {
                            echo '<tr><td colspan="4" class="text-center text-muted">Belum ada pengumuman tahapan</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Card Statistik -->
    <div class="card card-stats">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h5 class="card-title text-uppercase text-muted mb-0">Total Proposal</h5>
                    <span class="h2 font-weight-bold mb-0 total-proposal">0</span>
                </div>
                <div class="col-auto">
                    <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                        <i class="ni ni-active-40"></i>
                    </div>
                </div>
            </div>
            <p class="mt-3 mb-0 text-sm">
                <a href="<?= base_url() ?>mahasiswa/proposal" class="text-success mr-2"><i class="fa fa-arrow-right"></i> Selengkapnya</a>
            </p>
        </div>
    </div>
<?php } ?>

<!-- Profil Mahasiswa -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title text-uppercase text-muted mb-0">
            <i class="ni ni-single-02 text-primary"></i> Profil Mahasiswa
        </h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3 text-center mb-3 mb-md-0">
                <img src="<?= base_url() ?>cdn/img/mahasiswa/default.png" class="foto" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
            </div>

            <div class="col-md-9">
                <h3 class="nama" style="font-weight: 600;">Nama Mahasiswa</h3>
                <hr class="mt-2 mb-3">
                <p style="margin-bottom: 0.5rem;"><strong>Prodi:</strong> <span class="prodi_nama">Nama Prodi</span></p>
                <p style="margin-bottom: 0.5rem;"><strong>Fakultas:</strong> <span class="prodi_fakultas_nama">Nama Fakultas</span></p>
                <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> <span class="email">Email Mahasiswa</span></p>
                <p style="margin-bottom: 1.5rem;"><strong>Nomor Telepon:</strong> <span class="nomor_telepon">Nomor Telepon</span></p>

                <a href="<?= base_url() ?>mahasiswa/profil" class="btn btn-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit Profil
                </a>
            </div>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
    $(document).ready(function() {
        // Load data mahasiswa
        call('api/mahasiswa/detail/<?= $this->session->userdata('id') ?>').done(function(req) {
            if (req.data) {
                $('.nama').html(req.data.nama);
                $('.prodi_nama').html(req.data.prodi.nama);
                $('.prodi_fakultas_nama').html(req.data.prodi.fakultas.nama);
                $('.email').html(req.data.email);
                $('.nomor_telepon').html(req.data.nomor_telepon);
                $('img.foto').attr('src', base_url + 'cdn/img/mahasiswa/' + ((req.data.foto) ? req.data.foto : 'default.png'));
                $('.total-proposal').html(req.data.proposal.length);
            }
        });
    });
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>