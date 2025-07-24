<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Controller untuk Staf - Data Bimbingan
 * File: application/controllers/api/Staf.php
 * Mendukung AJAX calls untuk DataTables dan operasi lainnya
 */
class Staf extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Cek login staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }
    }

    /**
     * Get data bimbingan untuk DataTables
     */
    public function bimbingan() {
        try {
            // Parameters dari DataTables
            $draw = intval($this->input->post('draw', true));
            $start = intval($this->input->post('start', true));
            $length = intval($this->input->post('length', true));
            
            // Filter parameters
            $prodi_id = $this->input->post('prodi_id', true);
            $dosen_id = $this->input->post('dosen_id', true);
            $status_validasi = $this->input->post('status_validasi', true);
            $periode = $this->input->post('periode', true);
            
            // Search parameter
            $search_value = $this->input->post('search')['value'] ?? '';
            
            // Base query
            $this->db->select('
                pm.id,
                pm.judul as judul_proposal,
                pm.workflow_status,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                COUNT(jb.id) as total_pertemuan,
                SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as pertemuan_valid,
                SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as pertemuan_pending
            ');
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
            
            // Base filters
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->where_in('pm.workflow_status', [
                'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'
            ]);
            
            // Additional filters
            if ($prodi_id && is_numeric($prodi_id)) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            if ($dosen_id && is_numeric($dosen_id)) {
                $this->db->where('pm.dosen_id', $dosen_id);
            }
            if ($status_validasi !== '' && in_array($status_validasi, ['0', '1', '2'])) {
                $this->db->where('jb.status_validasi', $status_validasi);
            }
            
            // Search filter
            if ($search_value && strlen(trim($search_value)) > 0) {
                $search_term = trim($search_value);
                $this->db->group_start();
                $this->db->like('m.nama', $search_term);
                $this->db->or_like('m.nim', $search_term);
                $this->db->or_like('pm.judul', $search_term);
                $this->db->or_like('d.nama', $search_term);
                $this->db->group_end();
            }
            
            $this->db->group_by('pm.id');
            
            // Count total records
            $total_query = clone $this->db;
            $total_records = $total_query->count_all_results('', false);
            
            // Apply pagination
            if ($length != -1) {
                $this->db->limit($length, $start);
            }
            
            // Order by
            $this->db->order_by('pm.created_at', 'DESC');
            
            $data = $this->db->get()->result();
            
            // Format data untuk DataTables
            $formatted_data = [];
            $no = $start + 1;
            
            foreach ($data as $row) {
                $formatted_data[] = [
                    'no' => $no++,
                    'id' => $row->id,
                    'nim' => $row->nim,
                    'nama_mahasiswa' => $row->nama_mahasiswa,
                    'email_mahasiswa' => $row->email_mahasiswa,
                    'nama_prodi' => $row->nama_prodi,
                    'judul_proposal' => $row->judul_proposal,
                    'nama_pembimbing' => $row->nama_pembimbing ?: 'Belum ditetapkan',
                    'workflow_status' => $row->workflow_status,
                    'total_pertemuan' => (int)$row->total_pertemuan,
                    'pertemuan_valid' => (int)$row->pertemuan_valid,
                    'pertemuan_pending' => (int)$row->pertemuan_pending,
                    'status_validasi' => $this->_determine_overall_status($row)
                ];
            }
            
            // Response untuk DataTables
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $total_records,
                'recordsFiltered' => $total_records,
                'data' => $formatted_data
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'API Staf Bimbingan Error: ' . $e->getMessage());
            echo json_encode([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Database error occurred'
            ]);
        }
    }
    
    /**
     * Get list dosen untuk dropdown
     */
    public function dosen() {
        try {
            $this->db->select('d.id, d.nama');
            $this->db->from('dosen d');
            $this->db->join('proposal_mahasiswa pm', 'd.id = pm.dosen_id');
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->where('d.level', '2');
            $this->db->group_by('d.id');
            $this->db->order_by('d.nama', 'ASC');
            
            $result = $this->db->get()->result();
            echo json_encode($result);
            
        } catch (Exception $e) {
            log_message('error', 'API Get Dosen Error: ' . $e->getMessage());
            echo json_encode([]);
        }
    }
    
    /**
     * Get statistik bimbingan
     */
    public function statistik() {
        try {
            $stats = [];
            
            // Total mahasiswa aktif bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_pembimbing', '1');
            $this->db->where_in('workflow_status', ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi']);
            $result = $this->db->get()->row();
            $stats['mahasiswa_aktif'] = $result ? (int)$result->total : 0;
            
            // Total jurnal bimbingan
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('jurnal_bimbingan');
                $result = $this->db->get()->row();
                $stats['total_jurnal'] = $result ? (int)$result->total : 0;
                
                // Jurnal tervalidasi
                $this->db->select('COUNT(*) as total');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('status_validasi', '1');
                $result = $this->db->get()->row();
                $stats['jurnal_valid'] = $result ? (int)$result->total : 0;
            } else {
                $stats['total_jurnal'] = 0;
                $stats['jurnal_valid'] = 0;
            }
            
            // Per tahap workflow
            $workflow_stages = ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'];
            foreach ($workflow_stages as $stage) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('workflow_status', $stage);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $stats[$stage] = $result ? (int)$result->total : 0;
            }
            
            // Yang perlu export (contoh: mahasiswa dengan jurnal pending > 3)
            $stats['perlu_export'] = 0;
            
            echo json_encode($stats);
            
        } catch (Exception $e) {
            log_message('error', 'API Get Statistik Error: ' . $e->getMessage());
            echo json_encode([]);
        }
    }
    
    /**
     * Determine overall validation status
     */
    private function _determine_overall_status($row) {
        if ($row->total_pertemuan == 0) {
            return '0'; // Pending - belum ada jurnal
        }
        
        if ($row->pertemuan_pending > 0) {
            return '0'; // Pending - ada yang belum divalidasi
        }
        
        if ($row->pertemuan_valid > 0) {
            return '1'; // Valid - semua sudah divalidasi
        }
        
        return '0'; // Default pending
    }
}