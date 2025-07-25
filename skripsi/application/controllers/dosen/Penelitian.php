<?php
// ========================================
// FILE: application/controllers/dosen/Penelitian.php (SUPER DEFENSIVE)
// ========================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Penelitian extends CI_Controller {

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
        $data['title'] = 'Penelitian - Surat Izin Penelitian';
        $dosen_id = $this->session->userdata('id');
        $data['surat_izin_penelitian'] = [];
        
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
                pm.id, pm.judul, pm.dosen_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                p.nama as nama_prodi
            ');
            
            // Tambahkan kolom lokasi_penelitian jika ada
            if (in_array('lokasi_penelitian', $fields)) {
                $this->db->select('pm.lokasi_penelitian', FALSE);
            }
            
            // Tambahkan kolom tanggal jika ada
            if (in_array('created_at', $fields)) {
                $this->db->select('pm.created_at', FALSE);
            }
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.status_pembimbing', '1');
            
            // Filter workflow status jika kolom ada
            if (in_array('workflow_status', $fields)) {
                $this->db->where('pm.workflow_status', 'penelitian');
            }
            
            $this->db->order_by($order_field, 'DESC');
            $this->db->limit(50);
            
            $proposals = $this->db->get()->result();
            
            // Format data agar compatible dengan view
            foreach ($proposals as $proposal) {
                $proposal->rekomendasi_pembimbing = null;
                $proposal->surat_izin_file = null;
                if (!isset($proposal->lokasi_penelitian)) {
                    $proposal->lokasi_penelitian = 'Belum ditentukan';
                }
                if (!isset($proposal->created_at)) {
                    $proposal->created_at = date('Y-m-d H:i:s');
                }
            }
            
            $data['surat_izin_penelitian'] = $proposals;
            
        } catch (Exception $e) {
            log_message('error', 'Penelitian Query Error: ' . $e->getMessage());
            $data['surat_izin_penelitian'] = [];
        }
        
        $this->load->view('dosen/penelitian', $data);
    }

    public function detail($surat_id) {
        $this->session->set_flashdata('info', 'Fitur detail akan tersedia setelah sistem lengkap.');
        redirect('dosen/penelitian');
    }

    public function rekomendasi() {
        // Implementation serupa dengan seminar_proposal
        if ($this->input->method() !== 'post') {
            redirect('dosen/penelitian');
            return;
        }
        
        $surat_id = $this->input->post('surat_id');
        $rekomendasi = $this->input->post('rekomendasi');
        $catatan_pembimbing = $this->input->post('catatan_pembimbing');
        
        if (empty($surat_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/penelitian');
            return;
        }
        
        try {
            $fields = $this->db->list_fields('proposal_mahasiswa');
            $update_data = [];
            
            if (in_array('workflow_status', $fields)) {
                $update_data['workflow_status'] = ($rekomendasi == '1') ? 'penelitian_approved' : 'penelitian_revision';
            }
            
            if (in_array('catatan_dosen', $fields)) {
                $update_data['catatan_dosen'] = $catatan_pembimbing;
            }
            
            if (!empty($update_data)) {
                $this->db->where('id', $surat_id);
                $result = $this->db->update('proposal_mahasiswa', $update_data);
                
                if ($result) {
                    $message = ($rekomendasi == '1') ? 
                        'Surat izin penelitian berhasil direkomendasikan!' : 
                        'Rekomendasi diberikan dengan catatan perbaikan!';
                    $this->session->set_flashdata('success', $message);
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyimpan rekomendasi!');
                }
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem!');
        }
        
        redirect('dosen/penelitian');
    }
}

?>