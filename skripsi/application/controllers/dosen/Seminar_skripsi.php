<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller untuk Dosen Pembimbing
 * 
 * Controller untuk mengelola seminar skripsi dari perspektif dosen pembimbing
 * Menggunakan template existing dan mengadaptasi struktur Seminar_proposal.php
 * 
 * Features:
 * 1. Dashboard dengan statistics dan overview
 * 2. Detail pengajuan dengan validasi eligibility
 * 3. Review & Rekomendasi (setujui/tolak) dengan email notification  
 * 4. Input Penilaian, Catatan Revisi, dan Rekomendasi
 * 
 * File: application/controllers/dosen/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Adapted from Seminar_proposal.php)
 */
class Seminar_skripsi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text']);
        
        // Load model dengan error handling
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
     * Menggunakan template existing dosen.php
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view dengan error handling
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
     * Menggunakan template existing dosen.php
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
        
        // Set headers untuk display PDF/Word
        $this->_display_file($file_path, $seminar->file_skripsi);
    }

    /**
     * Proses rekomendasi seminar skripsi (Review & Rekomendasi)
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
        
        // Process rekomendasi
        $this->db->trans_start();
        
        try {
            // Update seminar status berdasarkan rekomendasi
            $update_data = [
                'status_pembimbing' => $rekomendasi,
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'reviewed_by_pembimbing' => $dosen_id
            ];

            if ($rekomendasi == 'approved') {
                $update_data['status'] = 'review_kaprodi';
                $update_data['current_step'] = 'kaprodi';
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }

            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            // Send notification email
            $this->_send_notification_email($seminar, $rekomendasi, $komentar);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            $message = $rekomendasi == 'approved' ? 
                'Seminar skripsi berhasil disetujui dan diteruskan ke Kaprodi!' : 
                'Seminar skripsi ditolak. Mahasiswa akan mendapat notifikasi untuk revisi.';
                
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error in seminar skripsi rekomendasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
        
        redirect('dosen/seminar_skripsi');
    }

    /**
     * Form penilaian seminar skripsi (Input Penilaian, Catatan dan Rekomendasi)
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
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Get pengajuan yang perlu direview oleh dosen
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            $this->db->select('
                ssm.id, ssm.proposal_id, ssm.status, ssm.created_at,
                ssm.file_skripsi, ssm.keterangan_mahasiswa,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.workflow_status
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status', 'review_pembimbing');
            $this->db->where('ssm.current_step', 'pembimbing');
            $this->db->order_by('ssm.created_at', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat rekomendasi yang sudah diberikan
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                ssm.id, ssm.proposal_id, ssm.status, ssm.status_pembimbing,
                ssm.komentar_pembimbing, ssm.tanggal_review_pembimbing,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('ssm.status_pembimbing', ['approved', 'rejected']);
            $this->db->order_by('ssm.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get seminar yang perlu penilaian (scheduled atau completed)
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        try {
            $this->db->select('
                ssm.id, ssm.proposal_id, ssm.status, ssm.tanggal_seminar,
                ssm.jam_seminar, ssm.tempat_seminar,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                pss.status_penilaian
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('penilaian_seminar_skripsi pss', 
                           'ssm.id = pss.seminar_skripsi_id AND pss.dinilai_oleh = ' . $dosen_id, 
                           'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('ssm.status', ['scheduled', 'completed']);
            $this->db->where('(pss.id IS NULL OR pss.status_penilaian = "draft")');
            $this->db->order_by('ssm.tanggal_seminar', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar perlu penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        try {
            // Total seminar skripsi bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $total = $this->db->get()->row()->total;

            // Perlu review
            $this->db->select('COUNT(*) as perlu_review');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status', 'review_pembimbing');
            $perlu_review = $this->db->get()->row()->perlu_review;

            // Disetujui
            $this->db->select('COUNT(*) as disetujui');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status_pembimbing', 'approved');
            $disetujui = $this->db->get()->row()->disetujui;

            // Ditolak
            $this->db->select('COUNT(*) as ditolak');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.status_pembimbing', 'rejected');
            $ditolak = $this->db->get()->row()->ditolak;

            return [
                'total' => $total,
                'perlu_review' => $perlu_review,
                'disetujui' => $disetujui,
                'ditolak' => $ditolak
            ];
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return ['total' => 0, 'perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0];
        }
    }

    /**
     * Get detail seminar dengan validasi ownership
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                ssm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.workflow_status, pm.dosen_id as pembimbing_id,
                d.nama as nama_pembimbing, d.email as email_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id); // Validasi ownership
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detail seminar untuk penilaian (harus sudah scheduled/completed)
     */
    private function _get_seminar_detail_for_penilaian($seminar_id, $dosen_id) {
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            return null;
        }
        
        // Harus sudah scheduled atau completed untuk bisa dinilai
        if (!in_array($seminar->status, ['scheduled', 'completed'])) {
            return null;
        }
        
        return $seminar;
    }

    /**
     * Check eligibility mahasiswa untuk seminar skripsi
     */
    private function _check_eligibility($proposal_id) {
        try {
            // Check seminar proposal completed
            $this->db->select('status, status_kaprodi');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $seminar_proposal = $this->db->get()->row();

            // Check penelitian completed (optional, tergantung workflow)
            $this->db->select('status, approved_at');
            $this->db->from('permohonan_izin_penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $this->db->where('status', 'approved');
            $penelitian = $this->db->get()->row();

            return [
                'seminar_proposal_ok' => !empty($seminar_proposal),
                'penelitian_ok' => !empty($penelitian),
                'eligible' => !empty($seminar_proposal) && !empty($penelitian)
            ];
        } catch (Exception $e) {
            log_message('error', 'Error checking eligibility: ' . $e->getMessage());
            return ['seminar_proposal_ok' => false, 'penelitian_ok' => false, 'eligible' => false];
        }
    }

    /**
     * Process penilaian seminar skripsi
     */
    private function _process_penilaian($seminar_id, $dosen_id, $seminar) {
        $action_type = $this->input->post('action_type'); // 'draft' atau 'publish'
        
        // Validasi action type
        if (!in_array($action_type, ['draft', 'publish'])) {
            $this->session->set_flashdata('error', 'Action type tidak valid!');
            redirect('dosen/seminar_skripsi/penilaian/' . $seminar_id);
            return;
        }

        // Ambil data form
        $data_penilaian = [
            'seminar_skripsi_id' => $seminar_id,
            'mahasiswa_id' => $seminar->mahasiswa_id,
            'proposal_id' => $seminar->proposal_id,
            
            // 6 Komponen Catatan Revisi Seminar Skripsi
            'catatan_pendahuluan' => $this->input->post('catatan_pendahuluan'),
            'catatan_tinjauan_pustaka' => $this->input->post('catatan_tinjauan_pustaka'),
            'catatan_metodologi' => $this->input->post('catatan_metodologi'),
            'catatan_hasil_pembahasan' => $this->input->post('catatan_hasil_pembahasan'),
            'catatan_kesimpulan' => $this->input->post('catatan_kesimpulan'),
            'catatan_umum' => $this->input->post('catatan_umum'),
            
            // Nilai Final (Sistem 3 Dosen)
            'nilai_penguji1' => $this->input->post('nilai_penguji1'),
            'nilai_penguji2' => $this->input->post('nilai_penguji2'),
            'nilai_pembimbing' => $this->input->post('nilai_pembimbing'),
            
            // Rekomendasi Seminar Skripsi
            'rekomendasi' => $this->input->post('rekomendasi'),
            'keterangan_rekomendasi' => $this->input->post('keterangan_rekomendasi'),
            
            'status_penilaian' => ($action_type == 'publish') ? 'published' : 'draft',
            'dinilai_oleh' => $dosen_id,
            'role_penilai' => 'dosen_pembimbing'
        ];

        // Hitung nilai akhir jika semua nilai diisi
        if (!empty($data_penilaian['nilai_penguji1']) && 
            !empty($data_penilaian['nilai_penguji2']) && 
            !empty($data_penilaian['nilai_pembimbing'])) {
            
            $data_penilaian['nilai_akhir'] = round(
                ($data_penilaian['nilai_penguji1'] + 
                 $data_penilaian['nilai_penguji2'] + 
                 $data_penilaian['nilai_pembimbing']) / 3, 2
            );
            
            // Konversi ke huruf
            $data_penilaian['nilai_huruf'] = $this->_convert_nilai_huruf($data_penilaian['nilai_akhir']);
        }

        // Set published_at jika publish
        if ($action_type == 'publish') {
            $data_penilaian['published_at'] = date('Y-m-d H:i:s');
        }

        $this->db->trans_start();

        try {
            // Check apakah sudah ada penilaian
            $this->db->where('seminar_skripsi_id', $seminar_id);
            $this->db->where('dinilai_oleh', $dosen_id);
            $existing = $this->db->get('penilaian_seminar_skripsi')->row();

            if ($existing) {
                // Update existing
                $this->db->where('id', $existing->id);
                $this->db->update('penilaian_seminar_skripsi', $data_penilaian);
            } else {
                // Insert new
                $this->db->insert('penilaian_seminar_skripsi', $data_penilaian);
            }

            // Jika publish, update status seminar ke completed
            if ($action_type == 'publish') {
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_skripsi_mahasiswa', [
                    'status' => 'completed',
                    'current_step' => 'completed'
                ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }

            $message = $action_type == 'publish' ? 
                'Penilaian berhasil dipublikasi!' : 
                'Penilaian berhasil disimpan sebagai draft.';
                
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error saving penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }

        redirect('dosen/seminar_skripsi');
    }

    /**
     * Get existing penilaian
     */
    private function _get_existing_penilaian($seminar_id, $dosen_id) {
        try {
            $this->db->where('seminar_skripsi_id', $seminar_id);
            $this->db->where('dinilai_oleh', $dosen_id);
            return $this->db->get('penilaian_seminar_skripsi')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting existing penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert nilai angka ke huruf
     */
    private function _convert_nilai_huruf($nilai) {
        if ($nilai >= 80) return 'A';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';
        return 'E';
    }

    /**
     * Get dosen by ID
     */
    private function _get_dosen_by_id($dosen_id) {
        try {
            $this->db->where('id', $dosen_id);
            return $this->db->get('dosen')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen: ' . $e->getMessage());
            return null;
        }
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
            $this->db->order_by('tanggal_bimbingan', 'DESC');
            $this->db->limit(5);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal bimbingan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send notification email
     */
    private function _send_notification_email($seminar, $rekomendasi, $komentar) {
        try {
            // Setup email
            $this->email->from('sistem@stkstya.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
            
            if ($rekomendasi == 'approved') {
                // Email ke Kaprodi
                $this->email->to($this->_get_kaprodi_email());
                $this->email->subject('Pengajuan Seminar Skripsi Perlu Persetujuan - ' . $seminar->nama_mahasiswa);
                $message = "Pengajuan seminar skripsi mahasiswa {$seminar->nama_mahasiswa} ({$seminar->nim}) telah disetujui dosen pembimbing dan memerlukan persetujuan Kaprodi.";
            } else {
                // Email ke mahasiswa
                $this->email->to($seminar->email_mahasiswa);
                $this->email->subject('Pengajuan Seminar Skripsi Ditolak - ' . $seminar->nama_mahasiswa);
                $message = "Pengajuan seminar skripsi Anda ditolak oleh dosen pembimbing.\n\nKomentar: {$komentar}\n\nSilakan lakukan revisi dan ajukan kembali.";
            }
            
            $this->email->message($message);
            $this->email->send();
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email: ' . $e->getMessage());
        }
    }

    /**
     * Get kaprodi email
     */
    private function _get_kaprodi_email() {
        try {
            $this->db->select('d.email');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.id = p.dosen_id');
            $this->db->where('d.level', '4'); // Kaprodi
            $this->db->limit(1);
            
            $result = $this->db->get()->row();
            return $result ? $result->email : 'kaprodi@stkstya.ac.id';
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi email: ' . $e->getMessage());
            return 'kaprodi@stkstya.ac.id';
        }
    }

    /**
     * Display file (PDF/Word)
     */
    private function _display_file($file_path, $filename) {
        $file_info = pathinfo($filename);
        $extension = strtolower($file_info['extension']);
        
        if ($extension == 'pdf') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
        } else {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
        
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    /**
     * Get JavaScript untuk halaman index
     */
    private function _get_index_script() {
        return '
        <script>
        $(document).ready(function() {
            // DataTable initialization jika diperlukan
            if ($("#table-pengajuan").length) {
                $("#table-pengajuan").DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "searching": true
                });
            }
        });
        </script>';
    }

    /**
     * Get JavaScript untuk halaman detail
     */
    private function _get_detail_script() {
        return '
        <script>
        function confirmRekomendasi(action) {
            var message = action === "approved" ? 
                "Yakin menyetujui pengajuan seminar skripsi ini?" : 
                "Yakin menolak pengajuan seminar skripsi ini?";
            return confirm(message);
        }
        </script>';
    }

    /**
     * Get JavaScript untuk halaman penilaian
     */
    private function _get_penilaian_script() {
        return '
        <script>
        $(document).ready(function() {
            // Auto calculate nilai akhir
            $(".nilai-input").on("input", function() {
                calculateNilaiAkhir();
            });
            
            function calculateNilaiAkhir() {
                var nilai1 = parseFloat($("#nilai_penguji1").val()) || 0;
                var nilai2 = parseFloat($("#nilai_penguji2").val()) || 0;
                var nilai3 = parseFloat($("#nilai_pembimbing").val()) || 0;
                
                if (nilai1 > 0 && nilai2 > 0 && nilai3 > 0) {
                    var nilaiAkhir = Math.round((nilai1 + nilai2 + nilai3) / 3 * 100) / 100;
                    $("#nilai_akhir").val(nilaiAkhir);
                    
                    // Update nilai huruf
                    var huruf = "E";
                    if (nilaiAkhir >= 80) huruf = "A";
                    else if (nilaiAkhir >= 70) huruf = "B";
                    else if (nilaiAkhir >= 60) huruf = "C";
                    else if (nilaiAkhir >= 50) huruf = "D";
                    
                    $("#nilai_huruf").val(huruf);
                }
            }
            
            // Validate before submit
            $("#form-penilaian").on("submit", function(e) {
                var action = $(document.activeElement).val();
                
                if (action === "publish") {
                    if (!$("#rekomendasi").val()) {
                        alert("Rekomendasi harus dipilih untuk publikasi!");
                        e.preventDefault();
                        return false;
                    }
                    
                    if (!confirm("Yakin akan mempublikasi penilaian? Data tidak dapat diubah setelah dipublikasi.")) {
                        e.preventDefault();
                        return false;
                    }
                }
                
                return true;
            });
        });
        </script>';
    }

}