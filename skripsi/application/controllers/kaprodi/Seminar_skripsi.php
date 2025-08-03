<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller untuk Kaprodi - Phase 5
 * 
 * Controller untuk mengelola seminar skripsi dari perspektif Kaprodi
 * Workflow: mahasiswa ajukan → review dosen → review kaprodi (turnitin check) → penjadwalan
 * 
 * Features:
 * 1. Dashboard dengan statistik dan overview
 * 2. Detail pengajuan dengan validasi turnitin (max 30%)
 * 3. Validasi plagiarisme (approve/reject) dengan file upload
 * 4. Penjadwalan seminar dan penunjukan dosen penguji
 * 5. Auto rekomendasi dosen penguji dari seminar proposal
 * 6. Email notifications ke semua pihak
 * 
 * File: application/controllers/kaprodi/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Kaprodi
 * @category    Seminar Skripsi - Phase 5
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (New Implementation)
 */
class Seminar_skripsi extends CI_Controller {

    private $prodi_id;

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->library('upload');
        $this->load->helper(['url', 'date', 'file']);
        
        // Cek login dan level kaprodi
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
        
        // Set prodi_id dari session kaprodi
        $this->prodi_id = $this->session->userdata('prodi_id');
    }

    /**
     * Index - Dashboard seminar skripsi untuk kaprodi
     */
    public function index() {
        $data = [
            'title' => 'Kelola Seminar Skripsi',
            'pengajuan_review' => $this->_get_pengajuan_perlu_review(),
            'perlu_dijadwalkan' => $this->_get_seminar_perlu_dijadwalkan(),
            'jadwal_mendatang' => $this->_get_jadwal_mendatang(),
            'stats' => $this->_get_statistics(),
            'content' => $this->load->view('kaprodi/seminar_skripsi/index', [], TRUE)
        ];
        
        $this->load->view('template/kaprodi', $data);
    }

    /**
     * Detail pengajuan seminar skripsi untuk review
     */
    public function detail($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        // Get detail seminar dengan join untuk data lengkap
        $this->db->select('
            ssm.*,
            pm.judul,
            pm.dosen_id as pembimbing_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.prodi_id,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing,
            p.nama as nama_prodi
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('ssm.id', $seminar_id);
        $this->db->where('m.prodi_id', $this->prodi_id); // Filter prodi
        
        $seminar = $this->db->get()->row();
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan dari prodi Anda!');
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        // Get dosen penguji recommendations dari seminar proposal
        $dosen_penguji_rekomendasi = $this->_get_dosen_penguji_rekomendasi($seminar->proposal_id);
        
        // Get daftar semua dosen untuk override options
        $dosen_list = $this->_get_dosen_list();
        
        $data = [
            'title' => 'Review Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'seminar' => $seminar,
            'dosen_penguji_rekomendasi' => $dosen_penguji_rekomendasi,
            'dosen_list' => $dosen_list,
            'is_turnitin_valid' => $this->_is_turnitin_valid($seminar),
            'content' => $this->load->view('kaprodi/seminar_skripsi/detail', [
                'seminar' => $seminar,
                'dosen_penguji_rekomendasi' => $dosen_penguji_rekomendasi,
                'dosen_list' => $dosen_list
            ], TRUE)
        ];
        
        $this->load->view('template/kaprodi', $data);
    }

    /**
     * Proses validasi turnitin dan keputusan kaprodi
     */
    public function validasi_turnitin() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $plagiarism_percentage = $this->input->post('plagiarism_percentage');
        $keputusan = $this->input->post('keputusan'); // 'approve' atau 'reject'
        $komentar = trim($this->input->post('komentar_kaprodi'));
        
        // Validasi input
        if (empty($seminar_id) || empty($keputusan)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        if (empty($plagiarism_percentage) || !is_numeric($plagiarism_percentage)) {
            $this->session->set_flashdata('error', 'Persentase plagiarisme harus diisi dengan angka!');
            redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        $plagiarism_percentage = floatval($plagiarism_percentage);
        
        // WORKFLOW RULE: Validasi threshold plagiarisme 30%
        if ($keputusan === 'approve' && $plagiarism_percentage > 30) {
            $this->session->set_flashdata('error', 'Skripsi dengan plagiarisme >30% tidak dapat disetujui!');
            redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        if ($keputusan === 'reject' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan!');
            redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        // Handle file upload turnitin
        $turnitin_file = null;
        if (!empty($_FILES['file_turnitin']['name'])) {
            $upload_result = $this->_handle_turnitin_upload();
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
                return;
            }
            $turnitin_file = $upload_result['filename'];
        }
        
        // Get seminar data untuk notifikasi
        $seminar = $this->_get_seminar_by_id($seminar_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        // Proses update ke database
        $this->db->trans_start();
        
        try {
            $update_data = [
                'status_kaprodi' => $keputusan === 'approve' ? 'approved' : 'rejected',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'plagiarism_percentage' => $plagiarism_percentage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($turnitin_file) {
                $update_data['file_turnitin'] = $turnitin_file;
            }
            
            // WORKFLOW: Update status sesuai keputusan
            if ($keputusan === 'approve') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'kaprodi_jadwal'; // Lanjut ke penjadwalan
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa'; // Kembali ke mahasiswa
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan data');
            }
            
            // Kirim notifikasi email
            $this->_kirim_notifikasi_validasi($seminar, $keputusan, $plagiarism_percentage, $komentar);
            
            $message = $keputusan === 'approve' ? 
                "Seminar skripsi disetujui! Plagiarisme: {$plagiarism_percentage}%. Silakan lanjut ke penjadwalan." :
                "Seminar skripsi ditolak. Mahasiswa akan mendapat notifikasi untuk perbaikan.";
                
            $this->session->set_flashdata('success', $message);
            
            // Redirect ke penjadwalan jika disetujui
            if ($keputusan === 'approve') {
                redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
                return;
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error validasi turnitin: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        redirect('kaprodi/seminar_skripsi');
    }

    /**
     * Form penjadwalan seminar skripsi
     */
    public function penjadwalan($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        // Get seminar data
        $seminar = $this->_get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->status_kaprodi !== 'approved') {
            $this->session->set_flashdata('error', 'Seminar belum disetujui atau tidak dapat dijadwalkan!');
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        // Get dosen penguji recommendations
        $dosen_penguji_rekomendasi = $this->_get_dosen_penguji_rekomendasi($seminar->proposal_id);
        $dosen_list = $this->_get_dosen_list();
        
        $data = [
            'title' => 'Penjadwalan Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'seminar' => $seminar,
            'dosen_penguji_rekomendasi' => $dosen_penguji_rekomendasi,
            'dosen_list' => $dosen_list,
            'content' => $this->load->view('kaprodi/seminar_skripsi/penjadwalan', [
                'seminar' => $seminar,
                'dosen_penguji_rekomendasi' => $dosen_penguji_rekomendasi,
                'dosen_list' => $dosen_list
            ], TRUE)
        ];
        
        $this->load->view('template/kaprodi', $data);
    }

    /**
     * Proses simpan jadwal dan penunjukan dosen penguji
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $tanggal_seminar = $this->input->post('tanggal_seminar');
        $jam_seminar = $this->input->post('jam_seminar');
        $tempat_seminar = trim($this->input->post('tempat_seminar'));
        $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
        $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');
        
        // Validasi input
        if (empty($seminar_id) || empty($tanggal_seminar) || empty($jam_seminar) || 
            empty($tempat_seminar) || empty($dosen_penguji1_id) || empty($dosen_penguji2_id)) {
            $this->session->set_flashdata('error', 'Semua field harus diisi!');
            redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
            return;
        }
        
        // Validasi tanggal tidak boleh masa lalu
        if (strtotime($tanggal_seminar) < strtotime(date('Y-m-d'))) {
            $this->session->set_flashdata('error', 'Tanggal seminar tidak boleh masa lalu!');
            redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
            return;
        }
        
        // Validasi dosen penguji tidak sama dengan pembimbing dan tidak sama satu sama lain
        $seminar = $this->_get_seminar_by_id($seminar_id);
        if ($dosen_penguji1_id == $seminar->pembimbing_id || $dosen_penguji2_id == $seminar->pembimbing_id) {
            $this->session->set_flashdata('error', 'Dosen penguji tidak boleh sama dengan dosen pembimbing!');
            redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
            return;
        }
        
        if ($dosen_penguji1_id == $dosen_penguji2_id) {
            $this->session->set_flashdata('error', 'Dosen penguji 1 dan 2 tidak boleh sama!');
            redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
            return;
        }
        
        // Proses simpan jadwal
        $this->db->trans_start();
        
        try {
            $update_data = [
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'dosen_penguji1_id' => $dosen_penguji1_id,
                'dosen_penguji2_id' => $dosen_penguji2_id,
                'status_penguji1' => 'approved', // Langsung ditunjuk, tidak perlu konfirmasi
                'status_penguji2' => 'approved', // Sesuai kebijakan STK Santo Yakobus
                'status' => 'scheduled',
                'current_step' => 'staf',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan jadwal');
            }
            
            // Kirim notifikasi ke semua pihak
            $this->_kirim_notifikasi_jadwal($seminar_id);
            
            $this->session->set_flashdata('success', 'Jadwal seminar skripsi berhasil ditetapkan! Notifikasi telah dikirim ke semua pihak.');
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error simpan jadwal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        redirect('kaprodi/seminar_skripsi');
    }

    // ===============================
    // PRIVATE HELPER METHODS
    // ===============================

    /**
     * Get pengajuan yang perlu direview kaprodi
     */
    private function _get_pengajuan_perlu_review() {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('ssm.status', 'review_kaprodi');
        $this->db->where('ssm.status_pembimbing', 'approved');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->order_by('ssm.tanggal_review_pembimbing', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar yang perlu dijadwalkan
     */
    private function _get_seminar_perlu_dijadwalkan() {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('ssm.status', 'approved');
        $this->db->where('ssm.status_kaprodi', 'approved');
        $this->db->where('ssm.tanggal_seminar IS NULL');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->order_by('ssm.tanggal_review_kaprodi', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get jadwal seminar mendatang
     */
    private function _get_jadwal_mendatang() {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing,
            d1.nama as nama_penguji1,
            d2.nama as nama_penguji2
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('ssm.status', 'scheduled');
        $this->db->where('ssm.tanggal_seminar >=', date('Y-m-d'));
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->order_by('ssm.tanggal_seminar', 'ASC');
        $this->db->order_by('ssm.jam_seminar', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics() {
        // Total pengajuan dalam prodi
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $total = $this->db->get()->row()->total;
        
        // Perlu review
        $this->db->select('COUNT(*) as perlu_review');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'review_kaprodi');
        $this->db->where('ssm.status_pembimbing', 'approved');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $perlu_review = $this->db->get()->row()->perlu_review;
        
        // Perlu dijadwalkan
        $this->db->select('COUNT(*) as perlu_dijadwalkan');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'approved');
        $this->db->where('ssm.status_kaprodi', 'approved');
        $this->db->where('ssm.tanggal_seminar IS NULL');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $perlu_dijadwalkan = $this->db->get()->row()->perlu_dijadwalkan;
        
        // Terjadwal
        $this->db->select('COUNT(*) as terjadwal');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'scheduled');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $terjadwal = $this->db->get()->row()->terjadwal;
        
        return [
            'total' => $total,
            'perlu_review' => $perlu_review,
            'perlu_dijadwalkan' => $perlu_dijadwalkan,
            'terjadwal' => $terjadwal
        ];
    }

    /**
     * Get seminar by ID dengan data lengkap
     */
    private function _get_seminar_by_id($seminar_id) {
        $this->db->select('
            ssm.*,
            pm.judul,
            pm.dosen_id as pembimbing_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('ssm.id', $seminar_id);
        $this->db->where('m.prodi_id', $this->prodi_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get rekomendasi dosen penguji dari seminar proposal
     */
    private function _get_dosen_penguji_rekomendasi($proposal_id) {
        $this->db->select('
            spm.dosen_penguji1_id,
            spm.dosen_penguji2_id,
            d1.nama as nama_penguji1,
            d2.nama as nama_penguji2
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('dosen d1', 'spm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'spm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('spm.proposal_id', $proposal_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get daftar dosen untuk dropdown
     */
    private function _get_dosen_list() {
        $this->db->select('id, nama, email');
        $this->db->from('dosen');
        $this->db->where('status', '1'); // Aktif
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Check validitas turnitin
     */
    private function _is_turnitin_valid($seminar) {
        return !empty($seminar->plagiarism_percentage) && 
               is_numeric($seminar->plagiarism_percentage) && 
               $seminar->plagiarism_percentage <= 30;
    }

    /**
     * Handle upload file turnitin
     */
    private function _handle_turnitin_upload() {
        $config = [
            'upload_path' => './uploads/turnitin/',
            'allowed_types' => 'pdf',
            'max_size' => 5120, // 5MB
            'encrypt_name' => TRUE,
            'file_ext_tolower' => TRUE
        ];
        
        // Create directory if not exists
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }
        
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('file_turnitin')) {
            return [
                'success' => FALSE,
                'message' => 'Upload gagal: ' . $this->upload->display_errors('', '')
            ];
        }
        
        $upload_data = $this->upload->data();
        return [
            'success' => TRUE,
            'filename' => $upload_data['file_name']
        ];
    }

    /**
     * Kirim notifikasi validasi turnitin
     */
    private function _kirim_notifikasi_validasi($seminar, $keputusan, $plagiarism_percentage, $komentar) {
        try {
            $status_text = $keputusan === 'approve' ? 'DISETUJUI' : 'DITOLAK';
            $bg_color = $keputusan === 'approve' ? '#28a745' : '#dc3545';
            
            // Email ke mahasiswa
            $subject_mhs = "🔔 Status Validasi Seminar Skripsi - {$status_text}";
            
            $message_mhs = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: {$bg_color}; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>{$status_text} - Validasi Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Pengajuan seminar skripsi Anda telah <strong>{$status_text}</strong> oleh Ketua Program Studi.</p>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='margin: 0 0 10px 0;'>📊 Detail Validasi:</h4>
                        <ul style='margin: 0;'>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}%</li>
                            <li><strong>Status:</strong> {$status_text}</li>
                            <li><strong>Tanggal Review:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>";
            
            if (!empty($komentar)) {
                $message_mhs .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Komentar Kaprodi:</h4>
                        <p style='color: #856404; margin: 0;'>{$komentar}</p>
                    </div>";
            }
            
            if ($keputusan === 'approve') {
                $message_mhs .= "
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>🎉 Selamat!</h4>
                        <p style='color: #155724; margin: 0;'>Seminar skripsi Anda akan segera dijadwalkan. Anda akan mendapat notifikasi jadwal melalui email.</p>
                    </div>";
            } else {
                $message_mhs .= "
                    <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>🔄 Tindakan Diperlukan:</h4>
                        <p style='color: #721c24; margin: 0;'>Silakan perbaiki skripsi sesuai komentar dan ajukan ulang melalui sistem.</p>
                    </div>";
            }
            
            $message_mhs .= "
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject($subject_mhs);
            $this->email->message($message_mhs);
            $this->email->send();
            
            // Email ke dosen pembimbing
            if (!empty($seminar->email_pembimbing)) {
                $subject_dosen = "🔔 Validasi Seminar Skripsi Mahasiswa Bimbingan - {$status_text}";
                
                $message_dosen = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                    <div style='background: {$bg_color}; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>Validasi Seminar Skripsi - {$status_text}</h2>
                    </div>
                    
                    <div style='padding: 20px; background-color: #f8f9fa;'>
                        <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                        
                        <p>Seminar skripsi mahasiswa bimbingan Anda telah <strong>{$status_text}</strong> oleh Kaprodi.</p>
                        
                        <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <h4 style='margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                            <ul style='margin: 0;'>
                                <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                                <li><strong>NIM:</strong> {$seminar->nim}</li>
                                <li><strong>Judul:</strong> {$seminar->judul}</li>
                                <li><strong>Plagiarisme:</strong> {$plagiarism_percentage}%</li>
                            </ul>
                        </div>
                        
                        <p>Terima kasih atas bimbingan yang telah diberikan.</p>
                    </div>
                    
                    <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                        STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                    </div>
                </div>";
                
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($seminar->email_pembimbing);
                $this->email->subject($subject_dosen);
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error mengirim notifikasi validasi: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi jadwal ke semua pihak
     */
    private function _kirim_notifikasi_jadwal($seminar_id) {
        try {
            // Get data seminar lengkap dengan dosen penguji
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d1.nama as nama_penguji1,
                d1.email as email_penguji1,
                d2.nama as nama_penguji2,
                d2.email as email_penguji2
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            
            $seminar = $this->db->get()->row();
            
            if (!$seminar) return;
            
            $tanggal_formatted = date('d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            // Email ke mahasiswa
            $subject = "🎯 Jadwal Seminar Skripsi Telah Ditetapkan";
            
            $message_mahasiswa = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📅 Jadwal Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Jadwal seminar skripsi Anda telah ditetapkan. Berikut detail jadwalnya:</p>
                    
                    <div style='background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h3 style='color: #155724; margin: 0 0 15px 0; text-align: center;'>📋 DETAIL SEMINAR SKRIPSI</h3>
                        <table style='width: 100%; color: #155724;'>
                            <tr><td><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>{$seminar->judul}</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='margin: 0 0 10px 0;'>👥 Tim Penguji:</h4>
                        <ul style='margin: 0;'>
                            <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
                            <li><strong>Penguji 1:</strong> {$seminar->nama_penguji1}</li>
                            <li><strong>Penguji 2:</strong> {$seminar->nama_penguji2}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #cce7ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>✅ Persiapan Seminar:</h4>
                        <ul style='color: #004085; margin: 0;'>
                            <li>Siapkan presentasi maksimal 20 menit</li>
                            <li>Bawa hard copy skripsi untuk tim penguji</li>
                            <li>Siapkan mental untuk sesi tanya jawab</li>
                            <li>Hadir 15 menit sebelum jadwal</li>
                        </ul>
                    </div>
                    
                    <p style='text-align: center; margin: 20px 0;'><strong>Semoga sukses!</strong></p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            // Kirim ke mahasiswa
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message_mahasiswa);
            $this->email->send();
            
            // Email ke dosen (pembimbing + penguji)
            $recipients = [
                ['email' => $seminar->email_pembimbing, 'nama' => $seminar->nama_pembimbing, 'role' => 'Pembimbing'],
                ['email' => $seminar->email_penguji1, 'nama' => $seminar->nama_penguji1, 'role' => 'Penguji 1'],
                ['email' => $seminar->email_penguji2, 'nama' => $seminar->nama_penguji2, 'role' => 'Penguji 2']
            ];
            
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) continue;
                
                $message_dosen = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                    <div style='background: linear-gradient(135deg, #007bff 0%, #6610f2 100%); color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>📅 Penunjukan Seminar Skripsi</h2>
                    </div>
                    
                    <div style='padding: 20px; background-color: #f8f9fa;'>
                        <p>Kepada Yth. <strong>{$recipient['nama']}</strong>,</p>
                        
                        <p>Anda ditunjuk sebagai <strong>{$recipient['role']}</strong> dalam seminar skripsi mahasiswa berikut:</p>
                        
                        <div style='background-color: #cce7ff; padding: 20px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                            <h3 style='color: #004085; margin: 0 0 15px 0; text-align: center;'>📋 DETAIL SEMINAR</h3>
                            <table style='width: 100%; color: #004085;'>
                                <tr><td><strong>Mahasiswa:</strong></td><td>{$seminar->nama_mahasiswa} ({$seminar->nim})</td></tr>
                                <tr><td><strong>Judul:</strong></td><td>{$seminar->judul}</td></tr>
                                <tr><td><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                                <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                                <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                                <tr><td><strong>Role Anda:</strong></td><td><strong>{$recipient['role']}</strong></td></tr>
                            </table>
                        </div>
                        
                        <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <h4 style='color: #856404; margin: 0 0 10px 0;'>ℹ️ Informasi Penting:</h4>
                            <ul style='color: #856404; margin: 0;'>
                                <li>Sesuai kebijakan STK Santo Yakobus, penunjukan ini langsung berlaku</li>
                                <li>Tidak diperlukan konfirmasi kesediaan</li>
                                <li>Harap hadir tepat waktu sesuai jadwal</li>
                                <li>Siapkan pertanyaan dan masukan konstruktif</li>
                            </ul>
                        </div>
                        
                        <p>Terima kasih atas kesediaan Anda.</p>
                    </div>
                    
                    <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                        STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                    </div>
                </div>";
                
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($recipient['email']);
                $this->email->subject("🔔 Penunjukan {$recipient['role']} Seminar Skripsi");
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            // Email ke staf akademik
            $this->_kirim_notifikasi_ke_staf($seminar);
            
        } catch (Exception $e) {
            log_message('error', 'Error mengirim notifikasi jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke staf akademik
     */
    private function _kirim_notifikasi_ke_staf($seminar) {
        // Get email staf akademik
        $this->db->select('email');
        $this->db->from('users');
        $this->db->where('level', '4'); // Level staf
        $this->db->where('status', '1'); // Aktif
        $staf_emails = $this->db->get()->result();
        
        foreach ($staf_emails as $staf) {
            if (empty($staf->email)) continue;
            
            $tanggal_formatted = date('d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📊 Seminar Skripsi Terjadwal</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>Staf Akademik</strong>,</p>
                    
                    <p>Jadwal seminar skripsi baru telah ditetapkan dan memerlukan persiapan administrasi:</p>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='margin: 0 0 10px 0;'>📋 Detail Seminar:</h4>
                        <ul style='margin: 0;'>
                            <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                            <li><strong>Tanggal:</strong> {$tanggal_formatted}</li>
                            <li><strong>Waktu:</strong> {$jam_formatted} WIB</li>
                            <li><strong>Tempat:</strong> {$seminar->tempat_seminar}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 Persiapan Diperlukan:</h4>
                        <ul style='color: #856404; margin: 0;'>
                            <li>Siapkan ruang seminar sesuai jadwal</li>
                            <li>Siapkan form penilaian untuk tim penguji</li>
                            <li>Koordinasi teknis (proyektor, sound system, dll)</li>
                            <li>Update jadwal di papan pengumuman</li>
                        </ul>
                    </div>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($staf->email);
            $this->email->subject('📊 Persiapan Seminar Skripsi Terjadwal');
            $this->email->message($message);
            $this->email->send();
        }
    }
}