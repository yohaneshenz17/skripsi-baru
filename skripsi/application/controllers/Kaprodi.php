<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kaprodi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan level
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id kaprodi
        $this->prodi_id = $this->session->userdata('prodi_id');
    }

    public function index() {
        redirect('kaprodi/dashboard');
    }

    public function dashboard() {
        $data['title'] = 'Dashboard Kaprodi';
        
        // Statistik total proposal yang masuk dan belum diproses
        $data['total_proposal'] = $this->db->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->where('proposal_mahasiswa.status', '0')
            ->where('mahasiswa.prodi_id', $this->prodi_id)
            ->count_all_results();
            
        // Statistik proposal yang belum ditetapkan pembimbingnya
        $data['proposal_belum_ditetapkan'] = $this->db->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->where('proposal_mahasiswa.status', '0')
            ->where('proposal_mahasiswa.dosen_id IS NULL')
            ->where('mahasiswa.prodi_id', $this->prodi_id)
            ->count_all_results();
            
        // Statistik total mahasiswa
        $data['total_mahasiswa'] = $this->db->where('prodi_id', $this->prodi_id)
            ->where('status', '1')
            ->count_all_results('mahasiswa');
            
        // Statistik total dosen
        $data['total_dosen'] = $this->db->where('level !=', '1')
            ->count_all_results('dosen');
        
        $this->load->view('kaprodi/dashboard', $data);
    }

    public function proposal() {
        $data['title'] = 'Penetapan Pembimbing & Penguji';
        
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.status', '0');
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $data['dosens'] = $this->db->where('level !=', '1')
            ->get('dosen')->result();
        
        $this->load->view('kaprodi/proposal', $data);
    }

    public function penetapan($proposal_id) {
        $data['title'] = 'Form Penetapan Pembimbing & Penguji';
        
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa, mahasiswa.email');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if(!$data['proposal']) {
            redirect('kaprodi/proposal');
        }
        
        $data['dosens'] = $this->db->where('level !=', '1')
            ->get('dosen')->result();
        
        $this->load->view('kaprodi/penetapan', $data);
    }

    public function simpan_penetapan() {
        $proposal_id = $this->input->post('proposal_id');
        $dosen_id = $this->input->post('dosen_id');
        $dosen_penguji_id = $this->input->post('dosen_penguji_id');
        $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');
        
        if($dosen_id == $dosen_penguji_id || $dosen_id == $dosen_penguji2_id || ($dosen_penguji_id && $dosen_penguji_id == $dosen_penguji2_id)) {
            $this->session->set_flashdata('error', 'Pembimbing dan penguji harus orang yang berbeda!');
            redirect('kaprodi/penetapan/' . $proposal_id);
        }
        
        $data_update = array(
            'dosen_id' => $dosen_id,
            'dosen_penguji_id' => $dosen_penguji_id,
            'dosen_penguji2_id' => $dosen_penguji2_id,
            'tanggal_penetapan' => date('Y-m-d H:i:s'),
            'penetapan_oleh' => $this->session->userdata('id'),
            'status' => '1'
        );
        
        $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $data_update);
        
        $this->session->set_flashdata('success', 'Pembimbing dan penguji berhasil ditetapkan!');
        redirect('kaprodi/proposal');
    }

    public function riwayat() {
        $data['title'] = 'Riwayat Penetapan';
        
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            d1.nama as nama_pembimbing,
            dp1.nama as nama_penguji1,
            dp2.nama as nama_penguji2
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->join('dosen dp1', 'proposal_mahasiswa.dosen_penguji_id = dp1.id', 'left');
        $this->db->join('dosen dp2', 'proposal_mahasiswa.dosen_penguji2_id = dp2.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.penetapan_oleh IS NOT NULL');
        $this->db->order_by('proposal_mahasiswa.tanggal_penetapan', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/riwayat', $data);
    }

    public function mahasiswa() {
        $data['title'] = 'Daftar Mahasiswa Prodi';
        
        $this->db->from('mahasiswa');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->order_by('nim', 'ASC');
        $data['mahasiswa_list'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/mahasiswa', $data);
    }

    public function dosen() {
        $data['title'] = 'Daftar Seluruh Dosen';
        
        $this->db->from('dosen');
        $this->db->where('level !=', '1');
        $this->db->order_by('nama', 'ASC');
        $data['dosen_list'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/dosen', $data);
    }
    
    public function laporan() {
        $data['title'] = 'Rekapitulasi Laporan';

        // 1. Ambil data proposal yang menunggu penetapan (status = 0)
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.status', '0');
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        $data['proposals_menunggu'] = $this->db->get()->result();

        // 2. Ambil data riwayat proposal yang sudah ditetapkan
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            d1.nama as nama_pembimbing,
            dp1.nama as nama_penguji1,
            dp2.nama as nama_penguji2
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->join('dosen dp1', 'proposal_mahasiswa.dosen_penguji_id = dp1.id', 'left');
        $this->db->join('dosen dp2', 'proposal_mahasiswa.dosen_penguji2_id = dp2.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.penetapan_oleh IS NOT NULL');
        $this->db->order_by('proposal_mahasiswa.tanggal_penetapan', 'DESC');
        $data['proposals_riwayat'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/laporan', $data);
    }

    private function _send_notification($proposal_id) {
        // Implementasi email notification
    }

    public function profil() {
        $data['title'] = 'Profil Kaprodi';
        $data['user'] = $this->db->get_where('dosen', array('id' => $this->session->userdata('id')))->row();
        $this->load->view('kaprodi/profil', $data);
    }
}