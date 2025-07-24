<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Penelitian
 * Mengelola surat izin penelitian dan upload dokumen
 */
class Penelitian extends CI_Controller {

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
     * Halaman utama menu penelitian
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status_izin = $this->input->get('status_izin');
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
        
        // Filter hanya yang sudah masuk tahap penelitian atau lebih
        $this->db->where_in('pm.workflow_status', ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status_izin !== '') {
            $this->db->where('pm.status_izin_penelitian', $status_izin);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama', $search);
            $this->db->or_like('m.nim', $search);
            $this->db->or_like('pm.judul', $search);
            $this->db->or_like('pm.lokasi_penelitian', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('pm.created_at', 'DESC');
        
        $data['penelitian'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status_izin' => $status_izin,
            'search' => $search
        ];
        
        // Statistik
        $data['stats'] = $this->_get_penelitian_stats();
        
        $this->load->view('staf/penelitian/index', $data);
    }

    /**
     * Detail penelitian mahasiswa
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap penelitian
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing, d1.nomor_telepon as telp_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal'] || !in_array($data['proposal']->workflow_status, ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai'])) {
            show_404();
        }
        
        $this->load->view('staf/penelitian/detail', $data);
    }

    /**
     * Export PDF Surat Izin Penelitian
     */
    public function export_surat_izin($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap untuk surat izin
        $data = $this->_get_penelitian_data($proposal_id);
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate PDF Surat Izin Penelitian
        $this->_generate_surat_izin_pdf($data);
    }

    /**
     * Upload surat izin penelitian yang sudah ditandatangani
     */
    public function upload_surat_izin($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Validasi proposal
        $proposal = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id])->row();
        if (!$proposal || !in_array($proposal->workflow_status, ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai'])) {
            show_404();
        }
        
        // Konfigurasi upload
        $config['upload_path'] = './uploads/surat_izin_penelitian/';
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;
        
        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('surat_izin_file')) {
            $upload_data = $this->upload->data();
            
            // Update data proposal
            $update_data = [
                'surat_izin_penelitian' => $upload_data['file_name'],
                'status_izin_penelitian' => '1' // Disetujui setelah upload
            ];
            
            $this->db->where('id', $proposal_id);
            $update = $this->db->update('proposal_mahasiswa', $update_data);
            
            if ($update) {
                // Hapus file lama jika ada
                if ($proposal->surat_izin_penelitian && file_exists('./uploads/surat_izin_penelitian/' . $proposal->surat_izin_penelitian)) {
                    unlink('./uploads/surat_izin_penelitian/' . $proposal->surat_izin_penelitian);
                }
                
                // Log aktivitas
                $this->_log_aktivitas('upload_surat_izin', $proposal->mahasiswa_id, $proposal_id, 
                                     "Upload surat izin penelitian yang sudah ditandatangani");
                
                $this->session->set_flashdata('success', 'Surat izin penelitian berhasil diupload');
            } else {
                // Hapus file yang sudah diupload jika gagal update database
                unlink($config['upload_path'] . $upload_data['file_name']);
                $this->session->set_flashdata('error', 'Gagal menyimpan data surat izin penelitian');
            }
        } else {
            $this->session->set_flashdata('error', $this->upload->display_errors());
        }
        
