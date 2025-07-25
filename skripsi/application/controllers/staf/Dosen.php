<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Daftar Dosen (PERBAIKAN: No Photo, Simple Display)
 * File: application/controllers/staf/Dosen.php
 * FOKUS: Hanya Dosen Level 2, tanpa foto profil
 */
class Dosen extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);

        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Daftar Dosen';
        
        try {
            // Get filter parameters
            $search = $this->input->get('search');
            $prodi_id = $this->input->get('prodi_id');
            
            // PERBAIKAN: Query yang lebih sederhana, fokus pada data penting
            $this->db->select('
                d.id,
                d.nip,
                d.nama,
                d.email,
                d.nomor_telepon,
                d.level
            ');
            $this->db->from('dosen d');
            $this->db->where('d.level', '2'); // HANYA DOSEN AKTIF
            
            // Apply search filter
            if ($search) {
                $this->db->group_start();
                $this->db->like('d.nama', $search);
                $this->db->or_like('d.nip', $search);
                $this->db->or_like('d.email', $search);
                $this->db->group_end();
            }
            
            $this->db->order_by('d.nama', 'ASC');
            $dosen_raw = $this->db->get()->result();
            
            // PERBAIKAN: Process data in PHP untuk menambah informasi tambahan
            $data['dosen_list'] = [];
            foreach ($dosen_raw as $dsn) {
                // Get total bimbingan aktif
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('dosen_id', $dsn->id);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $dsn->total_bimbingan = $result ? $result->total : 0;
                
                // Check if dosen is kaprodi
                $this->db->select('p.nama as nama_prodi');
                $this->db->from('prodi p');
                $this->db->where('p.dosen_id', $dsn->id);
                $this->db->limit(1);
                $result = $this->db->get()->row();
                $dsn->prodi_kelola = $result ? $result->nama_prodi : null;
                $dsn->is_kaprodi = $result ? true : false;
                
                // Status dosen (untuk badge)
                $dsn->status_dosen = $dsn->is_kaprodi ? 'Kaprodi' : 'Dosen';
                
                $data['dosen_list'][] = $dsn;
            }
            
            // Get prodi list for filter
            $data['prodi_list'] = $this->db->get('prodi')->result();
            
            // PERBAIKAN: Statistik yang lebih sederhana
            $data['statistics'] = $this->_get_simple_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in Dosen controller: ' . $e->getMessage());
            $data['dosen_list'] = [];
            $data['prodi_list'] = [];
            $data['statistics'] = [
                'total_dosen' => 0,
                'total_kaprodi' => 0,
                'total_membimbing' => 0,
                'total_aktif' => 0
            ];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data dosen.');
        }
        
        $this->load->view('staf/dosen/index', $data);
    }
    
    /**
     * PERBAIKAN: Statistik yang disederhanakan
     */
    private function _get_simple_statistics() {
        try {
            // Total dosen aktif (level 2)
            $this->db->where('level', '2');
            $total_dosen = $this->db->count_all_results('dosen');
            
            // Total kaprodi (dosen yang mengelola prodi)
            $this->db->select('COUNT(DISTINCT p.dosen_id) as total');
            $this->db->from('prodi p');
            $this->db->join('dosen d', 'p.dosen_id = d.id', 'inner');
            $this->db->where('d.level', '2');
            $result = $this->db->get()->row();
            $total_kaprodi = $result ? $result->total : 0;
            
            // Total dosen yang sedang membimbing
            $this->db->select('COUNT(DISTINCT pm.dosen_id) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'inner');
            $this->db->where('d.level', '2');
            $this->db->where('pm.status_pembimbing', '1');
            $result = $this->db->get()->row();
            $total_membimbing = $result ? $result->total : 0;
            
            // Dosen yang tidak sedang membimbing
            $total_tidak_membimbing = $total_dosen - $total_membimbing;
            
            return [
                'total_dosen' => $total_dosen,
                'total_kaprodi' => $total_kaprodi,
                'total_membimbing' => $total_membimbing,
                'total_tidak_membimbing' => $total_tidak_membimbing
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen statistics: ' . $e->getMessage());
            return [
                'total_dosen' => 0,
                'total_kaprodi' => 0,
                'total_membimbing' => 0,
                'total_tidak_membimbing' => 0
            ];
        }
    }
    
    /**
     * Export data dosen ke Excel
     */
    public function export() {
        try {
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="daftar_dosen_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Get search parameter
            $search = $this->input->get('search');
            
            // Get all dosen data - HANYA LEVEL 2
            $this->db->select('
                d.nip,
                d.nama,
                d.email,
                d.nomor_telepon
            ');
            $this->db->from('dosen d');
            $this->db->where('d.level', '2'); // HANYA DOSEN
            
            if ($search) {
                $this->db->group_start();
                $this->db->like('d.nama', $search);
                $this->db->or_like('d.nip', $search);
                $this->db->or_like('d.email', $search);
                $this->db->group_end();
            }
            
            $this->db->order_by('d.nama');
            $dosen_list = $this->db->get()->result();
            
            // Process additional data
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
                echo "<td>" . htmlspecialchars($dsn->prodi_kelola) . "</td>";
                echo "<td>" . $dsn->total_bimbingan . "</td>";
                echo "</tr>";
                $no++;
            }
            echo "</table>";
            
        } catch (Exception $e) {
            log_message('error', 'Error exporting dosen data: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mengexport data dosen.');
            redirect('staf/dosen');
        }
    }

    /**
     * REMOVED: Detail dan fungsi lain yang tidak diperlukan
     * Fokus pada view dan export data saja
     */
}

/* End of file Dosen.php */
/* Location: ./application/controllers/staf/Dosen.php */