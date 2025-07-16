<?php
ob_start();
?>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Proposal Masuk</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($total_proposal) ? $total_proposal : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="ni ni-paper-diploma"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i> <?= isset($proposal_belum_ditetapkan) ? $proposal_belum_ditetapkan : 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= isset($total_mahasiswa) ? $total_mahasiswa : 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= isset($total_dosen) ? $total_dosen : 0 ?></span>
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
    <!-- Card Pengumuman Aktif -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Pengumuman Aktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            // Cek apakah tabel pengumuman_tahapan ada
                            $pengumuman_count = 0;
                            if ($this->db->table_exists('pengumuman_tahapan')) {
                                $pengumuman_count = $this->db->get_where('pengumuman_tahapan', ['aktif' => '1'])->num_rows();
                            }
                            echo $pengumuman_count;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
                            <i class="ni ni-bell-55"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-bullhorn"></i></span>
                    <span class="text-nowrap">Tahapan aktif</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Pengumuman Tahapan Aktif -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengumuman Tahapan Aktif</h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url() ?>kaprodi/pengumuman" class="btn btn-sm btn-primary">Kelola</a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tahapan</th>
                            <th>Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Cek apakah tabel pengumuman_tahapan ada
                        if ($this->db->table_exists('pengumuman_tahapan')) {
                            $this->db->where('aktif', '1');
                            $this->db->order_by('no', 'ASC');
                            $pengumuman_aktif = $this->db->get('pengumuman_tahapan')->result();
                            
                            if (!empty($pengumuman_aktif)) {
                                foreach($pengumuman_aktif as $p): 
                                    // Hitung status deadline
                                    $deadline_date = new DateTime($p->tanggal_deadline);
                                    $today = new DateTime();
                                    
                                    if ($today > $deadline_date) {
                                        $status_class = 'text-danger';
                                    } else {
                                        $interval = $today->diff($deadline_date);
                                        if ($interval->days <= 7) {
                                            $status_class = 'text-warning';
                                        } else {
                                            $status_class = 'text-success';
                                        }
                                    }
                            ?>
                                <tr>
                                    <td><?= $p->no ?></td>
                                    <td><?= $p->tahapan ?></td>
                                    <td><span class="<?= $status_class ?>"><?= date('d/m/Y', strtotime($p->tanggal_deadline)) ?></span></td>
                                </tr>
                            <?php 
                                endforeach; 
                            } else {
                                echo '<tr><td colspan="3" class="text-center">Belum ada pengumuman aktif.</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center">Tabel pengumuman belum dibuat. <a href="' . base_url() . 'kaprodi/pengumuman" class="btn btn-sm btn-primary">Buat Pengumuman</a></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Proposal Terbaru Menunggu Penetapan -->
    <div class="col-lg-6">
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get 5 proposal terbaru yang belum ditetapkan
                        $prodi_id = $this->session->userdata('prodi_id');
                        if ($prodi_id) {
                            $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
                            $this->db->from('proposal_mahasiswa');
                            $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
                            $this->db->where('mahasiswa.prodi_id', $prodi_id);
                            $this->db->where('proposal_mahasiswa.status', '0');
                            $this->db->order_by('proposal_mahasiswa.id', 'DESC');
                            $this->db->limit(5);
                            $proposals = $this->db->get()->result();
                            
                            if (!empty($proposals)) {
                                foreach($proposals as $p): ?>
                                <tr>
                                    <td><?= $p->nim ?></td>
                                    <td><?= $p->nama_mahasiswa ?></td>
                                    <td>
                                        <a href="<?= base_url() ?>kaprodi/penetapan/<?= $p->id ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-user-check"></i> Tetapkan
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; 
                            } else {
                                echo '<tr><td colspan="3" class="text-center">Tidak ada proposal yang menunggu penetapan.</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center">Session prodi tidak ditemukan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Debug Info (hanya untuk development) -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>Debug Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Session Level:</strong> <?= $this->session->userdata('level') ?></p>
                <p><strong>Session Nama:</strong> <?= $this->session->userdata('nama') ?></p>
                <p><strong>Session Prodi ID:</strong> <?= $this->session->userdata('prodi_id') ?></p>
                <p><strong>Session Logged In:</strong> <?= $this->session->userdata('logged_in') ? 'Yes' : 'No' ?></p>
                <p><strong>Table pengumuman_tahapan exists:</strong> <?= $this->db->table_exists('pengumuman_tahapan') ? 'Yes' : 'No' ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
$content = ob_get_clean();

$this->load->view('template/kaprodi', [
    'title' => $title,
    'content' => $content,
    'script' => ''
]); 
?>