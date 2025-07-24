<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Publikasi
 * Mengelola publikasi tugas akhir dan validasi repository
 */
class Publikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        $this->load->library('pdf');
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu publikasi
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status_publikasi = $this->input->get('status_publikasi');
        $validasi_staf = $this->input->get('validasi_staf');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            ds.nama as nama_staf_validator
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen ds', 'pm.staf_validator_id = ds.id', 'left');
        
        // Filter hanya yang sudah masuk tahap publikasi atau selesai
        $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status_publikasi !== '') {
            $this->db->where('pm.status_publikasi', $status_publikasi);
        }
        
        if ($validasi_staf !== '') {
            if ($validasi_staf == 'null') {
                $this->db->where('(pm.validasi_staf_publikasi IS NULL OR pm.validasi_staf_publikasi = "0")');
            } else {
                $this->db->where('pm.validasi_staf_publikasi', $validasi_staf);
            }
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('pm.link_repository', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('pm.tanggal_review_publikasi', 'DESC');
        $this->db->order_by('pm.created_at', 'DESC');
        
        $data['publikasi'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status_publikasi' => $status_publikasi,
            'validasi_staf' => $validasi_staf,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_publikasi_stats();
        
        $this->load->view('staf/publikasi/index', $data);
    }

    /**
     * Detail publikasi mahasiswa
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap publikasi
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing,
            ds.nama as nama_staf_validator
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen ds', 'pm.staf_validator_id = ds.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal'] || !in_array($data['proposal']->workflow_status, ['publikasi', 'selesai'])) {
            show_404();
        }
        
        $this->load->view('staf/publikasi/detail', $data);
    }

    /**
     * Upload/Update link repository publikasi
     */
    public function update_repository($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url|max_length[500]');
        $this->form_validation->set_rules('tanggal_publikasi', 'Tanggal Publikasi', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('staf/publikasi/detail/' . $proposal_id);
        }
        
        // Validasi proposal
        $proposal = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id])->row();
        if (!$proposal || !in_array($proposal->workflow_status, ['publikasi', 'selesai'])) {
            show_404();
        }
        
        $update_data = [
            'link_repository' => $this->input->post('link_repository'),
            'tanggal_publikasi' => $this->input->post('tanggal_publikasi'),
            'staf_validator_id' => $this->session->userdata('id'),
            'validasi_staf_publikasi' => '1', // Otomatis valid karena diinput oleh staf
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'catatan_staf' => $this->input->post('catatan_staf')
        ];
        
        $this->db->where('id', $proposal_id);
        $update = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update) {
            // Log aktivitas
            $this->_log_aktivitas('upload_repository', $proposal->mahasiswa_id, $proposal_id, 
                                 "Upload link repository publikasi {$proposal->nama_mahasiswa}");
            
            $this->session->set_flashdata('success', 'Link repository publikasi berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan link repository publikasi');
        }
        
        redirect('staf/publikasi/detail/' . $proposal_id);
    }

    /**
     * Validasi publikasi tugas akhir
     */
    public function validasi_publikasi($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $this->form_validation->set_rules('validasi_staf_publikasi', 'Status Validasi', 'required|in_list[1,2]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('staf/publikasi/detail/' . $proposal_id);
        }
        
        // Validasi proposal
        $proposal = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id])->row();
        if (!$proposal || $proposal->workflow_status != 'publikasi') {
            show_404();
        }
        
        $validasi_status = $this->input->post('validasi_staf_publikasi');
        $catatan_staf = $this->input->post('catatan_staf');
        
        $update_data = [
            'validasi_staf_publikasi' => $validasi_status,
            'staf_validator_id' => $this->session->userdata('id'),
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'catatan_staf' => $catatan_staf
        ];
        
        // Jika disetujui, ubah workflow status ke selesai
        if ($validasi_status == '1') {
            $update_data['workflow_status'] = 'selesai';
        }
        
        $this->db->where('id', $proposal_id);
        $update = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update) {
            // Log aktivitas
            $status_text = $validasi_status == '1' ? 'Disetujui' : 'Perlu Perbaikan';
            $this->_log_aktivitas('validasi_publikasi', $proposal->mahasiswa_id, $proposal_id, 
                                 "Validasi publikasi: {$status_text}");
            
            $this->session->set_flashdata('success', 'Validasi publikasi berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan validasi publikasi');
        }
        
        redirect('staf/publikasi/detail/' . $proposal_id);
    }

    /**
     * Export PDF Sertifikat Penyelesaian
     */
    public function export_sertifikat($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap untuk sertifikat
        $data = $this->_get_publikasi_data($proposal_id);
        
        if (!$data['proposal'] || $data['proposal']->workflow_status != 'selesai') {
            show_404();
        }
        
        // Generate PDF Sertifikat
        $this->_generate_sertifikat_pdf($data);
    }

    /**
     * Export PDF Laporan Publikasi
     */
    public function export_laporan_publikasi() {
        $prodi_id = $this->input->post('prodi_id');
        $tahun_akademik = $this->input->post('tahun_akademik');
        $status_publikasi = $this->input->post('status_publikasi');
        
        // Query dengan filter
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            ds.nama as nama_staf_validator
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen ds', 'pm.staf_validator_id = ds.id', 'left');
        
        $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
        
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($tahun_akademik) {
            $this->db->where('YEAR(pm.tanggal_publikasi)', $tahun_akademik);
        }
        
        if ($status_publikasi !== '') {
            $this->db->where('pm.status_publikasi', $status_publikasi);
        }
        
        $this->db->order_by('pm.tanggal_publikasi', 'DESC');
        
        $data = $this->db->get()->result();
        
        if (empty($data)) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk di-export');
            redirect('staf/publikasi');
        }
        
        // Generate PDF laporan
        $this->_generate_laporan_publikasi_pdf($data, [
            'prodi_id' => $prodi_id,
            'tahun_akademik' => $tahun_akademik,
            'status_publikasi' => $status_publikasi
        ]);
    }

    /**
     * Daftar mahasiswa yang sudah selesai (untuk referensi)
     */
    public function mahasiswa_selesai() {
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.workflow_status', 'selesai');
        $this->db->where('pm.validasi_staf_publikasi', '1');
        $this->db->order_by('pm.tanggal_publikasi', 'DESC');
        
        $data['mahasiswa_selesai'] = $this->db->get()->result();
        
        $this->load->view('staf/publikasi/mahasiswa_selesai', $data);
    }

    /**
     * Ambil data lengkap publikasi untuk PDF
     */
    private function _get_publikasi_data($proposal_id) {
        // Data proposal dan mahasiswa
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat, m.ipk,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas, f.dekan as nama_dekan,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing,
            ds.nama as nama_staf_validator
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen ds', 'pm.staf_validator_id = ds.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $proposal = $this->db->get()->row();
        
        return [
            'proposal' => $proposal
        ];
    }

    /**
     * Generate PDF Sertifikat Penyelesaian
     */
    private function _generate_sertifikat_pdf($data) {
        $this->pdf->setPaper('A4', 'landscape');
        $this->pdf->filename = "Sertifikat_Penyelesaian_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/publikasi/pdf_sertifikat', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('validasi_publikasi', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export sertifikat penyelesaian {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Laporan Publikasi
     */
    private function _generate_laporan_publikasi_pdf($data, $filters) {
        $this->pdf->setPaper('A4', 'landscape');
        $this->pdf->filename = "Laporan_Publikasi_" . date('Y-m-d') . ".pdf";
        
        // Siapkan data untuk view
        $prodi_name = '';
        if ($filters['prodi_id']) {
            $prodi = $this->db->get_where('prodi', ['id' => $filters['prodi_id']])->row();
            $prodi_name = $prodi ? $prodi->nama : '';
        }
        
        $html = $this->load->view('staf/publikasi/pdf_laporan', [
            'data' => $data,
            'filters' => $filters,
            'prodi_name' => $prodi_name,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('validasi_publikasi', null, null, "Export laporan publikasi dengan filter");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Statistik publikasi
     */
    private function _get_publikasi_stats() {
        $stats = [];
        
        // Total yang sudah tahap publikasi
        $this->db->where_in('workflow_status', ['publikasi', 'selesai']);
        $stats['total_publikasi'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang menunggu validasi staf
        $this->db->where('workflow_status', 'publikasi');
        $this->db->where('status_publikasi', '1');
        $this->db->where('(validasi_staf_publikasi = "0" OR validasi_staf_publikasi IS NULL)');
        $stats['menunggu_validasi'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah divalidasi staf (disetujui)
        $this->db->where('validasi_staf_publikasi', '1');
        $stats['sudah_divalidasi'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang perlu perbaikan
        $this->db->where('validasi_staf_publikasi', '2');
        $stats['perlu_perbaikan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah selesai
        $this->db->where('workflow_status', 'selesai');
        $stats['sudah_selesai'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Publikasi bulan ini
        $this->db->where('MONTH(tanggal_publikasi)', date('m'));
        $this->db->where('YEAR(tanggal_publikasi)', date('Y'));
        $stats['publikasi_bulan_ini'] = $this->db->count_all_results('proposal_mahasiswa');
        
        return $stats;
    }

    /**
     * Log aktivitas staf
     */
    private function _log_aktivitas($aktivitas, $mahasiswa_id = null, $proposal_id = null, $keterangan = '') {
        $data = [
            'staf_id' => $this->session->userdata('id'),
            'aktivitas' => $aktivitas,
            'mahasiswa_id' => $mahasiswa_id,
            'proposal_id' => $proposal_id,
            'keterangan' => $keterangan,
            'tanggal_aktivitas' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('staf_aktivitas', $data);
    }
}