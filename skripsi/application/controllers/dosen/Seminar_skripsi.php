<?php
// ========================================
// FILE: application/controllers/dosen/Seminar_skripsi.php (SUPER DEFENSIVE)
// ========================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Seminar_skripsi extends CI_Controller {

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
        $data['title'] = 'Seminar Skripsi - Ujian Akhir';
        $dosen_id = $this->session->userdata('id');
        $data['seminar_skripsi'] = [];
        $data['jadwal_sebagai_penguji'] = [];
        
        try {
            // Cek kolom yang tersedia
            $fields = $this->db->list_fields('proposal_mahasiswa');
            $order_field = 'pm.id';
            
            if (in_array('updated_at', $fields)) {
                $order_field = 'pm.updated_at';
            } elseif (in_array('created_at', $fields)) {
                $order_field = 'pm.created_at';
            }
            
            $this->db->select('
                pm.id, pm.judul, pm.dosen_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                p.nama as nama_prodi
            ');
            
            if (in_array('created_at', $fields)) {
                $this->db->select('pm.created_at', FALSE);
            }
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            
            if (in_array('workflow_status', $fields)) {
                $this->db->where('pm.workflow_status', 'seminar_skripsi');
            }
            
            $this->db->order_by($order_field, 'DESC');
            $this->db->limit(50);
            
            $proposals = $this->db->get()->result();
            
            foreach ($proposals as $proposal) {
                $proposal->rekomendasi_pembimbing = null;
                $proposal->tanggal_seminar = null;
                if (!isset($proposal->created_at)) {
                    $proposal->created_at = date('Y-m-d H:i:s');
                }
            }
            
            $data['seminar_skripsi'] = $proposals;
            
        } catch (Exception $e) {
            log_message('error', 'Seminar Skripsi Query Error: ' . $e->getMessage());
            $data['seminar_skripsi'] = [];
        }
        
        $this->load->view('dosen/seminar_skripsi', $data);
    }

    public function detail($seminar_id) {
        $this->session->set_flashdata('info', 'Fitur detail akan tersedia setelah sistem lengkap.');
        redirect('dosen/seminar_skripsi');
    }

    public function rekomendasi() {
        // Implementation serupa dengan seminar_proposal
        if ($this->input->method() !== 'post') {
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $rekomendasi = $this->input->post('rekomendasi');
        $catatan_pembimbing = $this->input->post('catatan_pembimbing');
        
        if (empty($seminar_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        try {
            $fields = $this->db->list_fields('proposal_mahasiswa');
            $update_data = [];
            
            if (in_array('workflow_status', $fields)) {
                $update_data['workflow_status'] = ($rekomendasi == '1') ? 'seminar_skripsi_approved' : 'seminar_skripsi_revision';
            }
            
            if (in_array('catatan_dosen', $fields)) {
                $update_data['catatan_dosen'] = $catatan_pembimbing;
            }
            
            if (!empty($update_data)) {
                $this->db->where('id', $seminar_id);
                $result = $this->db->update('proposal_mahasiswa', $update_data);
                
                if ($result) {
                    $message = ($rekomendasi == '1') ? 
                        'Seminar skripsi berhasil direkomendasikan!' : 
                        'Rekomendasi diberikan dengan catatan perbaikan!';
                    $this->session->set_flashdata('success', $message);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyimpan rekomendasi!');
                }
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem!');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    public function input_nilai() {
        $this->session->set_flashdata('info', 'Fitur input nilai akan tersedia setelah tabel seminar_skripsi dibuat.');
        redirect('dosen/seminar_skripsi');
    }

    public function berita_acara($seminar_id) {
        $this->session->set_flashdata('info', 'Fitur berita acara akan tersedia setelah tabel seminar_skripsi dibuat.');
        redirect('dosen/seminar_skripsi');
    }
}

?>