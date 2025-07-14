<?php $this->load->view('template/kaprodi', ['content' => '']); ob_start(); ?>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Proposal Masuk</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_proposal ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="ni ni-paper-diploma"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i> <?= $proposal_belum_ditetapkan ?></span>
                    <span class="text-nowrap">Belum ditetapkan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_mahasiswa ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                            <i class="ni ni-hat-3"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Mahasiswa aktif</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Dosen</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_dosen ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                            <i class="ni ni-single-02"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-users"></i></span>
                    <span class="text-nowrap">Dosen prodi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Proses Skripsi</h5>
                        <span class="h2 font-weight-bold mb-0">-</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                            <i class="ni ni-chart-bar-32"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-primary mr-2"><i class="fa fa-chart-line"></i></span>
                    <span class="text-nowrap">Sedang berjalan</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Proposal Terbaru</h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url() ?>kaprodi/proposal" class="btn btn-sm btn-primary">Lihat semua</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul Proposal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get 5 proposal terbaru
                        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
                        $this->db->from('proposal_mahasiswa');
                        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
                        $this->db->where('mahasiswa.prodi_id', $this->session->userdata('prodi_id'));
                        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
                        $this->db->limit(5);
                        $proposals = $this->db->get()->result();
                        
                        foreach($proposals as $p): ?>
                        <tr>
                            <td><?= $p->nim ?></td>
                            <td><?= $p->nama_mahasiswa ?></td>
                            <td><?= substr($p->judul, 0, 50) ?>...</td>
                            <td>
                                <?php if($p->status == '0' && $p->dosen_id == null): ?>
                                    <span class="badge badge-warning">Menunggu Penetapan</span>
                                <?php elseif($p->status == '1'): ?>
                                    <span class="badge badge-success">Sudah Ditetapkan</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Proses</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($p->status == '0' && $p->dosen_id == null): ?>
                                    <a href="<?= base_url() ?>kaprodi/penetapan/<?= $p->id ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-user-check"></i> Tetapkan
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url() ?>kaprodi/detail/<?= $p->id ?>" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php $this->load->view('template/kaprodi', ['content' => $content]); ?>