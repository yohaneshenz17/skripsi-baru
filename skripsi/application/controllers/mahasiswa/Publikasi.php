<?php
/**
 * =====================================================
 * CONTROLLER PUBLIKASI MAHASISWA - ROBUST VERSION
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * PERBAIKAN ROBUSTNESS:
 * 1. _get_proposal_eligible() - tidak terpengaruh inkonsistensi status
 * 2. _check_syarat_publikasi() - simple, hanya cek jurnal
 * 3. Struktur dan method lain tetap stabil - TIDAK DIUBAH
 * 
 * File: application/controllers/mahasiswa/Publikasi.php
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Publikasi extends CI_Controller {

    private $mahasiswa_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'upload', 'form_validation']);
        $this->load->helper(['url', 'file', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk mahasiswa
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '3') {
            show_error('Akses ditolak. Halaman khusus mahasiswa.', 403);
        }
        
        $this->mahasiswa_id = $this->session->userdata('id');
    }

    /**
     * Dashboard publikasi mahasiswa - STRUKTUR TIDAK DIUBAH
     */
    public function index() {
        // ✅ ROBUST: Get proposal dengan logic yang tidak terpengaruh inkonsistensi data
        $proposal = $this->_get_proposal_eligible();
        
        // Prepare data untuk view content - STRUKTUR TIDAK DIUBAH
        $view_data = [
            'proposal' => $proposal,
            'publikasi' => null,
            'syarat_status' => null,
            'jurnal_count' => 0,
            'eligible' => false
        ];
        
        if ($proposal) {
            // ✅ ROBUST: Cek syarat publikasi dengan logic simple
            $view_data['syarat_status'] = $this->_check_syarat_publikasi($proposal->id);
            $view_data['jurnal_count'] = $this->_count_jurnal_tervalidasi($proposal->id);
            $view_data['eligible'] = ($view_data['syarat_status'] === 'ELIGIBLE');
            
            // Get existing publikasi jika ada - LOGIC TIDAK DIUBAH
            $view_data['publikasi'] = $this->publikasi->get_by_proposal($proposal->id);
        }
        
        // Load template dengan pattern yang konsisten - TIDAK DIUBAH
        $this->_load_template('mahasiswa/publikasi/index', $view_data, 'Publikasi Tugas Akhir');
    }

    /**
     * Form pengajuan - STRUKTUR TIDAK DIUBAH
     */
    public function ajukan($proposal_id = null) {
        // LOGIC BISNIS TIDAK DIUBAH - hanya method internal yang di-robust
        if (!$proposal_id) {
            $proposal = $this->_get_proposal_eligible();
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Tidak ada proposal yang eligible.');
                redirect('mahasiswa/publikasi');
                return;
            }
            $proposal_id = $proposal->id;
        }
        
        $proposal = $this->_get_proposal_by_id($proposal_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // ✅ ROBUST: Cek syarat dengan logic simple (backward compatible)
        $syarat_check = $this->_check_syarat_publikasi($proposal_id);
        if ($syarat_check !== 'ELIGIBLE') {
            // Buat message yang user-friendly
            $jurnal_count = $this->_count_jurnal_tervalidasi($proposal_id);
            $message = "Syarat belum terpenuhi: Jurnal bimbingan masih {$jurnal_count}/16";
            $this->session->set_flashdata('error', $message);
            redirect('mahasiswa/publikasi');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_form_publikasi($proposal);
        } else {
            $this->_show_form_pengajuan($proposal);
        }
    }

    /**
     * Edit pengajuan - TIDAK DIUBAH
     */
    public function edit($publikasi_id) {
        // LOGIC BISNIS TIDAK DIUBAH
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Hanya bisa edit jika status draft atau rejected - LOGIC TIDAK DIUBAH
        if (!in_array($publikasi->status, ['draft', 'rejected'])) {
            $this->session->set_flashdata('error', 'Tidak dapat mengedit. Status: ' . $publikasi->status);
            redirect('mahasiswa/publikasi');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_update($publikasi);
        } else {
            $this->_show_form_edit($publikasi);
        }
    }

    /**
     * Detail/Tracking publikasi - TIDAK DIUBAH
     */
    public function tracking($publikasi_id) {
        // LOGIC BISNIS TIDAK DIUBAH
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Prepare data untuk view
        $view_data = [
            'publikasi' => $publikasi,
            'timeline' => $this->_get_publikasi_timeline($publikasi_id)
        ];
        
        // Load template dengan pattern konsisten - TIDAK DIUBAH
        $this->_load_template('mahasiswa/publikasi/tracking', $view_data, 'Tracking Publikasi - ' . $publikasi->nama_mahasiswa);
    }

    /**
     * Submit pengajuan ke dosen pembimbing - LOGIC TIDAK DIUBAH
     */
    public function submit($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        if ($publikasi->status !== 'draft') {
            $this->session->set_flashdata('error', 'Pengajuan sudah disubmit sebelumnya.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Update status ke submitted - LOGIC TIDAK DIUBAH
        $result = $this->publikasi->submit_pengajuan($publikasi_id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan berhasil disubmit ke dosen pembimbing.');
            
            // Send notification ke dosen pembimbing
            $this->_send_notification_to_dosen($publikasi);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('mahasiswa/publikasi');
    }

    /**
     * Download surat keterangan publikasi - LOGIC TIDAK DIUBAH
     */
    public function download_surat($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->status !== 'completed') {
            $this->session->set_flashdata('error', 'Surat belum tersedia.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Generate PDF surat keterangan - LOGIC TIDAK DIUBAH
        $this->load->library('pdf');
        
        $data = [
            'publikasi' => $publikasi,
            'mahasiswa' => $this->_get_mahasiswa_data(),
            'tanggal_surat' => date('d F Y')
        ];
        
        $html = $this->load->view('mahasiswa/publikasi/surat_keterangan', $data, TRUE);
        
        $this->pdf->filename = 'Surat_Keterangan_Publikasi_' . $publikasi->nim . '.pdf';
        $this->pdf->load_html($html);
        $this->pdf->render();
        $this->pdf->stream($this->pdf->filename, ["Attachment" => false]);
    }

    // =================================================================
    // ✅ ROBUST PRIVATE METHODS - HANYA 2 METHOD INI YANG DIUBAH
    // =================================================================

    /**
     * ✅ ROBUST: Get proposal eligible - TIDAK TERPENGARUH INKONSISTENSI DATA
     */
    private function _get_proposal_eligible() {
        try {
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $this->mahasiswa_id);
            
            // ✅ ROBUST CHANGE: Cek status_kaprodi (yang reliable) bukan status (yang inkonsisten)
            // Ini mengatasi masalah inkonsistensi data secara permanen
            $this->db->where('pm.status_kaprodi', '1'); // Proposal sudah disetujui kaprodi
            
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16'); // Minimal 16 jurnal tervalidasi
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_proposal_eligible: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ ROBUST: Check syarat publikasi - SIMPLE DAN KONSISTEN
     */
    private function _check_syarat_publikasi($proposal_id) {
        try {
            // ✅ SIMPLE: Hanya cek jurnal bimbingan, tidak ada syarat lain
            $jurnal_count = $this->_count_jurnal_tervalidasi($proposal_id);
            
            // Simple rule: 16+ jurnal = ELIGIBLE
            return ($jurnal_count >= 16) ? 'ELIGIBLE' : 'NOT_ELIGIBLE';
            
        } catch (Exception $e) {
            log_message('error', 'Error checking syarat publikasi: ' . $e->getMessage());
            return 'NOT_ELIGIBLE';
        }
    }

    // =================================================================
    // PRIVATE METHODS LAIN - SEMUA TIDAK DIUBAH
    // =================================================================

    /**
     * Template loading method yang konsisten - TIDAK DIUBAH
     */
    private function _load_template($view_path, $data = [], $title = 'Publikasi Tugas Akhir', $script = '') {
        // Prepare template data seperti controller phase lain yang working
        $template_data = [
            'title' => $title,
            'content' => $this->load->view($view_path, $data, TRUE),
            'script' => $script
        ];
        
        // Load template mahasiswa dengan pattern konsisten
        $this->load->view('template/mahasiswa', $template_data);
    }

    /**
     * Get proposal by ID - TIDAK DIUBAH
     */
    private function _get_proposal_by_id($proposal_id) {
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing, d.email as email_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.mahasiswa_id', $this->mahasiswa_id);
        
        return $this->db->get()->row();
    }

    /**
     * Count jurnal tervalidasi - TIDAK DIUBAH
     */
    private function _count_jurnal_tervalidasi($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)
                       ->where('status_validasi', '1')
                       ->count_all_results('jurnal_bimbingan');
    }

    /**
     * Show form pengajuan dengan template wrapper - TIDAK DIUBAH
     */
    private function _show_form_pengajuan($proposal) {
        $view_data = [
            'title' => 'Ajukan Publikasi Tugas Akhir',
            'proposal' => $proposal,
            'action' => 'ajukan'
        ];
        
        // Gunakan template wrapper
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Ajukan Publikasi Tugas Akhir', $this->_get_form_script());
    }

    /**
     * Show form edit dengan template wrapper - TIDAK DIUBAH
     */
    private function _show_form_edit($publikasi) {
        $view_data = [
            'title' => 'Edit Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'action' => 'edit'
        ];
        
        // Gunakan template wrapper
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Edit Publikasi Tugas Akhir', $this->_get_form_script());
    }

    // =================================================================
    // SEMUA METHOD PROCESSING LAINNYA - TIDAK DIUBAH
    // =================================================================

    /**
     * Process form publikasi - DIPERBAIKI MAPPING KOLOM
     * Fixed: Sesuaikan nama kolom dengan database schema
     */
    private function _process_form_publikasi($proposal) {
        // Set validation rules
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if ($this->form_validation->run() === FALSE) {
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // Handle file uploads
        $upload_result = $this->_handle_file_uploads(true);
        if (!$upload_result['success']) {
            $this->session->set_flashdata('error', $upload_result['message']);
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // Prepare data untuk insert - FIXED COLUMN MAPPING
        $data = [
            'proposal_mahasiswa_id' => $proposal->id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'dosen_pembimbing_id' => $proposal->dosen_id,
            'nim' => $proposal->nim,
            'nama_mahasiswa' => $proposal->nama_mahasiswa,
            'program_studi' => $proposal->nama_prodi,
            
            // ✅ FIXED: Ganti 'judul_skripsi' menjadi 'judul_skripsi_final'
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            
            // ✅ FIXED: Pastikan nama dosen diambil dengan benar
            'nama_dosen_pembimbing' => isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Belum ditetapkan',
            
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            'file_surat_revisi' => $upload_result['files']['file_surat_revisi'],
            'file_skripsi_final' => $upload_result['files']['file_skripsi_final'],
            'file_surat_perpustakaan' => $upload_result['files']['file_surat_perpustakaan'],
            'link_repository' => $this->input->post('link_repository'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'status' => 'draft',
            'tanggal_pengajuan' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert ke database menggunakan model
        $result = $this->publikasi->create($data);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan publikasi berhasil disimpan sebagai draft.');
            redirect('mahasiswa/publikasi/detail/' . $result['id']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            $this->_show_form_pengajuan($proposal);
        }
    }

    /**
     * Process update publikasi - DIPERBAIKI MAPPING KOLOM
     * Fixed: Sesuaikan nama kolom dengan database schema
     */
    private function _process_update($publikasi) {
        // Set validation rules
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if ($this->form_validation->run() === FALSE) {
            $this->_show_form_edit($publikasi);
            return;
        }
        
        // Handle file uploads (optional untuk update)
        $upload_result = $this->_handle_file_uploads(false);
        if (!$upload_result['success']) {
            $this->session->set_flashdata('error', $upload_result['message']);
            $this->_show_form_edit($publikasi);
            return;
        }
        
        // Prepare data untuk update - FIXED COLUMN MAPPING
        $data = [
            // ✅ FIXED: Ganti 'judul_skripsi' menjadi 'judul_skripsi_final'
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            'link_repository' => $this->input->post('link_repository'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Update file jika ada yang diupload
        if (!empty($upload_result['files'])) {
            $data = array_merge($data, $upload_result['files']);
        }
        
        // Update database menggunakan model
        $result = $this->publikasi->update($publikasi->id, $data, $this->mahasiswa_id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Data publikasi berhasil diperbarui.');
            redirect('mahasiswa/publikasi/detail/' . $publikasi->id);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            $this->_show_form_edit($publikasi);
        }
    }

    /**
     * Handle file uploads - DIPERBAIKI UNTUK MULTIPLE SUBFOLDERS
     * Fixed: Setiap file type upload ke folder yang berbeda sesuai requirement
     */
    private function _handle_file_uploads($required = true) {
        // Definisi mapping file ke subfolder masing-masing
        $file_config = [
            'file_surat_perpustakaan' => [
                'path' => './uploads/publikasi/surat_perpustakaan/',
                'max_size' => 1024, // 1MB
                'prefix' => 'SURAT_PERPUS'
            ],
            'file_surat_revisi' => [
                'path' => './uploads/publikasi/surat_revisi/',
                'max_size' => 1024, // 1MB  
                'prefix' => 'SURAT_REVISI'
            ],
            'file_skripsi_final' => [
                'path' => './uploads/publikasi/skripsi_final/',
                'max_size' => 5120, // 5MB
                'prefix' => 'SKRIPSI_FINAL'
            ]
        ];
        
        $uploaded_files = [];
        $errors = [];
        
        // Loop setiap file dengan config masing-masing
        foreach ($file_config as $field => $config) {
            if (!empty($_FILES[$field]['name'])) {
                
                // Buat direktori jika belum ada
                if (!is_dir($config['path'])) {
                    mkdir($config['path'], 0755, true);
                }
                
                // Set upload config untuk file ini
                $upload_config = [
                    'upload_path' => $config['path'],
                    'allowed_types' => 'pdf',
                    'max_size' => $config['max_size'],
                    'encrypt_name' => false,
                    'file_name' => $this->_generate_filename($config['prefix'])
                ];
                
                $this->upload->initialize($upload_config);
                
                if ($this->upload->do_upload($field)) {
                    $uploaded_files[$field] = $this->upload->data('file_name');
                    
                    // Log successful upload
                    log_message('info', "File uploaded: {$field} -> {$uploaded_files[$field]}");
                    
                } else {
                    $upload_error = $this->upload->display_errors('', '');
                    $errors[] = $field . ': ' . $upload_error;
                    
                    // Log upload error
                    log_message('error', "Upload failed for {$field}: {$upload_error}");
                }
                
            } elseif ($required) {
                $errors[] = $field . ': File wajib diupload';
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('<br>', $errors)
            ];
        }
        
        return [
            'success' => true,
            'files' => $uploaded_files
        ];
    }

    /**
     * Generate filename untuk upload - TIDAK DIUBAH
     */
    private function _generate_filename($prefix = 'PUBLIKASI') {
        return $prefix . '_' . date('YmdHis') . '_' . $this->mahasiswa_id . '_' . uniqid();
    }

    /**
     * ✅ REPLACE: Method utama untuk kirim notifikasi publikasi
     * Ganti method _send_notification_to_dosen() yang lama
     */
    private function _send_notification_to_dosen($publikasi) {
        try {
            // Get data lengkap untuk notifikasi
            $data = $this->db->select('
                    p.*, 
                    pm.judul as judul_proposal,
                    pm.dosen_id as pembimbing_id,
                    m.nama as nama_mahasiswa,
                    m.nim,
                    m.email as email_mahasiswa,
                    d.email as email_pembimbing,
                    d.nama as nama_pembimbing
                ')
                ->from('publikasi_mahasiswa p')
                ->join('proposal_mahasiswa pm', 'p.proposal_id = pm.id')
                ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                ->join('dosen d', 'pm.dosen_id = d.id')
                ->where('p.id', $publikasi->id)
                ->get()->row();
                
            if (!$data || !$data->email_pembimbing) {
                log_message('error', 'Data publikasi atau email dosen tidak ditemukan untuk notifikasi');
                return false;
            }
            
            // Tentukan apakah ini pengajuan baru atau pengajuan ulang
            $is_resubmission = $this->_is_resubmission_publikasi($publikasi->proposal_id);
            
            // Kirim email ke dosen pembimbing
            $this->_kirim_email_publikasi_ke_dosen($data, $is_resubmission);
            
            // Kirim email konfirmasi ke mahasiswa
            $this->_kirim_email_konfirmasi_publikasi_mahasiswa($data, $is_resubmission);
            
            log_message('info', "Publikasi notification sent - ID: {$publikasi->id}, Proposal: {$publikasi->proposal_id}");
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending publikasi notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ BARU: Email ke dosen pembimbing ketika mahasiswa ajukan publikasi
     */
    private function _kirim_email_publikasi_ke_dosen($data, $is_resubmission = false) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($data->email_pembimbing);
            
            $subject_prefix = $is_resubmission ? '🔄 Pengajuan Ulang' : '📄 Pengajuan Baru';
            $this->email->subject($subject_prefix . ' Publikasi Tugas Akhir - ' . $data->nama_mahasiswa);
            
            $submission_text = $is_resubmission ? 
                'telah mengajukan <strong>ULANG</strong> publikasi tugas akhir' : 
                'telah mengajukan publikasi tugas akhir';
                
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📄 Publikasi Tugas Akhir</h2>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>" . ($is_resubmission ? 'Pengajuan Ulang' : 'Pengajuan Baru') . "</p>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$data->nama_pembimbing}</strong>,</p>
                    
                    <p>Mahasiswa bimbingan Anda {$submission_text} dengan detail sebagai berikut:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 5px 0;'><strong>Nama:</strong></td><td>{$data->nama_mahasiswa}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>NIM:</strong></td><td>{$data->nim}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Judul:</strong></td><td>{$data->judul_proposal}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Tanggal Ajukan:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                        </table>
                    </div>";
                    
            if ($is_resubmission) {
                $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0;'>
                        <h4 style='color: #856404; margin-top: 0;'>⚠️ Pengajuan Ulang</h4>
                        <p style='margin: 0; color: #856404;'>Ini adalah pengajuan ulang setelah perbaikan berdasarkan feedback sebelumnya.</p>
                    </div>";
            }
            
            $message .= "
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #495057; margin-top: 0;'>📋 Tindakan Diperlukan:</h4>
                        <p style='margin: 5px 0;'>• Review kelengkapan dokumen publikasi</p>
                        <p style='margin: 5px 0;'>• Verifikasi kesesuaian dengan hasil seminar skripsi</p>
                        <p style='margin: 5px 0;'>• Berikan rekomendasi (Setujui/Revisi)</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/publikasi') . "' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📄 Buka Menu Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem untuk koordinasi proses publikasi tugas akhir.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email publikasi sent to dosen: {$data->email_pembimbing}");
                return true;
            } else {
                log_message('error', 'Failed to send email to dosen: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email to dosen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Email konfirmasi ke mahasiswa setelah submit
     */
    private function _kirim_email_konfirmasi_publikasi_mahasiswa($data, $is_resubmission = false) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($data->email_mahasiswa);
            
            $subject_prefix = $is_resubmission ? 'Pengajuan Ulang' : 'Pengajuan';
            $this->email->subject('✅ ' . $subject_prefix . ' Publikasi Berhasil Dikirim');
            
            $submission_text = $is_resubmission ? 'pengajuan ulang' : 'pengajuan';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Publikasi Berhasil Diajukan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$data->nama_mahasiswa}</strong>,</p>
                    
                    <p>Terima kasih! {$submission_text} publikasi tugas akhir Anda telah berhasil dikirim kepada dosen pembimbing untuk review.</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #28a745; margin-top: 0;'>Detail Pengajuan:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td><strong>Judul:</strong></td><td>{$data->judul_proposal}</td></tr>
                            <tr><td><strong>Pembimbing:</strong></td><td>{$data->nama_pembimbing}</td></tr>
                            <tr><td><strong>Waktu Submit:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span style='color: #ffc107;'>⏳ Menunggu Review Dosen</span></td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-left: 4px solid #bee5eb; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin-top: 0;'>📋 Langkah Selanjutnya:</h4>
                        <p style='margin: 5px 0; color: #0c5460;'>• Dosen pembimbing akan melakukan review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Anda akan mendapat notifikasi hasil review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Jika disetujui, akan diteruskan ke staf untuk validasi</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/publikasi') . "' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📊 Lihat Status Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px;'>
                        Anda dapat memantau progress publikasi melalui dashboard mahasiswa.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email konfirmasi publikasi sent to mahasiswa: {$data->email_mahasiswa}");
                return true;
            } else {
                log_message('error', 'Failed to send confirmation email to mahasiswa: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending confirmation email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Helper method untuk cek apakah ini pengajuan ulang
     */
    private function _is_resubmission_publikasi($proposal_id) {
        try {
            $count = $this->db->where('proposal_id', $proposal_id)
                             ->where('status !=', 'draft')
                             ->count_all_results('publikasi_mahasiswa');
            return $count > 1;
        } catch (Exception $e) {
            log_message('error', 'Error checking resubmission: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Helper method untuk email config (sama seperti Seminar_skripsi.php)
     */
    private function _get_email_config() {
        return [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30
        ];
    }

    /**
     * Get publikasi timeline - TIDAK DIUBAH
     */
    private function _get_publikasi_timeline($publikasi_id) {
        // Get timeline/log untuk publikasi
        return [];
    }

    /**
     * Get mahasiswa data - TIDAK DIUBAH
     */
    private function _get_mahasiswa_data() {
        // Get data mahasiswa untuk surat
        return $this->db->get_where('mahasiswa', ['id' => $this->mahasiswa_id])->row();
    }

    /**
     * JavaScript untuk form publikasi - TIDAK DIUBAH
     */
    private function _get_form_script() {
        return '
        <script>
        $(document).ready(function() {
            // Validasi file size
            $("input[type=\'file\']").change(function() {
                const fileInput = this;
                const file = fileInput.files[0];
                const maxSize = fileInput.name === "file_skripsi_final" ? 5 * 1024 * 1024 : 1 * 1024 * 1024;
                
                if (file && file.size > maxSize) {
                    alert("Ukuran file terlalu besar. Maksimal " + (maxSize / 1024 / 1024) + " MB");
                    fileInput.value = "";
                }
            });
            
            // Konfirmasi submit
            $("#formPublikasi").submit(function(e) {
                if (!confirm("Yakin ingin mengajukan publikasi ini? Pastikan semua data sudah benar.")) {
                    e.preventDefault();
                }
            });
        });
        </script>';
    }
}