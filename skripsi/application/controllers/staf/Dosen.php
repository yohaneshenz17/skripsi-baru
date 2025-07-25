<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Daftar Dosen (FOKUS Hanya Dosen Level 2)
 * File: application/controllers/staf/Dosen.php
 */
class Dosen extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);

        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || !in_array($this->session->userdata('level'), ['1', '2', '5'])) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Daftar Dosen';
        
        try {
            // Get filter parameters
            $search = $this->input->get('search');
            
            // QUERY HANYA UNTUK DOSEN LEVEL 2 - TIDAK TERMASUK ADMIN
            $this->db->select('d.*');
            $this->db->from('dosen d');
            $this->db->where('d.level', '2'); // HANYA DOSEN
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('d.nama', $search);
                $this->db->or_like('d.nip', $search);
                $this->db->or_like('d.email', $search);
                $this->db->group_end();
            }
            
            $this->db->order_by('d.nama', 'ASC');
            $dosen_raw = $this->db->get()->result();
            
            // Process data in PHP
            $data['dosen_list'] = [];
            foreach ($dosen_raw as $dsn) {
                // Set level name (semua adalah dosen)
                $dsn->level_name = 'Dosen';
                
                // Get bimbingan count
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('dosen_id', $dsn->id);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $dsn->total_bimbingan = $result ? $result->total : 0;
                
                // Check if kaprodi (tapi tidak akan ditampilkan di list)
                $this->db->select('COUNT(*) as total');
                $this->db->from('prodi');
                $this->db->where('dosen_id', $dsn->id);
                $result = $this->db->get()->row();
                $dsn->is_kaprodi = $result ? $result->total : 0;
                
                $data['dosen_list'][] = $dsn;
            }
            
            // Get statistics
            $data['statistics'] = $this->_get_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in Dosen controller: ' . $e->getMessage());
            $data['dosen_list'] = [];
            $data['statistics'] = ['total_dosen' => 0, 'total_kaprodi' => 0, 'total_membimbing' => 0];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data dosen: ' . $e->getMessage());
        }
        
        $this->load->view('staf/dosen/index', $data);
    }
    
    public function export() {
        try {
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="daftar_dosen_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Get all dosen data - HANYA LEVEL 2
            $this->db->select('d.*');
            $this->db->from('dosen d');
            $this->db->where('d.level', '2'); // HANYA DOSEN
            $this->db->order_by('d.nama');
            $dosen_list = $this->db->get()->result();
            
            // Process in PHP
            foreach ($dosen_list as $dsn) {
                // Get bimbingan count
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('dosen_id', $dsn->id);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $dsn->total_bimbingan = $result ? $result->total : 0;
                
                // Get prodi kelola
                $this->db->select('nama');
                $this->db->from('prodi');
                $this->db->where('dosen_id', $dsn->id);
                $this->db->limit(1);
                $result = $this->db->get()->row();
                $dsn->prodi_kelola = $result ? $result->nama : '';
            }
            
            // Generate Excel content
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>No</th>";
            echo "<th>NIP</th>";
            echo "<th>Nama Dosen</th>";
            echo "<th>Email</th>";
            echo "<th>No. Telepon</th>";
            echo "<th>Program Studi (Jika Kaprodi)</th>";
            echo "<th>Total Bimbingan</th>";
            echo "</tr>";
            
            $no = 1;
            foreach ($dosen_list as $dsn) {
                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>" . htmlspecialchars($dsn->nip) . "</td>";
                echo "<td>" . htmlspecialchars($dsn->nama) . "</td>";
                echo "<td>" . htmlspecialchars($dsn->email) . "</td>";
                echo "<td>" . htmlspecialchars($dsn->nomor_telepon) . "</td>";
                echo "<td>" . htmlspecialchars($dsn->prodi_kelola ?: '-') . "</td>";
                echo "<td>" . $dsn->total_bimbingan . "</td>";
                echo "</tr>";
                $no++;
            }
            echo "</table>";
            
        } catch (Exception $e) {
            log_message('error', 'Error exporting dosen: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mengexport data dosen.');
            redirect('staf/dosen');
        }
    }
    
    /**
     * Get statistics dosen - FOKUS PADA DATA YANG RELEVAN
     */
    private function _get_statistics() {
        $stats = [
            'total_dosen' => 0,
            'total_kaprodi' => 0,
            'total_membimbing' => 0
        ];
        
        try {
            // Total dosen aktif (level 2) - SESUAI PERMINTAAN
            $this->db->where('level', '2');
            $stats['total_dosen'] = $this->db->count_all_results('dosen');
            
            // Total kaprodi (dosen yang mengelola prodi)
            $this->db->distinct();
            $this->db->select('dosen_id');
            $this->db->from('prodi');
            $stats['total_kaprodi'] = $this->db->count_all_results();
            
            // Dosen yang sedang membimbing
            $this->db->distinct();
            $this->db->select('dosen_id');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_pembimbing', '1');
            $this->db->where('dosen_id IS NOT NULL');
            $stats['total_membimbing'] = $this->db->count_all_results();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen statistics: ' . $e->getMessage());
        }
        
        return $stats;
    }
}

/* End of file Dosen.php */