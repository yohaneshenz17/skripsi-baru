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
     * Mengatasi masalah redirect ke dashboard dengan proper error handling
     */
    public function index() {
        try {
            // Prepare data untuk view berdasarkan database structure yang benar
            $data = [
                'title' => 'Kelola Seminar Skripsi',
                'seminar_skripsi' => $this->_get_seminar_skripsi_list(),
                'pengajuan_review' => $this->_get_pengajuan_perlu_review(),
                'perlu_dijadwalkan' => $this->_get_seminar_perlu_dijadwalkan(),
                'jadwal_mendatang' => $this->_get_jadwal_mendatang(),
                'stats' => $this->_get_statistics_from_view()
            ];
            
            // ✅ FIX: Load view dengan error handling untuk prevent fallback
            try {
                $content = $this->load->view('kaprodi/seminar_skripsi/index', $data, TRUE);
            } catch (Exception $e) {
                // Jika view utama error, gunakan view sederhana
                log_message('error', 'Seminar_skripsi view error: ' . $e->getMessage());
                $content = $this->load->view('kaprodi/seminar_skripsi/index_simple', $data, TRUE);
            }
            
            // Data untuk template kaprodi
            $template_data = [
                'title' => 'Kelola Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);
            
        } catch (Exception $e) {
            log_message('error', 'Seminar_skripsi index critical error: ' . $e->getMessage());
            
            // Jangan redirect ke dashboard - tampilkan error page
            $error_data = [
                'title' => 'Error - Seminar Skripsi',
                'error_message' => 'Terjadi kesalahan sistem pada modul Seminar Skripsi.',
                'technical_error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ];
            
            $error_content = $this->load->view('errors/custom_error', $error_data, TRUE);
            
            $template_data = [
                'title' => 'Error - Seminar Skripsi',
                'content' => $error_content
            ];
            
            $this->load->view('template/kaprodi', $template_data);
        }
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
     * Validasi turnitin dan approve/reject seminar
     */
    public function validasi_turnitin() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
        }

        try {
            $seminar_id = $this->input->post('seminar_id');
            $decision = $this->input->post('decision');
            $plagiarism_percentage = $this->input->post('plagiarism_percentage');
            $komentar = $this->input->post('komentar_kaprodi');

            // Validasi input
            if (!$seminar_id || !$decision) {
                throw new Exception('Data tidak lengkap');
            }

            // Process validation
            $result = $this->_process_validation($seminar_id, $decision, $plagiarism_percentage, $komentar);

            if ($result) {
                $message = ($decision == 'approved') ? 'Seminar skripsi berhasil disetujui!' : 'Seminar skripsi ditolak.';
                $this->session->set_flashdata('success', $message);
            } else {
                throw new Exception('Gagal memproses validasi');
            }

        } catch (Exception $e) {
            log_message('error', 'Validasi turnitin error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        redirect('kaprodi/seminar_skripsi');
    }

    /**
     * Penjadwalan seminar
     */
    public function penjadwalan($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }

        try {
            $seminar = $this->_get_seminar_detail($seminar_id);
            
            if (!$seminar || $seminar->status_kaprodi != 'approved') {
                $this->session->set_flashdata('error', 'Data tidak valid untuk penjadwalan!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }

            $data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'seminar' => $seminar,
                'dosen_list' => $this->_get_dosen_penguji()
            ];

            $content = $this->load->view('kaprodi/seminar_skripsi/penjadwalan', $data, TRUE);
            
            $template_data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);

        } catch (Exception $e) {
            log_message('error', 'Penjadwalan error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('kaprodi/seminar_skripsi');
        }
    }

    /**
     * Simpan jadwal seminar
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
        }

        try {
            $seminar_id = $this->input->post('seminar_id');
            $tanggal_seminar = $this->input->post('tanggal_seminar');
            $jam_seminar = $this->input->post('jam_seminar');
            $tempat_seminar = $this->input->post('tempat_seminar');
            $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
            $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');

            // Validasi
            if (!$seminar_id || !$tanggal_seminar || !$jam_seminar || !$tempat_seminar) {
                throw new Exception('Data jadwal tidak lengkap');
            }

            // Save jadwal
            $result = $this->_save_jadwal($seminar_id, $tanggal_seminar, $jam_seminar, $tempat_seminar, $dosen_penguji1_id, $dosen_penguji2_id);

            if ($result) {
                $this->session->set_flashdata('success', 'Jadwal seminar berhasil disimpan!');
            } else {
                throw new Exception('Gagal menyimpan jadwal');
            }

        } catch (Exception $e) {
            log_message('error', 'Simpan jadwal error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            // ✅ QUERY DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
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
                    d2.nama as nama_penguji2,
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
     * ✅ FIXED: Get detail seminar dengan JOIN yang benar
     */
    private function _get_seminar_detail($seminar_id) {
        try {
            // ✅ QUERY DIPERBAIKI - Menggunakan m.prodi_id bukan pm.prodi_id
            $sql = "
                SELECT 
                    ss.*,
                    m.nim, 
                    m.nama as nama_mahasiswa, 
                    m.email as email_mahasiswa,
                    m.prodi_id,
                    pm.judul as judul_proposal, 
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
                return $query->row();
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
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
     * Get dosen penguji list
     */
    private function _get_dosen_penguji() {
        try {
            $query = $this->db->select('id, nama, email')
                             ->from('dosen')
                             ->where('aktif', 1)
                             ->order_by('nama')
                             ->get();
            
            return $query ? $query->result() : [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen penguji: ' . $e->getMessage());
            return [];
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