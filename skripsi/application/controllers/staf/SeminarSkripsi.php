<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Seminar Skripsi
 * Mengelola penjadwalan, berita acara, dan export dokumen seminar skripsi
 */
class Seminar_Skripsi extends CI_Controller {

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
     * Halaman utama menu seminar skripsi
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
        $this->db->join('dosen d2', 'pm.dosen_penguji_skripsi_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_skripsi = r.id', 'left');
        
        // Filter hanya yang sudah tahap seminar skripsi
        $this->db->where_in('pm.workflow_status', ['seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status !== '') {
            if ($status == 'dijadwalkan') {
                $this->db->where('pm.status_seminar_skripsi', '1');
                $this->db->where('pm.tanggal_seminar_skripsi IS NOT NULL');
            } elseif ($status == 'menunggu_jadwal') {
                $this->db->where('pm.status_seminar_skripsi', '1');
                $this->db->where('pm.tanggal_seminar_skripsi IS NULL');
            } elseif ($status == 'selesai') {
                $this->db->where('pm.status_seminar_skripsi', '2');
            } elseif ($status == 'lulus') {
                $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
            }
        }
        
        if ($periode) {
            $this->db->where('DATE_FORMAT(pm.tanggal_seminar_skripsi, "%Y-%m")', $periode);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('d1.nama', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('pm.tanggal_seminar_skripsi', 'ASC');
        
        $data['seminar_skripsi'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'periode' => $periode,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_seminar_skripsi_stats();
        
        $this->load->view('staf/seminar_skripsi/index', $data);
    }

    /**
     * Detail seminar skripsi mahasiswa
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap seminar skripsi
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
        $this->db->join('dosen d2', 'pm.dosen_penguji_skripsi_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_skripsi = r.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Ambil berita acara jika ada
        $this->db->select('*');
        $this->db->from('berita_acara_seminar');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('jenis_seminar', 'skripsi');
        
        $data['berita_acara'] = $this->db->get()->row();
        
        // Ambil hasil penelitian jika ada
        $this->db->select('*');
        $this->db->from('hasil_penelitian');
        $this->db->where('proposal_id', $proposal_id);
        
        $data['hasil_penelitian'] = $this->db->get()->row();
        
        $this->load->view('staf/seminar_skripsi/detail', $data);
    }

    /**
     * Export undangan seminar skripsi
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
        $this->pdf->filename = 'Undangan_Seminar_Skripsi_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_undangan', [
            'proposal' => $data['proposal'],
            'nomor_undangan' => $this->_generate_nomor_undangan(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_undangan_skripsi', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export undangan seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export berita acara seminar skripsi
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
        $this->db->where('jenis_seminar', 'skripsi');
        
        $berita_acara = $this->db->get()->row();
        
        // Ambil hasil penelitian
        $this->db->select('*');
        $this->db->from('hasil_penelitian');
        $this->db->where('proposal_id', $proposal_id);
        
        $hasil_penelitian = $this->db->get()->row();
        
        // Generate PDF berita acara
        $this->pdf->filename = 'Berita_Acara_Seminar_Skripsi_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_berita_acara', [
            'proposal' => $data['proposal'],
            'berita_acara' => $berita_acara,
            'hasil_penelitian' => $hasil_penelitian,
            'nomor_berita_acara' => $this->_generate_nomor_berita_acara(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara_skripsi', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export berita acara seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export form penilaian seminar skripsi
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
        $this->pdf->filename = 'Form_Penilaian_Seminar_Skripsi_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_form_penilaian', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_form_penilaian_skripsi', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export form penilaian seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export sertifikat lulus
     */
    public function export_sertifikat($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Cek apakah sudah lulus
        if (!in_array($data['proposal']->workflow_status, ['publikasi', 'selesai'])) {
            $this->session->set_flashdata('error', 'Mahasiswa belum lulus seminar skripsi');
            redirect('staf/seminar_skripsi/detail/' . $proposal_id);
        }
        
        // Generate PDF sertifikat
        $this->pdf->filename = 'Sertifikat_Lulus_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_sertifikat', [
            'proposal' => $data['proposal'],
            'nomor_sertifikat' => $this->_generate_nomor_sertifikat(),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_sertifikat_lulus', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export sertifikat lulus {$data['proposal']->nama_mahasiswa}");
        
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
        $this->db->join('dosen d2', 'pm.dosen_penguji_skripsi_id = d2.id', 'left');
        $this->db->join('ruangan r', 'pm.ruangan_seminar_skripsi = r.id', 'left');
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
        $this->db->where('MONTH(tanggal_seminar_skripsi)', $bulan);
        $this->db->where('YEAR(tanggal_seminar_skripsi)', $tahun);
        $count = $this->db->count_all_results('proposal_mahasiswa') + 1;
        
        return sprintf("%03d/STK-SY/UND-SS/%s/%s", $count, $bulan, $tahun);
    }

    /**
     * Generate nomor berita acara
     */
    private function _generate_nomor_berita_acara() {
        $tahun = date('Y');
        $bulan = date('m');
        
        $this->db->where('MONTH(created_at)', $bulan);
        $this->db->where('YEAR(created_at)', $tahun);
        $this->db->where('jenis_seminar', 'skripsi');
        $count = $this->db->count_all_results('berita_acara_seminar') + 1;
        
        return sprintf("%03d/STK-SY/BA-SS/%s/%s", $count, $bulan, $tahun);
    }

    /**
     * Generate nomor sertifikat
     */
    private function _generate_nomor_sertifikat() {
        $tahun = date('Y');
        
        $this->db->where_in('workflow_status', ['publikasi', 'selesai']);
        $this->db->where('YEAR(updated_at)', $tahun);
        $count = $this->db->count_all_results('proposal_mahasiswa') + 1;
        
        return sprintf("%03d/STK-SY/SERT/%s", $count, $tahun);
    }

    /**
     * Statistik seminar skripsi
     */
    private function _get_seminar_skripsi_stats() {
        $stats = [];
        
        // Total yang sudah tahap seminar skripsi
        $this->db->where_in('workflow_status', ['seminar_skripsi', 'publikasi', 'selesai']);
        $stats['total_seminar_skripsi'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah dijadwalkan seminar skripsi
        $this->db->where('status_seminar_skripsi', '1');
        $this->db->where('tanggal_seminar_skripsi IS NOT NULL');
        $stats['sudah_dijadwalkan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sedang menunggu jadwal
        $this->db->where('status_seminar_skripsi', '1');
        $this->db->where('tanggal_seminar_skripsi IS NULL');
        $stats['menunggu_jadwal'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah selesai seminar skripsi (lulus)
        $this->db->where_in('workflow_status', ['publikasi', 'selesai']);
        $stats['sudah_lulus'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Seminar bulan ini
        $this->db->where('MONTH(tanggal_seminar_skripsi)', date('m'));
        $this->db->where('YEAR(tanggal_seminar_skripsi)', date('Y'));
        $stats['seminar_bulan_ini'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang tidak lulus dan perlu mengulang
        $this->db->select('COUNT(*) as total');
        $this->db->from('hasil_penelitian');
        $this->db->where('status', '2'); // Tidak lulus
        $result = $this->db->get()->row();
        $stats['tidak_lulus'] = $result ? $result->total : 0;
        
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