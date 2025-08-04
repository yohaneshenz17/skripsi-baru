<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FINAL FIXED - Seminar Skripsi Controller untuk Kaprodi
 * 
 * PERBAIKAN BERDASARKAN STRUKTUR DATABASE AKTUAL:
 * - proposal_mahasiswa TIDAK punya prodi_id
 * - Relasi prodi melalui mahasiswa.prodi_id  
 * - Menggunakan join yang benar sesuai database structure
 * - Query disesuaikan dengan view yang ada
 * 
 * STRUKTUR JOIN YANG BENAR:
 * seminar_skripsi_mahasiswa -> proposal_mahasiswa -> mahasiswa -> prodi
 * 
 * File: application/controllers/kaprodi/Seminar_skripsi.php
 * URL: https://stkyakobus.ac.id/skripsi/kaprodi/seminar_skripsi/index
 */
class Seminar_skripsi extends CI_Controller {

    private $prodi_id;

    public function __construct() {
        parent::__construct();
        
        // Load core libraries
        $this->load->database();
        $this->load->library(['session', 'email', 'upload']);
        
        // ✅ CRITICAL FIX: Load text helper untuk word_limiter() function
        $this->load->helper(['url', 'date', 'file', 'text', 'form']);
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $user_level = $this->session->userdata('level');
        if(!in_array($user_level, ['3', '4'])) { // Support both level 3 and 4 for kaprodi
            show_error('Akses ditolak. Anda bukan Kaprodi.', 403);
        }
        
        // ✅ FIX: Load model dengan error handling
        try {
            $this->load->model('Seminar_skripsi_model', 'seminar_model');
        } catch (Exception $e) {
            log_message('error', 'Failed to load Seminar_skripsi_model: ' . $e->getMessage());
            // Create simple fallback model untuk mencegah crash
            $this->_create_fallback_model();
        }
        
        // Set prodi_id dari session
        $this->prodi_id = $this->session->userdata('prodi_id');
        
        // Get prodi_id jika belum ada di session
        if (!$this->prodi_id) {
            $this->_set_prodi_id();
        }
    }

    /**
     * ✅ FIXED: Index - Dashboard seminar skripsi untuk kaprodi
     * MENGGUNAKAN EXACT PATTERN DOSEN YANG STABLE
     */
    public function index() {
        // Prepare data untuk view berdasarkan database structure yang benar
        $data = [
            'title' => 'Kelola Seminar Skripsi',
            'seminar_skripsi' => $this->_get_seminar_skripsi_list(),
            'pengajuan_review' => $this->_get_pengajuan_perlu_review(),
            'perlu_dijadwalkan' => $this->_get_seminar_perlu_dijadwalkan(),
            'jadwal_mendatang' => $this->_get_jadwal_mendatang(),
            'stats' => $this->_get_statistics_from_view()
        ];
        
        // Pastikan semua data tidak null (EXACT seperti dosen)
        $data['seminar_skripsi'] = $data['seminar_skripsi'] ?: [];
        $data['pengajuan_review'] = $data['pengajuan_review'] ?: [];
        $data['perlu_dijadwalkan'] = $data['perlu_dijadwalkan'] ?: [];
        $data['jadwal_mendatang'] = $data['jadwal_mendatang'] ?: [];
        $data['stats'] = $data['stats'] ?: [
            'pending_review' => 0, 
            'approved_month' => 0, 
            'rejected_month' => 0, 
            'scheduled' => 0
        ];
        
        // Load view dengan template kaprodi (EXACT pattern dosen)
        $this->load->view('template/kaprodi', [
            'title' => 'Kelola Seminar Skripsi',
            'content' => $this->load->view('kaprodi/seminar_skripsi/index', $data, TRUE),
            'script' => '' // atau $this->_get_index_script() jika ada
        ]);
    }

