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
            // Existing validation (UNCHANGED)
            $seminar_id = $this->input->post('seminar_id');
            $decision = $this->input->post('decision');
            $plagiarism_percentage = $this->input->post('plagiarism_percentage');
            $komentar = $this->input->post('komentar_kaprodi');
    
            if (!$seminar_id || !$decision) {
                throw new Exception('Data tidak lengkap');
            }
    
            // NEW: Handle file turnitin upload (sesuai path di views)
            $uploaded_turnitin_file = null;
            if (!empty($_FILES['file_turnitin']['name'])) {
                $uploaded_turnitin_file = $this->_handle_turnitin_upload($seminar_id);
                if (!$uploaded_turnitin_file) {
                    throw new Exception('Gagal mengupload file hasil turnitin');
                }
            }
    
            // Enhanced process validation dengan file
            $result = $this->_process_validation_with_file($seminar_id, $decision, $plagiarism_percentage, $komentar, $uploaded_turnitin_file);
    
            if ($result) {
                $message = ($decision == 'approved') ? 'Seminar skripsi berhasil disetujui!' : 'Seminar skripsi ditolak.';
                $this->session->set_flashdata('success', $message);
                
                // Enhanced notifications
                $this->_send_comprehensive_notifications($seminar_id, $decision);
            } else {
                throw new Exception('Gagal memproses validasi');
            }
    
        } catch (Exception $e) {
            log_message('error', 'Enhanced validasi turnitin error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    
        redirect('kaprodi/seminar_skripsi');
    }

    /**
     * Enhanced penjadwalan method - UPDATE existing method
     * Menambah data rekomendasi penguji tanpa mengubah logic existing
     */
    public function penjadwalan($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
    
        try {
            // Logic existing (UNCHANGED)
            $seminar = $this->_get_seminar_detail($seminar_id);
            
            if (!$seminar || $seminar->status_kaprodi != 'approved') {
                $this->session->set_flashdata('error', 'Data tidak valid untuk penjadwalan!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }
    
            // Enhanced data dengan rekomendasi (NEW)
            $data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'seminar' => $seminar,
                'dosen_list' => $this->_get_dosen_penguji(),
                'penguji_recommendations' => $this->_get_penguji_recommendations($seminar->proposal_id) // NEW
            ];
    
            // Views loading (UNCHANGED)
            $content = $this->load->view('kaprodi/seminar_skripsi/penjadwalan', $data, TRUE);
            
            $template_data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);
    
        } catch (Exception $e) {
            log_message('error', 'Enhanced penjadwalan error: ' . $e->getMessage());
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
            // Path sesuai views existing
            $upload_path = FCPATH . 'uploads/turnitin/';
            
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
    
            $config = [
                'upload_path' => $upload_path,
                'allowed_types' => 'pdf|doc|docx|jpg|png', // Sesuai views
                'max_size' => 5120, // 5MB sesuai views  
                'encrypt_name' => true,
                'file_ext_tolower' => true
            ];
    
            $this->upload->initialize($config);
    
            if ($this->upload->do_upload('file_turnitin')) {
                $upload_data = $this->upload->data();
                
                // Rename dengan format yang jelas
                $new_name = 'turnitin_' . $seminar_id . '_' . date('YmdHis') . $upload_data['file_ext'];
                $old_path = $upload_path . $upload_data['file_name'];
                $new_path = $upload_path . $new_name;
                
                if (rename($old_path, $new_path)) {
                    return $new_name;
                }
                
                return $upload_data['file_name'];
                
            } else {
                log_message('error', 'Upload turnitin error: ' . $this->upload->display_errors());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Turnitin upload exception: ' . $e->getMessage());
            return false;
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