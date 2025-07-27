<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Mahasiswa - Seminar Proposal
 * Mengelola pengajuan seminar proposal oleh mahasiswa
 * 
 * @property Seminar_proposal_model $seminar_model
 * @property CI_Upload $upload
 * @property CI_Form_validation $form_validation
 */
class Seminar extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form', 'file', 'security']);
        $this->load->model('Seminar_proposal_model', 'seminar_model');
        
        // Cek login dan level mahasiswa
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != 'mahasiswa') {
            redirect('auth/login');
        }
        
        // Load helper classes
        $this->load->library('Seminar_proposal_validation', 'validation');
        $this->load->library('Seminar_proposal_helper', 'helper');
    }

    /**
     * ========================================
     * HALAMAN UTAMA SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Halaman utama menu seminar proposal mahasiswa
     */
    public function index() {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data proposal mahasiswa yang aktif
        $this->db->select('*');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('status_pembimbing', '1'); // Sudah disetujui pembimbing
        $this->db->where('workflow_status', 'bimbingan'); // Masih tahap bimbingan atau sudah seminar_proposal
        $this->db->or_where('workflow_status', 'seminar_proposal');
        $this->db->order_by('created_at', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        // Untuk setiap proposal, cek status seminar proposal
        foreach ($data['proposals'] as &$proposal) {
            // Cek syarat jurnal bimbingan
            $syarat_jurnal = $this->seminar_model->cek_syarat_jurnal_bimbingan($proposal->id);
            $proposal->syarat_jurnal = $syarat_jurnal;
            
            // Cek status pengajuan seminar proposal
            $seminar_proposal = $this->seminar_model->get_seminar_by_proposal_id($proposal->id);
            $proposal->seminar_proposal = $seminar_proposal;
            
            // Status yang bisa ditampilkan ke mahasiswa
            $proposal->status_display = $this->_get_status_display($proposal, $seminar_proposal);
            
            // Cek apakah bisa mengajukan/mengajukan ulang
            $can_ajukan = Seminar_proposal_helper::can_mahasiswa_ajukan_seminar($proposal->id);
            $proposal->can_ajukan = $can_ajukan;
        }
        
        $data['title'] = 'Seminar Proposal';
        $data['mahasiswa_name'] = $this->session->userdata('nama');
        
        $this->load->view('mahasiswa/seminar_proposal/index', $data);
    }
    
    /**
     * Generate status display untuk mahasiswa
     */
    private function _get_status_display($proposal, $seminar_proposal) {
        if (!$seminar_proposal) {
            $syarat = $this->seminar_model->cek_syarat_jurnal_bimbingan($proposal->id);
            if ($syarat['memenuhi_syarat']) {
                return [
                    'status' => 'belum_ajukan',
                    'text' => 'Belum mengajukan seminar proposal',
                    'color' => 'secondary',
                    'icon' => 'file-plus',
                    'action' => 'Ajukan Seminar Proposal'
                ];
            } else {
                return [
                    'status' => 'syarat_belum_terpenuhi',
                    'text' => "Syarat belum terpenuhi (perlu {$syarat['kekurangan']} jurnal lagi)",
                    'color' => 'warning',
                    'icon' => 'alert-triangle',
                    'action' => null
                ];
            }
        }
        
        // Status berdasarkan workflow seminar proposal
        return Seminar_proposal_helper::format_status_seminar($seminar_proposal);
    }

    /**
     * ========================================
     * PENGAJUAN SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Halaman form pengajuan seminar proposal
     */
    public function ajukan($proposal_id = null) {
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak valid!');
            redirect('mahasiswa/seminar');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi proposal milik mahasiswa
        $proposal = $this->db->get_where('proposal_mahasiswa', [
            'id' => $proposal_id,
            'mahasiswa_id' => $mahasiswa_id,
            'status_pembimbing' => '1'
        ])->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau belum disetujui pembimbing!');
            redirect('mahasiswa/seminar');
        }
        
        // Cek syarat jurnal bimbingan
        $syarat_jurnal = $this->seminar_model->cek_syarat_jurnal_bimbingan($proposal_id);
        if (!$syarat_jurnal['memenuhi_syarat']) {
            $this->session->set_flashdata('error', "Belum memenuhi syarat minimal {$syarat_jurnal['syarat_minimal']} jurnal bimbingan yang divalidasi. Saat ini: {$syarat_jurnal['jumlah_validasi']} jurnal.");
            redirect('mahasiswa/seminar');
        }
        
        // Cek apakah sudah pernah mengajukan
        $existing_seminar = $this->seminar_model->get_seminar_by_proposal_id($proposal_id);
        
        $data['proposal'] = $proposal;
        $data['existing_seminar'] = $existing_seminar;
        $data['syarat_jurnal'] = $syarat_jurnal;
        $data['title'] = $existing_seminar ? 'Ajukan Ulang Seminar Proposal' : 'Ajukan Seminar Proposal';
        $data['is_resubmission'] = (bool) $existing_seminar;
        
        // Load daftar jurnal bimbingan yang sudah divalidasi
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1');
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $data['jurnal_validasi'] = $this->db->get()->result();
        
        $this->load->view('mahasiswa/seminar_proposal/ajukan', $data);
    }
    
    /**
     * Proses pengajuan seminar proposal (AJAX)
     */
    public function proses_pengajuan() {
        // Cek request method
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_pengajuan_mahasiswa());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Validasi file upload
        if (!isset($_FILES['file_proposal_seminar']) || $_FILES['file_proposal_seminar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => true, 'message' => 'File proposal wajib diupload!']);
            return;
        }
        
        $file_validation = Seminar_proposal_helper::validate_file_upload($_FILES['file_proposal_seminar'], 1);
        if (!$file_validation['valid']) {
            echo json_encode(['error' => true, 'message' => $file_validation['message']]);
            return;
        }
        
        // Scan malware
        if (!Seminar_proposal_helper::basic_malware_scan($_FILES['file_proposal_seminar']['tmp_name'])) {
            echo json_encode(['error' => true, 'message' => 'File tidak aman! Terdeteksi potensi malware.']);
            return;
        }
        
        // Prepare data untuk model
        $data = [
            'proposal_id' => $this->input->post('proposal_id'),
            'mahasiswa_id' => $this->session->userdata('id'),
            'keterangan_tambahan' => $this->input->post('keterangan_tambahan') ?? ''
        ];
        
        // Proses pengajuan
        $result = $this->seminar_model->ajukan_seminar_proposal($data);
        
        // Log aktivitas
        $this->_log_aktivitas($result['data']['seminar_id'] ?? null, 'pengajuan', 
                             'Mahasiswa mengajukan seminar proposal', $data);
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * LIHAT STATUS & HASIL SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Detail status seminar proposal
     */
    public function detail($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data seminar proposal lengkap
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->mahasiswa_id != $mahasiswa_id) {
            show_404();
        }
        
        // Ambil hasil seminar jika ada dan sudah dipublikasi
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->where('status_input', 'published'); // Hanya yang sudah dipublikasi
        
        $hasil_seminar = $this->db->get()->row();
        
        // Timeline aktivitas seminar
        $this->db->select('*');
        $this->db->from('log_aktivitas_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->order_by('created_at', 'ASC');
        
        $timeline = $this->db->get()->result();
        
        // Dokumen yang bisa didownload mahasiswa
        $this->db->select('*');
        $this->db->from('dokumen_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->where('is_public', 1);
        $this->db->or_like('access_roles', 'mahasiswa');
        
        $dokumen = $this->db->get()->result();
        
        $data['seminar'] = $seminar;
        $data['hasil_seminar'] = $hasil_seminar;
        $data['timeline'] = $timeline;
        $data['dokumen'] = $dokumen;
        $data['title'] = 'Detail Seminar Proposal';
        
        $this->load->view('mahasiswa/seminar_proposal/detail', $data);
    }
    
    /**
     * Lihat hasil seminar proposal (nilai & rekomendasi)
     */
    public function hasil($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data seminar dan hasil
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->mahasiswa_id != $mahasiswa_id) {
            show_404();
        }
        
        // Cek apakah hasil sudah dipublikasi
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->where('status_input', 'published');
        
        $hasil = $this->db->get()->row();
        
        if (!$hasil) {
            $this->session->set_flashdata('warning', 'Hasil seminar proposal belum tersedia atau belum dipublikasi.');
            redirect('mahasiswa/seminar/detail/' . $seminar_id);
        }
        
        $data['seminar'] = $seminar;
        $data['hasil'] = $hasil;
        $data['title'] = 'Hasil Seminar Proposal';
        
        // Format rekomendasi untuk display
        $data['rekomendasi_text'] = Seminar_proposal_helper::format_rekomendasi_penguji($hasil->rekomendasi_penguji);
        
        $this->load->view('mahasiswa/seminar_proposal/hasil', $data);
    }

    /**
     * ========================================
     * DOWNLOAD DOKUMEN
     * ========================================
     */
    
    /**
     * Download file proposal yang sudah diupload
     */
    public function download_proposal($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->mahasiswa_id != $mahasiswa_id) {
            show_404();
        }
        
        $file_path = FCPATH . 'uploads/seminar_proposal/' . $seminar->file_proposal_seminar;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('mahasiswa/seminar/detail/' . $seminar_id);
        }
        
        // Log download
        $this->_log_aktivitas($seminar_id, 'download', 'Mahasiswa mendownload file proposal');
        
        // Force download
        $this->load->helper('download');
        force_download($seminar->file_proposal_seminar, file_get_contents($file_path));
    }
    
    /**
     * Download dokumen seminar (undangan, berita acara, dll)
     */
    public function download_dokumen($dokumen_id = null) {
        if (!$dokumen_id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Cek akses dokumen
        $this->db->select('dsp.*, sp.mahasiswa_id');
        $this->db->from('dokumen_seminar_proposal dsp');
        $this->db->join('seminar_proposal sp', 'dsp.seminar_proposal_id = sp.id');
        $this->db->where('dsp.id', $dokumen_id);
        $this->db->where('sp.mahasiswa_id', $mahasiswa_id);
        $this->db->group_start();
        $this->db->where('dsp.is_public', 1);
        $this->db->or_like('dsp.access_roles', 'mahasiswa');
        $this->db->group_end();
        
        $dokumen = $this->db->get()->row();
        
        if (!$dokumen) {
            show_404();
        }
        
        if (!file_exists($dokumen->file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('mahasiswa/seminar');
        }
        
        // Update download count
        $this->db->where('id', $dokumen_id);
        $this->db->set('download_count', 'download_count + 1', FALSE);
        $this->db->set('last_downloaded_at', date('Y-m-d H:i:s'));
        $this->db->set('last_downloaded_by', $mahasiswa_id);
        $this->db->update('dokumen_seminar_proposal');
        
        // Log download
        $this->_log_aktivitas($dokumen->seminar_proposal_id, 'download', 
                             "Mahasiswa mendownload dokumen: {$dokumen->jenis_dokumen}");
        
        // Force download
        $this->load->helper('download');
        force_download($dokumen->nama_file, file_get_contents($dokumen->file_path));
    }

    /**
     * ========================================
     * AJAX METHODS
     * ========================================
     */
    
    /**
     * Cek syarat jurnal bimbingan via AJAX
     */
    public function cek_syarat_jurnal() {
        $proposal_id = $this->input->post('proposal_id');
        
        if (!$proposal_id) {
            echo json_encode(['error' => true, 'message' => 'ID Proposal tidak valid!']);
            return;
        }
        
        // Validasi ownership
        $mahasiswa_id = $this->session->userdata('id');
        $proposal = $this->db->get_where('proposal_mahasiswa', [
            'id' => $proposal_id,
            'mahasiswa_id' => $mahasiswa_id
        ])->row();
        
        if (!$proposal) {
            echo json_encode(['error' => true, 'message' => 'Proposal tidak ditemukan!']);
            return;
        }
        
        $syarat = $this->seminar_model->cek_syarat_jurnal_bimbingan($proposal_id);
        
        echo json_encode([
            'error' => false,
            'data' => $syarat
        ]);
    }
    
    /**
     * Get status terkini seminar proposal via AJAX
     */
    public function get_status_seminar() {
        $proposal_id = $this->input->post('proposal_id');
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$proposal_id) {
            echo json_encode(['error' => true, 'message' => 'ID Proposal tidak valid!']);
            return;
        }
        
        // Validasi ownership
        $proposal = $this->db->get_where('proposal_mahasiswa', [
            'id' => $proposal_id,
            'mahasiswa_id' => $mahasiswa_id
        ])->row();
        
        if (!$proposal) {
            echo json_encode(['error' => true, 'message' => 'Proposal tidak ditemukan!']);
            return;
        }
        
        $seminar = $this->seminar_model->get_seminar_by_proposal_id($proposal_id);
        $status_display = $this->_get_status_display($proposal, $seminar);
        
        echo json_encode([
            'error' => false,
            'data' => [
                'seminar' => $seminar,
                'status_display' => $status_display
            ]
        ]);
    }

    /**
     * ========================================
     * HELPER METHODS
     * ========================================
     */
    
    /**
     * Log aktivitas mahasiswa
     */
    private function _log_aktivitas($seminar_id, $jenis, $deskripsi, $data = null) {
        if (!$seminar_id) return;
        
        $log_data = [
            'seminar_proposal_id' => $seminar_id,
            'jenis_aktivitas' => $jenis,
            'deskripsi' => $deskripsi,
            'dilakukan_oleh' => $this->session->userdata('id'),
            'role_pelaku' => 'mahasiswa',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'data_perubahan' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_aktivitas_seminar_proposal', $log_data);
    }
    
    /**
     * Validasi mahasiswa memiliki akses ke seminar
     */
    private function _validate_seminar_access($seminar_id) {
        $mahasiswa_id = $this->session->userdata('id');
        
        $seminar = $this->db->get_where('seminar_proposal', [
            'id' => $seminar_id,
            'mahasiswa_id' => $mahasiswa_id
        ])->row();
        
        return $seminar ? $seminar : false;
    }
    
    /**
     * Format ukuran file untuk display
     */
    private function _format_file_size($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Generate breadcrumb untuk navigation
     */
    private function _generate_breadcrumb($seminar = null) {
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => 'mahasiswa'],
            ['title' => 'Seminar Proposal', 'url' => 'mahasiswa/seminar']
        ];
        
        if ($seminar) {
            $breadcrumb[] = [
                'title' => 'Detail Seminar', 
                'url' => 'mahasiswa/seminar/detail/' . $seminar->id
            ];
        }
        
        return $breadcrumb;
    }
}