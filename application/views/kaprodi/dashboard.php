<?php
// [PERBAIKAN] Langsung mulai buffer, hapus pemanggilan template dari sini
ob_start();
?>

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
                    <span class="text-nowrap">Mahasiswa aktif prodi</span>
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
                    <span class="text-nowrap">Dosen di prodi</span>
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
                        <h3 class="mb-0">5 Proposal Terbaru Menunggu Penetapan</h3>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get 5 proposal terbaru yang belum ditetapkan
                        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
                        $this->db->from('proposal_mahasiswa');
                        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
                        $this->db->where('mahasiswa.prodi_id', $this->session->userdata('prodi_id'));
                        $this->db->where('proposal_mahasiswa.status', '0');
                        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
                        $this->db->limit(5);
                        $proposals = $this->db->get()->result();
                        
                        if (!empty($proposals)) {
                            foreach($proposals as $p): ?>
                            <tr>
                                <td><?= $p->nim ?></td>
                                <td><?= $p->nama_mahasiswa ?></td>
                                <td><?= substr($p->judul, 0, 70) ?>...</td>
                                <td>
                                    <a href="<?= base_url() ?>kaprodi/penetapan/<?= $p->id ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-user-check"></i> Tetapkan
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; 
                        } else {
                            echo '<tr><td colspan="4" class="text-center">Tidak ada proposal yang menunggu penetapan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();

$this->load->view('template/kaprodi', [
    'title' => 'Dashboard Kaprodi',
    'content' => $content,
    'script' => '' // Tidak ada skrip khusus untuk halaman ini
]); 
?>