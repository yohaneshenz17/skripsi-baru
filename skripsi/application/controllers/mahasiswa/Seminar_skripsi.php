<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller - Complete Fixed Version
 * 
 * FIXED: Removed proposal status = '1' requirement
 * Controller lengkap untuk mengelola pengajuan seminar skripsi mahasiswa
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.2 (Complete Fixed Version)
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
            $this->load->model('Seminar_skripsi_model', 'seminar_model');  // ✅ FIXED
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_skripsi_model: ' . $e->getMessage());
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
            'eligibility' => $eligibility,
            'form_title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi', // FIX ERROR
            'requirements' => ['requirements' => [], 'all_met' => true] // FIX ERROR
        ];

        // Load form view
        $this->load->view('template/mahasiswa', [
            'title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Ajukan Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/pengajuan', $data, TRUE),
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
     * FIXED: Prepare dashboard data tanpa syarat proposal aktif
     */
    private function _prepare_dashboard_data($mahasiswa_id)
    {
        try {
            // ✅ DIRECT QUERY: Cek seminar existing
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('created_at', 'DESC');
            $existing_seminar = $this->db->get('seminar_skripsi_mahasiswa')->row();
            
            if ($existing_seminar) {
                // ✅ ADA SEMINAR: Tampilkan progress
                return [
                    'has_existing_seminar' => true,
                    'current_seminar' => $existing_seminar,
                    'show_progress' => true,
                    'show_form' => false,
                    'status_text' => 'Menunggu Review Dosen Pembimbing',
                    'progress_percentage' => 20
                ];
            } else {
                // ✅ TIDAK ADA: Tampilkan eligibility check
                $eligible_proposals = $this->_get_eligible_proposals($mahasiswa_id);
                return [
                    'has_existing_seminar' => false,
                    'proposals_eligible' => $eligible_proposals,
                    'can_create_new' => count($eligible_proposals) > 0,
                    'show_form' => true,
                    'show_progress' => false
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard data error: ' . $e->getMessage());
            return ['has_existing_seminar' => false, 'show_form' => true];
        }
    }

    /**
     * FIXED: Get eligible proposals TANPA syarat status = '1'
     */
    private function _get_eligible_proposals($mahasiswa_id)
    {
        try {
            // FIXED: Query proposals TANPA filter status = '1'
            $this->db->select('pm.id, pm.judul, pm.workflow_status, pm.mahasiswa_id, pm.status');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
 
            // Allow workflow status penelitian DAN seminar_skripsi
            $this->db->where_in('pm.workflow_status', ['penelitian', 'seminar_skripsi']);
            
            $proposals = $this->db->get()->result();
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', "Found " . count($proposals) . " proposals for mahasiswa {$mahasiswa_id}");
                foreach ($proposals as $p) {
                    log_message('debug', "Proposal ID: {$p->id}, workflow: {$p->workflow_status}, status: {$p->status}");
                }
            }
            
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
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', "Final eligible proposals: " . count($eligible_proposals));
            }
            
            return $eligible_proposals;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting eligible proposals: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check eligibility dengan 3 syarat sederhana (FIXED tabel penelitian)
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
            
            // SYARAT 2: Seminar proposal completed
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
            
            // SYARAT 3: FIXED - Surat izin penelitian (tabel yang benar)
            $penelitian_count = 0;
            
            // Cek di tabel permohonan_izin_penelitian (tabel yang benar)
            if ($this->db->table_exists('permohonan_izin_penelitian')) {
                $this->db->select('COUNT(*) as count');
                $this->db->from('permohonan_izin_penelitian');
                $this->db->where('proposal_mahasiswa_id', $proposal_id);
                $penelitian_count = $this->db->get()->row()->count;
            }
            
            // Fallback: cek di tabel penelitian (jika ada)
            if ($penelitian_count == 0 && $this->db->table_exists('penelitian')) {
                $this->db->select('COUNT(*) as count');
                $this->db->from('penelitian');
                $this->db->where('proposal_mahasiswa_id', $proposal_id);
                $penelitian_count = $this->db->get()->row()->count;
            }
            
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

    // ===============================================
    // 2. ADD METHOD: _get_existing_seminar_by_mahasiswa()
    // LOKASI: Tambahkan di bagian private methods
    // ===============================================
    
    /**
     * TAMBAHKAN method baru ini:
     */
    private function _get_existing_seminar_by_mahasiswa($mahasiswa_id)
    {
        try {
            // Direct query tanpa dependency pada view
            $this->db->select('
                ssm.*, 
                pm.judul, pm.workflow_status,
                m.nim, m.nama as nama_mahasiswa,
                d.nama as nama_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ssm.created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Get existing seminar error: ' . $e->getMessage());
            return null;
        }
    }
    
    // ===============================================
    // 3. ADD METHOD: _build_progress_data()
    // LOKASI: Tambahkan di bagian private methods  
    // ===============================================
    
    /**
     * TAMBAHKAN method baru ini:
     */
    private function _build_progress_data($seminar)
    {
        // Progress steps definition
        $steps = [
            ['title' => 'Pengajuan Dikirim', 'status' => 'completed'],
            ['title' => 'Review Pembimbing', 'status' => $this->_get_step_status($seminar, 'pembimbing')],
            ['title' => 'Validasi Kaprodi', 'status' => $this->_get_step_status($seminar, 'kaprodi')],
            ['title' => 'Penjadwalan', 'status' => $this->_get_step_status($seminar, 'jadwal')],
            ['title' => 'Pelaksanaan', 'status' => $this->_get_step_status($seminar, 'selesai')]
        ];
        
        // Calculate percentage
        $completed_steps = 1; // Submit selalu completed
        if ($seminar->status_pembimbing == 'approved') $completed_steps++;
        if ($seminar->status_kaprodi == 'approved') $completed_steps++;
        if ($seminar->status == 'scheduled') $completed_steps++;
        if ($seminar->status == 'completed') $completed_steps++;
        
        $percentage = ($completed_steps / 5) * 100;
        
        return [
            'steps' => $steps,
            'percentage' => $percentage,
            'current_status' => $seminar->status,
            'status_pembimbing' => $seminar->status_pembimbing,
            'status_kaprodi' => $seminar->status_kaprodi
        ];
    }
    
    // ===============================================
    // 4. ADD METHOD: _get_step_status()
    // LOKASI: Tambahkan di bagian private methods
    // ===============================================
    
    /**
     * TAMBAHKAN method baru ini:
     */
    private function _get_step_status($seminar, $step)
    {
        switch ($step) {
            case 'pembimbing':
                return ($seminar->status_pembimbing == 'approved') ? 'completed' : 
                       (($seminar->status_pembimbing == 'pending') ? 'active' : 'pending');
                       
            case 'kaprodi':
                return ($seminar->status_kaprodi == 'approved') ? 'completed' :
                       (($seminar->status_kaprodi == 'pending' && $seminar->status_pembimbing == 'approved') ? 'active' : 'pending');
                       
            case 'jadwal':
                return ($seminar->status == 'scheduled' || $seminar->status == 'completed') ? 'completed' : 'pending';
                
            case 'selesai':
                return ($seminar->status == 'completed') ? 'completed' : 'pending';
                
            default:
                return 'pending';
        }
    }

    /**
     * Get existing seminars
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
            if ($this->db->table_exists('seminar_skripsi_mahasiswa')) {
                $this->db->select('ss.*, pm.judul, m.nim, m.nama as nama_mahasiswa');
                $this->db->from('seminar_skripsi_mahasiswa ss');
                $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
                $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
                $this->db->order_by('ss.created_at', 'DESC');
                
                return $this->db->get()->result();
            }
            
            return [];
            
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
            // Total proposals mahasiswa (FIXED: tanpa filter status)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            // REMOVED: $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $summary['total_proposals'] = $result ? $result->total : 0;
            
            // Eligible proposals (workflow_status = 'penelitian' atau 'seminar_skripsi')
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where_in('workflow_status', ['penelitian', 'seminar_skripsi']);
            // REMOVED: $this->db->where('status', '1');
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
     * Get proposal by ID dengan ownership check (FIXED: tanpa filter status)
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        $this->db->select('p.*, d.nama as nama_pembimbing, d.email as email_pembimbing, pr.nama as nama_prodi');
        $this->db->from('proposal_mahasiswa p');
        $this->db->join('dosen d', 'p.dosen_id = d.id', 'left');
        $this->db->join('mahasiswa m', 'p.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
        $this->db->where('p.id', $proposal_id);
        $this->db->where('p.mahasiswa_id', $mahasiswa_id);
        $result = $this->db->get()->row();
        
        // Ensure nama_pembimbing exists
        if ($result && !isset($result->nama_pembimbing)) {
            $result->nama_pembimbing = 'Belum ditetapkan';
        }
        
        return $result;
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
            if ($this->db->table_exists('seminar_skripsi_mahasiswa')) {
                $this->db->select('ss.*, pm.judul, m.nim, m.nama as nama_mahasiswa');
                $this->db->from('seminar_skripsi_mahasiswa ss');
                $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
                $this->db->where('ss.id', $seminar_id);
                $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
                
                return $this->db->get()->row();
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ IMPROVED: Handle form submission with better error handling
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
            // Pastikan tabel ada
            if (!$this->db->table_exists('seminar_skripsi_mahasiswa')) {
                throw new Exception('Tabel seminar_skripsi_mahasiswa belum tersedia');
            }
            
            if ($is_edit) {
                // ✅ IMPROVED: Add validation for existing record
                $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                if (!$existing) {
                    throw new Exception('Data seminar yang akan diupdate tidak ditemukan');
                }
                
                $data['updated_at'] = date('Y-m-d H:i:s');
                
                $this->db->where('id', $existing->id);
                $affected = $this->db->update('seminar_skripsi_mahasiswa', $data);
                
                // ✅ IMPROVED: Check if update actually happened
                if ($affected === 0) {
                    throw new Exception('Tidak ada data yang diupdate');
                }
                
                $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil diperbarui.');
                $action_type = 'updated';
                
            } else {
                // ✅ IMPROVED: Check for duplicate submission
                $existing_check = $this->_get_seminar_by_proposal_id($proposal_id);
                if ($existing_check) {
                    $this->session->set_flashdata('error', 'Pengajuan untuk proposal ini sudah ada. Gunakan fitur edit untuk memperbarui.');
                    redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
                    return;
                }
                
                // Create new
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                
                $insert_id = $this->db->insert('seminar_skripsi_mahasiswa', $data);
                
                // ✅ IMPROVED: Check if insert was successful
                if (!$insert_id) {
                    throw new Exception('Gagal menyimpan data pengajuan');
                }
                
                $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim.');
                $action_type = 'created';
            }
            
            // ✅ IMPROVED: Safe email notification with try-catch
            try {
                $notification_sent = $this->_send_notification($proposal_id, $action_type);
                if ($notification_sent) {
                    log_message('info', "Email notification sent successfully - Proposal ID: {$proposal_id}, Action: {$action_type}");
                } else {
                    log_message('warning', "Email notification failed but process continued - Proposal ID: {$proposal_id}");
                }
            } catch (Exception $email_error) {
                // ✅ IMPROVED: Jangan gagalkan proses utama jika email gagal
                log_message('error', 'Email notification error (process continued): ' . $email_error->getMessage() . " - Proposal ID: {$proposal_id}");
                // Tidak set flashdata error untuk email, karena proses utama berhasil
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error saving seminar skripsi: ' . $e->getMessage() . " - Proposal ID: {$proposal_id}, Mahasiswa ID: {$mahasiswa_id}");
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
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
     * Method untuk pengajuan ulang seminar skripsi yang ditolak
     */
    public function pengajuan_ulang($proposal_id = null)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validate proposal_id
        if (!$proposal_id || !is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // Get existing rejected seminar
        $rejected_seminar = $this->_get_rejected_seminar($proposal_id, $mahasiswa_id);
        if (!$rejected_seminar) {
            $this->session->set_flashdata('error', 'Tidak ada pengajuan yang ditolak untuk diajukan ulang.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // Handle form submission untuk pengajuan ulang
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->_handle_resubmission($proposal_id, $rejected_seminar);
            return;
        }
    
        // Prepare data untuk form pengajuan ulang
        $data = [
            'proposal' => $this->_get_proposal_by_id($proposal_id, $mahasiswa_id),
            'rejected_seminar' => $rejected_seminar,
            'is_resubmission' => true
        ];
    
        // Load view
        $this->load->view('template/mahasiswa', [
            'title' => 'Pengajuan Ulang Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/pengajuan_ulang', $data, TRUE),
            'script' => $this->_get_form_script()
        ]);
    }
    
    /**
     * Get rejected seminar for resubmission
     */
    private function _get_rejected_seminar($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('ss.*, p.judul, p.dosen_id');
            $this->db->from('seminar_skripsi_mahasiswa ss');
            $this->db->join('proposal_mahasiswa p', 'ss.proposal_id = p.id');
            $this->db->where('ss.proposal_id', $proposal_id);
            $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
            $this->db->where('ss.status', 'rejected');
            $this->db->order_by('ss.created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting rejected seminar: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Handle resubmission process
     */
    private function _handle_resubmission($proposal_id, $rejected_seminar)
    {
        // Validation seperti biasa
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'required|trim|max_length[1000]');
        $this->form_validation->set_rules('file_skripsi', 'File Skripsi', 'callback__validate_file_upload');
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi/pengajuan_ulang/' . $proposal_id);
            return;
        }
        
        // Handle file upload
        $file_name = $this->_handle_file_upload();
        if (!$file_name) {
            redirect('mahasiswa/seminar_skripsi/pengajuan_ulang/' . $proposal_id);
            return;
        }
        
        // Create new submission (pengajuan ulang)
        $data = [
            'proposal_id' => $proposal_id,
            'mahasiswa_id' => $this->session->userdata('id'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'file_skripsi' => $file_name,
            'status' => 'resubmitted',
            'current_step' => 'pembimbing',
            'previous_rejection_id' => $rejected_seminar->id,
            'resubmission_count' => ($rejected_seminar->resubmission_count ?? 0) + 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->insert('seminar_skripsi_mahasiswa', $data);
            
            // Send notification untuk pengajuan ulang
            $this->_send_resubmission_notification($proposal_id, $rejected_seminar);
            
            $this->session->set_flashdata('success', 'Pengajuan ulang seminar skripsi berhasil dikirim.');
            redirect('mahasiswa/seminar_skripsi');
            
        } catch (Exception $e) {
            log_message('error', 'Error saving resubmission: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan pengajuan ulang.');
            redirect('mahasiswa/seminar_skripsi/pengajuan_ulang/' . $proposal_id);
        }
    }

    /**
     * Send notification untuk pengajuan ulang
     */
    private function _send_resubmission_notification($proposal_id, $rejected_seminar)
    {
        try {
            $proposal = $this->_get_proposal_by_id($proposal_id, $this->session->userdata('id'));
            $mahasiswa = $this->session->userdata();
            $dosen = $this->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
            
            if ($dosen && $dosen->email) {
                // Setup email config
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
                
                $subject = '🔄 Pengajuan Ulang Seminar Skripsi - ' . $mahasiswa['nama'];
                $message = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: #17a2b8; color: white; padding: 20px; text-align: center;'>
                        <h2>🔄 Pengajuan Ulang Seminar Skripsi</h2>
                    </div>
                    <div style='padding: 20px; background: #f8f9fa;'>
                        <p>Kepada Yth. <strong>{$dosen->nama}</strong>,</p>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                            <p><strong>📋 PENGAJUAN ULANG</strong></p>
                            <p>Mahasiswa telah melakukan perbaikan dan mengajukan ulang seminar skripsi yang sebelumnya ditolak.</p>
                        </div>
                        
                        <h4>Detail Pengajuan:</h4>
                        <ul>
                            <li><strong>Nama:</strong> {$mahasiswa['nama']}</li>
                            <li><strong>NIM:</strong> {$mahasiswa['nim']}</li>
                            <li><strong>Judul:</strong> {$proposal->judul}</li>
                            <li><strong>Tanggal Pengajuan Ulang:</strong> " . date('d F Y, H:i') . " WIB</li>
                            <li><strong>Alasan Penolakan Sebelumnya:</strong> {$rejected_seminar->komentar_pembimbing}</li>
                        </ul>
                        
                        <p><strong>Keterangan Perbaikan dari Mahasiswa:</strong></p>
                        <div style='background: #e7f3ff; padding: 10px; border-radius: 5px; margin: 10px 0;'>
                            {$this->input->post('keterangan_mahasiswa')}
                        </div>
                        
                        <p>Silakan login ke sistem untuk melakukan review pengajuan ulang ini.</p>
                        <p><a href='" . base_url('dosen/seminar_skripsi') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Review Pengajuan Ulang</a></p>
                    </div>
                </div>";
                
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($dosen->email);
                $this->email->subject($subject);
                $this->email->message($message);
                
                return $this->email->send();
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending resubmission notification: ' . $e->getMessage());
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
     * ✅ IMPROVED: Send notification with better error handling and support for resubmission
     */
    private function _send_notification($proposal_id, $action_type = 'created')
    {
        try {
            // Get proposal dengan JOIN untuk data lengkap
            $proposal = $this->_get_proposal_by_id($proposal_id, $this->session->userdata('id'));
            $mahasiswa = $this->session->userdata();
            
            if (!$proposal || !$proposal->dosen_id) {
                log_message('debug', 'No proposal or dosen_id found for notification');
                return false;
            }
            
            // Get dosen email dengan error handling
            $dosen = $this->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
            
            if (!$dosen || !$dosen->email) {
                log_message('warning', 'Dosen not found or no email for dosen_id: ' . $proposal->dosen_id);
                return false;
            }
            
            // ✅ IMPROVED: Better email config with timeout
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls',
                'smtp_timeout' => 30, // ✅ TAMBAHAN: Timeout
                'wordwrap' => TRUE
            ];
            
            $this->email->initialize($config);
            
            // ✅ IMPROVED: Dynamic subject based on action type
            $is_resubmission = ($action_type === 'resubmitted' || $action_type === 'updated');
            $subject_prefix = $is_resubmission ? '🔄 Pengajuan Ulang' : '📋 Pengajuan Baru';
            $subject = $subject_prefix . ' Seminar Skripsi - ' . $mahasiswa['nama'];
            
            // ✅ IMPROVED: Better template with fallbacks
            $nim = isset($mahasiswa['nim']) ? $mahasiswa['nim'] : 'Tidak tersedia';
            $status_text = $is_resubmission ? 'PENGAJUAN ULANG' : 'PENGAJUAN BARU';
            $action_text = $is_resubmission ? 
                'telah melakukan perbaikan dan mengajukan ulang seminar skripsi.' :
                'telah mengajukan seminar skripsi.';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;'>
                    <h2>📋 {$status_text} Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>" . htmlspecialchars($dosen->nama) . "</strong>,</p>
                    
                    <p>Mahasiswa bimbingan Anda <strong>" . htmlspecialchars($mahasiswa['nama']) . "</strong> {$action_text}</p>
                    
                    <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #0056b3; margin: 0 0 10px 0;'>📋 Detail Pengajuan:</h4>
                        <ul style='color: #0056b3; margin: 0; padding-left: 20px;'>
                            <li><strong>Nama:</strong> " . htmlspecialchars($mahasiswa['nama']) . "</li>
                            <li><strong>NIM:</strong> " . htmlspecialchars($nim) . "</li>
                            <li><strong>Judul:</strong> " . htmlspecialchars($proposal->judul) . "</li>
                            <li><strong>Tanggal Pengajuan:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>";
            
            // ✅ IMPROVED: Add different message for resubmission
            if ($is_resubmission) {
                $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>🔄 Informasi Pengajuan Ulang:</h4>
                        <p style='color: #856404; margin: 0;'>
                            Ini adalah pengajuan ulang setelah perbaikan dari feedback sebelumnya. 
                            Mohon review kembali dengan seksama.
                        </p>
                    </div>";
            }
            
            $message .= "
                    <p><strong>Langkah Selanjutnya:</strong></p>
                    <ol style='margin: 10px 0; padding-left: 20px;'>
                        <li>Review file skripsi yang diajukan</li>
                        <li>Berikan rekomendasi (setujui/tolak)</li>
                        <li>Jika disetujui, akan diteruskan ke Kaprodi</li>
                    </ol>
                    
                    <p style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/seminar_skripsi') . "' 
                           style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
                                  color: white; padding: 12px 25px; text-decoration: none; 
                                  border-radius: 5px; display: inline-block; font-weight: bold;'>
                            📝 Review Pengajuan Sekarang
                        </a>
                    </p>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Terima kasih atas perhatian dan bimbingannya.
                    </p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir<br>
                    Email ini dikirim secara otomatis, mohon tidak membalas langsung.
                </div>
            </div>";
            
            // ✅ IMPROVED: Better email setup with error handling
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($dosen->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // ✅ IMPROVED: Send email with detailed logging
            $email_sent = $this->email->send();
            
            if ($email_sent) {
                log_message('info', "Email notification sent successfully - Proposal ID: {$proposal_id}, Dosen: {$dosen->email}, Action: {$action_type}");
            } else {
                $error_msg = $this->email->print_debugger();
                log_message('error', "Failed to send email notification - Proposal ID: {$proposal_id}, Error: {$error_msg}");
            }
            
            return $email_sent;
            
        } catch (Exception $e) {
            log_message('error', 'Exception in _send_notification: ' . $e->getMessage() . ' - Proposal ID: ' . $proposal_id);
            return false;
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