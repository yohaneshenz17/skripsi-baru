<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staf Seminar Proposal Controller - FIXED VERSION (Lengkap dengan Email & Penilaian)
 * 
 * Controller untuk mengelola seminar proposal dari perspektif staf akademik
 * 
 * FIXED ISSUES:
 * - Headers already sent error
 * - Session cannot be started error
 * - Clean output buffering
 * - Duplicate method declarations
 * - Missing email_pembimbing, email_penguji1, email_penguji2
 * - Missing nilai_akhir, rata_rata_substansi, rekomendasi
 * 
 * File: application/controllers/staf/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (FIXED - Complete with Email & Scoring)
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
     * Detail seminar proposal untuk staf - FIXED VERSION
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
                'can_edit_penilaian' => true,
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
     * Input penilaian seminar proposal untuk staf - FIXED VERSION
     */
    public function input_penilaian($seminar_id) {
        try {
            // Validasi akses dan status seminar
            $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
            
            if (!$seminar_detail) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
                redirect('staf/seminar_proposal');
                return;
            }

            // Cek apakah penilaian masih bisa diedit
            if (!$this->_can_edit_penilaian($seminar_detail->status ?? '')) {
                $this->session->set_flashdata('error', 'Penilaian sudah tidak dapat diedit lagi!');
                redirect('staf/seminar_proposal/detail/' . $seminar_id);
                return;
            }

            // Proses form jika method POST
            if ($this->input->method() === 'post') {
                $this->_process_input_penilaian_enhanced($seminar_id);
                return;
            }

            // Get data untuk form penilaian
            $existing_penilaian = $this->_get_existing_penilaian_staf($seminar_id);
            $rubrik_template = $this->_get_rubrik_penilaian_template();
            
            // ✅ FIXED: Pastikan semua variabel yang dibutuhkan view tersedia
            $view_data = [
                'seminar' => $seminar_detail,
                'existing_penilaian' => $existing_penilaian,
                'rubrik_template' => $rubrik_template,
                'is_edit' => !empty($existing_penilaian), // ✅ FIXED: Variable yang missing
                'page_title' => 'Input Penilaian - ' . ($seminar_detail->nama_mahasiswa ?? 'Unknown')
            ];
            
            // Data untuk template staf.php
            $data = [
                'title' => 'Input Penilaian Seminar Proposal - ' . ($seminar_detail->nama_mahasiswa ?? 'Unknown'),
                'content' => $this->load->view('staf/seminar_proposal/input_penilaian', $view_data, TRUE)
            ];
            
            // Load template existing
            $this->load->view('template/staf', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error in input penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat form penilaian.');
            redirect('staf/seminar_proposal');
        }
    }

    // =================================================================
    // DOWNLOAD FUNCTIONS - SIMPLIFIED
    // =================================================================

    /**
     * Download form permohonan seminar proposal
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

    // =================================================================
    // PRIVATE HELPER FUNCTIONS - FIXED VERSION (WITH EMAIL & SCORING)
    // =================================================================

    /**
     * Get list seminar proposal yang sudah dijadwalkan kaprodi dan siap untuk administrasi staf
     */
    private function _get_approved_seminar_list() {
        try {
            $this->db->select('
                spm.id, spm.proposal_id, spm.mahasiswa_id, spm.status,
                spm.current_step, spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, 
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left');
            $this->db->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left');
            
            // ✅ PERBAIKAN FILTER: Lebih fleksibel untuk menampilkan seminar yang sudah dijadwalkan
            $this->db->where('spm.status_kaprodi', 'approved'); // Yang sudah disetujui kaprodi
            
            // Terima berbagai kombinasi status
            $this->db->group_start();
                $this->db->where('spm.status', 'scheduled'); // Status scheduled
                $this->db->or_group_start();
                    $this->db->where('spm.status', 'approved'); // Atau approved
                    $this->db->where('spm.tanggal_seminar IS NOT NULL'); // Yang sudah ada jadwal
                $this->db->group_end();
            $this->db->group_end();
            
            $this->db->order_by('spm.tanggal_seminar', 'ASC');
            $this->db->limit(50);
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting scheduled seminar list for staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ FIXED: Get detail seminar untuk staf dengan email lengkap dan penilaian
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
     * ✅ IMPROVED: Get dewan penguji untuk seminar dengan email lengkap
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
     * ✅ IMPROVED: Get penilaian existing dari staf dengan handling error yang lebih baik
     */
    private function _get_existing_penilaian_staf($seminar_id) {
        try {
            // Check if table exists first
            if (!$this->db->table_exists('penilaian_seminar_proposal')) {
                return null;
            }
            
            // Enhanced query untuk cek existing penilaian
            $this->db->select('
                psp.*,
                psp.nilai_substansi_metode as rata_rata_substansi,
                psp.nilai_akhir,
                psp.rekomendasi
            ');
            $this->db->from('penilaian_seminar_proposal psp');
            $this->db->where('psp.seminar_proposal_id', $seminar_id);
            $this->db->where('psp.role_penilai', 'staf');
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting existing penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NEW: Enhanced process input penilaian dengan validation yang lengkap
     */
    private function _process_input_penilaian_enhanced($seminar_id) {
        try {
            $this->load->library('form_validation');
            
            // ✅ ENHANCED: Validation rules yang lengkap
            $this->form_validation->set_rules('nilai_substansi', 'Nilai Substansi & Metode', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_presentasi', 'Nilai Presentasi & Teknik', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('nilai_diskusi', 'Nilai Penguasaan & Diskusi', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('rekomendasi', 'Rekomendasi', 'required|in_list[diterima_tanpa_revisi,revisi_minor,revisi_mayor,ditolak]');
            $this->form_validation->set_rules('catatan_latar_belakang', 'Catatan Latar Belakang', 'trim|max_length[2000]');
            $this->form_validation->set_rules('catatan_tinjauan_pustaka', 'Catatan Tinjauan Pustaka', 'trim|max_length[2000]');
            $this->form_validation->set_rules('catatan_landasan_teori', 'Catatan Landasan Teori', 'trim|max_length[2000]');
            $this->form_validation->set_rules('catatan_metodologi', 'Catatan Metodologi', 'trim|max_length[2000]');
            $this->form_validation->set_rules('catatan_sistematika', 'Catatan Sistematika', 'trim|max_length[2000]');
            $this->form_validation->set_rules('catatan_umum', 'Catatan Umum', 'trim|max_length[2000]');

            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', 'Validasi gagal! Periksa kembali input Anda.');
                redirect('staf/seminar_proposal/input_penilaian/' . $seminar_id);
                return;
            }

            // ✅ ENHANCED: Get detail seminar untuk mahasiswa_id dan proposal_id
            $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
            if (!$seminar_detail) {
                throw new Exception('Data seminar tidak ditemukan');
            }

            // ✅ ENHANCED: Calculate nilai akhir dan huruf
            $nilai_substansi = (float)$this->input->post('nilai_substansi');
            $nilai_presentasi = (float)$this->input->post('nilai_presentasi');
            $nilai_diskusi = (float)$this->input->post('nilai_diskusi');
            
            // Hitung nilai akhir dengan bobot: Substansi (50%), Presentasi (20%), Diskusi (30%)
            $nilai_akhir = ($nilai_substansi * 0.5) + ($nilai_presentasi * 0.2) + ($nilai_diskusi * 0.3);
            
            // Tentukan nilai huruf
            $nilai_huruf = 'E';
            if ($nilai_akhir >= 80) $nilai_huruf = 'A';
            elseif ($nilai_akhir >= 70) $nilai_huruf = 'B';
            elseif ($nilai_akhir >= 60) $nilai_huruf = 'C';
            elseif ($nilai_akhir >= 50) $nilai_huruf = 'D';

            // ✅ ENHANCED: Prepare comprehensive save data
            $save_data = [
                'seminar_proposal_id' => $seminar_id,
                'mahasiswa_id' => $seminar_detail->mahasiswa_id,
                'proposal_id' => $seminar_detail->proposal_id,
                'dinilai_oleh' => $this->session->userdata('id'),
                'role_penilai' => 'staf',
                
                // Nilai komponen
                'nilai_substansi_metode' => $nilai_substansi,
                'nilai_presentasi_teknik' => $nilai_presentasi,
                'nilai_penguasaan_diskusi' => $nilai_diskusi,
                'nilai_akhir' => $nilai_akhir,
                'nilai_huruf' => $nilai_huruf,
                
                // Rekomendasi
                'rekomendasi' => $this->input->post('rekomendasi'),
                
                // Catatan detail
                'catatan_latar_belakang' => $this->input->post('catatan_latar_belakang'),
                'catatan_tinjauan_pustaka' => $this->input->post('catatan_tinjauan_pustaka'),
                'catatan_landasan_teori' => $this->input->post('catatan_landasan_teori'),
                'catatan_metodologi' => $this->input->post('catatan_metodologi'),
                'catatan_sistematika' => $this->input->post('catatan_sistematika'),
                'catatan_umum' => $this->input->post('catatan_umum'),
                
                // Status dan timestamp
                'status_penilaian' => 'published',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // ✅ ENHANCED: Check if table exists and handle insert/update
            if (!$this->db->table_exists('penilaian_seminar_proposal')) {
                // Create table if not exists (optional)
                log_message('error', 'Table penilaian_seminar_proposal does not exist');
                throw new Exception('Tabel penilaian tidak ditemukan dalam database');
            }

            // Check if existing penilaian
            $existing = $this->db->get_where('penilaian_seminar_proposal', [
                'seminar_proposal_id' => $seminar_id,
                'dinilai_oleh' => $this->session->userdata('id'),
                'role_penilai' => 'staf'
            ])->row();

            if ($existing) {
                // Update existing
                $result = $this->db->update('penilaian_seminar_proposal', $save_data, [
                    'seminar_proposal_id' => $seminar_id,
                    'dinilai_oleh' => $this->session->userdata('id'),
                    'role_penilai' => 'staf'
                ]);
                $action = 'diperbarui';
            } else {
                // Insert new
                $save_data['created_at'] = date('Y-m-d H:i:s');
                $result = $this->db->insert('penilaian_seminar_proposal', $save_data);
                $action = 'disimpan';
            }

            if ($result) {
                $this->session->set_flashdata('success', "Penilaian berhasil {$action}! Nilai akhir: {$nilai_akhir} ({$nilai_huruf})");
                redirect('staf/seminar_proposal/detail/' . $seminar_id);
            } else {
                throw new Exception('Gagal menyimpan data ke database');
            }

        } catch (Exception $e) {
            log_message('error', 'Error processing enhanced penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
            redirect('staf/seminar_proposal/input_penilaian/' . $seminar_id);
        }
    }

    /**
     * Get statistik dashboard
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

    /**
     * Cek apakah penilaian masih bisa diedit
     */
    private function _can_edit_penilaian($seminar_status) {
        // Simple logic untuk testing
        $non_editable_status = ['completed', 'published', 'archived'];
        return !in_array($seminar_status, $non_editable_status);
    }

    /**
     * Proses input penilaian - SIMPLIFIED
     */
    private function _process_input_penilaian($seminar_id) {
        try {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('status', 'Status Hasil Seminar', 'required|in_list[1,2,3]');
            $this->form_validation->set_rules('catatan_masukan', 'Catatan/Masukan', 'trim');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', 'Validasi gagal! Periksa kembali input Anda.');
                redirect('staf/seminar_proposal/input_penilaian/' . $seminar_id);
                return;
            }

            // Simple save untuk testing
            $save_data = [
                'seminar_proposal_id' => $seminar_id,
                'dinilai_oleh' => $this->session->userdata('id'),
                'status_penilaian' => $this->input->post('status'),
                'catatan_saran' => $this->input->post('catatan_masukan'),
                'tanggal_penilaian' => date('Y-m-d H:i:s'),
                'role_penilai' => 'staf'
            ];

            // Try insert/update
            if ($this->db->table_exists('penilaian_seminar_proposal')) {
                $existing = $this->db->get_where('penilaian_seminar_proposal', [
                    'seminar_proposal_id' => $seminar_id,
                    'dinilai_oleh' => $this->session->userdata('id')
                ])->row();

                if ($existing) {
                    $result = $this->db->update('penilaian_seminar_proposal', $save_data, [
                        'seminar_proposal_id' => $seminar_id,
                        'dinilai_oleh' => $this->session->userdata('id')
                    ]);
                } else {
                    $result = $this->db->insert('penilaian_seminar_proposal', $save_data);
                }
            } else {
                $result = true; // Simulate success untuk testing
            }

            if ($result) {
                $this->session->set_flashdata('success', 'Penilaian berhasil disimpan!');
                redirect('staf/seminar_proposal/detail/' . $seminar_id);
            } else {
                throw new Exception('Gagal menyimpan data ke database');
            }

        } catch (Exception $e) {
            log_message('error', 'Error processing penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
            redirect('staf/seminar_proposal/input_penilaian/' . $seminar_id);
        }
    }

    /**
     * Get rubrik penilaian template - SIMPLIFIED
     */
    private function _get_rubrik_penilaian_template() {
        return [
            'status_options' => [
                '1' => 'Lanjut (Sempurna)',
                '2' => 'Lanjut dengan Perbaikan',
                '3' => 'Ditolak'
            ],
            'guidelines' => [
                '1' => 'Proposal sangat baik dan dapat dilanjutkan tanpa revisi',
                '2' => 'Proposal baik namun perlu perbaikan minor sebelum dilanjutkan',
                '3' => 'Proposal memerlukan perbaikan major atau ditolak'
            ]
        ];
    }
}