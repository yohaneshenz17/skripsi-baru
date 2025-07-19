<?php
// ============================================
// FILE: application/controllers/kaprodi/Dashboard.php (DIPERBAIKI)
// ============================================

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');

        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        // Cek level kaprodi
        if ($this->session->userdata('level') != '4') {
            show_error('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
        }
    }

    public function index()
    {
        $data['title'] = 'Dashboard Kaprodi';
        $prodi_id = $this->session->userdata('prodi_id');

        // Jika prodi_id tidak ada, ambil dari tabel prodi berdasarkan dosen_id
        if (!$prodi_id) {
            $dosen_id = $this->session->userdata('id');
            $prodi = $this->db->get_where('prodi', ['dosen_id' => $dosen_id])->row();
            if ($prodi) {
                $prodi_id = $prodi->id;
                $this->session->set_userdata('prodi_id', $prodi_id);
            }
        }

        // Hitung statistik
        if ($prodi_id) {
            // Total proposal masuk (hanya yang valid - bukan data lama)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            // Filter: hanya proposal yang valid (bukan data lama)
            $this->db->where('pm.id NOT IN (34, 35)'); // Exclude data lama
            $result = $this->db->get()->row();
            $data['total_proposal'] = $result ? $result->total : 0;

            // Proposal belum ditetapkan (hanya yang valid)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status_kaprodi', '0');
            // Filter: hanya proposal yang valid (bukan data lama)
            $this->db->where('pm.id NOT IN (34, 35)'); // Exclude data lama
            $result = $this->db->get()->row();
            $data['proposal_belum_ditetapkan'] = $result ? $result->total : 0;

            // Total mahasiswa aktif di prodi ini
            $this->db->select('COUNT(*) as total');
            $this->db->from('mahasiswa');
            $this->db->where('prodi_id', $prodi_id);
            $result = $this->db->get()->row();
            $data['total_mahasiswa'] = $result ? $result->total : 0;

            // Total dosen dari SEMUA prodi (bukan hanya prodi kaprodi)
            // Kaprodi bisa memilih pembimbing dari dosen manapun
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where_not_in('level', ['1']); // Exclude admin saja
            $result = $this->db->get()->row();
            $data['total_dosen'] = $result ? $result->total : 0;
        } else {
            // Jika prodi_id tidak ditemukan, set nilai default
            $data['total_proposal'] = 0;
            $data['proposal_belum_ditetapkan'] = 0;
            $data['total_mahasiswa'] = 0;
            
            // Tetap hitung total dosen meskipun prodi_id tidak ada
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where_not_in('level', ['1']); // Exclude admin saja
            $result = $this->db->get()->row();
            $data['total_dosen'] = $result ? $result->total : 0;
        }

        // Load view
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->_get_dashboard_content($data),
            'script' => $this->_get_dashboard_script()
        ]);
    }

    private function _get_dashboard_content($data)
    {
        ob_start();
        ?>
        
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Proposal Masuk</h5>
                                <span class="h2 font-weight-bold mb-0"><?= $data['total_proposal'] ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                                    <i class="ni ni-paper-diploma"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-sm">
                            <span class="text-warning mr-2"><i class="fa fa-clock"></i> <?= $data['proposal_belum_ditetapkan'] ?></span>
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
                                <span class="h2 font-weight-bold mb-0"><?= $data['total_mahasiswa'] ?></span>
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
                                <span class="h2 font-weight-bold mb-0"><?= $data['total_dosen'] ?></span>
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
                                <h3 class="mb-0">Proposal Menunggu Review</h3>
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
                                // Get proposal terbaru yang belum direview
                                $prodi_id = $this->session->userdata('prodi_id');
                                if ($prodi_id) {
                                    $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
                                    $this->db->from('proposal_mahasiswa');
                                    $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
                                    $this->db->where('mahasiswa.prodi_id', $prodi_id);
                                    $this->db->where('proposal_mahasiswa.status_kaprodi', '0');
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
                                                <a href="<?= base_url() ?>kaprodi/review_proposal/<?= $p->id ?>" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-eye"></i> Review
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; 
                                    } else {
                                        echo '<tr><td colspan="3" class="text-center">Tidak ada proposal yang menunggu review.</td></tr>';
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

        <!-- Info Panel Workflow -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card bg-gradient-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-white mb-0">Workflow SIM Tugas Akhir</h3>
                                <p class="text-white mt-2 mb-0">
                                    <strong>1. Usulan Proposal</strong> → <strong>2. Seminar Proposal</strong> → 
                                    <strong>3. Penelitian</strong> → <strong>4. Seminar Skripsi</strong> → 
                                    <strong>5. Publikasi</strong> → <strong>6. Selesai</strong>
                                </p>
                                <small class="text-white-50">Kaprodi berperan memvalidasi setiap tahap dan menentukan pembimbing dari seluruh dosen yang tersedia.</small>
                            </div>
                            <div class="col-auto">
                                <span class="h2 text-white">🎓</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    private function _get_dashboard_script()
    {
        ob_start();
        ?>
        <script>
        $(document).ready(function() {
            // Auto refresh dashboard setiap 5 menit
            setInterval(function() {
                // Optional: AJAX call untuk refresh data statistik
                console.log('Dashboard auto-refresh...');
            }, 300000);
            
            // Initialize tooltips dan popovers
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
        });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Function untuk membersihkan data proposal lama (Development only)
     */
    public function cleanup_old_proposals()
    {
        // Hanya bisa diakses dalam development mode
        if (ENVIRONMENT !== 'development') {
            show_error('Akses ditolak.', 403);
        }

        // Backup data sebelum dihapus
        $this->db->query("CREATE TABLE IF NOT EXISTS proposal_mahasiswa_backup_" . date('Ymd') . " AS 
                         SELECT * FROM proposal_mahasiswa WHERE id IN (34, 35)");

        // Hapus data proposal lama (ID 34 dan 35)
        $this->db->where_in('id', [34, 35]);
        $deleted = $this->db->delete('proposal_mahasiswa');
        
        $deleted_count = $this->db->affected_rows();

        // Hapus data workflow terkait
        $this->db->where_in('proposal_id', [34, 35]);
        $this->db->delete('proposal_workflow');

        if ($deleted_count > 0) {
            $this->session->set_flashdata('success', "Berhasil menghapus {$deleted_count} proposal lama. Data telah dibackup.");
        } else {
            $this->session->set_flashdata('info', 'Tidak ada data yang perlu dibersihkan.');
        }

        redirect('kaprodi/dashboard');
    }

    /**
     * Function untuk debug dan melihat data (Development only)
     */
    public function debug_data()
    {
        if (ENVIRONMENT !== 'development') {
            show_error('Akses ditolak.', 403);
        }

        echo "<h3>Debug Data Dashboard Kaprodi</h3>";
        echo "<p><a href='" . base_url('kaprodi/dashboard') . "'>← Kembali ke Dashboard</a></p>";

        // Debug session
        echo "<h4>Session Data:</h4>";
        echo "<pre>";
        print_r($this->session->userdata());
        echo "</pre>";

        // Debug proposal data
        echo "<h4>Data Proposal:</h4>";
        $proposals = $this->db->select('pm.id, pm.mahasiswa_id, m.nim, m.nama, pm.judul, pm.status_kaprodi')
                              ->from('proposal_mahasiswa pm')
                              ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left')
                              ->order_by('pm.id')
                              ->get()->result();

        if ($proposals) {
            echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>ID</th><th>Mahasiswa ID</th><th>NIM</th><th>Nama</th><th>Status Kaprodi</th><th>Keterangan</th></tr>';
            foreach ($proposals as $p) {
                $keterangan = '';
                if (in_array($p->id, [34, 35])) {
                    $keterangan = '<span style="color: red;">DATA LAMA</span>';
                } else {
                    $keterangan = '<span style="color: green;">DATA VALID</span>';
                }
                echo "<tr>";
                echo "<td>{$p->id}</td>";
                echo "<td>{$p->mahasiswa_id}</td>";
                echo "<td>" . ($p->nim ?? 'NULL') . "</td>";
                echo "<td>" . ($p->nama ?? 'NULL') . "</td>";
                echo "<td>{$p->status_kaprodi}</td>";
                echo "<td>{$keterangan}</td>";
                echo "</tr>";
            }
            echo '</table>';
        }

        echo "<br><a href='" . base_url('kaprodi/dashboard') . "'>← Kembali ke Dashboard</a>";
    }
}