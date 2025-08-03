<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller untuk Dosen Pembimbing - DATABASE STRUCTURE CORRECT
 * 
 * Controller untuk mengelola seminar skripsi dari perspektif dosen pembimbing
 * FIXED: Berdasarkan struktur database sebenarnya dari tabel seminar_skripsi_mahasiswa
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
 * @version     4.0 (Database Structure Correct)
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
     * FIXED: Query sesuai struktur database sebenarnya
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
     * FIXED: Menggunakan kolom yang benar dari database
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
                
                // Send email notification
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
     * Form penilaian seminar skripsi
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
    // PRIVATE HELPER METHODS - FIXED: Sesuai struktur database
    // =================================================================

    /**
     * FIXED: Get pengajuan yang perlu review oleh dosen
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
                pm.judul
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('ssm.reviewed_by_pembimbing', $dosen_id); // Sudah direview oleh dosen ini
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
     * FIXED: Get seminar yang perlu penilaian
     * Langsung muncul setelah mahasiswa submit, tanpa syarat tambahan
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
            
            // Seminar yang sudah submitted dan belum ada penilaian yang dipublish
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
     * Get detail seminar untuk penilaian
     * FIXED: Tidak perlu syarat khusus, langsung bisa dinilai setelah submit
     */
    private function _get_seminar_detail_for_penilaian($seminar_id, $dosen_id) {
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            return null;
        }
        
        // Seminar bisa dinilai setelah submitted (tidak perlu scheduled)
        if ($seminar->status == 'draft') {
            return null; // Hanya draft yang tidak bisa dinilai
        }
        
        return $seminar;
    }

    /**
     * Check eligibility untuk seminar skripsi
     */
    private function _check_eligibility($proposal_id) {
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
            
            // Check penelitian completed - bisa disesuaikan dengan kebutuhan
            $eligibility['penelitian_completed'] = true; // Untuk sementara
            
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
            $this->db->limit(5);
            
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
     * Process penilaian (form submission)
     * FIXED: Complete implementation dengan workflow update
     */
    private function _process_penilaian($seminar_id, $dosen_id, $seminar) {
        $action = $this->input->post('action'); // 'draft' atau 'publish'
        
        // Validasi input required
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
        
        // Validasi action untuk publish
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
        
        // Prepare data penilaian
        $nilai_penguji1 = (float)$this->input->post('nilai_penguji1');
        $nilai_penguji2 = (float)$this->input->post('nilai_penguji2');
        $nilai_pembimbing = (float)$this->input->post('nilai_pembimbing');
        
        // Hitung nilai akhir (rata-rata dari yang diisi)
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
        
        // Check if update or insert
        $existing = $this->_get_existing_penilaian($seminar_id, $dosen_id);
        
        try {
            $this->db->trans_start();
            
            if ($existing) {
                // Update existing penilaian
                $this->db->where('id', $existing->id);
                $success = $this->db->update('penilaian_seminar_skripsi', $penilaian_data);
            } else {
                // Insert new penilaian
                $penilaian_data['created_at'] = date('Y-m-d H:i:s');
                $success = $this->db->insert('penilaian_seminar_skripsi', $penilaian_data);
            }
            
            // Jika publish, update workflow status mahasiswa menjadi completed
            if ($action == 'publish' && $success) {
                // Update seminar status menjadi completed
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_skripsi_mahasiswa', [
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update workflow status proposal mahasiswa menjadi completed atau publikasi
                $this->db->where('id', $seminar->proposal_id);
                $this->db->update('proposal_mahasiswa', [
                    'workflow_status' => 'publikasi', // atau 'selesai' sesuai kebutuhan
                    'status_seminar_skripsi' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                $this->session->set_flashdata('error', 'Gagal menyimpan penilaian!');
            } else {
                if ($action == 'publish') {
                    $this->session->set_flashdata('success', 'Penilaian berhasil dipublikasikan! Status mahasiswa telah diupdate ke tahap selanjutnya.');
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

    /**
     * Send email notification
     */
    private function _send_email_notification($seminar, $rekomendasi, $komentar) {
        try {
            log_message('info', "Email notification: Seminar skripsi {$seminar->nama_mahasiswa} - {$rekomendasi}");
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
     * ENHANCED: Auto calculate dan form validation
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
            
            // Form validation
            $('#penilaian-form').on('submit', function(e) {
                const action = $('button[type=submit][clicked=true]').val();
                const pembimbing = $('#nilai_pembimbing').val();
                const rekomendasi = $('input[name=\"rekomendasi\"]:checked').val();
                
                // Basic validation
                if (!pembimbing || !rekomendasi) {
                    e.preventDefault();
                    alert('Nilai pembimbing dan rekomendasi wajib diisi!');
                    return;
                }
                
                // Validation for publish
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
            
            // Track which submit button was clicked
            $('button[type=submit]').click(function() {
                $('button[type=submit]').removeAttr('clicked');
                $(this).attr('clicked', 'true');
            });
            
            // Initialize calculation on page load
            calculateNilaiAkhir();
        });
        </script>";
    }
}