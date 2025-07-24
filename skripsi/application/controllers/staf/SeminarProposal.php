<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Seminar Proposal
 * Mengelola penjadwalan, berita acara, dan export dokumen seminar proposal
 */
class Seminar_Proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        $this->load->library(['pdf', 'upload']);
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu seminar proposal
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status = $this->input->get('status');
        $periode = $this->input->get('periode');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            d2.nama as nama_penguji,
            r.nama as nama_ruangan
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_proposal = r.id', 'left');
        
        // Filter hanya yang sudah tahap seminar proposal
        $this->db->where_in('pm.workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status !== '') {
            if ($status == 'dijadwalkan') {
                $this->db->where('pm.status_seminar_proposal', '1');
                $this->db->where('pm.tanggal_seminar_proposal IS NOT NULL');
            } elseif ($status == 'menunggu_jadwal') {
                $this->db->where('pm.status_seminar_proposal', '1');
                $this->db->where('pm.tanggal_seminar_proposal IS NULL');
            } elseif ($status == 'selesai') {
                $this->db->where('pm.status_seminar_proposal', '2');
            }
        }
        
        if ($periode) {
            $this->db->where('DATE_FORMAT(pm.tanggal_seminar_proposal, "%Y-%m")', $periode);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('d1.nama', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('pm.tanggal_seminar_proposal', 'ASC');
        
        $data['seminar_proposal'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'periode' => $periode,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_seminar_proposal_stats();
        
        $this->load->view('staf/seminar_proposal/index', $data);
    }

    /**
     * Detail seminar proposal mahasiswa
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap seminar proposal
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing,
            d2.nama as nama_penguji, d2.nip as nip_penguji,
            r.nama as nama_ruangan, r.kapasitas
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_proposal = r.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Ambil berita acara jika ada
        $this->db->select('*');
        $this->db->from('berita_acara_seminar');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('jenis_seminar', 'proposal');
        
        $data['berita_acara'] = $this->db->get()->row();
        
        $this->load->view('staf/seminar_proposal/detail', $data);
    }

    /**
     * Export undangan seminar proposal
     */
    public function export_undangan($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF undangan
        $this->pdf->filename = 'Undangan_Seminar_Proposal_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_proposal/pdf_undangan', [
            'proposal' => $data['proposal'],
            'nomor_undangan' => $this->_generate_nomor_undangan(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_undangan_proposal', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export undangan seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export berita acara seminar proposal
     */
    public function export_berita_acara($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Ambil atau buat berita acara
        $this->db->select('*');
        $this->db->from('berita_acara_seminar');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('jenis_seminar', 'proposal');
        
        $berita_acara = $this->db->get()->row();
        
        // Generate PDF berita acara
        $this->pdf->filename = 'Berita_Acara_Seminar_Proposal_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_proposal/pdf_berita_acara', [
            'proposal' => $data['proposal'],
            'berita_acara' => $berita_acara,
            'nomor_berita_acara' => $this->_generate_nomor_berita_acara(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara_proposal', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export berita acara seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export form penilaian seminar proposal
     */
    public function export_form_penilaian($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF form penilaian
        $this->pdf->filename = 'Form_Penilaian_Seminar_Proposal_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_proposal/pdf_form_penilaian', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_form_penilaian_proposal', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export form penilaian seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Helper: Ambil data seminar lengkap
     */
    private function _get_seminar_data($proposal_id) {
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing,
            d2.nama as nama_penguji, d2.nip as nip_penguji,
            r.nama as nama_ruangan, r.lantai, r.gedung
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_proposal = r.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        return $data;
    }

    /**
     * Generate nomor undangan
     */
    private function _generate_nomor_undangan() {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah undangan bulan ini
        $this->db->where('MONTH(tanggal_seminar_proposal)', $bulan);
        $this->db->where('YEAR(tanggal_seminar_proposal)', $tahun);
        $count = $this->db->count_all_results('proposal_mahasiswa') + 1;
        
        return sprintf("%03d/STK-SY/UND-SP/%s/%s", $count, $bulan, $tahun);
    }

    /**
     * Generate nomor berita acara
     */
    private function _generate_nomor_berita_acara() {
        $tahun = date('Y');
        $bulan = date('m');
        
        $this->db->where('MONTH(created_at)', $bulan);
        $this->db->where('YEAR(created_at)', $tahun);
        $this->db->where('jenis_seminar', 'proposal');
        $count = $this->db->count_all_results('berita_acara_seminar') + 1;
        
        return sprintf("%03d/STK-SY/BA-SP/%s/%s", $count, $bulan, $tahun);
    }

    /**
     * Statistik seminar proposal
     */
    private function _get_seminar_proposal_stats() {
        $stats = [];
        
        // Total yang sudah tahap seminar proposal
        $this->db->where_in('workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        $stats['total_seminar_proposal'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah dijadwalkan
        $this->db->where('status_seminar_proposal', '1');
        $this->db->where('tanggal_seminar_proposal IS NOT NULL');
        $stats['sudah_dijadwalkan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang menunggu jadwal
        $this->db->where('status_seminar_proposal', '1');
        $this->db->where('tanggal_seminar_proposal IS NULL');
        $stats['menunggu_jadwal'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah selesai seminar proposal
        $this->db->where('status_seminar_proposal', '2');
        $stats['sudah_selesai'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Seminar bulan ini
        $this->db->where('MONTH(tanggal_seminar_proposal)', date('m'));
        $this->db->where('YEAR(tanggal_seminar_proposal)', date('Y'));
        $stats['seminar_bulan_ini'] = $this->db->count_all_results('proposal_mahasiswa');
        
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