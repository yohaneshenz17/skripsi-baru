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
 * Input repository link dengan flexible column handling
 * REPLACE method input_repository yang sudah ada dengan kode ini
 */
public function input_repository($publikasi_id) {
    try {
        // Validasi ID publikasi (gunakan logic yang sudah ada)
        $publikasi = $this->_get_publikasi_detail_safe($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
            return;
        }
        
        // ===== PERBAIKAN: HANDLE POST REQUEST DENGAN PROPER VALIDATION =====
        if ($this->input->post()) {
            // Set validation rules yang proper
            $this->form_validation->set_rules('link_repository', 'Link Repository', 
                'required|valid_url|max_length[500]');
            $this->form_validation->set_rules('catatan_staf', 'Catatan Staf', 
                'max_length[1000]');
            
            if ($this->form_validation->run()) {
                // Prepare update data (gunakan struktur yang sudah ada)
                $update_data = [
                    'link_repository' => $this->input->post('link_repository'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // ===== PERBAIKAN: TAMBAH CATATAN STAF JIKA ADA =====
                $catatan_staf = $this->input->post('catatan_staf');
                if (!empty($catatan_staf) && $this->_column_exists('catatan_staf')) {
                    $update_data['catatan_staf'] = $catatan_staf;
                }
                
                // Tambahan kolom jika tersedia (logic yang sudah ada)
                if ($this->_column_exists('tanggal_input_repository')) {
                    $update_data['tanggal_input_repository'] = date('Y-m-d H:i:s');
                }
                
                // ===== PERBAIKAN: UPDATE DAN PROPER RESPONSE =====
                if ($this->_update_publikasi_safe($publikasi_id, $update_data)) {
                    $action = empty($publikasi->link_repository) ? 'disimpan' : 'diperbarui';
                    $this->session->set_flashdata('success', "Link repository berhasil {$action}.");
                    
                    // ===== PERBAIKAN: PROPER REDIRECT =====
                    redirect('staf/publikasi/detail/' . $publikasi_id);
                    return;
                } else {
                    $this->session->set_flashdata('error', 'Gagal menyimpan data. Silakan coba lagi.');
                }
            } else {
                // ===== PERBAIKAN: PROPER VALIDATION ERROR HANDLING =====
                $this->session->set_flashdata('error', 'Data tidak valid. ' . validation_errors(' ', ' '));
            }
            
            // Redirect kembali ke form jika ada error
            redirect('staf/publikasi/input_repository/' . $publikasi_id);
            return;
        }
        
        // ===== GET REQUEST - TAMPILKAN FORM (logic yang sudah ada) =====
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
        // Error handling yang sudah ada
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
     * Update publikasi dengan safe handling
     */
    private function _update_publikasi_safe($publikasi_id, $data) {
        try {
            $main_table = $this->table_info['main_table'] ?? 'proposal_mahasiswa';
            
            // Filter data untuk kolom yang tersedia saja
            $filtered_data = [];
            foreach ($data as $key => $value) {
                if ($this->_column_exists($key)) {
                    $filtered_data[$key] = $value;
                }
            }
            
            return $this->db->where('id', $publikasi_id)
                           ->update($main_table, $filtered_data);
                           
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
            'updated_at' => date('Y-m-d H:i:s')
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
     * Send notification email with error handling
     */
    private function _send_notification_email_safe($publikasi, $keputusan, $catatan) {
        try {
            $this->load->library('email');
            
            $subject = 'Notifikasi Validasi Publikasi Tugas Akhir';
            $status_text = $keputusan === 'approved' ? 'DISETUJUI' : 'DITOLAK';
            
            $message = "
            <h3>Publikasi Tugas Akhir {$status_text}</h3>
            <p><strong>Mahasiswa:</strong> {$publikasi->nama_mahasiswa}</p>
            <p><strong>Judul:</strong> {$publikasi->judul}</p>
            <p><strong>Status:</strong> {$status_text}</p>
            <p><strong>Catatan:</strong> {$catatan}</p>
            <p><strong>Tanggal:</strong> " . date('d F Y') . "</p>
            ";
            
            // Kirim ke mahasiswa jika email tersedia
            if (!empty($publikasi->email_mahasiswa)) {
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM-TA STK Santo Yakobus');
                $this->email->to($publikasi->email_mahasiswa);
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
            }
            
        } catch (Exception $e) {
            log_message('error', 'Email notification failed: ' . $e->getMessage());
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