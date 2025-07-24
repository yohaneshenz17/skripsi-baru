<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Bimbingan
 * Mengelola monitoring dan export jurnal bimbingan
 */
class Bimbingan extends CI_Controller {

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
     * Halaman utama menu bimbingan
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $dosen_id = $this->input->get('dosen_id');
        $status = $this->input->get('status');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            COUNT(jb.id) as total_bimbingan,
            MAX(jb.tanggal_bimbingan) as last_bimbingan
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        
        // Filter hanya yang sudah ada pembimbing
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->where('pm.dosen_id IS NOT NULL');
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($dosen_id) {
            $this->db->where('pm.dosen_id', $dosen_id);
        }
        
        if ($status !== '') {
            if ($status == 'aktif') {
                $this->db->where_in('pm.workflow_status', ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi']);
            } elseif ($status == 'selesai') {
                $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
            }
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('d1.nama', $search);
            $this->db->group_end();
        }
        
        $this->db->group_by('pm.id');
        $this->db->order_by('pm.created_at', 'DESC');
        
        $data['bimbingan'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        
        // Daftar dosen pembimbing
        $this->db->select('DISTINCT d.id, d.nama');
        $this->db->from('dosen d');
        $this->db->join('proposal_mahasiswa pm', 'd.id = pm.dosen_id');
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->order_by('d.nama', 'ASC');
        $data['dosen_list'] = $this->db->get()->result();
        
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'dosen_id' => $dosen_id,
            'status' => $status,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_bimbingan_stats();
        
        $this->load->view('staf/bimbingan/index', $data);
    }

    /**
     * Detail mahasiswa bimbingan
     */
    public function detail_mahasiswa($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data proposal dan mahasiswa
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.email as email_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Ambil jurnal bimbingan
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('tanggal_bimbingan', 'DESC');
        
        $data['jurnal_bimbingan'] = $this->db->get()->result();
        
        $this->load->view('staf/bimbingan/detail', $data);
    }

    /**
     * Export jurnal bimbingan mahasiswa ke PDF
     */
    public function export_jurnal($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data proposal dan mahasiswa (sama seperti detail_mahasiswa)
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Ambil jurnal bimbingan
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('tanggal_bimbingan', 'ASC');
        
        $data['jurnal_bimbingan'] = $this->db->get()->result();
        
        // Generate PDF
        $this->pdf->filename = 'Jurnal_Bimbingan_' . $data['proposal']->nim . '_' . date('Y-m-d') . '.pdf';
        
        $html = $this->load->view('staf/bimbingan/pdf_jurnal', [
            'proposal' => $data['proposal'],
            'jurnal_bimbingan' => $data['jurnal_bimbingan'],
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_jurnal', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export jurnal bimbingan {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Export semua jurnal bimbingan (bulk export)
     */
    public function export_all() {
        $proposal_ids = $this->input->post('proposal_ids');
        
        if (empty($proposal_ids)) {
            $this->session->set_flashdata('error', 'Pilih minimal satu mahasiswa untuk di-export');
            redirect('staf/bimbingan');
        }
        
        // Buat ZIP file untuk multiple PDF
        $this->load->library('zip');
        
        foreach ($proposal_ids as $proposal_id) {
            // Generate PDF untuk setiap mahasiswa
            $pdf_content = $this->_generate_jurnal_pdf($proposal_id);
            
            if ($pdf_content) {
                $filename = "Jurnal_Bimbingan_{$proposal_id}.pdf";
                $this->zip->add_data($filename, $pdf_content);
            }
        }
        
        // Download ZIP file
        $zip_filename = 'Jurnal_Bimbingan_' . date('Y-m-d') . '.zip';
        $this->zip->download($zip_filename);
    }

    /**
     * Generate PDF untuk jurnal bimbingan (helper function)
     */
    private function _generate_jurnal_pdf($proposal_id) {
        // Sama seperti export_jurnal tapi return string
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            return false;
        }
        
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('tanggal_bimbingan', 'ASC');
        
        $jurnal_bimbingan = $this->db->get()->result();
        
        // Generate PDF
        $pdf = new Pdf();
        $html = $this->load->view('staf/bimbingan/pdf_jurnal', [
            'proposal' => $proposal,
            'jurnal_bimbingan' => $jurnal_bimbingan,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $pdf->load_html($html);
        $pdf->render();
        
        return $pdf->output_string();
    }

    /**
     * Statistik bimbingan
     */
    private function _get_bimbingan_stats() {
        $stats = [];
        
        // Total mahasiswa bimbingan aktif
        $this->db->where('status_pembimbing', '1');
        $this->db->where('dosen_id IS NOT NULL');
        $stats['total_bimbingan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Jurnal yang valid
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('status_validasi', '1');
        $result = $this->db->get()->row();
        $stats['jurnal_valid'] = $result ? $result->total : 0;
        
        // Jurnal yang belum valid
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('status_validasi', '0');
        $result = $this->db->get()->row();
        $stats['jurnal_pending'] = $result ? $result->total : 0;
        
        // Bimbingan bulan ini
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('MONTH(tanggal_bimbingan)', date('m'));
        $this->db->where('YEAR(tanggal_bimbingan)', date('Y'));
        $result = $this->db->get()->row();
        $stats['bimbingan_bulan_ini'] = $result ? $result->total : 0;
        
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