<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penelitian Controller untuk Mahasiswa - SIM-TA STK Santo Yakobus
 * 
 * Controller untuk mengelola modul Penelitian (Tahap 4 Workflow)
 * Workflow: Cek Syarat → Ajukan Permohonan → Dosen Review → Staf Proses → Download Surat
 * 
 * File: application/controllers/mahasiswa/Penelitian.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Workflow Integration)
 */
class Penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('upload');
        $this->load->helper(['url', 'file', 'security']);
        $this->load->model('Penelitian_model', 'penelitian');
        
        // Cek login mahasiswa
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard penelitian mahasiswa
     * Menampilkan status dan progress permohonan izin penelitian
     */
    public function index() {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal aktif mahasiswa
        $proposal_aktif = $this->_get_proposal_aktif($mahasiswa_id);
        
        if (!$proposal_aktif) {
            $this->session->set_flashdata('error', 'Anda belum memiliki proposal yang aktif');
            redirect('mahasiswa/dashboard');
        }

        // Cek eligibility untuk mengajukan penelitian
        $eligibility = $this->penelitian->check_eligibility($proposal_aktif->id, $mahasiswa_id);
        
        // Get permohonan yang sudah ada (jika ada)
        $permohonan_existing = $this->penelitian->get_permohonan_by_mahasiswa($mahasiswa_id);
        $permohonan_data = !empty($permohonan_existing['data']) ? $permohonan_existing['data'][0] : null;
        
        // Prepare data untuk view
        $view_data = [
            'proposal' => $proposal_aktif,
            'eligibility' => $eligibility,
            'permohonan' => $permohonan_data,
            'can_submit' => $eligibility['eligible'] && !$permohonan_data,
            'progress_steps' => $this->_get_progress_steps($permohonan_data)
        ];
        
        // Data untuk template mahasiswa
        $data = [
            'title' => 'Penelitian - Tahap 4',
            'content' => $this->load->view('mahasiswa/penelitian/index', $view_data, TRUE),
            'active_menu' => 'penelitian'
        ];
        
        $this->load->view('template/mahasiswa', $data);
    }

    /**
     * Form pengajuan permohonan izin penelitian
     */
    public function ajukan() {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal aktif
        $proposal_aktif = $this->_get_proposal_aktif($mahasiswa_id);
        
        if (!$proposal_aktif) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('mahasiswa/penelitian');
        }

        // Cek eligibility
        $eligibility = $this->penelitian->check_eligibility($proposal_aktif->id, $mahasiswa_id);
        
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', $eligibility['message']);
            redirect('mahasiswa/penelitian');
        }

        // Cek apakah sudah ada permohonan
        $existing = $this->penelitian->get_permohonan_by_mahasiswa($mahasiswa_id);
        if (!empty($existing['data']) && $existing['data'][0]->status != 'rejected') {
            $this->session->set_flashdata('error', 'Anda sudah memiliki permohonan yang sedang diproses');
            redirect('mahasiswa/penelitian');
        }

        // Handle form submission
        if ($this->input->post()) {
            $this->_handle_form_submission($proposal_aktif, $mahasiswa_id);
            return;
        }

        // Get data mahasiswa untuk pre-fill form
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
    }

    /**
     * Detail permohonan penelitian
     */
    public function detail($permohonan_id = null) {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$permohonan_id) {
            show_404();
        }

        // Get detail permohonan dengan validasi ownership
        $detail_result = $this->penelitian->get_permohonan_detail($permohonan_id, $mahasiswa_id);
        
        if ($detail_result['error'] || !$detail_result['data']) {
            $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan');
            redirect('mahasiswa/penelitian');
        }

        $permohonan = $detail_result['data'];
        
        $view_data = [
            'permohonan' => $permohonan,
            'progress_steps' => $this->_get_progress_steps($permohonan),
            'can_download' => ($permohonan->status == 'surat_ready' || $permohonan->status == 'completed')
        ];
        
        $data = [
            'title' => 'Detail Permohonan Penelitian',
            'content' => $this->load->view('mahasiswa/penelitian/detail', $view_data, TRUE),
            'active_menu' => 'penelitian'
        ];
        
        $this->load->view('template/mahasiswa', $data);
    }

    /**
     * Download surat izin penelitian
     */
    public function download_surat($permohonan_id) {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get detail permohonan dengan validasi
        $detail_result = $this->penelitian->get_permohonan_detail($permohonan_id, $mahasiswa_id);
        
        if ($detail_result['error'] || !$detail_result['data']) {
            show_404();
        }

        $permohonan = $detail_result['data'];
        
        // Validasi status dan file
        if (!in_array($permohonan->status, ['surat_ready', 'completed']) || !$permohonan->file_surat_izin_staf) {
            $this->session->set_flashdata('error', 'Surat belum tersedia untuk didownload');
            redirect('mahasiswa/penelitian/detail/' . $permohonan_id);
        }

        $file_path = FCPATH . 'uploads/surat_izin/' . $permohonan->file_surat_izin_staf;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File surat tidak ditemukan di server');
            redirect('mahasiswa/penelitian/detail/' . $permohonan_id);
        }

        // Update status ke completed jika masih surat_ready
        if ($permohonan->status == 'surat_ready') {
            $this->penelitian->update_status($permohonan_id, 'completed', [
                'mahasiswa_download_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Log aktivitas download
        $this->_log_activity($permohonan_id, 'download_surat', 'Mahasiswa mendownload surat izin penelitian');

        // Force download
        $file_name = 'Surat_Izin_Penelitian_' . $permohonan->nim . '_' . date('Y-m-d') . '.pdf';
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    }

    // =================================================================
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Get proposal aktif mahasiswa
     */
    private function _get_proposal_aktif($mahasiswa_id) {
        $this->db->select('pm.*, m.nama, m.nim, m.prodi_id, p.nama as nama_prodi, d.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->where('pm.status_kaprodi', '1'); // Sudah disetujui kaprodi
        $this->db->order_by('pm.id', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Get data mahasiswa lengkap
     */
    private function _get_mahasiswa_data($mahasiswa_id) {
        $this->db->select('m.*, p.nama as nama_prodi');
        $this->db->from('mahasiswa m');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('m.id', $mahasiswa_id);
        
        return $this->db->get()->row();
    }

    /**
     * Handle form submission untuk pengajuan penelitian
     */
    private function _handle_form_submission($proposal, $mahasiswa_id) {
        // Set validation rules
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

        // Prepare data untuk model
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
            'dosen_pembimbing_id' => $proposal->dosen_id
        ];

        // Handle file upload jika ada
        if (!empty($_FILES['file_proposal_revisi']['name'])) {
            $input_data['file_proposal_revisi'] = $_FILES['file_proposal_revisi'];
        }

        // Submit permohonan
        $result = $this->penelitian->create_permohonan($input_data);

        if ($result['error']) {
            $this->session->set_flashdata('error', $result['message']);
        } else {
            $this->session->set_flashdata('success', 'Permohonan izin penelitian berhasil diajukan');
            
            // Send notification email ke dosen pembimbing
            $this->_send_notification_to_pembimbing($result['data']['permohonan_id'], $proposal->dosen_id);
        }

        redirect('mahasiswa/penelitian');
    }

    /**
     * Get progress steps untuk tracking visual
     */
    private function _get_progress_steps($permohonan) {
        $steps = [
            ['title' => 'Pengajuan', 'status' => 'completed', 'icon' => 'file-text'],
            ['title' => 'Review Pembimbing', 'status' => 'pending', 'icon' => 'user-check'],
            ['title' => 'Proses Staf', 'status' => 'pending', 'icon' => 'clipboard'],
            ['title' => 'Download Surat', 'status' => 'pending', 'icon' => 'download']
        ];

        if (!$permohonan) {
            $steps[0]['status'] = 'pending';
            return $steps;
        }

        // Update step status berdasarkan status permohonan
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

    /**
     * Send notification email ke dosen pembimbing
     */
    private function _send_notification_to_pembimbing($permohonan_id, $dosen_id) {
        // Get email dosen
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if ($dosen && $dosen->email) {
            // Insert ke tabel notifikasi
            $notif_data = [
                'user_id' => $dosen_id,
                'untuk_role' => 'dosen',
                'judul' => 'Permohonan Izin Penelitian Baru',
                'pesan' => 'Ada permohonan izin penelitian baru yang memerlukan review Anda',
                'link' => 'dosen/penelitian/review/' . $permohonan_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('notifikasi', $notif_data);
            
            // TODO: Send actual email using email library
            // $this->_send_email($dosen->email, $subject, $message);
        }
    }

    /**
     * Log aktivitas penelitian
     */
    private function _log_activity($permohonan_id, $aktivitas, $keterangan) {
        $log_data = [
            'permohonan_id' => $permohonan_id,
            'user_id' => $this->session->userdata('id'),
            'aktivitas' => $aktivitas,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_penelitian', $log_data);
    }
}