<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller untuk Dosen - FIXED VERSION
 * 
 * Controller untuk mengelola seminar proposal dari perspektif dosen pembimbing
 * Menggunakan template existing dan helper function approach untuk badge counter
 * 
 * Features:
 * 1. Dashboard dengan statistics dan overview
 * 2. Detail pengajuan dengan validasi syarat jurnal bimbingan
 * 3. Rekomendasi (setujui/tolak) dengan email notification
 * 4. Helper functions untuk badge counter dan utilities
 * 
 * File: application/controllers/dosen/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.1 (Fixed Version)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        
        // ✅ FIX: Load semua helper yang diperlukan
        $this->load->helper(['url', 'date', 'text', 'string']); // text helper untuk character_limiter()
        
        // Load model existing
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk dosen
     * Menggunakan template existing dosen.php
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view menggunakan tabel existing
        $view_data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review($dosen_id),
            'riwayat_rekomendasi' => $this->_get_riwayat_rekomendasi($dosen_id),
            'perlu_penilaian' => $this->_get_seminar_perlu_penilaian($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Seminar Proposal',
            'content' => $this->load->view('dosen/seminar_proposal/index', $view_data, TRUE),
            'script' => ''
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail pengajuan seminar proposal
     * Menggunakan template existing dosen.php
     */
    public function detail($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'jurnal_requirement' => $this->seminar_model->check_jurnal_requirement($seminar->proposal_id),
            'jurnal_bimbingan' => $this->_get_jurnal_bimbingan($seminar->proposal_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/detail', $view_data, TRUE),
            'script' => '' // JavaScript tambahan jika diperlukan
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Proses rekomendasi seminar proposal
     */
    public function rekomendasi() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $rekomendasi = $this->input->post('rekomendasi'); // 'approved' atau 'rejected'
        $komentar = trim($this->input->post('komentar_pembimbing'));
        
        // Validasi input
        if (empty($seminar_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/seminar_proposal');
            return;
        }

        if ($rekomendasi == 'rejected' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        
        // Validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Proses rekomendasi
        $this->db->trans_start();
        
        try {
            // Update status seminar proposal
            $update_data = [
                'status_pembimbing' => $rekomendasi,
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'reviewed_by_pembimbing' => $dosen_id,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($rekomendasi == 'approved') {
                $update_data['status'] = 'review_kaprodi';
                $update_data['current_step'] = 'kaprodi';
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan rekomendasi');
            }
            
            // Kirim notifikasi email
            $this->_kirim_notifikasi_rekomendasi($seminar, $rekomendasi, $komentar);
            
            $message = ($rekomendasi == 'approved') ? 
                'Rekomendasi berhasil diberikan! Pengajuan diteruskan ke Kaprodi.' : 
                'Pengajuan berhasil ditolak. Mahasiswa akan mendapat notifikasi untuk perbaikan.';
                
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error rekomendasi seminar: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('dosen/seminar_proposal');
    }

    /**
     * Input penilaian setelah seminar selesai (future development)
     */
    public function penilaian($seminar_id) {
        // Placeholder untuk fitur penilaian yang akan dikembangkan
        $this->session->set_flashdata('info', 'Fitur input penilaian akan dikembangkan pada tahap selanjutnya sesuai poin 8-12 dalam workflow.');
        redirect('dosen/seminar_proposal');
    }

    // =================================================================
    // HELPER FUNCTIONS - OPTION 2: CLEAN APPROACH
    // =================================================================

    /**
     * Get badge count untuk seminar proposal yang perlu review
     * Helper function untuk template badge counter
     * 
     * @param int $dosen_id
     * @return int
     */
    public function get_seminar_proposal_badge_count($dosen_id = null) {
        if (!$dosen_id) {
            $dosen_id = $this->session->userdata('id');
        }
        
        if (!$dosen_id) return 0;
        
        try {
            $this->db->select('COUNT(*) as count');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing']);
            $this->db->where('spm.status_pembimbing', 'pending');
            $result = $this->db->get()->row();
            return $result ? (int)$result->count : 0;
        } catch (Exception $e) {
            log_message('debug', 'Badge count error: ' . $e->getMessage());
            return 0;
        }
    }

    // =================================================================
    // PRIVATE METHODS
    // =================================================================

    /**
     * Get pengajuan yang perlu direkomendasi
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        $this->db->select('
            s.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            p.nama as nama_prodi
        ');
        $this->db->from('seminar s'); // Tabel existing: seminar
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('(s.persetujuan IS NULL OR s.persetujuan = "")'); // Belum ada persetujuan
        $this->db->order_by('s.id', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get riwayat rekomendasi yang sudah diberikan
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        $this->db->select('
            s.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            s.persetujuan as status_pembimbing
        ');
        $this->db->from('seminar s'); // Tabel existing
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('s.persetujuan IS NOT NULL AND s.persetujuan != ""'); // Sudah ada persetujuan
        $this->db->order_by('s.id', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar yang perlu penilaian (sudah terjadwal dan selesai)
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        $this->db->select('
            s.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            hs.status as hasil_status
        ');
        $this->db->from('seminar s'); // ✅ FIX: Tidak ada typo 'smp'
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('hasil_seminar hs', 's.id = hs.seminar_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('DATE(s.tanggal) <=', date('Y-m-d')); // Seminar sudah lewat
        $this->db->where('(hs.status IS NULL OR hs.status = "")'); // Belum ada hasil
        $this->db->order_by('s.tanggal', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get detail seminar dengan validasi ownership
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        $this->db->select('
            s.*,
            pm.judul,
            pm.ringkasan,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi
        ');
        $this->db->from('seminar s'); // Tabel existing
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('s.id', $seminar_id);
        $this->db->where('pm.dosen_id', $dosen_id); // Validasi ownership
        
        return $this->db->get()->row();
    }

    /**
     * Get jurnal bimbingan mahasiswa
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan'); // Tabel existing sudah benar
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1'); // Sudah divalidasi
        $this->db->order_by('tanggal_bimbingan', 'DESC');
        $this->db->limit(5); // 5 terakhir
        
        return $this->db->get()->result();
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        $stats = new stdClass();
        
        // Total pengajuan yang perlu review
        $this->db->from('seminar s');
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('(s.persetujuan IS NULL OR s.persetujuan = "")');
        $stats->perlu_review = $this->db->count_all_results();
        
        // Total yang sudah direkomendasi bulan ini
        $this->db->from('seminar s');
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('s.persetujuan IS NOT NULL AND s.persetujuan != ""');
        $this->db->where('MONTH(s.tanggal)', date('n')); // Bulan ini
        $this->db->where('YEAR(s.tanggal)', date('Y'));
        $stats->direkomendasi_bulan_ini = $this->db->count_all_results();
        
        // Total seminar terjadwal yang perlu penilaian
        $this->db->from('seminar s');
        $this->db->join('proposal_mahasiswa pm', 's.proposal_mahasiswa_id = pm.id');
        $this->db->join('hasil_seminar hs', 's.id = hs.seminar_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('DATE(s.tanggal) <=', date('Y-m-d'));
        $this->db->where('(hs.status IS NULL OR hs.status = "")');
        $stats->perlu_penilaian = $this->db->count_all_results();
        
        return $stats;
    }

    /**
     * Kirim notifikasi email rekomendasi
     */
    private function _kirim_notifikasi_rekomendasi($seminar, $rekomendasi, $komentar) {
        try {
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
            $dosen_nama = $this->session->userdata('nama');
            
            if ($rekomendasi == 'approved') {
                $this->_kirim_email_rekomendasi_disetujui($seminar, $dosen_nama);
            } else {
                $this->_kirim_email_rekomendasi_ditolak($seminar, $dosen_nama, $komentar);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email: ' . $e->getMessage());
        }
    }

    /**
     * Email untuk rekomendasi disetujui
     */
    private function _kirim_email_rekomendasi_disetujui($seminar, $dosen_nama) {
        // Email ke mahasiswa
        $subject_mhs = 'Seminar Proposal Direkomendasikan Pembimbing - STK Santo Yakobus';
        $message_mhs = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>✅ Seminar Proposal Direkomendasikan</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Seminar proposal Anda telah <strong>direkomendasikan</strong> oleh dosen pembimbing untuk dilanjutkan ke tahap review Kaprodi.</p>
                
                <div style='background-color: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4 style='color: #0056b3; margin: 0 0 10px 0;'>📋 Detail Pengajuan:</h4>
                    <ul style='color: #0056b3; margin: 0;'>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                        <li><strong>Pembimbing:</strong> {$dosen_nama}</li>
                        <li><strong>Status:</strong> Menunggu Review Kaprodi</li>
                    </ul>
                </div>
                
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Pengajuan akan direview oleh Kaprodi</li>
                    <li>Kaprodi akan melakukan validasi plagiarisme</li>
                    <li>Jika disetujui, seminar akan dijadwalkan</li>
                </ol>
                
                <p>Anda dapat memantau progress melalui sistem SIM-TA.</p>
                <p>Terima kasih.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject($subject_mhs);
        $this->email->message($message_mhs);
        $this->email->send();
        
        // Email ke kaprodi
        $kaprodi = $this->_get_kaprodi_by_prodi($seminar->nama_prodi);
        if ($kaprodi) {
            $subject_kaprodi = 'Pengajuan Seminar Proposal Perlu Review - STK Santo Yakobus';
            $message_kaprodi = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📋 Pengajuan Seminar Proposal Perlu Review</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. Kaprodi {$seminar->nama_prodi},</p>
                    <p>Ada pengajuan seminar proposal yang perlu review dan validasi.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>📚 Detail Mahasiswa:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$seminar->nim}</li>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Pembimbing:</strong> {$dosen_nama}</li>
                        </ul>
                    </div>
                    
                    <p><strong>Catatan:</strong> Dosen pembimbing telah memberikan rekomendasi untuk melanjutkan ke tahap seminar proposal.</p>
                    <p>Silakan login ke sistem untuk melakukan review dan validasi plagiarisme.</p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>
            ";
            
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($kaprodi->email);
            $this->email->subject($subject_kaprodi);
            $this->email->message($message_kaprodi);
            $this->email->send();
        }
    }

    /**
     * Email untuk rekomendasi ditolak
     */
    private function _kirim_email_rekomendasi_ditolak($seminar, $dosen_nama, $komentar) {
        $subject = 'Seminar Proposal Perlu Perbaikan - STK Santo Yakobus';
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>⚠️ Seminar Proposal Perlu Perbaikan</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Pengajuan seminar proposal Anda perlu diperbaiki sesuai catatan dari dosen pembimbing.</p>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 Catatan Pembimbing:</h4>
                    <p style='font-style: italic; color: #856404; margin: 0;'>\"{$komentar}\"</p>
                </div>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #6c757d;'>
                    <h4 style='color: #495057; margin: 0 0 10px 0;'>📋 Detail Pengajuan:</h4>
                    <ul style='color: #495057; margin: 0;'>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                        <li><strong>Pembimbing:</strong> {$dosen_nama}</li>
                        <li><strong>Status:</strong> Perlu Perbaikan</li>
                    </ul>
                </div>
                
                <p><strong>Langkah Selanjutnya:</strong></p>
                <ol>
                    <li>Perbaiki proposal sesuai catatan pembimbing</li>
                    <li>Konsultasikan kembali dengan dosen pembimbing</li>
                    <li>Ajukan ulang setelah perbaikan selesai</li>
                </ol>
                
                <p>Silakan hubungi dosen pembimbing untuk konsultasi lebih lanjut.</p>
                <p>Terima kasih.</p>
            </div>
            
            <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Get kaprodi berdasarkan nama prodi
     */
    private function _get_kaprodi_by_prodi($nama_prodi) {
        $this->db->select('d.nama, d.email');
        $this->db->from('dosen d');
        $this->db->join('prodi p', 'd.id = p.dosen_id');
        $this->db->where('p.nama', $nama_prodi);
        $this->db->where('d.level', '4'); // Level kaprodi
        
        return $this->db->get()->row();
    }
}