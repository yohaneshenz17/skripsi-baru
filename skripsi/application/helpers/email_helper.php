<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Email Helper untuk SIM Tugas Akhir STK St. Yakobus Merauke
 * Updated: Support for Port 587 + TLS configuration
 */

if (!function_exists('send_email_notification')) {
    /**
     * Fungsi umum untuk mengirim email dengan template yang konsisten
     */
    function send_email_notification($to_email, $subject, $message, $from_name = 'SIM Tugas Akhir STK St. Yakobus', $attachments = array())
    {
        $CI =& get_instance();
        
        try {
            // Load library email dengan konfigurasi
            $CI->load->library('email');
            
            // Load konfigurasi email secara manual
            $CI->load->config('email');
            
            // Initialize email dengan konfigurasi
            $CI->email->initialize();
            
            // Clear previous data
            $CI->email->clear();
            
            // Setup email
            $CI->email->from('stkyakobus@gmail.com', $from_name);
            $CI->email->to($to_email);
            $CI->email->subject($subject);
            $CI->email->message($message);
            
            // Tambahkan attachment jika ada
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (file_exists($file)) {
                        $CI->email->attach($file);
                    }
                }
            }
            
            // Kirim email
            if ($CI->email->send()) {
                log_message('info', "Email berhasil dikirim ke: {$to_email} dengan subject: {$subject}");
                return TRUE;
            } else {
                log_message('error', "Gagal mengirim email ke: {$to_email}. Debug: " . $CI->email->print_debugger());
                return FALSE;
            }
            
        } catch (Exception $e) {
            log_message('error', "Exception saat mengirim email: " . $e->getMessage());
            return FALSE;
        }
    }
}

if (!function_exists('get_email_template')) {
    /**
     * Menghasilkan template email yang konsisten
     */
    function get_email_template($title, $content, $type = 'info', $buttons = array())
    {
        $colors = [
            'success' => '#28a745',
            'warning' => '#ffc107', 
            'danger' => '#dc3545',
            'info' => '#007bff'
        ];
        
        $primary_color = isset($colors[$type]) ? $colors[$type] : $colors['info'];
        
        $button_html = '';
        if (!empty($buttons)) {
            foreach ($buttons as $button) {
                $btn_color = isset($button['color']) ? $button['color'] : $primary_color;
                $button_html .= "
                <p style='margin-top: 20px;'>
                    <a href='{$button['url']}' 
                       style='background-color: {$btn_color}; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       {$button['text']}
                    </a>
                </p>";
            }
        }
        
        $template = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background-color: {$primary_color}; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>{$title}</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    {$content}
                    {$button_html}
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
        
        return $template;
    }
}

if (!function_exists('validate_email_config')) {
    /**
     * Validasi konfigurasi email - UPDATED untuk Port 587 + TLS
     */
    function validate_email_config()
    {
        $CI =& get_instance();
        $result = ['status' => TRUE, 'errors' => []];
        
        try {
            // Load konfigurasi email
            $CI->load->config('email');
            
            // Cek konfigurasi email dengan cara yang benar
            $required_configs = [
                'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass'
            ];
            
            foreach ($required_configs as $config) {
                $value = $CI->config->item($config);
                if (empty($value)) {
                    $result['status'] = FALSE;
                    $result['errors'][] = "Konfigurasi {$config} tidak ditemukan atau kosong";
                }
            }
            
            // Validasi khusus untuk konfigurasi Gmail Port 587 + TLS (YANG TERBUKTI BEKERJA)
            $smtp_host = $CI->config->item('smtp_host');
            $smtp_port = $CI->config->item('smtp_port');
            $smtp_user = $CI->config->item('smtp_user');
            $smtp_crypto = $CI->config->item('smtp_crypto');
            
            // Validasi untuk konfigurasi yang sudah PROVEN WORKING
            if (!empty($smtp_host) && $smtp_host !== 'smtp.gmail.com') {
                $result['errors'][] = "SMTP host harus: smtp.gmail.com (untuk port 587 + TLS)";
            }
            
            if (!empty($smtp_port) && $smtp_port != 587) {
                $result['errors'][] = "SMTP port harus: 587 (TLS - terbukti bekerja)";
            }
            
            if (!empty($smtp_crypto) && $smtp_crypto !== 'tls') {
                $result['errors'][] = "SMTP crypto harus: tls (untuk port 587)";
            }
            
            if (!empty($smtp_user) && $smtp_user !== 'stkyakobus@gmail.com') {
                $result['errors'][] = "SMTP user harus: stkyakobus@gmail.com";
            }
            
            // Cek extension yang diperlukan
            if (!extension_loaded('openssl')) {
                $result['status'] = FALSE;
                $result['errors'][] = "Extension OpenSSL tidak tersedia (diperlukan untuk TLS)";
            }
            
            // Cek direktori log
            $log_path = APPPATH . 'logs/';
            if (!is_writable($log_path)) {
                $result['errors'][] = "Direktori log tidak dapat ditulis: {$log_path}";
            }
            
            // Tambahan: Validasi protokol
            $protocol = $CI->config->item('protocol');
            if (!empty($protocol) && $protocol !== 'smtp') {
                $result['errors'][] = "Protocol harus: smtp";
            }
            
            // Info tambahan (bukan error, tapi informasi)
            if (empty($result['errors'])) {
                $result['info'] = [
                    'config_type' => 'Gmail SMTP Port 587 + TLS',
                    'status' => 'Konfigurasi sesuai dengan setup yang terbukti bekerja',
                    'last_tested' => '16 July 2025 - SUCCESS'
                ];
            }
            
        } catch (Exception $e) {
            $result['status'] = FALSE;
            $result['errors'][] = "Error loading config: " . $e->getMessage();
        }
        
        return $result;
    }
}

