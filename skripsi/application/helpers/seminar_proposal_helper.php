<?php
// =================================================================
// File: application/helpers/seminar_proposal_helper.php
// =================================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Helper
 * 
 * Helper functions untuk mengelola seminar proposal
 * 
 * @package     SIM_TA
 * @subpackage  Helpers  
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0
 */

if (!function_exists('validate_file_upload')) {
    /**
     * Validasi file upload untuk seminar proposal
     * 
     * @param array $file $_FILES data
     * @param int $max_size_mb Maximum size in MB
     * @return array
     */
    function validate_file_upload($file, $max_size_mb = 1)
    {
        $allowed_types = [
            'application/pdf',
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $max_size_bytes = $max_size_mb * 1024 * 1024;
        
        // Cek apakah file ada
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return [
                'valid' => false,
                'message' => 'File tidak ditemukan atau tidak berhasil diupload.'
            ];
        }
        
        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi batas server).',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi batas form).',
                UPLOAD_ERR_PARTIAL => 'File hanya sebagian yang terupload.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi.'
            ];
            
            $message = $error_messages[$file['error']] ?? 'Error tidak dikenal dalam upload file.';
            return [
                'valid' => false,
                'message' => $message
            ];
        }
        
        // Cek ukuran file
        if ($file['size'] > $max_size_bytes) {
            return [
                'valid' => false,
                'message' => "File terlalu besar. Maksimal {$max_size_mb}MB diperbolehkan."
            ];
        }
        
        // Cek ekstensi file
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            return [
                'valid' => false,
                'message' => 'Format file tidak diperbolehkan. Hanya PDF, DOC, atau DOCX yang diizinkan.'
            ];
        }
        
        // Cek MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return [
                'valid' => false,
                'message' => 'Tipe file tidak valid. Pastikan file adalah PDF, DOC, atau DOCX yang asli.'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'File valid untuk diupload.',
            'file_info' => [
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $mime_type,
                'extension' => $file_extension
            ]
        ];
    }
}

if (!function_exists('basic_malware_scan')) {
    /**
     * Basic malware scan untuk file upload
     * 
     * @param string $file_path Path to uploaded file
     * @return bool
     */
    function basic_malware_scan($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Baca beberapa bytes pertama untuk cek signature
        $file_content = file_get_contents($file_path, false, null, 0, 1024);
        
        // Suspicious patterns (basic detection)
        $malware_signatures = [
            '<?php',        // PHP code
            '<script',      // JavaScript
            'eval(',        // eval function
            'base64_decode', // base64 decode
            'exec(',        // exec function
            'system(',      // system function
            'shell_exec',   // shell exec
            'passthru',     // passthru function
            'file_get_contents', // file_get_contents
            'curl_exec',    // curl exec
            'fsockopen',    // socket functions
            'pfsockopen',   // persistent socket
        ];
        
        foreach ($malware_signatures as $signature) {
            if (stripos($file_content, $signature) !== false) {
                return false; // Suspicious content detected
            }
        }
        
        return true; // Basic scan passed
    }
}

if (!function_exists('get_workflow_steps')) {
    /**
     * Get workflow steps untuk progress UI
     * 
     * @param string $current_status
     * @return array
     */
    function get_workflow_steps($current_status = 'draft')
    {
        $steps = [
            [
                'key' => 'pengajuan',
                'title' => 'Pengajuan',
                'icon' => 'ni-send',
                'description' => 'Upload proposal dan submit',
                'completed' => in_array($current_status, ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => $current_status == 'draft'
            ],
            [
                'key' => 'review_pembimbing', 
                'title' => 'Review Pembimbing',
                'icon' => 'ni-single-02',
                'description' => 'Rekomendasi dosen pembimbing',
                'completed' => in_array($current_status, ['review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => in_array($current_status, ['submitted', 'review_pembimbing'])
            ],
            [
                'key' => 'review_kaprodi',
                'title' => 'Review Kaprodi', 
                'icon' => 'ni-badge',
                'description' => 'Validasi dan cek plagiarisme',
                'completed' => in_array($current_status, ['approved', 'scheduled', 'completed']),
                'active' => $current_status == 'review_kaprodi'
            ],
            [
                'key' => 'penjadwalan',
                'title' => 'Penjadwalan',
                'icon' => 'ni-calendar-grid-58', 
                'description' => 'Jadwal dan penunjukan penguji',
                'completed' => in_array($current_status, ['scheduled', 'completed']),
                'active' => $current_status == 'approved'
            ],
            [
                'key' => 'pelaksanaan',
                'title' => 'Pelaksanaan',
                'icon' => 'ni-trophy',
                'description' => 'Seminar proposal',
                'completed' => $current_status == 'completed',
                'active' => $current_status == 'scheduled'
            ]
        ];
        
        return $steps;
    }
}

if (!function_exists('format_status_badge')) {
    /**
     * Format status dengan badge HTML
     * 
     * @param string $status
     * @param bool $with_icon
     * @return string
     */
    function format_status_badge($status, $with_icon = true)
    {
        $config = [
            'draft' => [
                'class' => 'badge-secondary',
                'icon' => 'ni-settings',
                'text' => 'Draft'
            ],
            'submitted' => [
                'class' => 'badge-info',
                'icon' => 'ni-send', 
                'text' => 'Diajukan'
            ],
            'review_pembimbing' => [
                'class' => 'badge-warning',
                'icon' => 'ni-single-02',
                'text' => 'Review Pembimbing'
            ],
            'review_kaprodi' => [
                'class' => 'badge-warning', 
                'icon' => 'ni-badge',
                'text' => 'Review Kaprodi'
            ],
            'approved' => [
                'class' => 'badge-success',
                'icon' => 'ni-check-bold',
                'text' => 'Disetujui'
            ],
            'rejected' => [
                'class' => 'badge-danger',
                'icon' => 'ni-fat-remove',
                'text' => 'Ditolak'
            ],
            'scheduled' => [
                'class' => 'badge-primary',
                'icon' => 'ni-calendar-grid-58',
                'text' => 'Terjadwal'
            ],
            'completed' => [
                'class' => 'badge-success',
                'icon' => 'ni-trophy',
                'text' => 'Selesai'
            ]
        ];
        
        $cfg = $config[$status] ?? [
            'class' => 'badge-light',
            'icon' => 'ni-help',
            'text' => ucfirst($status)
        ];
        
        $icon_html = $with_icon ? "<i class='ni {$cfg['icon']}'></i> " : '';
        
        return "<span class='badge {$cfg['class']}'>{$icon_html}{$cfg['text']}</span>";
    }
}

if (!function_exists('generate_seminar_filename')) {
    /**
     * Generate filename untuk file seminar proposal
     * 
     * @param int $mahasiswa_id
     * @param string $original_name
     * @param string $prefix
     * @return string
     */
    function generate_seminar_filename($mahasiswa_id, $original_name, $prefix = 'seminar_proposal')
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid(rand(), true)), 0, 6);
        
        return "{$prefix}_{$mahasiswa_id}_{$timestamp}_{$random}.{$extension}";
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format ukuran file untuk display
     * 
     * @param int $bytes
     * @return string
     */
    function format_file_size($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}