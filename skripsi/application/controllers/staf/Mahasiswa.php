<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Daftar Mahasiswa (VERIFIED untuk database yang ada)
 * File: application/controllers/staf/Mahasiswa.php
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
            
            // Build query - DISESUAIKAN dengan database yang ada
            $this->db->select('
                m.*,
                p.nama as nama_prodi,
                p.kode as kode_prodi,
                (SELECT COUNT(*) FROM proposal_mahasiswa pm WHERE pm.mahasiswa_id = m.id) as total_proposal
            ');
            
            // Cek apakah field workflow_status ada
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->select('(SELECT workflow_status FROM proposal_mahasiswa pm WHERE pm.mahasiswa_id = m.id ORDER BY pm.created_at DESC LIMIT 1) as current_workflow', FALSE);
            } else {
                $this->db->select('NULL as current_workflow', FALSE);
            }
            
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
            
            $this->db->order_by('m.nama', 'ASC');
            $data['mahasiswa_list'] = $this->db->get()->result();
            
            // Get prodi list for filter
            $data['prodi_list'] = $this->db->get('prodi')->result();
            
            // Get statistics
            $data['statistics'] = $this->_get_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in Mahasiswa controller: ' . $e->getMessage());
            $data['mahasiswa_list'] = [];
            $data['prodi_list'] = [];
            $data['statistics'] = ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'mengerjakan_ta' => 0];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data mahasiswa.');
        }
        
        $this->load->view('staf/mahasiswa/index', $data);
    }
    
    public function detail($mahasiswa_id) {
        $data['title'] = 'Detail Mahasiswa';
        
        try {
            // Get mahasiswa detail with prodi
            $this->db->select('m.*, p.nama as nama_prodi, p.kode as kode_prodi');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->where('m.id', $mahasiswa_id);
            $mahasiswa = $this->db->get()->row();
            
            if (!$mahasiswa) {
                $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan!');
                redirect('staf/mahasiswa');
            }
            
            $data['mahasiswa'] = $mahasiswa;
            
            // Get proposal history dengan data yang ada
            $this->db->select('
                pm.*,
                d.nama as nama_pembimbing
            ');
            
            // Cek apakah tabel jurnal_bimbingan ada
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('(SELECT COUNT(*) FROM jurnal_bimbingan jb WHERE jb.proposal_id = pm.id) as total_jurnal', FALSE);
            } else if ($this->db->table_exists('konsultasi')) {
                $this->db->select('(SELECT COUNT(*) FROM konsultasi k WHERE k.proposal_mahasiswa_id = pm.id) as total_jurnal', FALSE);
            } else {
                $this->db->select('0 as total_jurnal', FALSE);
            }
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('pm.created_at', 'DESC');
            $data['proposal_history'] = $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting mahasiswa detail: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat detail mahasiswa.');
            redirect('staf/mahasiswa');
        }
        
        $this->load->view('staf/mahasiswa/detail', $data);
    }
    
    public function progress($mahasiswa_id) {
        $data['title'] = 'Progress Tugas Akhir';
        
        try {
            // Get current proposal
            $this->db->select('
                pm.*,
                m.nama as nama_mahasiswa,
                m.nim,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('pm.created_at', 'DESC');
            $this->db->limit(1);
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Mahasiswa belum mengajukan proposal!');
                redirect('staf/mahasiswa');
            }
            
            $data['proposal'] = $proposal;
            
            // Get workflow progress
            $workflow_status = isset($proposal->workflow_status) ? $proposal->workflow_status : 'proposal';
            $data['workflow_progress'] = $this->_get_workflow_progress($workflow_status);
            
            // Get jurnal/konsultasi data
            $data['jurnal_list'] = [];
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('*');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('proposal_id', $proposal->id);
                $this->db->order_by('tanggal_bimbingan', 'DESC');
                $data['jurnal_list'] = $this->db->get()->result();
            } else if ($this->db->table_exists('konsultasi')) {
                $this->db->select('*, tanggal as tanggal_bimbingan, isi as catatan_mahasiswa');
                $this->db->from('konsultasi');
                $this->db->where('proposal_mahasiswa_id', $proposal->id);
                $this->db->order_by('tanggal', 'DESC');
                $data['jurnal_list'] = $this->db->get()->result();
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error getting progress: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat progress mahasiswa.');
            redirect('staf/mahasiswa');
        }
        
        $this->load->view('staf/mahasiswa/progress', $data);
    }
    
    public function export() {
        try {
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="daftar_mahasiswa_' . date('Y-m-d') . '.xls"');
            header('Cache-Control: max-age=0');
            
            // Get all mahasiswa data
            $this->db->select('
                m.nim,
                m.nama,
                p.nama as nama_prodi,
                m.jenis_kelamin,
                m.email,
                m.nomor_telepon,
                CASE 
                    WHEN m.status = "1" THEN "Aktif"
                    ELSE "Nonaktif"
                END as status,
                (SELECT COUNT(*) FROM proposal_mahasiswa pm WHERE pm.mahasiswa_id = m.id) as total_proposal
            ');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->order_by('p.nama, m.nama');
            $mahasiswa_list = $this->db->get()->result();
            
            // Generate Excel content
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>No</th>";
            echo "<th>NIM</th>";
            echo "<th>Nama</th>";
            echo "<th>Program Studi</th>";
            echo "<th>Jenis Kelamin</th>";
            echo "<th>Email</th>";
            echo "<th>No. Telepon</th>";
            echo "<th>Status</th>";
            echo "<th>Total Proposal</th>";
            echo "</tr>";
            
            $no = 1;
            foreach ($mahasiswa_list as $mhs) {
                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>" . htmlspecialchars($mhs->nim) . "</td>";
                echo "<td>" . htmlspecialchars($mhs->nama) . "</td>";
                echo "<td>" . htmlspecialchars($mhs->nama_prodi ?: 'Tidak Ada') . "</td>";
                echo "<td>" . ucfirst($mhs->jenis_kelamin) . "</td>";
                echo "<td>" . htmlspecialchars($mhs->email) . "</td>";
                echo "<td>" . htmlspecialchars($mhs->nomor_telepon) . "</td>";
                echo "<td>" . $mhs->status . "</td>";
                echo "<td>" . $mhs->total_proposal . "</td>";
                echo "</tr>";
                $no++;
            }
            echo "</table>";
            
        } catch (Exception $e) {
            log_message('error', 'Error exporting mahasiswa: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mengexport data mahasiswa.');
            redirect('staf/mahasiswa');
        }
    }
    
    /**
     * Get statistics mahasiswa
     */
    private function _get_statistics() {
        $stats = [
            'total' => 0,
            'aktif' => 0,
            'nonaktif' => 0,
            'mengerjakan_ta' => 0
        ];
        
        try {
            // Total mahasiswa
            $stats['total'] = $this->db->count_all('mahasiswa');
            
            // Mahasiswa aktif
            $stats['aktif'] = $this->db->where('status', '1')->count_all_results('mahasiswa');
            
            // Mahasiswa nonaktif
            $stats['nonaktif'] = $stats['total'] - $stats['aktif'];
            
            // Mahasiswa yang sedang mengerjakan TA
            $this->db->distinct();
            $this->db->select('mahasiswa_id');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_kaprodi', '1');
            $stats['mengerjakan_ta'] = $this->db->count_all_results();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get workflow progress percentage
     */
    private function _get_workflow_progress($current_status) {
        $stages = [
            'proposal' => 10,
            'bimbingan' => 30,
            'seminar_proposal' => 50,
            'penelitian' => 70,
            'seminar_skripsi' => 90,
            'publikasi' => 100,
            'selesai' => 100
        ];
        
        return isset($stages[$current_status]) ? $stages[$current_status] : 0;
    }
}

/* End of file Mahasiswa.php */