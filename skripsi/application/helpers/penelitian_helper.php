<?php
/**
 * File: application/helpers/penelitian_helper.php
 * Helper functions untuk badge counter dan utilities penelitian dosen
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Get badge count untuk menu penelitian di sidebar
 * Menampilkan jumlah permohonan yang perlu direview dosen
 */
if (!function_exists('get_penelitian_badge_count')) {
    function get_penelitian_badge_count() {
        $CI =& get_instance();
        
        // Pastikan dosen login
        if (!$CI->session->userdata('logged_in') || $CI->session->userdata('level') != '2') {
            return 0;
        }
        
        $dosen_id = $CI->session->userdata('id');
        
        try {
            // Load database jika belum ada
            if (!isset($CI->db)) {
                $CI->load->database();
            }
            
            // Hitung permohonan yang perlu review oleh dosen ini
            $CI->db->where('dosen_pembimbing_id', $dosen_id);
            $CI->db->where('status_pembimbing', 'pending');
            $CI->db->where('status', 'submitted');
            $count = $CI->db->count_all_results('permohonan_izin_penelitian');
            
            return (int) $count;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_penelitian_badge_count: ' . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Get status badge untuk permohonan penelitian
 */
if (!function_exists('get_penelitian_status_badge')) {
    function get_penelitian_status_badge($status_pembimbing, $status = '') {
        switch($status_pembimbing) {
            case 'pending':
                return '<span class="badge badge-warning">Menunggu Review</span>';
            case 'approved':
                return '<span class="badge badge-success">Disetujui</span>';
            case 'rejected':
                return '<span class="badge badge-danger">Ditolak</span>';
            default:
                return '<span class="badge badge-secondary">Status Tidak Dikenal</span>';
        }
    }
}

/**
 * Format tanggal untuk display
 */
if (!function_exists('format_tanggal_penelitian')) {
    function format_tanggal_penelitian($tanggal, $format = 'd F Y') {
        if (empty($tanggal) || $tanggal == '0000-00-00' || $tanggal == '0000-00-00 00:00:00') {
            return 'Tidak ada data';
        }
        
        return date($format, strtotime($tanggal));
    }
}

/**
 * Get workflow status description
 */
if (!function_exists('get_penelitian_workflow_description')) {
    function get_penelitian_workflow_description($status) {
        $descriptions = [
            'draft' => 'Draft Permohonan',
            'submitted' => 'Menunggu Review Pembimbing',
            'review_pembimbing' => 'Sedang Direview Pembimbing',
            'approved' => 'Disetujui Pembimbing - Menunggu Staf',
            'rejected' => 'Ditolak Pembimbing',
            'surat_ready' => 'Surat Siap - Menunggu Download',
            'completed' => 'Selesai'
        ];
        
        return isset($descriptions[$status]) ? $descriptions[$status] : 'Status Tidak Dikenal';
    }
}

/**
 * Get progress percentage untuk workflow
 */
if (!function_exists('get_penelitian_progress_percentage')) {
    function get_penelitian_progress_percentage($status) {
        $progress = [
            'draft' => 10,
            'submitted' => 25,
            'review_pembimbing' => 40,
            'approved' => 60,
            'rejected' => 0,
            'surat_ready' => 80,
            'completed' => 100
        ];
        
        return isset($progress[$status]) ? $progress[$status] : 0;
    }
}

/**
 * Check if file exists and get download URL
 */
if (!function_exists('get_penelitian_file_url')) {
    function get_penelitian_file_url($filename, $type = 'proposal_revisi') {
        if (empty($filename)) {
            return null;
        }
        
        $upload_paths = [
            'proposal_revisi' => 'uploads/penelitian/proposal_revisi/',
            'surat_izin' => 'uploads/penelitian/surat_izin/'
        ];
        
        $path = isset($upload_paths[$type]) ? $upload_paths[$type] : 'uploads/penelitian/';
        $file_path = FCPATH . $path . $filename;
        
        if (file_exists($file_path)) {
            return base_url($path . $filename);
        }
        
        return null;
    }
}

/**
 * Truncate text with ellipsis
 */
if (!function_exists('truncate_text_penelitian')) {
    function truncate_text_penelitian($text, $length = 50, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Get notification count untuk dosen
 * Termasuk semua notifikasi penelitian yang belum dibaca
 */
if (!function_exists('get_penelitian_notification_count')) {
    function get_penelitian_notification_count() {
        $CI =& get_instance();
        
        // Pastikan dosen login
        if (!$CI->session->userdata('logged_in') || $CI->session->userdata('level') != '2') {
            return 0;
        }
        
        $dosen_id = $CI->session->userdata('id');
        
        try {
            // Load database jika belum ada
            if (!isset($CI->db)) {
                $CI->load->database();
            }
            
            $total_notifications = 0;
            
            // 1. Permohonan penelitian yang perlu review
            $CI->db->where('dosen_pembimbing_id', $dosen_id);
            $CI->db->where('status_pembimbing', 'pending');
            $CI->db->where('status', 'submitted');
            $penelitian_count = $CI->db->count_all_results('permohonan_izin_penelitian');
            
            $total_notifications += $penelitian_count;
            
            // 2. Update status dari staf (jika ada notifikasi tabel khusus)
            // Bisa ditambahkan sesuai kebutuhan
            
            return (int) $total_notifications;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_penelitian_notification_count: ' . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Generate email template untuk notifikasi penelitian
 */
if (!function_exists('get_penelitian_email_template')) {
    function get_penelitian_email_template($type, $data) {
        $templates = [
            'approved_to_staf' => [
                'subject' => 'Permohonan Izin Penelitian Disetujui - ' . $data['nama_mahasiswa'],
                'body' => "
                <h3>Permohonan Izin Penelitian Disetujui</h3>
                <p>Dosen pembimbing telah menyetujui permohonan izin penelitian:</p>
                <ul>
                    <li><strong>Mahasiswa:</strong> {$data['nama_mahasiswa']} ({$data['nim']})</li>
                    <li><strong>Judul:</strong> {$data['judul_skripsi_terbaru']}</li>
                    <li><strong>Tempat:</strong> {$data['tempat_penelitian']}</li>
                    <li><strong>Periode:</strong> {$data['tanggal_mulai_penelitian']} s/d {$data['tanggal_selesai_penelitian']}</li>
                </ul>
                <p>Silakan proses surat izin penelitian melalui sistem.</p>
                "
            ],
            'rejected_to_mahasiswa' => [
                'subject' => 'Permohonan Izin Penelitian Perlu Perbaikan - ' . $data['nama_mahasiswa'],
                'body' => "
                <h3>Permohonan Izin Penelitian Perlu Perbaikan</h3>
                <p>Dosen pembimbing memberikan catatan untuk perbaikan:</p>
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0;'>
                    <strong>Catatan Dosen:</strong><br>
                    {$data['komentar_pembimbing']}
                </div>
                <p>Silakan perbaiki dan ajukan kembali melalui sistem.</p>
                "
            ]
        ];
        
        return isset($templates[$type]) ? $templates[$type] : null;
    }
}

/**
 * Validate file upload untuk penelitian
 */
if (!function_exists('validate_penelitian_file')) {
    function validate_penelitian_file($file_path, $type = 'proposal') {
        if (!file_exists($file_path)) {
            return ['valid' => false, 'message' => 'File tidak ditemukan'];
        }
        
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        $file_size = filesize($file_path);
        
        // Validasi ekstensi
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        if (!in_array($extension, $allowed_extensions)) {
            return ['valid' => false, 'message' => 'Format file tidak diizinkan'];
        }
        
        // Validasi ukuran (max 2MB untuk proposal, 1MB untuk surat)
        $max_size = $type === 'proposal' ? 2097152 : 1048576; // 2MB : 1MB
        if ($file_size > $max_size) {
            $max_mb = $type === 'proposal' ? '2MB' : '1MB';
            return ['valid' => false, 'message' => "Ukuran file maksimal {$max_mb}"];
        }
        
        return ['valid' => true, 'message' => 'File valid'];
    }
}

/**
 * Log aktivitas penelitian
 */
if (!function_exists('log_penelitian_activity')) {
    function log_penelitian_activity($permohonan_id, $user_id, $user_role, $aktivitas, $deskripsi) {
        $CI =& get_instance();
        
        try {
            // Load database jika belum ada
            if (!isset($CI->db)) {
                $CI->load->database();
            }
            
            $log_data = [
                'permohonan_id' => $permohonan_id,
                'user_id' => $user_id,
                'user_role' => $user_role,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $CI->db->insert('log_penelitian', $log_data);
            
        } catch (Exception $e) {
            log_message('error', 'Error in log_penelitian_activity: ' . $e->getMessage());
            return false;
        }
    }
}

// Auto-load helper jika diperlukan
$CI =& get_instance();
if (method_exists($CI, 'load')) {
    $CI->load->helper('text'); // Untuk character_limiter
}