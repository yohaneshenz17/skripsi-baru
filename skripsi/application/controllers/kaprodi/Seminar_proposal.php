<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller untuk Kaprodi - Phase 3
 * 
 * Controller untuk mengelola workflow seminar proposal dari perspektif Kaprodi
 * Features:
 * 1. Dashboard dengan list pengajuan perlu review
 * 2. Validasi plagiarisme dengan upload file Turnitin
 * 3. Penjadwalan seminar dan penunjukan penguji
 * 4. Sistem notifikasi email otomatis
 * 
 * File: application/controllers/kaprodi/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Kaprodi
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Phase 3 Implementation)
 */
class Seminar_proposal extends CI_Controller {
    
    private $prodi_id;
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'email', 'upload']);
        $this->load->helper(['url', 'file', 'security']);
        
        // Load model yang sudah ada
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        
        // Auth check untuk kaprodi (konsisten dengan controller kaprodi lain)
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id dari session (sesuai pola kaprodi existing)
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
        }
    }
    
    // =================================================================
    // MAIN METHODS - WORKFLOW KAPRODI
    // =================================================================
    
    /**
     * Dashboard Seminar Proposal Kaprodi
     * URL: kaprodi/seminar_proposal
     */
    public function index() {
        $data['title'] = 'Seminar Proposal';
        
        // Get data untuk dashboard
        $data['pending_reviews'] = $this->_get_pengajuan_perlu_review();
        $data['statistics'] = $this->_get_statistics();
        $data['recent_approved'] = $this->_get_recent_approved();
        
        // Load view
        $this->load->view('kaprodi/seminar_proposal/index', $data);
    }
    
    /**
     * Detail pengajuan seminar proposal dan form validasi plagiarisme
     * URL: kaprodi/seminar_proposal/detail/{id}
     */
    public function detail($seminar_id) {
        $data['title'] = 'Detail Seminar Proposal';
        
        // Get detail menggunakan model yang sudah ada
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        
        if (!$seminar || !$this->_is_kaprodi_authorized($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau tidak memiliki akses!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // Pastikan status sudah di tahap review kaprodi
        if ($seminar->status != 'review_kaprodi' || $seminar->status_pembimbing != 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum melalui tahap review pembimbing atau sudah diproses!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $data['seminar'] = $seminar;
        $data['jurnal_requirement'] = $this->seminar_model->check_jurnal_requirement($seminar->proposal_id);
        $data['dosen_list'] = $this->_get_available_penguji();
        
        $this->load->view('kaprodi/seminar_proposal/detail', $data);
    }
    
    /**
     * Proses validasi plagiarisme
     * URL: kaprodi/seminar_proposal/proses_validasi (POST)
     */
    public function proses_validasi() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $plagiarism_percentage = floatval($this->input->post('plagiarism_percentage'));
        $komentar = trim($this->input->post('komentar_kaprodi'));
        
        // Validasi input
        if (empty($seminar_id) || $plagiarism_percentage < 0 || $plagiarism_percentage > 100) {
            $this->session->set_flashdata('error', 'Data tidak valid!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        if (!$seminar || !$this->_is_kaprodi_authorized($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau tidak memiliki akses!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $this->db->trans_start();
        
        try {
            // Jika plagiarisme >= 30%, tolak
            if ($plagiarism_percentage >= 30) {
                // Handle upload file turnitin (WAJIB untuk penolakan)
                $file_turnitin = $this->_handle_turnitin_upload();
                
                if (!$file_turnitin) {
                    throw new Exception('File hasil pengecekan plagiarisme wajib diupload untuk penolakan!');
                }
                
                $update_data = [
                    'status_kaprodi' => 'rejected',
                    'status' => 'rejected',
                    'current_step' => 'mahasiswa',
                    'komentar_kaprodi' => empty($komentar) ? 'Plagiarisme terlalu tinggi (' . $plagiarism_percentage . '%)' : $komentar,
                    'plagiarism_percentage' => $plagiarism_percentage,
                    'file_turnitin' => $file_turnitin,
                    'reviewed_by_kaprodi' => $this->session->userdata('id'),
                    'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_proposal_mahasiswa', $update_data);
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Gagal menyimpan data penolakan');
                }
                
                // Send rejection notifications
                $this->_send_rejection_notifications($seminar, $plagiarism_percentage, $komentar);
                
                $this->session->set_flashdata('success', 'Pengajuan seminar proposal berhasil ditolak karena plagiarisme tinggi (' . $plagiarism_percentage . '%).');
                redirect('kaprodi/seminar_proposal');
                
            } else {
                // Jika plagiarisme < 30%, simpan data dan lanjut ke penjadwalan
                $update_data = [
                    'plagiarism_percentage' => $plagiarism_percentage,
                    'komentar_kaprodi' => $komentar,
                    'reviewed_by_kaprodi' => $this->session->userdata('id'),
                    'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_proposal_mahasiswa', $update_data);
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Gagal menyimpan data validasi');
                }
                
                // Redirect ke form penjadwalan
                $this->session->set_flashdata('success', 'Validasi plagiarisme berhasil! Silakan lanjutkan dengan penjadwalan seminar.');
                redirect('kaprodi/seminar_proposal/jadwal_seminar/' . $seminar_id);
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error proses validasi plagiarisme: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
        }
    }
    
    /**
     * Form penjadwalan seminar dan penunjukan penguji
     * URL: kaprodi/seminar_proposal/jadwal_seminar/{id}
     */
    public function jadwal_seminar($seminar_id) {
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        
        if (!$seminar || !$this->_is_kaprodi_authorized($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau tidak memiliki akses!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // Pastikan sudah melalui tahap validasi plagiarisme dan tidak ditolak
        if (empty($seminar->plagiarism_percentage) || $seminar->plagiarism_percentage >= 30 || $seminar->status == 'rejected') {
            $this->session->set_flashdata('error', 'Seminar proposal belum melalui validasi plagiarisme atau ditolak!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        $data['title'] = 'Jadwal Seminar Proposal - ' . $seminar->nama_mahasiswa;
        $data['seminar'] = $seminar;
        $data['dosen_list'] = $this->_get_available_penguji();
        
        $this->load->view('kaprodi/seminar_proposal/jadwal', $data);
    }
    
    /**
     * Simpan jadwal dan penunjukan penguji
     * URL: kaprodi/seminar_proposal/simpan_jadwal (POST)
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $tanggal_seminar = $this->input->post('tanggal_seminar');
        $jam_seminar = $this->input->post('jam_seminar');
        $tempat_seminar = trim($this->input->post('tempat_seminar'));
        $penguji1_id = $this->input->post('penguji1_id');
        $penguji2_id = $this->input->post('penguji2_id');
        
        // Validasi input
        if (empty($seminar_id) || empty($tanggal_seminar) || empty($jam_seminar) || 
            empty($tempat_seminar) || empty($penguji1_id) || empty($penguji2_id)) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi!');
            redirect('kaprodi/seminar_proposal/jadwal_seminar/' . $seminar_id);
            return;
        }
        
        if ($penguji1_id == $penguji2_id) {
            $this->session->set_flashdata('error', 'Penguji 1 dan Penguji 2 tidak boleh sama!');
            redirect('kaprodi/seminar_proposal/jadwal_seminar/' . $seminar_id);
            return;
        }
        
        // Validasi tanggal tidak boleh di masa lalu
        if (strtotime($tanggal_seminar . ' ' . $jam_seminar) <= time()) {
            $this->session->set_flashdata('error', 'Tanggal dan waktu seminar harus di masa depan!');
            redirect('kaprodi/seminar_proposal/jadwal_seminar/' . $seminar_id);
            return;
        }
        
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        if (!$seminar || !$this->_is_kaprodi_authorized($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau tidak memiliki akses!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $this->db->trans_start();
        
        try {
            // Update seminar proposal dengan jadwal dan penguji
            $update_data = [
                'status_kaprodi' => 'approved',
                'status' => 'scheduled',
                'current_step' => 'staf',
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'dosen_penguji1_id' => $penguji1_id,
                'dosen_penguji2_id' => $penguji2_id,
                'komentar_kaprodi' => $this->input->post('catatan_kaprodi'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            if ($this->db->affected_rows() == 0) {
                throw new Exception('Gagal menyimpan jadwal seminar');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }
            
            // Send notifications ke semua stakeholder
            $this->_send_approval_notifications($seminar_id);
            
            $this->session->set_flashdata('success', 'Seminar proposal berhasil dijadwalkan dan penguji telah ditunjuk! Notifikasi telah dikirim ke semua pihak terkait.');
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error saving seminar schedule: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('kaprodi/seminar_proposal/jadwal_seminar/' . $seminar_id);
            return;
        }
        
        redirect('kaprodi/seminar_proposal');
    }
    
    // =================================================================
    // HELPER METHODS - PRIVATE
    // =================================================================
    
    /**
     * Get pengajuan yang perlu review kaprodi
     */
    private function _get_pengajuan_perlu_review() {
        $this->db->select('spm.*, pm.judul, m.nama as nama_mahasiswa, m.nim, 
                          d.nama as nama_pembimbing, spm.tanggal_review_pembimbing as tanggal_pengajuan');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'review_kaprodi');
        $this->db->where('spm.status_pembimbing', 'approved');
        $this->db->order_by('spm.tanggal_review_pembimbing', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get statistik untuk dashboard
     */
    private function _get_statistics() {
        $stats = [];
        
        // Count pending reviews
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'review_kaprodi');
        $this->db->where('spm.status_pembimbing', 'approved');
        $stats['pending_review'] = $this->db->count_all_results();
        
        // Count approved this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->where('MONTH(spm.tanggal_review_kaprodi)', date('m'));
        $this->db->where('YEAR(spm.tanggal_review_kaprodi)', date('Y'));
        $stats['approved_month'] = $this->db->count_all_results();
        
        // Count rejected this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'rejected');
        $this->db->where('MONTH(spm.tanggal_review_kaprodi)', date('m'));
        $this->db->where('YEAR(spm.tanggal_review_kaprodi)', date('Y'));
        $stats['rejected_month'] = $this->db->count_all_results();
        
        // Count scheduled this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'scheduled');
        $this->db->where('MONTH(spm.tanggal_seminar)', date('m'));
        $this->db->where('YEAR(spm.tanggal_seminar)', date('Y'));
        $stats['scheduled_month'] = $this->db->count_all_results();
        
        return $stats;
    }
    
    /**
     * Get recent approved seminars
     */
    private function _get_recent_approved() {
        $this->db->select('spm.*, pm.judul, m.nama as nama_mahasiswa, m.nim');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->order_by('spm.tanggal_review_kaprodi', 'DESC');
        $this->db->limit(5);
        
        return $this->db->get()->result();
    }
    
    /**
     * Check authorization - mahasiswa harus dari prodi yang sama
     */
    private function _is_kaprodi_authorized($mahasiswa_id) {
        $mahasiswa = $this->db->select('prodi_id')->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        return $mahasiswa && $mahasiswa->prodi_id == $this->prodi_id;
    }
    
    /**
     * Get available dosen untuk penguji
     */
    private function _get_available_penguji() {
        $this->db->select('id, nama, nip');
        $this->db->from('dosen');
        $this->db->where_in('level', ['2', '4']); // Dosen biasa dan Kaprodi
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Handle upload file Turnitin
     */
    private function _handle_turnitin_upload() {
        if (empty($_FILES['file_turnitin']['name'])) {
            return null;
        }
        
        // Create upload directory if not exists
        $upload_path = './uploads/turnitin/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf',
            'max_size' => 5120, // 5MB
            'encrypt_name' => TRUE,
            'remove_spaces' => TRUE
        ];
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('file_turnitin')) {
            return $this->upload->data('file_name');
        } else {
            throw new Exception('Upload file gagal: ' . $this->upload->display_errors('', ''));
        }
    }
    
    // =================================================================
    // EMAIL NOTIFICATION METHODS
    // =================================================================
    
    /**
     * Send notifications setelah approval
     */
    private function _send_approval_notifications($seminar_id) {
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        if (!$seminar) return;
        
        try {
            // 1. Email ke mahasiswa
            $this->_send_email_mahasiswa_approved($seminar);
            
            // 2. Email ke dosen pembimbing
            $this->_send_email_pembimbing_approved($seminar);
            
            // 3. Email ke dosen penguji (tanpa konfirmasi sesuai workflow)
            $this->_send_email_penguji($seminar);
            
            // 4. Email ke staf
            $this->_send_email_staf($seminar);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending approval notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Send notifications setelah rejection
     */
    private function _send_rejection_notifications($seminar, $plagiarism_percentage, $komentar) {
        try {
            // Email ke mahasiswa
            $this->_send_email_mahasiswa_rejected($seminar, $plagiarism_percentage, $komentar);
            
            // Email ke dosen pembimbing
            $this->_send_email_pembimbing_rejected($seminar, $plagiarism_percentage, $komentar);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending rejection notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Email ke mahasiswa - approved
     */
    private function _send_email_mahasiswa_approved($seminar) {
        $tanggal_indo = $this->_format_tanggal_indo($seminar->tanggal_seminar);
        $jam_seminar = date('H:i', strtotime($seminar->jam_seminar));
        
        $subject = 'Seminar Proposal Disetujui - STK Santo Yakobus';
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>🎉 Seminar Proposal Disetujui!</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Selamat! Pengajuan seminar proposal Anda telah <strong>disetujui</strong> oleh Kaprodi dan telah dijadwalkan.</p>
                
                <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #155724; margin: 0 0 10px 0;'>📅 Jadwal Seminar Proposal:</h4>
                    <ul style='color: #155724; margin: 0;'>
                        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
                        <li><strong>Waktu:</strong> {$jam_seminar} WIT</li>
                        <li><strong>Tempat:</strong> {$seminar->tempat_seminar}</li>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                    </ul>
                </div>
                
                <div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #004085; margin: 0 0 10px 0;'>👥 Tim Penguji:</h4>
                    <ul style='color: #004085; margin: 0;'>
                        <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
                        <li><strong>Penguji 1:</strong> {$seminar->nama_penguji1}</li>
                        <li><strong>Penguji 2:</strong> {$seminar->nama_penguji2}</li>
                    </ul>
                </div>
                
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Pastikan Anda hadir tepat waktu</li>
                    <li>Siapkan presentasi dan dokumen pendukung</li>
                    <li>Koordinasi dengan staf untuk persiapan teknis</li>
                </ol>
                
                <p>Semoga sukses untuk seminar proposal Anda!</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email ke mahasiswa - rejected
     */
    private function _send_email_mahasiswa_rejected($seminar, $plagiarism_percentage, $komentar) {
        $subject = 'Pengajuan Seminar Proposal Ditolak - STK Santo Yakobus';
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>❌ Pengajuan Seminar Proposal Ditolak</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Pengajuan seminar proposal Anda telah <strong>ditolak</strong> oleh Kaprodi dengan alasan berikut:</p>
                
                <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                    <h4 style='color: #721c24; margin: 0 0 10px 0;'>📊 Hasil Validasi Plagiarisme:</h4>
                    <ul style='color: #721c24; margin: 0;'>
                        <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}%</li>
                        <li><strong>Batas Maksimal:</strong> 30%</li>
                        <li><strong>Status:</strong> Melebihi batas yang diizinkan</li>
                    </ul>
                </div>
                
                " . (!empty($komentar) ? "
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Catatan Kaprodi:</h4>
                    <p style='color: #856404; margin: 0; font-style: italic;'>{$komentar}</p>
                </div>
                " : "") . "
                
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Perbaiki proposal untuk mengurangi tingkat plagiarisme</li>
                    <li>Konsultasikan dengan dosen pembimbing</li>
                    <li>Ajukan kembali setelah perbaikan</li>
                </ol>
                
                <p>Silakan hubungi dosen pembimbing untuk bimbingan lebih lanjut.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email ke dosen pembimbing - approved
     */
    private function _send_email_pembimbing_approved($seminar) {
        $tanggal_indo = $this->_format_tanggal_indo($seminar->tanggal_seminar);
        $jam_seminar = date('H:i', strtotime($seminar->jam_seminar));
        
        $subject = 'Seminar Proposal Mahasiswa Bimbingan Disetujui - STK Santo Yakobus';
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>📋 Seminar Proposal Mahasiswa Disetujui</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                <p>Pengajuan seminar proposal mahasiswa bimbingan Anda telah <strong>disetujui</strong> oleh Kaprodi dan telah dijadwalkan.</p>
                
                <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #0056b3; margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                    <ul style='color: #0056b3; margin: 0;'>
                        <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                        <li><strong>NIM:</strong> {$seminar->nim}</li>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                    </ul>
                </div>
                
                <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #155724; margin: 0 0 10px 0;'>📅 Jadwal Seminar:</h4>
                    <ul style='color: #155724; margin: 0;'>
                        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
                        <li><strong>Waktu:</strong> {$jam_seminar} WIT</li>
                        <li><strong>Tempat:</strong> {$seminar->tempat_seminar}</li>
                    </ul>
                </div>
                
                <p>Mohon kehadiran Bapak/Ibu sebagai pembimbing dalam seminar proposal tersebut.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_pembimbing);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email ke dosen pembimbing - rejected
     */
    private function _send_email_pembimbing_rejected($seminar, $plagiarism_percentage, $komentar) {
        $subject = 'Pengajuan Seminar Proposal Mahasiswa Ditolak - STK Santo Yakobus';
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>⚠️ Seminar Proposal Mahasiswa Ditolak</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                <p>Pengajuan seminar proposal mahasiswa bimbingan Anda telah <strong>ditolak</strong> oleh Kaprodi.</p>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                    <ul style='color: #856404; margin: 0;'>
                        <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                        <li><strong>NIM:</strong> {$seminar->nim}</li>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                    </ul>
                </div>
                
                <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #721c24; margin: 0 0 10px 0;'>📊 Alasan Penolakan:</h4>
                    <ul style='color: #721c24; margin: 0;'>
                        <li><strong>Plagiarisme:</strong> {$plagiarism_percentage}% (melebihi batas 30%)</li>
                        " . (!empty($komentar) ? "<li><strong>Catatan:</strong> {$komentar}</li>" : "") . "
                    </ul>
                </div>
                
                <p>Mohon bimbingan lebih lanjut kepada mahasiswa untuk perbaikan proposal.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_pembimbing);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email ke dosen penguji
     */
    private function _send_email_penguji($seminar) {
        $tanggal_indo = $this->_format_tanggal_indo($seminar->tanggal_seminar);
        $jam_seminar = date('H:i', strtotime($seminar->jam_seminar));
        
        // Email ke penguji 1
        if (!empty($seminar->dosen_penguji1_id)) {
            $penguji1 = $this->db->get_where('dosen', ['id' => $seminar->dosen_penguji1_id])->row();
            if ($penguji1) {
                $subject = 'Penunjukan Sebagai Penguji Seminar Proposal - STK Santo Yakobus';
                $message = $this->_get_penguji_email_template($seminar, $penguji1->nama, '1', $tanggal_indo, $jam_seminar);
                
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($penguji1->email);
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
            }
        }
        
        // Email ke penguji 2
        if (!empty($seminar->dosen_penguji2_id)) {
            $penguji2 = $this->db->get_where('dosen', ['id' => $seminar->dosen_penguji2_id])->row();
            if ($penguji2) {
                $subject = 'Penunjukan Sebagai Penguji Seminar Proposal - STK Santo Yakobus';
                $message = $this->_get_penguji_email_template($seminar, $penguji2->nama, '2', $tanggal_indo, $jam_seminar);
                
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($penguji2->email);
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
            }
        }
    }
    
    /**
     * Email ke staf
     */
    private function _send_email_staf($seminar) {
        $tanggal_indo = $this->_format_tanggal_indo($seminar->tanggal_seminar);
        $jam_seminar = date('H:i', strtotime($seminar->jam_seminar));
        
        // Get staf emails (level 3)
        $staf_list = $this->db->select('email, nama')->get_where('dosen', ['level' => '3'])->result();
        
        foreach ($staf_list as $staf) {
            $subject = 'Seminar Proposal Dijadwalkan - Persiapan Administrasi - STK Santo Yakobus';
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #6f42c1 0%, #563d7c 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📋 Seminar Proposal Dijadwalkan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$staf->nama}</strong>,</p>
                    <p>Seminar proposal telah dijadwalkan dan memerlukan persiapan administrasi.</p>
                    
                    <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #0056b3; margin: 0 0 10px 0;'>📋 Detail Seminar:</h4>
                        <ul style='color: #0056b3; margin: 0;'>
                            <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
                            <li><strong>Waktu:</strong> {$jam_seminar} WIT</li>
                            <li><strong>Tempat:</strong> {$seminar->tempat_seminar}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Tugas Administrasi:</h4>
                        <ol style='color: #155724; margin: 0; padding-left: 20px;'>
                            <li>Siapkan ruang seminar</li>
                            <li>Koordinasi dengan tim penguji</li>
                            <li>Siapkan dokumen berita acara</li>
                            <li>Koordinasi teknis dengan mahasiswa</li>
                        </ol>
                    </div>
                    
                    <p>Terima kasih atas dukungan administrasinya.</p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($staf->email);
            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
        }
    }
    
    /**
     * Template email untuk penguji
     */
    private function _get_penguji_email_template($seminar, $nama_penguji, $urutan, $tanggal_indo, $jam_seminar) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #007bff 0%, #6610f2 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>👨‍🏫 Penunjukan Sebagai Penguji</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Yth. <strong>{$nama_penguji}</strong>,</p>
                <p>Dengan hormat, Anda telah ditunjuk sebagai <strong>Penguji {$urutan}</strong> pada Seminar Proposal mahasiswa berikut:</p>
                
                <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #0056b3; margin: 0 0 10px 0;'>📋 Detail Seminar:</h4>
                    <ul style='color: #0056b3; margin: 0;'>
                        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                        <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
                        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
                        <li><strong>Waktu:</strong> {$jam_seminar} WIT</li>
                        <li><strong>Tempat:</strong> {$seminar->tempat_seminar}</li>
                    </ul>
                </div>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0;'>ℹ️ Informasi Penting:</h4>
                    <p style='color: #856404; margin: 0;'>
                        Sesuai kebijakan STK Santo Yakobus, penunjukan dosen penguji tidak memerlukan konfirmasi kesediaan. 
                        Mohon hadir sesuai jadwal yang telah ditetapkan.
                    </p>
                </div>
                
                <p>Terima kasih atas kesediaan Bapak/Ibu untuk berpartisipasi dalam kegiatan akademik ini.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
    }
    
    /**
     * Format tanggal ke bahasa Indonesia
     */
    private function _format_tanggal_indo($date) {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        $timestamp = strtotime($date);
        $hari_nama = $hari[date('w', $timestamp)];
        $tanggal = date('j', $timestamp);
        $bulan_nama = $bulan[date('n', $timestamp)];
        $tahun = date('Y', $timestamp);
        
        return "{$hari_nama}, {$tanggal} {$bulan_nama} {$tahun}";
    }
}