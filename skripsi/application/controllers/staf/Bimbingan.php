<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Bimbingan
 * Mengelola jurnal bimbingan dan export PDF
 */
class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        $this->load->library('pdf'); // Library untuk generate PDF
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu bimbingan
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status_validasi = $this->input->get('status_validasi');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('
            jb.*,
            pm.judul as judul_proposal,
            m.nim, m.nama as nama_mahasiswa, m.email,
            p.nama as nama_prodi,
            d.nama as nama_pembimbing,
            dv.nama as nama_validator
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen dv', 'jb.validasi_oleh = dv.id', 'left');
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status_validasi !== '') {
            $this->db->where('jb.status_validasi', $status_validasi);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('jb.materi_bimbingan', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('jb.tanggal_bimbingan', 'DESC');
        $this->db->order_by('jb.pertemuan_ke', 'DESC');
        
        $data['bimbingan'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status_validasi' => $status_validasi,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_bimbingan_stats();
        
        $this->load->view('staf/bimbingan/index', $data);
    }

    /**
     * Export PDF Jurnal Bimbingan per Mahasiswa
     */
    public function export_jurnal_pdf($mahasiswa_id) {
        if (!$mahasiswa_id) {
            show_404();
        }
        
        // Ambil data mahasiswa
        $this->db->select('m.*, p.nama as nama_prodi, pm.judul, pm.id as proposal_id');
        $this->db->from('mahasiswa m');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id');
        $this->db->where('m.id', $mahasiswa_id);
        $mahasiswa = $this->db->get()->row();
        
        if (!$mahasiswa) {
            show_404();
        }
        
        // Ambil data jurnal bimbingan
        $this->db->select('
            jb.*,
            d.nama as nama_pembimbing,
            dv.nama as nama_validator
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen dv', 'jb.validasi_oleh = dv.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        $jurnal = $this->db->get()->result();
        
        // Generate PDF
        $this->_generate_jurnal_pdf($mahasiswa, $jurnal);
    }

    /**
     * Export PDF Jurnal Bimbingan berdasarkan filter
     */
    public function export_filtered_pdf() {
        $prodi_id = $this->input->post('prodi_id');
        $status_validasi = $this->input->post('status_validasi');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        
        // Query dengan filter
        $this->db->select('
            jb.*,
            pm.judul as judul_proposal,
            m.nim, m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            d.nama as nama_pembimbing
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status_validasi !== '') {
            $this->db->where('jb.status_validasi', $status_validasi);
        }
        
        if ($tanggal_mulai) {
            $this->db->where('jb.tanggal_bimbingan >=', $tanggal_mulai);
        }
        
        if ($tanggal_selesai) {
            $this->db->where('jb.tanggal_bimbingan <=', $tanggal_selesai);
        }
        
        $this->db->order_by('m.nama', 'ASC');
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        
        $data = $this->db->get()->result();
        
        if (empty($data)) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk di-export');
            redirect('staf/bimbingan');
        }
        
        // Generate PDF laporan
        $this->_generate_laporan_bimbingan_pdf($data, [
            'prodi_id' => $prodi_id,
            'status_validasi' => $status_validasi,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai
        ]);
    }

    /**
     * Detail jurnal bimbingan mahasiswa
     */
    public function detail($mahasiswa_id) {
        if (!$mahasiswa_id) {
            show_404();
        }
        
        // Data mahasiswa
        $this->db->select('m.*, p.nama as nama_prodi, pm.judul, pm.id as proposal_id, d.nama as nama_pembimbing');
        $this->db->from('mahasiswa m');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('m.id', $mahasiswa_id);
        $data['mahasiswa'] = $this->db->get()->row();
        
        if (!$data['mahasiswa']) {
            show_404();
        }
        
        // Data jurnal bimbingan
        $this->db->select('jb.*, dv.nama as nama_validator');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('dosen dv', 'jb.validasi_oleh = dv.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        $data['jurnal'] = $this->db->get()->result();
        
        $this->load->view('staf/bimbingan/detail', $data);
    }

    /**
     * Statistik bimbingan
     */
    private function _get_bimbingan_stats() {
        $stats = [];
        
        // Total jurnal bimbingan
        $stats['total_jurnal'] = $this->db->count_all('jurnal_bimbingan');
        
        // Jurnal yang sudah divalidasi
        $this->db->where('status_validasi', '1');
        $stats['jurnal_valid'] = $this->db->count_all_results('jurnal_bimbingan');
        
        // Jurnal pending
        $this->db->where('status_validasi', '0');
        $stats['jurnal_pending'] = $this->db->count_all_results('jurnal_bimbingan');
        
        // Jurnal perlu revisi
        $this->db->where('status_validasi', '2');
        $stats['jurnal_revisi'] = $this->db->count_all_results('jurnal_bimbingan');
        
        // Mahasiswa aktif bimbingan
        $this->db->select('COUNT(DISTINCT pm.mahasiswa_id) as total');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->where('pm.workflow_status', 'bimbingan');
        $result = $this->db->get()->row();
        $stats['mahasiswa_aktif'] = $result ? $result->total : 0;
        
        return $stats;
    }

    /**
     * Generate PDF jurnal bimbingan individual
     */
    private function _generate_jurnal_pdf($mahasiswa, $jurnal) {
        // Setup PDF
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Jurnal_Bimbingan_{$mahasiswa->nim}_{$mahasiswa->nama}.pdf";
        
        // Load view untuk PDF
        $html = $this->load->view('staf/bimbingan/pdf_jurnal', [
            'mahasiswa' => $mahasiswa,
            'jurnal' => $jurnal,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_jurnal', $mahasiswa->id, null, "Export jurnal bimbingan {$mahasiswa->nama}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF laporan bimbingan
     */
    private function _generate_laporan_bimbingan_pdf($data, $filters) {
        $this->pdf->setPaper('A4', 'landscape');
        $this->pdf->filename = "Laporan_Bimbingan_" . date('Y-m-d') . ".pdf";
        
        // Siapkan data untuk view
        $prodi_name = '';
        if ($filters['prodi_id']) {
            $prodi = $this->db->get_where('prodi', ['id' => $filters['prodi_id']])->row();
            $prodi_name = $prodi ? $prodi->nama : '';
        }
        
        $html = $this->load->view('staf/bimbingan/pdf_laporan', [
            'data' => $data,
            'filters' => $filters,
            'prodi_name' => $prodi_name,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_jurnal', null, null, "Export laporan bimbingan dengan filter");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
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