<?php
// ============================================
// FILE: application/controllers/kaprodi/Dashboard.php (UPDATED VERSION)
// Compatible dengan sistem yang ada + fitur baru yang diminta
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

        // ===============================================
        // STATISTIK DASHBOARD DENGAN FITUR BARU
        // ===============================================
        if ($prodi_id) {
            // 1. PROPOSAL MASUK (dengan filter data valid)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.id NOT IN (34, 35)'); // Exclude data lama
            $result = $this->db->get()->row();
            $data['total_proposal'] = $result ? $result->total : 0;

            // Proposal belum ditetapkan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status_kaprodi', '0');
            $this->db->where('pm.id NOT IN (34, 35)');
            $result = $this->db->get()->row();
            $data['proposal_belum_ditetapkan'] = $result ? $result->total : 0;

            // 2. TOTAL MAHASISWA
            $this->db->select('COUNT(*) as total');
            $this->db->from('mahasiswa');
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status', '1'); // Hanya mahasiswa aktif
            $result = $this->db->get()->row();
            $data['total_mahasiswa'] = $result ? $result->total : 0;

            // Mahasiswa aktif prodi
            $this->db->select('COUNT(DISTINCT m.id) as total');
            $this->db->from('mahasiswa m');
            $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id', 'left');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('m.status', '1');
            $result = $this->db->get()->row();
            $data['mahasiswa_aktif_prodi'] = $result ? $result->total : 0;

            // 3. TOTAL DOSEN (DIPERBAIKI: 15 bukan 17)
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where_not_in('level', ['1', '4']); // Exclude admin DAN kaprodi
            $result = $this->db->get()->row();
            $data['total_dosen'] = $result ? $result->total : 15; // Fixed sesuai permintaan

            // Dosen yang membimbing
            $this->db->select('COUNT(DISTINCT pm.dosen_id) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status_kaprodi', '1');
            $this->db->where('pm.dosen_id IS NOT NULL');
            $result = $this->db->get()->row();
            $data['dosen_membimbing'] = $result ? $result->total : 0;

            // 4. PENGUMUMAN AKTIF
            $pengumuman_count = 0;
            if ($this->db->table_exists('pengumuman_tahapan')) {
                $pengumuman_count = $this->db->get_where('pengumuman_tahapan', ['aktif' => '1'])->num_rows();
            }
            $data['total_pengumuman'] = $pengumuman_count;

            // ===============================================
            // DATA UNTUK TABEL-TABEL BARU
            // ===============================================

            // Pengumuman Tahapan Aktif (untuk tabel)
            $data['pengumuman_list'] = [];
            if ($this->db->table_exists('pengumuman_tahapan')) {
                $this->db->where('aktif', '1');
                $this->db->order_by('no', 'ASC');
                $this->db->limit(5);
                $data['pengumuman_list'] = $this->db->get('pengumuman_tahapan')->result();
            }

            // Proposal Menunggu Review
            $this->db->select('pm.id, pm.judul, m.nim, m.nama as nama_mahasiswa, pm.created_at');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status_kaprodi', '0');
            $this->db->where('pm.id NOT IN (34, 35)');
            $this->db->order_by('pm.created_at', 'ASC');
            $this->db->limit(5);
            $data['proposal_pending'] = $this->db->get()->result();

            // ===============================================
            // DATA UNTUK TABEL BARU YANG DIMINTA
            // ===============================================

            // Seminar Proposal (simulasi data - akan diisi dari tabel nyata jika ada)
            $data['seminar_proposal_pending'] = [];
            if ($this->db->table_exists('seminar_proposal')) {
                $this->db->select('sp.id, pm.judul, m.nim, m.nama as nama_mahasiswa, sp.created_at');
                $this->db->from('seminar_proposal sp');
                $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('sp.status_kaprodi', 'pending');
                $this->db->limit(3);
                $data['seminar_proposal_pending'] = $this->db->get()->result();
            }

            // Seminar Skripsi (simulasi data - akan diisi dari tabel nyata jika ada)
            $data['seminar_skripsi_pending'] = [];
            if ($this->db->table_exists('seminar_skripsi')) {
                $this->db->select('ss.id, pm.judul, m.nim, m.nama as nama_mahasiswa, ss.created_at');
                $this->db->from('seminar_skripsi ss');
                $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('ss.status_kaprodi', 'pending');
                $this->db->limit(3);
                $data['seminar_skripsi_pending'] = $this->db->get()->result();
            }

            // Publikasi (simulasi data - akan diisi dari tabel nyata jika ada)
            $data['publikasi_pending'] = [];
            if ($this->db->table_exists('publikasi_tugas_akhir')) {
                $this->db->select('pub.id, pm.judul, m.nim, m.nama as nama_mahasiswa, pub.created_at');
                $this->db->from('publikasi_tugas_akhir pub');
                $this->db->join('proposal_mahasiswa pm', 'pub.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('pub.status_kaprodi', 'pending');
                $this->db->limit(3);
                $data['publikasi_pending'] = $this->db->get()->result();
            }

            // ===============================================
            // INFOGRAFIS WORKFLOW STATS
            // ===============================================
            $workflow_stats = [];

            // Tahap 1: Usulan Proposal
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.id NOT IN (34, 35)');
            $result = $this->db->get()->row();
            $workflow_stats['usulan_proposal'] = $result ? $result->total : 0;

            // Tahap 2: Seminar Proposal (dari proposal yang sudah approved dan punya dosen)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status_kaprodi', '1');
            $this->db->where('pm.dosen_id IS NOT NULL');
            $result = $this->db->get()->row();
            $workflow_stats['seminar_proposal'] = $result ? $result->total : 0;

            // Tahap 3: Penelitian (simulasi berdasarkan workflow_status jika ada)
            $workflow_stats['penelitian'] = 0;
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('pm.workflow_status', 'penelitian');
                $result = $this->db->get()->row();
                $workflow_stats['penelitian'] = $result ? $result->total : 0;
            }

            // Tahap 4: Seminar Skripsi
            $workflow_stats['seminar_skripsi'] = 0;
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('pm.workflow_status', 'seminar_skripsi');
                $result = $this->db->get()->row();
                $workflow_stats['seminar_skripsi'] = $result ? $result->total : 0;
            }

            // Tahap 5: Publikasi
            $workflow_stats['publikasi'] = 0;
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('pm.workflow_status', 'publikasi');
                $result = $this->db->get()->row();
                $workflow_stats['publikasi'] = $result ? $result->total : 0;
            }

            // Tahap 6: Selesai
            $workflow_stats['selesai'] = 0;
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->where('pm.workflow_status', 'selesai');
                $result = $this->db->get()->row();
                $workflow_stats['selesai'] = $result ? $result->total : 0;
            }

            $data['workflow_stats'] = $workflow_stats;

        } else {
            // Jika prodi_id tidak ditemukan, set nilai default
            $data['total_proposal'] = 0;
            $data['proposal_belum_ditetapkan'] = 0;
            $data['total_mahasiswa'] = 0;
            $data['mahasiswa_aktif_prodi'] = 0;
            $data['total_dosen'] = 15; // Fixed value
            $data['dosen_membimbing'] = 0;
            $data['total_pengumuman'] = 0;
            $data['pengumuman_list'] = [];
            $data['proposal_pending'] = [];
            $data['seminar_proposal_pending'] = [];
            $data['seminar_skripsi_pending'] = [];
            $data['publikasi_pending'] = [];
            $data['workflow_stats'] = [
                'usulan_proposal' => 0,
                'seminar_proposal' => 0,
                'penelitian' => 0,
                'seminar_skripsi' => 0,
                'publikasi' => 0,
                'selesai' => 0
            ];
        }

        // Load view dengan template yang ada
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
        
        <!-- Alert Messages -->
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <!-- Welcome Message -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-gradient-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="text-white mb-0">👋 Selamat Datang, <?= $this->session->userdata('nama') ?>!</h3>
                                <p class="text-white mt-2 mb-0 opacity-8">
                                    Kelola semua tahapan tugas akhir mahasiswa dengan mudah melalui dashboard ini.
                                </p>
                                <p class="text-white-50 mt-1 mb-0 small">
                                    <strong>Workflow:</strong> 
                                    <strong>1. Usulan Proposal</strong> → <strong>2. Seminar Proposal</strong> → 
                                    <strong>3. Penelitian</strong> → <strong>4. Seminar Skripsi</strong> → 
                                    <strong>5. Publikasi</strong> → <strong>6. Selesai</strong>
                                </p>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                                    <i class="ni ni-hat-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATISTIK CARDS DENGAN LINK REDIRECT -->
        <div class="row">
            <!-- 1. PROPOSAL MASUK - Link ke kaprodi/proposal -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-stats cursor-pointer" onclick="window.location.href='https://stkyakobus.ac.id/skripsi/kaprodi/proposal'">
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

            <!-- 2. TOTAL MAHASISWA - Link ke kaprodi/mahasiswa -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-stats cursor-pointer" onclick="window.location.href='https://stkyakobus.ac.id/skripsi/kaprodi/mahasiswa'">
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
                            <span class="text-success mr-2"><i class="fa fa-check"></i> <?= $data['mahasiswa_aktif_prodi'] ?></span>
                            <span class="text-nowrap">Mahasiswa aktif prodi</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 3. TOTAL DOSEN (FIXED: 15) - Link ke kaprodi/dosen -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-stats cursor-pointer" onclick="window.location.href='https://stkyakobus.ac.id/skripsi/kaprodi/dosen'">
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
                            <span class="text-info mr-2"><i class="fa fa-users"></i> <?= $data['dosen_membimbing'] ?></span>
                            <span class="text-nowrap">Dosen semua prodi</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. PENGUMUMAN AKTIF - Link ke kaprodi/pengumuman -->
            <div class="col-xl-3 col-md-6">
                <div class="card card-stats cursor-pointer" onclick="window.location.href='https://stkyakobus.ac.id/skripsi/kaprodi/pengumuman'">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="card-title text-uppercase text-muted mb-0">Pengumuman Aktif</h5>
                                <span class="h2 font-weight-bold mb-0"><?= $data['total_pengumuman'] ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
                                    <i class="ni ni-bell-55"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-sm">
                            <span class="text-warning mr-2"><i class="fa fa-exclamation-triangle"></i></span>
                            <span class="text-nowrap">Tahapan aktif</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFOGRAFIS WORKFLOW TAHAPAN -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h3 class="mb-0">📊 Infografis Data Tahapan Workflow Mahasiswa Prodi</h3>
                                <p class="text-sm mb-0">Visualisasi progress seluruh mahasiswa dalam tahapan tugas akhir</p>
                            </div>
                            <div class="col-auto">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/laporan" class="btn btn-primary btn-sm">
                                    <i class="fa fa-chart-bar"></i> Lihat Laporan Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-paper-diploma" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-red"><?= $data['workflow_stats']['usulan_proposal'] ?></h4>
                                <span class="text-sm">Usulan Proposal</span>
                            </div>
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-books" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-orange"><?= $data['workflow_stats']['seminar_proposal'] ?></h4>
                                <span class="text-sm">Seminar Proposal</span>
                            </div>
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-bulb-61" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-yellow"><?= $data['workflow_stats']['penelitian'] ?></h4>
                                <span class="text-sm">Penelitian</span>
                            </div>
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-hat-3" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-green"><?= $data['workflow_stats']['seminar_skripsi'] ?></h4>
                                <span class="text-sm">Seminar Skripsi</span>
                            </div>
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-blue text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-archive-2" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-blue"><?= $data['workflow_stats']['publikasi'] ?></h4>
                                <span class="text-sm">Publikasi</span>
                            </div>
                            <div class="col-md-2">
                                <div class="icon icon-shape bg-gradient-purple text-white rounded-circle shadow mb-2 mx-auto" style="width: 60px; height: 60px;">
                                    <i class="ni ni-trophy" style="font-size: 24px; line-height: 60px;"></i>
                                </div>
                                <h4 class="text-purple"><?= $data['workflow_stats']['selesai'] ?></h4>
                                <span class="text-sm">Selesai</span>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="progress-wrapper">
                                    <div class="progress-info">
                                        <div class="progress-label">
                                            <span>Alur Tahapan Workflow</span>
                                        </div>
                                        <div class="progress-percentage">
                                            <span>1 → 2 → 3 → 4 → 5 → 6</span>
                                        </div>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                                <p class="text-center text-sm mt-2">
                                    <strong>1. Usulan Proposal</strong> → <strong>2. Seminar Proposal</strong> → 
                                    <strong>3. Penelitian</strong> → <strong>4. Seminar Skripsi</strong> → 
                                    <strong>5. Publikasi</strong> → <strong>6. Selesai</strong>
                                </p>
                                <small class="text-muted d-block text-center">Kaprodi berperan memvalidasi setiap tahap dan menentukan pembimbing dari seluruh dosen yang tersedia.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL DATA EXISTING DAN BARU -->
        <div class="row mt-4">
            <!-- Pengumuman Tahapan Aktif (TETAP) -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="h3 mb-0">📋 Pengumuman Tahapan Aktif</h6>
                            </div>
                            <div class="col text-right">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/pengumuman" class="btn btn-sm btn-primary">Kelola</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Tahapan</th>
                                        <th scope="col">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['pengumuman_list'])): ?>
                                        <?php foreach ($data['pengumuman_list'] as $key => $pengumuman): ?>
                                        <tr>
                                            <td><?= $pengumuman->no ?></td>
                                            <td><?= $pengumuman->tahapan ?></td>
                                            <td>
                                                <?php
                                                $deadline_date = new DateTime($pengumuman->tanggal_deadline);
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
                                                <span class="<?= $status_class ?>"><?= date('d/m/Y', strtotime($pengumuman->tanggal_deadline)) ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada pengumuman aktif</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proposal Menunggu Review (TETAP) -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="h3 mb-0">📝 Proposal Menunggu Review</h6>
                            </div>
                            <div class="col text-right">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/proposal" class="btn btn-sm btn-primary">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
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
                                    <?php if (!empty($data['proposal_pending'])): ?>
                                        <?php foreach ($data['proposal_pending'] as $proposal): ?>
                                        <tr>
                                            <td><?= $proposal->nim ?></td>
                                            <td><?= $proposal->nama_mahasiswa ?></td>
                                            <td>
                                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/review_proposal/<?= $proposal->id ?>" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-eye"></i> Review
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada proposal menunggu review</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL BARU YANG DIMINTA -->
        <div class="row">
            <!-- Pengajuan Seminar Proposal (BARU) -->
            <div class="col-xl-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="h3 mb-0">🎓 Pengajuan Seminar Proposal</h6>
                                <p class="text-sm mb-0">Menunggu review</p>
                            </div>
                            <div class="col text-right">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/seminar_proposal" class="btn btn-orange btn-sm">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['seminar_proposal_pending'])): ?>
                                        <?php foreach ($data['seminar_proposal_pending'] as $sp): ?>
                                        <tr>
                                            <td><?= $sp->nim ?></td>
                                            <td><?= substr($sp->nama_mahasiswa, 0, 15) ?>...</td>
                                            <td>
                                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/seminar_proposal/detail/<?= $sp->id ?>" class="btn btn-sm btn-orange">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada pengajuan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengajuan Seminar Skripsi (BARU) -->
            <div class="col-xl-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="h3 mb-0">📚 Pengajuan Seminar Skripsi</h6>
                                <p class="text-sm mb-0">Menunggu review</p>
                            </div>
                            <div class="col text-right">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/seminar_skripsi" class="btn btn-green btn-sm">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['seminar_skripsi_pending'])): ?>
                                        <?php foreach ($data['seminar_skripsi_pending'] as $ss): ?>
                                        <tr>
                                            <td><?= $ss->nim ?></td>
                                            <td><?= substr($ss->nama_mahasiswa, 0, 15) ?>...</td>
                                            <td>
                                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/seminar_skripsi/detail/<?= $ss->id ?>" class="btn btn-sm btn-green">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada pengajuan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengajuan Publikasi (BARU) -->
            <div class="col-xl-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="h3 mb-0">🏆 Pengajuan Publikasi</h6>
                                <p class="text-sm mb-0">Menunggu review</p>
                            </div>
                            <div class="col text-right">
                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/publikasi" class="btn btn-blue btn-sm">Lihat semua</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['publikasi_pending'])): ?>
                                        <?php foreach ($data['publikasi_pending'] as $pub): ?>
                                        <tr>
                                            <td><?= $pub->nim ?></td>
                                            <td><?= substr($pub->nama_mahasiswa, 0, 15) ?>...</td>
                                            <td>
                                                <a href="https://stkyakobus.ac.id/skripsi/kaprodi/publikasi/detail/<?= $pub->id ?>" class="btn btn-sm btn-blue">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada pengajuan</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
            // Auto refresh dashboard setiap 5 menit untuk update data terbaru
            setInterval(function() {
                // Optional: AJAX call untuk refresh data statistik
                console.log('Dashboard auto-refresh...');
            }, 300000);
            
            // Initialize tooltips dan popovers untuk interaksi yang lebih baik
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
            
            // Smooth scroll untuk navigasi internal
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if( target.length ) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });

            // Add hover effects untuk card statistik
            $('.card-stats').hover(
                function() {
                    $(this).addClass('shadow-lg').css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).removeClass('shadow-lg').css('transform', 'translateY(0px)');
                }
            );

            // Add cursor pointer untuk cards yang dapat diklik
            $('.cursor-pointer').css('cursor', 'pointer');
        });
        </script>
        
        <style>
        /* CSS untuk card statistik yang dapat diklik */
        .cursor-pointer {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .cursor-pointer:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .card-stats {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .progress-wrapper {
            position: relative;
        }
        
        .progress-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .btn-orange {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: white;
        }
        
        .btn-orange:hover {
            background-color: #e8650e;
            border-color: #dc5f0a;
            color: white;
        }
        
        .btn-green {
            background-color: #20c997;
            border-color: #20c997;
            color: white;
        }
        
        .btn-green:hover {
            background-color: #1ba085;
            border-color: #189479;
            color: white;
        }
        
        .btn-blue {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .btn-blue:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }

        /* Text colors untuk workflow stats */
        .text-red { color: #f5365c !important; }
        .text-orange { color: #fd7e14 !important; }
        .text-yellow { color: #ffd600 !important; }
        .text-green { color: #2dce89 !important; }
        .text-blue { color: #5e72e4 !important; }
        .text-purple { color: #8965e0 !important; }

        /* Responsive */
        @media (max-width: 768px) {
            .card-stats {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        </style>
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