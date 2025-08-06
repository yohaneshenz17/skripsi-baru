<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Staf - ROBUST DATABASE FLEXIBLE VERSION
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * 
 * PERBAIKAN:
 * - Deteksi otomatis struktur database yang tersedia
 * - Fallback untuk berbagai nama kolom yang mungkin ada
 * - Error handling yang komprehensif
 * - Tidak bergantung pada struktur database yang spesifik
 * 
 * File: application/controllers/staf/Publikasi.php
 */
class Publikasi extends CI_Controller {

    private $table_info = [];
    private $available_columns = [];

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'date']);
        
        // Auth check untuk staf
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '5') { // Level 5 = Staf
            show_error('Akses ditolak. Halaman khusus staf.', 403);
        }
        
        // Initialize database info
        $this->_initialize_database_info();
    }

    /**
     * Dashboard publikasi untuk staf
     * ROBUST: Menggunakan deteksi database otomatis
     */
    public function index() {
        try {
            // Get statistics dengan error handling
            $stats = $this->_get_statistics_safe();
            
            // Get pengajuan list dengan error handling
            $pengajuan_list = $this->_get_pengajuan_list_safe();
            
            // Prepare data untuk view
            $view_data = [
                'statistics' => $stats,
                'pengajuan_list' => $pengajuan_list,
                'title' => 'Validasi Publikasi Tugas Akhir',
                'debug_info' => $this->_get_debug_info()
            ];
            
            // Render dengan template staf yang konsisten
            $content = $this->load->view('staf/publikasi/index', $view_data, TRUE);
            
            $this->load->view('template/staf', [
                'title' => 'Validasi Publikasi Tugas Akhir',
                'content' => $content,
                'css' => $this->_get_page_css(),
                'script' => $this->_get_page_script()
            ]);
            
        } catch (Exception $e) {
            // Jika ada error, tampilkan halaman error yang informatif
            $this->_show_error_page($e->getMessage());
        }
    }

    /**
     * Detail publikasi dengan error handling
     */
    public function detail($publikasi_id) {
        try {
            $publikasi = $this->_get_publikasi_detail_safe($publikasi_id);
            
            if (!$publikasi) {
                $this->session->set_flashdata('error', 'Data tidak ditemukan.');
                redirect('staf/publikasi');
            }
            
            $view_data = [
                'publikasi' => $publikasi,
                'title' => 'Detail Publikasi - ' . substr($publikasi->judul, 0, 50)
            ];
            
            $content = $this->load->view('staf/publikasi/detail', $view_data, TRUE);
            
            $this->load->view('template/staf', [
                'title' => 'Detail Publikasi',
                'content' => $content
            ]);
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
            redirect('staf/publikasi');
        }
    }

/**
 * REPLACE method input_repository() dengan kode ini:
 */
