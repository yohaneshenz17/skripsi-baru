<?php
// ========================================
// FILE: application/controllers/dosen/Seminar_proposal.php (SUPER DEFENSIVE)
// ========================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Seminar_proposal extends CI_Controller {

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
        $data['title'] = 'Seminar Proposal - Rekomendasi & Penilaian';
        $dosen_id = $this->session->userdata('id');
        $data['seminar_proposals'] = [];
        $data['jadwal_sebagai_penguji'] = [];
        
        // Method 1: Coba ambil dari tabel seminar_proposal jika ada
        if ($this->db->table_exists('seminar_proposal')) {
            try {
                $this->db->select('
                    sp.id, sp.proposal_id, sp.created_at, sp.rekomendasi_pembimbing,
                    pm.judul, pm.dosen_id,
                    m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                    p.nama as nama_prodi
                ');
                $this->db->from('seminar_proposal sp');
                $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->join('prodi p', 'm.prodi_id = p.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->order_by('sp.created_at', 'DESC');
                
                $data['seminar_proposals'] = $this->db->get()->result();
            } catch (Exception $e) {
                log_message('error', 'Seminar Proposal Query Error: ' . $e->getMessage());
                $data['seminar_proposals'] = [];
            }
        }
        
        // Method 2: Jika tabel tidak ada atau kosong, ambil dari proposal_mahasiswa
        if (empty($data['seminar_proposals'])) {
            try {
                // Cek kolom yang tersedia di tabel proposal_mahasiswa
                $fields = $this->db->list_fields('proposal_mahasiswa');
                $order_field = 'pm.id'; // default fallback
                
                if (in_array('updated_at', $fields)) {
                    $order_field = 'pm.updated_at';
                } elseif (in_array('created_at', $fields)) {
                    $order_field = 'pm.created_at';
                } elseif (in_array('tanggal_penetapan', $fields)) {
                    $order_field = 'pm.tanggal_penetapan';
                }
                
                $this->db->select('
                    pm.id, pm.judul, pm.dosen_id, pm.workflow_status,
                    m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                    p.nama as nama_prodi
                ');
                
                // Tambahkan kolom tanggal jika ada
                if (in_array('created_at', $fields)) {
                    $this->db->select('pm.created_at', FALSE);
                }
                if (in_array('updated_at', $fields)) {
                    $this->db->select('pm.updated_at', FALSE);
                }
                
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->join('prodi p', 'm.prodi_id = p.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('pm.status_pembimbing', '1'); // Sudah disetujui sebagai pembimbing
                
                // Filter workflow status jika kolom ada
                if (in_array('workflow_status', $fields)) {
                    $this->db->where('pm.workflow_status', 'seminar_proposal');
                }
                
                $this->db->order_by($order_field, 'DESC');
                $this->db->limit(50); // Batasi hasil
                
                $proposals = $this->db->get()->result();
                
                // Format data agar compatible dengan view
                foreach ($proposals as $proposal) {
                    $proposal->rekomendasi_pembimbing = null; // Default belum ada rekomendasi
                    if (!isset($proposal->created_at)) {
                        $proposal->created_at = date('Y-m-d H:i:s'); // Default timestamp
                    }
                }
                
                $data['seminar_proposals'] = $proposals;
                
            } catch (Exception $e) {
                log_message('error', 'Proposal Mahasiswa Query Error: ' . $e->getMessage());
                $data['seminar_proposals'] = [];
            }
        }
        
        $this->load->view('dosen/seminar_proposal', $data);
    }

    public function detail($proposal_id) {
        $data['title'] = 'Detail Seminar Proposal';
        $this->session->set_flashdata('info', 'Fitur detail akan tersedia setelah sistem lengkap.');
        redirect('dosen/seminar_proposal');
    }

    public function rekomendasi() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/seminar_proposal');
            return;
        }
        
        $proposal_id = $this->input->post('seminar_id');
        $rekomendasi = $this->input->post('rekomendasi');
        $catatan_pembimbing = $this->input->post('catatan_pembimbing');
        
        if (empty($proposal_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Update workflow_status di proposal_mahasiswa
        try {
            $fields = $this->db->list_fields('proposal_mahasiswa');
            $update_data = [];
            
            if (in_array('workflow_status', $fields)) {
                $update_data['workflow_status'] = ($rekomendasi == '1') ? 'seminar_proposal_approved' : 'seminar_proposal_revision';
            }
            
            if (in_array('catatan_dosen', $fields)) {
                $update_data['catatan_dosen'] = $catatan_pembimbing;
            }
            
            if (in_array('updated_at', $fields)) {
                $update_data['updated_at'] = date('Y-m-d H:i:s');
            }
            
            if (!empty($update_data)) {
                $this->db->where('id', $proposal_id);
                $result = $this->db->update('proposal_mahasiswa', $update_data);
                
                if ($result) {
                    $message = ($rekomendasi == '1') ? 
                        'Seminar proposal berhasil direkomendasikan!' : 
                        'Rekomendasi diberikan dengan catatan perbaikan!';
                    $this->session->set_flashdata('success', $message);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyimpan rekomendasi!');
                }
            } else {
                $this->session->set_flashdata('info', 'Struktur database tidak mendukung penyimpanan rekomendasi.');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Update Rekomendasi Error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem!');
        }
        
        redirect('dosen/seminar_proposal');
    }

    public function input_nilai() {
        $this->session->set_flashdata('info', 'Fitur input nilai akan tersedia setelah tabel seminar_proposal dibuat.');
        redirect('dosen/seminar_proposal');
    }

    public function berita_acara($seminar_id) {
        $this->session->set_flashdata('info', 'Fitur berita acara akan tersedia setelah tabel seminar_proposal dibuat.');
        redirect('dosen/seminar_proposal');
    }
}

?>