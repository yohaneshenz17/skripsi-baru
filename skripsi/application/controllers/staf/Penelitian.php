<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Penelitian
 * FINAL FIX - Sesuai dengan struktur database actual
 * - Kolom timestamp: tanggal_aktivitas (bukan created_at)
 * - Kolom aktivitas: aktivitas (bukan jenis_aktivitas)
 */
class Penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file', 'text']);
        $this->load->library(['pdf', 'upload', 'form_validation', 'email']); // ← TAMBAHKAN 'email'
        
        // Cek login dan level staf (level 5 di tabel dosen)
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu penelitian - FIXED QUERY
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status_izin = $this->input->get('status_izin'); 
        $search = $this->input->get('search');
        
        // Query utama - join dengan permohonan_izin_penelitian untuk mendapatkan status terbaru
        $this->db->select('
            pm.id,
            pm.judul,
            pm.lokasi_penelitian,
            pm.workflow_status,
            pm.status_izin_penelitian,
            pm.surat_izin_penelitian,
            pm.created_at,
            m.nim, 
            m.nama as nama_mahasiswa, 
            m.email,
            p.nama as nama_prodi,
            d1.nama as nama_pembimbing,
            pip.id as permohonan_id,
            pip.status as status_permohonan,
            pip.status_pembimbing,
            pip.tanggal_review_pembimbing,
            pip.file_surat_izin_staf,
            pip.tanggal_upload_surat_staf,
            pip.keterangan_staf
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'pm.dosen_id = d1.id', 'left');
        // LEFT JOIN dengan permohonan_izin_penelitian berdasarkan proposal_mahasiswa_id
        $this->db->join('permohonan_izin_penelitian pip', 'pm.id = pip.proposal_mahasiswa_id', 'left');
        
        // Filter hanya yang sudah masuk tahap penelitian atau lebih
        $this->db->where_in('pm.workflow_status', ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        if ($status_izin !== '') {
            if ($status_izin == '0') {
                // Belum ada surat izin - cek kedua kondisi
                $this->db->group_start();
                $this->db->where('(pm.surat_izin_penelitian IS NULL OR pm.surat_izin_penelitian = "")');
                $this->db->or_where('(pip.file_surat_izin_staf IS NULL OR pip.file_surat_izin_staf = "")');
                $this->db->group_end();
            } elseif ($status_izin == '1') {
                // Sudah ada surat izin
                $this->db->group_start();
                $this->db->where('pm.surat_izin_penelitian IS NOT NULL');
                $this->db->where('pm.surat_izin_penelitian !=', '');
                $this->db->or_where('pip.file_surat_izin_staf IS NOT NULL');
                $this->db->where('pip.file_surat_izin_staf !=', '');
                $this->db->group_end();
            }
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
        
        $penelitian_raw = $this->db->get()->result();
        
        // Proses data untuk menentukan action_status
        $data['penelitian'] = array_map(function($p) {
            // Tentukan action status berdasarkan kondisi workflow
            if ($p->permohonan_id) {
                // Ada permohonan di tabel permohonan_izin_penelitian
                if ($p->status_pembimbing == 'approved' && !$p->file_surat_izin_staf) {
                    $p->action_status = 'butuh_surat';
                } elseif ($p->status_pembimbing == 'approved' && $p->file_surat_izin_staf) {
                    $p->action_status = 'surat_ready';
                } elseif ($p->status_pembimbing == 'pending') {
                    $p->action_status = 'menunggu_pembimbing';
                } elseif ($p->status_pembimbing == 'rejected') {
                    $p->action_status = 'ditolak_pembimbing';
                } else {
                    $p->action_status = 'review_pembimbing';
                }
            } else {
                // Belum ada permohonan, hanya cek workflow_status
                if ($p->workflow_status == 'penelitian') {
                    $p->action_status = 'belum_mengajukan';
                } else {
                    $p->action_status = 'no_action_needed';
                }
            }
            return $p;
        }, $penelitian_raw);
        
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
     * Detail penelitian mahasiswa - FIXED QUERY
     */
    public function detail($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data lengkap proposal
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
        
        // Ambil data permohonan izin penelitian jika ada
        $this->db->select('pip.*, d.nama as nama_pembimbing_pip, d.nip as nip_pembimbing_pip');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
        $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
        
        $data['permohonan'] = $this->db->get()->row();
        
        // FIXED: Ambil log aktivitas dengan kolom yang benar
        if ($this->db->table_exists('staf_aktivitas')) {
            $this->db->select('sa.*, d.nama as nama_staf');
            $this->db->from('staf_aktivitas sa');
            $this->db->join('dosen d', 'sa.staf_id = d.id', 'left'); // Join dengan dosen, bukan staf
            $this->db->where('sa.proposal_id', $proposal_id);
            $this->db->order_by('sa.tanggal_aktivitas', 'DESC'); // FIXED: tanggal_aktivitas bukan created_at
            
            $data['log_aktivitas'] = $this->db->get()->result();
        } else {
            $data['log_aktivitas'] = [];
        }
        
        $this->load->view('staf/penelitian/detail', $data);
    }
    
    /**
     * Cetak form permohonan izin penelitian
     */
    public function cetak_form_permohonan($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data dari tabel permohonan_izin_penelitian yang sudah disetujui pembimbing
        $this->db->select('pip.*, d.nama as nama_pembimbing, d.nip as nip_pembimbing');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
        $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
        $this->db->where('pip.status_pembimbing', 'approved');
        
        $permohonan = $this->db->get()->row();
        
        if (!$permohonan) {
            $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan atau belum disetujui dosen pembimbing');
            redirect('staf/penelitian');
        }
        
        // Generate simple HTML response instead of PDF for now
        echo "<h1>Form Permohonan Penelitian</h1>";
        echo "<p><strong>Nama:</strong> {$permohonan->nama_mahasiswa}</p>";
        echo "<p><strong>NIM:</strong> {$permohonan->nim}</p>";
        echo "<p><strong>Program Studi:</strong> {$permohonan->program_studi}</p>";
        echo "<p><strong>Tempat Penelitian:</strong> {$permohonan->tempat_penelitian}</p>";
        echo "<p><strong>Tanggal:</strong> " . date('d F Y', strtotime($permohonan->tanggal_mulai_penelitian)) . " - " . date('d F Y', strtotime($permohonan->tanggal_selesai_penelitian)) . "</p>";
        
        // Log aktivitas - FIXED: gunakan kolom yang benar
        $this->_log_aktivitas('cetak_form_permohonan', $proposal_id, 
                             "Cetak form permohonan untuk {$permohonan->nama_mahasiswa}");
    }
    
    /**
     * Cetak surat izin penelitian
     */
    public function cetak_surat($proposal_id) {
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
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau belum disetujui pembimbing');
            redirect('staf/penelitian');
        }
        
        // Generate simple HTML response instead of PDF for now
        echo "<h1>SURAT IZIN PENELITIAN</h1>";
        echo "<p><strong>Nomor:</strong> " . $this->_generate_nomor_surat() . "</p>";
        echo "<p><strong>Nama:</strong> {$data_lengkap->nama_mahasiswa}</p>";
        echo "<p><strong>NIM:</strong> {$data_lengkap->nim}</p>";
        echo "<p><strong>Program Studi:</strong> {$data_lengkap->nama_prodi}</p>";
        echo "<p><strong>Judul:</strong> {$data_lengkap->judul}</p>";
        echo "<p><strong>Tempat Penelitian:</strong> {$data_lengkap->tempat_penelitian}</p>";
        echo "<p><strong>Pembimbing:</strong> {$data_lengkap->nama_pembimbing}</p>";
        
        // Log aktivitas
        $this->_log_aktivitas('cetak_surat', $proposal_id, 
                             "Cetak surat izin penelitian untuk {$data_lengkap->nama_mahasiswa}");
    }
    
    /**
     * Upload surat izin yang sudah ditandatangani - FIXED VERSION
     */
    public function upload_surat($proposal_id) {
    if (!$proposal_id) {
        echo json_encode(['status' => 'error', 'message' => 'ID proposal tidak valid']);
        return;
    }
    
    // Cek apakah permohonan ada dan sudah disetujui pembimbing
    $this->db->select('pip.*, pm.mahasiswa_id, m.nama as nama_mahasiswa, m.nim');
    $this->db->from('permohonan_izin_penelitian pip');
    $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
    $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
    $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
    $this->db->where('pip.status_pembimbing', 'approved');
    
    $permohonan = $this->db->get()->row();
    
    if (!$permohonan) {
        echo json_encode(['status' => 'error', 'message' => 'Permohonan tidak ditemukan atau belum disetujui pembimbing']);
        return;
    }
    
    // ========================================
    // ðŸ†• NATIVE PHP UPLOAD - BYPASS CI LIBRARY
    // ========================================
    
    // Validasi file upload
    if (!isset($_FILES['file_surat']) || $_FILES['file_surat']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        ];
        
        $error_code = $_FILES['file_surat']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Error upload tidak dikenal';
        
        echo json_encode(['status' => 'error', 'message' => $error_message]);
        return;
    }
    
    $uploaded_file = $_FILES['file_surat'];
    
    // Validasi tipe file
    $allowed_types = ['application/pdf'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $detected_type = finfo_file($file_info, $uploaded_file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($detected_type, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Hanya file PDF yang diizinkan']);
        return;
    }
    
    // Validasi ukuran file (max 2MB)
    if ($uploaded_file['size'] > 2048 * 1024) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 2MB']);
        return;
    }
    
    // Tentukan path upload - GUNAKAN YANG WORKING dari debug
    $upload_dir = FCPATH . 'uploads/surat_izin/';
    
    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal membuat folder upload']);
            return;
        }
    }
    
    // Generate nama file unik
    $file_extension = 'pdf'; // Sudah dipastikan PDF dari validasi di atas
    $filename = 'SURAT_IZIN_' . $permohonan->nim . '_' . date('YmdHis') . '.' . $file_extension;
    $destination_path = $upload_dir . $filename;
    
    // Upload file menggunakan native PHP
    if (move_uploaded_file($uploaded_file['tmp_name'], $destination_path)) {
        
        // Verifikasi file berhasil tersimpan
        if (!file_exists($destination_path)) {
            echo json_encode(['status' => 'error', 'message' => 'File gagal tersimpan']);
            return;
        }
        
        // Update database - kedua tabel sekaligus
        $this->db->trans_start();
        
        // Update tabel permohonan_izin_penelitian
        $this->db->where('proposal_mahasiswa_id', $proposal_id);
        $this->db->update('permohonan_izin_penelitian', [
            'file_surat_izin_staf' => $filename,
            'tanggal_upload_surat_staf' => date('Y-m-d H:i:s'),
            'uploaded_by_staf' => $this->session->userdata('id'),
            'keterangan_staf' => $this->input->post('keterangan') ?: null,
            'status' => 'surat_ready',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update tabel proposal_mahasiswa untuk compatibility
        $this->db->where('id', $proposal_id);
        $this->db->update('proposal_mahasiswa', [
            'surat_izin_penelitian' => $filename,
            'status_izin_penelitian' => '1' // 1 = disetujui/sudah ada
        ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            // Hapus file jika database gagal
            if (file_exists($destination_path)) {
                unlink($destination_path);
            }
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data ke database']);
        } else {
            // Log aktivitas
            $this->_log_aktivitas('upload_surat', $proposal_id, 
                                 "Upload surat izin penelitian untuk {$permohonan->nama_mahasiswa}");
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Surat izin berhasil diupload',
                'filename' => $filename,
                'file_size' => number_format($uploaded_file['size'] / 1024, 2) . ' KB'
            ]);
            // ✅ TAMBAHKAN BARIS INI:
            $this->send_upload_notification($proposal_id);
        }
        
    } else {
        // Cek penyebab gagal move_uploaded_file
        $error_details = error_get_last();
        $error_msg = 'Gagal memindahkan file';
        
        if ($error_details && strpos($error_details['message'], 'move_uploaded_file') !== false) {
            $error_msg .= ': ' . $error_details['message'];
        }
        
        // Additional checks
        if (!is_writable($upload_dir)) {
            $error_msg .= ' (Folder tidak writable)';
        }
        
        if (!is_uploaded_file($uploaded_file['tmp_name'])) {
            $error_msg .= ' (File bukan hasil upload yang valid)';
        }
        
        echo json_encode(['status' => 'error', 'message' => $error_msg]);
    }
}
   
    /**
     * Download surat izin penelitian
     */
    public function download_surat($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // Ambil data surat dari tabel permohonan_izin_penelitian
        $this->db->select('pip.file_surat_izin_staf, m.nim, m.nama');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
        
        $data = $this->db->get()->row();
        
        if (!$data || !$data->file_surat_izin_staf) {
            $this->session->set_flashdata('error', 'File surat tidak ditemukan');
            redirect('staf/penelitian');
            return;
        }
        
        $file_path = './uploads/surat_izin/' . $data->file_surat_izin_staf;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File fisik tidak ditemukan di server');
            redirect('staf/penelitian');
            return;
        }
        
        // Force download
        $this->load->helper('download');
        $filename = 'Surat_Izin_Penelitian_' . $data->nim . '_' . $data->nama . '.pdf';
        force_download($filename, file_get_contents($file_path));
    }
    
    /**
     * Download template surat izin penelitian kosong
     */
    public function download_template() {
        // Simple template response
        echo "<h1>Template Surat Izin Penelitian</h1>";
        echo "<p>Template untuk surat izin penelitian STK Santo Yakobus</p>";
        echo "<p>Generated: " . date('d F Y') . "</p>";
    }
    
    /**
     * Log aktivitas staf - FIXED QUERY
     */
    public function log_aktivitas($proposal_id) {
        if (!$proposal_id) {
            show_404();
        }
        
        // FIXED: Ambil log aktivitas dengan kolom yang benar
        if ($this->db->table_exists('staf_aktivitas')) {
            $this->db->select('sa.*, d.nama as nama_staf');
            $this->db->from('staf_aktivitas sa');
            $this->db->join('dosen d', 'sa.staf_id = d.id', 'left'); // Join dengan dosen level 5
            $this->db->where('sa.proposal_id', $proposal_id);
            $this->db->order_by('sa.tanggal_aktivitas', 'DESC'); // FIXED: tanggal_aktivitas bukan created_at
            
            $data['log_aktivitas'] = $this->db->get()->result();
        } else {
            $data['log_aktivitas'] = [];
        }
        
        // Info mahasiswa
        $this->db->select('m.nama, m.nim, pm.judul');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        
        $data['mahasiswa'] = $this->db->get()->row();
        
        $this->load->view('template/staf', [
            'title' => 'Log Aktivitas Penelitian',
            'content' => $this->load->view('staf/penelitian/log_aktivitas', $data, TRUE),
            'script' => ''
        ]);
    }

    /**
     * ✅ BARU: Kirim notifikasi email setelah surat izin diupload oleh staf
     * Method ini dipanggil SETELAH upload_surat() berhasil
     */
    public function send_upload_notification($proposal_id) {
        try {
            // Ambil data lengkap untuk notifikasi
            $this->db->select('
                pip.*, 
                pm.mahasiswa_id, 
                m.nama as nama_mahasiswa, 
                m.nim, 
                m.email as email_mahasiswa, 
                d.nama as nama_pembimbing, 
                d.email as email_pembimbing
            ');
            $this->db->from('permohonan_izin_penelitian pip');
            $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id');
            $this->db->where('pip.proposal_mahasiswa_id', $proposal_id);
            
            $permohonan = $this->db->get()->row();
            
            if (!$permohonan) {
                log_message('error', 'Data permohonan tidak ditemukan untuk notifikasi: ' . $proposal_id);
                return false;
            }
            
            // Kirim notifikasi
            $this->_send_notification_surat_ready($permohonan);
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error in send_upload_notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get statistik penelitian
     */
    private function _get_penelitian_stats() {
        // Total penelitian (workflow_status = penelitian atau lebih)
        $this->db->where_in('workflow_status', ['penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        $total_penelitian = $this->db->count_all_results('proposal_mahasiswa');
        
        // Butuh surat izin (yang sudah disetujui dosen tapi belum ada surat dari staf)
        $this->db->select('COUNT(*) as count');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('permohonan_izin_penelitian pip', 'pm.id = pip.proposal_mahasiswa_id', 'inner');
        $this->db->where('pip.status_pembimbing', 'approved');
        $this->db->where('(pip.file_surat_izin_staf IS NULL OR pip.file_surat_izin_staf = "")');
        $butuh_surat_result = $this->db->get()->row();
        $butuh_surat_izin = $butuh_surat_result ? $butuh_surat_result->count : 0;
        
        // Sudah ada surat
        $this->db->select('COUNT(*) as count');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('permohonan_izin_penelitian pip', 'pm.id = pip.proposal_mahasiswa_id', 'inner');
        $this->db->where('pip.file_surat_izin_staf IS NOT NULL');
        $this->db->where('pip.file_surat_izin_staf !=', '');
        $sudah_ada_surat_result = $this->db->get()->row();
        $sudah_ada_surat = $sudah_ada_surat_result ? $sudah_ada_surat_result->count : 0;
        
        // Bulan ini (pengajuan baru)
        $this->db->select('COUNT(*) as count');
        $this->db->from('permohonan_izin_penelitian');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $bulan_ini_result = $this->db->get()->row();
        $bulan_ini = $bulan_ini_result ? $bulan_ini_result->count : 0;
        
        return [
            'total_penelitian' => $total_penelitian,
            'butuh_surat_izin' => $butuh_surat_izin,
            'sudah_ada_surat' => $sudah_ada_surat,
            'bulan_ini' => $bulan_ini
        ];
    }
    
    /**
     * Generate nomor surat otomatis
     */
    private function _generate_nomor_surat() {
        $year = date('Y');
        $month = date('m');
        
        // Hitung urutan surat di bulan ini
        $this->db->like('created_at', $year . '-' . $month, 'after');
        $count = $this->db->count_all_results('permohonan_izin_penelitian') + 1;
        
        return sprintf('%03d/STK-SY/SURAT-IZIN/%s/%s', $count, $month, $year);
    }
    
    /**
     * FIXED: Log aktivitas dengan kolom yang benar
     */
    private function _log_aktivitas($aktivitas, $proposal_id, $keterangan) {
        // Cek apakah tabel staf_aktivitas ada
        if ($this->db->table_exists('staf_aktivitas')) {
            $data = [
                'aktivitas' => $aktivitas, // FIXED: gunakan 'aktivitas' bukan 'jenis_aktivitas'
                'staf_id' => $this->session->userdata('id'), // ID dari tabel dosen dengan level 5
                'proposal_id' => $proposal_id,
                'keterangan' => $keterangan,
                'tanggal_aktivitas' => date('Y-m-d H:i:s') // FIXED: gunakan 'tanggal_aktivitas' bukan 'created_at'
            ];
            
            $this->db->insert('staf_aktivitas', $data);
        }
    }
    
    /**
     * ✅ BARU: Method utama untuk kirim notifikasi email
     */
    private function _send_notification_surat_ready($permohonan) {
        try {
            // Load email library jika belum ada
            if (!isset($this->email)) {
                $this->load->library('email');
            }
            
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            // 1. Kirim email ke MAHASISWA
            $email_mahasiswa_sent = $this->_send_email_to_mahasiswa($permohonan);
            
            // 2. Kirim email ke DOSEN PEMBIMBING  
            $email_dosen_sent = $this->_send_email_to_dosen($permohonan);
            
            // Log hasil
            if ($email_mahasiswa_sent && $email_dosen_sent) {
                log_message('info', 'All upload notifications sent successfully for proposal: ' . $permohonan->proposal_mahasiswa_id);
            } else {
                log_message('warning', 'Some upload notifications failed for proposal: ' . $permohonan->proposal_mahasiswa_id);
            }
            
            return ($email_mahasiswa_sent && $email_dosen_sent);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending surat ready notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Kirim email notifikasi ke mahasiswa
     */
    private function _send_email_to_mahasiswa($permohonan) {
        try {
            $subject = 'Surat Izin Penelitian Siap - ' . $permohonan->nama_mahasiswa;
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Surat Izin Penelitian Siap</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$permohonan->nama_mahasiswa}</strong>,</p>
                    
                    <p>Surat izin penelitian Anda telah <strong>siap dan telah ditandatangani</strong> oleh pihak akademik.</p>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 15px 0;'>
                        <p style='margin: 0; color: #155724;'><strong>Detail Penelitian:</strong></p>
                        <ul style='color: #155724; margin: 10px 0;'>
                            <li><strong>NIM:</strong> {$permohonan->nim}</li>
                            <li><strong>Tempat:</strong> {$permohonan->tempat_penelitian}</li>
                            <li><strong>Periode:</strong> " . date('d-m-Y', strtotime($permohonan->tanggal_mulai_penelitian)) . " s/d " . date('d-m-Y', strtotime($permohonan->tanggal_selesai_penelitian)) . "</li>
                            <li><strong>Pembimbing:</strong> {$permohonan->nama_pembimbing}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                        <p style='margin: 0; color: #856404;'><strong>Langkah Selanjutnya:</strong></p>
                        <p style='margin: 5px 0 0 0; color: #856404;'>
                            1. Login ke sistem untuk mengunduh surat izin penelitian<br>
                            2. Gunakan surat tersebut untuk keperluan penelitian Anda<br>
                            3. Lanjutkan ke tahap penelitian dan dokumentasi
                        </p>
                    </div>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0;'>SIM Tugas Akhir STK Santo Yakobus</p>
                    <p style='margin: 5px 0 0 0;'>Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($permohonan->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Surat ready notification sent to mahasiswa: ' . $permohonan->email_mahasiswa);
            } else {
                log_message('error', 'Failed to send surat ready notification to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exception sending email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Kirim email notifikasi ke dosen pembimbing
     */
    private function _send_email_to_dosen($permohonan) {
        try {
            $subject = 'Surat Izin Penelitian Telah Diproses - ' . $permohonan->nama_mahasiswa;
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📋 Surat Izin Penelitian Diproses</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$permohonan->nama_pembimbing}</strong>,</p>
                    
                    <p>Surat izin penelitian untuk mahasiswa bimbingan Anda telah <strong>siap dan ditandatangani</strong> oleh staf akademik.</p>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #0c5460; margin: 15px 0;'>
                        <p style='margin: 0; color: #0c5460;'><strong>Detail Mahasiswa:</strong></p>
                        <ul style='color: #0c5460; margin: 10px 0;'>
                            <li><strong>Nama:</strong> {$permohonan->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$permohonan->nim}</li>
                            <li><strong>Tempat Penelitian:</strong> {$permohonan->tempat_penelitian}</li>
                            <li><strong>Periode:</strong> " . date('d-m-Y', strtotime($permohonan->tanggal_mulai_penelitian)) . " s/d " . date('d-m-Y', strtotime($permohonan->tanggal_selesai_penelitian)) . "</li>
                        </ul>
                    </div>
                    
                    <p style='color: #6c757d;'>Mahasiswa dapat melanjutkan ke tahap penelitian lapangan dengan surat izin yang telah disetujui.</p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0;'>SIM Tugas Akhir STK Santo Yakobus</p>
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($permohonan->email_pembimbing);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Surat ready notification sent to dosen: ' . $permohonan->email_pembimbing);
            } else {
                log_message('error', 'Failed to send surat ready notification to dosen: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exception sending email to dosen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BARU: Konfigurasi email - sama dengan controller dosen yang sudah stabil
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
            'smtp_timeout' => 30,
            'wordwrap' => TRUE
        ];
    }
}