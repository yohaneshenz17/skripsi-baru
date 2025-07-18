<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');

        // Debug session
        log_message('debug', 'Dashboard Kaprodi - Session level: ' . $this->session->userdata('level'));
        log_message('debug', 'Dashboard Kaprodi - Session logged_in: ' . $this->session->userdata('logged_in'));
        log_message('debug', 'Dashboard Kaprodi - Session nama: ' . $this->session->userdata('nama'));

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

        // Debug prodi_id
        log_message('debug', 'Dashboard Kaprodi - Prodi ID: ' . $prodi_id);

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
            $this->db->where('pm.status', '0');
            // Filter: hanya proposal yang valid (bukan data lama)
            $this->db->where('pm.id NOT IN (34, 35)'); // Exclude data lama
            $result = $this->db->get()->row();
            $data['proposal_belum_ditetapkan'] = $result ? $result->total : 0;

            // Total mahasiswa aktif di prodi ini
            $this->db->select('COUNT(*) as total');
            $this->db->from('mahasiswa');
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $data['total_mahasiswa'] = $result ? $result->total : 0;

            // PERBAIKAN: Total dosen dari SEMUA prodi (bukan hanya prodi kaprodi)
            // Kaprodi bisa memilih pembimbing dari dosen manapun
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where_not_in('level', ['1']); // Exclude admin saja, kaprodi dan dosen biasa bisa jadi pembimbing
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
        $this->load->view('kaprodi/dashboard', $data);
    }

    /**
     * Function untuk membersihkan data proposal lama
     */
    public function cleanup_old_proposals()
    {
        // Hanya bisa diakses oleh kaprodi atau admin
        if ($this->session->userdata('level') != '4' && $this->session->userdata('level') != '1') {
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
            $this->session->set_flashdata('error', 'Tidak ada data yang perlu dibersihkan.');
        }

        redirect('kaprodi/dashboard');
    }

    /**
     * Function untuk debug dan melihat data
     */
    public function debug_data()
    {
        if (ENVIRONMENT !== 'development') {
            show_error('Akses ditolak.', 403);
        }

        echo "<h3>Debug Data Dashboard Kaprodi</h3>";

        // Cek data proposal
        echo "<h4>Data Proposal saat ini:</h4>";
        $proposals = $this->db->select('pm.id, pm.mahasiswa_id, m.nim, m.nama, pm.judul, pm.status')
                              ->from('proposal_mahasiswa pm')
                              ->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left')
                              ->order_by('pm.id')
                              ->get()->result();

        if ($proposals) {
            echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>ID</th><th>Mahasiswa ID</th><th>NIM</th><th>Nama</th><th>Judul</th><th>Status</th><th>Keterangan</th></tr>';
            foreach ($proposals as $p) {
                $keterangan = '';
                if (in_array($p->id, [34, 35])) {
                    $keterangan = '<span style="color: red;">DATA LAMA - AKAN DIHAPUS</span>';
                } else {
                    $keterangan = '<span style="color: green;">DATA VALID</span>';
                }
                echo "<tr>";
                echo "<td>{$p->id}</td>";
                echo "<td>{$p->mahasiswa_id}</td>";
                echo "<td>" . ($p->nim ?? 'NULL') . "</td>";
                echo "<td>" . ($p->nama ?? 'NULL') . "</td>";
                echo "<td>" . substr($p->judul, 0, 50) . "...</td>";
                echo "<td>{$p->status}</td>";
                echo "<td>{$keterangan}</td>";
                echo "</tr>";
            }
            echo '</table>';
        }

        // Cek total dosen dari semua prodi
        echo "<h4>Info Dosen:</h4>";
        $total_dosen = $this->db->where_not_in('level', ['1'])->count_all_results('dosen');
        echo "<p>Total dosen (semua prodi): {$total_dosen}</p>";
        
        $dosens_by_level = $this->db->select('level, COUNT(*) as total')
                                   ->from('dosen')
                                   ->where_not_in('level', ['1'])
                                   ->group_by('level')
                                   ->get()->result();
        
        echo "<h5>Breakdown dosen by level:</h5>";
        foreach($dosens_by_level as $d) {
            $level_name = ($d->level == '2') ? 'Dosen' : (($d->level == '4') ? 'Kaprodi' : 'Unknown');
            echo "<p>Level {$d->level} ({$level_name}): {$d->total} orang</p>";
        }

        // Cek prodi dan kaprodi
        echo "<h4>Info Prodi dan Kaprodi:</h4>";
        $prodi_info = $this->db->select('p.id, p.nama, d.nama as nama_kaprodi')
                               ->from('prodi p')
                               ->join('dosen d', 'p.dosen_id = d.id')
                               ->where('p.id', $this->session->userdata('prodi_id'))
                               ->get()->row();

        if ($prodi_info) {
            echo "<p>Prodi ID: {$prodi_info->id}</p>";
            echo "<p>Nama Prodi: {$prodi_info->nama}</p>";
            echo "<p>Kaprodi: {$prodi_info->nama_kaprodi}</p>";
        }

        echo "<br><a href='" . base_url('kaprodi/dashboard') . "'>← Kembali ke Dashboard</a>";
    }
}