    /**
     * Detail pengajuan seminar skripsi untuk review
     */
public function detail($seminar_id) {
    // Validasi ID
    if (!is_numeric($seminar_id)) {
        show_404();
        return;
    }
    
    try {
        // ✅ QUERY SEDERHANA - Ambil data seminar berdasarkan ID
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('ssm.id', $seminar_id);
        
        // Validasi prodi jika ada
        if ($this->prodi_id) {
            $this->db->where('m.prodi_id', $this->prodi_id);
        }
        
        $seminar = $this->db->get()->row();
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan dari prodi Anda!');
            redirect('kaprodi/seminar_skripsi');
            return;
        }
        
        // Data untuk view
        $data = [
            'seminar' => $seminar,
            'allow_edit' => true
        ];
        
        // ✅ POLA YANG SAMA dengan controller stable
        $content = $this->load->view('kaprodi/seminar_skripsi/detail', $data, TRUE);
        
        $template_data = [
            'title' => 'Detail Seminar Skripsi - ' . $seminar->nama_mahasiswa,
            'content' => $content
        ];
        
        $this->load->view('template/kaprodi', $template_data);
        
    } catch (Exception $e) {
        log_message('error', 'Seminar_skripsi detail error: ' . $e->getMessage());
        $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
        redirect('kaprodi/seminar_skripsi');
    }
}

    /**
     * Enhanced validasi_turnitin - REPLACE method existing
     * Menambah file upload handling tanpa mengubah logic existing
     */
    public function validasi_turnitin() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
            return;
        }
    
        try {
            // Get input data
            $seminar_id = $this->input->post('seminar_id');
            $decision = $this->input->post('decision');
            $plagiarism_percentage = $this->input->post('plagiarism_percentage');
            $komentar = trim($this->input->post('komentar_kaprodi'));
    
            // ✅ COMPREHENSIVE INPUT VALIDATION
            if (empty($seminar_id) || !is_numeric($seminar_id)) {
                throw new Exception('ID seminar tidak valid');
            }
    
            if (empty($decision) || !in_array($decision, ['approved', 'rejected'])) {
                throw new Exception('Keputusan validasi tidak valid');
            }
    
            if (empty($plagiarism_percentage) || !is_numeric($plagiarism_percentage)) {
                throw new Exception('Persentase plagiarisme harus diisi dengan angka');
            }
    
            $plagiarism_score = floatval($plagiarism_percentage);
    
            // ✅ RANGE VALIDATION (APPLICATION LEVEL)
            if ($plagiarism_score < 0 || $plagiarism_score > 100) {
                throw new Exception('Persentase plagiarisme harus antara 0-100%');
            }
    
            // ✅ BUSINESS LOGIC VALIDATION - CORE WORKFLOW RULE
            if ($decision === 'approved' && $plagiarism_score > 30) {
                $this->session->set_flashdata('error', 
                    'Tidak dapat menyetujui pengajuan dengan skor plagiarisme ' . 
                    number_format($plagiarism_score, 1) . '% (maksimal 30% untuk approval). ' .
                    'Silakan pilih "Tolak" jika ingin menolak pengajuan ini.');
                redirect('kaprodi/seminar_skripsi/detail/' . $seminar_id);
                return;
            }
    
            // ✅ GET SEMINAR DATA FOR VALIDATION
            $seminar = $this->_get_seminar_detail($seminar_id);
            if (!$seminar) {
                throw new Exception('Data seminar tidak ditemukan');
            }
    
            // ✅ AUTHORIZATION CHECK
            if ($seminar->status_kaprodi !== 'pending') {
                throw new Exception('Seminar sudah divalidasi sebelumnya');
            }
    
            // ✅ MANDATORY COMMENT FOR REJECTION >30%
            if ($decision === 'rejected' && $plagiarism_score > 30 && empty($komentar)) {
                $komentar = 'Pengajuan ditolak karena skor plagiarisme ' . 
                           number_format($plagiarism_score, 1) . '% melebihi batas maksimal 30%.';
            }
    
            // ✅ HANDLE FILE UPLOAD (OPTIONAL)
            $uploaded_turnitin_file = null;
            if (!empty($_FILES['file_turnitin']['name'])) {
                $uploaded_turnitin_file = $this->_handle_turnitin_upload($seminar_id);
            }
    
            // ✅ PREPARE UPDATE DATA
            $update_data = [
                'status_kaprodi' => $decision,
                'komentar_kaprodi' => $komentar,
                'plagiarism_percentage' => $plagiarism_score, // Simpan skor asli
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            // ✅ UPDATE WORKFLOW STATUS
            if ($decision === 'approved') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'staf';
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }
    
            // ✅ ADD UPLOADED FILE
            if ($uploaded_turnitin_file) {
                $update_data['file_turnitin'] = $uploaded_turnitin_file;
            }
    
            // ✅ DATABASE TRANSACTION
            $this->db->trans_start();
    
            $this->db->where('id', $seminar_id);
            $affected = $this->db->update('seminar_skripsi_mahasiswa', $update_data);
    
            if (!$affected) {
                throw new Exception('Gagal memperbarui data di database');
            }
    
            $this->db->trans_complete();
    
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }
    
            // ✅ SUCCESS MESSAGING
            if ($decision === 'approved') {
                $success_msg = 'Seminar skripsi berhasil DISETUJUI! ' .
                              'Skor plagiarisme: ' . number_format($plagiarism_score, 1) . '% ' .
                              '(dalam batas toleransi ≤30%)';
            } else {
                $success_msg = 'Seminar skripsi berhasil DITOLAK. ' .
                              'Skor plagiarisme: ' . number_format($plagiarism_score, 1) . '%';
                
                if ($plagiarism_score > 30) {
                    $success_msg .= ' (melebihi batas maksimal 30%)';
                }
            }
    
            $this->session->set_flashdata('success', $success_msg);
    
            // ✅ SEND NOTIFICATIONS (Optional - bisa dimatikan dulu untuk testing)
            $this->_send_validation_notifications($seminar_id, $decision, $plagiarism_score);
    
            // ✅ LOG FOR AUDIT TRAIL
            log_message('info', sprintf(
                'Kaprodi validation: Seminar ID=%d, Decision=%s, Plagiarism=%.1f%%, Kaprodi ID=%d',
                $seminar_id, $decision, $plagiarism_score, $this->session->userdata('id')
            ));
    
        } catch (Exception $e) {
            // ✅ COMPREHENSIVE ERROR HANDLING
            $this->db->trans_rollback();
            
            log_message('error', sprintf(
                'Kaprodi validation error: Seminar ID=%s, Error=%s, User ID=%d',
                $seminar_id ?? 'unknown', $e->getMessage(), $this->session->userdata('id')
            ));
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    
        redirect('kaprodi/seminar_skripsi');
    }

    /**
     * ✅ PERBAIKAN 2: Method penjadwalan() - Fix untuk mengambil data seminar dan dosen
     * UPDATE method existing dengan perbaikan ini
     */
    public function penjadwalan($seminar_id) {
        // Validasi ID
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        try {
            // ✅ Ambil data seminar dengan JOIN yang benar
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim, 
                m.nama as nama_mahasiswa, 
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            
            // Validasi prodi jika ada
            if ($this->prodi_id) {
                $this->db->where('m.prodi_id', $this->prodi_id);
            }
            
            $seminar = $this->db->get()->row();
            
            if (!$seminar) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan dari prodi Anda!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }
            
            // ✅ Validasi status - harus sudah approved
            if (($seminar->status_kaprodi ?? 'pending') !== 'approved') {
                $this->session->set_flashdata('error', 'Seminar belum disetujui, tidak dapat dijadwalkan!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }
            
            // ✅ Get dosen list untuk penguji
            $data = [
                'seminar' => $seminar,
                'dosen_list' => $this->_get_dosen_penguji(),
                'penguji_recommendations' => $this->_get_penguji_recommendations($seminar->proposal_id ?? null)
            ];
            
            // Load view dengan template
            $content = $this->load->view('kaprodi/seminar_skripsi/penjadwalan', $data, TRUE);
            
            $template_data = [
                'title' => 'Penjadwalan Seminar Skripsi - ' . $seminar->nama_mahasiswa,
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);
            
        } catch (Exception $e) {
            log_message('error', 'Penjadwalan error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
            redirect('kaprodi/seminar_skripsi');
        }
    }

    /**
     * ✅ PERBAIKAN 4: Method simpan_jadwal() - untuk menyimpan jadwal
     * UPDATE method existing dengan perbaikan ini
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
            return;
        }
    
        try {
            // Get input data
            $seminar_id = $this->input->post('seminar_id');
            $tanggal_seminar = $this->input->post('tanggal_seminar');
            $jam_seminar = $this->input->post('jam_seminar');
            $tempat_seminar = trim($this->input->post('tempat_seminar'));
            $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
            $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');
            $catatan_kaprodi = trim($this->input->post('catatan_kaprodi'));
    
            // Validasi input
            if (empty($seminar_id) || empty($tanggal_seminar) || empty($jam_seminar) || 
                empty($tempat_seminar) || empty($dosen_penguji1_id) || empty($dosen_penguji2_id)) {
                throw new Exception('Semua field wajib diisi!');
            }
    
            if ($dosen_penguji1_id === $dosen_penguji2_id) {
                throw new Exception('Penguji 1 dan Penguji 2 tidak boleh sama!');
            }
    
            // Validasi tanggal (minimal H+1)
            $datetime_seminar = $tanggal_seminar . ' ' . $jam_seminar;
            if (strtotime($datetime_seminar) <= strtotime('+1 day')) {
                throw new Exception('Jadwal seminar minimal H+1 dari sekarang!');
            }
    
            // ✅ PERBAIKAN: Get seminar data sebelum update untuk notifikasi
            $seminar_detail = $this->_get_seminar_detail($seminar_id);
            if (!$seminar_detail) {
                throw new Exception('Data seminar tidak ditemukan!');
            }
    
            // Update database
            $update_data = [
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'dosen_penguji1_id' => $dosen_penguji1_id,
                'dosen_penguji2_id' => $dosen_penguji2_id,
                'status' => 'scheduled',
                'current_step' => 'staf',
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            if (!empty($catatan_kaprodi)) {
                $update_data['komentar_kaprodi'] = $catatan_kaprodi;
            }
    
            $this->db->trans_start();
            
            $this->db->where('id', $seminar_id);
            $affected = $this->db->update('seminar_skripsi_mahasiswa', $update_data);
    
            if (!$affected) {
                throw new Exception('Gagal memperbarui data di database');
            }
    
            $this->db->trans_complete();
    
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }
    
            // ✅ PERBAIKAN: Kirim notifikasi yang sesungguhnya
            $this->_send_comprehensive_scheduling_notifications($seminar_id, $update_data);
    
            $this->session->set_flashdata('success', 'Jadwal seminar berhasil disimpan dan notifikasi telah dikirim ke semua pihak!');
    
        } catch (Exception $e) {
            log_message('error', 'Simpan jadwal error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            if (isset($seminar_id)) {
                redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
                return;
            }
        }
    
        redirect('kaprodi/seminar_skripsi');
    }

    // ================================================================
    // HELPER METHODS - DISESUAIKAN DENGAN DATABASE STRUCTURE YANG BENAR
    // ================================================================

    /**
     * ✅ FIXED: Get seminar skripsi list dengan JOIN yang benar
     * Relasi: seminar_skripsi_mahasiswa -> proposal_mahasiswa -> mahasiswa -> prodi
     */
    private function _get_seminar_skripsi_list() {
        try {
            // ✅ QUERY DIPERLUAS - Tambah data dosen penguji untuk info jadwal
            $sql = "
                SELECT 
                    ss.id,
                    ss.proposal_id,
                    ss.mahasiswa_id,
                    ss.status,
                    ss.current_step,
                    ss.judul_skripsi,
                    ss.file_skripsi,
                    ss.status_pembimbing,
                    ss.status_kaprodi,
                    ss.tanggal_seminar,
                    ss.jam_seminar,
                    ss.tempat_seminar,
                    ss.plagiarism_percentage,
                    ss.created_at,
                    m.nim,
                    m.nama as nama_mahasiswa,
                    m.email as email_mahasiswa,
                    m.prodi_id,
                    pm.judul as judul_proposal,
                    pm.dosen_id as pembimbing_id,
                    d.nama as nama_pembimbing,
                    d.email as email_pembimbing,
                    d1.nama as nama_penguji1,
                    d1.email as email_penguji1,
                    d2.nama as nama_penguji2,
                    d2.email as email_penguji2,
                    pr.nama as nama_prodi
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id  
                LEFT JOIN prodi pr ON m.prodi_id = pr.id
                LEFT JOIN dosen d ON pm.dosen_id = d.id
                LEFT JOIN dosen d1 ON ss.dosen_penguji1_id = d1.id
                LEFT JOIN dosen d2 ON ss.dosen_penguji2_id = d2.id
                WHERE m.prodi_id = ? OR m.prodi_id IS NULL
                ORDER BY ss.created_at DESC
                LIMIT 100
            ";
            
            $query = $this->db->query($sql, [$this->prodi_id]);
            
            if ($query) {
                return $query->result();
            }
            
            return [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar skripsi list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Enhanced _get_seminar_detail - REPLACE existing method
     * Menambah judul comparison logic
     */
    private function _get_seminar_detail($seminar_id) {
        try {
            // Query existing (UNCHANGED)
            $sql = "
                SELECT 
                    ss.*,
                    m.nim, 
                    m.nama as nama_mahasiswa, 
                    m.email as email_mahasiswa,
                    m.prodi_id,
                    pm.judul as judul_proposal_original, 
                    pm.dosen_id as pembimbing_id,
                    d.nama as nama_pembimbing, 
                    d.email as email_pembimbing,
                    d1.nama as nama_penguji1,
                    d2.nama as nama_penguji2,
                    pr.nama as nama_prodi
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id
                LEFT JOIN prodi pr ON m.prodi_id = pr.id
                LEFT JOIN dosen d ON pm.dosen_id = d.id
                LEFT JOIN dosen d1 ON ss.dosen_penguji1_id = d1.id
                LEFT JOIN dosen d2 ON ss.dosen_penguji2_id = d2.id
                WHERE ss.id = ? AND (m.prodi_id = ? OR m.prodi_id IS NULL)
                LIMIT 1
            ";
            
            $query = $this->db->query($sql, [$seminar_id, $this->prodi_id]);
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                
                // NEW: Enhanced judul comparison logic
                $result->judul_current = !empty($result->judul_skripsi) ? $result->judul_skripsi : $result->judul_proposal_original;
                $result->is_judul_changed = !empty($result->judul_skripsi) && 
                                           ($result->judul_skripsi !== $result->judul_proposal_original);
                
                // NEW: Calculate similarity percentage
                if ($result->is_judul_changed) {
                    $result->judul_similarity = $this->_calculate_title_similarity(
                        $result->judul_proposal_original, 
                        $result->judul_skripsi
                    );
                } else {
                    $result->judul_similarity = 100;
                }
                
                return $result;
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Enhanced get seminar detail error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ FIXED: Get statistics menggunakan view atau manual query yang benar
     */
    private function _get_statistics_from_view() {
        try {
            // Coba gunakan view seminar_skripsi_progress_v jika ada
            if ($this->db->table_exists('seminar_skripsi_progress_v')) {
                $query = $this->db->get('seminar_skripsi_progress_v');
                
                if ($query && $query->num_rows() > 0) {
                    $result = $query->row();
                    
                    return [
                        'pending_review' => $result->review_kaprodi_count ?? 0,
                        'approved_month' => $result->approved_count ?? 0,
                        'rejected_month' => $result->rejected_count ?? 0,
                        'scheduled' => $result->scheduled_count ?? 0,
                        'completed' => $result->completed_count ?? 0,
                        'total_mahasiswa' => $result->total_mahasiswa ?? 0,
                        'avg_progress' => $result->avg_progress_percentage ?? 0
                    ];
                }
            }
            
            // Fallback manual query dengan JOIN yang benar
            return $this->_get_statistics_manual();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics from view: ' . $e->getMessage());
            return $this->_get_statistics_manual();
        }
    }

    /**
     * ✅ FIXED: Get statistics manual dengan JOIN yang benar
     */
    private function _get_statistics_manual() {
        try {
            $stats = [
                'pending_review' => 0,
                'approved_month' => 0,
                'rejected_month' => 0,
                'scheduled' => 0,
                'completed' => 0,
                'total_mahasiswa' => 0
            ];

            // ✅ COUNT DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
            $sql = "
                SELECT 
                    ss.status_kaprodi,
                    COUNT(*) as total
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id
                WHERE m.prodi_id = ? OR m.prodi_id IS NULL
                GROUP BY ss.status_kaprodi
            ";
            
            $query = $this->db->query($sql, [$this->prodi_id]);
            
            if ($query) {
                foreach ($query->result() as $row) {
                    switch ($row->status_kaprodi) {
                        case 'pending':
                            $stats['pending_review'] = $row->total;
                            break;
                        case 'approved':
                            $stats['approved_month'] = $row->total;
                            break;
                        case 'rejected':
                            $stats['rejected_month'] = $row->total;
                            break;
                    }
                }
            }

            return $stats;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting manual statistics: ' . $e->getMessage());
            return [
                'pending_review' => 0,
                'approved_month' => 0,
                'rejected_month' => 0,
                'scheduled' => 0,
                'completed' => 0,
                'total_mahasiswa' => 0
            ];
        }
    }

    /**
     * ✅ FIXED: Get pengajuan yang perlu review kaprodi dengan JOIN yang benar
     */
    private function _get_pengajuan_perlu_review() {
        try {
            // ✅ QUERY DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
            $sql = "
                SELECT COUNT(*) as total
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id
                WHERE ss.status = 'review_kaprodi' 
                AND ss.status_kaprodi = 'pending'
                AND (m.prodi_id = ? OR m.prodi_id IS NULL)
            ";
            
            $query = $this->db->query($sql, [$this->prodi_id]);
            $result = $query ? $query->row() : null;
            
            return $result ? $result->total : 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan review: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Handle upload file turnitin - METHOD BARU
     * Path sesuai yang sudah ditulis di views: uploads/turnitin/
     */
    private function _handle_turnitin_upload($seminar_id) {
        try {
            $upload_path = FCPATH . 'uploads/turnitin/';
            
            // Create directory if not exists
            if (!is_dir($upload_path)) {
                if (!mkdir($upload_path, 0755, true)) {
                    log_message('error', 'Cannot create turnitin upload directory');
                    return null;
                }
            }
    
            // Basic file validation
            if (empty($_FILES['file_turnitin']['name'])) {
                return null;
            }
    
            $file = $_FILES['file_turnitin'];
            
            // Check file size (5MB max)
            if ($file['size'] > 5 * 1024 * 1024) {
                log_message('error', 'Turnitin file too large: ' . $file['size']);
                return null;
            }
    
            // Check file type
            $allowed_types = ['pdf', 'doc', 'docx'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                log_message('error', 'Invalid turnitin file type: ' . $file_ext);
                return null;
            }
    
            // Generate secure filename
            $new_filename = 'turnitin_' . $seminar_id . '_' . date('YmdHis') . '.' . $file_ext;
            $target_path = $upload_path . $new_filename;
    
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                chmod($target_path, 0644);
                return $new_filename;
            } else {
                log_message('error', 'Failed to move turnitin upload file');
                return null;
            }
    
        } catch (Exception $e) {
            log_message('error', 'Turnitin upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ FIXED: Get seminar yang perlu dijadwalkan dengan JOIN yang benar
     */
    private function _get_seminar_perlu_dijadwalkan() {
        try {
            // ✅ QUERY DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
            $sql = "
                SELECT COUNT(*) as total
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id
                WHERE ss.status_kaprodi = 'approved' 
                AND ss.tanggal_seminar IS NULL
                AND (m.prodi_id = ? OR m.prodi_id IS NULL)
            ";
            
            $query = $this->db->query($sql, [$this->prodi_id]);
            $result = $query ? $query->row() : null;
            
            return $result ? $result->total : 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar perlu dijadwalkan: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ FIXED: Get jadwal mendatang dengan JOIN yang benar
     */
    private function _get_jadwal_mendatang() {
        try {
            // ✅ QUERY DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
            $sql = "
                SELECT 
                    ss.id,
                    ss.tanggal_seminar,
                    ss.jam_seminar,
                    ss.tempat_seminar,
                    m.nama as nama_mahasiswa,
                    m.nim,
                    ss.judul_skripsi
                FROM seminar_skripsi_mahasiswa ss
                LEFT JOIN proposal_mahasiswa pm ON ss.proposal_id = pm.id
                LEFT JOIN mahasiswa m ON ss.mahasiswa_id = m.id
                WHERE ss.tanggal_seminar >= CURDATE()
                AND ss.status = 'scheduled'
                AND (m.prodi_id = ? OR m.prodi_id IS NULL)
                ORDER BY ss.tanggal_seminar ASC, ss.jam_seminar ASC
                LIMIT 10
            ";
            
            $query = $this->db->query($sql, [$this->prodi_id]);
            
            return $query ? $query->result() : [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting jadwal mendatang: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ PERBAIKAN 1: Method _get_dosen_penguji() - Fix query kolom aktif
     * GANTI method existing dengan ini
     */
    private function _get_dosen_penguji() {
        try {
            $this->db->select('id, nama, email');
            $this->db->from('dosen');
            // ✅ FIX: Ganti 'aktif' dengan 'level'
            $this->db->where_in('level', ['2', '4']); // Level 2=dosen, 4=kaprodi
            $this->db->order_by('nama', 'ASC');
            
            $result = $this->db->get()->result();
            return $result ? $result : [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen penguji: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rekomendasi penguji dari seminar proposal - METHOD BARU
     * Untuk views penjadwalan yang sudah ada
     */
    private function _get_penguji_recommendations($proposal_id) {
        try {
            $sql = "
                SELECT 
                    spm.dosen_penguji1_id, 
                    spm.dosen_penguji2_id,
                    d1.nama as nama_penguji1,
                    d1.email as email_penguji1,
                    d2.nama as nama_penguji2,  
                    d2.email as email_penguji2,
                    spm.tanggal_seminar as tanggal_seminar_proposal
                FROM seminar_proposal_mahasiswa spm
                LEFT JOIN dosen d1 ON spm.dosen_penguji1_id = d1.id
                LEFT JOIN dosen d2 ON spm.dosen_penguji2_id = d2.id
                WHERE spm.proposal_id = ? 
                AND spm.status = 'completed'
                ORDER BY spm.tanggal_seminar DESC
                LIMIT 1
            ";
            
            $query = $this->db->query($sql, [$proposal_id]);
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                
                $recommendations = [
                    'found' => false,
                    'penguji1' => null,
                    'penguji2' => null,
                    'tanggal_proposal' => $result->tanggal_seminar_proposal
                ];
                
                if (!empty($result->dosen_penguji1_id)) {
                    $recommendations['found'] = true;
                    $recommendations['penguji1'] = [
                        'id' => $result->dosen_penguji1_id,
                        'nama' => $result->nama_penguji1,
                        'email' => $result->email_penguji1
                    ];
                }
                
                if (!empty($result->dosen_penguji2_id)) {
                    $recommendations['found'] = true;
                    $recommendations['penguji2'] = [
                        'id' => $result->dosen_penguji2_id,
                        'nama' => $result->nama_penguji2,
                        'email' => $result->email_penguji2
                    ];
                }
                
                return $recommendations;
            }
            
            return ['found' => false, 'penguji1' => null, 'penguji2' => null];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting penguji recommendations: ' . $e->getMessage());
            return ['found' => false, 'penguji1' => null, 'penguji2' => null];
        }
    }

    /**
     * Process validation berdasarkan database structure
     */
    private function _process_validation($seminar_id, $decision, $plagiarism_percentage, $komentar) {
        try {
            $this->db->trans_start();
            
            $update_data = [
                'status_kaprodi' => $decision,
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($decision == 'approved') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'staf';
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }
            
            if ($plagiarism_percentage !== null && $plagiarism_percentage !== '') {
                $update_data['plagiarism_percentage'] = $plagiarism_percentage;
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            return $this->db->trans_status();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Process validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Save jadwal seminar
     */
    private function _save_jadwal($seminar_id, $tanggal_seminar, $jam_seminar, $tempat_seminar, $dosen_penguji1_id, $dosen_penguji2_id) {
        try {
            $this->db->trans_start();
            
            $update_data = [
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'status' => 'scheduled',
                'current_step' => 'staf',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($dosen_penguji1_id) {
                $update_data['dosen_penguji1_id'] = $dosen_penguji1_id;
            }
            
            if ($dosen_penguji2_id) {
                $update_data['dosen_penguji2_id'] = $dosen_penguji2_id;
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            return $this->db->trans_status();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Save jadwal error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set prodi_id dari session
     */
    private function _set_prodi_id() {
        try {
            $kaprodi = $this->db->select('id')
                               ->from('prodi')
                               ->where('dosen_id', $this->session->userdata('id'))
                               ->get()
                               ->row();
            
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error setting prodi_id: ' . $e->getMessage());
            $this->prodi_id = 1; // Default fallback
        }
    }

    /**
     * Calculate title similarity percentage - METHOD BARU
     */
    private function _calculate_title_similarity($original, $new) {
        if (empty($original) || empty($new)) {
            return 0;
        }
        
        // Clean and split titles
        $original_clean = strtolower(preg_replace('/[^\w\s]/', '', $original));
        $new_clean = strtolower(preg_replace('/[^\w\s]/', '', $new));
        
        $original_words = array_filter(explode(' ', $original_clean));
        $new_words = array_filter(explode(' ', $new_clean));
        
        if (empty($original_words) || empty($new_words)) {
            return 0;
        }
        
        // Calculate Jaccard similarity
        $intersection = array_intersect($original_words, $new_words);
        $union = array_unique(array_merge($original_words, $new_words));
        
        return count($union) > 0 ? round((count($intersection) / count($union)) * 100, 1) : 0;
    }

    /**
     * Enhanced process validation dengan file handling - METHOD BARU
     */
    private function _process_validation_with_file($seminar_id, $decision, $plagiarism_percentage, $komentar, $uploaded_file = null) {
        try {
            $this->db->trans_start();
            
            // Data update existing (UNCHANGED)
            $update_data = [
                'status_kaprodi' => $decision,
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($decision == 'approved') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'staf';
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }
            
            if ($plagiarism_percentage !== null && $plagiarism_percentage !== '') {
                $update_data['plagiarism_percentage'] = $plagiarism_percentage;
            }
            
            // NEW: Save uploaded turnitin file
            if ($uploaded_file) {
                $update_data['file_turnitin'] = $uploaded_file;
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            return $this->db->trans_status();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Enhanced validation process error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send comprehensive notifications - METHOD BARU
     */
    private function _send_comprehensive_notifications($seminar_id, $decision) {
        try {
            $seminar = $this->_get_seminar_detail($seminar_id);
            if (!$seminar) return false;
            
            $config = [
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
            
            $this->email->initialize($config);
            
            // 1. Email ke mahasiswa
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            
            if ($decision == 'approved') {
                $this->email->subject('✅ Seminar Skripsi Disetujui - ' . $seminar->nama_mahasiswa);
                $message = $this->_build_approval_email($seminar);
            } else {
                $this->email->subject('❌ Seminar Skripsi Perlu Perbaikan - ' . $seminar->nama_mahasiswa);
                $message = $this->_build_rejection_email($seminar);
            }
            
            $this->email->message($message);
            $email_sent = $this->email->send();
            
            // 2. Email ke dosen pembimbing
            if (!empty($seminar->email_pembimbing)) {
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
                $this->email->to($seminar->email_pembimbing);
                $this->email->subject('📋 Status Seminar Skripsi Mahasiswa - ' . $seminar->nama_mahasiswa);
                
                $message_dosen = $this->_build_notification_email_dosen($seminar, $decision);
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            return $email_sent;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending comprehensive notifications: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build email templates - METHODS BARU
     */
    private function _build_approval_email($seminar) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #28a745; color: white; padding: 20px; text-align: center;'>
                <h2>✅ Seminar Skripsi Disetujui</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Selamat! Pengajuan seminar skripsi Anda telah <strong>DISETUJUI</strong> oleh Ketua Program Studi.</p>
                <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>📚 Detail Seminar:</h4>
                    <ul>
                        <li><strong>Judul:</strong> " . ($seminar->judul_current ?? 'N/A') . "</li>
                        <li><strong>Status:</strong> Menunggu Penjadwalan</li>
                    </ul>
                </div>
                <p>Anda akan menerima email pemberitahuan jadwal seminar setelah Kaprodi menetapkan jadwal.</p>
            </div>
        </div>";
    }
    
    private function _build_rejection_email($seminar) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                <h2>❌ Seminar Skripsi Perlu Perbaikan</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Pengajuan seminar skripsi Anda <strong>PERLU DIPERBAIKI</strong>.</p>
                <div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>📝 Catatan:</h4>
                    <p>" . ($seminar->komentar_kaprodi ?? 'Silakan konsultasi dengan dosen pembimbing.') . "</p>
                </div>
                <p>Silakan lakukan perbaikan dan submit ulang pengajuan Anda.</p>
            </div>
        </div>";
    }
    
    private function _build_notification_email_dosen($seminar, $decision) {
        $status_text = $decision == 'approved' ? 'DISETUJUI' : 'DITOLAK';
        $bg_color = $decision == 'approved' ? '#28a745' : '#dc3545';
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: {$bg_color}; color: white; padding: 20px; text-align: center;'>
                <h2>📋 Status Seminar Skripsi Mahasiswa</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                <p>Seminar skripsi mahasiswa bimbingan Anda telah <strong>{$status_text}</strong> oleh Kaprodi.</p>
                <div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>👨‍🎓 Detail Mahasiswa:</h4>
                    <ul>
                        <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                        <li><strong>NIM:</strong> {$seminar->nim}</li>
                        <li><strong>Judul:</strong> " . ($seminar->judul_current ?? 'N/A') . "</li>
                        <li><strong>Status:</strong> {$status_text}</li>
                    </ul>
                </div>
            </div>
        </div>";
    }
    
    /**
     * ✅ Main method untuk kirim notifikasi validasi
     */
    private function _send_validation_notifications($seminar_id, $decision, $plagiarism_score) {
        try {
            // Get fresh seminar data untuk email
            $seminar = $this->_get_seminar_detail($seminar_id);
            if (!$seminar) {
                log_message('error', 'Cannot send notifications: seminar data not found');
                return false;
            }
            
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
            
            if ($decision === 'approved') {
                // Kirim email disetujui ke mahasiswa dan dosen
                $this->_kirim_email_skripsi_disetujui($seminar, $plagiarism_score);
            } else {
                // Kirim email ditolak ke mahasiswa dan dosen  
                $this->_kirim_email_skripsi_ditolak($seminar, $plagiarism_score);
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending validation notifications: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ Kirim email ke mahasiswa saat skripsi disetujui
     */
    private function _kirim_email_skripsi_disetujui($seminar, $plagiarism_score) {
        try {
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('✅ Seminar Skripsi Disetujui Kaprodi - ' . $seminar->nama_mahasiswa);
            
            // ✅ PERBAIKAN: Gunakan variable lokal untuk judul (sesuai catatan chat sebelumnya)
            $judul_seminar = $seminar->judul_current ?? $seminar->judul_skripsi ?? $seminar->judul_proposal_original ?? 'Judul Skripsi';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Seminar Skripsi Disetujui</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Selamat <strong>{$seminar->nama_mahasiswa}</strong>!</p>
                    
                    <p>Pengajuan seminar skripsi Anda telah <strong>DISETUJUI</strong> oleh Kaprodi setelah melalui validasi plagiarisme.</p>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>📚 Detail Seminar:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Judul:</strong> {$judul_seminar}</li>
                            <li><strong>Persentase Plagiarisme:</strong> " . number_format($plagiarism_score, 1) . "% (Memenuhi syarat)</li>
                            <li><strong>Status:</strong> Disetujui untuk dijadwalkan</li>
                            <li><strong>Tanggal Persetujuan:</strong> " . date('d/m/Y H:i') . "</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>⏭️ Tahap Selanjutnya:</h4>
                        <p style='color: #856404; margin: 0;'>
                            Penjadwalan seminar akan diatur oleh Kaprodi. Anda akan mendapat notifikasi lebih lanjut.
                        </p>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('mahasiswa/seminar_skripsi') . "' 
                           style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📱 Cek Status di SIM-TA
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Mohon segera persiapkan presentasi dan dokumen yang diperlukan untuk seminar skripsi.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result_mahasiswa = $this->email->send();
            
            if (!$result_mahasiswa) {
                log_message('error', 'Failed to send approval email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            // ✅ Kirim juga ke dosen pembimbing
            $this->_kirim_email_skripsi_disetujui_dosen($seminar, $plagiarism_score);
            
            return $result_mahasiswa;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending skripsi approval email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ Kirim email ke dosen saat skripsi disetujui
     */
    private function _kirim_email_skripsi_disetujui_dosen($seminar, $plagiarism_score) {
        try {
            // Skip jika tidak ada email dosen
            if (empty($seminar->email_pembimbing)) {
                log_message('info', 'Skipping dosen notification: no email found');
                return true;
            }
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_pembimbing);
            $this->email->subject('✅ Seminar Skripsi Mahasiswa Bimbingan Disetujui - ' . $seminar->nama_mahasiswa);
            
            // ✅ PERBAIKAN: Gunakan variable lokal untuk judul
            $judul_seminar = $seminar->judul_current ?? $seminar->judul_skripsi ?? $seminar->judul_proposal_original ?? 'Judul Skripsi';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Seminar Skripsi Mahasiswa Bimbingan Disetujui</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                    
                    <p>Pengajuan seminar skripsi mahasiswa bimbingan Anda telah <strong>DISETUJUI</strong> oleh Kaprodi.</p>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>👨‍🎓 Data Mahasiswa:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$seminar->nim}</li>
                            <li><strong>Judul:</strong> {$judul_seminar}</li>
                            <li><strong>Plagiarisme:</strong> " . number_format($plagiarism_score, 1) . "%</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>⏭️ Informasi:</h4>
                        <p style='color: #856404; margin: 0;'>
                            Seminar akan dijadwalkan oleh Kaprodi. Anda akan mendapat pemberitahuan jadwal lebih lanjut.
                        </p>
                    </div>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Mohon bantu persiapan mahasiswa untuk seminar skripsi.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send approval email to dosen: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending skripsi approval email to dosen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ Kirim email ke mahasiswa saat skripsi ditolak
     */
    private function _kirim_email_skripsi_ditolak($seminar, $plagiarism_score) {
        try {
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('⚠️ Seminar Skripsi Perlu Perbaikan - ' . $seminar->nama_mahasiswa);
            
            // ✅ PERBAIKAN: Gunakan variable lokal untuk judul
            $judul_seminar = $seminar->judul_current ?? $seminar->judul_skripsi ?? $seminar->judul_proposal_original ?? 'Judul Skripsi';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>⚠️ Seminar Skripsi Perlu Perbaikan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Mohon maaf, pengajuan seminar skripsi Anda <strong>PERLU DIPERBAIKI</strong> berdasarkan hasil validasi Kaprodi.</p>
                    
                    <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>📚 Detail Seminar:</h4>
                        <ul style='color: #721c24; margin: 0;'>
                            <li><strong>Judul:</strong> {$judul_seminar}</li>
                            <li><strong>Persentase Plagiarisme:</strong> " . number_format($plagiarism_score, 1) . "%</li>
                            <li><strong>Tanggal Review:</strong> " . date('d/m/Y H:i') . "</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Catatan Kaprodi:</h4>
                        <p style='color: #856404; margin: 0;'>" . nl2br(htmlspecialchars($seminar->komentar_kaprodi ?? 'Silakan lakukan perbaikan sesuai standar plagiarisme yang ditetapkan.')) . "</p>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>⏭️ Tindak Lanjut:</h4>
                        <ol style='color: #004085; margin: 0;'>
                            <li>Lakukan perbaikan sesuai catatan Kaprodi</li>
                            <li>Konsultasi dengan dosen pembimbing</li>
                            <li>Ajukan ulang setelah perbaikan selesai</li>
                        </ol>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('mahasiswa/seminar_skripsi') . "' 
                           style='background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📱 Lihat Detail di SIM-TA
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Jangan menyerah! Lakukan perbaikan dan ajukan kembali.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result_mahasiswa = $this->email->send();
            
            if (!$result_mahasiswa) {
                log_message('error', 'Failed to send rejection email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            // ✅ Kirim juga ke dosen pembimbing
            $this->_kirim_email_skripsi_ditolak_dosen($seminar, $plagiarism_score);
            
            return $result_mahasiswa;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending skripsi rejection email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ Kirim email ke dosen saat skripsi ditolak
     */
    private function _kirim_email_skripsi_ditolak_dosen($seminar, $plagiarism_score) {
        try {
            // Skip jika tidak ada email dosen
            if (empty($seminar->email_pembimbing)) {
                log_message('info', 'Skipping dosen notification: no email found');
                return true;
            }
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_pembimbing);
            $this->email->subject('⚠️ Seminar Skripsi Mahasiswa Bimbingan Ditolak - ' . $seminar->nama_mahasiswa);
            
            // ✅ PERBAIKAN: Gunakan variable lokal untuk judul
            $judul_seminar = $seminar->judul_current ?? $seminar->judul_skripsi ?? $seminar->judul_proposal_original ?? 'Judul Skripsi';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>⚠️ Seminar Skripsi Mahasiswa Bimbingan Ditolak</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                    
                    <p>Pengajuan seminar skripsi mahasiswa bimbingan Anda <strong>DITOLAK</strong> oleh Kaprodi.</p>
                    
                    <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>👨‍🎓 Data Mahasiswa:</h4>
                        <ul style='color: #721c24; margin: 0;'>
                            <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$seminar->nim}</li>
                            <li><strong>Judul:</strong> {$judul_seminar}</li>
                            <li><strong>Plagiarisme:</strong> " . number_format($plagiarism_score, 1) . "%</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Catatan Kaprodi:</h4>
                        <p style='color: #856404; margin: 0;'>" . nl2br(htmlspecialchars($seminar->komentar_kaprodi ?? 'Silakan bantu mahasiswa melakukan perbaikan sesuai standar yang ditetapkan.')) . "</p>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>⏭️ Harap Bantu:</h4>
                        <p style='color: #004085; margin: 0;'>
                            Mohon bantu mahasiswa melakukan perbaikan sesuai catatan Kaprodi agar dapat mengajukan ulang.
                        </p>
                    </div>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Terima kasih atas bimbingannya.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send rejection email to dosen: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending skripsi rejection email to dosen: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ REPLACE METHOD _send_comprehensive_scheduling_notifications()
     * GANTI method ini dengan script di bawah (HAPUS method lama, PASTE yang ini)
     */
    private function _send_comprehensive_scheduling_notifications($seminar_id, $jadwal_data) {
        try {
            // ✅ Get data lengkap seminar dengan dosen (QUERY TIDAK DIUBAH)
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                d1.nama as nama_penguji1, d1.email as email_penguji1,
                d2.nama as nama_penguji2, d2.email as email_penguji2
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            
            $seminar = $this->db->get()->row();
            
            if (!$seminar) {
                log_message('error', 'Seminar data not found for notifications: ID ' . $seminar_id);
                return false;
            }
    
            // ✅ PASTIKAN DATA EMAIL LENGKAP
            if (empty($seminar->email_mahasiswa)) {
                log_message('error', 'Email mahasiswa tidak ditemukan untuk seminar ID: ' . $seminar_id);
                return false;
            }
    
            // ✅ Konfigurasi email (TIDAK DIUBAH)
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
    
            // ✅ TRACKING HASIL EMAIL
            $email_results = [];
            $success_count = 0;
            $total_count = 0;
    
            // ✅ 1. Email ke mahasiswa (WAJIB)
            $total_count++;
            $result_mahasiswa = $this->_kirim_email_jadwal_mahasiswa($seminar);
            $email_results['mahasiswa'] = $result_mahasiswa;
            if ($result_mahasiswa) $success_count++;
    
            // ✅ 2. Email ke dosen pembimbing (jika ada email)
            if (!empty($seminar->email_pembimbing)) {
                $total_count++;
                $result_pembimbing = $this->_kirim_email_jadwal_pembimbing($seminar);
                $email_results['pembimbing'] = $result_pembimbing;
                if ($result_pembimbing) $success_count++;
            }
    
            // ✅ 3. Email ke dosen penguji 1 (jika ada email)
            if (!empty($seminar->email_penguji1)) {
                $total_count++;
                $result_penguji1 = $this->_kirim_email_jadwal_penguji($seminar, 'Penguji 1');
                $email_results['penguji1'] = $result_penguji1;
                if ($result_penguji1) $success_count++;
            }
    
            // ✅ 4. Email ke dosen penguji 2 (jika ada email)
            if (!empty($seminar->email_penguji2)) {
                $total_count++;
                $result_penguji2 = $this->_kirim_email_jadwal_penguji($seminar, 'Penguji 2');
                $email_results['penguji2'] = $result_penguji2;
                if ($result_penguji2) $success_count++;
            }
    
            // ✅ 5. Email ke staf (coba kirim ke semua staf aktif)
            $total_count++;
            $result_staf = $this->_kirim_email_jadwal_staf($seminar);
            $email_results['staf'] = $result_staf;
            if ($result_staf) $success_count++;
    
            // ✅ LOG HASIL LENGKAP
            log_message('info', sprintf(
                'Scheduling notifications completed - Seminar ID: %d, Success: %d/%d emails sent',
                $seminar_id, $success_count, $total_count
            ));
    
            // ✅ LOG DETAIL HASIL PER PENERIMA
            foreach ($email_results as $recipient => $result) {
                if ($result) {
                    log_message('info', "✅ Email berhasil dikirim ke: {$recipient}");
                } else {
                    log_message('warning', "❌ Email gagal dikirim ke: {$recipient}");
                }
            }
    
            // Return true jika minimal 1 email berhasil (prioritas mahasiswa)
            return $success_count > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending comprehensive scheduling notifications: ' . $e->getMessage());
            return false;
        }
    }
        
    /**
     * ✅ ENHANCE METHOD _kirim_email_jadwal_mahasiswa()
     * JIKA METHOD INI SUDAH ADA, GANTI dengan yang ini. JIKA BELUM ADA, TAMBAHKAN.
     */
    private function _kirim_email_jadwal_mahasiswa($seminar) {
        try {
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('📅 Jadwal Seminar Skripsi Telah Ditetapkan - ' . $seminar->nama_mahasiswa);
            
            $tanggal_formatted = date('l, d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📅 Jadwal Seminar Skripsi Ditetapkan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    
                    <p>Selamat! Jadwal seminar skripsi Anda telah <strong>DITETAPKAN</strong> oleh Kaprodi.</p>
                    
                    <div style='background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 15px 0;'>📋 INFORMASI SEMINAR</h4>
                        <table style='width: 100%; color: #155724;'>
                            <tr><td width='30%'><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>" . htmlspecialchars($seminar->judul ?? 'N/A') . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 15px 0;'>👥 TIM PENGUJI</h4>
                        <ul style='color: #004085; margin: 0;'>
                            <li><strong>Pembimbing:</strong> " . htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') . "</li>
                            <li><strong>Penguji 1:</strong> " . htmlspecialchars($seminar->nama_penguji1 ?? 'N/A') . "</li>
                            <li><strong>Penguji 2:</strong> " . htmlspecialchars($seminar->nama_penguji2 ?? 'N/A') . "</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>⚠️ PERSIAPAN SEMINAR</h4>
                        <ul style='color: #856404; margin: 0;'>
                            <li>Siapkan presentasi PowerPoint</li>
                            <li>Siapkan dokumen skripsi lengkap</li>
                            <li>Hadir 15 menit sebelum jadwal</li>
                            <li>Bawa alat tulis dan dokumen pendukung</li>
                        </ul>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('mahasiswa/seminar_skripsi') . "' 
                           style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📱 Lihat Detail di SIM-TA
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Semoga sukses dengan seminar skripsi Anda! 🎓
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send schedule email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending schedule email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ENHANCE METHOD _kirim_email_jadwal_pembimbing()
     * JIKA METHOD INI SUDAH ADA, GANTI dengan yang ini. JIKA BELUM ADA, TAMBAHKAN.
     */
    private function _kirim_email_jadwal_pembimbing($seminar) {
        try {
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_pembimbing);
            $this->email->subject('📅 Jadwal Seminar Skripsi Mahasiswa Bimbingan - ' . $seminar->nama_mahasiswa);
            
            $tanggal_formatted = date('l, d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📅 Jadwal Seminar Skripsi Mahasiswa Bimbingan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
                    
                    <p>Kaprodi telah menetapkan jadwal seminar skripsi untuk mahasiswa bimbingan Anda.</p>
                    
                    <div style='background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 15px 0;'>👨‍🎓 DATA MAHASISWA</h4>
                        <table style='width: 100%; color: #155724;'>
                            <tr><td width='25%'><strong>Nama:</strong></td><td>{$seminar->nama_mahasiswa}</td></tr>
                            <tr><td><strong>NIM:</strong></td><td>{$seminar->nim}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>" . htmlspecialchars($seminar->judul ?? 'N/A') . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 15px 0;'>📋 JADWAL SEMINAR</h4>
                        <table style='width: 100%; color: #004085;'>
                            <tr><td width='25%'><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 15px 0;'>👥 TIM PENGUJI</h4>
                        <ul style='color: #856404; margin: 0;'>
                            <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing} (Anda)</li>
                            <li><strong>Penguji 1:</strong> " . htmlspecialchars($seminar->nama_penguji1 ?? 'N/A') . "</li>
                            <li><strong>Penguji 2:</strong> " . htmlspecialchars($seminar->nama_penguji2 ?? 'N/A') . "</li>
                        </ul>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('dosen/seminar_skripsi') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📱 Lihat Detail di SIM-TA
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Terima kasih atas bimbingannya untuk mahasiswa ini.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Failed to send schedule email to pembimbing: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending schedule email to pembimbing: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ENHANCE METHOD _kirim_email_jadwal_penguji()
     * JIKA METHOD INI SUDAH ADA, GANTI dengan yang ini. JIKA BELUM ADA, TAMBAHKAN.
     */
    private function _kirim_email_jadwal_penguji($seminar, $role_penguji) {
        try {
            // Tentukan email dan nama penguji berdasarkan role
            $email_penguji = ($role_penguji == 'Penguji 1') ? $seminar->email_penguji1 : $seminar->email_penguji2;
            $nama_penguji = ($role_penguji == 'Penguji 1') ? $seminar->nama_penguji1 : $seminar->nama_penguji2;
            
            if (empty($email_penguji)) {
                log_message('warning', "Email {$role_penguji} tidak ditemukan");
                return false;
            }
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($email_penguji);
            $this->email->subject('📅 Penunjukan Sebagai ' . $role_penguji . ' Seminar Skripsi - ' . $seminar->nama_mahasiswa);
            
            $tanggal_formatted = date('l, d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📅 Penunjukan Sebagai {$role_penguji}</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$nama_penguji}</strong>,</p>
                    
                    <p>Anda ditunjuk sebagai <strong>{$role_penguji}</strong> untuk seminar skripsi mahasiswa berikut:</p>
                    
                    <div style='background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 15px 0;'>👨‍🎓 DATA MAHASISWA</h4>
                        <table style='width: 100%; color: #155724;'>
                            <tr><td width='25%'><strong>Nama:</strong></td><td>{$seminar->nama_mahasiswa}</td></tr>
                            <tr><td><strong>NIM:</strong></td><td>{$seminar->nim}</td></tr>
                            <tr><td><strong>Judul:</strong></td><td>" . htmlspecialchars($seminar->judul ?? 'N/A') . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 15px 0;'>📋 JADWAL SEMINAR</h4>
                        <table style='width: 100%; color: #004085;'>
                            <tr><td width='25%'><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                            <tr><td><strong>Peran Anda:</strong></td><td><strong>{$role_penguji}</strong></td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 15px 0;'>👥 TIM PENGUJI LENGKAP</h4>
                        <ul style='color: #856404; margin: 0;'>
                            <li><strong>Pembimbing:</strong> " . htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') . "</li>
                            <li><strong>Penguji 1:</strong> " . htmlspecialchars($seminar->nama_penguji1 ?? 'N/A') . "</li>
                            <li><strong>Penguji 2:</strong> " . htmlspecialchars($seminar->nama_penguji2 ?? 'N/A') . "</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>ℹ️ CATATAN PENTING</h4>
                        <p style='color: #721c24; margin: 0;'>
                            Sesuai kebijakan STK Santo Yakobus, penunjukan dosen penguji berlaku otomatis. 
                            Mohon untuk hadir sesuai jadwal yang telah ditetapkan.
                        </p>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('dosen/seminar_skripsi') . "' 
                           style='background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📱 Lihat Detail di SIM-TA
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Terima kasih atas kesediaan Anda sebagai penguji.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', "Failed to send schedule email to {$role_penguji}: " . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', "Error sending schedule email to {$role_penguji}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ENHANCE METHOD _kirim_email_jadwal_staf()
     * JIKA METHOD INI SUDAH ADA, GANTI dengan yang ini. JIKA BELUM ADA, TAMBAHKAN.
     */
    private function _kirim_email_jadwal_staf($seminar) {
        try {
            // Get email staf aktif
            $this->db->select('email, nama');
            $this->db->from('users');
            $this->db->where('level', '1'); // Level 1 = staf
            $this->db->where('status', '1'); // Status aktif
            $staf_emails = $this->db->get()->result();
            
            if (empty($staf_emails)) {
                log_message('warning', 'No active staff emails found for notification');
                return false;
            }
            
            $tanggal_formatted = date('l, d F Y', strtotime($seminar->tanggal_seminar));
            $jam_formatted = date('H:i', strtotime($seminar->jam_seminar));
            
            $sent_count = 0;
            $total_staf = count($staf_emails);
            
            foreach ($staf_emails as $staf) {
                if (!empty($staf->email) && filter_var($staf->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $this->email->clear();
                        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhil STK Santo Yakobus');
                        $this->email->to($staf->email);
                        $this->email->subject('📅 Info Jadwal Seminar Skripsi - ' . $seminar->nama_mahasiswa);
                        
                        $message = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                            <div style='background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 20px; text-align: center;'>
                                <h2 style='margin: 0;'>📅 Informasi Jadwal Seminar Skripsi</h2>
                            </div>
                            
                            <div style='padding: 20px; background-color: #f8f9fa;'>
                                <p>Kepada Yth. <strong>Tim Staf Akademik</strong>,</p>
                                
                                <p>Kaprodi telah menetapkan jadwal seminar skripsi. Berikut informasinya:</p>
                                
                                <div style='background-color: #e2e3e5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                                    <h4 style='margin: 0 0 15px 0;'>📋 INFORMASI LENGKAP</h4>
                                    <table style='width: 100%;'>
                                        <tr><td width='25%'><strong>Mahasiswa:</strong></td><td>{$seminar->nama_mahasiswa} ({$seminar->nim})</td></tr>
                                        <tr><td><strong>Tanggal:</strong></td><td>{$tanggal_formatted}</td></tr>
                                        <tr><td><strong>Waktu:</strong></td><td>{$jam_formatted} WIB</td></tr>
                                        <tr><td><strong>Tempat:</strong></td><td>{$seminar->tempat_seminar}</td></tr>
                                        <tr><td><strong>Pembimbing:</strong></td><td>" . htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') . "</td></tr>
                                        <tr><td><strong>Penguji 1:</strong></td><td>" . htmlspecialchars($seminar->nama_penguji1 ?? 'N/A') . "</td></tr>
                                        <tr><td><strong>Penguji 2:</strong></td><td>" . htmlspecialchars($seminar->nama_penguji2 ?? 'N/A') . "</td></tr>
                                    </table>
                                </div>
                                
                                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                                    <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 TINDAK LANJUT</h4>
                                    <p style='color: #856404; margin: 0;'>
                                        Mohon bantuan untuk persiapan administrasi dan logistik seminar ini.
                                    </p>
                                </div>
                                
                                <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                                    Terima kasih atas dukungan administrasi untuk kegiatan akademik ini.
                                </p>
                            </div>
                            
                            <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                                <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                            </div>
                        </div>";
                        
                        $this->email->message($message);
                        
                        if ($this->email->send()) {
                            $sent_count++;
                        }
                        
                    } catch (Exception $e) {
                        log_message('error', 'Error sending email to staff: ' . $staf->email . ' - ' . $e->getMessage());
                    }
                }
            }
            
            log_message('info', "Staff notification result: {$sent_count}/{$total_staf} emails sent successfully");
            
            return $sent_count > 0; // Return true jika minimal 1 email terkirim
            
        } catch (Exception $e) {
            log_message('error', 'Error sending schedule email to staff: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create fallback model untuk mencegah crash
     */
    private function _create_fallback_model() {
        $this->seminar_model = (object) [
            'get_seminar_detail' => function($id) {
                return $this->_get_seminar_detail($id);
            },
            'get_seminar_list' => function() {
                return $this->_get_seminar_skripsi_list();
            }
        ];
    }
}