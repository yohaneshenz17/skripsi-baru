<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller - Role Mahasiswa (COMPLETE FIXED VERSION)
 * Updated untuk menggunakan template mahasiswa.php yang konsisten
 * 
 * Controller untuk mengelola seminar proposal dari sisi mahasiswa
 * Sesuai dengan workflow yang telah ditetapkan
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     3.0 (Complete Final)
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
                log_message('debug', 'Seminar_proposal index - Mahasiswa ID: ' . $mahasiswa_id);
            }
            
            // PERBAIKAN: Cek proposal yang sudah disetujui dan siap untuk seminar proposal
            $proposal = $this->_get_approved_proposal($mahasiswa_id);
            
            if (!$proposal) {
                // CASE 1: Belum ada proposal yang disetujui
                // Tampilkan no_proposal.php yang mengarahkan ke pengajuan proposal
                $data = [
                    'title' => 'Seminar Proposal',
                    'content' => 'mahasiswa/seminar_proposal/no_proposal',
                    'proposal' => null,
                    'seminar_data' => null,
                    'current_step' => 'no_approved_proposal'
                ];
                
                $this->_load_view($data);
                return;
            }

            // CASE 2: Ada proposal yang disetujui
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
     * Process Form Pengajuan Seminar Proposal (DIPERBAIKI - NAMA TABEL YANG BENAR)
     */
    public function submit_ajukan()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $proposal_id = $this->input->post('proposal_id');
        $is_edit = $this->input->post('is_edit') === '1';
        
        // Set validation rules
        $this->form_validation->set_rules('proposal_id', 'ID Proposal', 'required|integer');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'required|min_length[10]|max_length[1000]');
        
        if (!$is_edit || !empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_rules('file_proposal', 'File Proposal', 'callback__check_file_proposal');
        }
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_proposal/ajukan/' . $proposal_id);
            return;
        }
        
        // Verify proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak valid.');
            redirect('mahasiswa/seminar_proposal');
            return;
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
                    // 🚀 PERBAIKAN: Gunakan nama tabel yang benar
                    $this->db->update('seminar_proposal_mahasiswa', $data);
                    
                    $action_type = 'updated';
                    $this->session->set_flashdata('success', 'Pengajuan seminar proposal berhasil diperbarui.');
                } else {
                    throw new Exception('Data seminar tidak ditemukan untuk diupdate.');
                }
            } else {
                // Create new record
                $data['proposal_id'] = $proposal_id;
                $data['mahasiswa_id'] = $mahasiswa_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                
                // 🚀 PERBAIKAN: Gunakan nama tabel yang benar
                $this->db->insert('seminar_proposal_mahasiswa', $data);
                
                $action_type = 'created';
                $this->session->set_flashdata('success', 'Pengajuan seminar proposal berhasil dikirim. Menunggu review dari dosen pembimbing.');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // KIRIM NOTIFIKASI EMAIL setelah berhasil submit
            if ($action_type === 'created') {
                $this->_kirim_notifikasi_seminar_proposal($proposal, $data);
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Seminar proposal submission error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_proposal');
    }
    
    // ========================================
    // METHOD UNTUK NOTIFIKASI EMAIL
    // ========================================
    
    /**
     * 🚀 METHOD BARU: Kirim notifikasi email setelah pengajuan seminar proposal
     */
    private function _kirim_notifikasi_seminar_proposal($proposal, $seminar_data)
    {
        try {
            // Load email library
            $this->load->library('email');
            
            // Setup email configuration
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug', // Gunakan app password
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls',
                'smtp_timeout' => 30
            ];
            
            $this->email->initialize($config);
            
            // 1. KIRIM EMAIL KE MAHASISWA (KONFIRMASI)
            $this->_kirim_email_mahasiswa_seminar_proposal($proposal, $seminar_data);
            
            // 2. KIRIM EMAIL KE DOSEN PEMBIMBING (NOTIFIKASI REVIEW)
            $this->_kirim_email_dosen_seminar_proposal($proposal, $seminar_data);
            
            // 3. SIMPAN KE TABEL NOTIFIKASI (INTERNAL SYSTEM)
            $this->_simpan_notifikasi_internal($proposal, $seminar_data);
            
            log_message('info', 'Notifikasi seminar proposal berhasil dikirim untuk proposal ID: ' . $proposal->id);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending seminar proposal notification: ' . $e->getMessage());
            // Jangan throw exception, biarkan proses utama tetap berjalan
        }
    }
    
    /**
     * 📧 EMAIL KE MAHASISWA - Konfirmasi pengajuan
     */
    private function _kirim_email_mahasiswa_seminar_proposal($proposal, $seminar_data)
    {
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($proposal->email_mahasiswa);
        $this->email->subject('[SIM-TA] Pengajuan Seminar Proposal Berhasil - Menunggu Review Pembimbing');
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>📋 Pengajuan Seminar Proposal</h1>
            </div>
            
            <div style='padding: 30px; background-color: #f8f9fa;'>
                <h2 style='color: #28a745; margin: 0 0 20px 0;'>✅ Pengajuan Berhasil Dikirim</h2>
                
                <p><strong>Halo {$proposal->nama_mahasiswa},</strong></p>
                
                <p>Pengajuan seminar proposal Anda telah <strong>berhasil dikirim</strong> dan sedang menunggu review dari dosen pembimbing.</p>
                
                <div style='background-color: white; padding: 20px; border-left: 4px solid #007bff; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #007bff;'>📊 Detail Pengajuan:</h4>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>NIM:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->nim}</td></tr>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Judul:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->judul}</td></tr>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Pembimbing:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->nama_pembimbing}</td></tr>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Status:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><span style='background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px;'>⏳ Menunggu Review Pembimbing</span></td></tr>
                        <tr><td style='padding: 8px 0;'><strong>Tanggal Pengajuan:</strong></td><td style='padding: 8px 0;'>" . date('d F Y, H:i') . " WIT</td></tr>
                    </table>
                </div>
                
                <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                    <ul style='color: #0c5460; margin: 0; padding-left: 20px;'>
                        <li>Dosen pembimbing akan mereview pengajuan Anda</li>
                        <li>Anda akan mendapat notifikasi hasil review via email</li>
                        <li>Pantau status melalui dashboard SIM-TA</li>
                        <li>Jika disetujui, akan dilanjutkan ke proses penjadwalan seminar</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/seminar_proposal') . "' 
                       style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📊 Cek Status Pengajuan
                    </a>
                </div>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 20px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>STK Santo Yakobus Merauke | SIM Tugas Akhir</p>
                <p style='margin: 5px 0 0 0;'>Email otomatis - mohon tidak membalas</p>
            </div>
        </div>";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * 📧 EMAIL KE DOSEN PEMBIMBING - Notifikasi review
     */
    private function _kirim_email_dosen_seminar_proposal($proposal, $seminar_data)
    {
        // Get email dosen dari database
        $this->db->select('email, nama');
        $this->db->from('dosen');
        $this->db->where('id', $proposal->dosen_id);
        $dosen = $this->db->get()->row();
        
        if (!$dosen || empty($dosen->email)) {
            log_message('warning', 'Email dosen tidak ditemukan untuk proposal ID: ' . $proposal->id);
            return;
        }
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject('[SIM-TA] Review Pengajuan Seminar Proposal - ' . $proposal->nama_mahasiswa);
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 24px;'>🔍 Review Seminar Proposal</h1>
            </div>
            
            <div style='padding: 30px; background-color: #f8f9fa;'>
                <h2 style='color: #dc3545; margin: 0 0 20px 0;'>⚡ Action Required</h2>
                
                <p><strong>Yth. {$dosen->nama},</strong></p>
                
                <p>Mahasiswa bimbingan Anda telah mengajukan <strong>Seminar Proposal</strong> dan membutuhkan review dari Anda.</p>
                
                <div style='background-color: white; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;'>
                    <h4 style='margin-top: 0; color: #dc3545;'>👨‍🎓 Detail Mahasiswa:</h4>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Nama:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->nama_mahasiswa}</td></tr>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>NIM:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->nim}</td></tr>
                        <tr><td style='padding: 8px 0; border-bottom: 1px solid #eee;'><strong>Judul:</strong></td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$proposal->judul}</td></tr>
                        <tr><td style='padding: 8px 0;'><strong>Tanggal Pengajuan:</strong></td><td style='padding: 8px 0;'>" . date('d F Y, H:i') . " WIT</td></tr>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0;'>📋 Yang Perlu Direview:</h4>
                    <ul style='color: #856404; margin: 0; padding-left: 20px;'>
                        <li>Kelengkapan jurnal bimbingan (minimal 8 tervalidasi)</li>
                        <li>Kesiapan mahasiswa untuk seminar proposal</li>
                        <li>File proposal yang diupload mahasiswa</li>
                        <li>Keterangan tambahan dari mahasiswa</li>
                    </ul>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('dosen/seminar_proposal') . "' 
                       style='background-color: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>
                       🔍 Review Sekarang
                    </a>
                    <a href='" . base_url('dosen/bimbingan') . "' 
                       style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📋 Lihat Jurnal Bimbingan
                    </a>
                </div>
                
                <p style='font-size: 14px; color: #6c757d; text-align: center;'>
                    <em>Harap segera direview untuk kelancaran proses akademik mahasiswa.</em>
                </p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 20px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>STK Santo Yakobus Merauke | SIM Tugas Akhir</p>
                <p style='margin: 5px 0 0 0;'>Email otomatis - mohon tidak membalas</p>
            </div>
        </div>";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * 💾 SIMPAN NOTIFIKASI INTERNAL KE DATABASE
     */
    private function _simpan_notifikasi_internal($proposal, $seminar_data)
    {
        try {
            if (!$this->db->table_exists('notifikasi')) {
                return; // Skip jika tabel belum ada
            }
            
            $notifications = [
                // Notifikasi untuk mahasiswa
                [
                    'user_id' => $proposal->mahasiswa_id,
                    'untuk_role' => 'mahasiswa',
                    'judul' => 'Pengajuan Seminar Proposal Berhasil',
                    'pesan' => 'Pengajuan seminar proposal Anda telah berhasil dikirim dan sedang menunggu review dari dosen pembimbing.',
                    'jenis' => 'proposal_masuk', // Sesuaikan dengan enum yang ada
                    'dibaca' => 0,
                    'tanggal_dibuat' => date('Y-m-d H:i:s')
                ],
                // Notifikasi untuk dosen pembimbing
                [
                    'user_id' => $proposal->dosen_id,
                    'untuk_role' => 'dosen',
                    'judul' => 'Review Pengajuan Seminar Proposal',
                    'pesan' => "Mahasiswa {$proposal->nama_mahasiswa} ({$proposal->nim}) telah mengajukan seminar proposal dan membutuhkan review Anda.",
                    'jenis' => 'proposal_masuk', // Sesuaikan dengan enum yang ada
                    'dibaca' => 0,
                    'tanggal_dibuat' => date('Y-m-d H:i:s')
                ]
            ];
            
            $this->db->insert_batch('notifikasi', $notifications);
            
        } catch (Exception $e) {
            log_message('error', 'Error saving internal notification: ' . $e->getMessage());
        }
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
            // Extract view path dan data lainnya
            $view_path = isset($data['content']) ? $data['content'] : '';
            $title = isset($data['title']) ? $data['title'] : 'Seminar Proposal';
            $styles = isset($data['styles']) ? $data['styles'] : '';
            $script = isset($data['script']) ? $data['script'] : '';
            
            // Remove keys yang tidak perlu dikirim ke view
            unset($data['content'], $data['styles'], $data['script']);
            
            // Render view content menjadi HTML string
            if (!empty($view_path)) {
                // Start output buffering
                ob_start();
                
                // Load view dengan data (extract semua variables)
                extract($data);
                include(APPPATH . 'views/' . $view_path . '.php');
                
                // Get rendered content
                $content = ob_get_clean();
            } else {
                $content = '<div class="alert alert-warning">No content specified.</div>';
            }
            
            // Prepare final data untuk template
            $template_data = array(
                'title' => $title,
                'content' => $content,
                'styles' => $styles,
                'script' => $script
            );
            
            // Load template mahasiswa
            if (file_exists(VIEWPATH . 'template/mahasiswa.php')) {
                $this->load->view('template/mahasiswa', $template_data);
            } else if (file_exists(VIEWPATH . 'template/mahasiswa_simple.php')) {
                $this->load->view('template/mahasiswa_simple', $template_data);
            } else {
                throw new Exception('Template mahasiswa tidak ditemukan');
            }
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                show_error('View Loading Error: ' . $e->getMessage() . '<br><br>' . 
                          'View Path: ' . $view_path . '<br>' .
                          'Available Data: ' . print_r(array_keys($data), true));
            } else {
                // Production error handling
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
                redirect('mahasiswa/dashboard');
            }
        }
    }

    /**
     * PERBAIKAN: Method untuk mendapatkan proposal yang sudah disetujui
     * Sesuaikan dengan kolom yang ada di tabel proposal_mahasiswa
     */
    private function _get_approved_proposal($mahasiswa_id)
    {
        try {
            $this->db->select('
                pm.*, 
                m.nama as nama_mahasiswa,
                m.nim,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                p.nama as nama_prodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status_kaprodi', '1');        // Disetujui Kaprodi
            $this->db->where('pm.status_pembimbing', '1');     // Disetujui Pembimbing
            
            // Cek jika ada kolom workflow_status
            if ($this->db->field_exists('workflow_status', 'proposal_mahasiswa')) {
                $this->db->where('pm.workflow_status', 'bimbingan'); // Dalam fase bimbingan
            }
            
            $this->db->where('m.status', '1');                 // Mahasiswa aktif
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            $proposal = $this->db->get()->row();
            
            if (ENVIRONMENT === 'development' && $proposal) {
                log_message('debug', 'Found approved proposal: ' . $proposal->id . ' for mahasiswa: ' . $mahasiswa_id);
            }
            
            return $proposal;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting approved proposal: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * PERBAIKAN: Method untuk mendapatkan proposal berdasarkan ID dengan security check
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                pm.*,
                m.nama as nama_mahasiswa,
                m.nim,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                p.nama as nama_prodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id); // Security check
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * PERBAIKAN: Method untuk mendapatkan seminar berdasarkan proposal_id
     * Gunakan nama tabel yang benar: seminar_proposal_mahasiswa
     */
    private function _get_seminar_by_proposal_id($proposal_id)
    {
        try {
            $this->db->select('*');
            $this->db->from('seminar_proposal_mahasiswa'); // ✅ Nama tabel yang benar
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by proposal ID: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * PERBAIKAN: Method untuk mendapatkan seminar berdasarkan ID dengan security check
     * Gunakan nama tabel yang benar: seminar_proposal_mahasiswa
     */
    private function _get_seminar_by_id($seminar_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                sp.*,
                pm.judul as proposal_judul,
                pm.dosen_id,
                m.nama as nama_mahasiswa,
                m.nim,
                d.nama as nama_pembimbing
            ');
            $this->db->from('seminar_proposal_mahasiswa sp'); // ✅ Nama tabel yang benar
            $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            
            $this->db->where('sp.id', $seminar_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id); // Security check
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * PERBAIKAN: Method untuk mengecek syarat jurnal bimbingan
     */
    private function _check_jurnal_requirement($proposal_id)
    {
        try {
            // Minimum jurnal yang harus tervalidasi (bisa diatur di config)
            $minimum_required = 8;
            
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [
                    'eligible' => false,
                    'minimum_required' => $minimum_required,
                    'total_validated' => 0,
                    'message' => 'Tabel jurnal bimbingan belum tersedia'
                ];
            }
            
            // Hitung jurnal yang sudah tervalidasi
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $validated_count = $this->db->count_all_results('jurnal_bimbingan');
            
            $eligible = $validated_count >= $minimum_required;
            
            return [
                'eligible' => $eligible,
                'minimum_required' => $minimum_required,
                'total_validated' => $validated_count,
                'message' => $eligible ? 
                    "Syarat jurnal bimbingan terpenuhi ({$validated_count}/{$minimum_required})" :
                    "Jurnal bimbingan belum mencukupi ({$validated_count}/{$minimum_required})"
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error checking jurnal requirement: ' . $e->getMessage());
            return [
                'eligible' => false,
                'minimum_required' => 8,
                'total_validated' => 0,
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
     * PERBAIKAN: Method untuk file upload dengan validation
     */
    private function _handle_file_upload($field_name, $subfolder = 'proposal_files')
    {
        try {
            $upload_path = FCPATH . 'uploads/seminar_proposal/' . $subfolder . '/';
            
            // Buat folder jika belum ada
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            $config = [
                'upload_path' => $upload_path,
                'allowed_types' => 'pdf|doc|docx',
                'max_size' => 1024, // 1MB
                'encrypt_name' => true,
                'remove_spaces' => true
            ];
            
            $this->upload->initialize($config);
            
            if ($this->upload->do_upload($field_name)) {
                $upload_data = $this->upload->data();
                
                return [
                    'status' => true,
                    'filename' => $upload_data['file_name'],
                    'original_name' => $upload_data['orig_name'],
                    'file_size' => $upload_data['file_size']
                ];
            } else {
                return [
                    'status' => false,
                    'message' => $this->upload->display_errors('', ''),
                    'filename' => null
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'filename' => null
            ];
        }
    }

    /**
     * PERBAIKAN: Custom validation callback untuk file upload
     */
    public function _check_file_proposal($str)
    {
        if (empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_message('_check_file_proposal', 'File proposal harus diupload.');
            return false;
        }
        
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_type = $_FILES['file_proposal']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $this->form_validation->set_message('_check_file_proposal', 'Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.');
            return false;
        }
        
        if ($_FILES['file_proposal']['size'] > 1048576) { // 1MB
            $this->form_validation->set_message('_check_file_proposal', 'Ukuran file terlalu besar. Maksimal 1MB.');
            return false;
        }
        
        return true;
    }

    /**
     * PERBAIKAN: Method untuk mendapatkan log aktivitas
     */
    private function _get_activity_log($proposal_id)
    {
        try {
            // Jika tabel activity_log ada
            if ($this->db->table_exists('activity_log')) {
                $this->db->select('*');
                $this->db->from('activity_log');
                $this->db->where('proposal_id', $proposal_id);
                $this->db->where('module', 'seminar_proposal');
                $this->db->order_by('created_at', 'DESC');
                $this->db->limit(10);
                
                return $this->db->get()->result();
            }
            
            // Fallback: Buat log sederhana dari data yang ada
            $log = [];
            
            // Log dari jurnal bimbingan
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('created_at, "Jurnal bimbingan dibuat" as activity');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('proposal_id', $proposal_id);
                $this->db->order_by('created_at', 'DESC');
                $this->db->limit(5);
                
                $jurnal_log = $this->db->get()->result();
                $log = array_merge($log, $jurnal_log);
            }
            
            return $log;
            
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