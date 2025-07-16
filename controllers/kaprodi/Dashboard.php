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
            // Total proposal masuk
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $result = $this->db->get()->row();
            $data['total_proposal'] = $result ? $result->total : 0;

            // Proposal belum ditetapkan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('pm.status', '0');
            $result = $this->db->get()->row();
            $data['proposal_belum_ditetapkan'] = $result ? $result->total : 0;

            // Total mahasiswa aktif
            $this->db->select('COUNT(*) as total');
            $this->db->from('mahasiswa');
            $this->db->where('prodi_id', $prodi_id);
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $data['total_mahasiswa'] = $result ? $result->total : 0;

            // Total dosen di prodi
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where('prodi_id', $prodi_id);
            $result = $this->db->get()->row();
            $data['total_dosen'] = $result ? $result->total : 0;
        } else {
            // Jika prodi_id tidak ditemukan, set nilai default
            $data['total_proposal'] = 0;
            $data['proposal_belum_ditetapkan'] = 0;
            $data['total_mahasiswa'] = 0;
            $data['total_dosen'] = 0;
        }

        // Load view
        $this->load->view('kaprodi/dashboard', $data);
    }
}