        redirect('staf/penelitian/detail/' . $proposal_id);
    }

    /**
     * Download surat izin penelitian
     */
    public function download_surat_izin($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $proposal = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id])->row();
        
        if (!$proposal || !$proposal->surat_izin_penelitian) {
            show_404();
        }
        
        $file_path = './uploads/surat_izin_penelitian/' . $proposal->surat_izin_penelitian;
        
        if (!file_exists($file_path)) {
            show_404();
        }
        
        // Download file
        $this->load->helper('download');
        $filename = "Surat_Izin_Penelitian_{$proposal->mahasiswa_id}.pdf";
        force_download($filename, file_get_contents($file_path));
    }

    /**
     * Update status izin penelitian
     */
    public function update_status_izin($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        $status = $this->input->post('status_izin_penelitian');
        $catatan = $this->input->post('catatan_izin');
        
        if (!in_array($status, ['0', '1', '2'])) {
            $this->session->set_flashdata('error', 'Status tidak valid');
            redirect('staf/penelitian/detail/' . $proposal_id);
        }
        
        $update_data = [
            'status_izin_penelitian' => $status
        ];
        
        // Jika ada catatan, simpan sebagai komentar
        if ($catatan) {
            // Bisa ditambahkan field catatan_izin_penelitian di tabel proposal_mahasiswa
            $update_data['komentar_kaprodi'] = $catatan; // Sementara pakai field ini
        }
        
        $this->db->where('id', $proposal_id);
        $update = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update) {
            // Log aktivitas
            $proposal = $this->db->get_where('proposal_mahasiswa', ['id' => $proposal_id])->row();
            $status_text = ['0' => 'Pending', '1' => 'Disetujui', '2' => 'Ditolak'][$status];
            $this->_log_aktivitas('validasi_publikasi', $proposal->mahasiswa_id, $proposal_id, 
                                 "Update status izin penelitian: {$status_text}");
            
            $this->session->set_flashdata('success', 'Status izin penelitian berhasil diperbarui');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui status izin penelitian');
        }
        
        redirect('staf/penelitian/detail/' . $proposal_id);
    }

    /**
     * Ambil data lengkap penelitian untuk PDF
     */
    private function _get_penelitian_data($proposal_id) {
        // Data proposal dan mahasiswa
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email, m.nomor_telepon,
            m.tempat_lahir, m.tanggal_lahir, m.jenis_kelamin, m.alamat,
            p.nama as nama_prodi, p.kode as kode_prodi,
            f.nama as nama_fakultas, f.dekan as nama_dekan,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('fakultas f', 'p.fakultas_id = f.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $proposal = $this->db->get()->row();
        
        return [
            'proposal' => $proposal
        ];
    }

    /**
     * Generate PDF Surat Izin Penelitian
     */
    private function _generate_surat_izin_pdf($data) {
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->filename = "Surat_Izin_Penelitian_{$data['proposal']->nim}.pdf";
        
        // Generate nomor surat otomatis
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung urutan surat bulan ini
        $this->db->like('surat_izin_penelitian', $tahun . $bulan, 'after');
        $count = $this->db->count_all_results('proposal_mahasiswa') + 1;
        
        $nomor_surat = sprintf("%03d/STK-SY/IP/%s/%s", $count, $bulan, $tahun);
        
        $html = $this->load->view('staf/penelitian/pdf_surat_izin', [
            'proposal' => $data['proposal'],
            'nomor_surat' => $nomor_surat,
            'tanggal_surat' => date('d F Y'),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Log aktivitas
        $this->_log_aktivitas('export_surat_izin', $data['proposal']->mahasiswa_id, $data['proposal']->id, 
                             "Export surat izin penelitian {$data['proposal']->nama_mahasiswa}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Statistik penelitian
     */
    private function _get_penelitian_stats() {
        $stats = [];
        
        // Total yang sudah tahap penelitian
        $this->db->where_in('workflow_status', ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        $stats['total_penelitian'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang butuh surat izin penelitian
        $this->db->where('workflow_status', 'penelitian');
        $this->db->where('status_izin_penelitian', '0');
        $stats['butuh_surat_izin'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang sudah ada surat izin
        $this->db->where('status_izin_penelitian', '1');
        $this->db->where('surat_izin_penelitian IS NOT NULL');
        $stats['sudah_ada_surat'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Yang surat izinnya ditolak
        $this->db->where('status_izin_penelitian', '2');
        $stats['surat_ditolak'] = $this->db->count_all_results('proposal_mahasiswa');
        
        // Penelitian bulan ini
        $this->db->where('workflow_status', 'penelitian');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $stats['penelitian_bulan_ini'] = $this->db->count_all_results('proposal_mahasiswa');
        
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