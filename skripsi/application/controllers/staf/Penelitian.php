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
        $this->load->library(['pdf', 'upload', 'form_validation']);
        
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
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing, d1.email as email_pembimbing
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
        
        // Ambil log aktivitas penelitian
        $this->db->select('sa.*, d.nama as nama_staf');
        $this->db->from('staf_aktivitas sa');
        $this->db->join('dosen d', 'sa.staf_id = d.id');
        $this->db->where('sa.proposal_id', $proposal_id);
        $this->db->where_in('sa.aktivitas', ['export_surat_izin', 'upload_surat_izin']);
        $this->db->order_by('sa.tanggal_aktivitas', 'DESC');
        
        $data['log_aktivitas'] = $this->db->get()->result();
        
        $this->load->view('staf/penelitian/detail', $data);
    }

    /**
     * Cetak surat izin penelitian
     */
    public function cetak_surat($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data proposal
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.tempat_lahir, m.tanggal_lahir,
            m.alamat, m.nomor_telepon, m.email,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing, d1.nip as nip_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.workflow_status', 'penelitian');
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            show_404();
        }
        
        // Generate nomor surat
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah surat izin yang sudah dibuat bulan ini
        $this->db->where('workflow_status', 'penelitian');
        $this->db->where('DATE_FORMAT(created_at, "%Y-%m") <=', date('Y-m'));
        $this->db->where('status_izin_penelitian !=', '0');
        $count = $this->db->count_all_results('proposal_mahasiswa') + 1;
        
        $nomor_surat = sprintf("%03d/STK-SY/IP/%s/%s", $count, $bulan, $tahun);
        
        // Generate PDF
        $this->pdf->filename = 'Surat_Izin_Penelitian_' . $data['proposal']->nim . '.pdf';
        
        $html = $this->load->view('staf/penelitian/pdf_surat_izin', [
            'proposal' => $data['proposal'],
            'nomor_surat' => $nomor_surat,
            'tanggal_surat' => date('d F Y'),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ], true);
        
        $this->pdf->load_html($html);
        $this->pdf->render();
        
        // Update status jika belum ada
        if ($data['proposal']->status_izin_penelitian == '0') {
            $this->db->where('id', $proposal_id);
            $this->db->update('proposal_mahasiswa', [
                'status_izin_penelitian' => '1',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Log aktivitas
        $this->_log_aktivitas('export_surat_izin', $data['proposal']->mahasiswa_id, $proposal_id, 
                             "Export surat izin penelitian nomor: {$nomor_surat}");
        
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }

    /**
     * Upload surat izin yang sudah ditandatangani
     */
    public function upload_surat() {
        if ($this->input->method() == 'post') {
            $proposal_id = $this->input->post('proposal_id');
            
            // Validasi
            $this->form_validation->set_rules('proposal_id', 'Proposal ID', 'required|numeric');
            
            if ($this->form_validation->run() == FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('staf/penelitian/detail/' . $proposal_id);
            }
            
            // Cek proposal
            $this->db->where('id', $proposal_id);
            $this->db->where('workflow_status', 'penelitian');
            $proposal = $this->db->get('proposal_mahasiswa')->row();
            
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
                redirect('staf/penelitian');
            }
            
            // Config upload
            $config['upload_path'] = './uploads/surat_izin/';
            $config['allowed_types'] = 'pdf';
            $config['max_size'] = 5120; // 5MB
            $config['encrypt_name'] = TRUE;
            
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }
            
            $this->upload->initialize($config);
            
            if (!$this->upload->do_upload('surat_file')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('staf/penelitian/detail/' . $proposal_id);
            }
            
            $upload_data = $this->upload->data();
            
            // Update database
            $update_data = [
                'surat_izin_penelitian' => $upload_data['file_name'],
                'tanggal_upload_surat' => date('Y-m-d H:i:s'),
                'status_izin_penelitian' => '1',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $proposal_id);
            $update = $this->db->update('proposal_mahasiswa', $update_data);
            
            if ($update) {
                // Hapus file lama jika ada
                if ($proposal->surat_izin_penelitian && file_exists('./uploads/surat_izin/' . $proposal->surat_izin_penelitian)) {
                    unlink('./uploads/surat_izin/' . $proposal->surat_izin_penelitian);
                }
                
                // Log aktivitas
                $this->_log_aktivitas('upload_surat_izin', $proposal->mahasiswa_id, $proposal_id, 
                                     "Upload surat izin penelitian: {$upload_data['file_name']}");
                
                $this->session->set_flashdata('success', 'Surat izin penelitian berhasil diupload');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan data surat');
            }
            
            redirect('staf/penelitian/detail/' . $proposal_id);
        }
        
        // Jika GET request, redirect ke halaman utama
        redirect('staf/penelitian');
    }

    /**
     * Download surat izin penelitian
     */
    public function download_surat($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data proposal
        $this->db->select('pm.surat_izin_penelitian, m.nim, m.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.surat_izin_penelitian IS NOT NULL');
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Surat tidak ditemukan');
            redirect('staf/penelitian');
        }
        
        $file_path = './uploads/surat_izin/' . $proposal->surat_izin_penelitian;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File surat tidak ditemukan');
            redirect('staf/penelitian/detail/' . $proposal_id);
        }
        
        // Force download
        $this->load->helper('download');
        $filename = 'Surat_Izin_Penelitian_' . $proposal->nim . '.pdf';
        force_download($filename, file_get_contents($file_path));
    }

    /**
     * ========================================
     * METHODS BARU UNTUK WORKFLOW PENELITIAN
     * ========================================
     * Tambahkan methods ini di bagian bawah controller existing
     */
    
    /**
     * Cetak form permohonan izin penelitian berdasarkan tabel permohonan_izin_penelitian
     */
    public function cetak_form_permohonan($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data dari tabel permohonan_izin_penelitian (sesuai workflow existing)
        $this->db->select('pip.*, d.nama as nama_pembimbing, d.nip as nip_pembimbing');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
        $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
        $this->db->where('pip.status_pembimbing', 'approved'); // Hanya yang sudah disetujui pembimbing
        
        $permohonan = $this->db->get()->row();
        
        if (!$permohonan) {
            $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan atau belum disetujui dosen pembimbing');
            redirect('staf/penelitian');
        }
        
        // Generate PDF menggunakan library existing
        $data = [
            'permohonan' => $permohonan,
            'tanggal_cetak' => date('d F Y'),
            'dicetak_oleh' => $this->session->userdata('nama')
        ];
        
        $html = $this->load->view('staf/penelitian/pdf_form_permohonan', $data, TRUE);
        
        $this->pdf->filename = 'Form_Permohonan_Penelitian_' . $permohonan->nim . '_' . date('Y-m-d') . '.pdf';
        $this->pdf->load_html($html);
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
        
        // Log aktivitas
        $this->_log_aktivitas('cetak_form_permohonan', null, $proposal_id, 
                             "Cetak form permohonan untuk {$permohonan->nama_mahasiswa}");
    }
    
    /**
     * Download template surat izin penelitian kosong
     */
    public function download_template() {
        $data = [
            'tanggal_template' => date('d F Y'),
            'generated_by' => $this->session->userdata('nama')
        ];
        
        $html = $this->load->view('staf/penelitian/pdf_template_kosong', $data, TRUE);
        
        $this->pdf->filename = 'Template_Surat_Izin_Penelitian_' . date('Y-m-d') . '.pdf';
        $this->pdf->load_html($html);
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
    }
    
    /**
     * Upload surat izin yang sudah ditandatangani (enhanced version)
     */
    public function upload_surat_final($proposal_id) {
        if (!$proposal_id || $this->input->method() !== 'post') {
            redirect('staf/penelitian');
        }
        
        // Validasi proposal
        $this->db->where('id', $proposal_id);
        $proposal = $this->db->get('proposal_mahasiswa')->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data proposal tidak ditemukan');
            redirect('staf/penelitian');
        }
        
        // Config upload dengan penamaan yang lebih baik
        $config['upload_path'] = './uploads/surat_izin/';
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 3072; // 3MB
        $config['file_name'] = 'SURAT_IZIN_FINAL_' . $proposal->nim . '_' . date('Ymd_His');
        
        // Pastikan folder ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        
        $this->load->library('upload', $config);
        
        if (!$this->upload->do_upload('file_surat')) {
            $this->session->set_flashdata('error', 'Error upload: ' . $this->upload->display_errors());
            redirect('staf/penelitian/detail/' . $proposal_id);
        }
        
        $upload_data = $this->upload->data();
        $keterangan = $this->input->post('keterangan_staf');
        
        // Update database - kedua tabel sekaligus untuk konsistensi
        $this->db->trans_start();
        
        // Update tabel permohonan_izin_penelitian
        $this->db->where('proposal_mahasiswa_id', $proposal_id);
        $this->db->update('permohonan_izin_penelitian', [
            'file_surat_izin_staf' => $upload_data['file_name'],
            'tanggal_upload_surat_staf' => date('Y-m-d H:i:s'),
            'uploaded_by_staf' => $this->session->userdata('id'),
            'keterangan_staf' => $keterangan,
            'status' => 'surat_ready'
        ]);
        
        // Update tabel proposal_mahasiswa
        $this->db->where('id', $proposal_id);
        $this->db->update('proposal_mahasiswa', [
            'surat_izin_penelitian' => $upload_data['file_name'],
            'status_izin_penelitian' => '1', // Surat siap
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data surat');
        } else {
            // Send email notification ke mahasiswa (optional)
            $this->_send_notification_surat_ready($proposal_id);
            
            // Log aktivitas
            $this->_log_aktivitas('upload_surat_final', $proposal->mahasiswa_id, $proposal_id, 
                                 "Upload surat izin final: {$upload_data['file_name']}");
            
            $this->session->set_flashdata('success', 'Surat izin berhasil diupload. Mahasiswa akan mendapat notifikasi.');
        }
        
        redirect('staf/penelitian/detail/' . $proposal_id);
    }
    
    /**
     * Send email notification saat surat ready (optional feature)
     */
    private function _send_notification_surat_ready($proposal_id) {
        // Ambil data mahasiswa
        $this->db->select('m.email, m.nama, pm.judul');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        
        $mahasiswa = $this->db->get()->row();
        
        if ($mahasiswa && $mahasiswa->email) {
            // Setup email configuration (sesuaikan dengan setting SMTP Anda)
            $config = [
                'protocol' => 'mail', // atau 'smtp' jika ada setting SMTP
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
            
            $this->load->library('email', $config);
            
            $subject = 'Surat Izin Penelitian Siap - SIM TA STK Santo Yakobus';
            $message = "
            <h3>Surat Izin Penelitian Siap</h3>
            <p>Yth. {$mahasiswa->nama},</p>
            <p>Surat izin penelitian untuk skripsi Anda sudah siap dan dapat didownload melalui sistem.</p>
            <p><strong>Judul:</strong> {$mahasiswa->judul}</p>
            <p>Silakan login ke sistem untuk download surat izin penelitian.</p>
            <p>Terima kasih.</p>
            <hr>
            <small>STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir</small>
            ";
            
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM TA STK Santo Yakobus');
            $this->email->to($mahasiswa->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // Send email (tidak akan error jika gagal)
            @$this->email->send();
        }
    }
    
    /**
     * Generate nomor surat otomatis
     */
    private function _generate_nomor_surat() {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah surat bulan ini dari tabel permohonan_izin_penelitian
        $this->db->where('MONTH(tanggal_upload_surat_staf)', $bulan);
        $this->db->where('YEAR(tanggal_upload_surat_staf)', $tahun);
        $this->db->where('file_surat_izin_staf IS NOT NULL');
        $count = $this->db->count_all_results('permohonan_izin_penelitian');
        
        $nomor_urut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        return "{$nomor_urut}/STK-SY/PENELITIAN/{$bulan}/{$tahun}";
    }
    
    /**
     * Enhanced version dari method cetak_surat existing
     */
    public function cetak_surat_enhanced($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap dari kedua tabel
        $this->db->select('
            pip.*, 
            pm.judul, pm.lokasi_penelitian,
            m.nama as nama_mahasiswa, m.nim, m.email,
            d.nama as nama_pembimbing, d.nip as nip_pembimbing,
            p.nama as nama_prodi
        ');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
        $this->db->where('pip.status_pembimbing', 'approved');
        
        $data_lengkap = $this->db->get()->row();
        
        if (!$data_lengkap) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau belum valid untuk dicetak');
            redirect('staf/penelitian');
        }
        
        // Generate PDF dengan template enhanced
        $data = [
            'data_lengkap' => $data_lengkap,
            'nomor_surat' => $this->_generate_nomor_surat(),
            'tanggal_surat' => date('d F Y'),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ];
        
        $html = $this->load->view('staf/penelitian/pdf_surat_izin_enhanced', $data, TRUE);
        
        $this->pdf->filename = 'Surat_Izin_Penelitian_' . $data_lengkap->nim . '_' . date('Y-m-d') . '.pdf';
        $this->pdf->load_html($html);
        $this->pdf->stream($this->pdf->filename, array("Attachment" => false));
        
        // Log aktivitas
        $this->_log_aktivitas('cetak_surat_enhanced', null, $proposal_id, 
                             "Cetak surat izin enhanced untuk {$data_lengkap->nama_mahasiswa}");
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