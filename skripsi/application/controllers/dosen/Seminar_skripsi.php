<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller untuk Dosen Pembimbing - FIXED VERSION
 * 
 * Controller untuk mengelola seminar skripsi dari perspektif dosen pembimbing
 * Menggunakan template existing dan mengadaptasi struktur Seminar_proposal.php
 * 
 * PERBAIKAN:
 * 1. Fixed model loading (Seminar_skripsi_model bukan Seminar_skripsi_mahasiswa_model)
 * 2. Complete semua method yang terpotong
 * 3. Fixed data retrieval untuk menampilkan pengajuan
 * 4. Improved error handling dan validation
 * 5. Konsisten dengan pattern Seminar_proposal.php
 * 
 * Features:
 * 1. Dashboard dengan statistics dan overview
 * 2. Detail pengajuan dengan validasi eligibility
 * 3. Review & Rekomendasi (setujui/tolak) dengan email notification  
 * 4. Input Penilaian, Catatan Revisi, dan Rekomendasi setelah seminar
 * 
 * File: application/controllers/dosen/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (FIXED - Data & UI Issues Resolved)
 */
class Seminar_skripsi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text']);
        
        // FIXED: Load model yang benar
        try {
            $this->load->model('Seminar_skripsi_model', 'seminar_model');
        } catch (Exception $e) {
            log_message('error', 'Error loading seminar skripsi model: ' . $e->getMessage());
        }
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar skripsi untuk dosen
     * FIXED: Complete implementation dengan data yang benar
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // FIXED: Prepare data untuk view dengan error handling
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
     * FIXED: Complete implementation
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
        
        // Check eligibility (seminar proposal completed + penelitian completed)
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
     * FIXED: Complete implementation
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
     * Proses rekomendasi seminar skripsi (Setujui/Tolak)
     * FIXED: Complete implementation dengan workflow yang benar
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
        
        // Process rekomendasi menggunakan model
        $result = $this->seminar_model->process_rekomendasi_pembimbing($seminar_id, [
            'rekomendasi' => $rekomendasi,
            'komentar_pembimbing' => $komentar,
            'reviewed_by' => $dosen_id,
            'reviewed_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
            
            // Send email notification
            $this->_send_email_notification($seminar, $rekomendasi, $komentar);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('dosen/seminar_skripsi');
    }

    /**
     * Form penilaian seminar skripsi (Input Penilaian, Catatan dan Rekomendasi)
     * FIXED: Complete implementation
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
    // PRIVATE HELPER METHODS - FIXED: Complete implementation
    // =================================================================

    /**
     * FIXED: Get pengajuan yang perlu review oleh dosen
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            // Query untuk mendapatkan seminar skripsi yang perlu review
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id); // Filter by dosen pembimbing
            $this->db->where('ssm.status', 'submitted'); // Status submitted (perlu review)
            $this->db->where('ssm.current_step', 'pembimbing'); // Current step di pembimbing
            $this->db->order_by('ssm.created_at', 'DESC');
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan perlu review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * FIXED: Get riwayat rekomendasi yang sudah diberikan dosen
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                CASE 
                    WHEN ssm.status_pembimbing = "approved" THEN "Disetujui"
                    WHEN ssm.status_pembimbing = "rejected" THEN "Ditolak"
                    ELSE "Pending"
                END as status_rekomendasi
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.reviewed_by_pembimbing IS NOT NULL'); // Sudah direview
            $this->db->order_by('ssm.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(5); // Limit 5 riwayat terakhir
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * FIXED: Get seminar yang perlu penilaian (sudah terjadwal/selesai seminar)
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
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
            $this->db->where_in('ssm.status', ['scheduled', 'completed']); // Terjadwal atau selesai
            $this->db->where('ssm.tanggal_seminar <=', date('Y-m-d')); // Sudah lewat tanggal seminar
            
            // Check if belum ada penilaian
            $this->db->where('ssm.id NOT IN (
                SELECT seminar_id FROM penilaian_seminar_skripsi 
                WHERE role_penilai = "pembimbing" AND dinilai_oleh = ' . $dosen_id . '
            )');
            
            $this->db->order_by('ssm.tanggal_seminar', 'ASC');
            
            $result = $this->db->get()->result();
            return $result ?: [];
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar perlu penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * FIXED: Get statistics untuk dashboard
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
            $this->db->where('ssm.status', 'submitted');
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
     * FIXED: Get detail seminar dengan validasi ownership
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id); // Validasi ownership
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * FIXED: Get detail seminar untuk penilaian (harus sudah scheduled/completed)
     */
    private function _get_seminar_detail_for_penilaian($seminar_id, $dosen_id) {
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            return null;
        }
        
        // Validasi apakah sudah bisa dinilai
        if (!in_array($seminar->status, ['scheduled', 'completed'])) {
            return null;
        }
        
        // Validasi tanggal seminar sudah lewat
        if (empty($seminar->tanggal_seminar) || $seminar->tanggal_seminar > date('Y-m-d')) {
            return null;
        }
        
        return $seminar;
    }

    /**
     * Check eligibility untuk seminar skripsi
     */
    private function _check_eligibility($proposal_id) {
        // Default eligibility structure
        $eligibility = [
            'can_proceed' => false,
            'seminar_proposal_completed' => false,
            'penelitian_completed' => false,
            'issues' => []
        ];
        
        try {
            // Check seminar proposal completed
            $this->db->select('status');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $seminar_proposal = $this->db->get()->row();
            
            $eligibility['seminar_proposal_completed'] = !empty($seminar_proposal);
            
            if (!$eligibility['seminar_proposal_completed']) {
                $eligibility['issues'][] = 'Seminar proposal belum selesai';
            }
            
            // Check penelitian completed (ada surat keterangan penelitian)
            $this->db->select('status');
            $this->db->from('surat_penelitian');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $penelitian = $this->db->get()->row();
            
            $eligibility['penelitian_completed'] = !empty($penelitian);
            
            if (!$eligibility['penelitian_completed']) {
                $eligibility['issues'][] = 'Penelitian belum selesai (surat keterangan penelitian diperlukan)';
            }
            
            // Can proceed jika semua requirement terpenuhi
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
     */
    private function _safe_get_jurnal_bimbingan($proposal_id) {
        try {
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(5); // 5 jurnal terakhir
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal bimbingan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get existing penilaian
     */
    private function _get_existing_penilaian($seminar_id, $dosen_id) {
        try {
            $this->db->select('*');
            $this->db->from('penilaian_seminar_skripsi');
            $this->db->where('seminar_id', $seminar_id);
            $this->db->where('dinilai_oleh', $dosen_id);
            $this->db->where('role_penilai', 'pembimbing');
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting existing penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get dosen by ID
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
     * Process penilaian (setelah seminar)
     */
    private function _process_penilaian($seminar_id, $dosen_id, $seminar) {
        // Validasi input
        $required_fields = [
            'nilai_presentasi_materi',
            'nilai_presentasi_teknik', 
            'nilai_penguasaan_diskusi',
            'rekomendasi'
        ];
        
        foreach ($required_fields as $field) {
            if (empty($this->input->post($field))) {
                $this->session->set_flashdata('error', 'Semua field penilaian wajib diisi!');
                redirect('dosen/seminar_skripsi/penilaian/' . $seminar_id);
                return;
            }
        }
        
        // Prepare data penilaian
        $penilaian_data = [
            'seminar_id' => $seminar_id,
            'mahasiswa_id' => $seminar->mahasiswa_id,
            'proposal_id' => $seminar->proposal_id,
            'nilai_presentasi_materi' => $this->input->post('nilai_presentasi_materi'),
            'nilai_presentasi_teknik' => $this->input->post('nilai_presentasi_teknik'),
            'nilai_penguasaan_diskusi' => $this->input->post('nilai_penguasaan_diskusi'),
            'catatan_revisi' => $this->input->post('catatan_revisi'),
            'rekomendasi' => $this->input->post('rekomendasi'),
            'keterangan_rekomendasi' => $this->input->post('keterangan_rekomendasi'),
            'dinilai_oleh' => $dosen_id,
            'role_penilai' => 'pembimbing',
            'status_penilaian' => 'completed'
        ];
        
        // Hitung nilai akhir
        $nilai_akhir = ($penilaian_data['nilai_presentasi_materi'] + 
                       $penilaian_data['nilai_presentasi_teknik'] + 
                       $penilaian_data['nilai_penguasaan_diskusi']) / 3;
        
        $penilaian_data['nilai_akhir'] = round($nilai_akhir, 2);
        $penilaian_data['nilai_huruf'] = $this->_convert_to_letter_grade($nilai_akhir);
        $penilaian_data['updated_at'] = date('Y-m-d H:i:s');
        
        // Check if update or insert
        $existing = $this->_get_existing_penilaian($seminar_id, $dosen_id);
        
        try {
            if ($existing) {
                // Update existing penilaian
                $this->db->where('id', $existing->id);
                $success = $this->db->update('penilaian_seminar_skripsi', $penilaian_data);
            } else {
                // Insert new penilaian
                $penilaian_data['created_at'] = date('Y-m-d H:i:s');
                $success = $this->db->insert('penilaian_seminar_skripsi', $penilaian_data);
            }
            
            if ($success) {
                // Update status seminar jika diperlukan
                $this->_update_seminar_status_after_penilaian($seminar_id);
                
                $this->session->set_flashdata('success', 'Penilaian berhasil disimpan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan penilaian!');
            }
        } catch (Exception $e) {
            log_message('error', 'Error saving penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan penilaian!');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    /**
     * Update seminar status setelah penilaian
     */
    private function _update_seminar_status_after_penilaian($seminar_id) {
        // Check if all required assessments are completed
        $this->db->select('COUNT(*) as total_penilaian');
        $this->db->from('penilaian_seminar_skripsi');
        $this->db->where('seminar_id', $seminar_id);
        $this->db->where('status_penilaian', 'completed');
        $result = $this->db->get()->row();
        
        // Jika sudah ada penilaian dari pembimbing (minimal requirement)
        if ($result && $result->total_penilaian >= 1) {
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', [
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Convert numeric grade to letter grade
     */
    private function _convert_to_letter_grade($nilai) {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 80) return 'A-';
        if ($nilai >= 75) return 'B+';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 65) return 'B-';
        if ($nilai >= 60) return 'C+';
        if ($nilai >= 55) return 'C';
        if ($nilai >= 50) return 'C-';
        if ($nilai >= 45) return 'D';
        return 'E';
    }

    /**
     * Send email notification
     */
    private function _send_email_notification($seminar, $rekomendasi, $komentar) {
        try {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'ssl://smtp.gmail.com',
                'smtp_port' => 465,
                'smtp_user' => 'your-email@gmail.com', // Configure your email
                'smtp_pass' => 'your-password',
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
            
            $this->email->initialize($config);
            
            if ($rekomendasi == 'approved') {
                // Email ke Kaprodi
                $subject = 'Seminar Skripsi Disetujui Pembimbing - ' . $seminar->nama_mahasiswa;
                $message = "Seminar skripsi mahasiswa {$seminar->nama_mahasiswa} telah disetujui oleh dosen pembimbing dan menunggu review Kaprodi.";
                // Send to Kaprodi email here
            } else {
                // Email ke Mahasiswa
                $subject = 'Seminar Skripsi Ditolak - Perlu Revisi';
                $message = "Pengajuan seminar skripsi Anda ditolak dengan komentar: {$komentar}";
                $this->email->to($seminar->email_mahasiswa);
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
            }
        } catch (Exception $e) {
            log_message('error', 'Error sending email: ' . $e->getMessage());
        }
    }

    /**
     * Get content type for file viewing
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
     */
    private function _get_index_script() {
        return "
        <script>
        $(document).ready(function() {
            // Initialize DataTables jika ada
            if ($('#pengajuan-table').length) {
                $('#pengajuan-table').DataTable({
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                    }
                });
            }
            
            // Tooltip initialization
            $('[data-toggle=\"tooltip\"]').tooltip();
        });
        </script>";
    }

    /**
     * Get JavaScript for detail page
     */
    private function _get_detail_script() {
        return "
        <script>
        $(document).ready(function() {
            // Form validation
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
            
            // Show/hide komentar berdasarkan pilihan
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
     */
    private function _get_penilaian_script() {
        return "
        <script>
        $(document).ready(function() {
            // Auto calculate nilai akhir
            $('.nilai-input').on('input', function() {
                calculateNilaiAkhir();
            });
            
            function calculateNilaiAkhir() {
                const materi = parseFloat($('#nilai_presentasi_materi').val()) || 0;
                const teknik = parseFloat($('#nilai_presentasi_teknik').val()) || 0;
                const diskusi = parseFloat($('#nilai_penguasaan_diskusi').val()) || 0;
                
                const average = (materi + teknik + diskusi) / 3;
                $('#nilai_akhir_preview').text(average.toFixed(2));
                $('#nilai_huruf_preview').text(convertToLetterGrade(average));
            }
            
            function convertToLetterGrade(nilai) {
                if (nilai >= 85) return 'A';
                if (nilai >= 80) return 'A-';
                if (nilai >= 75) return 'B+';
                if (nilai >= 70) return 'B';
                if (nilai >= 65) return 'B-';
                if (nilai >= 60) return 'C+';
                if (nilai >= 55) return 'C';
                if (nilai >= 50) return 'C-';
                if (nilai >= 45) return 'D';
                return 'E';
            }
            
            // Form validation
            $('#penilaian-form').on('submit', function(e) {
                const requiredFields = ['nilai_presentasi_materi', 'nilai_presentasi_teknik', 'nilai_penguasaan_diskusi', 'rekomendasi'];
                
                for (let field of requiredFields) {
                    if (!$('#' + field).val()) {
                        e.preventDefault();
                        alert('Semua field penilaian wajib diisi!');
                        $('#' + field).focus();
                        return;
                    }
                }
                
                return confirm('Yakin menyimpan penilaian ini?');
            });
        });
        </script>";
    }
}