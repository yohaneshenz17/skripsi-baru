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
                    <span class="text-nowrap">Dosen semua prodi</span>
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
                        <h3 class="mb-0">Proposal Menunggu Penetapan</h3>
                    </div>
                    <div class="col text-right">
                        <!-- Link ke workflow yang sudah ada -->
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
                        // Get proposal terbaru yang belum ditetapkan (hanya yang valid)
                        $prodi_id = $this->session->userdata('prodi_id');
                        if ($prodi_id) {
                            $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
                            $this->db->from('proposal_mahasiswa');
                            $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
                            $this->db->where('mahasiswa.prodi_id', $prodi_id);
                            $this->db->where('proposal_mahasiswa.status', '0');
                            // Filter: hanya proposal yang valid (bukan data lama)
                            $this->db->where('proposal_mahasiswa.id NOT IN (34, 35)');
                            $this->db->order_by('proposal_mahasiswa.id', 'DESC');
                            $this->db->limit(5);
                            $proposals = $this->db->get()->result();
                            
                            if (!empty($proposals)) {
                                foreach($proposals as $p): ?>
                                <tr>
                                    <td><?= $p->nim ?></td>
                                    <td><?= $p->nama_mahasiswa ?></td>
                                    <td>
                                        <!-- Link ke review proposal yang sudah ada -->
                                        <a href="<?= base_url() ?>kaprodi/review_proposal/<?= $p->id ?>" class="btn btn-sm btn-primary">
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

<!-- Info Panel Dosen -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Info Pemilihan Pembimbing</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Kaprodi dapat memilih pembimbing dari semua dosen di semua prodi.</strong> 
                            Total <?= isset($total_dosen) ? $total_dosen : 0 ?> dosen tersedia sebagai calon pembimbing.
                        </p>
                    </div>
                    <div class="col-auto">
                        <span class="h2 text-white">👨‍🏫</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tools Development untuk membersihkan data -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-white">Development Tools - Database Cleanup</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Perhatian:</strong> Tools ini hanya tersedia dalam mode development untuk membersihkan data lama.
                </div>
                
                <div class="btn-group mb-3" role="group">
                    <a href="<?= base_url() ?>kaprodi/dashboard/cleanup_old_proposals" 
                       class="btn btn-danger"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus proposal lama (ID 34 dan 35)? Data akan dibackup terlebih dahulu.')">
                        <i class="fa fa-trash"></i> Bersihkan Data Proposal Lama
                    </a>
                    
                    <a href="<?= base_url() ?>kaprodi/dashboard/debug_data" 
                       class="btn btn-secondary"
                       target="_blank">
                        <i class="fa fa-bug"></i> Debug Data
                    </a>
                </div>
                
                <small class="text-muted d-block">
                    • <strong>Bersihkan Data:</strong> Akan menghapus proposal ID 34 dan 35 (data lama/tidak valid)<br>
                    • <strong>Debug Data:</strong> Melihat detail data proposal saat ini
                </small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Debug Info (hanya untuk development) -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>Debug Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Session Info:</h6>
                        <p><strong>Level:</strong> <?= $this->session->userdata('level') ?></p>
                        <p><strong>Nama:</strong> <?= $this->session->userdata('nama') ?></p>
                        <p><strong>Prodi ID:</strong> <?= $this->session->userdata('prodi_id') ?></p>
                        <p><strong>Logged In:</strong> <?= $this->session->userdata('logged_in') ? 'Yes' : 'No' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Database Info:</h6>
                        <p><strong>Table pengumuman_tahapan:</strong> <?= $this->db->table_exists('pengumuman_tahapan') ? 'Exists' : 'Not Found' ?></p>
                        <p><strong>Environment:</strong> <?= ENVIRONMENT ?></p>
                        <p><strong>Base URL:</strong> <?= base_url() ?></p>
                        <p><strong>Total Dosen (All Prodi):</strong> <?= isset($total_dosen) ? $total_dosen : 0 ?></p>
                    </div>
                </div>
                
                <!-- Quick Data Preview -->
                <h6 class="mt-4">Quick Data Preview:</h6>
                <?php
                $quick_data = $this->db->select('pm.id, pm.mahasiswa_id, m.nim, m.nama, pm.status')
                    ->from('proposal_mahasiswa pm')
                    ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left')
                    ->limit(10)
                    ->get()->result();
                
                if ($quick_data) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-sm">';
                    echo '<tr><th>ID</th><th>Mahasiswa ID</th><th>NIM</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr>';
                    foreach ($quick_data as $qd) {
                        $keterangan = in_array($qd->id, [34, 35]) ? 
                            '<span class="badge badge-warning">Data Lama</span>' : 
                            '<span class="badge badge-success">Data Valid</span>';
                        echo "<tr>";
                        echo "<td>{$qd->id}</td>";
                        echo "<td>{$qd->mahasiswa_id}</td>";
                        echo "<td>" . ($qd->nim ?? 'NULL') . "</td>";
                        echo "<td>" . ($qd->nama ?? 'NULL') . "</td>";
                        echo "<td>{$qd->status}</td>";
                        echo "<td>{$keterangan}</td>";
                        echo "</tr>";
                    }
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p>Tidak ada data proposal.</p>';
                }
                ?>
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