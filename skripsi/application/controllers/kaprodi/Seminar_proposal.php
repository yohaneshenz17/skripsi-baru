<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan role kaprodi
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Ambil prodi_id dari session
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            $prodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($prodi) {
                $this->session->set_userdata('prodi_id', $prodi->id);
                $this->prodi_id = $prodi->id;
            }
        }
    }

    public function index() {
        $data['title'] = 'Seminar Proposal';
        
        // Ambil data proposal yang sudah dalam tahap seminar_proposal
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa, 
                          mahasiswa.email as email_mahasiswa, prodi.nama as nama_prodi,
                          dosen.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->join('dosen', 'proposal_mahasiswa.dosen_id = dosen.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.workflow_status', 'seminar_proposal');
        $this->db->order_by('proposal_mahasiswa.created_at', 'DESC');
        
        $data['seminar_proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/seminar_proposal', $data);
    }

    public function detail($proposal_id) {
        $data['title'] = 'Detail Seminar Proposal';
        
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa, 
                          mahasiswa.email as email_mahasiswa, prodi.nama as nama_prodi,
                          dosen.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->join('dosen', 'proposal_mahasiswa.dosen_id = dosen.id', 'left');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            redirect('kaprodi/seminar_proposal');
        }
        
        // Ambil daftar dosen untuk penguji
        $data['dosen_list'] = $this->db->get('dosen')->result();
        
        $this->load->view('kaprodi/seminar_proposal_detail', $data);
    }

    public function validasi() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
        }
        
        $proposal_id = $this->input->post('proposal_id');
        $status = $this->input->post('status'); // 'approve' atau 'reject'
        $komentar = $this->input->post('komentar_kaprodi');
        $dosen_penguji_id = $this->input->post('dosen_penguji_id');
        $tanggal_seminar = $this->input->post('tanggal_seminar');
        
        $update_data = [
            'status_seminar_proposal' => ($status == 'approve') ? '1' : '2',
            'komentar_seminar_proposal' => $komentar,
            'tanggal_review_seminar_proposal' => date('Y-m-d H:i:s')
        ];
        
        if ($status == 'approve') {
            $update_data['dosen_penguji_id'] = $dosen_penguji_id;
            $update_data['tanggal_seminar_proposal'] = $tanggal_seminar;
            $update_data['workflow_status'] = 'penelitian';
        }
        
        $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $update_data);
        
        $message = ($status == 'approve') ? 'Seminar proposal disetujui!' : 'Seminar proposal ditolak!';
        $this->session->set_flashdata('success', $message);
        
        redirect('kaprodi/seminar_proposal');
    }
}