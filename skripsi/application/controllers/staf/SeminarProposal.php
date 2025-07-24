<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Seminar Proposal
 * Mengelola seminar proposal dan export PDF berita acara
 */
class SeminarProposal extends CI_Controller {

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
     * Halaman utama menu seminar proposal
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
            d3.nama as nama_penguji2,
            k.nama as nama_kaprodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('dosen d2', 'pm.dosen_penguji_id = d2.id', 'left');
        $this->db->join('dosen d3', 'pm.dosen_penguji2_id = d3.id', 'left');
        $this->db->join('dosen k', 'pm.tanggal_review_seminar_proposal = k.id', 'left'); // Kaprodi yang mereview
        
        // Filter hanya yang sudah masuk tahap seminar proposal atau lebih
        $this->db->where_in('pm.workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status) {
            $this->db->where('pm.status_seminar_proposal', $status);
        }
        
        if ($bulan && $tahun) {
            $this->db->where('MONTH(pm.tanggal_seminar_proposal)', $bulan);
            $this->db->where('YEAR(pm.tanggal_seminar_proposal)', $tahun);
        } elseif ($tahun) {
            $this->db->where('YEAR(pm.tanggal_seminar_proposal)', $tahun);
        }
        
        $this->db->order_by('pm.tanggal_seminar_proposal', 'DESC');
        
        $data['seminar_proposal'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
        
        // Statistik
        $data['stats'] = $this->_get_seminar_proposal_stats();
        
        $this->load->view('staf/seminar_proposal/index', $data);
    }

    /**
     * Detail seminar proposal
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap seminar proposal
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
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
        
        // Cek apakah ada hasil seminar (berita acara)
        $this->db->select('*');
        $this->db->from('hasil_seminar');
        $this->db->where('seminar_id', $proposal_id);
        $data['hasil_seminar'] = $this->db->get()->row();
        
        $this->load->view('staf/seminar_proposal/detail', $data);
    }

    /**
     * Export PDF Berita Acara Seminar Proposal
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
     * Export PDF Form Penilaian Seminar Proposal
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
     * Export PDF Undangan Seminar Proposal
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
     * Update hasil seminar proposal
     */
    public function update_hasil($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $this->form_validation->set_rules('berita_acara', 'Berita Acara', 'required');
        $this->form_validation->set_rules('masukan', 'Masukan/Catatan', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[1,2,3]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('staf/seminar_proposal/detail/' . $proposal_id);
        }
        
        $data_hasil = [
            'seminar_id' => $proposal_id,
            'berita_acara' => $this->input->post('berita_acara'),
            'masukan' => $this->input->post('masukan'),
            'status' => $this->input->post('status')
        ];
        
        // Cek apakah sudah ada hasil sebelumnya
        $existing = $this->db->get_where('hasil_seminar', ['seminar_id' => $proposal_id])->row();
        
        if ($existing) {
            $this->db->where('seminar_id', $proposal_id);
            $update = $this->db->update('hasil_seminar', $data_hasil);
        } else {
            $update = $this->db->insert('hasil_seminar', $data_hasil);
        }
        
        if ($update) {
            // Log aktivitas
            $this->_log_aktivitas('export_berita_acara', null, $proposal_id, "Update hasil seminar proposal");
            
            $this->session->set_flashdata('success', 'Hasil seminar proposal berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan hasil seminar proposal');
        }
        
        redirect('staf/seminar_proposal/detail/' . $proposal_id);
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
        $hasil_seminar = $this->db->get_where('hasil_seminar', ['seminar_id' => $proposal_id])->row();
        
        return [
            'proposal' => $proposal,
            'hasil_seminar' => $hasil_seminar
        ];
    }

    /**
     * Generate PDF Berita Acara
     */
    private function _generate_berita_acara_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Berita_Acara_Seminar_Proposal_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_proposal/pdf_berita_acara', [
            'proposal' => $data['proposal'],
            'hasil_seminar' => $data['hasil_seminar'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export berita acara seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Form Penilaian
     */
    private function _generate_form_penilaian_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Form_Penilaian_Seminar_Proposal_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_proposal/pdf_form_penilaian', [
            'proposal' => $data['proposal'],
            'hasil_seminar' => $data['hasil_seminar'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export form penilaian seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Generate PDF Undangan
     */
    private function _generate_undangan_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Undangan_Seminar_Proposal_{$data['proposal']->nim}.pdf";
        
        $html = $this->load->view('staf/seminar_proposal/pdf_undangan', [
            'proposal' => $data['proposal'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_berita_acara', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export undangan seminar proposal {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Statistik seminar proposal
     */
    private function _get_seminar_proposal_stats() {
        $stats = [];
        
        // Total yang sudah tahap seminar proposal
        $this->db->where_in('workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        $stats['total_seminar_proposal'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah dijadwalkan seminar proposal
        $this->db->where('status_seminar_proposal', '1');
        $this->db->where('tanggal_seminar_proposal IS NOT NULL');
        $stats['sudah_dijadwalkan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sedang menunggu jadwal
        $this->db->where('status_seminar_proposal', '1');
        $this->db->where('tanggal_seminar_proposal IS NULL');
        $stats['menunggu_jadwal'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah selesai seminar proposal
        $this->db->where_in('workflow_status', ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
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