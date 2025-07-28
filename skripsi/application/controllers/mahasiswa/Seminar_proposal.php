<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller - Role Mahasiswa (FIXED VERSION)
 * Updated untuk menggunakan template mahasiswa.php yang konsisten
 * 
 * Controller untuk mengelola seminar proposal dari sisi mahasiswa
 * Sesuai dengan workflow yang telah ditetapkan
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.2 (Fixed Final)
 */

class Seminar_proposal extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Load core libraries first
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
        
        // Debug mode untuk development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            log_message('debug', 'Seminar_proposal constructor called - Session: ' . json_encode($this->session->userdata()));
        }
        
        // Check authentication dengan debugging
        if (!$this->session->userdata('logged_in')) {
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar_proposal: User not logged in');
            }
            redirect('auth/login');
            return;
        }
        
        $user_level = $this->session->userdata('level');
        if ($user_level !== '3') {  // '3' untuk mahasiswa
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar_proposal: Invalid user level: ' . $user_level);
            }
            $this->session->set_flashdata('error', 'Akses ditolak. Anda bukan mahasiswa.');
            redirect('auth/login');
            return;
        }
        
        // Load additional libraries after auth check
        $this->load->library(['form_validation', 'upload']);
        $this->load->helper(['file', 'security']);
        
        // Try to load model, with fallback
        try {
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_proposal_mahasiswa_model: ' . $e->getMessage());
            }
            // Create simple fallback if model doesn't exist
            $this->seminar_model = $this->_create_fallback_model();
        }
    }

    // =================================================================
    // MAIN PAGES
    // =================================================================

    /**
     * Dashboard Seminar Proposal Mahasiswa
     * URL: https://stkyakobus.ac.id/skripsi/mahasiswa/seminar_proposal
     * URL: https://stkyakobus.ac.id/skripsi/mahasiswa/seminar (redirect)
     */
    public function index()
    {
        try {
            $mahasiswa_id = $this->session->userdata('id');
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar_proposal index called for mahasiswa_id: ' . $mahasiswa_id);
            }
            
            // Get data proposal mahasiswa yang aktif
            $proposal = $this->_get_active_proposal($mahasiswa_id);
            
            if (!$proposal) {
                $data = [
                    'title' => 'Seminar Proposal',
                    'content' => 'mahasiswa/seminar_proposal/no_proposal',
                    'proposal' => null,
                    'seminar_data' => null,
                    'current_step' => 'no_proposal'
                ];
                
                $this->_load_view($data);
                return;
            }

            // Cek data seminar proposal yang sudah ada
            $seminar_data = $this->_get_seminar_by_proposal_id($proposal->id);
            
            // Cek syarat jurnal bimbingan
            $syarat_jurnal = $this->_check_jurnal_requirement($proposal->id);
            
            // Tentukan status dan langkah berikutnya
            $workflow_status = $this->_determine_workflow_status($proposal, $seminar_data, $syarat_jurnal);
            
            $data = [
                'title' => 'Seminar Proposal',
                'content' => 'mahasiswa/seminar_proposal/dashboard',
                'proposal' => $proposal,
                'seminar_data' => $seminar_data,
                'syarat_jurnal' => $syarat_jurnal,
                'workflow_status' => $workflow_status,
                'progress_percentage' => $this->_calculate_progress($workflow_status['current_step'])
            ];
            
            $this->_load_view($data);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Seminar_proposal index error: ' . $e->getMessage());
                show_error('Error: ' . $e->getMessage());
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
                redirect('mahasiswa/dashboard');
            }
        }
    }

    /**
     * Method untuk testing - bisa dihapus setelah sistem stabil
     */
    public function test()
    {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }
        
        echo "<h2>Seminar Proposal Controller Test</h2>";
        echo "<p><strong>Status:</strong> Controller berjalan dengan baik! ✅</p>";
        echo "<p><strong>Base URL:</strong> " . base_url() . "</p>";
        echo "<p><strong>Current URL:</strong> " . current_url() . "</p>";
        echo "<p><strong>Controller:</strong> " . $this->router->class . "</p>";
        echo "<p><strong>Method:</strong> " . $this->router->method . "</p>";
        
        echo "<h3>Session Data:</h3>";
        echo "<pre>" . print_r($this->session->userdata(), true) . "</pre>";
        
        echo "<h3>Database Test:</h3>";
        try {
            $result = $this->db->query("SELECT COUNT(*) as total FROM mahasiswa")->row();
            echo "<p>✅ Database connection OK - Total mahasiswa: " . $result->total . "</p>";
        } catch (Exception $e) {
            echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
        echo "<h3>Model Test:</h3>";
        if (isset($this->seminar_model)) {
            echo "<p>✅ Seminar model loaded successfully</p>";
        } else {
            echo "<p>❌ Seminar model not loaded</p>";
        }
        
        echo "<h3>View Test:</h3>";
        $view_files = [
            'mahasiswa/seminar_proposal/dashboard.php',
            'mahasiswa/seminar_proposal/form_ajukan.php',
            'mahasiswa/seminar_proposal/detail.php',
            'mahasiswa/seminar_proposal/no_proposal.php'
        ];
        
        foreach ($view_files as $view) {
            $path = VIEWPATH . $view;
            if (file_exists($path)) {
                echo "<p>✅ {$view} exists</p>";
            } else {
                echo "<p>❌ {$view} missing</p>";
            }
        }
        
        echo "<h3>Template Test:</h3>";
        $template_normal = VIEWPATH . 'template/mahasiswa.php';
        $template_simple = VIEWPATH . 'template/mahasiswa_simple.php';
        
        if (file_exists($template_normal)) {
            echo "<p>✅ Template mahasiswa.php exists</p>";
        } else {
            echo "<p>❌ Template mahasiswa.php missing</p>";
        }
        
        if (file_exists($template_simple)) {
            echo "<p>✅ Template mahasiswa_simple.php exists</p>";
        } else {
            echo "<p>❌ Template mahasiswa_simple.php missing</p>";
        }
        
        echo "<hr>";
        echo "<p><a href='" . base_url('mahasiswa/seminar_proposal') . "'>← Kembali ke Dashboard Seminar Proposal</a></p>";
    }

    /**
     * Form Pengajuan Seminar Proposal
     */
    public function ajukan($proposal_id = null)
    {
        if (!$proposal_id) {
            $proposal_id = $this->input->get('proposal_id');
        }
        
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak ditemukan.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal data dengan security check
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau bukan milik Anda.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        // Cek syarat jurnal bimbingan
        $syarat_jurnal = $this->_check_jurnal_requirement($proposal->id);
        
        if (!$syarat_jurnal['eligible']) {
            $this->session->set_flashdata('error', 
                "Belum memenuhi syarat pengajuan seminar proposal. " . $syarat_jurnal['message']);
            redirect('mahasiswa/seminar_proposal');
        }
        
        // Cek apakah sudah pernah mengajukan
        $existing_seminar = $this->_get_seminar_by_proposal_id($proposal->id);
        
        $data = [
            'title' => $existing_seminar ? 'Edit Pengajuan Seminar Proposal' : 'Ajukan Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/form_ajukan',
            'proposal' => $proposal,
            'existing_seminar' => $existing_seminar,
            'syarat_jurnal' => $syarat_jurnal,
            'is_edit' => (bool) $existing_seminar,
            'can_edit' => $existing_seminar ? in_array($existing_seminar->status, ['draft', 'rejected']) : true
        ];
        
        // Load daftar jurnal bimbingan yang sudah divalidasi
        $data['jurnal_validasi'] = $this->_get_validated_jurnal($proposal->id);
        
        $this->_load_view($data);
    }

    /**
     * Process Form Pengajuan Seminar Proposal
     */
    public function submit_ajukan()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $proposal_id = $this->input->post('proposal_id');
        $is_edit = $this->input->post('is_edit') === '1';
        
        // Set validation rules
        $this->form_validation->set_rules('proposal_id', 'ID Proposal', 'required|integer');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'required|min_length[10]|max_length[1000]');
        
        if (!$is_edit || empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_rules('file_proposal', 'File Proposal', 'callback__check_file_proposal');
        }
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_proposal/ajukan/' . $proposal_id);
        }
        
        // Verify proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak valid.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        try {
            $this->db->trans_start();
            
            $data = [
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle file upload if provided
            if (!empty($_FILES['file_proposal']['name'])) {
                $file_result = $this->_handle_file_upload('file_proposal', 'proposal_files');
                if ($file_result['status']) {
                    $data['file_proposal'] = $file_result['filename'];
                } else {
                    throw new Exception($file_result['message']);
                }
            }
            
            if ($is_edit) {
                // Update existing record
                $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                if ($existing) {
                    $this->db->where('id', $existing->id);
                    $this->db->update('seminar_proposal_mahasiswa', $data);
                    
                    $this->session->set_flashdata('success', 'Pengajuan seminar proposal berhasil diperbarui.');
                } else {
                    throw new Exception('Data seminar tidak ditemukan untuk diupdate.');
                }
            } else {
                // Create new record
                $data['proposal_id'] = $proposal_id;
                $data['mahasiswa_id'] = $mahasiswa_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                
                $this->db->insert('seminar_proposal_mahasiswa', $data);
                
                $this->session->set_flashdata('success', 'Pengajuan seminar proposal berhasil dikirim. Menunggu review dari dosen pembimbing.');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Seminar proposal submission error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_proposal');
    }

    /**
     * Detail Seminar Proposal
     */
    public function detail($id = null)
    {
        if (!$id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get seminar detail dengan security check
        $seminar = $this->_get_seminar_by_id($id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar proposal tidak ditemukan.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        // Get proposal data
        $proposal = $this->_get_proposal_by_id($seminar->proposal_id, $mahasiswa_id);
        
        // Get jurnal bimbingan yang sudah divalidasi
        $jurnal_validasi = $this->_get_validated_jurnal($seminar->proposal_id);
        
        // Get riwayat aktivitas (jika tabel ada)
        $riwayat = $this->_get_activity_log($seminar->proposal_id);
        
        $data = [
            'title' => 'Detail Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/detail',
            'seminar' => $seminar,
            'proposal' => $proposal,
            'jurnal_validasi' => $jurnal_validasi,
            'riwayat' => $riwayat
        ];
        
        $this->_load_view($data);
    }

    // =================================================================
    // HELPER METHODS
    // =================================================================

    /**
     * FIXED: Load view dengan template mahasiswa.php (PRIORITAS UTAMA)
     */
    private function _load_view($data)
    {
        try {
            // PERBAIKAN UTAMA: Gunakan template mahasiswa.php sebagai prioritas
            if (file_exists(VIEWPATH . 'template/mahasiswa.php')) {
                $this->load->view('template/mahasiswa', $data);
            } else {
                // Fallback ke template simple jika template utama tidak ada
                if (file_exists(VIEWPATH . 'template/mahasiswa_simple.php')) {
                    $this->load->view('template/mahasiswa_simple', $data);
                } else {
                    throw new Exception('Template mahasiswa tidak ditemukan');
                }
            }
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                show_error('Template error: ' . $e->getMessage());
            } else {
                echo "<h1>Error Loading Page</h1><p>Please contact administrator.</p>";
            }
        }
    }

    /**
     * Get active proposal untuk mahasiswa
     */
    private function _get_active_proposal($mahasiswa_id)
    {
        try {
            $this->db->select('pm.*, d.nama as nama_pembimbing, p.nama as nama_prodi');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status', '1'); // Status aktif
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting active proposal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get proposal by ID dengan security check
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('pm.*, d.nama as nama_pembimbing');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            
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
            if (!$this->db->table_exists('seminar_proposal_mahasiswa')) {
                return null;
            }
            
            $this->db->where('proposal_id', $proposal_id);
            return $this->db->get('seminar_proposal_mahasiswa')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by proposal ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get seminar by ID dengan security check
     */
    private function _get_seminar_by_id($id, $mahasiswa_id)
    {
        try {
            if (!$this->db->table_exists('seminar_proposal_mahasiswa')) {
                return null;
            }
            
            $this->db->where('id', $id);
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            return $this->db->get('seminar_proposal_mahasiswa')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check jurnal requirement
     */
    private function _check_jurnal_requirement($proposal_id)
    {
        try {
            $min_required = 5; // Dapat disesuaikan
            
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [
                    'eligible' => false,
                    'jurnal_validated_count' => 0,
                    'minimum_required' => $min_required,
                    'missing' => $min_required,
                    'message' => 'Tabel jurnal bimbingan belum tersedia'
                ];
            }
            
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $count = $this->db->count_all_results('jurnal_bimbingan');
            
            return [
                'eligible' => $count >= $min_required,
                'jurnal_validated_count' => $count,
                'minimum_required' => $min_required,
                'missing' => max(0, $min_required - $count),
                'message' => $count >= $min_required ? 
                    'Memenuhi syarat untuk mengajukan seminar proposal' : 
                    "Perlu " . ($min_required - $count) . " jurnal bimbingan lagi yang divalidasi dosen"
            ];
        } catch (Exception $e) {
            log_message('error', 'Error checking jurnal requirement: ' . $e->getMessage());
            return [
                'eligible' => false,
                'jurnal_validated_count' => 0,
                'minimum_required' => 5,
                'missing' => 5,
                'message' => 'Error checking jurnal requirement'
            ];
        }
    }

    /**
     * Get validated jurnal
     */
    private function _get_validated_jurnal($proposal_id)
    {
        try {
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [];
            }
            
            $this->db->select('jb.*, d.nama as nama_validator');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->where('jb.status_validasi', '1');
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting validated jurnal: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Determine workflow status berdasarkan kondisi saat ini
     */
    private function _determine_workflow_status($proposal, $seminar_data, $syarat_jurnal)
    {
        if (!$syarat_jurnal['eligible']) {
            return [
                'current_step' => 'belum_eligible',
                'next_action' => 'Lengkapi jurnal bimbingan',
                'status_text' => 'Belum Memenuhi Syarat',
                'status_class' => 'warning'
            ];
        }
        
        if (!$seminar_data) {
            return [
                'current_step' => 'belum_mengajukan',
                'next_action' => 'Ajukan Seminar Proposal',
                'status_text' => 'Siap Mengajukan',
                'status_class' => 'info'
            ];
        }
        
        switch ($seminar_data->status) {
            case 'draft':
                return [
                    'current_step' => 'draft',
                    'next_action' => 'Lengkapi dan Submit',
                    'status_text' => 'Draft',
                    'status_class' => 'secondary'
                ];
                
            case 'submitted':
                return [
                    'current_step' => 'review_pembimbing',
                    'next_action' => 'Menunggu review pembimbing',
                    'status_text' => 'Sedang Direview Pembimbing',
                    'status_class' => 'warning'
                ];
                
            case 'approved':
                return [
                    'current_step' => 'approved',
                    'next_action' => 'Menunggu penjadwalan',
                    'status_text' => 'Disetujui',
                    'status_class' => 'success'
                ];
                
            case 'rejected':
                return [
                    'current_step' => 'rejected',
                    'next_action' => 'Perbaiki dan ajukan ulang',
                    'status_text' => 'Ditolak',
                    'status_class' => 'danger'
                ];
                
            default:
                return [
                    'current_step' => 'unknown',
                    'next_action' => 'Hubungi admin',
                    'status_text' => 'Status Tidak Dikenali',
                    'status_class' => 'secondary'
                ];
        }
    }

    /**
     * Calculate progress percentage
     */
    private function _calculate_progress($current_step)
    {
        $steps = [
            'belum_eligible' => 10,
            'belum_mengajukan' => 20,
            'draft' => 25,
            'submitted' => 30,
            'review_pembimbing' => 50,
            'review_kaprodi' => 70,
            'approved' => 80,
            'scheduled' => 90,
            'completed' => 100,
            'rejected' => 30
        ];
        
        return $steps[$current_step] ?? 0;
    }

    /**
     * Handle file upload
     */
    private function _handle_file_upload($field_name, $subfolder = '')
    {
        $upload_path = FCPATH . 'uploads/seminar_proposal/';
        if ($subfolder) {
            $upload_path .= $subfolder . '/';
        }
        
        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 1024, // 1MB
            'file_name' => 'proposal_' . time() . '_' . rand(1000, 9999)
        ];
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload($field_name)) {
            return [
                'status' => true,
                'filename' => $this->upload->data('file_name'),
                'message' => 'File uploaded successfully'
            ];
        } else {
            return [
                'status' => false,
                'filename' => null,
                'message' => $this->upload->display_errors()
            ];
        }
    }

    /**
     * File validation callback
     */
    public function _check_file_proposal()
    {
        if (empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_message('_check_file_proposal', 'File proposal harus diupload');
            return false;
        }
        
        $allowed_types = ['pdf', 'doc', 'docx'];
        $file_ext = pathinfo($_FILES['file_proposal']['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_ext), $allowed_types)) {
            $this->form_validation->set_message('_check_file_proposal', 'Format file harus PDF, DOC, atau DOCX');
            return false;
        }
        
        if ($_FILES['file_proposal']['size'] > 1048576) { // 1MB
            $this->form_validation->set_message('_check_file_proposal', 'Ukuran file maksimal 1MB');
            return false;
        }
        
        return true;
    }

    /**
     * Get activity log (jika tabel ada)
     */
    private function _get_activity_log($proposal_id)
    {
        try {
            if (!$this->db->table_exists('seminar_proposal_log')) {
                return [];
            }
            
            $this->db->select('spl.*, u.nama as user_nama');
            $this->db->from('seminar_proposal_log spl');
            $this->db->join('mahasiswa u', 'spl.user_id = u.id AND spl.user_type = "mahasiswa"', 'left');
            $this->db->join('dosen d', 'spl.user_id = d.id AND spl.user_type = "dosen"', 'left');
            $this->db->where('spl.proposal_id', $proposal_id);
            $this->db->order_by('spl.created_at', 'DESC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting activity log: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create fallback model jika model utama tidak ada
     */
    private function _create_fallback_model()
    {
        return new stdClass();
    }
}