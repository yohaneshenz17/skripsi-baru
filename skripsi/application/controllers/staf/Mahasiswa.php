<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Daftar Mahasiswa (SIMPLIFIED - No Action Column)
 * File: application/controllers/staf/Mahasiswa.php
 * PERBAIKAN: Simplified table dengan kolom: NIM + Nama + Prodi + Jenis Kelamin + Email + Status Workflow
 */
class Mahasiswa extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        $this->load->library('form_validation');

        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Daftar Mahasiswa';
        
        try {
            // Get filter parameters
            $prodi_id = $this->input->get('prodi_id');
            $status = $this->input->get('status');
            $search = $this->input->get('search');
            
            // PERBAIKAN: Query yang lebih sederhana dan fokus pada kolom yang dibutuhkan
            $this->db->select('
                m.id,
                m.nim,
                m.nama,
                m.email,
                m.jenis_kelamin,
                m.status,
                p.nama as nama_prodi,
                p.kode as kode_prodi
            ');
            
            // PERBAIKAN: Tambahkan status workflow dari proposal_mahasiswa
            $this->db->select('
                (SELECT workflow_status 
                 FROM proposal_mahasiswa pm 
                 WHERE pm.mahasiswa_id = m.id 
                 ORDER BY pm.created_at DESC 
                 LIMIT 1) as current_workflow
            ', FALSE);
            
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            
            // Apply filters
            if ($prodi_id) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            
            if ($status) {
                $this->db->where('m.status', $status);
            }
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('m.nama', $search);
                $this->db->or_like('m.nim', $search);
                $this->db->or_like('m.email', $search);
                $this->db->group_end();
            }
            
            // PERBAIKAN: Order by nama untuk konsistensi
            $this->db->order_by('m.nama', 'ASC');
            $data['mahasiswa_list'] = $this->db->get()->result();
            
            // Get prodi list for filter
            $data['prodi_list'] = $this->db->get('prodi')->result();
            
            // PERBAIKAN: Statistik yang lebih sederhana
            $data['statistics'] = $this->_get_simple_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in Mahasiswa controller: ' . $e->getMessage());
            $data['mahasiswa_list'] = [];
            $data['prodi_list'] = [];
            $data['statistics'] = [
                'total' => 0,
                'aktif' => 0,
                'tidak_aktif' => 0
            ];
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data mahasiswa.');
        }
        
        $this->load->view('staf/mahasiswa/index', $data);
    }

    /**
     * PERBAIKAN: Statistik yang disederhanakan
     */
    private function _get_simple_statistics() {
        try {
            // Total mahasiswa
            $total = $this->db->count_all_results('mahasiswa');
            
            // Mahasiswa aktif
            $this->db->where('status', 'aktif');
            $aktif = $this->db->count_all_results('mahasiswa');
            
            // Mahasiswa tidak aktif/nonaktif
            $this->db->where('status !=', 'aktif');
            $tidak_aktif = $this->db->count_all_results('mahasiswa');
            
            // Status workflow distribution
            $workflow_stats = $this->db->query("
                SELECT 
                    workflow_status, 
                    COUNT(*) as count 
                FROM proposal_mahasiswa 
                WHERE workflow_status IS NOT NULL 
                GROUP BY workflow_status
            ")->result();
            
            $workflow_distribution = [];
            foreach ($workflow_stats as $stat) {
                $workflow_distribution[$stat->workflow_status] = $stat->count;
            }
            
            return [
                'total' => $total,
                'aktif' => $aktif,
                'tidak_aktif' => $tidak_aktif,
                'workflow' => $workflow_distribution
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return [
                'total' => 0,
                'aktif' => 0,
                'tidak_aktif' => 0,
                'workflow' => []
            ];
        }
    }

    /**
     * Export data mahasiswa ke Excel (OPTIONAL)
     */
    public function export() {
        // Load library untuk export Excel jika diperlukan
        $this->load->library('excel');
        
        try {
            // Get data mahasiswa
            $this->db->select('
                m.nim,
                m.nama,
                m.email,
                m.jenis_kelamin,
                m.status,
                p.nama as nama_prodi
            ');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->order_by('m.nama', 'ASC');
            
            $mahasiswa_data = $this->db->get()->result();
            
            // Set headers untuk download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="data_mahasiswa_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Output Excel content
            echo "NIM\tNama Mahasiswa\tProgram Studi\tJenis Kelamin\tEmail\tStatus\n";
            
            foreach ($mahasiswa_data as $mhs) {
                echo "{$mhs->nim}\t{$mhs->nama}\t{$mhs->nama_prodi}\t{$mhs->jenis_kelamin}\t{$mhs->email}\t{$mhs->status}\n";
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error exporting mahasiswa data: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mengexport data mahasiswa.');
            redirect('staf/mahasiswa');
        }
    }

    /**
     * REMOVED: Detail, edit, dan delete functions karena tidak diperlukan action column
     * Hanya fokus pada view dan export data
     */
}

/* End of file Mahasiswa.php */
/* Location: ./application/controllers/staf/Mahasiswa.php */