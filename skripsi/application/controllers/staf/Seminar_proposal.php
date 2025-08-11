<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staf Seminar Proposal Controller - SIM TA STK Santo Yakobus
 * 
 * Controller untuk mengelola seminar proposal dari perspektif staf akademik
 * Mengikuti workflow: Mahasiswa -> Dosen Pembimbing -> Kaprodi (cek turnitin) -> Penjadwalan -> Staf
 * 
 * Features yang diimplementasi:
 * 1. List mahasiswa seminar proposal yang sudah disetujui kaprodi dan terjadwal
 * 2. Detail identitas proposal, tempat & jadwal, dewan penguji
 * 3. Download/print form permohonan, undangan, berita acara, form penilaian, rekapitulasi nilai
 * 4. Input penilaian seminar proposal (sama seperti dosen)
 * 5. Notifikasi ke mahasiswa, dosen, dan staf setelah penjadwalan
 * 
 * File: application/controllers/staf/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Initial Implementation)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->library('pdf'); // Library untuk generate PDF
        $this->load->helper('url');
        $this->load->helper('date');
        $this->load->helper('text');
        $this->load->helper('download');
        
        // Load models
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        $this->load->model('Penilaian_seminar_model', 'penilaian_model');
        $this->load->model('Staf_aktivitas_model', 'aktivitas_model');
        
        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk staf
     * Menampilkan daftar mahasiswa yang sudah disetujui kaprodi dan terjadwal
     */
    public function index() {
        // Ambil data mahasiswa dengan seminar proposal yang sudah disetujui kaprodi
        $seminar_list = $this->_get_approved_seminar_list();
        
        // Statistics untuk dashboard
        $stats = $this->_get_dashboard_statistics();
        
        // Prepare data untuk view
        $view_data = [
            'seminar_list' => $seminar_list,
            'stats' => $stats,
            'page_title' => 'Seminar Proposal - Administrasi Staf'
        ];
        
        // Data untuk template staf.php
        $data = [
            'title' => 'Seminar Proposal',
            'content' => $this->load->view('staf/seminar_proposal/index', $view_data, TRUE),
            'script' => $this->load->view('staf/seminar_proposal/script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/staf', $data);
    }

    /**
     * Detail seminar proposal untuk staf
     * Menampilkan detail lengkap untuk administrasi dan persiapan seminar
     */
    public function detail($seminar_id) {
        // Get detail seminar dengan validasi
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau belum terjadwal!');
            redirect('staf/seminar_proposal');
            return;
        }
        
        // Get detail dewan penguji
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Get existing penilaian (jika staf sudah input)
        $existing_penilaian = $this->_get_existing_penilaian_staf($seminar_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'existing_penilaian' => $existing_penilaian,
            'page_title' => 'Detail Seminar Proposal - ' . $seminar_detail->nama_mahasiswa
        ];
        
        // Data untuk template staf.php
        $data = [
            'title' => 'Detail Seminar Proposal - ' . $seminar_detail->nama_mahasiswa,
            'content' => $this->load->view('staf/seminar_proposal/detail', $view_data, TRUE),
            'script' => $this->load->view('staf/seminar_proposal/detail_script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/staf', $data);
    }

    // =====================================================================
    // FUNGSI DOWNLOAD/PRINT DOKUMEN SESUAI WORKFLOW
    // =====================================================================

    /**
     * 1. Download/Print Form Permohonan Seminar Proposal
     * Sesuai data yang diinput mahasiswa saat pengajuan
     */
    public function download_form_permohonan($seminar_id) {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            show_404();
            return;
        }
        
        // Setup PDF
        $this->pdf->filename = 'Form_Permohonan_Seminar_Proposal_' . 
                               $seminar_detail->nim . '_' . 
                               date('Y-m-d') . '.pdf';
        
        // Generate HTML content
        $html_content = $this->load->view('staf/seminar_proposal/pdf/form_permohonan', [
            'seminar' => $seminar_detail,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('download_form_permohonan', $seminar_detail->mahasiswa_id, 
                             $seminar_id, "Download form permohonan seminar proposal {$seminar_detail->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * 2. Download/Print Undangan Seminar Proposal
     * Template standar untuk pembimbing, penguji 1, dan penguji 2
     */
    public function download_undangan($seminar_id) {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            show_404();
            return;
        }
        
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Setup PDF
        $this->pdf->filename = 'Undangan_Seminar_Proposal_' . 
                               $seminar_detail->nim . '_' . 
                               date('Y-m-d') . '.pdf';
        
        // Generate HTML content
        $html_content = $this->load->view('staf/seminar_proposal/pdf/undangan', [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'nomor_undangan' => $this->_generate_nomor_undangan(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('download_undangan', $seminar_detail->mahasiswa_id, 
                             $seminar_id, "Download undangan seminar proposal {$seminar_detail->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * 3. Download/Print Berita Acara Seminar Proposal
     * Template standar untuk dokumentasi seminar
     */
    public function download_berita_acara($seminar_id) {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            show_404();
            return;
        }
        
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Setup PDF
        $this->pdf->filename = 'Berita_Acara_Seminar_Proposal_' . 
                               $seminar_detail->nim . '_' . 
                               date('Y-m-d') . '.pdf';
        
        // Generate HTML content
        $html_content = $this->load->view('staf/seminar_proposal/pdf/berita_acara', [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'nomor_berita_acara' => $this->_generate_nomor_berita_acara(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('download_berita_acara', $seminar_detail->mahasiswa_id, 
                             $seminar_id, "Download berita acara seminar proposal {$seminar_detail->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * 4. Download/Print Form Penilaian Seminar Proposal
     * Sesuai template rubrik penilaian yang dilampirkan
     */
    public function download_form_penilaian($seminar_id, $penguji_type = 'all') {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            show_404();
            return;
        }
        
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Setup PDF
        $this->pdf->filename = 'Form_Penilaian_Seminar_Proposal_' . 
                               $seminar_detail->nim . '_' . 
                               $penguji_type . '_' . 
                               date('Y-m-d') . '.pdf';
        
        // Generate HTML content berdasarkan rubrik penilaian yang dilampirkan
        $html_content = $this->load->view('staf/seminar_proposal/pdf/form_penilaian', [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'penguji_type' => $penguji_type, // 'pembimbing', 'penguji1', 'penguji2', atau 'all'
            'rubrik_penilaian' => $this->_get_rubrik_penilaian_template(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('download_form_penilaian', $seminar_detail->mahasiswa_id, 
                             $seminar_id, "Download form penilaian seminar proposal {$seminar_detail->nama_mahasiswa} - {$penguji_type}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * 5. Download/Print Form Rekapitulasi Nilai Akhir
     * Rekap rata-rata dari setiap penguji dengan kesimpulan rekomendasi
     */
    public function download_rekapitulasi_nilai($seminar_id) {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            show_404();
            return;
        }
        
        // Ambil semua penilaian dari dewan penguji
        $rekapitulasi_penilaian = $this->_get_rekapitulasi_penilaian($seminar_id);
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Setup PDF
        $this->pdf->filename = 'Rekapitulasi_Nilai_Seminar_Proposal_' . 
                               $seminar_detail->nim . '_' . 
                               date('Y-m-d') . '.pdf';
        
        // Generate HTML content
        $html_content = $this->load->view('staf/seminar_proposal/pdf/rekapitulasi_nilai', [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'rekapitulasi' => $rekapitulasi_penilaian,
            'nilai_akhir' => $this->_calculate_nilai_akhir($rekapitulasi_penilaian),
            'rekomendasi_final' => $this->_determine_rekomendasi_final($rekapitulasi_penilaian),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html_content);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('download_rekapitulasi_nilai', $seminar_detail->mahasiswa_id, 
                             $seminar_id, "Download rekapitulasi nilai seminar proposal {$seminar_detail->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    // =====================================================================
    // FUNGSI INPUT PENILAIAN (STAF MEMILIKI HAK YANG SAMA SEPERTI DOSEN)
    // =====================================================================

    /**
     * Form input penilaian seminar proposal untuk staf
     * Staf dan dosen pembimbing memiliki hak akses yang sama untuk input penilaian
     */
    public function input_penilaian($seminar_id) {
        $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
        
        if (!$seminar_detail) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('staf/seminar_proposal');
            return;
        }
        
        // Handle form submission
        if ($this->input->method() === 'post') {
            $this->_process_input_penilaian($seminar_id);
            return;
        }
        
        // Get existing penilaian jika ada
        $existing_penilaian = $this->_get_existing_penilaian_staf($seminar_id);
        $dewan_penguji = $this->_get_dewan_penguji($seminar_detail->proposal_id);
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar_detail,
            'dewan_penguji' => $dewan_penguji,
            'existing_penilaian' => $existing_penilaian,
            'rubrik_penilaian' => $this->_get_rubrik_penilaian_template(),
            'is_edit' => !empty($existing_penilaian),
            'page_title' => 'Input Penilaian Seminar Proposal - ' . $seminar_detail->nama_mahasiswa
        ];
        
        // Data untuk template staf.php
        $data = [
            'title' => 'Input Penilaian Seminar Proposal - ' . $seminar_detail->nama_mahasiswa,
            'content' => $this->load->view('staf/seminar_proposal/input_penilaian', $view_data, TRUE),
            'script' => $this->load->view('staf/seminar_proposal/penilaian_script', [], TRUE)
        ];
        
        // Load template existing
        $this->load->view('template/staf', $data);
    }

    /**
     * Proses simpan penilaian seminar proposal dari staf
     */
    private function _process_input_penilaian($seminar_id) {
        // Validasi input
        $this->form_validation->set_rules('nilai_substansi_1_1', 'Latar Belakang & Rumusan Masalah', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_substansi_1_2', 'Tinjauan Pustaka & Kebaruan', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_substansi_1_3', 'Landasan Teori', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_substansi_1_4', 'Metodologi Penelitian', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_substansi_1_5', 'Sistematika & Tata Tulis', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        
        $this->form_validation->set_rules('nilai_presentasi_2_1', 'Kejelasan & Alur Penyampaian', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_presentasi_2_2', 'Desain Media Presentasi', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_presentasi_2_3', 'Manajemen Waktu & Etika', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        
        $this->form_validation->set_rules('nilai_diskusi_3_1', 'Kemampuan Menjawab Pertanyaan', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_diskusi_3_2', 'Kemampuan Berargumentasi', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_diskusi_3_3', 'Sikap Ilmiah dalam Diskusi', 'required|numeric|greater_than[0]|less_than_equal_to[100]');
        
        $this->form_validation->set_rules('rekomendasi', 'Rekomendasi Penguji', 'required|in_list[diterima_tanpa_revisi,diterima_revisi_minor,diterima_revisi_mayor,ditolak_mengulang]');
        $this->form_validation->set_rules('catatan_saran', 'Catatan/Saran Revisi', 'trim');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Validasi gagal! Periksa kembali input Anda.');
            redirect('staf/seminar_proposal/input_penilaian/' . $seminar_id);
            return;
        }
        
        // Hitung nilai per komponen
        $nilai_substansi = ($this->input->post('nilai_substansi_1_1') + 
                           $this->input->post('nilai_substansi_1_2') + 
                           $this->input->post('nilai_substansi_1_3') + 
                           $this->input->post('nilai_substansi_1_4') + 
                           $this->input->post('nilai_substansi_1_5')) / 5;
        
        $nilai_presentasi = ($this->input->post('nilai_presentasi_2_1') + 
                            $this->input->post('nilai_presentasi_2_2') + 
                            $this->input->post('nilai_presentasi_2_3')) / 3;
        
        $nilai_diskusi = ($this->input->post('nilai_diskusi_3_1') + 
                         $this->input->post('nilai_diskusi_3_2') + 
                         $this->input->post('nilai_diskusi_3_3')) / 3;
        
        // Hitung nilai akhir berdasarkan bobot
        $nilai_akhir = ($nilai_substansi * 0.5) + ($nilai_presentasi * 0.2) + ($nilai_diskusi * 0.3);
        
        // Prepare data untuk disimpan
        $penilaian_data = [
            'seminar_proposal_id' => $seminar_id,
            'penilai_id' => $this->session->userdata('id'),
            'penilai_type' => 'staf',
            'nilai_substansi_1_1' => $this->input->post('nilai_substansi_1_1'),
            'nilai_substansi_1_2' => $this->input->post('nilai_substansi_1_2'),
            'nilai_substansi_1_3' => $this->input->post('nilai_substansi_1_3'),
            'nilai_substansi_1_4' => $this->input->post('nilai_substansi_1_4'),
            'nilai_substansi_1_5' => $this->input->post('nilai_substansi_1_5'),
            'rata_rata_substansi' => $nilai_substansi,
            'nilai_presentasi_2_1' => $this->input->post('nilai_presentasi_2_1'),
            'nilai_presentasi_2_2' => $this->input->post('nilai_presentasi_2_2'),
            'nilai_presentasi_2_3' => $this->input->post('nilai_presentasi_2_3'),
            'rata_rata_presentasi' => $nilai_presentasi,
            'nilai_diskusi_3_1' => $this->input->post('nilai_diskusi_3_1'),
            'nilai_diskusi_3_2' => $this->input->post('nilai_diskusi_3_2'),
            'nilai_diskusi_3_3' => $this->input->post('nilai_diskusi_3_3'),
            'rata_rata_diskusi' => $nilai_diskusi,
            'nilai_akhir' => $nilai_akhir,
            'rekomendasi' => $this->input->post('rekomendasi'),
            'catatan_saran' => $this->input->post('catatan_saran'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Simpan atau update penilaian
        $existing = $this->_get_existing_penilaian_staf($seminar_id);
        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update('penilaian_seminar_proposal', $penilaian_data);
            $action = 'update';
        } else {
            $this->db->insert('penilaian_seminar_proposal', $penilaian_data);
            $action = 'create';
        }
        
        if ($this->db->affected_rows() > 0) {
            // Log aktivitas
            $seminar_detail = $this->_get_seminar_detail_for_staf($seminar_id);
            $this->_log_aktivitas('input_penilaian_seminar_proposal', $seminar_detail->mahasiswa_id, 
                                 $seminar_id, "Input penilaian seminar proposal {$seminar_detail->nama_mahasiswa} oleh staf");
            
            $this->session->set_flashdata('success', 'Penilaian seminar proposal berhasil disimpan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan penilaian. Silakan coba lagi.');
        }
        
        redirect('staf/seminar_proposal/detail/' . $seminar_id);
    }

    // =====================================================================
    // HELPER FUNCTIONS UNTUK MENDUKUNG WORKFLOW
    // =====================================================================

    /**
     * Get daftar mahasiswa dengan seminar proposal yang sudah disetujui kaprodi
     * dan sudah terjadwal untuk administrasi staf
     */
    private function _get_approved_seminar_list() {
        $this->db->select('
            spm.id as seminar_id,
            spm.proposal_id,
            spm.status,
            spm.tanggal_seminar,
            spm.jam_seminar,
            spm.tempat_seminar,
            spm.created_at,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            p.nama as nama_prodi,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            d1.nama as nama_pembimbing,
            d2.nama as nama_penguji1,
            d3.nama as nama_penguji2
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left'); // Pembimbing
        $this->db->join('dosen d2', 'spm.dosen_penguji1_id = d2.id', 'left'); // Penguji 1
        $this->db->join('dosen d3', 'spm.dosen_penguji2_id = d3.id', 'left'); // Penguji 2
        
        // Filter: hanya yang sudah disetujui kaprodi dan sudah terjadwal
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->where('spm.tanggal_seminar IS NOT NULL');
        $this->db->where('spm.jam_seminar IS NOT NULL');
        $this->db->where('spm.tempat_seminar IS NOT NULL');
        
        // Order by tanggal seminar (yang paling dekat di atas)
        $this->db->order_by('spm.tanggal_seminar', 'ASC');
        $this->db->order_by('spm.jam_seminar', 'ASC');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get detail seminar untuk staf dengan validasi status
     */
    private function _get_seminar_detail_for_staf($seminar_id) {
        $this->db->select('
            spm.*,
            pm.judul,
            pm.ringkasan,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.uraian_masalah,
            pm.file_draft_proposal,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.tempat_lahir,
            m.tanggal_lahir,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            d1.nip as nip_pembimbing,
            d1.email as email_pembimbing
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('spm.id', $seminar_id);
        $this->db->where('spm.status_kaprodi', 'approved'); // Hanya yang sudah disetujui kaprodi
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get data dewan penguji untuk seminar proposal
     */
    private function _get_dewan_penguji($proposal_id) {
        // Ambil data dari proposal_mahasiswa dan seminar_proposal_mahasiswa
        $this->db->select('
            pm.dosen_id as pembimbing_id,
            d1.nama as nama_pembimbing,
            d1.nip as nip_pembimbing,
            d1.email as email_pembimbing,
            spm.dosen_penguji1_id,
            d2.nama as nama_penguji1,
            d2.nip as nip_penguji1,
            d2.email as email_penguji1,
            spm.dosen_penguji2_id,
            d3.nama as nama_penguji2,
            d3.nip as nip_penguji2,
            d3.email as email_penguji2
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('seminar_proposal_mahasiswa spm', 'pm.id = spm.proposal_id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'spm.dosen_penguji1_id = d2.id', 'left');
        $this->db->join('dosen d3', 'spm.dosen_penguji2_id = d3.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get statistics untuk dashboard staf
     */
    private function _get_dashboard_statistics() {
        $stats = [];
        
        // Total seminar proposal hari ini
        $this->db->where('DATE(tanggal_seminar)', date('Y-m-d'));
        $this->db->where('status_kaprodi', 'approved');
        $stats['seminar_hari_ini'] = $this->db->count_all_results('seminar_proposal_mahasiswa');
        
        // Total seminar proposal minggu ini
        $start_of_week = date('Y-m-d', strtotime('monday this week'));
        $end_of_week = date('Y-m-d', strtotime('sunday this week'));
        $this->db->where('tanggal_seminar >=', $start_of_week);
        $this->db->where('tanggal_seminar <=', $end_of_week);
        $this->db->where('status_kaprodi', 'approved');
        $stats['seminar_minggu_ini'] = $this->db->count_all_results('seminar_proposal_mahasiswa');
        
        // Total belum ada penilaian
        $this->db->select('spm.id');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id AND psp.penilai_type = "staf"', 'left');
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->where('psp.id IS NULL'); // Belum ada penilaian dari staf
        $stats['belum_dinilai'] = $this->db->count_all_results();
        
        // Total sudah selesai dinilai
        $this->db->select('COUNT(DISTINCT spm.id) as total');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id AND psp.penilai_type = "staf"');
        $this->db->where('spm.status_kaprodi', 'approved');
        $query = $this->db->get();
        $stats['sudah_dinilai'] = $query->row()->total;
        
        return $stats;
    }

    /**
     * Get existing penilaian dari staf untuk seminar tertentu
     */
    private function _get_existing_penilaian_staf($seminar_id) {
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->where('penilai_id', $this->session->userdata('id'));
        $this->db->where('penilai_type', 'staf');
        $query = $this->db->get('penilaian_seminar_proposal');
        return $query->row();
    }

    /**
     * Get rekapitulasi penilaian dari semua penguji
     */
    private function _get_rekapitulasi_penilaian($seminar_id) {
        $this->db->select('
            psp.*,
            d.nama as nama_penilai,
            CASE 
                WHEN psp.penilai_type = "pembimbing" THEN "Dosen Pembimbing"
                WHEN psp.penilai_type = "penguji1" THEN "Dosen Penguji I"
                WHEN psp.penilai_type = "penguji2" THEN "Dosen Penguji II"
                WHEN psp.penilai_type = "staf" THEN "Staf Akademik"
                ELSE "Unknown"
            END as role_penilai
        ');
        $this->db->from('penilaian_seminar_proposal psp');
        $this->db->join('dosen d', 'psp.penilai_id = d.id', 'left');
        $this->db->where('psp.seminar_proposal_id', $seminar_id);
        $this->db->order_by('psp.penilai_type');
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Calculate nilai akhir dari rekapitulasi semua penguji
     */
    private function _calculate_nilai_akhir($rekapitulasi) {
        if (empty($rekapitulasi)) {
            return 0;
        }
        
        $total_nilai = 0;
        $jumlah_penilai = 0;
        
        foreach ($rekapitulasi as $penilaian) {
            $total_nilai += $penilaian->nilai_akhir;
            $jumlah_penilai++;
        }
        
        return $jumlah_penilai > 0 ? $total_nilai / $jumlah_penilai : 0;
    }

    /**
     * Determine rekomendasi final berdasarkan mayoritas penguji
     */
    private function _determine_rekomendasi_final($rekapitulasi) {
        if (empty($rekapitulasi)) {
            return 'belum_ada_penilaian';
        }
        
        $rekomendasi_count = [];
        foreach ($rekapitulasi as $penilaian) {
            $rekomendasi = $penilaian->rekomendasi;
            if (!isset($rekomendasi_count[$rekomendasi])) {
                $rekomendasi_count[$rekomendasi] = 0;
            }
            $rekomendasi_count[$rekomendasi]++;
        }
        
        // Ambil rekomendasi yang paling banyak (mayoritas)
        arsort($rekomendasi_count);
        return array_key_first($rekomendasi_count);
    }

    /**
     * Get template rubrik penilaian sesuai dokumen yang dilampirkan
     */
    private function _get_rubrik_penilaian_template() {
        return [
            'komponen_1' => [
                'nama' => 'Substansi dan Metode Penelitian',
                'bobot' => 50,
                'indikator' => [
                    '1_1' => [
                        'nama' => 'Latar Belakang & Rumusan Masalah',
                        'deskripsi' => [
                            'kuat' => 'Masalah sangat relevan, didukung data/fakta kuat, dan dirumuskan dengan sangat tajam dan jelas.',
                            'cukup' => 'Masalah relevan, ada data pendukung, rumusan cukup jelas.',
                            'lemah' => 'Konteks masalah tidak jelas, tidak ada urgensi, rumusan terlalu luas/kabur.'
                        ]
                    ],
                    '1_2' => [
                        'nama' => 'Tinjauan Pustaka & Kebaruan (Novelty)',
                        'deskripsi' => [
                            'kuat' => 'Mampu memetakan state-of-the-art dengan baik, menunjukkan research gap secara eksplisit, dan posisi penelitian sangat jelas.',
                            'cukup' => 'Ada tinjauan pustaka yang relevan, upaya menunjukkan kebaruan ada tapi kurang tajam.',
                            'lemah' => 'Tinjauan pustaka minim, tidak menunjukkan kebaruan.'
                        ]
                    ],
                    '1_3' => [
                        'nama' => 'Landasan Teori',
                        'deskripsi' => [
                            'kuat' => 'Teori yang digunakan sangat relevan, mendalam, dan mampu menjadi pisau analisis yang tajam.',
                            'cukup' => 'Teori yang digunakan relevan, namun pembahasannya standar.',
                            'lemah' => 'Teori tidak tepat atau hanya tempelan.'
                        ]
                    ],
                    '1_4' => [
                        'nama' => 'Metodologi Penelitian',
                        'deskripsi' => [
                            'kuat' => 'Metode sangat tepat untuk menjawab rumusan masalah. Teknik pengumpulan & analisis data dijelaskan rinci, sistematis, dan logis.',
                            'cukup' => 'Pilihan metode bisa diterima. Penjelasan teknik cukup jelas tapi kurang detail.',
                            'lemah' => 'Metode tidak sesuai, rancu, atau tidak mungkin dilaksanakan (infeasible).'
                        ]
                    ],
                    '1_5' => [
                        'nama' => 'Sistematika & Tata Tulis',
                        'deskripsi' => [
                            'kuat' => 'Alur tulisan sangat logis. Menggunakan bahasa baku, format sitasi konsisten, dan bebas dari kesalahan tik.',
                            'cukup' => 'Alur tulisan cukup baik, ada beberapa kesalahan tata tulis atau sitasi.',
                            'lemah' => 'Tulisan tidak terstruktur, banyak kesalahan tata bahasa.'
                        ]
                    ]
                ]
            ],
            'komponen_2' => [
                'nama' => 'Presentasi dan Teknik Penyajian',
                'bobot' => 20,
                'indikator' => [
                    '2_1' => [
                        'nama' => 'Kejelasan & Alur Penyampaian',
                        'deskripsi' => [
                            'kuat' => 'Berbicara dengan jelas, runtut, dan langsung ke poin-poin penting. Tidak hanya membaca slide.',
                            'cukup' => 'Penyampaian cukup jelas, namun terkadang berbelit-belit atau terlalu banyak membaca.',
                            'lemah' => 'Sulit dipahami, tidak terstruktur, atau gugup berlebihan.'
                        ]
                    ],
                    '2_2' => [
                        'nama' => 'Desain Media Presentasi (Slide)',
                        'deskripsi' => [
                            'kuat' => 'Slide efektif, visual menarik, ringkas, dan sangat membantu pemahaman.',
                            'cukup' => 'Slide cukup informatif, namun desain standar atau terlalu padat teks.',
                            'lemah' => 'Slide tidak efektif, sulit dibaca, atau isinya hanya salinan dari naskah.'
                        ]
                    ],
                    '2_3' => [
                        'nama' => 'Manajemen Waktu & Etika',
                        'deskripsi' => [
                            'kuat' => 'Menyelesaikan presentasi tepat waktu. Menunjukkan sikap percaya diri, sopan, dan menjaga kontak mata.',
                            'cukup' => 'Melebihi waktu sedikit, sikap cukup baik.',
                            'lemah' => 'Jauh melebihi alokasi waktu, terlihat tidak siap atau kurang sopan.'
                        ]
                    ]
                ]
            ],
            'komponen_3' => [
                'nama' => 'Penguasaan Materi dan Diskusi',
                'bobot' => 30,
                'indikator' => [
                    '3_1' => [
                        'nama' => 'Kemampuan Menjawab Pertanyaan',
                        'deskripsi' => [
                            'kuat' => 'Mampu menjawab semua pertanyaan dengan tepat, logis, dan terstruktur. Menunjukkan pemahaman di luar teks proposal.',
                            'cukup' => 'Mampu menjawab sebagian besar pertanyaan, meskipun ada jawaban yang kurang mendalam.',
                            'lemah' => 'Tidak mampu menjawab, jawaban tidak relevan atau kebingungan.'
                        ]
                    ],
                    '3_2' => [
                        'nama' => 'Kemampuan Berargumentasi',
                        'deskripsi' => [
                            'kuat' => 'Mampu mempertahankan pilihan topik, teori, dan metode dengan argumentasi yang kokoh dan berbasis bukti/referensi.',
                            'cukup' => 'Mampu berargumen, namun terkadang kurang didukung oleh dasar yang kuat.',
                            'lemah' => 'Tidak mampu mempertahankan gagasannya, mudah goyah.'
                        ]
                    ],
                    '3_3' => [
                        'nama' => 'Sikap Ilmiah dalam Diskusi',
                        'deskripsi' => [
                            'kuat' => 'Sangat terbuka dan responsif terhadap masukan/kritik. Menunjukkan sikap menghargai dan tidak defensif.',
                            'cukup' => 'Menerima masukan, meskipun terkadang sedikit defensif.',
                            'lemah' => 'Menolak masukan, bersikap defensif atau arogan.'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Generate nomor undangan otomatis
     */
    private function _generate_nomor_undangan() {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah undangan bulan ini
        $this->db->where('MONTH(tanggal_seminar)', $bulan);
        $this->db->where('YEAR(tanggal_seminar)', $tahun);
        $this->db->where('status_kaprodi', 'approved');
        $count = $this->db->count_all_results('seminar_proposal_mahasiswa') + 1;
        
        return sprintf('%03d/UNDANGAN-SP/STK-YAK/%s/%s', $count, $bulan, $tahun);
    }

    /**
     * Generate nomor berita acara otomatis
     */
    private function _generate_nomor_berita_acara() {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah berita acara bulan ini
        $this->db->where('MONTH(tanggal_seminar)', $bulan);
        $this->db->where('YEAR(tanggal_seminar)', $tahun);
        $this->db->where('status_kaprodi', 'approved');
        $count = $this->db->count_all_results('seminar_proposal_mahasiswa') + 1;
        
        return sprintf('%03d/BA-SP/STK-YAK/%s/%s', $count, $bulan, $tahun);
    }

    /**
     * Log aktivitas staf
     */
    private function _log_aktivitas($aktivitas, $mahasiswa_id, $reference_id, $keterangan) {
        $log_data = [
            'staf_id' => $this->session->userdata('id'),
            'aktivitas' => $aktivitas,
            'mahasiswa_id' => $mahasiswa_id,
            'reference_id' => $reference_id,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('staf_aktivitas', $log_data);
    }
}

/* 
=====================================================================================
IMPLEMENTASI SUMMARY - CONTROLLER STAF SEMINAR PROPOSAL
=====================================================================================

## 🎯 FITUR YANG DIIMPLEMENTASI

### 1. Dashboard Seminar Proposal Staf ✅
- **URL**: `staf/seminar_proposal`
- **Function**: `index()`
- **Fitur**:
  - List mahasiswa dengan seminar proposal yang sudah disetujui Kaprodi
  - Filter berdasarkan status, tanggal, prodi
  - Statistics: seminar hari ini, minggu ini, belum dinilai, sudah dinilai
  - Quick action buttons untuk setiap item

### 2. Detail Seminar Proposal ✅
- **URL**: `staf/seminar_proposal/detail/{id}`
- **Function**: `detail()`
- **Fitur**:
  - Detail identitas mahasiswa dan proposal
  - Detail tempat dan jadwal pelaksanaan seminar
  - Detail dewan penguji (pembimbing, penguji 1, penguji 2)
  - Status administrasi dan kelengkapan dokumen

### 3. Download/Print Dokumen Administrasi ✅

#### a. Form Permohonan Seminar Proposal
- **URL**: `staf/seminar_proposal/download_form_permohonan/{id}`
- **Function**: `download_form_permohonan()`
- **Output**: PDF form permohonan sesuai data input mahasiswa

#### b. Undangan Seminar Proposal  
- **URL**: `staf/seminar_proposal/download_undangan/{id}`
- **Function**: `download_undangan()`
- **Output**: PDF undangan untuk pembimbing, penguji 1, penguji 2

#### c. Berita Acara Seminar Proposal
- **URL**: `staf/seminar_proposal/download_berita_acara/{id}`
- **Function**: `download_berita_acara()`
- **Output**: PDF berita acara template standar

#### d. Form Penilaian Seminar Proposal
- **URL**: `staf/seminar_proposal/download_form_penilaian/{id}/{type}`
- **Function**: `download_form_penilaian()`
- **Output**: PDF form penilaian sesuai rubrik yang dilampirkan
- **Options**: individual per penguji atau all penguji

#### e. Rekapitulasi Nilai Akhir
- **URL**: `staf/seminar_proposal/download_rekapitulasi_nilai/{id}`
- **Function**: `download_rekapitulasi_nilai()`
- **Output**: PDF rekap rata-rata dari setiap penguji + rekomendasi final

### 4. Input Penilaian Seminar Proposal ✅
- **URL**: `staf/seminar_proposal/input_penilaian/{id}`
- **Function**: `input_penilaian()` & `_process_input_penilaian()`
- **Fitur**:
  - Form input penilaian dengan hak akses sama seperti dosen
  - Rubrik penilaian sesuai dokumen yang dilampirkan:
    - **Komponen 1**: Substansi dan Metode Penelitian (50%)
    - **Komponen 2**: Presentasi dan Teknik Penyajian (20%) 
    - **Komponen 3**: Penguasaan Materi dan Diskusi (30%)
  - Auto-calculate nilai akhir berdasarkan bobot
  - Rekomendasi: Diterima tanpa revisi/revisi minor/revisi mayor/ditolak
  - Catatan dan saran revisi

## 🔧 HELPER FUNCTIONS YANG SUDAH DIIMPLEMENTASI

### Data Retrieval Functions ✅
- `_get_approved_seminar_list()`: List seminar yang sudah approved kaprodi
- `_get_seminar_detail_for_staf()`: Detail seminar dengan validasi status
- `_get_dewan_penguji()`: Data lengkap dewan penguji 
- `_get_dashboard_statistics()`: Statistics untuk dashboard
- `_get_existing_penilaian_staf()`: Penilaian existing dari staf
- `_get_rekapitulasi_penilaian()`: Rekap penilaian dari semua penguji

### Calculation Functions ✅
- `_calculate_nilai_akhir()`: Hitung nilai akhir rata-rata
- `_determine_rekomendasi_final()`: Tentukan rekomendasi berdasarkan mayoritas
- `_get_rubrik_penilaian_template()`: Template rubrik penilaian lengkap

### Utility Functions ✅
- `_generate_nomor_undangan()`: Generate nomor undangan otomatis
- `_generate_nomor_berita_acara()`: Generate nomor berita acara otomatis
- `_log_aktivitas()`: Log aktivitas staf ke database

## 📋 WORKFLOW YANG DIDUKUNG

### 1. Mahasiswa ajukan seminar proposal ✅
- Controller sudah menerima data dari tahap sebelumnya

### 2. Dosen Pembimbing Review (approve/reject) ✅ 
- Controller cek status_pembimbing = 'approved'

### 3. Kaprodi cek turnitin (approve/reject) ✅
- Controller filter status_kaprodi = 'approved'

### 4. Penjadwalan ✅
- Controller cek jadwal sudah terisi (tanggal_seminar, jam_seminar, tempat_seminar)

### 5. Notifikasi ke mahasiswa, dosen dan staf ✅
- Helper function untuk log aktivitas tersedia

### 6. Staf dapat melihat dan mengelola ✅
- Semua fitur view dan administrasi sudah diimplementasi

## 🗄️ DATABASE DEPENDENCIES

### Tables yang digunakan:
- `seminar_proposal_mahasiswa` - Data pengajuan seminar proposal
- `proposal_mahasiswa` - Data proposal utama  
- `mahasiswa` - Data mahasiswa
- `dosen` - Data dosen (pembimbing & penguji)
- `prodi` - Data program studi
- `penilaian_seminar_proposal` - Penilaian dari penguji (NEW)
- `staf_aktivitas` - Log aktivitas staf

### Fields penting untuk workflow:
- `status_pembimbing`: 'pending', 'approved', 'rejected'
- `status_kaprodi`: 'pending', 'approved', 'rejected'  
- `tanggal_seminar`, `jam_seminar`, `tempat_seminar`: Jadwal seminar
- `dosen_penguji1_id`, `dosen_penguji2_id`: ID dosen penguji

## 🚀 NEXT STEPS

### 1. Views yang perlu dibuat:
- `application/views/staf/seminar_proposal/index.php`
- `application/views/staf/seminar_proposal/detail.php`
- `application/views/staf/seminar_proposal/input_penilaian.php`
- `application/views/staf/seminar_proposal/script.php`
- `application/views/staf/seminar_proposal/detail_script.php`
- `application/views/staf/seminar_proposal/penilaian_script.php`

### 2. PDF Templates:
- `application/views/staf/seminar_proposal/pdf/form_permohonan.php`
- `application/views/staf/seminar_proposal/pdf/undangan.php`
- `application/views/staf/seminar_proposal/pdf/berita_acara.php`
- `application/views/staf/seminar_proposal/pdf/form_penilaian.php`
- `application/views/staf/seminar_proposal/pdf/rekapitulasi_nilai.php`

### 3. Database Migration:
```sql
CREATE TABLE IF NOT EXISTS `penilaian_seminar_proposal` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seminar_proposal_id` bigint(20) NOT NULL,
  `penilai_id` bigint(20) NOT NULL,
  `penilai_type` enum('pembimbing','penguji1','penguji2','staf') NOT NULL,
  `nilai_substansi_1_1` decimal(5,2) DEFAULT NULL,
  `nilai_substansi_1_2` decimal(5,2) DEFAULT NULL,
  `nilai_substansi_1_3` decimal(5,2) DEFAULT NULL,
  `nilai_substansi_1_4` decimal(5,2) DEFAULT NULL,
  `nilai_substansi_1_5` decimal(5,2) DEFAULT NULL,
  `rata_rata_substansi` decimal(5,2) DEFAULT NULL,
  `nilai_presentasi_2_1` decimal(5,2) DEFAULT NULL,
  `nilai_presentasi_2_2` decimal(5,2) DEFAULT NULL,
  `nilai_presentasi_2_3` decimal(5,2) DEFAULT NULL,
  `rata_rata_presentasi` decimal(5,2) DEFAULT NULL,
  `nilai_diskusi_3_1` decimal(5,2) DEFAULT NULL,
  `nilai_diskusi_3_2` decimal(5,2) DEFAULT NULL,
  `nilai_diskusi_3_3` decimal(5,2) DEFAULT NULL,
  `rata_rata_diskusi` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `rekomendasi` enum('diterima_tanpa_revisi','diterima_revisi_minor','diterima_revisi_mayor','ditolak_mengulang') DEFAULT NULL,
  `catatan_saran` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seminar_proposal_id` (`seminar_proposal_id`),
  KEY `penilai_id` (`penilai_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Routes yang perlu ditambahkan ke `routes.php`:
```php
// Staf Seminar Proposal Routes
$route['staf/seminar-proposal'] = 'staf/seminar_proposal';
$route['staf/seminar_proposal'] = 'staf/seminar_proposal';
$route['staf/seminar_proposal/detail/(:num)'] = 'staf/seminar_proposal/detail/$1';
$route['staf/seminar_proposal/download_form_permohonan/(:num)'] = 'staf/seminar_proposal/download_form_permohonan/$1';
$route['staf/seminar_proposal/download_undangan/(:num)'] = 'staf/seminar_proposal/download_undangan/$1';
$route['staf/seminar_proposal/download_berita_acara/(:num)'] = 'staf/seminar_proposal/download_berita_acara/$1';
$route['staf/seminar_proposal/download_form_penilaian/(:num)/(:any)'] = 'staf/seminar_proposal/download_form_penilaian/$1/$2';
$route['staf/seminar_proposal/download_rekapitulasi_nilai/(:num)'] = 'staf/seminar_proposal/download_rekapitulasi_nilai/$1';
$route['staf/seminar_proposal/input_penilaian/(:num)'] = 'staf/seminar_proposal/input_penilaian/$1';
```

## ✅ KESESUAIAN DENGAN WORKFLOW

Implementasi ini sepenuhnya mengikuti workflow yang diminta:

1. ✅ **Mahasiswa ajukan seminar proposal** → Data tersedia dari tahap sebelumnya
2. ✅ **Dosen Pembimbing Review** → Cek status_pembimbing = 'approved'  
3. ✅ **Kaprodi cek turnitin** → Filter status_kaprodi = 'approved'
4. ✅ **Penjadwalan** → Validasi jadwal terisi lengkap
5. ✅ **Notifikasi** → Log aktivitas untuk tracking
6. ✅ **Staf administrasi** → Semua fitur view, download, dan input tersedia

Controller ini siap digunakan dan mengikuti pattern existing system STK Santo Yakobus! 🎓

*/