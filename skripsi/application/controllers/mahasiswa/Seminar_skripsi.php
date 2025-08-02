<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller - Role Mahasiswa (Phase 5)
 * 
 * Controller untuk mengelola pengajuan seminar skripsi mahasiswa
 * Menggunakan struktur yang sama dengan Seminar_proposal.php yang sudah stable
 * 
 * 🔄 WORKFLOW PHASE 5:
 * Usulan Proposal > Bimbingan > Seminar Proposal > Penelitian > Seminar Skripsi > Publikasi
 * 
 * Features:
 * - Upload file skripsi final (Word/PDF, max 2MB)
 * - Review pembimbing dan Kaprodi dengan Turnitin check
 * - Penjadwalan dan penunjukan dosen penguji
 * - Progress tracking dengan visual indicator
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Phase 5 Implementation)
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
        
        // Load model untuk seminar skripsi (akan menggunakan model yang sama dengan adaptasi)
        try {
            $this->load->model('Seminar_skripsi_mahasiswa_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_skripsi_mahasiswa_model: ' . $e->getMessage());
            }
            // Fallback ke model seminar proposal dengan alias
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
        $eligibility = $this->_check_seminar_skripsi_eligibility($proposal_id, $mahasiswa_id);
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat: ' . implode(', ', $eligibility['errors']));
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Check existing seminar skripsi
        $existing_seminar = $this->_get_seminar_by_proposal_id($proposal_id);
        $is_edit = $existing_seminar ? true : false;

        // Handle form submission
        if ($this->input->method() === 'post') {
            $this->_handle_submission($proposal_id, $is_edit);
            return;
        }

        // Prepare form data
        $data = [
            'proposal' => $proposal,
            'existing_seminar' => $existing_seminar,
            'is_edit' => $is_edit,
            'requirements' => $eligibility,
            'form_title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Pengajuan Seminar Skripsi'
        ];

        // Load view
        $this->load->view('template/mahasiswa', [
            'title' => $data['form_title'],
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
        
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }

        // Get detail dengan ownership validation
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan milik Anda.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Get progress tracking
        $progress = $this->_get_progress_data($seminar);

        $data = [
            'seminar' => $seminar,
            'progress' => $progress,
            'can_edit' => in_array($seminar->status, ['draft', 'rejected']),
            'can_resubmit' => $seminar->status === 'rejected'
        ];

        // Load view
        $this->load->view('template/mahasiswa', [
            'title' => 'Detail Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/detail', $data, TRUE),
            'script' => $this->_get_detail_script()
        ]);
    }

    /**
     * Download/view file skripsi
     */
    public function view_file($seminar_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        if (!$seminar || empty($seminar->file_skripsi)) {
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
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Prepare dashboard data
     */
    private function _prepare_dashboard_data($mahasiswa_id)
    {
        // Get proposal mahasiswa yang eligible untuk seminar skripsi
        $proposals = $this->_get_eligible_proposals($mahasiswa_id);
        
        // Get existing seminar skripsi
        $seminars = $this->_get_existing_seminars($mahasiswa_id);
        
        // Get progress summary
        $progress_summary = $this->_get_progress_summary($mahasiswa_id);
        
        return [
            'proposals_eligible' => $proposals,
            'existing_seminars' => $seminars,
            'progress_summary' => $progress_summary,
            'can_create_new' => count($proposals) > 0
        ];
    }

    /**
     * Check eligibility untuk seminar skripsi
     */
    private function _check_seminar_skripsi_eligibility($proposal_id, $mahasiswa_id)
    {
        $errors = [];
        $requirements = [];
        
        try {
            // 1. Check workflow status = 'seminar_skripsi'
            $this->db->select('workflow_status');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('id', $proposal_id);
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                $errors[] = 'Proposal tidak ditemukan';
                return ['eligible' => false, 'errors' => $errors, 'requirements' => $requirements];
            }
            
            $requirements['workflow_status'] = [
                'name' => 'Status Workflow',
                'required' => 'seminar_skripsi',
                'current' => $proposal->workflow_status,
                'met' => $proposal->workflow_status === 'seminar_skripsi'
            ];
            
            if ($proposal->workflow_status !== 'seminar_skripsi') {
                $errors[] = 'Belum menyelesaikan tahap penelitian';
            }
            
            // 2. Check minimal jurnal bimbingan (min 14x yang divalidasi)
            $this->db->select('COUNT(*) as count');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $jurnal_count = $this->db->get()->row()->count;
            
            $requirements['jurnal_bimbingan'] = [
                'name' => 'Jurnal Bimbingan',
                'required' => 14,
                'current' => $jurnal_count,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $errors[] = 'Minimal 14 jurnal bimbingan yang divalidasi (saat ini: ' . $jurnal_count . ')';
            }
            
            // 3. Check ada pengajuan surat izin penelitian yang disetujui
            $this->db->select('COUNT(*) as count');
            $this->db->from('penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $this->db->where('persetujuan_pembimbing', '1');
            $penelitian_count = $this->db->get()->row()->count;
            
            $requirements['surat_penelitian'] = [
                'name' => 'Surat Izin Penelitian',
                'required' => 1,
                'current' => $penelitian_count,
                'met' => $penelitian_count >= 1
            ];
            
            if ($penelitian_count < 1) {
                $errors[] = 'Belum ada surat izin penelitian yang disetujui';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
        
        return [
            'eligible' => empty($errors),
            'errors' => $errors,
            'requirements' => $requirements
        ];
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
        
        // Verify proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak valid.');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        try {
            $this->db->trans_start();
            
            $data = [
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'status_pembimbing' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle file upload if provided
            if (!empty($_FILES['file_skripsi']['name'])) {
                $file_result = $this->_handle_file_upload('file_skripsi', 'skripsi_files');
                if ($file_result['status']) {
                    $data['file_skripsi'] = $file_result['filename'];
                    
                    // Delete old file if editing
                    if ($is_edit) {
                        $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                        if ($existing && $existing->file_skripsi) {
                            $old_file_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $existing->file_skripsi;
                            if (file_exists($old_file_path)) {
                                unlink($old_file_path);
                            }
                        }
                    }
                } else {
                    throw new Exception($file_result['message']);
                }
            }
            
            $seminar_id = null;
            
            if ($is_edit) {
                // Update existing record
                $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                if ($existing) {
                    $this->db->where('id', $existing->id);
                    $this->db->update('seminar_skripsi_mahasiswa', $data);
                    $seminar_id = $existing->id;
                    $action_type = 'updated';
                } else {
                    throw new Exception('Data seminar tidak ditemukan untuk diupdate.');
                }
            } else {
                // Insert new record
                $data['proposal_id'] = $proposal_id;
                $data['mahasiswa_id'] = $mahasiswa_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                
                $this->db->insert('seminar_skripsi_mahasiswa', $data);
                $seminar_id = $this->db->insert_id();
                $action_type = 'created';
            }
            
            if (!$seminar_id) {
                throw new Exception('Gagal menyimpan data seminar skripsi.');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal.');
            }
            
            // Send notification to dosen pembimbing
            $this->_send_submission_notification($seminar_id, $proposal, $action_type);
            
            $message = $is_edit ? 'Pengajuan seminar skripsi berhasil diperbarui.' : 'Pengajuan seminar skripsi berhasil dikirim.';
            $this->session->set_flashdata('success', $message);
            redirect('mahasiswa/seminar_skripsi/detail/' . $seminar_id);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Seminar skripsi submission error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
        }
    }

    /**
     * File upload validation
     */
    public function _validate_file_upload($str)
    {
        if (empty($_FILES['file_skripsi']['name'])) {
            $this->form_validation->set_message('_validate_file_upload', 'File skripsi wajib diupload.');
            return FALSE;
        }

        $allowed_types = ['pdf', 'doc', 'docx'];
        $max_size = 2048; // 2MB dalam KB

        $file_ext = pathinfo($_FILES['file_skripsi']['name'], PATHINFO_EXTENSION);
        $file_size = $_FILES['file_skripsi']['size'] / 1024; // Convert ke KB

        if (!in_array(strtolower($file_ext), $allowed_types)) {
            $this->form_validation->set_message('_validate_file_upload', 'File harus berformat PDF, DOC, atau DOCX.');
            return FALSE;
        }

        if ($file_size > $max_size) {
            $this->form_validation->set_message('_validate_file_upload', 'Ukuran file maksimal 2MB.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Handle file upload
     */
    private function _handle_file_upload($field_name, $subfolder)
    {
        $upload_path = FCPATH . 'uploads/seminar_skripsi/' . $subfolder . '/';
        
        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        // Generate unique filename
        $original_name = $_FILES[$field_name]['name'];
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_filename = 'seminar_skripsi_' . $this->session->userdata('id') . '_' . date('YmdHis') . '_' . substr(md5(uniqid(rand(), true)), 0, 6) . '.' . $extension;

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 2048, // 2MB
            'file_name' => $new_filename,
            'overwrite' => TRUE,
            'remove_spaces' => TRUE
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload($field_name)) {
            return [
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->upload->data();
        
        // Basic security check
        if (!$this->_basic_security_check($upload_data['full_path'])) {
            unlink($upload_data['full_path']);
            return [
                'status' => false,
                'message' => 'File gagal validasi keamanan.'
            ];
        }

        return [
            'status' => true,
            'filename' => $upload_data['file_name'],
            'data' => $upload_data
        ];
    }

    /**
     * Basic security check untuk uploaded file
     */
    private function _basic_security_check($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Check file size
        if (filesize($file_path) > 5 * 1024 * 1024) { // Max 5MB
            return false;
        }
        
        // Basic content check
        $content = file_get_contents($file_path, false, null, 0, 1024);
        $dangerous_patterns = ['<?php', '<%', '<script', 'eval(', 'exec('];
        
        foreach ($dangerous_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Send notification to dosen pembimbing
     */
    private function _send_submission_notification($seminar_id, $proposal, $action_type)
    {
        try {
            // Get dosen pembimbing data
            $this->db->select('email, nama');
            $this->db->from('dosen');
            $this->db->where('id', $proposal->dosen_id);
            $pembimbing = $this->db->get()->row();
            
            if ($pembimbing) {
                $this->load->helper('email_workflow');
                
                $mahasiswa_name = $this->session->userdata('nama');
                $nim = $this->session->userdata('nim');
                
                $subject = 'Pengajuan Seminar Skripsi - ' . $mahasiswa_name;
                $message = "Pengajuan seminar skripsi telah " . ($action_type === 'updated' ? 'diperbarui' : 'dikirim') . " oleh mahasiswa:\n\n";
                $message .= "Nama: {$mahasiswa_name}\n";
                $message .= "NIM: {$nim}\n";
                $message .= "Judul: {$proposal->judul}\n\n";
                $message .= "Silakan login ke sistem untuk melakukan review.";
                
                send_email_notification($pembimbing->email, $subject, $message);
            }
        } catch (Exception $e) {
            log_message('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Get eligible proposals - FIXED untuk table proposal_mahasiswa
     */
    private function _get_eligible_proposals($mahasiswa_id)
    {
        try {
            $this->db->select('id, judul, workflow_status');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where('workflow_status', 'seminar_skripsi');
            $this->db->where('status', '1');
            $this->db->order_by('id', 'DESC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting eligible proposals: ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Get existing seminars - FIXED berdasarkan struktur database yang benar
     */
    private function _get_existing_seminars($mahasiswa_id)
    {
        try {
            // Prioritas 1: Gunakan view seminar_skripsi_mahasiswa_v (sudah join lengkap)
            $this->db->select('*');
            $this->db->from('seminar_skripsi_mahasiswa_v');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('created_at', 'DESC');
            $result = $this->db->get()->result();
            
            if (!empty($result)) {
                return $result;
            }
            
            // Fallback 2: Query direct table dengan join manual
            $this->db->select('ss.*, pm.judul, pm.workflow_status, m.nim, m.nama');
            $this->db->from('seminar_skripsi_mahasiswa ss');
            $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
            $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ss.created_at', 'DESC');
            $result = $this->db->get()->result();
            
            if (!empty($result)) {
                return $result;
            }
            
            // Fallback 3: Return eligible proposals sebagai "ready to submit"
            $this->db->select('pm.id as proposal_id, pm.judul, pm.workflow_status, m.nim, m.nama, 
                              "eligible" as status, "Siap untuk diajukan" as status_description,
                              pm.created_at');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.workflow_status', 'seminar_skripsi');
            $this->db->where('pm.status', '1');
            $this->db->order_by('pm.created_at', 'DESC');
            $result = $this->db->get()->result();
            
            return $result;
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting existing seminars: ' . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Get proposal by ID - FIXED untuk table proposal_mahasiswa
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('pm.*, d.nama as nama_pembimbing, d.email as email_pembimbing');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting proposal by ID: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get seminar by proposal ID - FIXED untuk table seminar_skripsi_mahasiswa
     */
    private function _get_seminar_by_proposal_id($proposal_id)
    {
        try {
            $this->db->select('*');
            $this->db->from('seminar_skripsi_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $result = $this->db->get()->row();
            
            return $result;
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting seminar by proposal ID: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get seminar detail - FIXED untuk view seminar_skripsi_mahasiswa_v
     */
    private function _get_seminar_detail($seminar_id, $mahasiswa_id)
    {
        try {
            // Prioritas 1: Gunakan view lengkap
            $this->db->select('*');
            $this->db->from('seminar_skripsi_mahasiswa_v');
            $this->db->where('id', $seminar_id);
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $result = $this->db->get()->row();
            
            if ($result) {
                return $result;
            }
            
            // Fallback: Query manual dengan join
            $this->db->select('ss.*, pm.judul, pm.workflow_status, m.nim, m.nama, 
                              d.nama as nama_pembimbing, d.email as email_pembimbing');
            $this->db->from('seminar_skripsi_mahasiswa ss');
            $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ss.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('ss.id', $seminar_id);
            $this->db->where('ss.mahasiswa_id', $mahasiswa_id);
            $result = $this->db->get()->row();
            
            return $result;
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Get progress data
     */
    private function _get_progress_data($seminar)
    {
        $steps = [
            [
                'key' => 'pengajuan',
                'title' => 'Pengajuan',
                'icon' => 'fa-upload',
                'description' => 'Upload file skripsi final',
                'completed' => !in_array($seminar->status, ['draft']),
                'active' => $seminar->status == 'draft'
            ],
            [
                'key' => 'review_pembimbing',
                'title' => 'Review Pembimbing',
                'icon' => 'fa-user-check',
                'description' => 'Review dan rekomendasi dosen pembimbing',
                'completed' => in_array($seminar->status, ['review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => in_array($seminar->status, ['submitted', 'review_pembimbing'])
            ],
            [
                'key' => 'review_kaprodi',
                'title' => 'Validasi Kaprodi',
                'icon' => 'fa-check-circle',
                'description' => 'Validasi Kaprodi dengan Turnitin check',
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
     * Get progress summary - FIXED berdasarkan struktur database yang benar
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
            
            // Eligible proposals (workflow_status = 'seminar_skripsi')
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where('workflow_status', 'seminar_skripsi');
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $summary['eligible_proposals'] = $result ? $result->total : 0;
            
            // Submitted seminars dari table seminar_skripsi_mahasiswa
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
            
            // Determine current phase
            if ($summary['eligible_proposals'] > 0) {
                $summary['current_phase'] = 'Seminar Skripsi';
            } else if ($summary['total_proposals'] > 0) {
                // Check latest proposal workflow status
                $this->db->select('workflow_status');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('mahasiswa_id', $mahasiswa_id);
                $this->db->where('status', '1');
                $this->db->order_by('id', 'DESC');
                $this->db->limit(1);
                $result = $this->db->get()->row();
                
                if ($result) {
                    $phases = [
                        'proposal' => 'Usulan Proposal',
                        'bimbingan' => 'Bimbingan',
                        'seminar_proposal' => 'Seminar Proposal',
                        'penelitian' => 'Penelitian',
                        'seminar_skripsi' => 'Seminar Skripsi',
                        'publikasi' => 'Publikasi'
                    ];
                    $summary['current_phase'] = $phases[$result->workflow_status] ?? 'Status tidak dikenal';
                }
            }
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Error getting progress summary: ' . $e->getMessage());
            }
        }
        
        return $summary;
    }

    /**
     * Get JavaScript untuk halaman index
     */
    private function _get_index_script()
    {
        return "
        <script>
        $(document).ready(function() {
            // Initialize DataTables jika diperlukan
            if ($('#seminarTable').length) {
                $('#seminarTable').DataTable({
                    'pageLength': 10,
                    'responsive': true,
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    }
                });
            }
            
            // Tooltip initialization
            $('[data-toggle=\"tooltip\"]').tooltip();
        });
        </script>
        ";
    }

    /**
     * Get JavaScript untuk halaman form
     */
    private function _get_form_script()
    {
        return "
        <script>
        $(document).ready(function() {
            // File upload validation
            $('#file_skripsi').on('change', function() {
                var file = this.files[0];
                var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                var maxSize = 2 * 1024 * 1024; // 2MB
                
                if (file) {
                    if (!allowedTypes.includes(file.type)) {
                        alert('File harus berformat PDF, DOC, atau DOCX.');
                        $(this).val('');
                        return;
                    }
                    
                    if (file.size > maxSize) {
                        alert('Ukuran file maksimal 2MB.');
                        $(this).val('');
                        return;
                    }
                    
                    // Show file info
                    $('#file-info').html('<small class=\"text-success\">File: ' + file.name + ' (' + (file.size/1024/1024).toFixed(2) + ' MB)</small>');
                }
            });
            
            // Form validation
            $('#seminarForm').on('submit', function() {
                var fileInput = $('#file_skripsi')[0];
                var isEdit = $(this).data('is-edit');
                
                if (!isEdit && !fileInput.files.length) {
                    alert('File skripsi wajib diupload.');
                    return false;
                }
                
                return true;
            });
        });
        </script>
        ";
    }

    /**
     * Get JavaScript untuk halaman detail
     */
    private function _get_detail_script()
    {
        return "
        <script>
        $(document).ready(function() {
            // Progress bar animation
            $('.progress-bar').each(function() {
                var width = $(this).attr('aria-valuenow');
                $(this).animate({width: width + '%'}, 1000);
            });
            
            // Tooltip initialization
            $('[data-toggle=\"tooltip\"]').tooltip();
        });
        </script>
        ";
    }
}