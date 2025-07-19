<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Publikasi extends CI_Controller {

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
        $data['title'] = 'Publikasi Tugas Akhir';
        
        // Ambil data proposal yang sudah dalam tahap publikasi
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa, 
                          mahasiswa.email as email_mahasiswa, prodi.nama as nama_prodi,
                          dosen.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->join('dosen', 'proposal_mahasiswa.dosen_id = dosen.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.workflow_status', 'publikasi');
        $this->db->order_by('proposal_mahasiswa.created_at', 'DESC');
        
        $data['publikasi_list'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/publikasi', $data);
    }

    public function detail($proposal_id) {
        $data['title'] = 'Detail Publikasi';
        
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
            redirect('kaprodi/publikasi');
        }
        
        $this->load->view('kaprodi/publikasi_detail', $data);
    }

    public function validasi() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/publikasi');
        }
        
        $proposal_id = $this->input->post('proposal_id');
        $status = $this->input->post('status'); // 'approve' atau 'reject'
        $komentar = $this->input->post('komentar_kaprodi');
        $link_repository = $this->input->post('link_repository');
        
        $update_data = [
            'status_publikasi' => ($status == 'approve') ? '1' : '2',
            'komentar_publikasi' => $komentar,
            'tanggal_review_publikasi' => date('Y-m-d H:i:s')
        ];
        
        if ($status == 'approve') {
            $update_data['link_repository'] = $link_repository;
            $update_data['workflow_status'] = 'selesai';
        }
        
        $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $update_data);
        
        $message = ($status == 'approve') ? 'Publikasi disetujui! Tugas akhir selesai.' : 'Publikasi ditolak!';
        $this->session->set_flashdata('success', $message);
        
        redirect('kaprodi/publikasi');
    }
}