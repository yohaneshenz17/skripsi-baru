<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penelitian Controller untuk Mahasiswa - FIXED FOR EXISTING DB
 * 
 * Fixed Issues:
 * - Accept mahasiswa level '3' (as shown in debug)
 * - Use correct database structure (no direct mahasiswa_id field)
 * - Better error handling
 * - Fallback for missing data
 * 
 * File: application/controllers/mahasiswa/Penelitian.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.2 (Fixed for Existing DB)
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
     * Handle form submission - SIMPLIFIED
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

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                return;
            }

            // Prepare data - FIXED: Sesuai struktur database
            $input_data = [
                'proposal_mahasiswa_id' => $proposal->id,
                'mahasiswa_id' => $mahasiswa_id, // Untuk validasi saja
                'nama_mahasiswa' => $this->input->post('nama_mahasiswa'),
                'nim' => $this->input->post('nim'),
                'semester' => $this->input->post('semester'),
                'program_studi' => $this->input->post('program_studi'),
                'judul_skripsi_terbaru' => $this->input->post('judul_skripsi_terbaru'),
                'tempat_penelitian' => $this->input->post('tempat_penelitian'),
                'tanggal_mulai_penelitian' => $this->input->post('tanggal_mulai_penelitian'),
                'tanggal_selesai_penelitian' => $this->input->post('tanggal_selesai_penelitian'),
                'dosen_pembimbing_id' => $proposal->dosen_id
            ];

            // Submit permohonan
            $result = $this->penelitian->create_permohonan($input_data);

            if ($result['error']) {
                $this->session->set_flashdata('error', $result['message']);
            } else {
                $this->session->set_flashdata('success', 'Permohonan izin penelitian berhasil diajukan');
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
                $steps[3]['status'] = 'active';
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