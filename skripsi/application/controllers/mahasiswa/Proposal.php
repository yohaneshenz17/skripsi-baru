<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Proposal extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');

        if ($this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title'] = 'Usulan Proposal Skripsi';
        $mahasiswa_id = $this->session->userdata('id');

        // Query untuk mengambil data proposal yang sedang aktif atau menunggu
        $this->db->select('p.*, d1.nama as nama_pembimbing');
        $this->db->from('proposal_mahasiswa p');
        $this->db->join('dosen d1', 'p.dosen_id = d1.id', 'left');
        $this->db->join('dosen dp1', 'p.dosen_penguji_id = dp1.id', 'left');
        $this->db->join('dosen dp2', 'p.dosen_penguji2_id = dp2.id', 'left');
        $this->db->where('p.mahasiswa_id', $mahasiswa_id);
        $this->db->where('p.status !=', '2'); // Status 2 = Ditolak. Proposal yg ditolak tidak ditampilkan.
        $data['proposal'] = $this->db->get()->row();

        $this->load->view('mahasiswa/proposal', $data);
    }

    public function ajukan()
    {
        if ($this->input->post()) {
            $mahasiswa_id = $this->session->userdata('id');

            // ========================================
            // PERBAIKAN: Fix validation logic
            // SEBELUM: 'status !=' => '2' 
            // SESUDAH: 'status_kaprodi !=' => '2'
            // ========================================
            $existing = $this->db->get_where('proposal_mahasiswa', [
                'mahasiswa_id' => $mahasiswa_id,
                'status_kaprodi !=' => '2' // PERBAIKAN: Gunakan field yang benar
            ])->row();

            if ($existing) {
                $this->session->set_flashdata('error', 'Anda sudah memiliki proposal yang sedang diproses dan belum ditolak. Anda tidak bisa mengajukan proposal baru.');
                redirect('mahasiswa/proposal');
                return;
            }

            // ========================================
            // PERBAIKAN UTAMA: VALIDASI FILE YANG LEBIH KETAT
            // ========================================
            $upload_result = $this->_handle_secure_upload('draft_proposal');
            
            if (!$upload_result['status']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/proposal');
                return;
            }

            $data_to_save = [
                'mahasiswa_id' => $mahasiswa_id,
                'judul' => $this->input->post('judul'),
                'ringkasan' => substr($this->input->post('uraian_masalah'), 0, 250),
                'jenis_penelitian' => $this->input->post('jenis_penelitian'),
                'lokasi_penelitian' => $this->input->post('lokasi_penelitian'),
                'uraian_masalah' => $this->input->post('uraian_masalah'),
                'file_draft_proposal' => $upload_result['filename'], // Gunakan filename dari upload yang aman
                'status' => '0',
                'dosen_id' => NULL,
                'dosen_penguji_id' => NULL,
                'dosen_penguji2_id' => NULL
            ];
            
            if($this->db->insert('proposal_mahasiswa', $data_to_save)) {
                // Ambil ID proposal yang baru diinsert
                $proposal_id = $this->db->insert_id();
                
                // Kirim notifikasi email
                $email_mahasiswa = $this->_send_notification_to_mahasiswa($mahasiswa_id, $proposal_id, $this->input->post());
                $email_kaprodi = $this->_send_notification_to_kaprodi($mahasiswa_id, $proposal_id, $this->input->post());
                
                // Log email results
                log_message('info', "Email notification - Mahasiswa: " . ($email_mahasiswa ? 'SUCCESS' : 'FAILED'));
                log_message('info', "Email notification - Kaprodi: " . ($email_kaprodi ? 'SUCCESS' : 'FAILED'));
                
                if ($email_mahasiswa && $email_kaprodi) {
                    $this->session->set_flashdata('success', 'Proposal berhasil diajukan! Email konfirmasi telah dikirim ke Anda dan Kaprodi. Silakan pantau email dan status proposal secara berkala.');
                } else if ($email_mahasiswa) {
                    $this->session->set_flashdata('success', 'Proposal berhasil diajukan! Email konfirmasi telah dikirim ke Anda. Kaprodi akan diberitahu melalui sistem.');
                } else {
                    $this->session->set_flashdata('success', 'Proposal berhasil diajukan! Silakan pantau status proposal secara berkala melalui dashboard.');
                }
            } else {
                // Hapus file jika gagal simpan ke database
                @unlink('./cdn/proposals/' . $upload_result['filename']);
                $this->session->set_flashdata('error', 'Gagal mengajukan proposal!');
            }
            redirect('mahasiswa/proposal');
        }
    }

    // ========================================
    // TAMBAHAN BARU: Method untuk pengajuan ulang
    // ========================================
    public function ajukan_ulang()
    {
        if ($this->input->post()) {
            $mahasiswa_id = $this->session->userdata('id');

            // Cek apakah ada proposal yang ditolak kaprodi
            $existing = $this->db->get_where('proposal_mahasiswa', [
                'mahasiswa_id' => $mahasiswa_id,
                'status_kaprodi' => '2' // Hanya proposal yang ditolak kaprodi
            ])->row();

            if (!$existing) {
                $this->session->set_flashdata('error', 'Tidak ada proposal yang perlu diajukan ulang!');
                redirect('mahasiswa/proposal');
                return;
            }

            // ========================================
            // PERBAIKAN UTAMA: VALIDASI FILE YANG LEBIH KETAT
            // ========================================
            $upload_result = $this->_handle_secure_upload('draft_proposal');
            
            if (!$upload_result['status']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/proposal');
                return;
            }

            // Hapus file proposal lama jika ada
            if ($existing->file_draft_proposal && file_exists('./cdn/proposals/' . $existing->file_draft_proposal)) {
                @unlink('./cdn/proposals/' . $existing->file_draft_proposal);
            }

            // Update proposal existing dengan data baru (reset status untuk review ulang)
            $data_update = [
                'judul' => $this->input->post('judul'),
                'ringkasan' => substr($this->input->post('uraian_masalah'), 0, 250),
                'jenis_penelitian' => $this->input->post('jenis_penelitian'),
                'lokasi_penelitian' => $this->input->post('lokasi_penelitian'),
                'uraian_masalah' => $this->input->post('uraian_masalah'),
                'file_draft_proposal' => $upload_result['filename'], // Gunakan filename dari upload yang aman
                'status_kaprodi' => '0', // Reset ke menunggu review
                'komentar_kaprodi' => NULL, // Reset komentar
                'tanggal_review_kaprodi' => NULL, // Reset tanggal review
                'updated_at' => date('Y-m-d H:i:s') // Timestamp update
            ];
            
            $this->db->where('id', $existing->id);
            if ($this->db->update('proposal_mahasiswa', $data_update)) {
                // Kirim notifikasi pengajuan ulang
                $email_mahasiswa = $this->_send_notification_to_mahasiswa($mahasiswa_id, $existing->id, $this->input->post(), true);
                $email_kaprodi = $this->_send_notification_to_kaprodi($mahasiswa_id, $existing->id, $this->input->post(), true);
                
                // Log email results
                log_message('info', "Resubmission email - Mahasiswa: " . ($email_mahasiswa ? 'SUCCESS' : 'FAILED'));
                log_message('info', "Resubmission email - Kaprodi: " . ($email_kaprodi ? 'SUCCESS' : 'FAILED'));
                
                if ($email_mahasiswa && $email_kaprodi) {
                    $this->session->set_flashdata('success', 'Proposal berhasil diajukan ulang! Email konfirmasi telah dikirim. Kaprodi akan melakukan review kembali terhadap proposal yang sudah diperbaiki.');
                } else {
                    $this->session->set_flashdata('success', 'Proposal berhasil diajukan ulang! Kaprodi akan melakukan review kembali terhadap proposal yang sudah diperbaiki.');
                }
            } else {
                // Hapus file jika gagal update
                @unlink('./cdn/proposals/' . $upload_result['filename']);
                $this->session->set_flashdata('error', 'Gagal mengajukan ulang proposal!');
            }
            
            redirect('mahasiswa/proposal');
        }
    }

    // ========================================
    // METODE BARU: UPLOAD FILE YANG AMAN DAN TERBATAS
    // ========================================
    private function _handle_secure_upload($field_name)
    {
        // Buat direktori jika belum ada
        if (!is_dir('./cdn/proposals/')) {
            mkdir('./cdn/proposals/', 0755, true);
        }

        // ========================================
        // PERBAIKAN: KONFIGURASI UPLOAD YANG LEBIH KETAT
        // Dari 5MB menjadi 500KB maksimal
        // ========================================
        $config = [
            'upload_path'   => './cdn/proposals/',
            'allowed_types' => 'doc|docx|pdf',
            'max_size'      => 512, // 500KB (512KB untuk buffer kecil)
            'encrypt_name'  => TRUE,
            'remove_spaces' => TRUE,
            'overwrite'     => FALSE
        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload($field_name)) {
            return [
                'status' => false,
                'message' => 'Upload gagal: ' . $this->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->upload->data();
        
        // ========================================
        // PERBAIKAN: VALIDASI KEAMANAN FILE YANG LEBIH KETAT
        // ========================================
        $security_check = $this->_validate_file_security($upload_data['full_path'], $upload_data['file_ext']);
        
        if (!$security_check['status']) {
            // Hapus file jika tidak aman
            @unlink($upload_data['full_path']);
            return [
                'status' => false,
                'message' => $security_check['message']
            ];
        }

        return [
            'status' => true,
            'filename' => $upload_data['file_name'],
            'message' => 'File berhasil diupload dan telah divalidasi keamanannya'
        ];
    }

    // ========================================
    // METODE BARU: VALIDASI KEAMANAN FILE YANG LENGKAP
    // ========================================
    private function _validate_file_security($file_path, $file_ext)
    {
        // 1. Validasi ukuran file (double check)
        $file_size = filesize($file_path);
        if ($file_size > 524288) { // 512KB dalam bytes
            return [
                'status' => false,
                'message' => 'Ukuran file melebihi 500KB yang diizinkan! Ukuran file: ' . round($file_size/1024, 2) . 'KB'
            ];
        }

        // 2. Validasi MIME type yang sebenarnya menggunakan finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $real_mime = finfo_file($finfo, $file_path);
            finfo_close($finfo);

            $allowed_mimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-office'
            ];

            if (!in_array($real_mime, $allowed_mimes)) {
                return [
                    'status' => false,
                    'message' => 'Tipe file tidak valid! File terdeteksi sebagai: ' . $real_mime . '. Hanya file Word (.doc, .docx) dan PDF yang diizinkan.'
                ];
            }
        }

        // 3. Validasi ekstensi file
        $file_extension = strtolower(ltrim($file_ext, '.'));
        $allowed_extensions = ['doc', 'docx', 'pdf'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            return [
                'status' => false,
                'message' => 'Ekstensi file tidak valid! Hanya file .doc, .docx, dan .pdf yang diizinkan.'
            ];
        }

        // 4. Validasi header file untuk mencegah file yang diubah ekstensinya
        if (!$this->_validate_file_header($file_path, $file_extension)) {
            return [
                'status' => false,
                'message' => 'File tidak valid atau rusak! Pastikan file adalah dokumen Word atau PDF yang valid, bukan file lain yang diubah ekstensinya.'
            ];
        }

        // 5. Scan basic virus pattern (fallback jika ClamAV tidak tersedia)
        $virus_scan = $this->_scan_basic_virus($file_path);
        if (!$virus_scan['status']) {
            return $virus_scan;
        }

        // 6. Scan menggunakan ClamAV jika tersedia
        $clamav_scan = $this->_scan_clamav($file_path);
        if (!$clamav_scan['status']) {
            return $clamav_scan;
        }

        return [
            'status' => true,
            'message' => 'File aman dan valid'
        ];
    }

    // ========================================
    // METODE BARU: VALIDASI HEADER FILE
    // ========================================
    private function _validate_file_header($file_path, $extension)
    {
        $handle = fopen($file_path, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 8);
        fclose($handle);

        switch ($extension) {
            case 'pdf':
                // PDF header: %PDF
                return substr($header, 0, 4) === '%PDF';
                
            case 'doc':
                // DOC header: D0CF11E0A1B11AE1 (OLE compound document)
                $hex_header = bin2hex($header);
                return substr($hex_header, 0, 16) === 'd0cf11e0a1b11ae1';
                
            case 'docx':
                // DOCX header: 504B0304 (ZIP signature, karena DOCX adalah ZIP)
                $hex_header = bin2hex(substr($header, 0, 4));
                return $hex_header === '504b0304';
                
            default:
                return false;
        }
    }

    // ========================================
    // METODE BARU: SCAN VIRUS MENGGUNAKAN CLAMAV
    // ========================================
    private function _scan_clamav($file_path)
    {
        // Cek apakah exec function enabled dan ClamAV tersedia
        if (!function_exists('exec') || $this->_is_function_disabled('exec')) {
            // Jika exec disabled, skip ClamAV scan (gunakan basic scan saja)
            return ['status' => true, 'message' => 'ClamAV scan skipped (exec disabled)'];
        }

        // Cek apakah clamscan tersedia
        $clamscan_path = exec('which clamscan 2>/dev/null');
        
        if (empty($clamscan_path)) {
            // Jika ClamAV tidak tersedia, skip scan
            return ['status' => true, 'message' => 'ClamAV not available'];
        }

        try {
            $output = [];
            $return_code = 0;
            
            // Jalankan ClamAV scan
            $scan_command = escapeshellcmd($clamscan_path) . ' --no-summary ' . escapeshellarg($file_path);
            exec($scan_command, $output, $return_code);
            
            // Return code 0 = clean, 1 = infected, 2 = error
            if ($return_code === 1) {
                return [
                    'status' => false,
                    'message' => 'File terdeteksi mengandung virus atau malware! Upload ditolak untuk keamanan sistem. Scan ulang file Anda dengan antivirus terbaru.'
                ];
            } elseif ($return_code === 2) {
                // Error dalam scanning, tapi izinkan upload (fallback ke basic scan)
                log_message('error', 'ClamAV scan error for file: ' . $file_path);
                return ['status' => true, 'message' => 'ClamAV scan error, using basic scan'];
            }
            
            return ['status' => true, 'message' => 'ClamAV scan clean'];
            
        } catch (Exception $e) {
            log_message('error', 'ClamAV scan exception: ' . $e->getMessage());
            // Error dalam scanning, tapi izinkan upload (fallback ke basic scan)
            return ['status' => true, 'message' => 'ClamAV scan exception'];
        }
    }

    // ========================================
    // METODE BARU: SCAN VIRUS BASIC (FALLBACK)
    // ========================================
    private function _scan_basic_virus($file_path)
    {
        // Pattern sederhana untuk mendeteksi konten berbahaya
        $dangerous_patterns = [
            '<?php',           // PHP code
            '<%',              // ASP code
            '<script',         // JavaScript
            'eval(',           // Eval function
            'base64_decode',   // Base64 decode
            'shell_exec',      // Shell execution
            'system(',         // System calls
            'exec(',           // Exec calls
            'passthru(',       // Passthru calls
            'file_get_contents(', // File access
            'fopen(',          // File operations
            'include(',        // Include operations
            'require(',        // Require operations
            'javascript:',     // JavaScript protocol
            'vbscript:'        // VBScript protocol
        ];

        try {
            // Baca bagian awal file untuk scanning (8KB pertama)
            $file_content = file_get_contents($file_path, false, null, 0, 8192);
            
            if ($file_content === false) {
                return [
                    'status' => false,
                    'message' => 'Tidak dapat membaca file untuk validasi keamanan!'
                ];
            }

            // Convert ke lowercase untuk pengecekan
            $content_lower = strtolower($file_content);
            
            foreach ($dangerous_patterns as $pattern) {
                if (strpos($content_lower, strtolower($pattern)) !== false) {
                    return [
                        'status' => false,
                        'message' => 'File terdeteksi mengandung konten berbahaya! Upload ditolak untuk keamanan sistem. Pastikan file Anda adalah dokumen Word atau PDF yang valid.'
                    ];
                }
            }
            
            return ['status' => true, 'message' => 'Basic virus scan clean'];
            
        } catch (Exception $e) {
            log_message('error', 'Basic virus scan error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Error dalam validasi keamanan file!'
            ];
        }
    }

    // ========================================
    // METODE HELPER: CEK APAKAH FUNCTION DISABLED
    // ========================================
    private function _is_function_disabled($function_name)
    {
        $disabled_functions = explode(',', ini_get('disable_functions'));
        $disabled_functions = array_map('trim', $disabled_functions);
        return in_array($function_name, $disabled_functions);
    }

    /**
     * Kirim notifikasi email ke mahasiswa
     * DIPERBARUI: Tambah parameter untuk pengajuan ulang
     */
    private function _send_notification_to_mahasiswa($mahasiswa_id, $proposal_id, $post_data, $is_resubmission = false)
    {
        try {
            // Ambil data mahasiswa dan prodi
            $this->db->select('m.*, p.nama as nama_prodi');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('m.id', $mahasiswa_id);
            $mahasiswa = $this->db->get()->row();

            if (!$mahasiswa) {
                log_message('error', 'Data mahasiswa tidak ditemukan untuk ID: ' . $mahasiswa_id);
                return false;
            }

            // Initialize email dengan konfigurasi yang sudah terbukti bekerja
            $this->email->initialize();
            $this->email->clear();
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($mahasiswa->email);

            // PERUBAHAN: Subject dan template berbeda untuk pengajuan ulang
            if ($is_resubmission) {
                $this->email->subject('Konfirmasi Pengajuan Ulang Proposal Skripsi');
                $message = $this->_get_mahasiswa_resubmission_email_template($mahasiswa, $proposal_id, $post_data);
            } else {
                $this->email->subject('Konfirmasi Pengajuan Usulan Proposal Skripsi');
                $message = $this->_get_mahasiswa_email_template($mahasiswa, $proposal_id, $post_data);
            }

            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', 'Email konfirmasi berhasil dikirim ke mahasiswa: ' . $mahasiswa->email);
                return true;
            } else {
                log_message('error', 'Gagal mengirim email ke mahasiswa: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi email ke kaprodi
     * DIPERBARUI: Tambah parameter untuk pengajuan ulang
     */
    private function _send_notification_to_kaprodi($mahasiswa_id, $proposal_id, $post_data, $is_resubmission = false)
    {
        try {
            // Ambil data mahasiswa dan prodi
            $this->db->select('m.*, p.nama as nama_prodi, p.id as prodi_id');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('m.id', $mahasiswa_id);
            $mahasiswa = $this->db->get()->row();

            if (!$mahasiswa) {
                log_message('error', 'Data mahasiswa tidak ditemukan untuk ID: ' . $mahasiswa_id);
                return false;
            }

            // Ambil data kaprodi berdasarkan prodi mahasiswa
            $this->db->select('d.*, p.nama as nama_prodi');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.id = p.dosen_id');
            $this->db->where('p.id', $mahasiswa->prodi_id);
            $this->db->where('d.level', '4'); // Level 4 = Kaprodi
            $kaprodi = $this->db->get()->row();

            if (!$kaprodi) {
                log_message('error', 'Data kaprodi tidak ditemukan untuk prodi ID: ' . $mahasiswa->prodi_id);
                return false;
            }

            // Initialize email
            $this->email->initialize();
            $this->email->clear();
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($kaprodi->email);

            // PERUBAHAN: Subject dan template berbeda untuk pengajuan ulang
            if ($is_resubmission) {
                $this->email->subject('🔄 Pengajuan Ulang Proposal (Sudah Diperbaiki) - ' . $mahasiswa->nama);
                $message = $this->_get_kaprodi_resubmission_email_template($mahasiswa, $kaprodi, $proposal_id, $post_data);
            } else {
                $this->email->subject('Notifikasi Pengajuan Usulan Proposal Skripsi Baru');
                $message = $this->_get_kaprodi_email_template($mahasiswa, $kaprodi, $proposal_id, $post_data);
            }

            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', 'Email notifikasi berhasil dikirim ke kaprodi: ' . $kaprodi->email);
                return true;
            } else {
                log_message('error', 'Gagal mengirim email ke kaprodi: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email to kaprodi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Template email untuk mahasiswa (EXISTING - TIDAK BERUBAH)
     */
    private function _get_mahasiswa_email_template($mahasiswa, $proposal_id, $post_data)
    {
        $tanggal_pengajuan = date('d F Y, H:i');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Konfirmasi Pengajuan Proposal</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>Konfirmasi Pengajuan Proposal</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Yth. <strong>{$mahasiswa->nama}</strong>,</p>
                    
                    <p>Usulan proposal skripsi Anda telah <strong>berhasil diterima</strong> dengan detail sebagai berikut:</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; width: 30%; font-weight: bold;'>ID Proposal:</td>
                                <td style='padding: 8px 0;'>#{$proposal_id}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nim}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Program Studi:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal:</td>
                                <td style='padding: 8px 0;'>{$post_data['judul']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Jenis Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['jenis_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Lokasi Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['lokasi_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Tanggal Pengajuan:</td>
                                <td style='padding: 8px 0;'>{$tanggal_pengajuan} WIT</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>🔄 Status: Menunggu Persetujuan Kaprodi</h4>
                        <p style='margin: 0; color: #856404;'>Proposal Anda sedang menunggu persetujuan dari Ketua Program Studi. Anda akan mendapat notifikasi lebih lanjut melalui email dan sistem.</p>
                    </div>
                    
                    <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ul style='color: #0c5460; margin: 0; padding-left: 20px;'>
                            <li>Pantau status proposal melalui dashboard SIM Tugas Akhir</li>
                            <li>Pastikan email Anda selalu aktif untuk menerima notifikasi</li>
                            <li>Siapkan berkas-berkas pendukung yang mungkin diperlukan</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('mahasiswa/proposal') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           📊 Lihat Status Proposal
                        </a>
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                        Telepon: 09713330264
                    </p>
                    <p style='margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;'>
                        Jangan membalas email ini. Email ini dikirim secara otomatis.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * TAMBAHAN BARU: Template email untuk mahasiswa pengajuan ulang
     */
    private function _get_mahasiswa_resubmission_email_template($mahasiswa, $proposal_id, $post_data)
    {
        $tanggal_pengajuan_ulang = date('d F Y, H:i');
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Konfirmasi Pengajuan Ulang Proposal</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🔄 Konfirmasi Pengajuan Ulang Proposal</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Yth. <strong>{$mahasiswa->nama}</strong>,</p>
                    
                    <p>Proposal skripsi yang telah Anda perbaiki telah <strong>berhasil diajukan ulang</strong> dengan detail sebagai berikut:</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; width: 30%; font-weight: bold;'>ID Proposal:</td>
                                <td style='padding: 8px 0;'>#{$proposal_id}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nim}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Program Studi:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal (Diperbaiki):</td>
                                <td style='padding: 8px 0;'><strong>{$post_data['judul']}</strong></td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Tanggal Pengajuan Ulang:</td>
                                <td style='padding: 8px 0;'>{$tanggal_pengajuan_ulang} WIT</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Status: Menunggu Review Ulang dari Kaprodi</h4>
                        <p style='margin: 0; color: #155724;'>Proposal yang sudah diperbaiki akan direview ulang oleh Ketua Program Studi. Terima kasih atas upaya perbaikan yang telah dilakukan.</p>
                    </div>
                    
                    <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ul style='color: #0c5460; margin: 0; padding-left: 20px;'>
                            <li>Pantau status proposal melalui dashboard SIM Tugas Akhir</li>
                            <li>Tunggu hasil review ulang dari Kaprodi</li>
                            <li>Pastikan email Anda selalu aktif untuk menerima notifikasi</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('mahasiswa/proposal') . "' 
                           style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           📊 Lihat Status Proposal
                        </a>
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                        Telepon: 09713330264
                    </p>
                    <p style='margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;'>
                        Jangan membalas email ini. Email ini dikirim secara otomatis.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template email untuk kaprodi (EXISTING - TIDAK BERUBAH)
     */
    private function _get_kaprodi_email_template($mahasiswa, $kaprodi, $proposal_id, $post_data)
    {
        $tanggal_pengajuan = date('d F Y, H:i');
        $ringkasan_masalah = substr($post_data['uraian_masalah'], 0, 300) . '...';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Notifikasi Proposal Baru</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background-color: #dc3545; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🚨 Proposal Baru Memerlukan Persetujuan</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Yth. <strong>{$kaprodi->nama}</strong><br>
                    Ketua Program Studi {$kaprodi->nama_prodi},</p>
                    
                    <p>Terdapat pengajuan usulan proposal skripsi baru yang memerlukan persetujuan Anda:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; width: 30%; font-weight: bold;'>ID Proposal:</td>
                                <td style='padding: 8px 0;'>#{$proposal_id}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Nama Mahasiswa:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nama}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nim}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Program Studi:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nama_prodi}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal:</td>
                                <td style='padding: 8px 0;'>{$post_data['judul']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Jenis Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['jenis_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Lokasi Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['lokasi_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Tanggal Pengajuan:</td>
                                <td style='padding: 8px 0;'>{$tanggal_pengajuan} WIT</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>📄 Ringkasan Uraian Masalah:</h4>
                        <p style='margin: 0; color: #721c24; font-style: italic;'>{$ringkasan_masalah}</p>
                    </div>
                    
                    <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Tindakan yang Diperlukan:</h4>
                        <ul style='color: #155724; margin: 0; padding-left: 20px;'>
                            <li>Review proposal mahasiswa</li>
                            <li>Tentukan dosen pembimbing</li>
                            <li>Berikan persetujuan atau feedback</li>
                            <li>Lakukan penetapan melalui sistem</li>
                        </ul>
                        <p style='color: #155724; margin: 10px 0 0 0; font-size: 12px; font-style: italic;'>
                            <strong>Catatan:</strong> Dosen penguji akan ditentukan nanti saat mahasiswa mengajukan Seminar Proposal.
                        </p>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('kaprodi/proposal') . "' 
                           style='background-color: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           🏃‍♂️ Akses SIM Tugas Akhir
                        </a>
                    </p>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 3px; margin: 20px 0;'>
                        <p style='margin: 0; font-size: 12px; color: #856404; text-align: center;'>
                            <strong>⏰ Mohon segera ditindaklanjuti</strong><br>
                            Mahasiswa menunggu konfirmasi persetujuan proposal
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                        Telepon: 09713330264
                    </p>
                    <p style='margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;'>
                        Mohon segera ditindaklanjuti. Jangan membalas email ini.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * TAMBAHAN BARU: Template email untuk kaprodi pengajuan ulang
     */
    private function _get_kaprodi_resubmission_email_template($mahasiswa, $kaprodi, $proposal_id, $post_data)
    {
        $tanggal_pengajuan_ulang = date('d F Y, H:i');
        $ringkasan_masalah = substr($post_data['uraian_masalah'], 0, 300) . '...';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Pengajuan Ulang Proposal</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background-color: #f39c12; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🔄 Pengajuan Ulang Proposal (Sudah Diperbaiki)</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Yth. <strong>{$kaprodi->nama}</strong><br>
                    Ketua Program Studi {$kaprodi->nama_prodi},</p>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 PENGAJUAN ULANG PROPOSAL</h4>
                        <p style='margin: 0; color: #856404;'>
                            Mahasiswa telah memperbaiki proposal sesuai catatan review sebelumnya dan mengajukan kembali untuk review ulang.
                        </p>
                    </div>
                    
                    <p>Detail proposal yang telah diperbaiki:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #f39c12;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; width: 30%; font-weight: bold;'>ID Proposal:</td>
                                <td style='padding: 8px 0;'>#{$proposal_id}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Nama Mahasiswa:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nama}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$mahasiswa->nim}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal (Diperbaiki):</td>
                                <td style='padding: 8px 0;'><strong>{$post_data['judul']}</strong></td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Jenis Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['jenis_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Lokasi Penelitian:</td>
                                <td style='padding: 8px 0;'>{$post_data['lokasi_penelitian']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Tanggal Pengajuan Ulang:</td>
                                <td style='padding: 8px 0;'>{$tanggal_pengajuan_ulang} WIT</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #0056b3; margin: 0 0 10px 0;'>📄 Ringkasan Uraian Masalah (Diperbaiki):</h4>
                        <p style='margin: 0; color: #0056b3; font-style: italic;'>{$ringkasan_masalah}</p>
                    </div>
                    
                    <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Tindakan yang Diperlukan:</h4>
                        <ul style='color: #155724; margin: 0; padding-left: 20px;'>
                            <li><strong>Review ulang</strong> proposal yang telah diperbaiki</li>
                            <li>Bandingkan dengan catatan review sebelumnya</li>
                            <li>Berikan persetujuan atau feedback lanjutan</li>
                            <li>Tetapkan dosen pembimbing jika disetujui</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('kaprodi/proposal') . "' 
                           style='background-color: #f39c12; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           🔍 Review Proposal Ulang
                        </a>
                    </p>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 3px; margin: 20px 0;'>
                        <p style='margin: 0; font-size: 12px; color: #856404; text-align: center;'>
                            <strong>🔄 Pengajuan Ulang</strong><br>
                            Mahasiswa telah memperbaiki proposal sesuai saran Anda
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                        Telepon: 09713330264
                    </p>
                    <p style='margin: 10px 0 0 0; font-size: 11px; color: #adb5bd;'>
                        Mohon segera ditindaklanjuti. Jangan membalas email ini.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
}