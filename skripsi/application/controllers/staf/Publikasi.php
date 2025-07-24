<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Publikasi
 * Mengelola upload link repository dan validasi publikasi tugas akhir
 */
class Publikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        $this->load->library(['pdf', 'upload', 'form_validation']);
        
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
        $status = $this->input->get('status');
        $periode = $this->input->get('periode');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        
        // Filter hanya yang sudah tahap publikasi atau selesai
        $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status !== '') {
            if ($status == 'pending') {
                $this->db->where('pm.status_publikasi', '0');
            } elseif ($status == 'diajukan') {
                $this->db->where('pm.status_publikasi', '1');
                $this->db->where('(pm.validasi_staf_publikasi = "0" OR pm.validasi_staf_publikasi IS NULL)');
            } elseif ($status == 'validated') {
                $this->db->where('pm.validasi_staf_publikasi', '1');
                $this->db->where('(pm.validasi_kaprodi_publikasi = "0" OR pm.validasi_kaprodi_publikasi IS NULL)');
            } elseif ($status == 'approved') {
                $this->db->where('pm.validasi_kaprodi_publikasi', '1');
                $this->db->where('pm.workflow_status', 'selesai');
            }
        }
        
        if ($periode) {
            $this->db->where('DATE_FORMAT(pm.tanggal_publikasi, "%Y-%m")', $periode);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('pm.link_repository', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('pm.tanggal_publikasi', 'DESC');
        
        $data['publikasi'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'periode' => $periode,
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
        
        // Ambil log aktivitas publikasi
        $this->db->select('sa.*, d.nama as nama_staf');
        $this->db->from('staf_aktivitas sa');
        $this->db->join('dosen d', 'sa.staf_id = d.id');
        $this->db->where('sa.proposal_id', $proposal_id);
        $this->db->where_in('sa.aktivitas', ['input_repository', 'validasi_publikasi']);
        $this->db->order_by('sa.tanggal_aktivitas', 'DESC');
        
        $data['log_aktivitas'] = $this->db->get()->result();
        
        $this->load->view('staf/publikasi/detail', $data);
    }

    /**
     * Input link repository oleh staf
     */
    public function input_repository() {
        if ($this->input->method() == 'post') {
            $proposal_id = $this->input->post('proposal_id');
            $link_repository = $this->input->post('link_repository');
            $keterangan = $this->input->post('keterangan');
            
            // Validasi
            $this->form_validation->set_rules('proposal_id', 'Proposal ID', 'required|numeric');
            $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('staf/publikasi/detail/' . $proposal_id);
            }
            
            // Cek apakah proposal ada dan valid
            $this->db->where('id', $proposal_id);
            $this->db->where_in('workflow_status', ['publikasi', 'selesai']);
            $proposal = $this->db->get('proposal_mahasiswa')->row();
            
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau belum pada tahap publikasi');
                redirect('staf/publikasi');
            }
            
            // Update link repository
            $update_data = [
                'link_repository' => $link_repository,
                'tanggal_repository' => date('Y-m-d H:i:s'),
                'validasi_staf_publikasi' => '1',
                'keterangan_staf_publikasi' => $keterangan,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $proposal_id);
            $update = $this->db->update('proposal_mahasiswa', $update_data);
            
            if ($update) {
                // Log aktivitas
                $this->_log_aktivitas('input_repository', $proposal->mahasiswa_id, $proposal_id, 
                                     "Input link repository: {$link_repository}");
                
                $this->session->set_flashdata('success', 'Link repository berhasil disimpan dan divalidasi');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan link repository');
            }
            
            redirect('staf/publikasi/detail/' . $proposal_id);
        }
        
        // Jika GET request, tampilkan form
        $proposal_id = $this->input->get('proposal_id');
        
        if (!$proposal_id) {
            redirect('staf/publikasi');
        }
        
        // Ambil data proposal
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa,
            p.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('staf/publikasi');
        }
        
        $this->load->view('staf/publikasi/form_repository', $data);
    }

    /**
     * Validasi publikasi oleh staf
     */
    public function validasi() {
        if ($this->input->method() != 'post') {
            redirect('staf/publikasi');
        }
        
        $proposal_id = $this->input->post('proposal_id');
        $status_validasi = $this->input->post('status_validasi'); // 1 = setuju, 2 = tolak
        $keterangan = $this->input->post('keterangan');
        
        // Validasi input
        if (!$proposal_id || !in_array($status_validasi, ['1', '2'])) {
            $this->session->set_flashdata('error', 'Data tidak valid');
            redirect('staf/publikasi');
        }
        
        // Cek proposal
        $this->db->where('id', $proposal_id);
        $this->db->where('status_publikasi', '1'); // Harus sudah diajukan mahasiswa
        $proposal = $this->db->get('proposal_mahasiswa')->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau belum diajukan untuk publikasi');
            redirect('staf/publikasi');
        }
        
        // Update validasi staf
        $update_data = [
            'validasi_staf_publikasi' => $status_validasi,
            'keterangan_staf_publikasi' => $keterangan,
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $proposal_id);
        $update = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update) {
            $status_text = ($status_validasi == '1') ? 'disetujui' : 'ditolak';
            
            // Log aktivitas
            $this->_log_aktivitas('validasi_publikasi', $proposal->mahasiswa_id, $proposal_id, 
                                 "Publikasi {$status_text}: {$keterangan}");
            
            $this->session->set_flashdata('success', "Publikasi berhasil {$status_text}");
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses validasi');
        }
        
        redirect('staf/publikasi/detail/' . $proposal_id);
    }

    /**
     * Export laporan publikasi
     */
    public function export_laporan() {
        // Filter data
        $prodi_id = $this->input->get('prodi_id');
        $periode_start = $this->input->get('periode_start');
        $periode_end = $this->input->get('periode_end');
        
        // Query data publikasi
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where_in('pm.workflow_status', ['publikasi', 'selesai']);
        
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($periode_start && $periode_end) {
            $this->db->where('pm.tanggal_publikasi >=', $periode_start);
            $this->db->where('pm.tanggal_publikasi <=', $periode_end);
        }
        
        $this->db->order_by('pm.tanggal_publikasi', 'DESC');
        
        $data['publikasi'] = $this->db->get()->result();
        $data['periode_start'] = $periode_start;
        $data['periode_end'] = $periode_end;
        $data['prodi_filter'] = $prodi_id;
        
        // Generate PDF
        $this->pdf->filename = 'Laporan_Publikasi_' . date('Y-m-d') . '.pdf';
        
        $html = $this->load->view('staf/publikasi/pdf_laporan', [
            'publikasi' => $data['publikasi'],
            'periode_start' => $periode_start,
            'periode_end' => $periode_end,
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_laporan_publikasi', null, null, 
                             "Export laporan publikasi periode {$periode_start} - {$periode_end}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Bulk validasi publikasi
     */
    public function bulk_validasi() {
        if ($this->input->method() != 'post') {
            redirect('staf/publikasi');
        }
        
        $proposal_ids = $this->input->post('proposal_ids');
        $status_validasi = $this->input->post('status_validasi');
        $keterangan = $this->input->post('keterangan');
        
        if (empty($proposal_ids) || !in_array($status_validasi, ['1', '2'])) {
            $this->session->set_flashdata('error', 'Pilih minimal satu publikasi dan status validasi');
            redirect('staf/publikasi');
        }
        
        $success_count = 0;
        $status_text = ($status_validasi == '1') ? 'disetujui' : 'ditolak';
        
        foreach ($proposal_ids as $proposal_id) {
            // Update validasi
            $update_data = [
                'validasi_staf_publikasi' => $status_validasi,
                'keterangan_staf_publikasi' => $keterangan,
                'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $proposal_id);
            $this->db->where('status_publikasi', '1');
            $update = $this->db->update('proposal_mahasiswa', $update_data);
            
            if ($update) {
                $success_count++;
                
                // Get mahasiswa info for log
                $this->db->select('mahasiswa_id');
                $this->db->where('id', $proposal_id);
                $proposal = $this->db->get('proposal_mahasiswa')->row();
                
                if ($proposal) {
                    $this->_log_aktivitas('validasi_publikasi', $proposal->mahasiswa_id, $proposal_id, 
                                         "Bulk validasi: {$status_text}");
                }
            }
        }
        
        if ($success_count > 0) {
            $this->session->set_flashdata('success', "{$success_count} publikasi berhasil {$status_text}");
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses validasi');
        }
        
        redirect('staf/publikasi');
    }

    /**
     * Statistik publikasi
     */
    private function _get_publikasi_stats() {
        $stats = [];
        
        // Total publikasi
        $this->db->where_in('workflow_status', ['publikasi', 'selesai']);
        $stats['total_publikasi'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang diajukan mahasiswa
        $this->db->where('status_publikasi', '1');
        $stats['diajukan'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah divalidasi staf
        $this->db->where('validasi_staf_publikasi', '1');
        $stats['validated_staf'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah selesai (approved kaprodi)
        $this->db->where('validasi_kaprodi_publikasi', '1');
        $this->db->where('workflow_status', 'selesai');
        $stats['selesai'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang pending validasi staf
        $this->db->where('status_publikasi', '1');
        $this->db->where('(validasi_staf_publikasi = "0" OR validasi_staf_publikasi IS NULL)');
        $stats['pending_staf'] = $this->db->count_all_results('proposal_mahasiswa');
        
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