if (!function_exists('test_email_connection')) {
    /**
     * Test koneksi email yang diperbaiki
     */
    function test_email_connection($test_email = 'sipd@stkyakobus.ac.id')
    {
        $CI =& get_instance();
        
        try {
            // Load email library dan konfigurasi
            $CI->load->library('email');
            $CI->load->config('email');
            
            // Initialize dengan konfigurasi
            $CI->email->initialize();
            $CI->email->clear();
            
            $subject = 'Test Koneksi Email - SIM Tugas Akhir [' . date('H:i:s') . ']';
            $message = get_email_template(
                'Test Koneksi Email',
                '<p>Ini adalah email test untuk memastikan konfigurasi email bekerja dengan baik.</p>
                 <p><strong>Waktu pengiriman:</strong> ' . date('d F Y, H:i:s') . ' WIT</p>
                 <p><strong>Konfigurasi:</strong> Gmail SMTP Port 587 + TLS</p>
                 <p><strong>Status:</strong> Konfigurasi email berhasil!</p>',
                'success'
            );
            
            $CI->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir - Test');
            $CI->email->to($test_email);
            $CI->email->subject($subject);
            $CI->email->message($message);
            
            if ($CI->email->send()) {
                return [
                    'status' => TRUE,
                    'message' => 'Email test berhasil dikirim ke: ' . $test_email,
                    'config_used' => 'smtp.gmail.com:587 (TLS)',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'status' => FALSE,
                    'message' => 'Gagal mengirim email: ' . $CI->email->print_debugger(),
                    'config_attempted' => 'smtp.gmail.com:587 (TLS)'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => FALSE,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('validate_current_email_setup')) {
    /**
     * Validasi setup email yang sedang aktif saat ini
     */
    function validate_current_email_setup()
    {
        $CI =& get_instance();
        $CI->load->config('email');
        
        $current_config = [
            'protocol' => $CI->config->item('protocol'),
            'smtp_host' => $CI->config->item('smtp_host'),
            'smtp_port' => $CI->config->item('smtp_port'),
            'smtp_crypto' => $CI->config->item('smtp_crypto'),
            'smtp_user' => $CI->config->item('smtp_user'),
            'mailtype' => $CI->config->item('mailtype')
        ];
        
        // Cek apakah menggunakan konfigurasi yang terbukti bekerja
        $is_proven_config = (
            $current_config['smtp_host'] === 'smtp.gmail.com' &&
            $current_config['smtp_port'] == 587 &&
            $current_config['smtp_crypto'] === 'tls' &&
            $current_config['protocol'] === 'smtp'
        );
        
        return [
            'current_config' => $current_config,
            'is_proven_working' => $is_proven_config,
            'recommendation' => $is_proven_config ? 
                'Konfigurasi sudah optimal' : 
                'Gunakan smtp.gmail.com:587 dengan TLS untuk hasil terbaik'
        ];
    }
}

if (!function_exists('log_email_activity')) {
    /**
     * Log aktivitas email untuk monitoring
     */
    function log_email_activity($action, $recipient, $subject, $details = '')
    {
        $log_message = "EMAIL {$action}: To={$recipient}, Subject={$subject}";
        if (!empty($details)) {
            $log_message .= ", Details={$details}";
        }
        
        log_message('info', $log_message);
    }
}

/**
 * Format tanggal Indonesia
 */
if (!function_exists('format_tanggal_indonesia')) {
    function format_tanggal_indonesia($date) {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $timestamp = strtotime($date);
        $hari = date('d', $timestamp);
        $bulan_num = date('n', $timestamp);
        $tahun = date('Y', $timestamp);
        
        return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
    }
}

/* End of file email_helper.php */
/* Location: ./application/helpers/email_helper.php */