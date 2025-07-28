<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller - Role Mahasiswa
 * 
 * Controller untuk mengelola seminar proposal dari sisi mahasiswa
 * File: application/controllers/mahasiswa/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0
 */

class Seminar_proposal extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Load required models, libraries
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        $this->load->library(['form_validation', 'upload', 'email', 'session']);
        
        // FIX: Hapus 'seminar_proposal' dari load helper karena sudah di autoload
        // Hanya load helper yang belum di autoload
        $this->load->helper(['file', 'security']);
        
        // Check authentication
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') !== 'mahasiswa') {
            redirect('auth/login');
        }
        
        // Setup validation error messages
        $this->load->library('seminar_proposal_validation', null, 'validation');
        $this->validation->setup_error_messages();
    }

    // =================================================================
    // MAIN PAGES
    // =================================================================

    /**
     * Dashboard Seminar Proposal Mahasiswa
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get data proposal mahasiswa yang sedang aktif
        $this->db->select('pm.*, d.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->where('pm.status_pembimbing', '1'); // Hanya yang sudah disetujui pembimbing
        $this->db->order_by('pm.id', 'DESC');
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            // Jika belum ada proposal atau belum disetujui pembimbing
            $data = [
                'title' => 'Seminar Proposal',
                'content' => 'mahasiswa/seminar_proposal/no_proposal',
                'proposal' => null,
                'seminar' => null,
                'workflow' => null
            ];
            
            $this->load->view('mahasiswa/layout/main', $data);
            return;
        }
        
        // Get seminar proposal jika ada
        $seminar = $this->seminar_model->get_by_proposal_id($proposal->id);
        
        // Get workflow status
        $workflow = null;
        if ($seminar) {
            $workflow = $this->seminar_model->get_workflow_status($seminar->id, $mahasiswa_id);
        }
        
        // Cek syarat jurnal bimbingan
        $jurnal_check = $this->seminar_model->check_jurnal_requirement($proposal->id);
        
        $data = [
            'title' => 'Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/dashboard',
            'proposal' => $proposal,
            'seminar' => $seminar,
            'workflow' => $workflow,
            'jurnal_check' => $jurnal_check,
            'can_submit' => !$seminar && $jurnal_check['eligible']
        ];
        
        $this->load->view('mahasiswa/layout/main', $data);
    }

    /**
     * Form Pengajuan Seminar Proposal
     */
    public function ajukan()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal mahasiswa
        $this->db->select('pm.*, d.nama as nama_pembimbing, d.email as email_pembimbing');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau belum disetujui pembimbing.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Cek syarat jurnal bimbingan
        $syarat_jurnal = $this->seminar_model->check_jurnal_requirement($proposal->id);
        
        if (!$syarat_jurnal['eligible']) {
            $this->session->set_flashdata('error', 
                "Belum memenuhi syarat pengajuan seminar proposal. " . $syarat_jurnal['message'] . 
                " Saat ini: {$syarat_jurnal['jurnal_validated_count']} jurnal.");
            redirect('mahasiswa/seminar_proposal');
        }
        
        // Cek apakah sudah pernah mengajukan
        $existing_seminar = $this->seminar_model->get_by_proposal_id($proposal->id);
        
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
        $data['jurnal_validasi'] = $this->seminar_model->get_validated_jurnal($proposal->id);
        
        $this->load->view('mahasiswa/layout/main', $data);
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
        $seminar = $this->seminar_model->get_by_id($id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar proposal tidak ditemukan.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Get workflow status
        $workflow = $this->seminar_model->get_workflow_status($id, $mahasiswa_id);
        
        // Get jurnal bimbingan yang sudah divalidasi
        $jurnal_validasi = $this->seminar_model->get_validated_jurnal($seminar->proposal_id);
        
        $data = [
            'title' => 'Detail Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/detail',
            'seminar' => $seminar,
            'workflow' => $workflow,
            'jurnal_validasi' => $jurnal_validasi
        ];
        
        $this->load->view('mahasiswa/layout/main', $data);
    }

    // =================================================================
    // AJAX ACTIONS
    // =================================================================

    /**
     * Proses pengajuan seminar proposal (AJAX)
     */
    public function proses_pengajuan()
    {
        // Cek request method
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_pengajuan_mahasiswa());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Validasi file upload
        if (!isset($_FILES['file_proposal_seminar']) || $_FILES['file_proposal_seminar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => true, 'message' => 'File proposal wajib diupload!']);
            return;
        }
        
        $file_validation = validate_file_upload($_FILES['file_proposal_seminar'], 1);
        if (!$file_validation['valid']) {
            echo json_encode(['error' => true, 'message' => $file_validation['message']]);
            return;
        }
        
        // Scan malware
        if (!basic_malware_scan($_FILES['file_proposal_seminar']['tmp_name'])) {
            echo json_encode(['error' => true, 'message' => 'File tidak aman! Terdeteksi potensi malware.']);
            return;
        }
        
        // Upload file
        $upload_result = $this->_upload_file($_FILES['file_proposal_seminar']);
        if (!$upload_result['success']) {
            echo json_encode(['error' => true, 'message' => $upload_result['message']]);
            return;
        }
        
        // Prepare data untuk model
        $data = [
            'proposal_id' => $this->input->post('proposal_id'),
            'mahasiswa_id' => $this->session->userdata('id'),
            'file_proposal' => $upload_result['filename'],
            'keterangan_mahasiswa' => $this->input->post('keterangan_tambahan') ?? ''
        ];
        
        // Cek apakah edit atau create baru
        $existing_id = $this->input->post('existing_id');
        
        if ($existing_id) {
            // Update existing
            $result = $this->seminar_model->update_pengajuan($existing_id, $data, $data['mahasiswa_id']);
        } else {
            // Create new
            $result = $this->seminar_model->create_pengajuan($data);
        }
        
        if ($result['success']) {
            // Kirim notifikasi email
            $this->_send_notification_email($data['proposal_id'], 'submitted');
            
            echo json_encode([
                'error' => false,
                'message' => 'Pengajuan seminar proposal berhasil disimpan! Email notifikasi telah dikirim ke dosen pembimbing.',
                'redirect' => base_url('mahasiswa/seminar_proposal')
            ]);
        } else {
            // Hapus file jika gagal simpan
            if (file_exists('./uploads/seminar_proposal/' . $upload_result['filename'])) {
                unlink('./uploads/seminar_proposal/' . $upload_result['filename']);
            }
            
            echo json_encode([
                'error' => true,
                'message' => $result['message']
            ]);
        }
    }

    /**
     * Get workflow status (AJAX untuk real-time tracking)
     */
    public function get_workflow_status()
    {
        $id = $this->input->get('id');
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$id) {
            echo json_encode(['error' => true, 'message' => 'ID tidak valid']);
            return;
        }
        
        $workflow = $this->seminar_model->get_workflow_status($id, $mahasiswa_id);
        
        if (!$workflow['found']) {
            echo json_encode(['error' => true, 'message' => 'Data tidak ditemukan']);
            return;
        }
        
        echo json_encode([
            'error' => false,
            'data' => $workflow
        ]);
    }

    /**
     * Check submission requirements (AJAX)
     */
    public function check_requirements()
    {
        $proposal_id = $this->input->get('proposal_id');
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$proposal_id) {
            echo json_encode(['error' => true, 'message' => 'Proposal ID tidak valid']);
            return;
        }
        
        $requirements = $this->seminar_model->can_submit($proposal_id, $mahasiswa_id);
        
        echo json_encode([
            'error' => false,
            'data' => $requirements
        ]);
    }

    // =================================================================
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Upload file proposal
     * 
     * @param array $file $_FILES data
     * @return array
     */
    private function _upload_file($file)
    {
        $upload_path = './uploads/seminar_proposal/';
        
        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Generate filename
        $mahasiswa_id = $this->session->userdata('id');
        $filename = generate_seminar_filename($mahasiswa_id, $file['name']);
        
        // Configure upload
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 1024, // 1MB
            'file_name' => $filename,
            'encrypt_name' => false, // We already have custom name
            'remove_spaces' => true
        ];
        
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('file_proposal_seminar')) {
            return [
                'success' => false,
                'message' => 'Gagal upload file: ' . $this->upload->display_errors('', ''),
                'filename' => null
            ];
        }
        
        $upload_data = $this->upload->data();
        
        return [
            'success' => true,
            'message' => 'File berhasil diupload',
            'filename' => $upload_data['file_name'],
            'file_info' => $upload_data
        ];
    }

    /**
     * Send notification email
     * 
     * @param int $proposal_id
     * @param string $event
     */
    private function _send_notification_email($proposal_id, $event)
    {
        try {
            // Get data untuk email
            $this->db->select('
                pm.id, pm.judul, pm.mahasiswa_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                p.nama as nama_prodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.id', $proposal_id);
            
            $data = $this->db->get()->row();
            
            if (!$data) {
                throw new Exception('Data not found for email notification');
            }
            
            // Setup email config (gunakan config existing)
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
            
            switch ($event) {
                case 'submitted':
                    $this->_send_submission_emails($data);
                    break;
                    
                // Add more cases for other events later
                default:
                    break;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Failed to send seminar proposal notification: ' . $e->getMessage());
        }
    }

    /**
     * Send submission notification emails
     * 
     * @param object $data
     */
    private function _send_submission_emails($data)
    {
        // Email ke mahasiswa (konfirmasi)
        $subject_mahasiswa = '[SIM-TA] Konfirmasi Pengajuan Seminar Proposal';
        $message_mahasiswa = $this->_build_email_template([
            'title' => 'Pengajuan Seminar Proposal Berhasil',
            'greeting' => "Yth. {$data->nama_mahasiswa}",
            'content' => "
                <p>Pengajuan seminar proposal Anda telah <strong>berhasil diterima</strong> sistem.</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <strong>Detail Pengajuan:</strong><br>
                    📚 <strong>Judul:</strong> {$data->judul}<br>
                    👨‍🏫 <strong>Pembimbing:</strong> {$data->nama_pembimbing}<br>
                    📅 <strong>Waktu Pengajuan:</strong> " . date('d-m-Y H:i') . "
                </div>
                <p><strong>Tahap Selanjutnya:</strong> Pengajuan Anda akan direview oleh dosen pembimbing.</p>
                <p>Anda akan menerima notifikasi email ketika ada update status.</p>
            ",
            'action_url' => base_url('mahasiswa/seminar_proposal'),
            'action_text' => 'Lihat Status'
        ]);
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($data->email_mahasiswa);
        $this->email->subject($subject_mahasiswa);
        $this->email->message($message_mahasiswa);
        $this->email->send();
        
        // Email ke dosen pembimbing (action required)
        $subject_pembimbing = '[SIM-TA] Pengajuan Seminar Proposal Baru - ' . $data->nama_mahasiswa;
        $message_pembimbing = $this->_build_email_template([
            'title' => 'Pengajuan Seminar Proposal Baru',
            'greeting' => "Yth. {$data->nama_pembimbing}",
            'content' => "
                <p>Anda menerima pengajuan seminar proposal baru yang memerlukan <strong>review dan rekomendasi</strong> Anda.</p>
                <div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <strong>Detail Mahasiswa:</strong><br>
                    👤 <strong>Nama:</strong> {$data->nama_mahasiswa} ({$data->nim})<br>
                    📚 <strong>Judul:</strong> {$data->judul}<br>
                    📅 <strong>Waktu Pengajuan:</strong> " . date('d-m-Y H:i') . "
                </div>
                <p><strong>Tindakan Diperlukan:</strong> Silakan login ke sistem untuk melakukan review dan memberikan rekomendasi.</p>
            ",
            'action_url' => base_url('dosen/seminar_proposal'),
            'action_text' => 'Review Sekarang'
        ]);
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($data->email_pembimbing);
        $this->email->subject($subject_pembimbing);
        $this->email->message($message_pembimbing);
        $this->email->send();
    }

    /**
     * Build email template
     * 
     * @param array $data
     * @return string
     */
    private function _build_email_template($data)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$data['title']}</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>{$data['title']}</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p style='margin: 0 0 20px 0; font-size: 16px;'>{$data['greeting']},</p>
                    
                    {$data['content']}
                    
                    <!-- Action Button -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$data['action_url']}' 
                           style='background: #667eea; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                           {$data['action_text']}
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}