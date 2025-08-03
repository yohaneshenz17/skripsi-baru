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
     * ✅ FIXED: Index - Dashboard Seminar Skripsi untuk Mahasiswa
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'Seminar_skripsi index called for mahasiswa_id: ' . $mahasiswa_id);
        }

        try {
            // ✅ FIXED: Get data dengan prioritas yang benar
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
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data');
            $this->load->view('template/mahasiswa', [
                'title' => 'Seminar Skripsi',
                'content' => '<div class="alert alert-danger">Error memuat data</div>'
            ]);
        }
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
     * Show pengajuan form
     */
    private function _show_pengajuan_form($proposal_id, $mahasiswa_id)
    {
        // Validasi proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Check eligibility
        $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat: ' . implode(', ', $eligibility['errors']));
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $data = [
            'proposal' => $proposal,
            'eligibility' => $eligibility,
            'form_action' => base_url('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id)
        ];

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
     * Handle file upload
     */
    private function _handle_file_upload()
    {
        $config = [
            'upload_path' => './uploads/seminar_skripsi/skripsi_files/',
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 2048, // 2MB
            'encrypt_name' => TRUE
        ];

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

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
 * 🔍 DEBUG: Method yang sudah diperbaiki
 * URL: mahasiswa/seminar_skripsi/debug_eligibility
 */
public function debug_eligibility()
{
    if (ENVIRONMENT !== 'development') {
        show_404();
        return;
    }
    
    $mahasiswa_id = $this->session->userdata('id');
    
    echo "<h2>🔍 Debug Eligibility Seminar Skripsi untuk Mahasiswa ID: {$mahasiswa_id}</h2>";
    
    // ===== TEST 1: Cek proposal =====
    echo "<h3>📋 Test 1: Cek Proposal</h3>";
    $this->db->select('id, judul, workflow_status, status_kaprodi, status_pembimbing, dosen_id');
    $this->db->from('proposal_mahasiswa');
    $this->db->where('mahasiswa_id', $mahasiswa_id);
    $proposals = $this->db->get()->result();
    
    echo "<p><strong>Proposals found:</strong> " . count($proposals) . "</p>";
    
    if ($proposals) {
        foreach ($proposals as $p) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
            echo "<h4>Proposal ID: {$p->id}</h4>";
            echo "<p>Workflow Status: <strong>{$p->workflow_status}</strong></p>";
            echo "<p>Status Kaprodi: {$p->status_kaprodi}, Status Pembimbing: {$p->status_pembimbing}</p>";
            
            // ===== TEST 2: Cek Jurnal Bimbingan =====
            echo "<h5>📝 Test 2: Jurnal Bimbingan</h5>";
            $this->db->select('COUNT(*) as count');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $p->id);
            $this->db->where('status_validasi', '1');
            $jurnal_count = $this->db->get()->row()->count;
            
            echo "<p>Jurnal Tervalidasi: <strong>{$jurnal_count}/14</strong> " . ($jurnal_count >= 14 ? '✅' : '❌') . "</p>";
            
            // ===== TEST 3: Cek Seminar Proposal =====
            echo "<h5>🎯 Test 3: Seminar Proposal</h5>";
            $this->db->select('status');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $p->id);
            $this->db->where('status', 'completed');
            $seminar_proposal = $this->db->get()->row();
            
            echo "<p>Seminar Proposal Completed: " . ($seminar_proposal ? '✅ YES' : '❌ NO') . "</p>";
            
            // ===== TEST 4: PERBAIKAN - Cek Surat Penelitian =====
            echo "<h5>🔬 Test 4: Surat Penelitian (FIXED)</h5>";
            
            try {
                // PERBAIKAN: Query yang aman tanpa field bermasalah
                $this->db->select('id, status_pembimbing');
                $this->db->from('permohonan_izin_penelitian');
                $this->db->where('proposal_mahasiswa_id', $p->id);
                $permohonan = $this->db->get()->result();
                
                echo "<p>Permohonan Count: <strong>" . count($permohonan) . "</strong></p>";
                
                if ($permohonan) {
                    $approved_count = 0;
                    foreach ($permohonan as $pm) {
                        echo "<p>- ID: {$pm->id}, Status Pembimbing: {$pm->status_pembimbing}</p>";
                        if ($pm->status_pembimbing === 'approved') {
                            $approved_count++;
                        }
                    }
                    echo "<p>Approved Count: <strong>{$approved_count}</strong> " . ($approved_count >= 1 ? '✅' : '❌') . "</p>";
                } else {
                    echo "<p>❌ Tidak ada data permohonan penelitian</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
                
                // Fallback ke tabel lama
                echo "<p>🔄 Trying fallback table...</p>";
                try {
                    $this->db->select('COUNT(*) as count');
                    $this->db->from('penelitian');
                    $this->db->where('proposal_mahasiswa_id', $p->id);
                    $this->db->where('persetujuan_pembimbing', '1');
                    $penelitian_count = $this->db->get()->row()->count;
                    
                    echo "<p>Penelitian Count (tabel lama): <strong>{$penelitian_count}</strong> " . ($penelitian_count >= 1 ? '✅' : '❌') . "</p>";
                } catch (Exception $e2) {
                    echo "<p>❌ Both tables failed: " . $e2->getMessage() . "</p>";
                }
            }
            
            echo "</div>";
        }
    }
}
    
}

/* End of file Seminar_skripsi.php */
/* Location: ./application/controllers/mahasiswa/Seminar_skripsi.php */