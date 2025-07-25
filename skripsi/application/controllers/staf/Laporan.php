<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Laporan
 * File: application/controllers/staf/Laporan.php
 */
class Laporan extends CI_Controller {

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
        $data['title'] = 'Laporan Tugas Akhir';
        
        try {
            // Get filter parameters
            $periode = $this->input->get('periode') ?: date('Y');
            $prodi_id = $this->input->get('prodi_id');
            
            // Get laporan summary
            $data['summary'] = $this->_get_summary_laporan($periode, $prodi_id);
            
            // Get workflow statistics
            $data['workflow_stats'] = $this->_get_workflow_statistics($periode, $prodi_id);
            
            // Get prodi list for filter
            $data['prodi_list'] = $this->db->get('prodi')->result();
            
            // Get tahun list for filter
            $data['tahun_list'] = $this->_get_tahun_list();
            
            $data['current_periode'] = $periode;
            $data['current_prodi'] = $prodi_id;
            
        } catch (Exception $e) {
            log_message('error', 'Error in Laporan controller: ' . $e->getMessage());
            $data['summary'] = [];
            $data['workflow_stats'] = [];
            $data['prodi_list'] = [];
            $data['tahun_list'] = [];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat laporan.');
        }
        
        $this->load->view('staf/laporan/index', $data);
    }
    
    public function tahapan() {
        $data['title'] = 'Laporan per Tahapan';
        
        try {
            $prodi_id = $this->input->get('prodi_id');
            $status = $this->input->get('status') ?: 'bimbingan';
            
            // Get mahasiswa by tahapan
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                pm.created_at as tanggal_mulai
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.workflow_status', $status);
            
            if ($prodi_id) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            
            $this->db->order_by('pm.created_at', 'DESC');
            $data['mahasiswa_list'] = $this->db->get()->result();
            
            // Get prodi list
            $data['prodi_list'] = $this->db->get('prodi')->result();
            
            $data['current_status'] = $status;
            $data['current_prodi'] = $prodi_id;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting laporan tahapan: ' . $e->getMessage());
            $data['mahasiswa_list'] = [];
            $data['prodi_list'] = [];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat laporan tahapan.');
        }
        
        $this->load->view('staf/laporan/tahapan', $data);
    }
    
    public function progress() {
        $data['title'] = 'Laporan Progress Mahasiswa';
        
        try {
            $prodi_id = $this->input->get('prodi_id');
            
            // Get mahasiswa dengan progress detail
            $this->db->select('
                m.nim,
                m.nama as nama_mahasiswa,
                p.nama as nama_prodi,
                pm.judul,
                pm.workflow_status,
                pm.created_at as tanggal_mulai,
                d.nama as nama_pembimbing,
                (SELECT COUNT(*) FROM jurnal_bimbingan jb WHERE jb.proposal_id = pm.id AND jb.status_validasi = "1") as jurnal_tervalidasi,
                (SELECT COUNT(*) FROM jurnal_bimbingan jb WHERE jb.proposal_id = pm.id) as total_jurnal
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.status_kaprodi', '1');
            
            if ($prodi_id) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            
            $this->db->order_by('p.nama, m.nama');
            $data['progress_list'] = $this->db->get()->result();
            
            // Calculate progress percentage for each
            foreach ($data['progress_list'] as &$item) {
                $item->progress_percentage = $this->_calculate_progress($item->workflow_status);
            }
            
            // Get prodi list
            $data['prodi_list'] = $this->db->get('prodi')->result();
            $data['current_prodi'] = $prodi_id;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting progress report: ' . $e->getMessage());
            $data['progress_list'] = [];
            $data['prodi_list'] = [];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat laporan progress.');
        }
        
        $this->load->view('staf/laporan/progress', $data);
    }
    
    public function statistik() {
        $data['title'] = 'Statistik Tugas Akhir';
        
        try {
            $tahun = $this->input->get('tahun') ?: date('Y');
            
            // Get statistik komprehensif
            $data['stats'] = $this->_get_comprehensive_stats($tahun);
            
            // Get chart data
            $data['chart_data'] = $this->_get_chart_data($tahun);
            
            $data['current_tahun'] = $tahun;
            $data['tahun_list'] = $this->_get_tahun_list();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistik: ' . $e->getMessage());
            $data['stats'] = [];
            $data['chart_data'] = [];
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat statistik.');
        }
        
        $this->load->view('staf/laporan/statistik', $data);
    }
    
    public function export() {
        try {
            $type = $this->input->get('type') ?: 'summary';
            $periode = $this->input->get('periode') ?: date('Y');
            $prodi_id = $this->input->get('prodi_id');
            
            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="laporan_tugas_akhir_' . $type . '_' . $periode . '.xls"');
            header('Cache-Control: max-age=0');
            
            if ($type == 'summary') {
                $this->_export_summary($periode, $prodi_id);
            } elseif ($type == 'progress') {
                $this->_export_progress($prodi_id);
            } elseif ($type == 'statistik') {
                $this->_export_statistik($periode);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error exporting laporan: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal mengexport laporan.');
            redirect('staf/laporan');
        }
    }
    
    /**
     * Get summary laporan
     */
    private function _get_summary_laporan($periode, $prodi_id = null) {
        $summary = [];
        
        try {
            $this->db->select('
                COUNT(*) as total_proposal,
                SUM(CASE WHEN workflow_status = "bimbingan" THEN 1 ELSE 0 END) as bimbingan,
                SUM(CASE WHEN workflow_status = "seminar_proposal" THEN 1 ELSE 0 END) as seminar_proposal,
                SUM(CASE WHEN workflow_status = "penelitian" THEN 1 ELSE 0 END) as penelitian,
                SUM(CASE WHEN workflow_status = "seminar_skripsi" THEN 1 ELSE 0 END) as seminar_skripsi,
                SUM(CASE WHEN workflow_status = "publikasi" THEN 1 ELSE 0 END) as publikasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('YEAR(pm.created_at)', $periode);
            
            if ($prodi_id) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            
            $summary = $this->db->get()->row_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting summary: ' . $e->getMessage());
        }
        
        return $summary ?: [];
    }
    
    /**
     * Get workflow statistics
     */
    private function _get_workflow_statistics($periode, $prodi_id = null) {
        $stats = [];
        
        try {
            $stages = [
                'bimbingan' => 'Bimbingan',
                'seminar_proposal' => 'Seminar Proposal', 
                'penelitian' => 'Penelitian',
                'seminar_skripsi' => 'Seminar Skripsi',
                'publikasi' => 'Publikasi'
            ];
            
            foreach ($stages as $key => $label) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('pm.workflow_status', $key);
                $this->db->where('YEAR(pm.created_at)', $periode);
                
                if ($prodi_id) {
                    $this->db->where('m.prodi_id', $prodi_id);
                }
                
                $result = $this->db->get()->row();
                $stats[] = [
                    'stage' => $key,
                    'label' => $label,
                    'total' => $result ? $result->total : 0
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error getting workflow stats: ' . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get tahun list for filter
     */
    private function _get_tahun_list() {
        try {
            $this->db->select('YEAR(created_at) as tahun');
            $this->db->from('proposal_mahasiswa');
            $this->db->distinct();
            $this->db->order_by('tahun', 'DESC');
            $result = $this->db->get()->result();
            
            return array_column($result, 'tahun');
            
        } catch (Exception $e) {
            log_message('error', 'Error getting tahun list: ' . $e->getMessage());
            return [date('Y')];
        }
    }
    
    /**
     * Calculate progress percentage
     */
    private function _calculate_progress($workflow_status) {
        $stages = [
            'bimbingan' => 20,
            'seminar_proposal' => 40,
            'penelitian' => 60,
            'seminar_skripsi' => 80,
            'publikasi' => 100
        ];
        
        return isset($stages[$workflow_status]) ? $stages[$workflow_status] : 0;
    }
    
    /**
     * Get comprehensive statistics
     */
    private function _get_comprehensive_stats($tahun) {
        // Implementation for detailed statistics
        return [];
    }
    
    /**
     * Get chart data
     */
    private function _get_chart_data($tahun) {
        // Implementation for chart data
        return [];
    }
    
    /**
     * Export methods
     */
    private function _export_summary($periode, $prodi_id) {
        echo "<h2>Laporan Summary Tugas Akhir - $periode</h2>";
        echo "<p>Akan dikembangkan lebih lanjut...</p>";
    }
    
    private function _export_progress($prodi_id) {
        echo "<h2>Laporan Progress Mahasiswa</h2>";
        echo "<p>Akan dikembangkan lebih lanjut...</p>";
    }
    
    private function _export_statistik($periode) {
        echo "<h2>Laporan Statistik - $periode</h2>";
        echo "<p>Akan dikembangkan lebih lanjut...</p>";
    }
}

/* End of file Laporan.php */