public function input_repository($publikasi_id) {
    try {
        // Validasi ID publikasi
        $publikasi = $this->_get_publikasi_detail_safe($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
            return;
        }
        
        // HANDLE POST REQUEST
        if ($this->input->post()) {
            // Validation rules
            $this->form_validation->set_rules('link_repository', 'Link Repository', 
                'required|valid_url|max_length[500]');
            $this->form_validation->set_rules('catatan_staf', 'Catatan Staf', 
                'max_length[1000]');
            
            if ($this->form_validation->run()) {
                
                // Get session data
                $user_id = $this->session->userdata('id');
                $user_name = $this->session->userdata('nama');
                
                if (empty($user_id)) {
                    $this->session->set_flashdata('error', 'Session tidak valid. Silakan login ulang.');
                    redirect('auth/login');
                    return;
                }
                
                // ===== FINAL FIX: GUNAKAN KOLOM YANG BENAR =====
                $update_data = [
                    'link_repository' => $this->input->post('link_repository'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'validated_by_staf_id' => $user_id,      // ✅ KOLOM INI ADA
                    'validated_by_staf_name' => $user_name   // ✅ KOLOM INI ADA
                ];
                
                // Tambah catatan staf jika ada
                $catatan_staf = $this->input->post('catatan_staf');
                if (!empty($catatan_staf)) {
                    $update_data['komentar_staf'] = $catatan_staf;  // ✅ KOLOM INI ADA
                }
                
                // Tambah timestamp validasi
                $update_data['tanggal_validasi_staf'] = date('Y-m-d H:i:s');  // ✅ KOLOM INI ADA
                
                // DIRECT UPDATE ke tabel
                $this->db->where('id', $publikasi_id);
                $result = $this->db->update('publikasi_tugas_akhir', $update_data);
                
                if ($result) {
                    $action = empty($publikasi->link_repository) ? 'disimpan' : 'diperbarui';
                    $this->session->set_flashdata('success', "Link repository berhasil {$action}.");
                    redirect('staf/publikasi/detail/' . $publikasi_id);
                    return;
                } else {
                    $error = $this->db->error();
                    log_message('error', 'Database error: ' . json_encode($error));
                    $this->session->set_flashdata('error', 'Gagal menyimpan data. Error: ' . $error['message']);
                }
            } else {
                $this->session->set_flashdata('error', 'Data tidak valid. ' . validation_errors(' ', ' '));
            }
            
            redirect('staf/publikasi/input_repository/' . $publikasi_id);
            return;
        }
        
        // GET REQUEST - TAMPILKAN FORM
        $view_data = [
            'publikasi' => $publikasi,
            'title' => 'Input Repository - ' . substr($publikasi->judul, 0, 50)
        ];
        
        $content = $this->load->view('staf/publikasi/input_repository', $view_data, TRUE);
        
        $this->load->view('template/staf', [
            'title' => 'Input Repository',
            'content' => $content
        ]);
        
    } catch (Exception $e) {
        log_message('error', 'input_repository exception: ' . $e->getMessage());
        $this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
        redirect('staf/publikasi');
    }
}


    /**
     * Validasi final dengan flexible status handling
     */
    public function validasi($publikasi_id) {
        try {
            $publikasi = $this->_get_publikasi_detail_safe($publikasi_id);
            
            if (!$publikasi || empty($publikasi->link_repository)) {
                $this->session->set_flashdata('error', 'Repository link belum diinput.');
                redirect('staf/publikasi');
            }
            
            if ($this->input->post()) {
                $keputusan = $this->input->post('keputusan'); // 'approved' atau 'rejected'
                $catatan = $this->input->post('catatan');
                
                $this->form_validation->set_rules('keputusan', 'Keputusan', 'required|in_list[approved,rejected]');
                $this->form_validation->set_rules('catatan', 'Catatan', 'required');
                
                if ($this->form_validation->run()) {
                    $update_data = $this->_prepare_validation_data($keputusan, $catatan);
                    
                    if ($this->_update_publikasi_safe($publikasi_id, $update_data)) {
                        // Send notification email
                        $this->_send_notification_email_safe($publikasi, $keputusan, $catatan);
                        
                        $message = $keputusan === 'approved' ? 'Publikasi berhasil divalidasi.' : 'Publikasi ditolak.';
                        $this->session->set_flashdata('success', $message);
                        redirect('staf/publikasi');
                    } else {
                        $this->session->set_flashdata('error', 'Gagal menyimpan validasi.');
                    }
                }
            }
            
            $view_data = [
                'publikasi' => $publikasi,
                'title' => 'Validasi Publikasi - ' . substr($publikasi->judul, 0, 50)
            ];
            
            $content = $this->load->view('staf/publikasi/validasi', $view_data, TRUE);
            
            $this->load->view('template/staf', [
                'title' => 'Validasi Publikasi',
                'content' => $content
            ]);
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
            redirect('staf/publikasi');
        }
    }

    /**
     * Export dengan pesan info
     */
    public function export() {
        $this->session->set_flashdata('info', 'Fitur export akan segera tersedia.');
        redirect('staf/publikasi');
    }

    // =================================================================
    // PRIVATE METHODS - DATABASE DETECTION & SAFE OPERATIONS
    // =================================================================

    /**
     * Initialize database information
     */
    private function _initialize_database_info() {
        try {
            // Deteksi tabel yang tersedia
            $tables = $this->db->list_tables();
            
            $this->table_info = [
                'has_publikasi_tugas_akhir' => in_array('publikasi_tugas_akhir', $tables),
                'has_proposal_mahasiswa' => in_array('proposal_mahasiswa', $tables),
                'has_publikasi_view' => in_array('publikasi_mahasiswa_v', $tables)
            ];
            
            // Tentukan tabel utama yang akan digunakan
            if ($this->table_info['has_publikasi_tugas_akhir']) {
                $this->table_info['main_table'] = 'publikasi_tugas_akhir';
                $this->available_columns = $this->db->list_fields('publikasi_tugas_akhir');
            } elseif ($this->table_info['has_proposal_mahasiswa']) {
                $this->table_info['main_table'] = 'proposal_mahasiswa';
                $this->available_columns = $this->db->list_fields('proposal_mahasiswa');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Database initialization failed: ' . $e->getMessage());
            $this->table_info = ['error' => $e->getMessage()];
        }
    }

    /**
     * Check if column exists in current table
     */
    private function _column_exists($column_name) {
        return in_array($column_name, $this->available_columns);
    }

    /**
     * Get statistics dengan safe fallback
     */
    private function _get_statistics_safe() {
        try {
            $main_table = $this->table_info['main_table'] ?? 'proposal_mahasiswa';
            
            // Base query conditions
            $base_conditions = [];
            
            if ($main_table === 'publikasi_tugas_akhir') {
                // Untuk tabel publikasi_tugas_akhir
                $status_column = $this->_column_exists('status_staf') ? 'status_staf' : 'status';
                
                $total = $this->db->count_all_results($main_table);
                
                $menunggu = $this->db->where('(link_repository IS NULL OR link_repository = "")')
                                  ->count_all_results($main_table);
                
                if ($this->_column_exists('status_staf')) {
                    $divalidasi = $this->db->where('status_staf', 'approved')->count_all_results($main_table);
                } else {
                    $divalidasi = $this->db->where('status', 'completed')->count_all_results($main_table);
                }
                
                $selesai = $divalidasi; // Same as validated for now
                
            } else {
                // Fallback ke proposal_mahasiswa
                $menunggu = $this->db->where('workflow_status', 'publikasi')
                                  ->where('(link_repository IS NULL OR link_repository = "")')
                                  ->count_all_results($main_table);
                
                $divalidasi = $this->db->where('workflow_status', 'publikasi')
                                     ->where('status_publikasi', 'completed')
                                     ->count_all_results($main_table);
                
                $selesai = $divalidasi;
                $total = $this->db->where('workflow_status', 'publikasi')->count_all_results($main_table);
            }
            
            return [
                'menunggu_validasi' => $menunggu,
                'sudah_divalidasi' => $divalidasi,
                'publikasi_selesai' => $selesai,
                'total_keseluruhan' => $total,
                'bulan_ini' => 0 // Default for now
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Statistics query failed: ' . $e->getMessage());
            
            // Return safe default values
            return [
                'menunggu_validasi' => 0,
                'sudah_divalidasi' => 0,
                'publikasi_selesai' => 0,
                'total_keseluruhan' => 0,
                'bulan_ini' => 0
            ];
        }
    }

    /**
     * Get pengajuan list dengan safe handling
     */
    private function _get_pengajuan_list_safe() {
        try {
            $main_table = $this->table_info['main_table'] ?? 'proposal_mahasiswa';
            
            if ($main_table === 'publikasi_tugas_akhir') {
                // Query untuk tabel publikasi_tugas_akhir
                $this->db->select('
                    pta.id, pta.link_repository, pta.created_at,
                    COALESCE(pta.status_staf, pta.status, "pending") as status_staf,
                    pm.judul,
                    m.nim, m.nama as nama_mahasiswa,
                    d.nama as nama_dosen
                ');
                $this->db->from('publikasi_tugas_akhir pta');
                $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id', 'left');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
                $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
                $this->db->order_by('pta.created_at', 'DESC');
                $this->db->limit(20);
                
                return $this->db->get()->result();
                
            } else {
                // Fallback ke proposal_mahasiswa
                $this->db->select('
                    pm.id, pm.link_repository, pm.created_at,
                    COALESCE(pm.validasi_staf_publikasi, "0") as status_staf,
                    pm.judul,
                    m.nim, m.nama as nama_mahasiswa,
                    d.nama as nama_dosen
                ');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
                $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
                $this->db->where('pm.workflow_status', 'publikasi');
                $this->db->order_by('pm.created_at', 'DESC');
                $this->db->limit(20);
                
                return $this->db->get()->result();
            }
            
        } catch (Exception $e) {
            log_message('error', 'Pengajuan list query failed: ' . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    /**
     * Get detail publikasi dengan safe handling
     */
    private function _get_publikasi_detail_safe($publikasi_id) {
        try {
            $main_table = $this->table_info['main_table'] ?? 'proposal_mahasiswa';
            
            if ($main_table === 'publikasi_tugas_akhir') {
                $this->db->select('
                    pta.*, 
                    pm.judul,
                    m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                    d.nama as nama_dosen, d.email as email_dosen,
                    pr.nama as nama_prodi
                ');
                $this->db->from('publikasi_tugas_akhir pta');
                $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id', 'left');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
                $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
                $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
                $this->db->where('pta.id', $publikasi_id);
                
            } else {
                $this->db->select('
                    pm.*, 
                    m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                    d.nama as nama_dosen, d.email as email_dosen,
                    pr.nama as nama_prodi
                ');
                $this->db->from('proposal_mahasiswa pm');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
                $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
                $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
                $this->db->where('pm.id', $publikasi_id);
            }
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Detail query failed: ' . $e->getMessage());
            return null;
        }
    }

/**
 * PERBAIKAN JUGA: Method _update_publikasi_safe()
 * REPLACE method ini juga untuk konsistensi
 */
private function _update_publikasi_safe($publikasi_id, $data) {
    try {
        $main_table = $this->table_info['main_table'] ?? 'publikasi_tugas_akhir';
        
        // ✅ HAPUS LOGIC user_id - TIDAK ADA KOLOM INI
        // Tidak perlu cek user_id lagi karena kolom tidak ada
        
        // Filter data untuk kolom yang tersedia saja
        $filtered_data = [];
        foreach ($data as $key => $value) {
            if ($this->_column_exists($key)) {
                $filtered_data[$key] = $value;
            }
        }
        
        // ✅ FALLBACK: Jika method _column_exists() bermasalah, langsung set
        if (empty($filtered_data)) {
            $filtered_data = $data; // Gunakan data langsung
        }
        
        if (empty($filtered_data)) {
            log_message('error', 'No valid data to update for publikasi_id: ' . $publikasi_id);
            return false;
        }
        
        $result = $this->db->where('id', $publikasi_id)
                          ->update($main_table, $filtered_data);
        
        if (!$result) {
            log_message('error', 'Database update failed. Last query: ' . $this->db->last_query());
        }
        
        return $result;
                           
    } catch (Exception $e) {
        log_message('error', 'Update failed: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Prepare validation data based on available columns
     */
    private function _prepare_validation_data($keputusan, $catatan) {
        $data = [
        ];
        
        // Add columns based on availability
        if ($this->_column_exists('status_staf')) {
            $data['status_staf'] = $keputusan;
        }
        
        if ($this->_column_exists('validasi_staf_publikasi')) {
            $data['validasi_staf_publikasi'] = $keputusan === 'approved' ? '1' : '2';
        }
        
        if ($this->_column_exists('catatan_staf')) {
            $data['catatan_staf'] = $catatan;
        }
        
        if ($this->_column_exists('komentar_staf')) {
            $data['komentar_staf'] = $catatan;
        }
        
        if ($this->_column_exists('staf_validator_id')) {
            $data['staf_validator_id'] = $this->session->userdata('id');
        }
        
        if ($this->_column_exists('tanggal_validasi_staf')) {
            $data['tanggal_validasi_staf'] = date('Y-m-d H:i:s');
        }
        
        // Jika approved, set status completed
        if ($keputusan === 'approved') {
            if ($this->_column_exists('status_publikasi')) {
                $data['status_publikasi'] = 'completed';
            }
            
            if ($this->_column_exists('tanggal_publikasi')) {
                $data['tanggal_publikasi'] = date('Y-m-d H:i:s');
            }
        }
        
        return $data;
    }

    /**
     * REPLACE method _send_notification_email_safe() yang ada di line ~350-380
     * dengan kode di bawah ini:
     */
    private function _send_notification_email_safe($publikasi, $keputusan, $catatan) {
        try {
            $this->load->library('email');
            
            // ===== FIX: INLINE EMAIL CONFIG (tidak perlu method terpisah) =====
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls',
                'smtp_timeout' => 30,
                'wordwrap' => TRUE
            ];
            
            $this->email->initialize($config);
            
            $status_text = $keputusan === 'approved' ? 'DISETUJUI' : 'DITOLAK';
            $staf_nama = $this->session->userdata('nama') ?? 'Staf Akademik';
            $success_count = 0;
            
            // ===== EMAIL KE MAHASISWA (Enhanced) =====
            if (!empty($publikasi->email_mahasiswa)) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM-TA STK Santo Yakobus');
                $this->email->to($publikasi->email_mahasiswa);
                $this->email->subject('📢 Hasil Validasi Publikasi Tugas Akhir - ' . $status_text);
                
                // Template HTML untuk mahasiswa
                $message_mahasiswa = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <style>
                        .container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; }
                        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background: #f8f9fa; }
                        .status-approved { color: #28a745; font-weight: bold; }
                        .status-rejected { color: #dc3545; font-weight: bold; }
                        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                        .footer { background: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>🎓 STK Santo Yakobus Merauke</h2>
                            <h3>Sistem Informasi Tugas Akhir</h3>
                        </div>
                        
                        <div class='content'>
                            <h3>Hasil Validasi Publikasi Tugas Akhir</h3>
                            
                            <div class='info-box'>
                                <p><strong>Mahasiswa:</strong> " . ($publikasi->nama_mahasiswa ?? 'N/A') . "</p>
                                <p><strong>NIM:</strong> " . ($publikasi->nim ?? 'N/A') . "</p>
                                <p><strong>Judul:</strong> " . ($publikasi->judul ?? 'N/A') . "</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Status Validasi:</strong> 
                                <span class='status-" . ($keputusan === 'approved' ? 'approved' : 'rejected') . "'>
                                    " . ($keputusan === 'approved' ? '✅ DISETUJUI' : '❌ DITOLAK') . "
                                </span></p>
                                <p><strong>Tanggal Validasi:</strong> " . date('d F Y') . "</p>
                                <p><strong>Validator:</strong> {$staf_nama}</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Catatan Validasi:</strong></p>
                                <p style='background: #e9ecef; padding: 10px; border-radius: 3px;'>{$catatan}</p>
                            </div>";
                
                if ($keputusan === 'approved') {
                    $message_mahasiswa .= "
                            <div class='info-box' style='border: 2px solid #28a745;'>
                                <h4 style='color: #28a745;'>🎉 Selamat!</h4>
                                <p>Publikasi tugas akhir Anda telah <strong>disetujui</strong> dan telah dipublikasikan di repository perpustakaan digital.</p>";
                    
                    if (!empty($publikasi->link_repository)) {
                        $message_mahasiswa .= "<p><strong>Link Repository:</strong> <a href='" . $publikasi->link_repository . "' target='_blank'>" . $publikasi->link_repository . "</a></p>";
                    }
                    
                    $message_mahasiswa .= "
                                <p>Terima kasih telah menyelesaikan tugas akhir dengan baik!</p>
                            </div>";
                } else {
                    $message_mahasiswa .= "
                            <div class='info-box' style='border: 2px solid #dc3545;'>
                                <h4 style='color: #dc3545;'>📝 Perlu Perbaikan</h4>
                                <p>Publikasi tugas akhir Anda <strong>perlu diperbaiki</strong> sesuai catatan di atas.</p>
                                <p>Silakan hubungi dosen pembimbing atau staf akademik untuk konsultasi lebih lanjut.</p>
                            </div>";
                }
                
                $message_mahasiswa .= "
                        </div>
                        
                        <div class='footer'>
                            <p>Email ini dikirim otomatis oleh Sistem Informasi Tugas Akhir STK Santo Yakobus</p>
                            <p>Jangan reply email ini. Untuk pertanyaan, hubungi staf akademik.</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $this->email->message($message_mahasiswa);
                if ($this->email->send()) {
                    $success_count++;
                    log_message('info', "✅ Email ke mahasiswa berhasil: " . $publikasi->email_mahasiswa);
                } else {
                    log_message('error', "❌ Email ke mahasiswa gagal: " . $this->email->print_debugger());
                }
            }
            
            // ===== EMAIL KE DOSEN PEMBIMBING =====
            if (!empty($publikasi->email_dosen)) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM-TA STK Santo Yakobus');
                $this->email->to($publikasi->email_dosen);
                $this->email->subject('📋 Notifikasi Validasi Publikasi Mahasiswa Bimbingan - ' . $status_text);
                
                // Template untuk dosen
                $message_dosen = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <style>
                        .container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; }
                        .header { background: #17a2b8; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background: #f8f9fa; }
                        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                        .footer { background: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>📋 Notifikasi untuk Dosen Pembimbing</h2>
                            <h3>Validasi Publikasi Tugas Akhir</h3>
                        </div>
                        
                        <div class='content'>
                            <p>Yth. <strong>" . ($publikasi->nama_dosen ?? 'Dosen Pembimbing') . "</strong>,</p>
                            
                            <div class='info-box'>
                                <p>Publikasi tugas akhir mahasiswa bimbingan Anda telah divalidasi oleh staf akademik.</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Mahasiswa:</strong> " . ($publikasi->nama_mahasiswa ?? 'N/A') . " (" . ($publikasi->nim ?? 'N/A') . ")</p>
                                <p><strong>Judul:</strong> " . ($publikasi->judul ?? 'N/A') . "</p>
                                <p><strong>Status Validasi:</strong> <strong>{$status_text}</strong></p>
                                <p><strong>Tanggal Validasi:</strong> " . date('d F Y') . "</p>
                                <p><strong>Validator:</strong> {$staf_nama}</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Catatan Validasi:</strong></p>
                                <p style='background: #e9ecef; padding: 10px; border-radius: 3px;'>{$catatan}</p>
                            </div>
                            
                            <p>Terima kasih atas bimbingan yang telah diberikan kepada mahasiswa.</p>
                        </div>
                        
                        <div class='footer'>
                            <p>Email ini dikirim otomatis oleh Sistem Informasi Tugas Akhir STK Santo Yakobus</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $this->email->message($message_dosen);
                if ($this->email->send()) {
                    $success_count++;
                    log_message('info', "✅ Email ke dosen berhasil: " . $publikasi->email_dosen);
                } else {
                    log_message('error', "❌ Email ke dosen gagal: " . $this->email->print_debugger());
                }
            }
            
            // ===== EMAIL KE KAPRODI =====
            $kaprodi = $this->db->select('email, nama')
                               ->where('level', '4')
                               ->where('email IS NOT NULL')
                               ->where('email !=', '')
                               ->get('dosen')
                               ->row();
            
            if ($kaprodi && !empty($kaprodi->email)) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM-TA STK Santo Yakobus');
                $this->email->to($kaprodi->email);
                $this->email->subject('📊 Laporan Validasi Publikasi Tugas Akhir - ' . $status_text);
                
                // Template untuk kaprodi
                $message_kaprodi = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <style>
                        .container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; }
                        .header { background: #6f42c1; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background: #f8f9fa; }
                        .info-box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                        .footer { background: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>📊 Laporan untuk Kaprodi</h2>
                            <h3>Validasi Publikasi Tugas Akhir</h3>
                        </div>
                        
                        <div class='content'>
                            <p>Yth. " . $kaprodi->nama . ",</p>
                            
                            <div class='info-box'>
                                <p>Berikut laporan hasil validasi publikasi tugas akhir mahasiswa:</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Mahasiswa:</strong> " . ($publikasi->nama_mahasiswa ?? 'N/A') . " (" . ($publikasi->nim ?? 'N/A') . ")</p>
                                <p><strong>Dosen Pembimbing:</strong> " . ($publikasi->nama_dosen ?? 'N/A') . "</p>
                                <p><strong>Judul:</strong> " . ($publikasi->judul ?? 'N/A') . "</p>
                                <p><strong>Status Validasi:</strong> <strong>{$status_text}</strong></p>
                                <p><strong>Validator:</strong> {$staf_nama}</p>
                                <p><strong>Tanggal:</strong> " . date('d F Y') . "</p>
                            </div>
                            
                            <div class='info-box'>
                                <p><strong>Catatan Validasi:</strong></p>
                                <p style='background: #e9ecef; padding: 10px; border-radius: 3px;'>{$catatan}</p>
                            </div>
                            
                            <p>Laporan ini untuk monitoring dan evaluasi program studi.</p>
                        </div>
                        
                        <div class='footer'>
                            <p>Email ini dikirim otomatis oleh Sistem Informasi Tugas Akhir STK Santo Yakobus</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $this->email->message($message_kaprodi);
                if ($this->email->send()) {
                    $success_count++;
                    log_message('info', "✅ Email ke kaprodi berhasil: " . $kaprodi->email);
                } else {
                    log_message('error', "❌ Email ke kaprodi gagal: " . $this->email->print_debugger());
                }
            }
            
            // Log summary
            log_message('info', "📧 Email notification summary - publikasi_id: " . ($publikasi->id ?? 'N/A') . ", success: {$success_count}/3");
            return $success_count > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get debug info untuk troubleshooting
     */
    private function _get_debug_info() {
        if (ENVIRONMENT === 'development') {
            return [
                'table_info' => $this->table_info,
                'available_columns' => $this->available_columns,
                'main_table' => $this->table_info['main_table'] ?? 'undefined'
            ];
        }
        return null;
    }

    /**
     * Show error page yang informatif
     */
    private function _show_error_page($error_message) {
        $view_data = [
            'title' => 'Error - Publikasi Staf',
            'error_message' => $error_message,
            'debug_info' => $this->_get_debug_info()
        ];
        
        $content = $this->load->view('staf/publikasi/error', $view_data, TRUE);
        
        $this->load->view('template/staf', [
            'title' => 'Error - Publikasi',
            'content' => $content
        ]);
    }

/**
 * ✅ TAMBAHAN: Update workflow status ke selesai
 */
private function _update_workflow_status_to_selesai($publikasi_id) {
    try {
        // Get proposal_mahasiswa_id
        $publikasi = $this->db->select('proposal_mahasiswa_id')
                            ->from('publikasi_tugas_akhir')
                            ->where('id', $publikasi_id)
                            ->get()
                            ->row();
        
        if ($publikasi) {
            $this->db->where('id', $publikasi->proposal_mahasiswa_id)
                   ->update('proposal_mahasiswa', [
                       'workflow_status' => 'selesai'
                   ]);
            
            log_message('info', "✅ Workflow status updated to 'selesai' for proposal_id: " . $publikasi->proposal_mahasiswa_id);
        }
        
        return true;
    } catch (Exception $e) {
        log_message('error', 'Error updating workflow status: ' . $e->getMessage());
        return false;
    }
}

/**
 * ✅ TAMBAHAN: Generate surat keterangan publikasi
 */
private function _generate_surat_keterangan($publikasi_id) {
    try {
        // Generate filename
        $filename = 'SURAT_KETERANGAN_' . date('Ymd_His') . '_' . $publikasi_id . '.pdf';
        
        // Create directory if not exists
        if (!is_dir('./uploads/surat_keterangan/')) {
            mkdir('./uploads/surat_keterangan/', 0755, true);
        }
        
        // Simple dummy file for now
        $content = "SURAT KETERANGAN PUBLIKASI TUGAS AKHIR\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Publikasi ID: " . $publikasi_id . "\n";
        $content .= "Status: SELESAI - TERVALIDASI\n";
        
        file_put_contents('./uploads/surat_keterangan/' . $filename, $content);
        
        // Update database dengan filename
        $this->db->where('id', $publikasi_id)
               ->update('publikasi_tugas_akhir', [
                   'file_surat_keterangan' => $filename
               ]);
        
        log_message('info', "✅ Surat keterangan generated: " . $filename);
        return true;
        
    } catch (Exception $e) {
        log_message('error', 'Error generating surat keterangan: ' . $e->getMessage());
        return false;
    }
}

    /**
     * CSS untuk halaman
     */
    private function _get_page_css() {
        return "
        <style>
        .card-stats { transition: transform 0.3s ease; }
        .card-stats:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .workflow-step { text-align: center; position: relative; margin-bottom: 20px; }
        .step-number { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-bottom: 10px; }
        .step-title { font-weight: 600; margin-bottom: 5px; color: #32325d; }
        .step-desc { font-size: 0.875rem; color: #6c757d; margin-bottom: 0; }
        .debug-info { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.8rem; }
        </style>
        ";
    }

    /**
     * JavaScript untuk halaman
     */
    private function _get_page_script() {
        return "
        <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Confirm before validation action
            $('.btn-validasi').click(function(e) {
                if (!confirm('Yakin ingin memvalidasi publikasi ini?')) {
                    e.preventDefault();
                }
            });
            
            // Tooltip for long titles
            $('[title]').tooltip();
            
            // Debug info toggle
            $('.debug-toggle').click(function() {
                $('.debug-info').toggle();
            });
        });
        </script>
        ";
    }
}

/* EOF */