<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller - COMPLETE FIXED VERSION
 * 
 * FIXED ISSUES:
 * - Dashboard tidak tampilkan progress setelah submit
 * - Logic prioritas diperbaiki: existing data dulu, baru eligibility
 * - Simplified eligibility: hanya 2 syarat (jurnal + surat izin)
 * - Model loading yang benar
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Complete Fixed)
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
        
        // ✅ FIXED: Load correct model name
        try {
            $this->load->model('Seminar_skripsi_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_skripsi_model: ' . $e->getMessage());
            }
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        }
    }

/**
 * ✅ SIMPLE FIXED: Index method yang disederhanakan
 * Langsung gunakan hasil debug yang sudah benar
 */
public function index()
{
    $mahasiswa_id = $this->session->userdata('id');
    
    try {
        // STEP 1: Cek existing seminar
        $existing_seminar = $this->_get_latest_seminar($mahasiswa_id);
        
        if ($existing_seminar) {
            // Ada seminar existing - tampilkan progress
            $data = [
                'has_existing_seminar' => true,
                'current_seminar' => $existing_seminar,
                'show_progress' => true,
                'show_form' => false,
                'show_eligibility_check' => false,
                'status_text' => $this->_get_status_text($existing_seminar),
                'progress_percentage' => $this->_get_progress_percentage($existing_seminar)
            ];
        } else {
            // STEP 2: Tidak ada seminar - cek eligibility (SIMPLIFIED)
            $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
            
            if ($eligibility['eligible']) {
                // ✅ ELIGIBLE - tampilkan form pengajuan
                $data = [
                    'has_existing_seminar' => false,
                    'show_progress' => false,
                    'show_form' => true,
                    'show_eligibility_check' => false,
                    'can_create_new' => true,
                    'eligible_proposal' => $eligibility['proposal'],
                    'action_url' => base_url('mahasiswa/seminar_skripsi/pengajuan/' . $eligibility['proposal']->id),
                    'requirements' => $eligibility['requirements'],
                    'summary' => $eligibility['summary']
                ];
            } else {
                // ❌ NOT ELIGIBLE - tampilkan syarat
                $data = [
                    'has_existing_seminar' => false,
                    'show_progress' => false,
                    'show_form' => false,
                    'show_eligibility_check' => true,
                    'can_create_new' => false,
                    'eligibility' => $eligibility,
                    'errors' => $eligibility['errors'],
                    'requirements' => $eligibility['requirements']
                ];
            }
        }
        
    } catch (Exception $e) {
        log_message('error', 'Seminar_skripsi index error: ' . $e->getMessage());
        
        // FALLBACK: Error handling
        $data = [
            'has_existing_seminar' => false,
            'show_progress' => false,
            'show_form' => false,
            'show_eligibility_check' => true,
            'error' => true,
            'error_message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ];
    }
    
    // Load view dengan data yang sudah jelas
    $this->load->view('template/mahasiswa', [
        'title' => 'Seminar Skripsi',
        'content' => $this->load->view('mahasiswa/seminar_skripsi/index', $data, TRUE)
    ]);
}

    /**
     * Pengajuan Seminar Skripsi - Form submission
     */
    public function pengajuan($proposal_id = null)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak valid');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->_handle_pengajuan_submit($proposal_id, $mahasiswa_id);
        } else {
            $this->_show_pengajuan_form($proposal_id, $mahasiswa_id);
        }
    }

    /**
     * Detail seminar skripsi
     */
    public function detail($seminar_id = null)
    {
        if (!$seminar_id) {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $mahasiswa_id = $this->session->userdata('id');
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);

        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $data = [
            'seminar' => $seminar,
            'progress_data' => $this->_build_simple_progress($seminar)
        ];

        $this->load->view('template/mahasiswa', [
            'title' => 'Detail Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/detail', $data, TRUE)
        ]);
    }

    // =================================================================
    // ✅ FIXED PRIVATE METHODS - DASHBOARD LOGIC
    // =================================================================

    /**
     * ✅ COMPLETELY FIXED: Prepare dashboard data dengan prioritas yang benar
     */
    private function _prepare_dashboard_data($mahasiswa_id)
    {
        try {
            // ✅ PRIORITAS 1: Cek existing seminar FIRST
            $existing_seminar = $this->_get_latest_seminar($mahasiswa_id);
            
            if ($existing_seminar) {
                // ✅ ADA SEMINAR: Langsung tampilkan progress (tidak cek eligibility lagi)
                if (ENVIRONMENT === 'development') {
                    log_message('debug', "Found existing seminar ID {$existing_seminar->id} for mahasiswa {$mahasiswa_id}");
                }
                
                return [
                    'has_existing_seminar' => true,
                    'current_seminar' => $existing_seminar,
                    'show_progress' => true,
                    'show_form' => false,
                    'status_text' => $this->_get_status_text($existing_seminar),
                    'progress_percentage' => $this->_get_progress_percentage($existing_seminar),
                    'progress_steps' => $this->_build_simple_progress($existing_seminar)
                ];
            }
            
            // ✅ PRIORITAS 2: Tidak ada seminar, cek eligibility SIMPLIFIED
            $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', "No existing seminar. Eligibility check result: " . ($eligibility['eligible'] ? 'ELIGIBLE' : 'NOT ELIGIBLE'));
            }
            
            return [
                'has_existing_seminar' => false,
                'eligibility' => $eligibility,
                'can_create_new' => $eligibility['eligible'],
                'show_form' => $eligibility['eligible'],
                'show_progress' => false,
                'eligible_proposal' => $eligibility['proposal'] ?? null,
                'action_url' => $eligibility['eligible'] && isset($eligibility['proposal']) ? 
                    base_url('mahasiswa/seminar_skripsi/pengajuan/' . $eligibility['proposal']->id) : null
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard data error: ' . $e->getMessage());
            return [
                'has_existing_seminar' => false, 
                'can_create_new' => false, 
                'show_form' => false,
                'error' => true,
                'error_message' => 'Terjadi kesalahan sistem'
            ];
        }
    }

    /**
     * ✅ NEW: Get latest seminar by mahasiswa (simple & reliable)
     */
    private function _get_latest_seminar($mahasiswa_id)
    {
        try {
            $this->db->select('ssm.*, pm.judul, pm.workflow_status');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ssm.created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Get latest seminar error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NEW: Simplified eligibility check - hanya 2 syarat
     */
    private function _check_simplified_eligibility($mahasiswa_id)
    {
        try {
            // Get proposal mahasiswa (fase penelitian atau seminar_skripsi)
            $proposal = $this->db->select('id, judul, workflow_status')
                                ->from('proposal_mahasiswa')
                                ->where('mahasiswa_id', $mahasiswa_id)
                                ->where_in('workflow_status', ['penelitian', 'seminar_skripsi'])
                                ->get()->row();
            
            if (!$proposal) {
                return [
                    'eligible' => false,
                    'errors' => ['Tidak ada proposal dalam fase penelitian'],
                    'requirements' => [],
                    'summary' => 'Belum ada proposal yang memenuhi syarat'
                ];
            }
            
            $requirements = [];
            $errors = [];
            
            // ✅ SYARAT 1: 14 jurnal bimbingan tervalidasi
            $jurnal_count = $this->db->where('proposal_id', $proposal->id)
                                    ->where('status_validasi', '1')
                                    ->count_all_results('jurnal_bimbingan');
            
            $requirements['jurnal'] = [
                'name' => 'Jurnal Bimbingan Tervalidasi',
                'current' => $jurnal_count,
                'required' => 14,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $errors[] = "Perlu " . (14 - $jurnal_count) . " jurnal bimbingan lagi";
            }
            
            // ✅ SYARAT 2: Sudah mengajukan surat izin penelitian
            $penelitian_count = 0;
            
            // Cek di tabel permohonan_izin_penelitian
            if ($this->db->table_exists('permohonan_izin_penelitian')) {
                $penelitian_count = $this->db->where('proposal_mahasiswa_id', $proposal->id)
                                            ->count_all_results('permohonan_izin_penelitian');
            }
            
            // Fallback: cek di tabel penelitian
            if ($penelitian_count == 0 && $this->db->table_exists('penelitian')) {
                $penelitian_count = $this->db->where('proposal_mahasiswa_id', $proposal->id)
                                            ->count_all_results('penelitian');
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
                'proposal' => $proposal,
                'summary' => empty($errors) ? 
                    'Memenuhi syarat untuk mengajukan seminar skripsi' : 
                    'Belum memenuhi syarat: ' . implode(', ', $errors)
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Eligibility check error: ' . $e->getMessage());
            return [
                'eligible' => false,
                'errors' => ['Terjadi kesalahan sistem'],
                'requirements' => [],
                'summary' => 'Error sistem'
            ];
        }
    }

    /**
     * ✅ NEW: Get status text berdasarkan data seminar
     */
    private function _get_status_text($seminar)
    {
        switch ($seminar->status) {
            case 'submitted':
                if ($seminar->status_pembimbing == 'pending') {
                    return 'Menunggu Review Dosen Pembimbing';
                } elseif ($seminar->status_pembimbing == 'approved' && $seminar->status_kaprodi == 'pending') {
                    return 'Menunggu Validasi Kaprodi';
                } else {
                    return 'Sedang Diproses';
                }
            case 'review_pembimbing':
                return 'Sedang Direview Dosen Pembimbing';
            case 'review_kaprodi':
                return 'Menunggu Validasi Kaprodi';
            case 'approved':
                return 'Disetujui - Menunggu Penjadwalan';
            case 'scheduled':
                return 'Seminar Telah Dijadwalkan';
            case 'completed':
                return 'Seminar Skripsi Selesai';
            case 'rejected':
                return 'Pengajuan Ditolak - Perlu Perbaikan';
            default:
                return 'Status: ' . ucfirst($seminar->status);
        }
    }

    /**
     * ✅ NEW: Get progress percentage
     */
    private function _get_progress_percentage($seminar)
    {
        switch ($seminar->status) {
            case 'submitted':
            case 'review_pembimbing':
                return 20;
            case 'review_kaprodi':
                return 40;
            case 'approved':
                return 60;
            case 'scheduled':
                return 80;
            case 'completed':
                return 100;
            case 'rejected':
                return 10;
            default:
                return 5;
        }
    }

    /**
     * ✅ NEW: Build simple progress steps
     */
    private function _build_simple_progress($seminar)
    {
        $steps = [
            ['title' => 'Pengajuan Dikirim', 'status' => 'completed'],
            ['title' => 'Review Pembimbing', 'status' => $seminar->status_pembimbing == 'approved' ? 'completed' : 'active'],
            ['title' => 'Validasi Kaprodi', 'status' => $seminar->status_kaprodi == 'approved' ? 'completed' : 'pending'],
            ['title' => 'Penjadwalan', 'status' => $seminar->status == 'scheduled' ? 'completed' : 'pending'],
            ['title' => 'Pelaksanaan', 'status' => $seminar->status == 'completed' ? 'completed' : 'pending']
        ];
        
        return $steps;
    }

    // =================================================================
    // FORM HANDLING METHODS
    // =================================================================

    /**
     * ✅ MINIMAL FIX: Show pengajuan form - hanya tambah variable yang missing
     */
    private function _show_pengajuan_form($proposal_id, $mahasiswa_id)
    {
        // Validasi proposal ownership (TIDAK BERUBAH)
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // Check eligibility (TIDAK BERUBAH)
        $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat: ' . implode(', ', $eligibility['errors']));
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // ✅ TAMBAHAN MINIMAL: Cek existing seminar untuk edit mode
        $existing_seminar = null;
        $this->db->where('proposal_id', $proposal_id);
        $existing_seminar = $this->db->get('seminar_skripsi_mahasiswa')->row();
        
        $is_edit = !empty($existing_seminar);
        
        // ✅ TAMBAHAN MINIMAL: Tentukan judul current (prioritas dari existing seminar)
        $current_judul = $proposal->judul; // Default: judul proposal
        if ($existing_seminar && !empty($existing_seminar->judul_skripsi)) {
            $current_judul = $existing_seminar->judul_skripsi; // Gunakan judul skripsi jika ada
        }
    
        // ✅ FIX: Tambah variable yang missing untuk mengatasi error
        $data = [
            'proposal' => $proposal,
            'eligibility' => $eligibility,
            'form_action' => base_url('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id),
            // Variable yang ditambahkan untuk fix error
            'is_edit' => $is_edit,
            'form_title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi',
            'existing_seminar' => $existing_seminar,
            'current_judul' => $current_judul,
            'judul_original' => $proposal->judul,
            'requirements' => ['requirements' => [], 'all_met' => true]
        ];
    
        // Load view (TIDAK BERUBAH)
        $this->load->view('template/mahasiswa', [
            'title' => 'Ajukan Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/pengajuan', $data, TRUE),
            'script' => $this->_get_form_script()
        ]);
    }

    /**
     * Handle pengajuan submission
     */
    private function _handle_pengajuan_submit($proposal_id, $mahasiswa_id)
    {
        // Set validation rules
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan Tambahan', 'required|max_length[500]');

        if (!$this->form_validation->run()) {
            $this->_show_pengajuan_form($proposal_id, $mahasiswa_id);
            return;
        }

        try {
            // Handle file upload
            $upload_result = $this->_handle_file_upload();
            
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
                return;
            }

            // Prepare data for insert
            $data = [
                'proposal_id' => $proposal_id,
                'mahasiswa_id' => $mahasiswa_id,
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'file_skripsi' => $upload_result['filename'],
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert to database
            $this->db->trans_start();
            
            $this->db->insert('seminar_skripsi_mahasiswa', $data);
            $insert_id = $this->db->insert_id();
            
            if (!$insert_id) {
                throw new Exception('Gagal menyimpan pengajuan');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction gagal');
            }
            
            // Send notification
            $this->_send_notification_success($proposal_id, $insert_id);
            
            $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim! Dosen pembimbing akan mendapat notifikasi.');
            
        } catch (Exception $e) {
            log_message('error', 'Error saving seminar skripsi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * ✅ MINIMAL FIX: Hanya perbaiki path upload untuk konsistensi
     */
    private function _handle_file_upload()
    {
        // ✅ FIXED: Gunakan FCPATH seperti controller lain
        $upload_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/';
        
        // Buat folder jika belum ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
    
        $config = [
            'upload_path' => $upload_path,  // ✅ KONSISTEN dengan controller lain
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 5120, // 5MB (disesuaikan dengan form)
            'encrypt_name' => TRUE
        ];
    
        $this->upload->initialize($config);
    
        if (!$this->upload->do_upload('file_skripsi')) {
            return [
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }
    
        $upload_data = $this->upload->data();
        return [
            'success' => true,
            'filename' => $upload_data['file_name'],
            'original_name' => $upload_data['orig_name']
        ];
    }

    /**
     * ✅ TAMBAHAN: Method untuk upload surat penelitian (jika diperlukan)
     */
    private function _handle_surat_penelitian_upload()
    {
        $upload_path = FCPATH . 'uploads/seminar_skripsi/surat_penelitian/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
    
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 3072, // 3MB
            'encrypt_name' => TRUE
        ];
    
        $this->upload->initialize($config);
    
        if (!$this->upload->do_upload('surat_penelitian')) {
            return [
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }
    
        $upload_data = $this->upload->data();
        return [
            'success' => true,
            'filename' => $upload_data['file_name'],
            'original_name' => $upload_data['orig_name']
        ];
    }

    /**
     * Send notification after successful submission
     */
    private function _send_notification_success($proposal_id, $seminar_id)
    {
        try {
            // Get proposal data with joins
            $proposal_data = $this->db->select('
                pm.*, 
                m.nama as nama_mahasiswa, 
                m.nim, 
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing, 
                d.email as email_pembimbing
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')
            ->where('pm.id', $proposal_id)
            ->get()->row();
            
            if (!$proposal_data || !$proposal_data->email_pembimbing) {
                log_message('warning', 'No proposal data or pembimbing email for notification');
                return false;
            }
            
            // Email config
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
                'smtp_timeout' => 30
            ];
            
            $this->email->initialize($config);
            
            // Send to dosen pembimbing
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($proposal_data->email_pembimbing);
            $this->email->subject('📋 Pengajuan Seminar Skripsi - ' . $proposal_data->nama_mahasiswa);
            
            $message = "
            <h3>Pengajuan Seminar Skripsi</h3>
            <p>Mahasiswa berikut telah mengajukan seminar skripsi:</p>
            <ul>
                <li><strong>Nama:</strong> {$proposal_data->nama_mahasiswa}</li>
                <li><strong>NIM:</strong> {$proposal_data->nim}</li>
                <li><strong>Judul:</strong> {$proposal_data->judul}</li>
            </ul>
            <p>Silakan login ke sistem untuk melakukan review pengajuan.</p>
            <p><a href='".base_url('dosen/seminar_skripsi')."' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Review Pengajuan</a></p>
            ";
            
            $this->email->message($message);
            $email_sent = $this->email->send();
            
            if ($email_sent) {
                log_message('info', "Seminar skripsi notification sent successfully - ID: {$seminar_id}");
                return true;
            } else {
                log_message('error', "Failed to send seminar skripsi notification - ID: {$seminar_id}");
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get proposal by ID with ownership validation
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        return $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa')
                       ->from('proposal_mahasiswa pm')
                       ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                       ->where('pm.id', $proposal_id)
                       ->where('pm.mahasiswa_id', $mahasiswa_id)
                       ->get()->row();
    }

    /**
     * Get seminar detail with ownership validation
     */
    private function _get_seminar_detail($seminar_id, $mahasiswa_id)
    {
        return $this->db->select('ssm.*, pm.judul, m.nim, m.nama as nama_mahasiswa')
                       ->from('seminar_skripsi_mahasiswa ssm')
                       ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
                       ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                       ->where('ssm.id', $seminar_id)
                       ->where('ssm.mahasiswa_id', $mahasiswa_id)
                       ->get()->row();
    }

    /**
     * Get index page script
     */
    private function _get_index_script()
    {
        return '
        <script>
        $(document).ready(function() {
            // Auto refresh progress setiap 30 detik jika ada seminar aktif
            if ($(".progress-bar").length > 0) {
                setInterval(function() {
                    location.reload();
                }, 30000);
            }
        });
        </script>
        ';
    }

    /**
     * Get form page script
     */
    private function _get_form_script()
    {
        return '
        <script>
        $(document).ready(function() {
            $("#file_skripsi").change(function() {
                var fileSize = this.files[0].size;
                if (fileSize > 2097152) { // 2MB
                    alert("File terlalu besar. Maksimal 2MB.");
                    $(this).val("");
                }
            });
        });
        </script>
        ';
    }
    
/**
 * 🔍 DEBUG: Test method _check_simplified_eligibility
 * URL: mahasiswa/seminar_skripsi/debug_simplified
 */
public function debug_simplified()
{
    if (ENVIRONMENT !== 'development') {
        show_404();
        return;
    }
    
    $mahasiswa_id = $this->session->userdata('id');
    
    echo "<h2>🔍 Debug Method _check_simplified_eligibility untuk Mahasiswa ID: {$mahasiswa_id}</h2>";
    
    // Test method simplified
    echo "<div style='border: 2px solid #007bff; padding: 15px; margin: 10px;'>";
    echo "<h3>🎯 Test _check_simplified_eligibility</h3>";
    
    try {
        $result = $this->_check_simplified_eligibility($mahasiswa_id);
        
        echo "<p><strong>Result:</strong> " . ($result['eligible'] ? '✅ ELIGIBLE' : '❌ NOT ELIGIBLE') . "</p>";
        
        if (!empty($result['errors'])) {
            echo "<p><strong>Errors:</strong></p>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li>❌ {$error}</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($result['requirements'])) {
            echo "<h4>Requirements Detail:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Requirement</th><th>Current</th><th>Required</th><th>Status</th></tr>";
            foreach ($result['requirements'] as $name => $req) {
                $status_icon = $req['met'] ? '✅' : '❌';
                echo "<tr>";
                echo "<td>{$req['name']}</td>";
                echo "<td>{$req['current']}</td>";
                echo "<td>{$req['required']}</td>";
                echo "<td>{$status_icon}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        if (isset($result['proposal'])) {
            echo "<p><strong>Proposal Found:</strong> ID {$result['proposal']->id}, Workflow: {$result['proposal']->workflow_status}</p>";
        }
        
        echo "<p><strong>Summary:</strong> {$result['summary']}</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    // Test dashboard data
    echo "<div style='border: 2px solid #28a745; padding: 15px; margin: 10px;'>";
    echo "<h3>🎯 Test _prepare_dashboard_data</h3>";
    
    try {
        $dashboard_data = $this->_prepare_dashboard_data($mahasiswa_id);
        
        echo "<p><strong>Has Existing Seminar:</strong> " . ($dashboard_data['has_existing_seminar'] ? '✅ YES' : '❌ NO') . "</p>";
        echo "<p><strong>Can Create New:</strong> " . ($dashboard_data['can_create_new'] ?? false ? '✅ YES' : '❌ NO') . "</p>";
        echo "<p><strong>Show Form:</strong> " . ($dashboard_data['show_form'] ?? false ? '✅ YES' : '❌ NO') . "</p>";
        
        if (isset($dashboard_data['eligibility'])) {
            echo "<p><strong>Eligibility Eligible:</strong> " . ($dashboard_data['eligibility']['eligible'] ? '✅ YES' : '❌ NO') . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
}
    
}

/* End of file Seminar_skripsi.php */
/* Location: ./application/controllers/mahasiswa/Seminar_skripsi.php */