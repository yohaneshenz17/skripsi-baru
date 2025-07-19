<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Kelola Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil mahasiswa yang sedang dibimbing (status_pembimbing = 1)
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1'); // Sudah disetujui sebagai pembimbing
        $this->db->order_by('pm.created_at', 'DESC');
        
        $data['mahasiswa_bimbingan'] = $this->db->get()->result();
        
        $this->load->view('dosen/bimbingan', $data);
    }

    public function detail_mahasiswa($proposal_id) {
        $data['title'] = 'Detail Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil detail proposal dan mahasiswa
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.foto,
            p.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('dosen/bimbingan');
            return;
        }
        
        $data['proposal'] = $proposal;
        
        // Ambil jurnal bimbingan jika ada
        if ($this->db->table_exists('jurnal_bimbingan')) {
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('tanggal_bimbingan', 'DESC');
            
            $data['jurnal_bimbingan'] = $this->db->get()->result();
        } else {
            $data['jurnal_bimbingan'] = [];
        }
        
        $this->load->view('dosen/bimbingan_detail', $data);
    }

    public function validasi_jurnal() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/bimbingan');
            return;
        }
        
        $jurnal_id = $this->input->post('jurnal_id');
        $status_validasi = $this->input->post('status_validasi');
        $komentar_dosen = $this->input->post('komentar_dosen');
        
        if (empty($jurnal_id) || empty($status_validasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Update validasi jurnal
        $update_data = [
            'status_validasi' => $status_validasi,
            'komentar_dosen' => $komentar_dosen,
            'tanggal_validasi' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $jurnal_id);
        $result = $this->db->update('jurnal_bimbingan', $update_data);
        
        if ($result) {
            $message = ($status_validasi == '1') ? 'Jurnal berhasil divalidasi!' : 'Jurnal perlu diperbaiki!';
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Gagal memvalidasi jurnal!');
        }
        
        redirect('dosen/bimbingan');
    }

    public function tambah_jurnal() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/bimbingan');
            return;
        }
        
        $proposal_id = $this->input->post('proposal_id');
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = $this->input->post('materi_bimbingan');
        $catatan_mahasiswa = $this->input->post('catatan_mahasiswa');
        
        if (empty($proposal_id) || empty($tanggal_bimbingan) || empty($materi_bimbingan)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Insert jurnal bimbingan
        $insert_data = [
            'proposal_id' => $proposal_id,
            'tanggal_bimbingan' => $tanggal_bimbingan,
            'materi_bimbingan' => $materi_bimbingan,
            'catatan_mahasiswa' => $catatan_mahasiswa,
            'status_validasi' => '1', // Langsung tervalidasi karena dibuat dosen
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->insert('jurnal_bimbingan', $insert_data);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan jurnal bimbingan!');
        }
        
        redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal_id);
    }

    public function export_jurnal($proposal_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Verify access
        $this->db->select('pm.*, m.nama as nama_mahasiswa, m.nim');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Generate PDF atau download logic di sini
        $this->session->set_flashdata('info', 'Fitur export jurnal akan segera tersedia!');
        redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal_id);
    }
}