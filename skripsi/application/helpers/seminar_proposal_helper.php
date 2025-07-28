<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Helper (Minimal Version)
 * 
 * File: application/helpers/seminar_proposal_helper.php
 * 
 * Helper tambahan untuk seminar proposal - menggunakan functions dari 
 * seminar_proposal_mahasiswa_helper.php yang sudah ada
 */

// Load existing helper functions dari seminar_proposal_mahasiswa_helper.php
if (!function_exists('validate_file_upload')) {
    // Include functions dari helper yang sudah ada
    $CI =& get_instance();
    $CI->load->helper('seminar_proposal_mahasiswa');
}

// Alias functions untuk backward compatibility (jika diperlukan)
if (!function_exists('basic_malware_scan')) {
    /**
     * Basic malware scan untuk file upload
     * 
     * @param string $file_path
     * @return bool
     */
    function basic_malware_scan($file_path)
    {
        // Basic check untuk file yang aman
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Cek ukuran file (max 5MB untuk safety)
        if (filesize($file_path) > 5 * 1024 * 1024) {
            return false;
        }
        
        // Basic signature check
        $content = file_get_contents($file_path, false, null, 0, 1024);
        $dangerous_patterns = ['<?php', '<%', '<script', 'eval(', 'exec('];
        
        foreach ($dangerous_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return false;
            }
        }
        
        return true;
    }
}

if (!function_exists('generate_seminar_filename')) {
    /**
     * Generate filename untuk seminar proposal
     * 
     * @param int $mahasiswa_id
     * @param string $original_name
     * @return string
     */
    function generate_seminar_filename($mahasiswa_id, $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid(rand(), true)), 0, 6);
        
        return "seminar_proposal_{$mahasiswa_id}_{$timestamp}_{$random}.{$extension}";
    }
}

if (!function_exists('format_status_badge')) {
    /**
     * Format status badge untuk UI
     * 
     * @param string $status
     * @return string
     */
    function format_status_badge($status)
    {
        $badges = [
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'submitted' => '<span class="badge badge-info">Diajukan</span>',
            'review_pembimbing' => '<span class="badge badge-warning">Review Pembimbing</span>',
            'review_kaprodi' => '<span class="badge badge-warning">Review Kaprodi</span>',
            'approved' => '<span class="badge badge-success">Disetujui</span>',
            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
            'scheduled' => '<span class="badge badge-primary">Terjadwal</span>',
            'completed' => '<span class="badge badge-success">Selesai</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }
}