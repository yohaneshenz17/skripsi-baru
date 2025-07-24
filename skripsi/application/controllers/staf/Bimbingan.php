<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Bimbingan (FIXED VERSION)
 * FINAL VERSION - Berdasarkan struktur database asli STK Santo Yakobus
 * Database: stkp7133_skripsi (Updated: 24 July 2025)
 * 
 * @author STK Santo Yakobus Development Team
 * @version 3.1 - FIXED SQL SYNTAX ERROR
 */
class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        
        // Load PDF library jika tersedia
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
        }
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu bimbingan
     * Menampilkan daftar mahasiswa yang sedang dalam tahap bimbingan
     */
    public function index() {
        $data['title'] = 'Monitoring Bimbingan Mahasiswa';
        
        // Filter parameters dari GET request
        $prodi_id = $this->input->get('prodi_id');
        $dosen_id = $this->input->get('dosen_id');
        $status = $this->input->get('status');
        $search = $this->input->get('search');
        
        try {
            // QUERY UTAMA: Ambil mahasiswa yang sedang bimbingan
            // Berdasarkan struktur database asli
            $this->db->select('
                pm.id as proposal_id,
                pm.judul,
                pm.workflow_status,
                pm.created_at as tanggal_pengajuan,
                pm.tanggal_penetapan,
                m.id as mahasiswa_id,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nomor_telepon as telepon_pembimbing
            ');
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            
            // FILTER: Hanya mahasiswa yang sedang dalam proses bimbingan
            $this->db->where('pm.status_pembimbing', '1'); // Pembimbing sudah menyetujui
            $this->db->where_in('pm.workflow_status', [
                'bimbingan', 
                'seminar_proposal', 
                'penelitian', 
                'seminar_skripsi', 
                'publikasi'
            ]);
            
            // Apply additional filters
            if ($prodi_id && is_numeric($prodi_id)) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            if ($dosen_id && is_numeric($dosen_id)) {
                $this->db->where('pm.dosen_id', $dosen_id);
            }
            if ($status && in_array($status, ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'])) {
                $this->db->where('pm.workflow_status', $status);
            }
            if ($search && strlen(trim($search)) > 0) {
                $search_term = trim($search);
                $this->db->group_start();
                $this->db->like('m.nama', $search_term);
                $this->db->or_like('m.nim', $search_term);
                $this->db->or_like('pm.judul', $search_term);
                $this->db->group_end();
            }
            
            $this->db->order_by('pm.tanggal_penetapan', 'DESC');
            $this->db->order_by('pm.created_at', 'DESC');
            
            $mahasiswa_result = $this->db->get()->result();
            
            // TAMBAH DATA JURNAL untuk setiap mahasiswa
            $data['mahasiswa_bimbingan'] = [];
            foreach ($mahasiswa_result as $mhs) {
                // Hitung jurnal bimbingan
                $jurnal_stats = $this->_get_jurnal_statistics($mhs->proposal_id);
                
                $mhs->total_jurnal = $jurnal_stats['total'];
                $mhs->jurnal_tervalidasi = $jurnal_stats['tervalidasi'];
                $mhs->jurnal_pending = $jurnal_stats['pending'];
                
                $data['mahasiswa_bimbingan'][] = $mhs;
            }
            
            // Data untuk dropdown filter
            $data['prodi_list'] = $this->_get_prodi_list();
            $data['dosen_list'] = $this->_get_dosen_pembimbing_list();
            $data['status_list'] = [
                'bimbingan' => 'Bimbingan',
                'seminar_proposal' => 'Seminar Proposal',
                'penelitian' => 'Penelitian', 
                'seminar_skripsi' => 'Seminar Skripsi',
                'publikasi' => 'Publikasi'
            ];
            
            // Statistik untuk dashboard
            $data['statistics'] = $this->_get_bimbingan_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in staf/bimbingan/index: ' . $e->getMessage());
            $data['mahasiswa_bimbingan'] = [];
            $data['prodi_list'] = [];
            $data['dosen_list'] = [];
            $data['status_list'] = [];
            $data['statistics'] = [];
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data bimbingan.');
        }
        
        $this->load->view('staf/bimbingan/index', $data);
    }

    /**
     * Detail progress mahasiswa
     */
    public function detail_mahasiswa($proposal_id) {
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('staf/bimbingan');
        }
        
        $data['title'] = 'Detail Progress Bimbingan';
        
        try {
            // Ambil detail mahasiswa dan proposal
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                m.alamat,
                m.foto,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nomor_telepon as telepon_pembimbing
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            
            $mahasiswa = $this->db->get()->row();
            
            if (!$mahasiswa) {
                $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan!');
                redirect('staf/bimbingan');
            }
            
            $data['proposal'] = $mahasiswa; // Changed from 'mahasiswa' to 'proposal' for consistency
            
            // Ambil jurnal bimbingan
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('pertemuan_ke', 'ASC');
            $data['jurnal_bimbingan'] = $this->db->get()->result(); // Changed name for consistency
            
            // Progress workflow
            $data['progress_data'] = $this->_get_progress_workflow($mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', 'Error in detail_mahasiswa: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat detail mahasiswa.');
            redirect('staf/bimbingan');
        }
        
        $this->load->view('staf/bimbingan/detail', $data);
    }

    /**
     * Export jurnal bimbingan ke PDF
     */
    public function export_jurnal($proposal_id) {
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('staf/bimbingan');
        }
        
        // Cek apakah PDF library tersedia
        if (!class_exists('Pdf')) {
            $this->session->set_flashdata('error', 'Library PDF tidak tersedia. Silakan hubungi administrator.');
            redirect('staf/bimbingan');
        }
        
        try {
            // Validasi proposal
            $proposal = $this->_get_proposal_data($proposal_id);
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Data proposal tidak ditemukan!');
                redirect('staf/bimbingan');
            }
            
            // Ambil jurnal bimbingan
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('pertemuan_ke', 'ASC');
            $jurnal_list = $this->db->get()->result();
            
            // Generate PDF menggunakan library yang tersedia
            $this->_generate_jurnal_pdf($proposal, $jurnal_list);
            
            // Log aktivitas staf
            $this->_log_staf_aktivitas('export_jurnal', $proposal->mahasiswa_id, $proposal_id, 
                                      'Export jurnal bimbingan mahasiswa ' . $proposal->nama_mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', 'Error in export_jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export jurnal PDF.');
            redirect('staf/bimbingan');
        }
    }

    /**
     * FIXED: Get dosen pembimbing list (tanpa syntax error)
     */
    private function _get_dosen_pembimbing_list() {
        try {
            // FIXED: Query yang benar untuk CodeIgniter
            $this->db->select('d.id, d.nama');
            $this->db->distinct(); // Gunakan distinct() method terpisah
            $this->db->from('dosen d');
            $this->db->join('proposal_mahasiswa pm', 'd.id = pm.dosen_id');
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->where('d.level', '2'); // Level dosen
            $this->db->order_by('d.nama', 'ASC');
            
            $query = $this->db->get();
            
            if ($query) {
                return $query->result();
            }
            
            return [];
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get prodi list
     */
    private function _get_prodi_list() {
        try {
            $this->db->select('id, nama');
            $this->db->from('prodi');
            $this->db->order_by('nama', 'ASC');
            $query = $this->db->get();
            
            if ($query) {
                return $query->result();
            }
            
            return [];
        } catch (Exception $e) {
            log_message('error', 'Error getting prodi list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get proposal data lengkap
     */
    private function _get_proposal_data($proposal_id) {
        try {
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.nip as nip_pembimbing
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get statistik jurnal bimbingan per proposal
     */
    private function _get_jurnal_statistics($proposal_id) {
        try {
            // Check if jurnal_bimbingan table exists first
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0];
            }
            
            $this->db->select('
                COUNT(*) as total,
                SUM(CASE WHEN status_validasi = "1" THEN 1 ELSE 0 END) as tervalidasi,
                SUM(CASE WHEN status_validasi = "0" THEN 1 ELSE 0 END) as pending
            ');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                return [
                    'total' => $result ? (int)$result->total : 0,
                    'tervalidasi' => $result ? (int)$result->tervalidasi : 0,
                    'pending' => $result ? (int)$result->pending : 0
                ];
            }
            
            return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0];
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal statistics: ' . $e->getMessage());
            return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0];
        }
    }

    /**
     * Get statistik bimbingan keseluruhan
     */
    private function _get_bimbingan_statistics() {
        try {
            $stats = [];
            
            // Total mahasiswa bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_pembimbing', '1');
            $this->db->where_in('workflow_status', ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi']);
            $result = $this->db->get()->row();
            $stats['total_mahasiswa'] = $result ? (int)$result->total : 0;
            
            // Per workflow status
            $workflow_stages = ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'];
            foreach ($workflow_stages as $stage) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('workflow_status', $stage);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $stats[$stage] = $result ? (int)$result->total : 0;
            }
            
            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting bimbingan statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get progress workflow mahasiswa
     */
    private function _get_progress_workflow($mahasiswa) {
        $progress = [
            'proposal' => ['completed' => true, 'date' => $mahasiswa->created_at],
            'bimbingan' => ['completed' => false, 'date' => null],
            'seminar_proposal' => ['completed' => false, 'date' => null],
            'penelitian' => ['completed' => false, 'date' => null],
            'seminar_skripsi' => ['completed' => false, 'date' => null],
            'publikasi' => ['completed' => false, 'date' => null],
            'selesai' => ['completed' => false, 'date' => null]
        ];
        
        $current_stages = ['proposal', 'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai'];
        $current_index = array_search($mahasiswa->workflow_status, $current_stages);
        
        if ($current_index !== false) {
            for ($i = 0; $i <= $current_index; $i++) {
                $progress[$current_stages[$i]]['completed'] = true;
            }
        }
        
        return $progress;
    }

    /**
     * Generate PDF jurnal bimbingan (SIMPLE VERSION)
     */
    private function _generate_jurnal_pdf($proposal, $jurnal_list) {
        try {
            // Instead of complex PDF generation, use simple HTML to PDF
            $data = [
                'proposal' => $proposal,
                'jurnal_bimbingan' => $jurnal_list,
                'generated_by' => $this->session->userdata('nama'),
                'generated_at' => date('d F Y H:i:s')
            ];
            
            // Load view and capture HTML
            $html = $this->load->view('staf/bimbingan/pdf_jurnal', $data, TRUE);
            
            // Simple download as HTML file (can be printed to PDF)
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', ',', '.'], '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.html';
            
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $html;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log aktivitas staf
     */
    private function _log_staf_aktivitas($aktivitas, $mahasiswa_id, $proposal_id, $keterangan, $file_output = null) {
        try {
            // Check if staf_aktivitas table exists
            if (!$this->db->table_exists('staf_aktivitas')) {
                return false;
            }
            
            $data = [
                'staf_id' => $this->session->userdata('id'),
                'aktivitas' => $aktivitas,
                'mahasiswa_id' => $mahasiswa_id,
                'proposal_id' => $proposal_id,
                'keterangan' => $keterangan,
                'file_output' => $file_output,
                'tanggal_aktivitas' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->insert('staf_aktivitas', $data);
        } catch (Exception $e) {
            log_message('error', 'Error logging staf aktivitas: ' . $e->getMessage());
            return false;
        }
    }
}