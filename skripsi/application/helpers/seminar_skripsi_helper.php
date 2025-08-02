<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Helper - Simple & Consistent
 * 
 * Helper functions yang minimal tapi effective, mengikuti pattern existing
 * Fokus pada functions yang benar-benar dibutuhkan tanpa redundancy
 * 
 * File: application/helpers/seminar_skripsi_helper.php
 * 
 * @package     SIM_TA
 * @subpackage  Helpers
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Simple & Consistent)
 */

// Load existing helper functions jika diperlukan
if (!function_exists('validate_file_upload')) {
    $CI =& get_instance();
    $CI->load->helper('seminar_proposal_mahasiswa');
}

if (!function_exists('get_seminar_skripsi_badge_count')) {
    /**
     * Get badge count untuk seminar skripsi - untuk template integration
     * Follow pattern dari seminar_proposal
     * 
     * @param int $dosen_id
     * @return int
     */
    function get_seminar_skripsi_badge_count($dosen_id = null)
    {
        $CI =& get_instance();
        
        if (!$dosen_id) {
            $dosen_id = $CI->session->userdata('id');
        }
        
        if (!$dosen_id) return 0;
        
        try {
            // Load model jika belum ada
            if (!isset($CI->seminar_skripsi_model)) {
                $CI->load->model('Seminar_skripsi_model', 'seminar_skripsi_model');
            }
            
            return $CI->seminar_skripsi_model->get_badge_count_dosen($dosen_id);
        } catch (Exception $e) {
            log_message('debug', 'Seminar skripsi badge count error: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('get_seminar_skripsi_status_badge')) {
    /**
     * Get status badge HTML untuk seminar skripsi
     * Follow pattern existing badge system
     * 
     * @param string $status
     * @return string
     */
    function get_seminar_skripsi_status_badge($status)
    {
        $badges = [
            'draft' => ['class' => 'secondary', 'text' => 'Draft'],
            'submitted' => ['class' => 'warning', 'text' => 'Submitted'],
            'review_pembimbing' => ['class' => 'warning', 'text' => 'Review Pembimbing'],
            'review_kaprodi' => ['class' => 'info', 'text' => 'Review Kaprodi'],
            'approved' => ['class' => 'primary', 'text' => 'Disetujui'],
            'scheduled' => ['class' => 'success', 'text' => 'Terjadwal'],
            'completed' => ['class' => 'success', 'text' => 'Selesai'],
            'rejected' => ['class' => 'danger', 'text' => 'Ditolak']
        ];
        
        $badge = $badges[$status] ?? ['class' => 'secondary', 'text' => 'Unknown'];
        
        return '<span class="badge badge-' . $badge['class'] . '">' . $badge['text'] . '</span>';
    }
}

if (!function_exists('get_seminar_skripsi_progress_percentage')) {
    /**
     * Get progress percentage berdasarkan status
     * Follow workflow document specifications
     * 
     * @param string $status
     * @return int
     */
    function get_seminar_skripsi_progress_percentage($status)
    {
        $progress_map = [
            'draft' => 0,           // Pengajuan (0%)
            'submitted' => 20,      // Review Dosen (20%)
            'review_pembimbing' => 20,
            'review_kaprodi' => 40, // Review Kaprodi (40%)
            'approved' => 60,       // Penjadwalan (60%)
            'scheduled' => 80,      // Administrasi (80%)
            'completed' => 100,     // Selesai (100%)
            'rejected' => 0
        ];
        
        return $progress_map[$status] ?? 0;
    }
}

if (!function_exists('check_seminar_skripsi_eligibility_quick')) {
    /**
     * Quick eligibility check - untuk AJAX/real-time validation
     * Simplified version dari model method
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    function check_seminar_skripsi_eligibility_quick($proposal_id, $mahasiswa_id)
    {
        $CI =& get_instance();
        $errors = [];
        
        try {
            // Check workflow status
            $CI->db->select('workflow_status');
            $CI->db->from('proposal_mahasiswa');
            $CI->db->where('id', $proposal_id);
            $CI->db->where('mahasiswa_id', $mahasiswa_id);
            $proposal = $CI->db->get()->row();
            
            if (!$proposal) {
                $errors[] = 'Proposal tidak ditemukan';
                return ['eligible' => false, 'errors' => $errors];
            }
            
            if ($proposal->workflow_status !== 'seminar_skripsi') {
                $errors[] = 'Belum menyelesaikan tahap penelitian';
            }
            
            // Quick jurnal check
            $CI->db->select('COUNT(*) as count');
            $CI->db->from('jurnal_bimbingan');
            $CI->db->where('proposal_id', $proposal_id);
            $CI->db->where('status_validasi', '1');
            $jurnal_count = $CI->db->get()->row()->count;
            
            if ($jurnal_count < 14) {
                $errors[] = 'Minimal 14 jurnal bimbingan yang divalidasi (saat ini: ' . $jurnal_count . ')';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Terjadi kesalahan sistem';
        }
        
        return [
            'eligible' => empty($errors),
            'errors' => $errors
        ];
    }
}

if (!function_exists('format_seminar_skripsi_filename')) {
    /**
     * Generate safe filename untuk seminar skripsi
     * Follow existing pattern
     * 
     * @param int $mahasiswa_id
     * @param string $original_name
     * @return string
     */
    function format_seminar_skripsi_filename($mahasiswa_id, $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid(rand(), true)), 0, 6);
        
        return "seminar_skripsi_{$mahasiswa_id}_{$timestamp}_{$random}.{$extension}";
    }
}

if (!function_exists('validate_seminar_skripsi_file_quick')) {
    /**
     * Quick file validation untuk seminar skripsi
     * Simplified untuk real-time validation
     * 
     * @param array $file $_FILES array element
     * @return array
     */
    function validate_seminar_skripsi_file_quick($file)
    {
        $errors = [];
        $allowed_types = ['pdf', 'doc', 'docx'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (empty($file['name'])) {
            $errors[] = 'File skripsi wajib diupload';
            return ['valid' => false, 'errors' => $errors];
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = 'File harus berformat PDF, DOC, atau DOCX';
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = 'Ukuran file maksimal 2MB';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

if (!function_exists('get_seminar_skripsi_next_action')) {
    /**
     * Get next action description berdasarkan status
     * Follow workflow specifications
     * 
     * @param string $status
     * @param string $current_step
     * @return string
     */
    function get_seminar_skripsi_next_action($status, $current_step = '')
    {
        $actions = [
            'draft' => 'Lengkapi dan submit pengajuan seminar skripsi',
            'submitted' => 'Menunggu review dari dosen pembimbing',
            'review_pembimbing' => 'Dosen pembimbing sedang melakukan review',
            'review_kaprodi' => 'Kaprodi sedang melakukan validasi dan Turnitin check',
            'approved' => 'Menunggu penjadwalan seminar dari Kaprodi',
            'scheduled' => 'Bersiap untuk pelaksanaan seminar skripsi',
            'completed' => 'Seminar selesai, siap lanjut ke tahap publikasi',
            'rejected' => 'Perbaiki berdasarkan catatan dan ajukan ulang'
        ];
        
        return $actions[$status] ?? 'Status tidak dikenal';
    }
}

if (!function_exists('send_seminar_skripsi_notification_simple')) {
    /**
     * Send simple notification untuk seminar skripsi
     * Integration dengan existing email system
     * 
     * @param string $type
     * @param array $data
     * @return bool
     */
    function send_seminar_skripsi_notification_simple($type, $data)
    {
        // Load email helper jika ada
        if (function_exists('send_email_notification')) {
            
            $templates = [
                'pengajuan_masuk' => [
                    'subject' => 'Pengajuan Seminar Skripsi - {mahasiswa_nama}',
                    'message' => "Pengajuan seminar skripsi telah diterima dari mahasiswa:\n\nNama: {mahasiswa_nama}\nNIM: {nim}\nJudul: {judul}\n\nSilakan login ke sistem untuk melakukan review."
                ],
                'review_approved' => [
                    'subject' => 'Seminar Skripsi Disetujui Pembimbing',
                    'message' => "Pengajuan seminar skripsi Anda telah disetujui oleh dosen pembimbing.\n\nJudul: {judul}\nStatus: Menunggu validasi Kaprodi\n\nSilakan pantau progress melalui sistem."
                ],
                'review_rejected' => [
                    'subject' => 'Seminar Skripsi Ditolak Pembimbing',
                    'message' => "Pengajuan seminar skripsi Anda ditolak oleh dosen pembimbing.\n\nJudul: {judul}\nKomentar: {komentar}\n\nSilakan perbaiki dan ajukan ulang."
                ],
                'seminar_scheduled' => [
                    'subject' => 'Jadwal Seminar Skripsi - {mahasiswa_nama}',
                    'message' => "Seminar skripsi telah dijadwalkan:\n\nMahasiswa: {mahasiswa_nama} ({nim})\nJudul: {judul}\nTanggal: {tanggal}\nJam: {jam}\nTempat: {tempat}"
                ]
            ];
            
            if (isset($templates[$type])) {
                $template = $templates[$type];
                
                // Replace placeholders
                $subject = $template['subject'];
                $message = $template['message'];
                
                foreach ($data as $key => $value) {
                    $subject = str_replace('{' . $key . '}', $value, $subject);
                    $message = str_replace('{' . $key . '}', $value, $message);
                }
                
                // Send email
                if (isset($data['email']) && !empty($data['email'])) {
                    return send_email_notification($data['email'], $subject, $message);
                }
            }
        }
        
        return false;
    }
}

if (!function_exists('format_plagiarism_badge')) {
    /**
     * Format plagiarism percentage dengan badge yang sesuai
     * 
     * @param float $percentage
     * @return string
     */
    function format_plagiarism_badge($percentage)
    {
        if (empty($percentage)) {
            return '<span class="badge badge-secondary">Belum dicek</span>';
        }
        
        $class = $percentage <= 30 ? 'success' : 'danger';
        $icon = $percentage <= 30 ? 'check-circle' : 'exclamation-triangle';
        
        return '<span class="badge badge-' . $class . '"><i class="fas fa-' . $icon . ' mr-1"></i>' . $percentage . '%</span>';
    }
}

if (!function_exists('calculate_duration_simple')) {
    /**
     * Calculate duration dari created ke completed (simple version)
     * 
     * @param string $created_at
     * @param string $completed_at
     * @return string
     */
    function calculate_duration_simple($created_at, $completed_at = null)
    {
        if (empty($created_at)) {
            return '0 hari';
        }
        
        $start = new DateTime($created_at);
        $end = $completed_at ? new DateTime($completed_at) : new DateTime();
        
        $diff = $start->diff($end);
        $days = $diff->days;
        
        if ($days == 0) {
            return 'Hari ini';
        } elseif ($days == 1) {
            return '1 hari';
        } elseif ($days < 30) {
            return $days . ' hari';
        } else {
            $months = floor($days / 30);
            return $months . ' bulan';
        }
    }
}

if (!function_exists('basic_file_security_check')) {
    /**
     * Basic security check untuk uploaded file
     * Simple version untuk production use
     * 
     * @param string $file_path
     * @return bool
     */
    function basic_file_security_check($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Check file size (max 5MB for safety)
        if (filesize($file_path) > 5 * 1024 * 1024) {
            return false;
        }
        
        // Basic content check (first 1KB)
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

/**
 * CATATAN IMPLEMENTASI:
 * 
 * 1. Helper functions minimalis tapi effective
 * 2. Follow pattern dari existing seminar_proposal helpers
 * 3. Focus pada functions yang benar-benar dibutuhkan
 * 4. Integration dengan existing email dan notification system
 * 5. Badge count function untuk template integration
 * 6. Quick validation functions untuk AJAX usage
 * 
 * PENGGUNAAN:
 * 
 * 1. Load helper di controller: $this->load->helper('seminar_skripsi');
 * 2. Atau autoload di config/autoload.php: $autoload['helper'] = array('seminar_skripsi');
 * 3. Badge count untuk template: get_seminar_skripsi_badge_count()
 * 4. Status badge di view: echo get_seminar_skripsi_status_badge($status);
 * 5. Progress bar: get_seminar_skripsi_progress_percentage($status);
 * 
 * INTEGRASI TEMPLATE:
 * 
 * // Di template dosen untuk badge notification
 * $badge_count = get_seminar_skripsi_badge_count($dosen_id);
 * 
 * // Di view untuk status display
 * echo get_seminar_skripsi_status_badge($seminar->status);
 * 
 * // Untuk progress bar
 * $progress = get_seminar_skripsi_progress_percentage($seminar->status);
 */