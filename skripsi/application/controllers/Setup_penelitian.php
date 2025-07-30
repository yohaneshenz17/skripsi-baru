<?php
/**
 * Setup Script untuk Tahap 4 Penelitian
 * File: application/controllers/Setup_penelitian.php
 * 
 * Controller untuk setup otomatis database, direktori, dan validasi sistem
 * Jalankan sekali setelah mengupload semua file implementasi
 * 
 * Akses: /setup_penelitian
 * PENTING: Hapus file ini setelah setup selesai untuk keamanan
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Setup_penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['file', 'directory']);
        
        // SECURITY: Hanya allow di development atau untuk admin
        if (ENVIRONMENT !== 'development' && $this->session->userdata('level') != '1') {
            show_404();
        }
    }

    /**
     * Main setup page
     */
    public function index() {
        echo $this->_render_header();
        
        $setup_results = [
            'database' => $this->_setup_database(),
            'directories' => $this->_setup_directories(), 
            'permissions' => $this->_check_permissions(),
            'validation' => $this->_validate_system()
        ];
        
        echo $this->_render_results($setup_results);
        echo $this->_render_footer();
    }

    /**
     * Setup database - Cek dan buat tabel jika perlu
     */
    private function _setup_database() {
        $results = [];
        
        try {
            // 1. Cek tabel permohonan_izin_penelitian
            if (!$this->db->table_exists('permohonan_izin_penelitian')) {
                $sql = "
                CREATE TABLE `permohonan_izin_penelitian` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `proposal_mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa',
                  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa untuk redundancy check',
                  `nama_mahasiswa` varchar(100) NOT NULL COMMENT 'Nama mahasiswa (input manual, huruf kapital)',
                  `nim` varchar(20) NOT NULL COMMENT 'NIM mahasiswa (input manual)',
                  `semester` varchar(10) NOT NULL COMMENT 'Semester (VII, VIII, IX)',
                  `program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar') NOT NULL COMMENT 'Program studi',
                  `judul_skripsi_terbaru` text NOT NULL COMMENT 'Judul skripsi terbaru setelah seminar proposal',
                  `tempat_penelitian` varchar(255) NOT NULL COMMENT 'Lokasi penelitian (wilayah gerejawi, instansi, dll)',
                  `tanggal_mulai_penelitian` date NOT NULL COMMENT 'Tanggal mulai penelitian',
                  `tanggal_selesai_penelitian` date NOT NULL COMMENT 'Tanggal selesai penelitian',
                  `dosen_pembimbing_id` bigint(20) NOT NULL COMMENT 'FK ke dosen pembimbing',
                  `file_proposal_revisi` varchar(255) NOT NULL COMMENT 'File proposal yang sudah direvisi (PDF/Word, max 2MB)',
                  `status` enum('draft','submitted','review_pembimbing','approved','rejected','surat_ready','completed') DEFAULT 'draft' COMMENT 'Status workflow permohonan',
                  `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Status review dosen pembimbing',
                  `komentar_pembimbing` text DEFAULT NULL COMMENT 'Komentar dosen pembimbing',
                  `tanggal_review_pembimbing` datetime DEFAULT NULL COMMENT 'Tanggal review pembimbing',
                  `file_surat_izin_staf` varchar(255) DEFAULT NULL COMMENT 'File surat izin yang diupload staf (PDF, max 1MB)',
                  `tanggal_upload_surat_staf` datetime DEFAULT NULL COMMENT 'Tanggal staf upload surat',
                  `uploaded_by_staf` bigint(20) DEFAULT NULL COMMENT 'ID staf yang upload surat',
                  `keterangan_staf` text DEFAULT NULL COMMENT 'Keterangan dari staf',
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal pengajuan',
                  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `unique_proposal_permohonan` (`proposal_mahasiswa_id`),
                  KEY `idx_status` (`status`),
                  KEY `idx_pembimbing` (`dosen_pembimbing_id`),
                  KEY `idx_status_pembimbing` (`status_pembimbing`),
                  KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permohonan izin penelitian - tidak mengubah tabel existing';
                ";
                
                if ($this->db->query($sql)) {
                    $results[] = ['status' => 'success', 'message' => 'Tabel permohonan_izin_penelitian berhasil dibuat'];
                } else {
                    $results[] = ['status' => 'error', 'message' => 'Gagal membuat tabel permohonan_izin_penelitian'];
                }
            } else {
                $results[] = ['status' => 'info', 'message' => 'Tabel permohonan_izin_penelitian sudah ada'];
            }

            // 2. Cek tabel log_penelitian (optional)
            if (!$this->db->table_exists('log_penelitian')) {
                $sql = "
                CREATE TABLE `log_penelitian` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `permohonan_id` bigint(20) NOT NULL,
                  `user_id` bigint(20) NOT NULL,
                  `user_role` enum('mahasiswa','dosen','staf','kaprodi','admin') NOT NULL,
                  `aktivitas` varchar(100) NOT NULL,
                  `deskripsi` text NOT NULL,
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_permohonan` (`permohonan_id`),
                  KEY `idx_user` (`user_id`),
                  KEY `idx_aktivitas` (`aktivitas`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ";
                
                if ($this->db->query($sql)) {
                    $results[] = ['status' => 'success', 'message' => 'Tabel log_penelitian berhasil dibuat'];
                } else {
                    $results[] = ['status' => 'warning', 'message' => 'Tabel log_penelitian gagal dibuat (optional)'];
                }
            } else {
                $results[] = ['status' => 'info', 'message' => 'Tabel log_penelitian sudah ada'];
            }

            // 3. Cek view v_penelitian_dashboard
            $sql = "
            CREATE OR REPLACE VIEW `v_penelitian_dashboard` AS
            SELECT 
                pip.id as permohonan_id,
                pip.proposal_mahasiswa_id,
                pip.nim,
                pip.nama_mahasiswa,
                pip.semester,
                pip.program_studi,
                pip.judul_skripsi_terbaru,
                pip.tempat_penelitian,
                pip.tanggal_mulai_penelitian,
                pip.tanggal_selesai_penelitian,
                pip.status,
                pip.status_pembimbing,
                pip.created_at as tanggal_pengajuan,
                pip.tanggal_review_pembimbing,
                pip.tanggal_upload_surat_staf,
                d.nama as nama_pembimbing,
                d.nip as nip_pembimbing,
                d.email as email_pembimbing,
                pm.workflow_status,
                pm.status_izin_penelitian,
                pm.surat_izin_penelitian,
                CASE pip.status
                    WHEN 'draft' THEN 'Draft Permohonan'
                    WHEN 'submitted' THEN 'Menunggu Review Pembimbing'
                    WHEN 'review_pembimbing' THEN 'Sedang Direview Pembimbing'
                    WHEN 'approved' THEN 'Disetujui Pembimbing - Menunggu Staf'
                    WHEN 'rejected' THEN 'Ditolak Pembimbing'
                    WHEN 'surat_ready' THEN 'Surat Siap - Menunggu Download'
                    WHEN 'completed' THEN 'Selesai'
                    ELSE 'Status Tidak Dikenal'
                END as status_description,
                CASE pip.status
                    WHEN 'draft' THEN 10
                    WHEN 'submitted' THEN 25
                    WHEN 'review_pembimbing' THEN 40
                    WHEN 'approved' THEN 60
                    WHEN 'rejected' THEN 0
                    WHEN 'surat_ready' THEN 80
                    WHEN 'completed' THEN 100
                    ELSE 0
                END as progress_percentage
            FROM permohonan_izin_penelitian pip
            LEFT JOIN dosen d ON pip.dosen_pembimbing_id = d.id
            LEFT JOIN proposal_mahasiswa pm ON pip.proposal_mahasiswa_id = pm.id;
            ";
            
            if ($this->db->query($sql)) {
                $results[] = ['status' => 'success', 'message' => 'View v_penelitian_dashboard berhasil dibuat/diupdate'];
            } else {
                $results[] = ['status' => 'warning', 'message' => 'View v_penelitian_dashboard gagal dibuat'];
            }

            // 4. Validasi tabel existing yang diperlukan
            $required_tables = ['proposal_mahasiswa', 'mahasiswa', 'dosen', 'jurnal_bimbingan', 'seminar_proposal_mahasiswa', 'penilaian_seminar_proposal'];
            foreach ($required_tables as $table) {
                if ($this->db->table_exists($table)) {
                    $results[] = ['status' => 'success', 'message' => "Tabel {$table} tersedia ✓"];
                } else {
                    $results[] = ['status' => 'error', 'message' => "Tabel {$table} TIDAK DITEMUKAN! Sistem tidak akan berfungsi"];
                }
            }

        } catch (Exception $e) {
            $results[] = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }

        return $results;
    }

    /**
     * Setup direktori upload
     */
    private function _setup_directories() {
        $results = [];
        
        $directories = [
            FCPATH . 'uploads/',
            FCPATH . 'uploads/penelitian/',
            FCPATH . 'uploads/penelitian/proposal_revisi/',
            FCPATH . 'uploads/penelitian/surat_izin/',
            FCPATH . 'uploads/seminar_proposal/',
            FCPATH . 'uploads/seminar_proposal/proposal_files/'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    $results[] = ['status' => 'success', 'message' => "Direktori {$dir} berhasil dibuat"];
                } else {
                    $results[] = ['status' => 'error', 'message' => "Gagal membuat direktori {$dir}"];
                }
            } else {
                $results[] = ['status' => 'info', 'message' => "Direktori {$dir} sudah ada"];
            }
        }

        // Buat .htaccess untuk keamanan upload
        $htaccess_content = "Options -Indexes\nDeny from all\n<Files ~ \"\\.(pdf|doc|docx)$\">\nAllow from all\n</Files>";
        $upload_dirs = [
            FCPATH . 'uploads/penelitian/proposal_revisi/',
            FCPATH . 'uploads/penelitian/surat_izin/'
        ];

        foreach ($upload_dirs as $dir) {
            $htaccess_file = $dir . '.htaccess';
            if (!file_exists($htaccess_file)) {
                if (file_put_contents($htaccess_file, $htaccess_content)) {
                    $results[] = ['status' => 'success', 'message' => "File .htaccess dibuat di {$dir}"];
                } else {
                    $results[] = ['status' => 'warning', 'message' => "Gagal membuat .htaccess di {$dir}"];
                }
            }
        }

        return $results;
    }

    /**
     * Cek permissions direktori
     */
    private function _check_permissions() {
        $results = [];
        
        $directories = [
            FCPATH . 'uploads/penelitian/',
            FCPATH . 'uploads/penelitian/proposal_revisi/',
            FCPATH . 'uploads/penelitian/surat_izin/'
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    $results[] = ['status' => 'success', 'message' => "Direktori {$dir} writable ✓"];
                } else {
                    $results[] = ['status' => 'error', 'message' => "Direktori {$dir} TIDAK writable! Perlu chmod 755"];
                }
            }
        }

        // Cek PHP upload settings
        $upload_max = ini_get('upload_max_filesize');
        $post_max = ini_get('post_max_size');
        $memory_limit = ini_get('memory_limit');

        $results[] = ['status' => 'info', 'message' => "PHP upload_max_filesize: {$upload_max}"];
        $results[] = ['status' => 'info', 'message' => "PHP post_max_size: {$post_max}"];
        $results[] = ['status' => 'info', 'message' => "PHP memory_limit: {$memory_limit}"];

        // Rekomendasikan setting minimal
        $upload_bytes = $this->_parse_size($upload_max);
        if ($upload_bytes < 2 * 1024 * 1024) { // 2MB
            $results[] = ['status' => 'warning', 'message' => 'Rekomendasi: upload_max_filesize minimal 2M'];
        }

        return $results;
    }

    /**
     * Validasi sistem secara keseluruhan
     */
    private function _validate_system() {
        $results = [];

        try {
            // 1. Test database connection
            if ($this->db->initialize()) {
                $results[] = ['status' => 'success', 'message' => 'Koneksi database OK'];
            } else {
                $results[] = ['status' => 'error', 'message' => 'Koneksi database GAGAL'];
                return $results;
            }

            // 2. Cek data sample untuk testing
            $mahasiswa_count = $this->db->count_all('mahasiswa');
            $dosen_count = $this->db->where('level', '2')->count_all_results('dosen');
            $proposal_count = $this->db->count_all('proposal_mahasiswa');

            $results[] = ['status' => 'info', 'message' => "Data mahasiswa: {$mahasiswa_count} records"];
            $results[] = ['status' => 'info', 'message' => "Data dosen: {$dosen_count} records"];
            $results[] = ['status' => 'info', 'message' => "Data proposal: {$proposal_count} records"];

            if ($mahasiswa_count == 0) {
                $results[] = ['status' => 'warning', 'message' => 'Tidak ada data mahasiswa untuk testing'];
            }

            if ($dosen_count == 0) {
                $results[] = ['status' => 'warning', 'message' => 'Tidak ada data dosen untuk testing'];
            }

            // 3. Test model loading
            $this->load->model('Penelitian_model', 'penelitian_test');
            $results[] = ['status' => 'success', 'message' => 'Model Penelitian_model berhasil dimuat'];

            // 4. Test sample eligibility check (jika ada data)
            if ($proposal_count > 0) {
                $sample_proposal = $this->db->select('id, mahasiswa_id')->get('proposal_mahasiswa', 1)->row();
                if ($sample_proposal) {
                    $eligibility = $this->penelitian_test->check_eligibility($sample_proposal->id, $sample_proposal->mahasiswa_id);
                    if (!$eligibility['error']) {
                        $results[] = ['status' => 'success', 'message' => 'Test eligibility check berhasil'];
                    } else {
                        $results[] = ['status' => 'warning', 'message' => 'Test eligibility: ' . $eligibility['message']];
                    }
                }
            }

            // 5. Cek controller files
            $controller_files = [
                APPPATH . 'controllers/mahasiswa/Penelitian.php'
            ];

            foreach ($controller_files as $file) {
                if (file_exists($file)) {
                    $results[] = ['status' => 'success', 'message' => 'Controller ' . basename($file) . ' tersedia'];
                } else {
                    $results[] = ['status' => 'error', 'message' => 'Controller ' . basename($file) . ' TIDAK DITEMUKAN'];
                }
            }

            // 6. Cek view files
            $view_files = [
                APPPATH . 'views/mahasiswa/penelitian/index.php',
                APPPATH . 'views/mahasiswa/penelitian/form_ajukan.php',
                APPPATH . 'views/mahasiswa/penelitian/detail.php'
            ];

            foreach ($view_files as $file) {
                if (file_exists($file)) {
                    $results[] = ['status' => 'success', 'message' => 'View ' . basename($file) . ' tersedia'];
                } else {
                    $results[] = ['status' => 'error', 'message' => 'View ' . basename($file) . ' TIDAK DITEMUKAN'];
                }
            }

        } catch (Exception $e) {
            $results[] = ['status' => 'error', 'message' => 'Validation error: ' . $e->getMessage()];
        }

        return $results;
    }

    /**
     * Parse file size string to bytes
     */
    private function _parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * Render HTML header
     */
    private function _render_header() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setup Tahap 4 Penelitian - SIM TA STK Santo Yakobus</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>
                                    Setup Tahap 4 Penelitian
                                </h4>
                                <p class="mb-0">Sistem Informasi Manajemen Tugas Akhir - STK Santo Yakobus</p>
                            </div>
                            <div class="card-body">
        ';
    }

    /**
     * Render results
     */
    private function _render_results($setup_results) {
        $html = '';
        
        foreach ($setup_results as $category => $results) {
            $html .= '<div class="mb-4">';
            $html .= '<h5 class="text-primary border-bottom pb-2">';
            
            switch ($category) {
                case 'database':
                    $html .= '<i class="fas fa-database me-2"></i>Database Setup';
                    break;
                case 'directories':
                    $html .= '<i class="fas fa-folder me-2"></i>Directory Setup';
                    break;
                case 'permissions':
                    $html .= '<i class="fas fa-shield-alt me-2"></i>Permissions Check';
                    break;
                case 'validation':
                    $html .= '<i class="fas fa-check-circle me-2"></i>System Validation';
                    break;
            }
            
            $html .= '</h5>';
            
            foreach ($results as $result) {
                $icon = '';
                $class = '';
                
                switch ($result['status']) {
                    case 'success':
                        $icon = 'fas fa-check-circle text-success';
                        $class = 'alert-success';
                        break;
                    case 'error':
                        $icon = 'fas fa-times-circle text-danger';
                        $class = 'alert-danger';
                        break;
                    case 'warning':
                        $icon = 'fas fa-exclamation-triangle text-warning';
                        $class = 'alert-warning';
                        break;
                    case 'info':
                        $icon = 'fas fa-info-circle text-info';
                        $class = 'alert-info';
                        break;
                }
                
                $html .= "<div class='alert {$class} py-2'>";
                $html .= "<i class='{$icon} me-2'></i>";
                $html .= htmlspecialchars($result['message']);
                $html .= "</div>";
            }
            
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Render HTML footer
     */
    private function _render_footer() {
        return '
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Langkah Selanjutnya:</h6>
                                    <ol class="mb-0">
                                        <li>Pastikan semua status di atas menunjukkan SUCCESS atau INFO</li>
                                        <li>Jika ada ERROR, perbaiki terlebih dahulu</li>
                                        <li>Test akses: <a href="' . base_url('mahasiswa/penelitian') . '" target="_blank">' . base_url('mahasiswa/penelitian') . '</a></li>
                                        <li>Login sebagai mahasiswa dan test workflow tahap 4</li>
                                        <li><strong>HAPUS file ini (/setup_penelitian) setelah setup selesai</strong></li>
                                    </ol>
                                </div>
                                
                                <div class="text-center">
                                    <a href="' . base_url() . '" class="btn btn-primary">
                                        <i class="fas fa-home me-2"></i>
                                        Kembali ke Dashboard
                                    </a>
                                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                                        <i class="fas fa-redo me-2"></i>
                                        Jalankan Ulang Setup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>';
    }
}

/* End of file Setup_penelitian.php */
/* Location: ./application/controllers/Setup_penelitian.php */