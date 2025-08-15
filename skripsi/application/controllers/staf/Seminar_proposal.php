<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staf Seminar Proposal Controller - READ-ONLY PENILAIAN VERSION
 * 
 * PERUBAHAN: 
 * - Method input_penilaian DIHAPUS/DISABLED
 * - Diganti dengan lihat_penilaian (READ-ONLY)
 * - Staf hanya bisa view penilaian yang diinput dosen
 * - Semua method existing lainnya TIDAK DIUBAH
 * 
 * File: application/controllers/staf/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.2 (READ-ONLY PENILAIAN)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // ✅ FIXED: Pastikan tidak ada output sebelum ini
        if (ob_get_level() == 0) ob_start();
        
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text', 'download']);
        
        // Load model yang sama dengan controller dosen yang stable
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        
        // Optional models dengan error handling
        try {
            $this->load->model('Staf_aktivitas_model', 'aktivitas_model');
        } catch (Exception $e) {
            log_message('debug', 'Staf_aktivitas_model not found, skipping...');
        }
        
        // Cek login dan level staf - FIXED: No redirect before headers
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            $this->session->set_flashdata('error', 'Akses ditolak. Silakan login sebagai staf.');
            redirect('auth/login');
            return;
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk staf
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function index() {
        try {
            // Ambil data dengan error handling
            $seminar_list = $this->_get_approved_seminar_list();
            $stats = $this->_get_dashboard_statistics();
            
            // Prepare data untuk view
            $view_data = [
                'seminar_list' => $seminar_list ?: [],
                'stats' => $stats ?: [],
                'page_title' => 'Seminar Proposal - Administrasi Staf'
            ];
            
            // Data untuk template staf.php
            $data = [
                'title' => 'Seminar Proposal',
                'content' => $this->load->view('staf/seminar_proposal/index', $view_data, TRUE)
            ];
            
            // Load template existing
            $this->load->view('template/staf', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error in seminar proposal index: ' . $e->getMessage());
            show_error('Terjadi kesalahan sistem. Silakan hubungi administrator.', 500);
        }
    }

    /**
     * Detail seminar proposal untuk staf
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function detail($seminar_id) {
        try {
            // Get detail seminar dengan validasi
            $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar_detail) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau belum terjadwal!');
                redirect('staf/seminar_proposal');
                return;
            }
            
            // Get data pendukung dengan default values
            $dewan_penguji = $this->_get_dewan_penguji($seminar_id);
            $existing_penilaian = $this->_get_existing_penilaian_staf($seminar_id);
            
            // ✅ FIXED: Pastikan semua property yang dibutuhkan view tersedia
            // Ensure email properties exist with defaults
            if (!isset($seminar_detail->email_pembimbing)) {
                $seminar_detail->email_pembimbing = 'Belum ditentukan';
            }
            if (!isset($seminar_detail->email_penguji1)) {
                $seminar_detail->email_penguji1 = 'Belum ditentukan';
            }
            if (!isset($seminar_detail->email_penguji2)) {
                $seminar_detail->email_penguji2 = 'Belum ditentukan';
            }
            
            // Ensure scoring properties exist with defaults
            if (!isset($seminar_detail->rata_rata_substansi)) {
                $seminar_detail->rata_rata_substansi = null;
            }
            if (!isset($seminar_detail->nilai_akhir)) {
                $seminar_detail->nilai_akhir = null;
            }
            if (!isset($seminar_detail->rekomendasi)) {
                $seminar_detail->rekomendasi = null;
            }
            
            // Prepare data untuk view dengan failsafe
            $view_data = [
                'seminar' => $seminar_detail,
                'dewan_penguji' => $dewan_penguji ?: (object)[
                    'nama_pembimbing' => $seminar_detail->nama_pembimbing ?? 'Belum ditentukan',
                    'nip_pembimbing' => 'Belum ditentukan',
                    'email_pembimbing' => $seminar_detail->email_pembimbing ?? 'Belum ditentukan',
                    'nama_penguji1' => $seminar_detail->nama_penguji1 ?? 'Belum ditentukan',
                    'nip_penguji1' => 'Belum ditentukan',
                    'email_penguji1' => $seminar_detail->email_penguji1 ?? 'Belum ditentukan',
                    'nama_penguji2' => $seminar_detail->nama_penguji2 ?? 'Belum ditentukan',
                    'nip_penguji2' => 'Belum ditentukan',
                    'email_penguji2' => $seminar_detail->email_penguji2 ?? 'Belum ditentukan'
                ],
                'existing_penilaian' => $existing_penilaian ?: (object)[
                    'status' => 'belum_dinilai',
                    'catatan' => null,
                    'nilai_total' => null,
                    'rata_rata_substansi' => null,
                    'nilai_akhir' => null,
                    'rekomendasi' => null
                ],
                'can_edit_penilaian' => false, // ✅ PERUBAHAN: Staf tidak bisa edit
                'page_title' => 'Detail Seminar Proposal - ' . ($seminar_detail->nama_mahasiswa ?? 'Unknown')
            ];
            
            // Data untuk template staf.php
            $data = [
                'title' => 'Detail Seminar Proposal',
                'content' => $this->load->view('staf/seminar_proposal/detail', $view_data, TRUE)
            ];
            
            // Load template existing
            $this->load->view('template/staf', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error in seminar proposal detail: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat detail seminar.');
            redirect('staf/seminar_proposal');
        }
    }

    /**
     * ✅ NEW: Lihat penilaian READ-ONLY (menggantikan input_penilaian)
     * Staf hanya bisa melihat, tidak bisa input/edit penilaian
     */
    public function lihat_penilaian($seminar_id) {
        try {
            // Validasi akses - menggunakan method existing yang sudah stabil
            $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar_detail) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
                redirect('staf/seminar_proposal');
                return;
            }

            // Get existing penilaian (hanya yang dari dosen)
            $existing_penilaian = $this->_get_existing_penilaian_dari_dosen($seminar_id);
            
            // Prepare data untuk view READ-ONLY
            $view_data = [
                'seminar' => $seminar_detail,
                'existing_penilaian' => $existing_penilaian,
                'dewan_penguji' => (object)[
                    'nama_pembimbing' => $seminar_detail->nama_pembimbing ?? 'Belum ditentukan',
                    'nama_penguji1' => $seminar_detail->nama_penguji1 ?? 'Belum ditentukan', 
                    'nama_penguji2' => $seminar_detail->nama_penguji2 ?? 'Belum ditentukan'
                ],
                'readonly' => true, // ✅ Flag untuk read-only mode
                'page_title' => 'Lihat Penilaian - ' . ($seminar_detail->nama_mahasiswa ?? 'Unknown')
            ];
            
            // Data untuk template staf.php
            $data = [
                'title' => 'Lihat Penilaian Seminar Proposal',
                'content' => $this->load->view('staf/seminar_proposal/lihat_penilaian', $view_data, TRUE)
            ];
            
            // Load template existing
            $this->load->view('template/staf', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error in lihat penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat penilaian.');
            redirect('staf/seminar_proposal');
        }
    }

    /**
     * ❌ DISABLED: Method input_penilaian dihapus/disabled
     * Redirect ke lihat_penilaian untuk backward compatibility
     */
    public function input_penilaian($seminar_id) {
        // ✅ REDIRECT: Staf tidak boleh input penilaian lagi
        $this->session->set_flashdata('info', 'Staf tidak memiliki hak akses untuk input penilaian. Hanya dapat melihat penilaian yang diinput dosen.');
        redirect('staf/seminar_proposal/lihat_penilaian/' . $seminar_id);
        return;
    }

    // =================================================================
    // ✅ NEW: METHOD HELPER UNTUK READ-ONLY PENILAIAN
    // =================================================================

    /**
     * Get existing penilaian HANYA dari dosen (bukan staf)
     */
    private function _get_existing_penilaian_dari_dosen($seminar_id) {
        try {
            $this->db->select('*');
            $this->db->from('penilaian_seminar_proposal');
            $this->db->where('seminar_proposal_id', $seminar_id);
            $this->db->where('role_penilai', 'dosen_pembimbing'); // ✅ HANYA dari dosen
            $this->db->order_by('updated_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting penilaian dari dosen: ' . $e->getMessage());
            return null;
        }
    }

    // =================================================================
    // DOWNLOAD METHODS - EXISTING & STABLE (TIDAK DIUBAH)
    // =================================================================

    /**
     * Download form permohonan seminar proposal
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function download_form_permohonan($seminar_id) {
        try {
            $seminar = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar) {
                show_404();
                return;
            }

            // Simple text-based output untuk testing
            $this->output
                ->set_content_type('application/pdf')
                ->set_header('Content-Disposition: attachment; filename="Form_Permohonan_' . $seminar->nim . '.pdf"')
                ->set_output("PDF Content - Form Permohonan untuk " . $seminar->nama_mahasiswa);
                
        } catch (Exception $e) {
            log_message('error', 'Error in download form permohonan: ' . $e->getMessage());
            show_404();
        }
    }

    /**
     * Download surat undangan seminar proposal
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function download_undangan($seminar_id) {
        try {
            $seminar = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar) {
                show_404();
                return;
            }

            // Simple text-based output untuk testing
            $this->output
                ->set_content_type('application/pdf')
                ->set_header('Content-Disposition: attachment; filename="Undangan_' . $seminar->nim . '.pdf"')
                ->set_output("PDF Content - Undangan Seminar untuk " . $seminar->nama_mahasiswa);
                
        } catch (Exception $e) {
            log_message('error', 'Error in download undangan: ' . $e->getMessage());
            show_404();
        }
    }

    /**
     * Download berita acara seminar proposal
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function download_berita_acara($seminar_id) {
        try {
            $seminar = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar) {
                show_404();
                return;
            }

            // Simple text-based output untuk testing
            $this->output
                ->set_content_type('application/pdf')
                ->set_header('Content-Disposition: attachment; filename="Berita_Acara_' . $seminar->nim . '.pdf"')
                ->set_output("PDF Content - Berita Acara untuk " . $seminar->nama_mahasiswa);
                
        } catch (Exception $e) {
            log_message('error', 'Error in download berita acara: ' . $e->getMessage());
            show_404();
        }
    }

    /**
     * Download rekapitulasi nilai
     * METHOD EXISTING - TIDAK DIUBAH
     */
    public function download_rekapitulasi_nilai($seminar_id) {
        try {
            $seminar = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar) {
                show_404();
                return;
            }

            // Simple text-based output untuk testing
            $this->output
                ->set_content_type('application/pdf')
                ->set_header('Content-Disposition: attachment; filename="Rekapitulasi_' . $seminar->nim . '.pdf"')
                ->set_output("PDF Content - Rekapitulasi Nilai untuk " . $seminar->nama_mahasiswa);
                
        } catch (Exception $e) {
            log_message('error', 'Error in download rekapitulasi: ' . $e->getMessage());
            show_404();
        }
    }

    // =================================================================
    // PRIVATE HELPER FUNCTIONS - EXISTING STABLE (TIDAK DIUBAH)
    // =================================================================

    /**
     * Get list seminar proposal yang sudah dijadwalkan kaprodi dan siap untuk administrasi staf
     * METHOD EXISTING - TIDAK DIUBAH
     */
    private function _get_approved_seminar_list() {
        try {
            $this->db->select('
                spm.id, spm.proposal_id, spm.mahasiswa_id, spm.status,
                spm.current_step, spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                spm.status_kaprodi, spm.status_pembimbing,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, 
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi,
                psp.status_penilaian,
                psp.role_penilai
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left');
            $this->db->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left');
            
            // ✅ LEFT JOIN dengan penilaian untuk melihat status
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id', 'left');
            
            // Filter minimal: hanya yang bukan draft
            $this->db->where('spm.status !=', 'draft');
            
            $this->db->order_by('spm.created_at', 'DESC');
            $this->db->limit(50);
            
            $result = $this->db->get()->result();
            
            // ✅ DEBUG: Log query untuk troubleshooting
            log_message('debug', 'Staf seminar query: ' . $this->db->last_query());
            log_message('debug', 'Staf seminar count: ' . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar list for staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detail seminar untuk staf dengan email lengkap dan penilaian
     * METHOD EXISTING - TIDAK DIUBAH
     */
    private function _get_seminar_detail_for_staf($seminar_id) {
        try {
            $this->db->select('
                spm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                m.nomor_telepon, m.tempat_lahir, m.tanggal_lahir,
                pm.judul, pm.ringkasan, pm.jenis_penelitian, pm.lokasi_penelitian,
                pm.uraian_masalah, pm.file_draft_proposal,
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing, 
                d_pembimbing.nip as nip_pembimbing,
                d_pembimbing.email as email_pembimbing,
                d_penguji1.nama as nama_penguji1, 
                d_penguji1.nip as nip_penguji1,
                d_penguji1.email as email_penguji1,
                d_penguji2.nama as nama_penguji2, 
                d_penguji2.nip as nip_penguji2,
                d_penguji2.email as email_penguji2,
                pr.nama as nama_prodi,
                psp.nilai_akhir,
                psp.rekomendasi,
                psp.nilai_substansi_metode as rata_rata_substansi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left');
            $this->db->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left');
            
            // ✅ LEFT JOIN dengan tabel penilaian untuk mendapatkan nilai dan rekomendasi
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id', 'left');
            
            $this->db->where('spm.id', $seminar_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get dewan penguji untuk seminar dengan email lengkap
     * METHOD EXISTING - TIDAK DIUBAH
     */
    private function _get_dewan_penguji($seminar_id) {
        try {
            $this->db->select('
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_pembimbing.nip as nip_pembimbing,
                d_pembimbing.email as email_pembimbing,
                spm.dosen_penguji1_id,
                d_penguji1.nama as nama_penguji1,
                d_penguji1.nip as nip_penguji1,
                d_penguji1.email as email_penguji1,
                spm.dosen_penguji2_id,
                d_penguji2.nama as nama_penguji2,
                d_penguji2.nip as nip_penguji2,
                d_penguji2.email as email_penguji2
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left');
            $this->db->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left');
            $this->db->where('spm.id', $seminar_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dewan penguji: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get penilaian existing dari staf dengan handling error yang lebih baik
     * METHOD EXISTING - TIDAK DIUBAH
     */
    private function _get_penilaian_with_correct_fields($seminar_id) {
        try {
            // Check if table exists first
            if (!$this->db->table_exists('penilaian_seminar_proposal')) {
                return null;
            }
            
            // ✅ PERBAIKAN: Query dengan field yang benar
            $this->db->select('
                psp.*,
                psp.nilai_pembimbing,
                psp.nilai_penguji1, 
                psp.nilai_penguji2,
                psp.nilai_akhir,
                psp.rekomendasi,
                psp.status_penilaian,
                psp.created_at,
                psp.updated_at,
                psp.catatan_umum as catatan_saran
            ');
            $this->db->from('penilaian_seminar_proposal psp');
            $this->db->where('psp.seminar_proposal_id', $seminar_id);
            $this->db->where('psp.role_penilai', 'dosen_pembimbing'); // Prioritas dosen
            
            // Ambil penilaian terbaru dari dosen
            $this->db->order_by('psp.updated_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting correct penilaian fields: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get penilaian existing dari staf dengan handling error yang lebih baik
     * METHOD EXISTING - UNTUK BACKWARD COMPATIBILITY
     */
    private function _get_existing_penilaian_staf($seminar_id) {
        // Redirect ke method baru
        return $this->_get_penilaian_with_correct_fields($seminar_id);
    }

    /**
     * Get statistik dashboard
     * METHOD EXISTING - TIDAK DIUBAH
     */
    private function _get_dashboard_statistics() {
        try {
            $today = date('Y-m-d');
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $week_end = date('Y-m-d', strtotime('sunday this week'));

            // Count dengan error handling
            $today_count = $this->db->where('tanggal_seminar', $today)
                                   ->count_all_results('seminar_proposal_mahasiswa');

            $week_count = $this->db->where('tanggal_seminar >=', $week_start)
                                  ->where('tanggal_seminar <=', $week_end)
                                  ->count_all_results('seminar_proposal_mahasiswa');

            return [
                'seminar_hari_ini' => $today_count ?: 0,
                'seminar_minggu_ini' => $week_count ?: 0,
                'belum_dinilai' => 0, // Simplified untuk testing
                'sudah_dinilai' => 0  // Simplified untuk testing
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return [
                'seminar_hari_ini' => 0,
                'seminar_minggu_ini' => 0,
                'belum_dinilai' => 0,
                'sudah_dinilai' => 0
            ];
        }
    }
}

?>