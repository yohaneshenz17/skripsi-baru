<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller - Role Mahasiswa (Phase 5) - FIXED VERSION
 * 
 * Controller untuk mengelola pengajuan seminar skripsi mahasiswa
 * PERBAIKAN: Simplified 3 requirements + workflow status fix
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.1 (Fixed Version)
 */

class Seminar_skripsi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Load core libraries
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form', 'text']);

        // Debug mode untuk development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            log_message('debug', 'Seminar_skripsi constructor called - Session: ' . json_encode($this->session->userdata()));
        }
        
        // Check authentication - Level 3 = Mahasiswa
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') !== '3') {
            redirect('auth/login');
            return;
        }
        
        // Load additional libraries after auth check
        $this->load->library(['form_validation', 'upload', 'email']);
        $this->load->helper(['file', 'security']);
        
        // Load model dengan fallback
        try {
            $this->load->model('Seminar_skripsi_mahasiswa_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_skripsi_mahasiswa_model: ' . $e->getMessage());
            }
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        }
    }

    /**
     * Index - Dashboard Seminar Skripsi untuk Mahasiswa
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'Seminar_skripsi index called for mahasiswa_id: ' . $mahasiswa_id);
        }

        try {
            // Get data seminar skripsi mahasiswa
            $data = $this->_prepare_dashboard_data($mahasiswa_id);
            
            // Load view dengan template mahasiswa
            $this->load->view('template/mahasiswa', [
                'title' => 'Seminar Skripsi',
                'content' => $this->load->view('mahasiswa/seminar_skripsi/index', $data, TRUE),
                'script' => $this->_get_index_script()
            ]);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Seminar_skripsi index error: ' . $e->getMessage());
            }
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data.');
            redirect('mahasiswa/dashboard');
        }
    }

    /**
     * Form pengajuan/edit seminar skripsi
     */
    public function pengajuan($proposal_id = null)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validate proposal_id
        if (!$proposal_id || !is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Get dan validate proposal ownership & eligibility
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak valid atau bukan milik Anda.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Check eligibility untuk seminar skripsi
        $eligibility = $this->_check_simple_eligibility($proposal_id);
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat: ' . implode(', ', $eligibility['errors']));
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Check existing seminar skripsi
        $existing_seminar = $this->_get_seminar_by_proposal_id($proposal_id);
        $is_edit = $existing_seminar ? true : false;

        // Handle form submission
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->_handle_submission($proposal_id, $is_edit);
            return;
        }

        // Prepare form data
        $data = [
            'proposal' => $proposal,
            'existing_seminar' => $existing_seminar,
            'is_edit' => $is_edit,
            'eligibility' => $eligibility
        ];

        // Load form view
        $this->load->view('template/mahasiswa', [
            'title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Ajukan Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/form', $data, TRUE),
            'script' => $this->_get_form_script()
        ]);
    }

    /**
     * Detail seminar skripsi
     */
    public function detail($seminar_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$seminar_id || !is_numeric($seminar_id)) {
            $this->session->set_flashdata('error', 'ID seminar tidak valid.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Get seminar dengan ownership check
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan milik Anda.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $data = [
            'seminar' => $seminar,
            'progress' => $this->_calculate_seminar_progress($seminar)
        ];

        $this->load->view('template/mahasiswa', [
            'title' => 'Detail Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/detail', $data, TRUE)
        ]);
    }

    /**
     * Download file skripsi
     */
    public function download($seminar_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        
        if (!$seminar || !$seminar->file_skripsi) {
            $this->session->set_flashdata('error', 'File tidak ditemukan.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $file_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $seminar->file_skripsi;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Set headers untuk download/view
        $this->load->helper('download');
        $data = file_get_contents($file_path);
        $name = 'skripsi_' . $seminar->nim . '_' . $seminar->id . '.pdf';
        
        force_download($name, $data);
    }

    // =================================================================
    // PRIVATE HELPER METHODS - FIXED VERSION
    // =================================================================

    /**
     * FIXED: Prepare dashboard data
     */
    private function _prepare_dashboard_data($mahasiswa_id)
    {
        // Get proposal yang eligible (dengan 3 syarat sederhana)
        $eligible_proposals = $this->_get_eligible_proposals($mahasiswa_id);
        
        // Get existing seminar skripsi
        $existing_seminars = $this->_get_existing_seminars($mahasiswa_id);
        
        // Get progress summary
        $progress_summary = $this->_get_progress_summary($mahasiswa_id);
        
        // Set variable untuk view
        $data = [
            'proposals_eligible' => $eligible_proposals,
            'existing_seminars' => $existing_seminars,
            'progress_summary' => $progress_summary,
            'can_create_new' => count($eligible_proposals) > 0,
            'has_eligible_proposals' => count($eligible_proposals) > 0
        ];
        
        // Set action URL dan text jika ada proposal eligible
        if (count($eligible_proposals) > 0) {
            $first_proposal = $eligible_proposals[0];
            $data['action_url'] = base_url('mahasiswa/seminar_skripsi/pengajuan/' . $first_proposal->id);
            $data['action_text'] = 'Ajukan Seminar Skripsi';
            $data['action_class'] = 'btn-success';
            $data['action_proposal_id'] = $first_proposal->id;
        }
        
        return $data;
    }

    /**
     * FIXED: Get eligible proposals berdasarkan 3 syarat sederhana
     */
    private function _get_eligible_proposals($mahasiswa_id)
    {
        try {
            // Query proposals yang memenuhi syarat dasar
            $this->db->select('pm.id, pm.judul, pm.workflow_status, pm.mahasiswa_id');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1'); // Proposal aktif
            
            // PERBAIKAN: Allow 'penelitian' workflow_status (tidak harus 'seminar_skripsi')
            $this->db->where_in('pm.workflow_status', ['penelitian', 'seminar_skripsi']);
            
            $proposals = $this->db->get()->result();
            
            if (empty($proposals)) {
                return [];
            }
            
            $eligible_proposals = [];
            
            foreach ($proposals as $proposal) {
                // Check 3 syarat untuk setiap proposal
                $eligibility = $this->_check_simple_eligibility($proposal->id);
                
                if ($eligibility['eligible']) {
                    $proposal->eligibility_details = $eligibility;
                    $eligible_proposals[] = $proposal;
                }
            }
            
            return $eligible_proposals;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting eligible proposals: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * NEW: Check eligibility dengan 3 syarat sederhana
     */
    private function _check_simple_eligibility($proposal_id)
    {
        $requirements = [];
        $errors = [];
        
        try {
            // SYARAT 1: 14 jurnal bimbingan tervalidasi
            $this->db->select('COUNT(*) as count');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $jurnal_count = $this->db->get()->row()->count;
            
            $requirements['jurnal'] = [
                'name' => 'Jurnal Bimbingan',
                'current' => $jurnal_count,
                'required' => 14,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $errors[] = "Perlu " . (14 - $jurnal_count) . " jurnal bimbingan lagi";
            }
            
            // SYARAT 2: Seminar proposal completed dengan nilai
            $this->db->select('COUNT(*) as count');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $semprop_count = $this->db->get()->row()->count;
            
            $requirements['seminar_proposal'] = [
                'name' => 'Seminar Proposal',
                'current' => $semprop_count,
                'required' => 1,
                'met' => $semprop_count >= 1
            ];
            
            if ($semprop_count < 1) {
                $errors[] = "Belum lulus seminar proposal";
            }
            
            // SYARAT 3: Surat izin penelitian diajukan
            $this->db->select('COUNT(*) as count');
            $this->db->from('penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $penelitian_count = $this->db->get()->row()->count;
            
            $requirements['penelitian'] = [
                'name' => 'Surat Izin Penelitian',
                'current' => $penelitian_count,
                'required' => 1,
                'met' => $penelitian_count >= 1
            ];
            
            if ($penelitian_count < 1) {
                $errors[] = "Belum mengajukan surat izin penelitian";
            }
            
            return [
                'eligible' => empty($errors),
                'requirements' => $requirements,
                'errors' => $errors,
                'summary' => empty($errors) ? 'Memenuhi syarat untuk seminar skripsi' : implode(', ', $errors)
            ];
            
        } catch (Exception $e) {
            return [
                'eligible' => false,
                'requirements' => [],
                'errors' => ['Error sistem: ' . $e->getMessage()],
                'summary' => 'Terjadi kesalahan sistem'
            ];
        }
    }

    /**
     * Get existing seminars - berdasarkan struktur database yang benar
     */
    private function _get_existing_seminars($mahasiswa_id)
    {
        try {
            // Prioritas 1: Gunakan view seminar_skripsi_mahasiswa_v jika ada
            if ($this->db->table_exists('seminar_skripsi_mahasiswa_v')) {
                $this->db->select('*');
                $this->db->from('seminar_skripsi_mahasiswa_v');
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                $this->db->order_by('created_at', 'DESC');
                $result = $this->db->get()->result();
                
                if (!empty($result)) {
                    return $result;
                }
            }
            
            // Fallback: Query direct table dengan join manual
            $this->db->select('ss.*, pm.judul, m.nim, m.nama as nama_mahasiswa');
            $this->db->from('seminar_skripsi_mahasiswa ss');
            $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
            $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ss.created_at', 'DESC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting existing seminars: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get progress summary
     */
    private function _get_progress_summary($mahasiswa_id)
    {
        $summary = [
            'total_proposals' => 0,
            'eligible_proposals' => 0,
            'submitted_seminars' => 0,
            'completed_seminars' => 0,
            'current_phase' => 'Tidak ada proposal aktif'
        ];
        
        try {
            // Total proposals mahasiswa
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $summary['total_proposals'] = $result ? $result->total : 0;
            
            // Eligible proposals (workflow_status = 'penelitian' atau 'seminar_skripsi')
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where_in('workflow_status', ['penelitian', 'seminar_skripsi']);
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $summary['eligible_proposals'] = $result ? $result->total : 0;
            
            // Submitted seminars
            if ($this->db->table_exists('seminar_skripsi_mahasiswa')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('seminar_skripsi_mahasiswa');
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                $result = $this->db->get()->row();
                $summary['submitted_seminars'] = $result ? $result->total : 0;
                
                // Completed seminars
                $this->db->select('COUNT(*) as total');
                $this->db->from('seminar_skripsi_mahasiswa');
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                $this->db->where('status', 'completed');
                $result = $this->db->get()->row();
                $summary['completed_seminars'] = $result ? $result->total : 0;
            }
            
            // Determine current phase
            if ($summary['eligible_proposals'] > 0) {
                $summary['current_phase'] = 'Seminar Skripsi';
            } else if ($summary['total_proposals'] > 0) {
                $summary['current_phase'] = 'Belum siap seminar skripsi';
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error getting progress summary: ' . $e->getMessage());
        }
        
        return $summary;
    }

    /**
     * Get proposal by ID dengan ownership check
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1');
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get seminar by proposal ID
     */
    private function _get_seminar_by_proposal_id($proposal_id)
    {
        try {
            if (!$this->db->table_exists('seminar_skripsi_mahasiswa')) {
                return null;
            }
            
            $this->db->select('*');
            $this->db->from('seminar_skripsi_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by proposal ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get seminar detail dengan ownership check
     */
    private function _get_seminar_detail($seminar_id, $mahasiswa_id)
    {
        try {
            // Prioritas gunakan view jika ada
            if ($this->db->table_exists('seminar_skripsi_mahasiswa_v')) {
                $this->db->select('*');
                $this->db->from('seminar_skripsi_mahasiswa_v');
                $this->db->where('id', $seminar_id);
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                
                return $this->db->get()->row();
            }
            
            // Fallback: Manual join
            $this->db->select('ss.*, pm.judul, m.nim, m.nama as nama_mahasiswa');
            $this->db->from('seminar_skripsi_mahasiswa ss');
            $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
            $this->db->where('ss.id', $seminar_id);
            $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle form submission
     */
    private function _handle_submission($proposal_id, $is_edit = false)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validation rules
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim|max_length[1000]');
        
        if (!$is_edit || !empty($_FILES['file_skripsi']['name'])) {
            $this->form_validation->set_rules('file_skripsi', 'File Skripsi', 'callback__validate_file_upload');
        }
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
            return;
        }
        
        // Handle file upload jika ada
        $file_name = null;
        if (!empty($_FILES['file_skripsi']['name'])) {
            $file_name = $this->_handle_file_upload();
            if (!$file_name) {
                redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
                return;
            }
        }
        
        // Prepare data
        $data = [
            'proposal_id' => $proposal_id,
            'mahasiswa_id' => $mahasiswa_id,
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'status' => 'submitted',
            'current_step' => 'pembimbing'
        ];
        
        if ($file_name) {
            $data['file_skripsi'] = $file_name;
        }
        
        try {
            if ($is_edit) {
                // Update existing
                $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                $data['updated_at'] = date('Y-m-d H:i:s');
                
                $this->db->where('id', $existing->id);
                $this->db->update('seminar_skripsi_mahasiswa', $data);
                
                $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil diperbarui.');
            } else {
                // Create new
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                
                $this->db->insert('seminar_skripsi_mahasiswa', $data);
                
                $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim.');
            }
            
            // Send notification (optional)
            $this->_send_notification($proposal_id, $is_edit ? 'updated' : 'created');
            
        } catch (Exception $e) {
            log_message('error', 'Error saving seminar skripsi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data.');
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * File upload validation callback
     */
    public function _validate_file_upload($str)
    {
        if (empty($_FILES['file_skripsi']['name'])) {
            $this->form_validation->set_message('_validate_file_upload', 'File skripsi wajib diupload.');
            return FALSE;
        }
        
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_ext = pathinfo($_FILES['file_skripsi']['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            $this->form_validation->set_message('_validate_file_upload', 'File harus berformat PDF, DOC, atau DOCX.');
            return FALSE;
        }
        
        if ($_FILES['file_skripsi']['size'] > 2097152) { // 2MB
            $this->form_validation->set_message('_validate_file_upload', 'Ukuran file maksimal 2MB.');
            return FALSE;
        }
        
        return TRUE;
    }

    /**
     * Handle file upload
     */
    private function _handle_file_upload()
    {
        $upload_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 2048, // 2MB
            'file_name' => 'skripsi_' . time() . '_' . uniqid()
        ];
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('file_skripsi')) {
            return $this->upload->data('file_name');
        } else {
            $this->session->set_flashdata('error', 'Error upload file: ' . $this->upload->display_errors('', ''));
            return false;
        }
    }

    /**
     * Calculate seminar progress
     */
    private function _calculate_seminar_progress($seminar)
    {
        $steps = [
            [
                'key' => 'pengajuan',
                'title' => 'Pengajuan',
                'icon' => 'fa-paper-plane',
                'description' => 'Mahasiswa mengajukan seminar skripsi',
                'completed' => !in_array($seminar->status, ['draft']),
                'active' => $seminar->status == 'submitted'
            ],
            [
                'key' => 'review_pembimbing', 
                'title' => 'Review Pembimbing',
                'icon' => 'fa-user-check',
                'description' => 'Dosen pembimbing melakukan review',
                'completed' => !in_array($seminar->status, ['draft', 'submitted', 'review_pembimbing']),
                'active' => $seminar->status == 'review_pembimbing'
            ],
            [
                'key' => 'review_kaprodi',
                'title' => 'Review Kaprodi', 
                'icon' => 'fa-user-graduate',
                'description' => 'Kaprodi melakukan validasi',
                'completed' => in_array($seminar->status, ['approved', 'scheduled', 'completed']),
                'active' => $seminar->status == 'review_kaprodi'
            ],
            [
                'key' => 'penjadwalan',
                'title' => 'Penjadwalan',
                'icon' => 'fa-calendar',
                'description' => 'Penentuan jadwal, tempat, dan penguji',
                'completed' => in_array($seminar->status, ['scheduled', 'completed']),
                'active' => $seminar->status == 'approved'
            ],
            [
                'key' => 'pelaksanaan',
                'title' => 'Pelaksanaan Seminar',
                'icon' => 'fa-presentation',
                'description' => 'Seminar skripsi dilaksanakan',
                'completed' => $seminar->status == 'completed',
                'active' => $seminar->status == 'scheduled'
            ]
        ];

        $progress_percentage = 0;
        switch ($seminar->status) {
            case 'draft': $progress_percentage = 0; break;
            case 'submitted':
            case 'review_pembimbing': $progress_percentage = 20; break;
            case 'review_kaprodi': $progress_percentage = 40; break;
            case 'approved': $progress_percentage = 60; break;
            case 'scheduled': $progress_percentage = 80; break;
            case 'completed': $progress_percentage = 100; break;
        }

        return [
            'steps' => $steps,
            'percentage' => $progress_percentage,
            'current_step' => $seminar->current_step,
            'status' => $seminar->status
        ];
    }

    /**
     * Send notification (optional)
     */
    private function _send_notification($proposal_id, $action_type = 'created')
    {
        try {
            // Get proposal dan mahasiswa data
            $proposal = $this->_get_proposal_by_id($proposal_id, $this->session->userdata('id'));
            if (!$proposal) return;
            
            // Get pembimbing data
            $this->db->select('email, nama');
            $this->db->from('dosen');
            $this->db->where('id', $proposal->dosen_id);
            $pembimbing = $this->db->get()->row();
            
            if ($pembimbing && $pembimbing->email) {
                $mahasiswa_name = $proposal->nama_mahasiswa;
                $nim = $proposal->nim;
                
                $subject = "Pengajuan Seminar Skripsi - {$mahasiswa_name}";
                $message = "Pengajuan seminar skripsi telah ";
                $message .= ($action_type === 'updated' ? 'diperbarui' : 'dikirim') . " oleh mahasiswa:\n\n";
                $message .= "Nama: {$mahasiswa_name}\n";
                $message .= "NIM: {$nim}\n";
                $message .= "Judul: {$proposal->judul}\n\n";
                $message .= "Silakan login ke sistem untuk melakukan review.";
                
                // Implementasi send email sesuai config sistem
                // send_email_notification($pembimbing->email, $subject, $message);
            }
        } catch (Exception $e) {
            log_message('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Get JavaScript untuk index page
     */
    private function _get_index_script()
    {
        return '
        <script>
        $(document).ready(function() {
            // Refresh data setiap 30 detik
            setInterval(function() {
                // Optional: Auto refresh data
            }, 30000);
            
            // Tooltip initialization
            $("[data-toggle=\"tooltip\"]").tooltip();
        });
        </script>';
    }

    /**
     * Get JavaScript untuk form page
     */
    private function _get_form_script()
    {
        return '
        <script>
        $(document).ready(function() {
            // File upload preview
            $("#file_skripsi").change(function() {
                var file = this.files[0];
                if (file) {
                    var fileSize = (file.size / 1024 / 1024).toFixed(2);
                    $("#file-info").html("File: " + file.name + " (" + fileSize + " MB)");
                }
            });
            
            // Form validation
            $("#form-pengajuan").submit(function() {
                var isValid = true;
                
                if ($("#file_skripsi").get(0).files.length === 0 && !$("#existing_file").length) {
                    alert("Silakan pilih file skripsi.");
                    isValid = false;
                }
                
                return isValid;
            });
        });
        </script>';
    }
}