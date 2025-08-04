<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller untuk Dosen Pembimbing - ENHANCED WITH COMPLETE NOTIFICATIONS
 * 
 * Controller untuk mengelola seminar skripsi dari perspektif dosen pembimbing
 * ENHANCED: Ditambahkan implementasi notifikasi lengkap dan handling pengajuan ulang
 * 
 * STRUKTUR DATABASE YANG BENAR (dari screenshot):
 * - Tabel: seminar_skripsi_mahasiswa
 * - Ada: status_pembimbing, komentar_pembimbing, tanggal_review_pembimbing, reviewed_by_pembimbing
 * - Ada: status_kaprodi, komentar_kaprodi, tanggal_review_kaprodi, reviewed_by_kaprodi  
 * - Ada: tanggal_seminar, jam_seminar, tempat_seminar
 * - Ada: dosen_penguji1_id, dosen_penguji2_id, status_penguji1, status_penguji2
 * 
 * File: application/controllers/dosen/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     5.0 (Enhanced with Complete Notifications)
 */
class Seminar_skripsi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text']);
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar skripsi untuk dosen
     * STABLE - TIDAK DIUBAH
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view
        $data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review($dosen_id),
            'riwayat_rekomendasi' => $this->_get_riwayat_rekomendasi($dosen_id),
            'perlu_penilaian' => $this->_get_seminar_perlu_penilaian($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Pastikan semua data tidak null
        $data['pengajuan_review'] = $data['pengajuan_review'] ?: [];
        $data['riwayat_rekomendasi'] = $data['riwayat_rekomendasi'] ?: [];
        $data['perlu_penilaian'] = $data['perlu_penilaian'] ?: [];
        $data['stats'] = $data['stats'] ?: ['total' => 0, 'perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0];
        
        // Load view dengan template dosen
        $this->load->view('template/dosen', [
            'title' => 'Seminar Skripsi',
            'content' => $this->load->view('dosen/seminar_skripsi/index', $data, TRUE),
            'script' => $this->_get_index_script()
        ]);
    }

    /**
     * Detail pengajuan seminar skripsi
     * STABLE - TIDAK DIUBAH
     */
    public function detail($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        // Check eligibility untuk seminar skripsi
        $eligibility = $this->_check_eligibility($seminar->proposal_id);
        
        // Get jurnal bimbingan untuk referensi
        $jurnal_bimbingan = $this->_safe_get_jurnal_bimbingan($seminar->proposal_id);
        
        // Prepare data untuk view
        $data = [
            'seminar' => $seminar,
            'eligibility' => $eligibility,
            'jurnal_bimbingan' => $jurnal_bimbingan
        ];
        
        // Load view dengan template dosen
        $this->load->view('template/dosen', [
            'title' => 'Detail Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_skripsi/detail', $data, TRUE),
            'script' => $this->_get_detail_script()
        ]);
    }

    /**
     * View file skripsi
     * STABLE - TIDAK DIUBAH
     */
    public function view_file($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar || empty($seminar->file_skripsi)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        // Path file skripsi
        $file_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $seminar->file_skripsi;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server!');
            redirect('dosen/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        // Set proper headers for file viewing
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $content_type = $this->_get_content_type($file_extension);
        
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: inline; filename="' . basename($seminar->file_skripsi) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }
    
    /**
     * NEW: View/Download surat keterangan penelitian
     * TAMBAHKAN METHOD INI SETELAH view_file() METHOD
     */
    public function view_surat_penelitian($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar || empty($seminar->surat_keterangan_penelitian)) {
            $this->session->set_flashdata('error', 'File surat keterangan penelitian tidak ditemukan!');
            redirect('dosen/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        // Path file surat keterangan penelitian
        $file_path = FCPATH . 'uploads/seminar_skripsi/surat_files/' . $seminar->surat_keterangan_penelitian;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File surat keterangan penelitian tidak ditemukan di server!');
            redirect('dosen/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        // Set proper headers for file viewing
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $content_type = $this->_get_content_type($file_extension);
        
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: inline; filename="' . basename($seminar->surat_keterangan_penelitian) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }

    /**
     * Proses rekomendasi seminar skripsi (Setujui/Tolak)
     * ENHANCED: Ditambahkan handling untuk pengajuan ulang
     */
    public function rekomendasi() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $rekomendasi = $this->input->post('rekomendasi'); // 'approved' atau 'rejected'
        $komentar = trim($this->input->post('komentar_pembimbing'));
        
        // Validasi input
        if (empty($seminar_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/seminar_skripsi');
            return;
        }

        if ($rekomendasi == 'rejected' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan!');
            redirect('dosen/seminar_skripsi/detail/' . $seminar_id);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        
        // Validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        // Process rekomendasi dengan kolom yang benar
        try {
            if ($rekomendasi == 'approved') {
                // Jika disetujui, lanjut ke kaprodi
                $update_data = [
                    'status' => 'review_kaprodi',
                    'current_step' => 'kaprodi',
                    'status_pembimbing' => 'approved',
                    'komentar_pembimbing' => $komentar,
                    'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                    'reviewed_by_pembimbing' => $dosen_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $message = 'Pengajuan seminar skripsi berhasil disetujui dan diteruskan ke Kaprodi!';
            } else {
                // Jika ditolak, kembali ke mahasiswa
                $update_data = [
                    'status' => 'rejected',
                    'current_step' => 'mahasiswa',
                    'status_pembimbing' => 'rejected',
                    'komentar_pembimbing' => $komentar,
                    'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                    'reviewed_by_pembimbing' => $dosen_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $message = 'Pengajuan seminar skripsi ditolak dan dikembalikan ke mahasiswa!';
            }
            
            // Update tabel seminar_skripsi_mahasiswa
            $this->db->where('id', $seminar_id);
            $success = $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            if ($success) {
                $this->session->set_flashdata('success', $message);
                
                // ENHANCED: Send complete email notification
                $this->_send_email_notification($seminar, $rekomendasi, $komentar);
            } else {
                $this->session->set_flashdata('error', 'Gagal memproses rekomendasi!');
            }
        } catch (Exception $e) {
            log_message('error', 'Error processing rekomendasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memproses rekomendasi!');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    /**
     * NEW: Handle pengajuan ulang setelah ditolak dosen atau kaprodi
     */
    public function handle_resubmission($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Validasi ownership dan status
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        // Hanya bisa handle resubmission jika status rejected
        if (!in_array($seminar->status, ['rejected'])) {
            $this->session->set_flashdata('error', 'Seminar ini tidak dalam status yang bisa diajukan ulang!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        try {
            // Reset status untuk review ulang
            $update_data = [
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'status_pembimbing' => 'pending',
                'komentar_pembimbing' => null,
                'tanggal_review_pembimbing' => null,
                'reviewed_by_pembimbing' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $seminar_id);
            $success = $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            if ($success) {
                $this->session->set_flashdata('success', 'Pengajuan ulang berhasil diproses. Silakan review kembali!');
                
                // Send notification untuk pengajuan ulang
                $this->_send_resubmission_notification($seminar);
            } else {
                $this->session->set_flashdata('error', 'Gagal memproses pengajuan ulang!');
            }
        } catch (Exception $e) {
            log_message('error', 'Error handling resubmission: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memproses pengajuan ulang!');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    /**
     * Form penilaian seminar skripsi
     * STABLE - TIDAK DIUBAH
     */
    public function penilaian($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi
        $seminar = $this->_get_seminar_detail_for_penilaian($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau belum bisa dinilai!');
            redirect('dosen/seminar_skripsi');
            return;
        }
        
        // Handle form submission
        if ($this->input->method() === 'post') {
            $this->_process_penilaian($seminar_id, $dosen_id, $seminar);
            return;
        }
        
        // Get existing penilaian jika ada
        $existing_penilaian = $this->_get_existing_penilaian($seminar_id, $dosen_id);
        
        // Get info dosen penguji untuk ditampilkan
        $dosen_penguji1 = null;
        $dosen_penguji2 = null;
        if (!empty($seminar->dosen_penguji1_id)) {
            $dosen_penguji1 = $this->_get_dosen_by_id($seminar->dosen_penguji1_id);
        }
        if (!empty($seminar->dosen_penguji2_id)) {
            $dosen_penguji2 = $this->_get_dosen_by_id($seminar->dosen_penguji2_id);
        }
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian' => $existing_penilaian,
            'dosen_penguji1' => $dosen_penguji1,
            'dosen_penguji2' => $dosen_penguji2,
            'is_edit' => !empty($existing_penilaian)
        ];
        
        // Load view dengan template dosen
        $this->load->view('template/dosen', [
            'title' => 'Penilaian Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_skripsi/penilaian', $view_data, TRUE),
            'script' => $this->_get_penilaian_script()
        ]);
    }

    // =================================================================
    // PRIVATE HELPER METHODS - STABLE (TIDAK DIUBAH)
    // =================================================================

    /**
     * Get pengajuan yang perlu review oleh dosen
     * ENHANCED: Tambah support untuk resubmission
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            
            // ENHANCED: Support untuk pengajuan ulang
            $this->db->where_in('ssm.status', ['submitted', 'resubmitted']);
            $this->db->where('ssm.current_step', 'pembimbing');
            $this->db->order_by('ssm.created_at', 'DESC');
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan perlu review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat rekomendasi yang sudah diberikan dosen
     * STABLE - TIDAK DIUBAH
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.reviewed_by_pembimbing', $dosen_id);
            $this->db->order_by('ssm.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(5);
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get seminar yang perlu penilaian
     * STABLE - TIDAK DIUBAH
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                ps.status_penilaian
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->join('penilaian_seminar_skripsi ps', 'ps.seminar_skripsi_id = ssm.id AND ps.role_penilai = "dosen_pembimbing" AND ps.dinilai_oleh = ' . $dosen_id, 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            
            $this->db->where_in('ssm.status', ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled']);
            $this->db->where('(ps.status_penilaian IS NULL OR ps.status_penilaian = "draft")');
            
            $this->db->order_by('ssm.created_at', 'ASC');
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar perlu penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics untuk dashboard
     * STABLE - TIDAK DIUBAH
     */
    private function _get_statistics($dosen_id) {
        try {
            $stats = [
                'total' => 0,
                'perlu_review' => 0,
                'disetujui' => 0,
                'ditolak' => 0
            ];

            // Total bimbingan skripsi
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $total = $this->db->get()->row();
            $stats['total'] = $total ? (int)$total->total : 0;

            // Perlu review
            $this->db->select('COUNT(*) as perlu_review');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('ssm.status', ['submitted', 'resubmitted']);
            $this->db->where('ssm.current_step', 'pembimbing');
            $perlu_review = $this->db->get()->row();
            $stats['perlu_review'] = $perlu_review ? (int)$perlu_review->perlu_review : 0;

            // Disetujui
            $this->db->select('COUNT(*) as disetujui');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status_pembimbing', 'approved');
            $disetujui = $this->db->get()->row();
            $stats['disetujui'] = $disetujui ? (int)$disetujui->disetujui : 0;

            // Ditolak
            $this->db->select('COUNT(*) as ditolak');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status_pembimbing', 'rejected');
            $ditolak = $this->db->get()->row();
            $stats['ditolak'] = $ditolak ? (int)$ditolak->ditolak : 0;

            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return ['total' => 0, 'perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0];
        }
    }

    /**
     * ENHANCED: Get seminar detail dengan judul original dari proposal
     * REPLACE METHOD _get_seminar_detail() YANG ADA DENGAN INI
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul as judul_proposal_original, pm.dosen_id as pembimbing_id,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            
            $result = $this->db->get()->row();
            
            // ENHANCEMENT: Tambahkan logic untuk menentukan judul yang digunakan
            if ($result) {
                // Jika ada judul_skripsi baru, gunakan itu. Jika tidak, gunakan judul proposal original
                $result->judul_current = !empty($result->judul_skripsi) ? $result->judul_skripsi : $result->judul_proposal_original;
                $result->is_judul_changed = !empty($result->judul_skripsi) && ($result->judul_skripsi !== $result->judul_proposal_original);
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Get detail seminar untuk penilaian
     * STABLE - TIDAK DIUBAH
     */
    private function _get_seminar_detail_for_penilaian($seminar_id, $dosen_id) {
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            return null;
        }
        
        if ($seminar->status == 'draft') {
            return null;
        }
        
        return $seminar;
    }

    /**
     * Check eligibility untuk seminar skripsi
     * STABLE - TIDAK DIUBAH
     */
    private function _check_eligibility($proposal_id) {
        $eligibility = [
            'can_proceed' => false,
            'seminar_proposal_completed' => false,
            'penelitian_completed' => false,
            'issues' => []
        ];
        
        try {
            $this->db->select('status');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $seminar_proposal = $this->db->get()->row();
            
            $eligibility['seminar_proposal_completed'] = !empty($seminar_proposal);
            
            if (!$eligibility['seminar_proposal_completed']) {
                $eligibility['issues'][] = 'Seminar proposal belum selesai';
            }
            
            $eligibility['penelitian_completed'] = true;
            
            $eligibility['can_proceed'] = $eligibility['seminar_proposal_completed'] && 
                                        $eligibility['penelitian_completed'];
                                        
        } catch (Exception $e) {
            log_message('error', 'Error checking eligibility: ' . $e->getMessage());
            $eligibility['issues'][] = 'Error checking eligibility';
        }
        
        return $eligibility;
    }

    /**
     * Safe get jurnal bimbingan
     * STABLE - TIDAK DIUBAH
     */
    private function _safe_get_jurnal_bimbingan($proposal_id) {
        try {
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(5);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal bimbingan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get existing penilaian
     * STABLE - TIDAK DIUBAH
     */
    private function _get_existing_penilaian($seminar_id, $dosen_id) {
        try {
            $this->db->select('*');
            $this->db->from('penilaian_seminar_skripsi');
            $this->db->where('seminar_skripsi_id', $seminar_id);
            $this->db->where('dinilai_oleh', $dosen_id);
            $this->db->where('role_penilai', 'dosen_pembimbing');
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting existing penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get dosen by ID
     * STABLE - TIDAK DIUBAH
     */
    private function _get_dosen_by_id($dosen_id) {
        try {
            $this->db->select('id, nama, email');
            $this->db->from('dosen');
            $this->db->where('id', $dosen_id);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ENHANCED: Process penilaian dengan notifikasi mahasiswa setelah publish
     * REPLACE METHOD _process_penilaian() DENGAN VERSI INI
     */
    private function _process_penilaian($seminar_id, $dosen_id, $seminar) {
        $action = $this->input->post('action');
        
        $required_fields = [
            'nilai_pembimbing',
            'rekomendasi'
        ];
        
        foreach ($required_fields as $field) {
            if (empty($this->input->post($field))) {
                $this->session->set_flashdata('error', 'Field yang wajib diisi belum lengkap!');
                redirect('dosen/seminar_skripsi/penilaian/' . $seminar_id);
                return;
            }
        }
        
        if ($action == 'publish') {
            $additional_required = ['nilai_penguji1', 'nilai_penguji2'];
            foreach ($additional_required as $field) {
                if (empty($this->input->post($field))) {
                    $this->session->set_flashdata('error', 'Untuk publikasi, semua nilai harus diisi!');
                    redirect('dosen/seminar_skripsi/penilaian/' . $seminar_id);
                    return;
                }
            }
        }
        
        $nilai_penguji1 = (float)$this->input->post('nilai_penguji1');
        $nilai_penguji2 = (float)$this->input->post('nilai_penguji2');
        $nilai_pembimbing = (float)$this->input->post('nilai_pembimbing');
        
        $total_nilai = 0;
        $count_nilai = 0;
        
        if ($nilai_penguji1 > 0) {
            $total_nilai += $nilai_penguji1;
            $count_nilai++;
        }
        if ($nilai_penguji2 > 0) {
            $total_nilai += $nilai_penguji2;
            $count_nilai++;
        }
        if ($nilai_pembimbing > 0) {
            $total_nilai += $nilai_pembimbing;
            $count_nilai++;
        }
        
        $nilai_akhir = $count_nilai > 0 ? $total_nilai / $count_nilai : 0;
        
        $penilaian_data = [
            'seminar_skripsi_id' => $seminar_id,
            'mahasiswa_id' => $seminar->mahasiswa_id,
            'proposal_id' => $seminar->proposal_id,
            'catatan_pendahuluan' => $this->input->post('catatan_pendahuluan'),
            'catatan_tinjauan_pustaka' => $this->input->post('catatan_tinjauan_pustaka'),
            'catatan_metodologi' => $this->input->post('catatan_metodologi'),
            'catatan_hasil_pembahasan' => $this->input->post('catatan_hasil_pembahasan'),
            'catatan_kesimpulan' => $this->input->post('catatan_kesimpulan'),
            'catatan_umum' => $this->input->post('catatan_umum'),
            'nilai_penguji1' => $nilai_penguji1,
            'nilai_penguji2' => $nilai_penguji2,
            'nilai_pembimbing' => $nilai_pembimbing,
            'nilai_akhir' => round($nilai_akhir, 2),
            'nilai_huruf' => $this->_convert_to_letter_grade($nilai_akhir),
            'rekomendasi' => $this->input->post('rekomendasi'),
            'keterangan_rekomendasi' => $this->input->post('keterangan_rekomendasi'),
            'dinilai_oleh' => $dosen_id,
            'role_penilai' => 'dosen_pembimbing',
            'status_penilaian' => $action == 'publish' ? 'published' : 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($action == 'publish') {
            $penilaian_data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $existing = $this->_get_existing_penilaian($seminar_id, $dosen_id);
        
        try {
            $this->db->trans_start();
            
            if ($existing) {
                $this->db->where('id', $existing->id);
                $success = $this->db->update('penilaian_seminar_skripsi', $penilaian_data);
            } else {
                $penilaian_data['created_at'] = date('Y-m-d H:i:s');
                $success = $this->db->insert('penilaian_seminar_skripsi', $penilaian_data);
            }
            
            if ($action == 'publish' && $success) {
                // Update status seminar skripsi ke completed
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_skripsi_mahasiswa', [
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update workflow status proposal ke publikasi
                $this->db->where('id', $seminar->proposal_id);
                $this->db->update('proposal_mahasiswa', [
                    'workflow_status' => 'publikasi',
                    'status_seminar_skripsi' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // ENHANCEMENT: Send email notification ke mahasiswa saat penilaian dipublikasi
                $this->_send_penilaian_published_notification($seminar, $penilaian_data);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan penilaian!');
            } else {
                if ($action == 'publish') {
                    $this->session->set_flashdata('success', 'Penilaian berhasil dipublikasikan! Mahasiswa dapat melihat hasil penilaian dan melanjutkan ke tahap publikasi.');
                } else {
                    $this->session->set_flashdata('success', 'Penilaian berhasil disimpan sebagai draft!');
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error saving penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan penilaian!');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    // =================================================================
    // NEW ENHANCED EMAIL NOTIFICATION METHODS
    // =================================================================

    /**
     * ENHANCED: Send complete email notification
     */
    private function _send_email_notification($seminar, $rekomendasi, $komentar) {
        try {
            if ($rekomendasi == 'approved') {
                // Kirim notifikasi ke mahasiswa bahwa disetujui dosen
                $this->_kirim_email_disetujui_mahasiswa($seminar);
                
                // Kirim notifikasi ke kaprodi untuk review selanjutnya
                $this->_kirim_email_disetujui_kaprodi($seminar);
                
                log_message('info', "Email approval sent: Seminar skripsi {$seminar->nama_mahasiswa} - approved");
            } else {
                // Kirim notifikasi ke mahasiswa bahwa ditolak
                $this->_kirim_email_ditolak_mahasiswa($seminar, $komentar);
                
                log_message('info', "Email rejection sent: Seminar skripsi {$seminar->nama_mahasiswa} - rejected");
            }
        } catch (Exception $e) {
            log_message('error', 'Error sending email notification: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Kirim email ke mahasiswa saat disetujui dosen
     */
    private function _kirim_email_disetujui_mahasiswa($seminar) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('✅ Seminar Skripsi Disetujui Pembimbing - ' . $seminar->nama_mahasiswa);
            
            $dosen_pembimbing = $this->_get_dosen_by_id($seminar->pembimbing_id);
            $nama_pembimbing = $dosen_pembimbing ? $dosen_pembimbing->nama : 'Dosen Pembimbing';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Seminar Skripsi Disetujui Pembimbing</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Selamat! Pengajuan seminar skripsi Anda telah <strong>DISETUJUI</strong> oleh dosen pembimbing dan diteruskan ke Ketua Program Studi untuk validasi selanjutnya.</p>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>📚 Detail Seminar:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Pembimbing:</strong> {$nama_pembimbing}</li>
                            <li><strong>Status:</strong> Menunggu validasi Kaprodi</li>
                            <li><strong>Tanggal Disetujui:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #cce7ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ol style='color: #004085; margin: 0;'>
                            <li>Kaprodi akan melakukan validasi plagiarisme</li>
                            <li>Jika disetujui, akan dilakukan penjadwalan seminar</li>
                            <li>Anda akan mendapat notifikasi hasil validasi via email</li>
                            <li>Pantau status melalui dashboard mahasiswa</li>
                        </ol>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem. Untuk informasi lebih lanjut, silakan hubungi bagian akademik atau dosen pembimbing Anda.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send approval email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error sending approval email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Kirim email ke kaprodi saat disetujui dosen
     */
    private function _kirim_email_disetujui_kaprodi($seminar) {
        try {
            // Get kaprodi email
            $kaprodi = $this->_get_kaprodi_data();
            if (!$kaprodi) {
                log_message('warning', 'Kaprodi data not found for notification');
                return false;
            }
            
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($kaprodi->email);
            $this->email->subject('🔔 Seminar Skripsi Perlu Validasi - ' . $seminar->nama_mahasiswa);
            
            $dosen_pembimbing = $this->_get_dosen_by_id($seminar->pembimbing_id);
            $nama_pembimbing = $dosen_pembimbing ? $dosen_pembimbing->nama : 'Dosen Pembimbing';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #007bff 0%, #6610f2 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🔔 Seminar Skripsi Perlu Validasi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>Ketua Program Studi</strong>,</p>
                    
                    <p>Terdapat pengajuan seminar skripsi yang telah disetujui dosen pembimbing dan memerlukan validasi Anda.</p>
                    
                    <div style='background-color: #cce7ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                        <ul style='color: #004085; margin: 0;'>
                            <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$seminar->nim}</li>
                            <li><strong>Judul Skripsi:</strong> {$seminar->judul}</li>
                            <li><strong>Dosen Pembimbing:</strong> {$nama_pembimbing}</li>
                            <li><strong>Tanggal Disetujui:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📋 Tindakan yang Diperlukan:</h4>
                        <ol style='color: #856404; margin: 0;'>
                            <li>Login ke sistem SIM Tugas Akhir</li>
                            <li>Cek file skripsi yang telah diupload</li>
                            <li>Lakukan validasi plagiarisme (maksimal 30%)</li>
                            <li>Setujui atau tolak pengajuan dengan komentar</li>
                            <li>Jika disetujui, lakukan penjadwalan seminar</li>
                        </ol>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('kaprodi/seminar_skripsi') . "' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            🔍 Buka Sistem SIM-TA
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem. Mohon segera melakukan validasi untuk kelancaran proses akademik mahasiswa.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send notification email to kaprodi: ' . $this->email->print_debugger());
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error sending notification email to kaprodi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Kirim email ke mahasiswa saat ditolak dosen
     */
    private function _kirim_email_ditolak_mahasiswa($seminar, $komentar) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('⚠️ Seminar Skripsi Perlu Perbaikan - ' . $seminar->nama_mahasiswa);
            
            $dosen_pembimbing = $this->_get_dosen_by_id($seminar->pembimbing_id);
            $nama_pembimbing = $dosen_pembimbing ? $dosen_pembimbing->nama : 'Dosen Pembimbing';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>⚠️ Seminar Skripsi Perlu Perbaikan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Mohon maaf, pengajuan seminar skripsi Anda <strong>PERLU DIPERBAIKI</strong> berdasarkan review dari dosen pembimbing.</p>
                    
                    <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>📚 Detail Seminar:</h4>
                        <ul style='color: #721c24; margin: 0;'>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Pembimbing:</strong> {$nama_pembimbing}</li>
                            <li><strong>Tanggal Review:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>";
            
            if (!empty($komentar)) {
                $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Komentar Dosen Pembimbing:</h4>
                        <p style='color: #856404; margin: 0; background-color: white; padding: 10px; border-radius: 3px;'>" . nl2br(htmlspecialchars($komentar)) . "</p>
                    </div>";
            }
            
            $message .= "
                    <div style='background-color: #cce7ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ol style='color: #004085; margin: 0;'>
                            <li>Perbaiki dokumen skripsi sesuai komentar dosen</li>
                            <li>Konsultasi dengan dosen pembimbing jika diperlukan</li>
                            <li>Upload ulang file skripsi yang sudah diperbaiki</li>
                            <li>Ajukan ulang seminar skripsi melalui sistem</li>
                        </ol>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/seminar_skripsi') . "' style='background-color: #dc3545; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            🔄 Ajukan Ulang Seminar
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem. Untuk konsultasi lebih lanjut, silakan hubungi dosen pembimbing Anda.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send rejection email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error sending rejection email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Send notification untuk pengajuan ulang
     */
    private function _send_resubmission_notification($seminar) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('🔄 Pengajuan Ulang Seminar Skripsi Diterima - ' . $seminar->nama_mahasiswa);
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #17a2b8 0%, #28a745 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🔄 Pengajuan Ulang Diterima</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Pengajuan ulang seminar skripsi Anda telah diterima dan siap untuk direview kembali oleh dosen pembimbing.</p>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #17a2b8;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📚 Detail Pengajuan:</h4>
                        <ul style='color: #0c5460; margin: 0;'>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Status:</strong> Menunggu review pembimbing</li>
                            <li><strong>Tanggal Pengajuan Ulang:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <p>Silakan pantau status melalui dashboard mahasiswa. Anda akan mendapat notifikasi hasil review via email.</p>
                </div>
            </div>";
            
            $this->email->message($message);
            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Error sending resubmission notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Send notification ke mahasiswa ketika penilaian dipublikasi
     */
    private function _send_penilaian_published_notification($seminar, $penilaian_data) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('🎓 Hasil Penilaian Seminar Skripsi Telah Dipublikasi - ' . $seminar->nama_mahasiswa);
            
            // Get dosen pembimbing info
            $dosen_pembimbing = $this->_get_dosen_by_id($seminar->pembimbing_id);
            $nama_pembimbing = $dosen_pembimbing ? $dosen_pembimbing->nama : 'Dosen Pembimbing';
            
            // Color untuk nilai
            $nilai_color = $penilaian_data['nilai_akhir'] >= 80 ? '#28a745' : 
                          ($penilaian_data['nilai_akhir'] >= 70 ? '#ffc107' : '#dc3545');
            
            // Color untuk rekomendasi
            $rekomendasi_text = '';
            $rekomendasi_color = '';
            switch($penilaian_data['rekomendasi']) {
                case 'diterima_tanpa_revisi':
                    $rekomendasi_text = 'Diterima tanpa revisi';
                    $rekomendasi_color = '#28a745';
                    break;
                case 'revisi_minor':
                    $rekomendasi_text = 'Diterima dengan revisi minor';
                    $rekomendasi_color = '#17a2b8';
                    break;
                case 'revisi_mayor':
                    $rekomendasi_text = 'Diterima dengan revisi mayor';
                    $rekomendasi_color = '#ffc107';
                    break;
                case 'ditolak':
                    $rekomendasi_text = 'Ditolak';
                    $rekomendasi_color = '#dc3545';
                    break;
            }
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🎓 Hasil Penilaian Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Hasil penilaian seminar skripsi Anda telah <strong>DIPUBLIKASI</strong> dan dapat dilihat melalui sistem.</p>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #495057; margin: 0 0 10px 0;'>📚 Detail Seminar:</h4>
                        <ul style='color: #495057; margin: 0;'>
                            <li><strong>Judul:</strong> " . ($seminar->judul_current ?? $seminar->judul_proposal_original) . "</li>
                            <li><strong>Pembimbing:</strong> {$nama_pembimbing}</li>
                            <li><strong>Tanggal Publikasi:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid {$nilai_color};'>
                        <h4 style='color: {$nilai_color}; margin: 0 0 10px 0;'>📊 Hasil Penilaian:</h4>
                        <div style='display: flex; justify-content: space-between; align-items: center;'>
                            <div>
                                <strong style='font-size: 18px; color: {$nilai_color};'>Nilai Akhir: {$penilaian_data['nilai_akhir']} ({$penilaian_data['nilai_huruf']})</strong><br>
                                <span style='color: {$rekomendasi_color}; font-weight: bold;'>Rekomendasi: {$rekomendasi_text}</span>
                            </div>
                        </div>
                    </div>";
            
            // Tambahkan catatan jika ada
            $ada_catatan = false;
            $catatan_sections = [
                'catatan_pendahuluan' => 'Bab I: Pendahuluan',
                'catatan_tinjauan_pustaka' => 'Bab II: Tinjauan Pustaka',
                'catatan_metodologi' => 'Bab III: Metodologi',
                'catatan_hasil_pembahasan' => 'Bab IV: Hasil & Pembahasan',
                'catatan_kesimpulan' => 'Bab V: Kesimpulan',
                'catatan_umum' => 'Catatan Umum'
            ];
            
            foreach ($catatan_sections as $field => $label) {
                if (!empty($penilaian_data[$field])) {
                    $ada_catatan = true;
                    break;
                }
            }
            
            if ($ada_catatan) {
                $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 Terdapat Catatan Revisi</h4>
                        <p style='color: #856404; margin: 0;'>Silakan cek detail catatan untuk setiap bab melalui sistem SIM-TA.</p>
                    </div>";
            }
            
            $message .= "
                    <div style='background-color: #cce7ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ol style='color: #004085; margin: 0;'>
                            <li>Login ke sistem SIM Tugas Akhir</li>
                            <li>Lihat detail hasil penilaian dan catatan</li>
                            <li>Lakukan revisi sesuai catatan (jika ada)</li>
                            <li>Lanjutkan ke tahap Publikasi Tugas Akhir</li>
                        </ol>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/seminar_skripsi/view_penilaian/' . $seminar->id) . "' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            👁️ Lihat Hasil Penilaian
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem. Selamat atas pencapaian Anda!
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send penilaian published email: ' . $this->email->print_debugger());
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error sending penilaian published email: ' . $e->getMessage());
            return false;
        }
    }
    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get email configuration
     */
    private function _get_email_config() {
        return [
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
    }

    /**
     * Get kaprodi data
     */
    private function _get_kaprodi_data() {
        try {
            // OPTION 1: Cari kaprodi berdasarkan level '4' (sesuai struktur database)
            $this->db->select('d.id, d.nama, d.email, d.nip');
            $this->db->from('dosen d');
            $this->db->where('d.level', '4'); // Level 4 = Kaprodi berdasarkan view kaprodi_v
            $this->db->limit(1);
            
            $kaprodi = $this->db->get()->row();
            
            if ($kaprodi) {
                return $kaprodi;
            }
            
            // OPTION 2: Fallback - cari dari tabel prodi (jika ada relasi)
            $this->db->select('d.id, d.nama, d.email, d.nip');
            $this->db->from('prodi p');
            $this->db->join('dosen d', 'p.dosen_id = d.id');
            $this->db->where('d.level', '4');
            $this->db->limit(1);
            
            $kaprodi = $this->db->get()->row();
            
            if ($kaprodi) {
                return $kaprodi;
            }
            
            // OPTION 3: Last fallback - ambil dosen pertama dengan level 4
            $this->db->select('id, nama, email, nip');
            $this->db->from('dosen');
            $this->db->where('level', '4');
            $this->db->or_where('level', '1'); // Jika tidak ada level 4, coba level 1 (admin)
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi data: ' . $e->getMessage());
            
            // Emergency fallback - return dummy data untuk mencegah crash
            return (object) [
                'id' => 1,
                'nama' => 'Kaprodi',
                'email' => 'kaprodi@stkyakobus.ac.id',
                'nip' => '000000'
            ];
        }
    }

    /**
     * Convert nilai to letter grade
     */
    private function _convert_to_letter_grade($nilai) {
        if ($nilai >= 80) return 'A';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    /**
     * Get content type for file viewing
     * STABLE - TIDAK DIUBAH
     */
    private function _get_content_type($extension) {
        $types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain'
        ];
        
        return $types[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Get JavaScript for index page
     * STABLE - TIDAK DIUBAH
     */
    private function _get_index_script() {
        return "
        <script>
        $(document).ready(function() {
            if ($('#pengajuan-table').length) {
                $('#pengajuan-table').DataTable({
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                    }
                });
            }
            $('[data-toggle=\"tooltip\"]').tooltip();
        });
        </script>";
    }

    /**
     * Get JavaScript for detail page
     * STABLE - TIDAK DIUBAH
     */
    private function _get_detail_script() {
        return "
        <script>
        $(document).ready(function() {
            $('#rekomendasi-form').on('submit', function(e) {
                const rekomendasi = $('input[name=\"rekomendasi\"]:checked').val();
                const komentar = $('#komentar_pembimbing').val().trim();
                
                if (!rekomendasi) {
                    e.preventDefault();
                    alert('Pilih rekomendasi terlebih dahulu!');
                    return;
                }
                
                if (rekomendasi === 'rejected' && !komentar) {
                    e.preventDefault();
                    alert('Komentar wajib diisi untuk penolakan!');
                    $('#komentar_pembimbing').focus();
                    return;
                }
                
                return confirm('Yakin dengan rekomendasi ini?');
            });
            
            $('input[name=\"rekomendasi\"]').on('change', function() {
                if ($(this).val() === 'rejected') {
                    $('#komentar-section').show();
                    $('#komentar_pembimbing').prop('required', true);
                } else {
                    $('#komentar-section').hide();
                    $('#komentar_pembimbing').prop('required', false);
                }
            });
        });
        </script>";
    }

    /**
     * Get JavaScript for penilaian page
     * STABLE - TIDAK DIUBAH
     */
    private function _get_penilaian_script() {
        return "
        <script>
        $(document).ready(function() {
            $('.nilai-input').on('input', function() {
                calculateNilaiAkhir();
            });
            
            function calculateNilaiAkhir() {
                const penguji1 = parseFloat($('#nilai_penguji1').val()) || 0;
                const penguji2 = parseFloat($('#nilai_penguji2').val()) || 0;
                const pembimbing = parseFloat($('#nilai_pembimbing').val()) || 0;
                
                let total = 0;
                let count = 0;
                
                if (penguji1 > 0) { total += penguji1; count++; }
                if (penguji2 > 0) { total += penguji2; count++; }
                if (pembimbing > 0) { total += pembimbing; count++; }
                
                const average = count > 0 ? total / count : 0;
                
                $('#nilai_akhir_display').val(average.toFixed(2));
                $('#nilai_akhir').val(average.toFixed(2));
                
                const huruf = convertToLetterGrade(average);
                $('#nilai_huruf_display').text(huruf);
                $('#nilai_huruf').val(huruf);
            }
            
            function convertToLetterGrade(nilai) {
                if (nilai >= 80) return 'A';
                if (nilai >= 70) return 'B';
                if (nilai >= 60) return 'C';
                if (nilai >= 50) return 'D';
                return 'E';
            }
            
            $('#penilaian-form').on('submit', function(e) {
                const action = $('button[type=submit][clicked=true]').val();
                const pembimbing = $('#nilai_pembimbing').val();
                const rekomendasi = $('input[name=\"rekomendasi\"]:checked').val();
                
                if (!pembimbing || !rekomendasi) {
                    e.preventDefault();
                    alert('Nilai pembimbing dan rekomendasi wajib diisi!');
                    return;
                }
                
                if (action === 'publish') {
                    const penguji1 = $('#nilai_penguji1').val();
                    const penguji2 = $('#nilai_penguji2').val();
                    
                    if (!penguji1 || !penguji2) {
                        e.preventDefault();
                        alert('Untuk publikasi, semua nilai penguji harus diisi!');
                        return;
                    }
                    
                    if (!confirm('Yakin akan mempublikasikan penilaian ini? Status mahasiswa akan berubah ke tahap selanjutnya.')) {
                        e.preventDefault();
                        return;
                    }
                } else {
                    if (!confirm('Yakin menyimpan penilaian sebagai draft?')) {
                        e.preventDefault();
                        return;
                    }
                }
            });
            
            $('button[type=submit]').click(function() {
                $('button[type=submit]').removeAttr('clicked');
                $(this).attr('clicked', 'true');
            });
            
            calculateNilaiAkhir();
        });
        </script>";
    }
}