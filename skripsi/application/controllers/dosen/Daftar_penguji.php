<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Daftar Penguji Controller untuk Dosen
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
 * @version     1.0
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
     * Export PDF daftar penguji
     */
    public function export_pdf() {
        $dosen_id = $this->session->userdata('id');
        $type = $this->input->get('type', TRUE); // 'proposal', 'skripsi', atau 'all'
        
        // Load library PDF jika ada
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
        }
        
        // Get data berdasarkan type
        $data_export = $this->penguji_model->get_data_export($dosen_id, $type);
        
        // Get info dosen
        $dosen_info = $this->db->select('nama, nip')
                              ->from('dosen')
                              ->where('id', $dosen_id)
                              ->get()
                              ->row();
        
        // Prepare data untuk PDF
        $pdf_data = [
            'dosen' => $dosen_info,
            'data' => $data_export,
            'type' => $type,
            'tanggal_cetak' => date('d F Y H:i:s')
        ];
        
        // Generate PDF
        $html = $this->load->view('dosen/daftar_penguji/pdf_export', $pdf_data, TRUE);
        
        // Jika library PDF tersedia
        if (isset($this->pdf)) {
            $this->pdf->loadHtml($html);
            $this->pdf->setPaper('A4', 'portrait');
            $this->pdf->render();
            
            $filename = 'Daftar_Penguji_' . $dosen_info->nama . '_' . date('Y-m-d') . '.pdf';
            $this->pdf->stream($filename, array("Attachment" => false));
        } else {
            // Fallback: tampilkan HTML
            echo $html;
        }
    }
}