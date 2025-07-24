<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Seminar Skripsi
 * Mengelola seminar skripsi dan export PDF berita acara
 */
class SeminarSkripsi extends CI_Controller {

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
     * Halaman utama menu seminar skripsi
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status = $this->input->get('status');
        $bulan = $this->input->get('bulan');
        $tahun = $this->input->get('tahun');
        
        // Base query
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            d2.nama as nama_penguji1,
            d3.nama as nama_penguji2
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('dosen d3', 'pm.dosen_penguji2_id = d3.id', 'left');
        
        // Filter hanya yang sudah masuk tahap seminar skripsi atau lebih
        $this->db->where_in('pm.workflow_status', ['seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status) {
            $this->db->where('pm.status_seminar_skripsi', $status);
        }
        
        if ($bulan && $tahun) {
            $this->db->where('MONTH(pm.tanggal_seminar_skripsi)', $bulan);
            $this->db->where('YEAR(pm.tanggal_seminar_skripsi)', $tahun);
        } elseif ($tahun) {
            $this->db->where('YEAR(pm.tanggal_seminar_skripsi)', $tahun);
        }
        
        $this->db->order_by('pm.tanggal_seminar_skripsi', 'DESC');
        
        $data['seminar_skripsi'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        
        // Statistik
        $data['stats'] = $this->_get_seminar_skripsi_stats();
        
        $this->load->view('staf/seminar_skripsi/index', $data);
    }

    /**
     * Detail seminar skripsi
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap seminar skripsi
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat, m.ipk,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing, d1.nomor_telepon as telp_pembimbing,
            d2.nama as nama_penguji1, d2.nip as nip_penguji1, d2.nomor_telepon as telp_penguji1,
            d3.nama as nama_penguji2, d3.nip as nip_penguji2, d3.nomor_telepon as telp_penguji2
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('dosen d3', 'pm.dosen_penguji2_id = d3.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Cek apakah ada hasil seminar skripsi dari tabel penelitian
        $this->db->select('*');
        $this->db->from('hasil_penelitian');
        $this->db->where('penelitian_id', $proposal_id);
        $data['hasil_seminar'] = $this->db->get()->row();
        
        $this->load->view('staf/seminar_skripsi/detail', $data);
    }

    /**
     * Export PDF Berita Acara Seminar Skripsi
     */
    public function export_berita_acara($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap untuk berita acara
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF Berita Acara
        $this->_generate_berita_acara_pdf($data);
    }

    /**
     * Export PDF Form Penilaian Seminar Skripsi
     */
    public function export_form_penilaian($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap untuk form penilaian
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF Form Penilaian
        $this->_generate_form_penilaian_pdf($data);
    }

    /**
     * Export PDF Undangan Seminar Skripsi
     */
    public function export_undangan($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF Undangan
        $this->_generate_undangan_pdf($data);
    }

    /**
     * Export PDF Daftar Hadir Seminar Skripsi
     */
    public function export_daftar_hadir($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $data = $this->_get_seminar_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF Daftar Hadir
        $this->_generate_daftar_hadir_pdf($data);
    }

    /**
     * Update hasil seminar skripsi
     */
    public function update_hasil($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $this->form_validation->set_rules('berita_acara', 'Berita Acara', 'required');
        $this->form_validation->set_rules('masukan', 'Masukan/Catatan', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[1,2]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('staf/seminar_skripsi/detail/' . $proposal_id);
        }
        
        $data_hasil = [
            'penelitian_id' => $proposal_id,
            'berita_acara' => $this->input->post('berita_acara'),
            'masukan' => $this->input->post('masukan'),
            'status' => $this->input->post('status')
        ];
        
        // Cek apakah sudah ada hasil sebelumnya
        $existing = $this->db->get_where('hasil_penelitian', ['penelitian_id' => $proposal_id])->row();
        
        if ($existing) {
            $this->db->where('penelitian_id', $proposal_id);
            $update = $this->db->update('hasil_penelitian', $data_hasil);
        } else {
            $update = $this->db->insert('hasil_penelitian', $data_hasil);
        }
        
        if ($update) {
            // Jika lulus, update workflow status ke publikasi
            if ($this->input->post('status') == '1') {
                $this->db->where('id', $proposal_id);
                $this->db->update('proposal_mahasiswa', ['workflow_status' => 'publikasi']);
            }
            
            // Log aktivitas
            $this->_log_aktivitas('export_berita_acara', null, $proposal_id, "Update hasil seminar skripsi");
            
            $this->session->set_flashdata('success', 'Hasil seminar skripsi berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan hasil seminar skripsi');
        }
        
        redirect('staf/seminar_skripsi/detail/' . $proposal_id);
    }

    /**
     * Ambil data lengkap seminar untuk PDF
     */
    private function _get_seminar_data($proposal_id) {
        // Data proposal dan mahasiswa
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat, m.ipk,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas, f.dekan as nama_dekan,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing, d1.nomor_telepon as telp_pembimbing,
            d2.nama as nama_penguji1, d2.nip as nip_penguji1, d2.nomor_telepon as telp_penguji1,
            d3.nama as nama_penguji2, d3.nip as nip_penguji2, d3.nomor_telepon as telp_penguji2
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('dosen d3', 'pm.dosen_penguji2_id = d3.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $proposal = $this->db->get()->row();
        
        // Data hasil seminar jika ada
        $hasil_seminar = $this->db->get_where('hasil_penelitian', ['penelitian_id' => $proposal_id])->row();
        
        return [
            'proposal' => $proposal,
            'hasil_seminar' => $hasil_seminar
        ];
    }

    /**
     * Generate PDF Berita Acara Seminar Skripsi
     */
    private function _generate_berita_acara_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Berita_Acara_Seminar_Skripsi_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_berita_acara', [
            'proposal' => $data['proposal'],
            'hasil_seminar' => $data['hasil_seminar'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export berita acara seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Form Penilaian Seminar Skripsi
     */
    private function _generate_form_penilaian_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Form_Penilaian_Seminar_Skripsi_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_form_penilaian', [
            'proposal' => $data['proposal'],
            'hasil_seminar' => $data['hasil_seminar'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export form penilaian seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Undangan Seminar Skripsi
     */
    private function _generate_undangan_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Undangan_Seminar_Skripsi_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_undangan', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export undangan seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Daftar Hadir Seminar Skripsi
     */
    private function _generate_daftar_hadir_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Daftar_Hadir_Seminar_Skripsi_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_skripsi/pdf_daftar_hadir', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export daftar hadir seminar skripsi {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
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