<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penelitian Controller untuk Mahasiswa - FIXED VERSION
 * 
 * Fixed Issues:
 * - Fixed undefined $upload_result variable
 * - Fixed database update query (removed updated_at field)
 * - Fixed file upload handling
 * - Better error handling
 * 
 * File: application/controllers/mahasiswa/Penelitian.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.3 (Error Fixed)
 */
class Penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Enable error reporting in development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            log_message('debug', 'Penelitian Controller - Constructor called');
        }
        
        // FIXED: Accept mahasiswa level '3' (as shown in debug session data)
        if (!$this->session->userdata('logged_in')) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Penelitian: User not logged in');
            }
            redirect('auth/login');
            return;
        }
        
        $level = $this->session->userdata('level');
        // FIXED: Accept level '3' which is the actual mahasiswa level in the system
        if ($level !== '3') {
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Penelitian: Invalid level: ' . $level . ' (Expected: 3)');
            }
            $this->session->set_flashdata('error', 'Akses ditolak. Level: ' . $level);
            redirect('mahasiswa/dashboard');
            return;
        }
        
        // Load libraries and model after authentication
        $this->load->library(['form_validation', 'upload']);
        $this->load->helper(['file', 'security']);
        
        try {
            $this->load->model('Penelitian_model', 'penelitian');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Penelitian_model: ' . $e->getMessage());
                show_error('Model Error: ' . $e->getMessage());
            } else {
                show_error('Sistem error. Silakan hubungi administrator.');
            }
        }
    }

    /**
     * Index - Dashboard penelitian mahasiswa
     * FIXED: Better error handling and fallback
     */
    public function index() {
        try {
            $mahasiswa_id = $this->session->userdata('id');
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Penelitian Index - Mahasiswa ID: ' . $mahasiswa_id);
            }
            
            // Get proposal aktif mahasiswa - SIMPLIFIED with better error handling
            $proposal_aktif = $this->_get_proposal_aktif($mahasiswa_id);
            
            // FIXED: Show page with message instead of redirect
            if (!$proposal_aktif) {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'Penelitian: No active proposal found for mahasiswa ' . $mahasiswa_id);
                }
                
                $view_data = [
                    'proposal' => null,
                    'eligibility' => [
                        'eligible' => false, 
                        'message' => 'Belum memiliki proposal yang disetujui oleh Kaprodi',
                        'requirements' => []
                    ],
                    'permohonan' => null,
                    'can_submit' => false,
                    'progress_steps' => []
                ];
                
                $data = [
                    'title' => 'Penelitian - Tahap 4',
                    'content' => $this->load->view('mahasiswa/penelitian/index', $view_data, TRUE),
                    'active_menu' => 'penelitian'
                ];
                
                $this->load->view('template/mahasiswa', $data);
                return;
            }

            // Check eligibility dengan error handling yang lebih baik
            try {
                $eligibility = $this->penelitian->check_eligibility($proposal_aktif->id, $mahasiswa_id);
                
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'Penelitian: Eligibility check result: ' . json_encode($eligibility));
                }
                
            } catch (Exception $e) {
                if (ENVIRONMENT === 'development') {
                    log_message('error', 'Eligibility check error: ' . $e->getMessage());
                }
                $eligibility = [
                    'error' => true,
                    'eligible' => false,
                    'message' => 'Terjadi kesalahan saat mengecek syarat: ' . $e->getMessage(),
                    'requirements' => []
                ];
            }
            
            // Get existing permohonan dengan error handling
            try {
                $permohonan_existing = $this->penelitian->get_permohonan_by_mahasiswa($mahasiswa_id);
                $permohonan_data = !empty($permohonan_existing['data']) ? $permohonan_existing['data'][0] : null;
                
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'Penelitian: Existing permohonan: ' . ($permohonan_data ? 'Found ID ' . $permohonan_data->id : 'Not found'));
                }
                
            } catch (Exception $e) {
                if (ENVIRONMENT === 'development') {
                    log_message('error', 'Get permohonan error: ' . $e->getMessage());
                }
                $permohonan_data = null;
            }
            
            // SIMPLE & FIXED: Direct logic untuk can_submit
            $can_submit = false;
            $debug_steps = [];
            
            // Step 1: Check eligibility error
            if (isset($eligibility['error']) && $eligibility['error'] === true) {
                $debug_steps[] = "FAIL: Eligibility has error";
            } else {
                $debug_steps[] = "PASS: No eligibility error";
                
                // Step 2: Check eligibility status
                if (!isset($eligibility['eligible']) || $eligibility['eligible'] !== true) {
                    $debug_steps[] = "FAIL: Not eligible (" . (isset($eligibility['eligible']) ? ($eligibility['eligible'] ? 'true' : 'false') : 'undefined') . ")";
                } else {
                    $debug_steps[] = "PASS: Is eligible";
                    
                    // Step 3: Check existing permohonan
                    if ($permohonan_data !== null) {
                        $debug_steps[] = "FAIL: Has existing permohonan (ID: " . $permohonan_data->id . ", Status: " . $permohonan_data->status . ")";
                    } else {
                        $debug_steps[] = "PASS: No existing permohonan";
                        
                        // ALL CONDITIONS MET!
                        $can_submit = true;
                        $debug_steps[] = "SUCCESS: All conditions met - CAN SUBMIT!";
                    }
                }
            }
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Penelitian can_submit steps: ' . implode(' | ', $debug_steps));
            }
            
            // Prepare view data
            $view_data = [
                'proposal' => $proposal_aktif,
                'eligibility' => $eligibility,
                'permohonan' => $permohonan_data,
                'can_submit' => $can_submit,
                'debug_steps' => $debug_steps, // For debugging
                'progress_steps' => $this->_get_progress_steps($permohonan_data)
            ];
            
            // Load view
            $data = [
                'title' => 'Penelitian - Tahap 4',
                'content' => $this->load->view('mahasiswa/penelitian/index', $view_data, TRUE),
                'active_menu' => 'penelitian'
            ];
            
            $this->load->view('template/mahasiswa', $data);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Penelitian index error: ' . $e->getMessage());
                
                // Show debug info in development
                echo "<h2>DEBUG ERROR</h2>";
                echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
                echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
                echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
                echo "<p><strong>Trace:</strong></p><pre>" . $e->getTraceAsString() . "</pre>";
                
                echo "<h3>Session Data:</h3>";
                echo "<pre>" . print_r($this->session->userdata(), true) . "</pre>";
                
                echo "<h3>Last Query:</h3>";
                echo "<pre>" . $this->db->last_query() . "</pre>";
                
                if ($this->db->error()['message']) {
                    echo "<h3>Database Error:</h3>";
                    echo "<pre>" . print_r($this->db->error(), true) . "</pre>";
                }
                
            } else {
                show_error('Terjadi kesalahan sistem. Silakan coba lagi.');
            }
        }
    }

    /**
     * Form pengajuan permohonan izin penelitian
     */
    public function ajukan() {
        try {
            $mahasiswa_id = $this->session->userdata('id');
            
            // Get proposal aktif
            $proposal_aktif = $this->_get_proposal_aktif($mahasiswa_id);
            
            if (!$proposal_aktif) {
                $this->session->set_flashdata('error', 'Belum memiliki proposal yang disetujui');
                redirect('mahasiswa/penelitian');
                return;
            }

            // Check eligibility
            $eligibility = $this->penelitian->check_eligibility($proposal_aktif->id, $mahasiswa_id);
            
            if ($eligibility['error'] || !$eligibility['eligible']) {
                $this->session->set_flashdata('error', $eligibility['message']);
                redirect('mahasiswa/penelitian');
                return;
            }

            // Handle form submission
            if ($this->input->post()) {
                $this->_handle_form_submission($proposal_aktif, $mahasiswa_id);
                return;
            }

            // Get mahasiswa data for form
            $mahasiswa_data = $this->_get_mahasiswa_data($mahasiswa_id);
            
            $view_data = [
                'proposal' => $proposal_aktif,
                'mahasiswa' => $mahasiswa_data,
                'eligibility' => $eligibility
            ];
            
            $data = [
                'title' => 'Ajukan Permohonan Penelitian',
                'content' => $this->load->view('mahasiswa/penelitian/form_ajukan', $view_data, TRUE),
                'active_menu' => 'penelitian'
            ];
            
            $this->load->view('template/mahasiswa', $data);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Ajukan form error: ' . $e->getMessage());
                show_error('Debug Error: ' . $e->getMessage());
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem');
                redirect('mahasiswa/penelitian');
            }
        }
    }

    /**
     * Detail permohonan penelitian
     */
    public function detail($permohonan_id = null) {
        try {
            if (!$permohonan_id) {
                show_404();
            }

            $mahasiswa_id = $this->session->userdata('id');
            
            // Get detail dengan error handling
            $detail_result = $this->penelitian->get_permohonan_detail($permohonan_id, $mahasiswa_id);
            
            if ($detail_result['error'] || !isset($detail_result['data'])) {
                $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan');
                redirect('mahasiswa/penelitian');
                return;
            }

            $permohonan = $detail_result['data'];
            
            $view_data = [
                'permohonan' => $permohonan,
                'progress_steps' => $this->_get_progress_steps($permohonan),
                'can_download' => in_array($permohonan->status, ['surat_ready', 'completed'])
            ];
            
            $data = [
                'title' => 'Detail Permohonan Penelitian',
                'content' => $this->load->view('mahasiswa/penelitian/detail', $view_data, TRUE),
                'active_menu' => 'penelitian'
            ];
            
            $this->load->view('template/mahasiswa', $data);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Detail error: ' . $e->getMessage());
                show_error('Debug Error: ' . $e->getMessage());
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem');
                redirect('mahasiswa/penelitian');
            }
        }
    }

    /**
     * PERBAIKAN 2: Tambah Method Download Surat
     * Tambahkan method ini di application/controllers/mahasiswa/Penelitian.php
     */
    public function download_surat($permohonan_id = null) {
        if (!$permohonan_id) {
            show_404();
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Ambil data permohonan dan file surat
            $this->db->select('
                pip.file_surat_izin_staf, 
                pip.status,
                pm.id as proposal_id,
                m.nim, 
                m.nama as nama_mahasiswa
            ');
            $this->db->from('permohonan_izin_penelitian pip');
            $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pip.id', $permohonan_id);
            $this->db->where('m.id', $mahasiswa_id); // Security: hanya file milik mahasiswa sendiri
            
            $data = $this->db->get()->row();
            
            // Validasi data dan status
            if (!$data) {
                $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan');
                redirect('mahasiswa/penelitian');
                return;
            }
            
            if (!$data->file_surat_izin_staf) {
                $this->session->set_flashdata('error', 'File surat belum tersedia');
                redirect('mahasiswa/penelitian');
                return;
            }
            
            if (!in_array($data->status, ['surat_ready', 'completed'])) {
                $this->session->set_flashdata('error', 'Surat belum siap untuk didownload');
                redirect('mahasiswa/penelitian');
                return;
            }
            
            // ✅ PERBAIKAN: Path file sesuai dengan yang diupload staf
            $file_path = FCPATH . 'uploads/surat_izin/' . $data->file_surat_izin_staf;
            
            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File surat tidak ditemukan di server');
                redirect('mahasiswa/penelitian');
                return;
            }
            
            // Generate nama file yang user-friendly
            $download_filename = 'Surat_Izin_Penelitian_' . $data->nim . '_' . $data->nama_mahasiswa . '.pdf';
            
            // Force download
            $this->load->helper('download');
            force_download($download_filename, file_get_contents($file_path));
            
            // Optional: Update status jadi 'completed' setelah pertama kali download
            $this->db->where('id', $permohonan_id);
            $this->db->update('permohonan_izin_penelitian', [
                'status' => 'completed',
                'tanggal_download' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Download surat error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat download file');
            redirect('mahasiswa/penelitian');
        }
    }

    // =================================================================
    // PRIVATE HELPER METHODS - SIMPLIFIED & FIXED
    // =================================================================

    /**
     * Get proposal aktif mahasiswa - FIXED
     */
    private function _get_proposal_aktif($mahasiswa_id) {
        try {
            $this->db->select('pm.*, m.nama, m.nim, m.prodi_id, p.nama as nama_prodi, d.nama as nama_pembimbing');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status_kaprodi', '1'); // Sudah disetujui kaprodi
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            $result = $this->db->get()->row();
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Proposal aktif query: ' . $this->db->last_query());
                log_message('debug', 'Proposal aktif result: ' . ($result ? 'Found ID ' . $result->id : 'Not found'));
            }
            
            return $result;
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Get proposal aktif error: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get data mahasiswa lengkap
     */
    private function _get_mahasiswa_data($mahasiswa_id) {
        try {
            $this->db->select('m.*, p.nama as nama_prodi');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->where('m.id', $mahasiswa_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Get mahasiswa data error: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Handle form submission - FIXED VERSION
     * Memperbaiki masalah undefined $upload_result dan error database
     */
    private function _handle_form_submission($proposal, $mahasiswa_id) {
        try {
            // Basic validation
            $this->form_validation->set_rules('nama_mahasiswa', 'Nama Mahasiswa', 'required|trim');
            $this->form_validation->set_rules('nim', 'NIM', 'required|trim');
            $this->form_validation->set_rules('semester', 'Semester', 'required');
            $this->form_validation->set_rules('program_studi', 'Program Studi', 'required');
            $this->form_validation->set_rules('judul_skripsi_terbaru', 'Judul Skripsi', 'required|trim');
            $this->form_validation->set_rules('tempat_penelitian', 'Tempat Penelitian', 'required|trim');
            $this->form_validation->set_rules('tanggal_mulai_penelitian', 'Tanggal Mulai', 'required');
            $this->form_validation->set_rules('tanggal_selesai_penelitian', 'Tanggal Selesai', 'required');

            // TAMBAHKAN INI: Validasi file proposal revisi
            if (empty($_FILES['file_proposal_revisi']['name'])) {
                $this->form_validation->set_rules('file_proposal_revisi_dummy', 'File Proposal Revisi', 'required');
                $this->form_validation->set_message('required', 'File Proposal Revisi wajib diupload');
            }
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                return;
            }

            // FIXED: Handle file upload SEBELUM prepare data
            $upload_result = $this->_handle_file_upload('file_proposal_revisi');
            
            if (!$upload_result['status']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                return;
            }

            // FIXED: Prepare data dengan file yang sudah diupload
            $input_data = [
                'proposal_mahasiswa_id' => $proposal->id,
                'mahasiswa_id' => $mahasiswa_id,
                'nama_mahasiswa' => $this->input->post('nama_mahasiswa'),
                'nim' => $this->input->post('nim'),
                'semester' => $this->input->post('semester'),
                'program_studi' => $this->input->post('program_studi'),
                'judul_skripsi_terbaru' => $this->input->post('judul_skripsi_terbaru'),
                'tempat_penelitian' => $this->input->post('tempat_penelitian'),
                'tanggal_mulai_penelitian' => $this->input->post('tanggal_mulai_penelitian'),
                'tanggal_selesai_penelitian' => $this->input->post('tanggal_selesai_penelitian'),
                'dosen_pembimbing_id' => $proposal->dosen_id,
                'file_proposal_revisi' => $upload_result['filename'] // FIXED: Sekarang sudah ada
            ];

            // Submit permohonan
            $result = $this->penelitian->create_permohonan($input_data);

            if ($result['error']) {
                $this->session->set_flashdata('error', $result['message']);
            } else {
                $this->session->set_flashdata('success', 'Permohonan izin penelitian berhasil diajukan');
                
                // OPTIONAL: Kirim notifikasi ke dosen pembimbing
                try {
                    $this->_kirim_notifikasi_penelitian($proposal, $input_data);
                } catch (Exception $e) {
                    // Jangan gagalkan proses utama jika notifikasi gagal
                    log_message('error', 'Failed to send email notification: ' . $e->getMessage());
                }
            }

            redirect('mahasiswa/penelitian');
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Form submission error: ' . $e->getMessage());
            }
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data');
            redirect('mahasiswa/penelitian');
        }
    }
    
    /**
     * Handle file upload untuk proposal revisi
     * 
     * @param string $field_name Nama field file input
     * @return array Status upload dan informasi file
     */
    private function _handle_file_upload($field_name) {
        $upload_path = FCPATH . 'uploads/penelitian/proposal_revisi/';
        
        // Buat direktori jika belum ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Konfigurasi upload
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 2048, // 2MB
            'file_name' => 'PENELITIAN_' . date('YmdHis') . '_' . $this->session->userdata('id') . '_' . uniqid(),
            'remove_spaces' => true,
            'encrypt_name' => false // Karena sudah custom naming
        ];
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return [
                'status' => true,
                'filename' => $upload_data['file_name'],
                'message' => 'File berhasil diupload'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Upload gagal: ' . $this->upload->display_errors('', ''),
                'filename' => null
            ];
        }
    }

    /**
     * Kirim notifikasi email ke dosen pembimbing
     * 
     * @param object $proposal Data proposal mahasiswa
     * @param array $data_penelitian Data permohonan penelitian
     * @return boolean Status pengiriman email
     */
    private function _kirim_notifikasi_penelitian($proposal, $data_penelitian) {
        try {
            // Ambil data dosen pembimbing
            $dosen = $this->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
            
            if (!$dosen || !$dosen->email) {
                log_message('error', 'Dosen pembimbing tidak ditemukan atau email kosong untuk proposal ID: ' . $proposal->id);
                return false;
            }
            
            // Load email library
            $this->load->library('email');
            
            // Konfigurasi email
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com', 
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            $subject = 'Permohonan Izin Penelitian - ' . $data_penelitian['nama_mahasiswa'];
            
            // Template email yang professional
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Permohonan Izin Penelitian</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    
                    <!-- Header -->
                    <div style='text-align: center; background-color: #17a2b8; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                        <h2 style='margin: 0;'>📋 Permohonan Izin Penelitian</h2>
                    </div>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>
                        Yth. <strong>{$dosen->nama}</strong>,<br>
                        S.Pd., M.Pd.
                    </p>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                        Dengan hormat, melalui email ini kami sampaikan bahwa mahasiswa bimbingan Anda telah mengajukan 
                        <strong>permohonan izin penelitian</strong> melalui Sistem Informasi Manajemen Tugas Akhir (SIM-TA).
                    </p>
                    
                    <!-- Detail Mahasiswa -->
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                        <h4 style='margin: 0 0 10px 0; color: #17a2b8;'>📚 Detail Mahasiswa:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 2px 0; width: 30%;'><strong>Nama</strong></td><td>: {$data_penelitian['nama_mahasiswa']}</td></tr>
                            <tr><td style='padding: 2px 0;'><strong>NIM</strong></td><td>: {$data_penelitian['nim']}</td></tr>
                            <tr><td style='padding: 2px 0;'><strong>Semester</strong></td><td>: {$data_penelitian['semester']}</td></tr>
                            <tr><td style='padding: 2px 0;'><strong>Program Studi</strong></td><td>: {$data_penelitian['program_studi']}</td></tr>
                        </table>
                    </div>
                    
                    <!-- Detail Penelitian -->
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <h4 style='margin: 0 0 10px 0; color: #28a745;'>🔍 Detail Penelitian:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 2px 0; width: 30%; vertical-align: top;'><strong>Judul Skripsi</strong></td><td>: {$data_penelitian['judul_skripsi_terbaru']}</td></tr>
                            <tr><td style='padding: 2px 0; vertical-align: top;'><strong>Lokasi Penelitian</strong></td><td>: {$data_penelitian['tempat_penelitian']}</td></tr>
                            <tr><td style='padding: 2px 0;'><strong>Periode Penelitian</strong></td><td>: {$data_penelitian['tanggal_mulai_penelitian']} s/d {$data_penelitian['tanggal_selesai_penelitian']}</td></tr>
                        </table>
                    </div>
                    
                    <!-- Call to Action -->
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='margin: 0 0 10px 0; color: #856404;'>⚡ Tindakan Diperlukan:</h4>
                        <p style='margin: 0; font-size: 14px; color: #856404;'>
                            Silakan login ke sistem untuk memberikan <strong>persetujuan atau penolakan</strong> 
                            terhadap permohonan izin penelitian ini.
                        </p>
                    </div>
                    
                    <!-- Button Login -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://stkyakobus.ac.id/skripsi/dosen' 
                           style='background-color: #17a2b8; color: white; padding: 12px 25px; text-decoration: none; 
                                  border-radius: 5px; display: inline-block; font-weight: bold;'>
                            🔐 Login ke Sistem SIM-TA
                        </a>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 0; font-size: 13px; color: #0c5460;'>
                            <strong>📌 Catatan:</strong> Permohonan ini memerlukan persetujuan Anda sebelum 
                            dapat diproses lebih lanjut oleh bagian administrasi akademik.
                        </p>
                    </div>
                    
                    <!-- Footer -->
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='text-align: center; color: #6c757d; font-size: 14px; margin: 0;'>
                        Email ini dikirim otomatis oleh SIM Tugas Akhir STK Santo Yakobus Merauke<br>
                        <small style='font-size: 12px;'>© 2025 Sekolah Tinggi Kateketik dan Pastoral Santo Yakobus Merauke</small>
                    </p>
                </div>
            </body>
            </html>";
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($dosen->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // Kirim email
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Email permohonan penelitian berhasil dikirim ke: ' . $dosen->email . ' untuk mahasiswa: ' . $data_penelitian['nama_mahasiswa']);
            } else {
                log_message('error', 'Email permohonan penelitian gagal dikirim ke: ' . $dosen->email . ' - Error: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exception saat kirim email permohonan penelitian: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get progress steps untuk tracking visual
     */
    private function _get_progress_steps($permohonan) {
        $steps = [
            ['title' => 'Pengajuan', 'status' => 'pending', 'icon' => 'file-text'],
            ['title' => 'Review Pembimbing', 'status' => 'pending', 'icon' => 'user-check'],
            ['title' => 'Proses Staf', 'status' => 'pending', 'icon' => 'clipboard'],
            ['title' => 'Download Surat', 'status' => 'pending', 'icon' => 'download']
        ];
    
        if (!$permohonan) {
            return $steps;
        }
    
        // Update based on status
        $steps[0]['status'] = 'completed'; // Pengajuan selalu completed jika ada permohonan
        
        switch ($permohonan->status) {
            case 'submitted':
            case 'review_pembimbing':
                $steps[1]['status'] = 'active';
                break;
            case 'approved':
                $steps[1]['status'] = 'completed';
                $steps[2]['status'] = 'active';
                break;
            case 'rejected':
                $steps[1]['status'] = 'error';
                break;
            case 'surat_ready':
                $steps[1]['status'] = 'completed';
                $steps[2]['status'] = 'completed';
                // ✅ PERBAIKAN: Status 'surat_ready' = surat siap download = COMPLETED!
                $steps[3]['status'] = 'completed'; // BUKAN 'active'
                break;
            case 'completed':
                for ($i = 0; $i < 4; $i++) {
                    $steps[$i]['status'] = 'completed';
                }
                break;
        }
    
        return $steps;
    }
}

/* End of file Penelitian.php */
/* Location: ./application/controllers/mahasiswa/Penelitian.php */