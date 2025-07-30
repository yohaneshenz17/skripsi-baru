<?php
/**
 * Email Retry Helper untuk Enhanced Error Handling
 * File: application/helpers/email_retry_helper.php
 * 
 * 🔧 Solusi untuk issue: "NO ERROR HANDLING, Only logging, no retry mechanism"
 */

defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('send_email_with_retry')) {
    /**
     * 📧 Send email dengan retry mechanism dan comprehensive error handling
     * 
     * @param object $ci - CodeIgniter instance
     * @param array $email_data - Email configuration dan content
     * @param int $max_retries - Maximum retry attempts (default: 3)
     * @return array - Result dengan status dan details
     */
    function send_email_with_retry($ci, $email_data, $max_retries = 3) {
        $required_fields = ['to', 'subject', 'message'];
        
        // Validasi input data
        foreach ($required_fields as $field) {
            if (empty($email_data[$field])) {
                return [
                    'success' => false,
                    'error' => "Required field missing: {$field}",
                    'error_code' => 'MISSING_FIELD'
                ];
            }
        }
        
        // Default email configuration
        $default_config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug', // TODO: Move to environment variable
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30
        ];
        
        // Merge dengan custom config jika ada
        $email_config = array_merge($default_config, $email_data['config'] ?? []);
        
        $last_error = '';
        $debug_info = [];
        
        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            try {
                log_message('info', "Email attempt {$attempt}/{$max_retries} to: {$email_data['to']}");
                
                // Initialize email library dengan fresh config
                $ci->email->clear();
                $ci->email->initialize($email_config);
                
                // Set email data
                $ci->email->from(
                    $email_data['from_email'] ?? $email_config['smtp_user'], 
                    $email_data['from_name'] ?? 'SIM Tugas Akhir STK Santo Yakobus'
                );
                $ci->email->to($email_data['to']);
                $ci->email->subject($email_data['subject']);
                $ci->email->message($email_data['message']);
                
                // Add CC jika ada
                if (!empty($email_data['cc'])) {
                    $ci->email->cc($email_data['cc']);
                }
                
                // Add BCC jika ada
                if (!empty($email_data['bcc'])) {
                    $ci->email->bcc($email_data['bcc']);
                }
                
                // Attempt to send
                $send_result = $ci->email->send();
                
                if ($send_result) {
                    // SUCCESS!
                    log_message('info', "Email sent successfully on attempt {$attempt} to: {$email_data['to']}");
                    
                    return [
                        'success' => true,
                        'message' => 'Email berhasil dikirim',
                        'attempts' => $attempt,
                        'recipient' => $email_data['to'],
                        'subject' => $email_data['subject'],
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                } else {
                    // Send failed, get debug info
                    $debug_output = $ci->email->print_debugger();
                    $last_error = "Email send failed - Debug: " . strip_tags($debug_output);
                    $debug_info[] = [
                        'attempt' => $attempt,
                        'error' => $last_error,
                        'debug_output' => $debug_output
                    ];
                    
                    throw new Exception($last_error);
                }
                
            } catch (Exception $e) {
                $last_error = $e->getMessage();
                log_message('error', "Email attempt {$attempt} failed: {$last_error}");
                
                // Jika bukan attempt terakhir, tunggu sebelum retry
                if ($attempt < $max_retries) {
                    $wait_time = $attempt * 2; // Progressive backoff: 2s, 4s, 6s...
                    log_message('info', "Waiting {$wait_time} seconds before retry...");
                    sleep($wait_time);
                    continue;
                }
            }
        }
        
        // Semua attempts gagal
        log_message('error', "Email FAILED after {$max_retries} attempts to: {$email_data['to']}. Last error: {$last_error}");
        
        return [
            'success' => false,
            'error' => $last_error,
            'error_code' => 'MAX_RETRIES_EXCEEDED',
            'attempts' => $max_retries,
            'recipient' => $email_data['to'],
            'debug_info' => $debug_info,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

if (!function_exists('send_notification_email')) {
    /**
     * 🚀 Wrapper function untuk mengirim notification email dengan template
     * 
     * @param object $ci - CodeIgniter instance
     * @param string $template_type - Type template: 'success', 'rejection', 'reminder', etc.
     * @param array $data - Data untuk template email
     * @return array
     */
    function send_notification_email($ci, $template_type, $data) {
        $templates = [
            'proposal_submitted' => [
                'subject' => '✅ Pengajuan Seminar Proposal Berhasil',
                'template' => 'email_templates/proposal_submitted'
            ],
            'proposal_approved' => [
                'subject' => '🎉 Seminar Proposal Disetujui',
                'template' => 'email_templates/proposal_approved'
            ],
            'proposal_rejected' => [
                'subject' => '❌ Seminar Proposal Perlu Perbaikan',
                'template' => 'email_templates/proposal_rejected'
            ],
            'schedule_notification' => [
                'subject' => '📅 Jadwal Seminar Proposal',
                'template' => 'email_templates/schedule_notification'
            ]
        ];
        
        if (!isset($templates[$template_type])) {
            return [
                'success' => false,
                'error' => "Unknown email template: {$template_type}",
                'error_code' => 'INVALID_TEMPLATE'
            ];
        }
        
        $template_config = $templates[$template_type];
        
        // Generate email content dari template
        try {
            $email_content = $ci->load->view($template_config['template'], $data, true);
        } catch (Exception $e) {
            // Fallback ke simple email jika template tidak ada
            $email_content = generate_simple_email_content($template_type, $data);
        }
        
        $email_data = [
            'to' => $data['recipient_email'],
            'subject' => $template_config['subject'] . (isset($data['subject_suffix']) ? ' - ' . $data['subject_suffix'] : ''),
            'message' => $email_content,
            'cc' => $data['cc'] ?? null,
            'bcc' => $data['bcc'] ?? null
        ];
        
        return send_email_with_retry($ci, $email_data);
    }
}

if (!function_exists('generate_simple_email_content')) {
    /**
     * Generate simple email content sebagai fallback
     */
    function generate_simple_email_content($template_type, $data) {
        $base_style = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center; color: white;'>
                <h2 style='margin: 0;'>{TITLE}</h2>
            </div>
            <div style='padding: 20px; background: #f8f9fa;'>
                {CONTENT}
            </div>
            <div style='background: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
            </div>
        </div>";
        
        switch ($template_type) {
            case 'proposal_submitted':
                $title = '✅ Pengajuan Berhasil';
                $content = "
                <p>Kepada Yth. <strong>{$data['nama_mahasiswa']}</strong>,</p>
                <p>Pengajuan seminar proposal Anda telah berhasil dikirim dan sedang menunggu review dari dosen pembimbing.</p>
                <p><strong>Judul:</strong> {$data['judul']}</p>
                <p>Anda akan mendapat notifikasi email selanjutnya setelah review selesai.</p>
                ";
                break;
                
            case 'proposal_approved':
                $title = '🎉 Proposal Disetujui';
                $content = "
                <p>Selamat! Seminar proposal Anda telah <strong>DISETUJUI</strong>.</p>
                <p><strong>Mahasiswa:</strong> {$data['nama_mahasiswa']}</p>
                <p><strong>Judul:</strong> {$data['judul']}</p>
                <p>Selanjutnya akan dilakukan penjadwalan seminar.</p>
                ";
                break;
                
            case 'proposal_rejected':
                $title = '❌ Perlu Perbaikan';
                $content = "
                <p>Pengajuan seminar proposal Anda perlu diperbaiki.</p>
                <p><strong>Mahasiswa:</strong> {$data['nama_mahasiswa']}</p>
                <p><strong>Catatan:</strong> {$data['komentar']}</p>
                <p>Silakan lakukan perbaikan dan ajukan ulang.</p>
                ";
                break;
                
            default:
                $title = 'Notifikasi Sistem';
                $content = "<p>Anda memiliki notifikasi baru dari sistem SIM Tugas Akhir.</p>";
        }
        
        return str_replace(['{TITLE}', '{CONTENT}'], [$title, $content], $base_style);
    }
}

if (!function_exists('log_email_activity')) {
    /**
     * Log email activity ke database (jika tabel tersedia)
     */
    function log_email_activity($ci, $email_result, $context = '') {
        try {
            // Check if email_logs table exists
            if (!$ci->db->table_exists('email_logs')) {
                return false; // Skip logging jika tabel tidak ada
            }
            
            $log_data = [
                'recipient' => $email_result['recipient'] ?? '',
                'subject' => $email_result['subject'] ?? '',
                'status' => $email_result['success'] ? 'sent' : 'failed',
                'attempts' => $email_result['attempts'] ?? 1,
                'error_message' => $email_result['error'] ?? null,
                'context' => $context,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $ci->session->userdata('id') ?? null
            ];
            
            return $ci->db->insert('email_logs', $log_data);
            
        } catch (Exception $e) {
            log_message('error', 'Failed to log email activity: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('create_email_logs_table')) {
    /**
     * Helper untuk membuat tabel email_logs (run sekali)
     */
    function create_email_logs_table($ci) {
        $sql = "
        CREATE TABLE IF NOT EXISTS `email_logs` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `recipient` varchar(255) NOT NULL,
            `subject` varchar(500) DEFAULT NULL,
            `status` enum('sent','failed','pending') DEFAULT 'pending',
            `attempts` int(11) DEFAULT 1,
            `error_message` text DEFAULT NULL,
            `context` varchar(100) DEFAULT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `created_by` bigint(20) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_recipient` (`recipient`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        return $ci->db->query($sql);
    }
}