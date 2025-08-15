<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Daftar Penguji Controller untuk Dosen - FIXED PDF VERSION
 * 
 * Controller untuk menampilkan daftar tugas penguji yang ditetapkan kaprodi kepada dosen
 * Mencakup Seminar Proposal dan Seminar Skripsi
 * 
 * File: application/controllers/dosen/Daftar_penguji.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Daftar Penguji
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.2 (FIXED DOWNLOAD & ASSESSMENT VIEW)
 */
class Daftar_penguji extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date']);
        $this->load->model('Daftar_penguji_model', 'penguji_model');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard daftar penguji untuk dosen
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Get data penguji seminar proposal dan seminar skripsi
        $data_penguji = $this->penguji_model->get_daftar_penguji($dosen_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar_proposal' => $data_penguji['seminar_proposal'],
            'seminar_skripsi' => $data_penguji['seminar_skripsi'],
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Daftar Penguji',
            'content' => $this->load->view('dosen/daftar_penguji/index', $view_data, TRUE),
            'script' => $this->load->view('dosen/daftar_penguji/script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail seminar proposal sebagai penguji
     */
    public function detail_proposal($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar proposal dengan validasi penguji
        $seminar = $this->penguji_model->get_detail_seminar_proposal($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda bukan penguji!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Get penilaian jika sudah ada
        $penilaian = $this->penguji_model->get_penilaian_proposal($seminar_id, $dosen_id);
        
        // Get susunan dewan penguji lengkap
        $dewan_penguji = $this->penguji_model->get_dewan_penguji_proposal($seminar_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian' => $penilaian,
            'dewan_penguji' => $dewan_penguji,
            'is_penguji1' => ($seminar->dosen_penguji1_id == $dosen_id),
            'is_penguji2' => ($seminar->dosen_penguji2_id == $dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/daftar_penguji/detail_proposal', $view_data, TRUE),
            'script' => $this->load->view('dosen/daftar_penguji/script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail seminar skripsi sebagai penguji
     */
    public function detail_skripsi($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar skripsi dengan validasi penguji
        $seminar = $this->penguji_model->get_detail_seminar_skripsi($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda bukan penguji!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Get penilaian jika sudah ada
        $penilaian = $this->penguji_model->get_penilaian_skripsi($seminar_id, $dosen_id);
        
        // Get susunan dewan penguji lengkap
        $dewan_penguji = $this->penguji_model->get_dewan_penguji_skripsi($seminar_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian' => $penilaian,
            'dewan_penguji' => $dewan_penguji,
            'is_penguji1' => ($seminar->dosen_penguji1_id == $dosen_id),
            'is_penguji2' => ($seminar->dosen_penguji2_id == $dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Detail Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/daftar_penguji/detail_skripsi', $view_data, TRUE),
            'script' => $this->load->view('dosen/daftar_penguji/script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Download file proposal untuk dosen penguji - PERBAIKAN MASALAH 1
     * PATH YANG BENAR: skripsi/uploads/seminar_proposal/proposal_files
     */
    public function download_proposal($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Validasi dosen sebagai penguji
        $seminar = $this->penguji_model->get_detail_seminar_proposal($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda bukan penguji!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Cek field file proposal (backward compatibility)
        $file_proposal = null;
        if (!empty($seminar->file_draft_proposal)) {
            $file_proposal = $seminar->file_draft_proposal;
        } elseif (!empty($seminar->file_proposal)) {
            $file_proposal = $seminar->file_proposal;
        }
        
        if (!$file_proposal) {
            $this->session->set_flashdata('error', 'File proposal tidak tersedia.');
            redirect('dosen/daftar_penguji/detail_proposal/' . $seminar_id);
            return;
        }
        
        // PATH YANG BENAR: skripsi/uploads/seminar_proposal/proposal_files
        $file_path = FCPATH . 'uploads/seminar_proposal/proposal_files/' . $file_proposal;
        
        // Cek apakah file fisik ada di server
        if (!file_exists($file_path)) {
            // Log error untuk debugging dengan path yang benar
            log_message('error', "Proposal file not found - Expected path: {$file_path}, Seminar ID: {$seminar_id}, File name: {$file_proposal}");
            
            $this->session->set_flashdata('error', 'File tidak ditemukan di server. Hubungi administrator.');
            redirect('dosen/daftar_penguji/detail_proposal/' . $seminar_id);
            return;
        }
        
        // Load helper download
        $this->load->helper('download');
        
        // Generate nama file yang user-friendly
        $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $seminar->nama_mahasiswa);
        $clean_nim = preg_replace('/[^a-zA-Z0-9]/', '', $seminar->nim);
        $file_extension = pathinfo($file_proposal, PATHINFO_EXTENSION);
        $download_name = "Proposal_{$clean_name}_{$clean_nim}.{$file_extension}";
        
        // Log download activity
        log_message('info', "Proposal downloaded by examiner - Dosen ID: {$dosen_id}, Seminar ID: {$seminar_id}, File: {$file_proposal}");
        
        // Force download
        force_download($download_name, file_get_contents($file_path));
    }

/**
     * Lihat hasil penilaian seminar proposal - VERSI SEDERHANA (FALLBACK)
     */
    public function lihat_penilaian_proposal($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Validasi dosen sebagai penguji
        $seminar = $this->penguji_model->get_detail_seminar_proposal($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda bukan penguji!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // VERSI SEDERHANA: Hanya tampilkan info basic tanpa query complex
        $penilaian_data = [];
        $berita_acara = null;
        
        // Get susunan dewan penguji lengkap
        $dewan_penguji = $this->penguji_model->get_dewan_penguji_proposal($seminar_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian_data' => $penilaian_data, // Kosong dulu untuk testing
            'dewan_penguji' => $dewan_penguji,
            'berita_acara' => $berita_acara,
            'is_penguji1' => ($seminar->dosen_penguji1_id == $dosen_id),
            'is_penguji2' => ($seminar->dosen_penguji2_id == $dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Hasil Penilaian Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/daftar_penguji/lihat_penilaian_proposal', $view_data, TRUE),
            'script' => ''
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Lihat hasil penilaian seminar skripsi - PERBAIKAN MASALAH 2 & 3
     */
    public function lihat_penilaian_skripsi($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Validasi dosen sebagai penguji
        $seminar = $this->penguji_model->get_detail_seminar_skripsi($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda bukan penguji!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Get semua penilaian dari dewan penguji
        $this->db->select('
            pss.*, 
            d.nama as nama_penguji,
            d.nip,
            CASE 
                WHEN pss.dosen_id = pm.dosen_id THEN "Dosen Pembimbing"
                WHEN pss.dosen_id = ssm.dosen_penguji1_id THEN "Dosen Penguji I" 
                WHEN pss.dosen_id = ssm.dosen_penguji2_id THEN "Dosen Penguji II"
                ELSE "Unknown"
            END as posisi_penguji
        ');
        $this->db->from('penilaian_seminar_skripsi pss');
        $this->db->join('seminar_skripsi_mahasiswa ssm', 'pss.seminar_skripsi_id = ssm.id');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('dosen d', 'pss.dosen_id = d.id');
        $this->db->where('pss.seminar_skripsi_id', $seminar_id);
        $this->db->order_by('pss.created_at', 'ASC');
        
        $penilaian_data = $this->db->get()->result();
        
        // Get susunan dewan penguji lengkap
        $dewan_penguji = $this->penguji_model->get_dewan_penguji_skripsi($seminar_id);
        
        // Get berita acara jika sudah ada
        $this->db->select('*');
        $this->db->from('berita_acara_seminar_skripsi');
        $this->db->where('seminar_skripsi_id', $seminar_id);
        $berita_acara = $this->db->get()->row();
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian_data' => $penilaian_data,
            'dewan_penguji' => $dewan_penguji,
            'berita_acara' => $berita_acara,
            'is_penguji1' => ($seminar->dosen_penguji1_id == $dosen_id),
            'is_penguji2' => ($seminar->dosen_penguji2_id == $dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Hasil Penilaian Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/daftar_penguji/lihat_penilaian_skripsi', $view_data, TRUE),
            'script' => ''
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        $stats = [
            'total_proposal' => 0,
            'total_skripsi' => 0,
            'menunggu_penilaian' => 0,
            'selesai_dinilai' => 0
        ];
        
        // Get statistics dari model
        $data_stats = $this->penguji_model->get_statistics($dosen_id);
        
        if ($data_stats) {
            $stats = array_merge($stats, $data_stats);
        }
        
        return $stats;
    }

    /**
     * AJAX: Get data untuk DataTables
     */
    public function ajax_data() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $type = $this->input->get('type'); // 'proposal' atau 'skripsi'
        
        if ($type == 'proposal') {
            $data = $this->penguji_model->get_datatable_proposal($dosen_id);
        } else if ($type == 'skripsi') {
            $data = $this->penguji_model->get_datatable_skripsi($dosen_id);
        } else {
            $data = ['data' => [], 'recordsTotal' => 0, 'recordsFiltered' => 0];
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * AJAX: Get statistics
     */
    public function ajax_stats() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $stats = $this->_get_statistics($dosen_id);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'stats' => $stats
            ]));
    }

    /**
     * Export PDF daftar penguji - FIXED VERSION
     */
    public function export_pdf() {
        $dosen_id = $this->session->userdata('id');
        $type = $this->input->get('type', TRUE); // 'proposal', 'skripsi', atau 'all'
        
        // Get data berdasarkan type
        $data_export = $this->penguji_model->get_data_export($dosen_id, $type);
        
        // Get info dosen
        $dosen_info = $this->db->select('nama, nip')
                              ->from('dosen')
                              ->where('id', $dosen_id)
                              ->get()
                              ->row();
        
        if (!$dosen_info) {
            $this->session->set_flashdata('error', 'Data dosen tidak ditemukan!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Prepare data untuk PDF
        $pdf_data = [
            'dosen' => $dosen_info,
            'data' => $data_export,
            'type' => $type,
            'tanggal_cetak' => date('d F Y H:i:s')
        ];
        
        // Generate HTML content
        $html_content = $this->load->view('dosen/daftar_penguji/pdf_export', $pdf_data, TRUE);
        
        // Try to load PDF library
        $pdf_library_loaded = false;
        
        // Method 1: Try to load existing PDF library
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            try {
                $this->load->library('pdf');
                $pdf_library_loaded = true;
                
                // Try different PDF library methods
                if (method_exists($this->pdf, 'loadHtml')) {
                    // Dompdf style
                    $this->pdf->loadHtml($html_content);
                    $this->pdf->setPaper('A4', 'portrait');
                    $this->pdf->render();
                    
                    $filename = 'Daftar_Penguji_' . str_replace(' ', '_', $dosen_info->nama) . '_' . date('Y-m-d') . '.pdf';
                    $this->pdf->stream($filename, array("Attachment" => false));
                    return;
                    
                } elseif (method_exists($this->pdf, 'writeHTML')) {
                    // TCPDF style
                    $this->pdf->AddPage();
                    $this->pdf->writeHTML($html_content, true, false, true, false, '');
                    
                    $filename = 'Daftar_Penguji_' . str_replace(' ', '_', $dosen_info->nama) . '_' . date('Y-m-d') . '.pdf';
                    $this->pdf->Output($filename, 'I');
                    return;
                    
                } elseif (method_exists($this->pdf, 'html')) {
                    // mPDF style
                    $this->pdf->html($html_content);
                    
                    $filename = 'Daftar_Penguji_' . str_replace(' ', '_', $dosen_info->nama) . '_' . date('Y-m-d') . '.pdf';
                    $this->pdf->Output($filename, 'I');
                    return;
                }
                
            } catch (Exception $e) {
                $pdf_library_loaded = false;
                log_message('error', 'PDF Library Error: ' . $e->getMessage());
            }
        }
        
        // Method 2: If PDF library not available or failed, show HTML version
        if (!$pdf_library_loaded) {
            // Set headers untuk download HTML sebagai PDF
            $filename = 'Daftar_Penguji_' . str_replace(' ', '_', $dosen_info->nama) . '_' . date('Y-m-d') . '.html';
            
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Add CSS untuk print
            $print_css = '
            <style>
                @media print {
                    body { margin: 0; padding: 20px; }
                    .no-print { display: none; }
                }
                @page { 
                    size: A4; 
                    margin: 2cm; 
                }
            </style>
            <script>
                window.onload = function() {
                    if(confirm("Apakah Anda ingin mencetak dokumen ini?")) {
                        window.print();
                    }
                }
            </script>';
            
            echo $print_css . $html_content;
            return;
        }
        
        // Fallback: Redirect with error message
        $this->session->set_flashdata('error', 'Library PDF tidak tersedia. Silakan hubungi administrator.');
        redirect('dosen/daftar_penguji');
    }

    /**
     * Export bulk data
     */
    public function export_bulk() {
        $dosen_id = $this->session->userdata('id');
        $type = $this->input->get('type', TRUE);
        $ids = $this->input->get('ids', TRUE);
        
        if (empty($ids)) {
            $this->session->set_flashdata('error', 'Tidak ada data yang dipilih!');
            redirect('dosen/daftar_penguji');
            return;
        }
        
        // Process selected IDs
        $selected_ids = explode(',', $ids);
        
        // Get specific data for selected IDs
        // Implementation depends on requirements
        
        $this->session->set_flashdata('info', 'Export bulk sedang dalam pengembangan.');
        redirect('dosen/daftar_penguji');
    }

    /**
     * Print detail single item
     */
    public function print_detail($type, $seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        if ($type == 'proposal') {
            $seminar = $this->penguji_model->get_detail_seminar_proposal($seminar_id, $dosen_id);
        } elseif ($type == 'skripsi') {
            $seminar = $this->penguji_model->get_detail_seminar_skripsi($seminar_id, $dosen_id);
        } else {
            show_404();
            return;
        }
        
        if (!$seminar) {
            show_404();
            return;
        }
        
        // Generate simple HTML for printing
        $print_data = [
            'seminar' => $seminar,
            'type' => $type,
            'tanggal_cetak' => date('d F Y H:i:s')
        ];
        
        // Load simple print view
        $this->load->view('dosen/daftar_penguji/print_detail', $print_data);
    }

    /**
     * Debug method untuk development
     */
    public function debug() {
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        
        echo "<h2>Debug Daftar Penguji</h2>";
        echo "<p>Dosen ID: " . $dosen_id . "</p>";
        
        // Test database connection
        echo "<h3>Database Test:</h3>";
        $test_query = $this->db->query("SELECT COUNT(*) as total FROM dosen WHERE id = ?", [$dosen_id]);
        echo "Dosen found: " . $test_query->row()->total . "<br>";
        
        // Test model
        echo "<h3>Model Test:</h3>";
        try {
            $stats = $this->penguji_model->get_statistics($dosen_id);
            echo "<pre>";
            print_r($stats);
            echo "</pre>";
        } catch (Exception $e) {
            echo "Model Error: " . $e->getMessage();
        }
        
        // Test table structure
        echo "<h3>Table Structure:</h3>";
        $tables = ['seminar_proposal_mahasiswa', 'seminar_skripsi_mahasiswa'];
        foreach ($tables as $table) {
            echo "<h4>Table: {$table}</h4>";
            $structure = $this->db->query("DESCRIBE {$table}")->result_array();
            echo "<pre>";
            print_r($structure);
            echo "</pre>";
        }
    }
}